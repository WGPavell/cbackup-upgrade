<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\controllers;

use yii\filters\AjaxFilter;
use app\models\Config;
use app\models\Exclusion;
use app\models\NodeAltInterfaceActions;
use app\models\Plugin;
use dautkom\ipv4\IPv4;
use dautkom\netsnmp\NetSNMP;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\search\NodeSearch;
use app\models\Node;
use app\models\Credential;
use app\models\Device;
use app\models\Network;
use app\models\OutBackup;
use app\models\OutOspfBackup;
use app\models\Task;
use app\models\DeviceAuthTemplate;
use yii\web\Response;
use app\components\NetSsh;
use Diff;
use Diff_Renderer_Html_Inline;
use yii\web\ForbiddenHttpException;
use app\models\AuthItemNode;
use app\models\search\RoleSearch;


/**
 * @package app\controllers
 */
class NodeController extends Controller
{

    /**
     * @var string
     */
    public $defaultAction = 'list';


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'                    => ['post'],
                    'inquire'                   => ['post'],
                    'ajax-run-interface-action' => ['post'],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'inquire',
                    'ajax-download',
                    'ajax-ospf-download',
                    'ajax-load-config',
                    'ajax-load-file-diff',
                    'ajax-load-ospf',
                    'ajax-load-ospf-diff',
                    'ajax-set-auth-template',
                    'ajax-load-widget',
                    'ajax-set-prepend-location',
                    'ajax-run-interface-action',
                    'ajax-backup-node',
                    'ajax-set-node-credentials',
                    'ajax-protect-node',
                    'ajax-assign-roles',
                ]
            ],
            'access' => [
              'class' => \yii\filters\AccessControl::className(),
              'rules' => [
                  [
                      'allow' => true,
                      'roles' => ['admin'],
                  ],
                  [
                    'actions' => ['list'],
                    'allow' => true,
                    'roles' => ['@']
                  ],
                  [
                    'actions' => ['view', 'ajax-download', 'ajax-ospf-download', 'ajax-load-config', 'ajax-load-file-diff', 'ajax-load-ospf', 'ajax-load-ospf-diff', 'ajax-load-widget', 'download'],
                    'allow' => Node::isUserAllowed(Yii::$app->request->get('id')),
                    'roles' => ['@'],
                  ]
              ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionList()
    {

        $searchModel  = new NodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /** List of devices */
        $devices = ArrayHelper::map(Device::find()->all(), 'id', function ($data) { /** @var $data Device */
            return "{$data->vendor} {$data->model}";
        }, 'vendor');

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'networks'     => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'credentials'  => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
            'auth_list'    => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column(),
            'devices'      => $devices
        ]);

    }


    /**
     * Render orphans list
     *
     * @return string
     */
    public function actionOrphans()
    {
        return $this->render('orphans', [
            'dataProvider' => Node::getOrphans()
        ]);
    }


    /**
     * Add new node
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Node();

        if (isset($_POST['Node'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $model->manual = 1;

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['/node/view', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form', [
            'model'       => $model,
            'networks'    => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'credentials' => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
            'devices'     => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor')
        ]);
    }


    /**
     * Edit node
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        $deniedRoles  = new RoleSearch();
        $dataProvider = $deniedRoles->search(Yii::$app->request->queryParams);
        $deniedRoles->node_id = $id;

        /** Prevent user from changing automatically added nodes */
        if ($model->manual == 0) {
            \Y::flashAndRedirect('warning', Yii::t('node', 'Edit automatically added nodes is forbidden!'), '/node/list');
        }

        if (isset($_POST['Node'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->ip));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->ip));
                    }

                    return $this->redirect(['/node/view', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form', [
            'model'       => $model,
            'networks'    => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'credentials' => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
            'devices'     => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor'),
            'searchModel' => $deniedRoles,
            'data'        => $dataProvider->getModels(),
            'dataProvider'=> $dataProvider,
        ]);

    }

    /**
     * Assign roles to node via Ajax
     *
     * @return string
     */
    public function actionAjaxAssignRoles()
    {

        $msg_status = 'error';
        $msg_text   = Yii::t('app', 'An error occurred while processing your request');

        $save_status   = [];
        $delete_status = [];

        if (Yii::$app->request->isAjax && isset($_POST['Role'])) {

            $_post = $_POST['Role'];

            foreach ($_post as $role_name => $data) {

                /** Find record in AuthItemNode */
                $record = AuthItemNode::find()->where(['node_id' => $data['node_id'], 'auth_item_name' => $role_name]);

                /** Add new record if it doesn't exists and set_node is set 1 */
                if (!$record->exists() && $data['set_role'] == '1') {
                    $model                 = new AuthItemNode();
                    $model->node_id        = $data['node_id'];
                    $model->auth_item_name = $role_name;
                    $save_status[]         = ($model->save()) ? true : false;
                }
                else {
                    $save_status[] = true;
                }

                /** Remove record if it exists and set node is set to 0 */
                if ($record->exists() && $data['set_role'] == '0') {
                    try {
                        $record->one()->delete();
                        $delete_status[] = true;
                    }
                    /** @noinspection PhpUndefinedClassInspection */
                    catch (\Throwable $e) {
                        $delete_status[] = false;
                    }
                }
                else {
                    $delete_status[] = true;
                }

            }

            /** Check if all save and remove requests return true if at least one return false show error */
            if ((!empty($save_status) && in_array(false, $save_status, true) === false) &&
                (!empty($delete_status) && in_array(false, $delete_status, true) === false)) {
                $msg_status = 'success';
                $msg_text   = Yii::t('app', 'Action successfully finished');
            }

        }

        return Json::encode(['status' => $msg_status, 'msg' => $msg_text]);

    }


    /**
     * @param  int $id
     * @return string
     */
    public function actionView($id)
    {

        $id   = intval($id);

        //if (!\Yii::$app->user->can('viewNode')) {
        //    throw new ForbiddenHttpException('Access denied');
        //}

        $data = Node::findOne(['id' => $id]);
        $cid  = Node::getCredentialsId($id);
        $ex   = Exclusion::exists($data->ip);

        /** Create alternative interfaces dataprovider */
        $interfaces   = ArrayHelper::toArray($data->altInterfaces);
        $int_provider = new ArrayDataProvider([
            'allModels' => $interfaces,
            'sort'  => new Sort(['attributes' => ['ip'], 'defaultOrder' => ['ip' => SORT_ASC]]),
            'pagination' => [
                'pageSize' => 9,
            ],
        ]);

        /** Create networks array for dropdownlist */
        $networks = Network::find()->select(['id', 'network', 'description'])->asArray()->all();
        $networks = ArrayHelper::map($networks, 'id', function ($data) {
            $description = (!empty($data['description'])) ? "- {$data['description']}" : "";
            return "{$data['network']} {$description}";
        });

        return $this->render('view', [
            'data'         => $data,
            'exclusion'    => $ex,
            'credential'   => Credential::findOne(['id' => $cid]),
            'task_info'    => Task::findOne('backup'),
            'commit_log'   => (Config::isGitRepo()) ? Node::getBackupCommitLog($id, 'backup') : null,
            'ospf_commit_log'   => (Config::isGitRepo()) ? Node::getBackupCommitLog($id, 'ospf_backup') : null,
            'int_provider' => $int_provider,
            'templates'    => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column(),
            'networks'     => $networks,
            'plugins'      => Plugin::find()->where(['enabled' => '1', 'widget' => 'node'])->all(),
            'credentials'  => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
        ]);
    }


    /**
     * Set node auth template via Ajax
     *
     * @param  int $node_id
     * @param  string $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSetAuthTemplate($node_id, $name)
    {

        $model = $this->findModel($node_id);
        $node  = (!is_null($model->hostname)) ? $model->hostname : $model->ip;
        $model->auth_template_name = $name;

        if ($model->validate(['auth_template_name']) && $model->save(false)) {
            $response = ['status' => 'success', 'msg' => Yii::t('app', 'Record <b>{0}</b> edited successfully.', $node)];
        }
        else {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $node)];
        }

        return Json::encode($response);

    }


    /** @noinspection PhpUndefinedClassInspection
     *  @param  int $id
     *  @return \yii\web\Response
     *  @throws NotFoundHttpException
     *  @throws \Throwable
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('node', 'Node <b>{0}</b> was successfully deleted.', $model->ip);
        }
        catch (\Exception $e) {
            $class   = 'danger';
            $message = Yii::t('node', 'An error occurred while deleting node <b>{0}</b>.', $model->ip);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/node/list']);

    }

    /**
     * Load config via Ajax
     *
     * @return bool|mixed|string
     */
    public function actionAjaxLoadOspf()
    {
        $response = Yii::t('node', 'File not found');

        if (isset($_POST)) {

            $_post = Yii::$app->request->post();

            /** Load config from DB */
            if ($_post['put'] == 'db') {
                $db_backup = OutOspfBackup::find()->select('ospf_config')->where(['node_id' => $_post['node_id']]);
                if ($db_backup->exists()) {
                    $config   = $db_backup->column();
                    $response = array_shift($config);
                }
            }

            /** Load config from file */
            if ($_post['put'] == 'file') {
                $path_to_file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'ospf_backup' . DIRECTORY_SEPARATOR . "{$_post['node_id']}.txt";
                if (file_exists($path_to_file)) {
                    $response = file_get_contents($path_to_file);
                }
            }
        }

        return Html::tag('pre', Html::encode($response));

    }

    public function actionAjaxLoadOspfDiff()
    {

        $response = Yii::t('app', 'An error occurred while processing your request');

        if (isset($_POST)) {

            $_post        = Yii::$app->request->post();
            $path_to_file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'ospf_backup' . DIRECTORY_SEPARATOR . "{$_post['node_id']}.txt";
            $backup       = null;
            $response     = [
                'meta' => [],
                'diff' => ''
            ];

            /** Get curent config file */
            if (file_exists($path_to_file)) {
                $content = file_get_contents($path_to_file);
                $backup  = ($content !== false) ? $content : null;
            }

            $response['meta'] = Node::getCommitMetaData($_post['hash'], 'ospf_backup');

            if (!is_null($backup)) {
                $git_file_ver     = explode("\n", Node::getBackupGitVersion($_post['node_id'], $_post['hash'], 'ospf_backup'));
                $cur_backup_ver   = explode("\n", $backup);
                $diff             = new Diff($git_file_ver, $cur_backup_ver);
                $renderer         = new Diff_Renderer_Html_Inline;
                $response['diff'] = str_replace(
                    ['<th>Old</th>', '<th>New</th>', '<th>Differences</th>'],
                    ['<th colspan="3">' . Yii::t('app', 'File: {0}.txt', $_post['node_id']) . '</th>'],
                    $diff->Render($renderer)
                );
            }

            return $this->renderPartial('diff', [
                'response' => $response
            ]);

        }

        return $response;

    }

    public function actionAjaxOspfDownload($id, $put, $hash = null, $directory)
    {
        return $this->renderPartial('_download_modal', [
            'id'   => $id,
            'put'  => $put,
            'hash' => $hash,
            'directory' => $directory
        ]);
    }


    /**
     * Load config via Ajax
     *
     * @return bool|mixed|string
     */
    public function actionAjaxLoadConfig()
    {

        $response = Yii::t('node', 'File not found');

        if (isset($_POST)) {

            $_post = Yii::$app->request->post();

            /** Load config from DB */
            if ($_post['put'] == 'db') {
                $db_backup = OutBackup::find()->select('config')->where(['node_id' => $_post['node_id']]);
                if ($db_backup->exists()) {
                    $config   = $db_backup->column();
                    $response = array_shift($config);
                }
            }

            /** Load config from file */
            if ($_post['put'] == 'file') {
                $path_to_file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$_post['node_id']}.txt";
                if (file_exists($path_to_file)) {
                    $response = file_get_contents($path_to_file);
                }
            }
        }

        return Html::tag('pre', Html::encode($response));

    }


    /**
     * Load file diff via Ajax
     *
     * @return array|string
     */
    public function actionAjaxLoadFileDiff()
    {

        $response = Yii::t('app', 'An error occurred while processing your request');

        if (isset($_POST)) {

            $_post        = Yii::$app->request->post();
            $path_to_file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$_post['node_id']}.txt";
            $backup       = null;
            $response     = [
                'meta' => [],
                'diff' => ''
            ];

            /** Get curent config file */
            if (file_exists($path_to_file)) {
                $content = file_get_contents($path_to_file);
                $backup  = ($content !== false) ? $content : null;
            }

            $response['meta'] = Node::getCommitMetaData($_post['hash'], 'backup');

            if (!is_null($backup)) {
                $git_file_ver     = explode("\n", Node::getBackupGitVersion($_post['node_id'], $_post['hash'], 'backup'));
                $cur_backup_ver   = explode("\n", $backup);
                $diff             = new Diff($git_file_ver, $cur_backup_ver);
                $renderer         = new Diff_Renderer_Html_Inline;
                $response['diff'] = str_replace(
                    ['<th>Old</th>', '<th>New</th>', '<th>Differences</th>'],
                    ['<th colspan="3">' . Yii::t('app', 'File: {0}.txt', $_post['node_id']) . '</th>'],
                    $diff->Render($renderer)
                );
            }

            return $this->renderPartial('diff', [
                'response' => $response
            ]);

        }

        return $response;

    }


    /**
     * @param  $id
     * @param  string $put
     * @param  string|null $hash
     * @param  bool $crlf
     * @return Response
     * @throws \yii\web\RangeNotSatisfiableHttpException
     * @throws \yii\base\ExitException
     */
    public function actionDownload($id, $put, $hash = null, $crlf = false, $directory = 'backup')
    {

        $config = '';
        $suffix = null;

        /** Get configuration backup based on put */
        if(!empty($hash)) {

            $meta   = Node::getCommitMetaData($hash, $directory);
            $config = Node::getBackupGitVersion($id, $hash, $directory);

            if( array_key_exists(3, $meta) ) {
                $suffix = preg_replace(['/:/', '/[^\d|\-]/'], ['-', '_'], $meta[3]);
                $suffix = ".".substr($suffix, 0, -7);
            }

        }
        elseif ($put == 'file') {
            $file_path = \Y::param('dataPath') . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . "{$id}.txt";
            $config    = file_get_contents($file_path);
        }
        elseif ($put == 'db') {
            $config = OutBackup::find()->select('config')->where(['node_id' => $id])->scalar();
        }
        else {
            \Y::flashAndRedirect('warning', Yii::t('node', 'Unknown backup destination passed'), 'node/view', ['id' => $id]);
            Yii::$app->end();
        }

        if( isset($crlf) && $crlf == true ) {
            $config = preg_replace('~\R~u', "\r\n", $config);
        }

        return Yii::$app->response->sendContentAsFile($config, "{$directory}_{$id}{$suffix}.txt", [
            'mimeType' => 'text/plain',
            'inline'   => false,
        ]);

    }


    /**
     * @param  int         $id
     * @param  string      $put
     * @param  string|null $hash
     * @return string
     */
    public function actionAjaxDownload($id, $put, $hash = null, $directory = "backup")
    {
        return $this->renderPartial('_download_modal', [
            'id'   => $id,
            'put'  => $put,
            'hash' => $hash,
            'directory' => $directory
        ]);
    }


    /**
     * @throws HttpException
     * @return string
     */
    public function actionInquire()
    {

        $ipaddr        = Yii::$app->request->post('ip');
        $credential_id = intval(Yii::$app->request->post('cid'));
        $network_id    = intval(Yii::$app->request->post('nid'));

        if( !filter_var($ipaddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            throw new HttpException(400, Yii::t('network', 'Invalid IP-address'));
        }

        if( !empty($network_id) ) {

            $net           = new IPv4();
            $network       = Network::find()->where(['id' => $network_id])->asArray()->one();
            $credential_id = empty($credential_id) ? $network['credential_id'] : $credential_id;

            if( !$net->subnet($network['network'])->has($ipaddr) ) {
                throw new HttpException(400, Yii::t('network', "IP-address doesn't belong to chosen subnet"));
            }

        }

        $credentials = Credential::find()->where(['id' => $credential_id])->asArray()->one();

        if( empty($credentials) ) {
            throw new HttpException(400, Yii::t('network', 'Unable to find credential data'));
        }

        try {

            $snmp = (new NetSNMP)->init($ipaddr, [$credentials['snmp_read'], $credentials['snmp_set']], $credentials['snmp_version']);
            $mac  = @$snmp->get('1.3.6.1.2.1.2.2.1.6.1');

            if(!empty($mac)) {
                $mac = explode(':', $mac);
                $mac = array_map(function($octet){ return str_pad($octet, 2, '0', STR_PAD_LEFT); }, $mac);
                $mac = join(':', $mac);
            }
            else {
                $mac = '';
            }

            $data = [
                'name'     => $snmp->get('1.3.6.1.2.1.1.5.0'),
                'contact'  => $snmp->get('1.3.6.1.2.1.1.4.0'),
                'descr'    => $snmp->get('1.3.6.1.2.1.1.1.0'),
                'location' => $snmp->get('1.3.6.1.2.1.1.6.0'),
                'mac'      => $mac,
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            throw new HttpException(504, $e->getMessage());
        }

        return json_encode($data);

    }


    /**
     * Run alt interface action via Ajax
     *
     * @return string
     */
    public function actionAjaxRunInterfaceAction()
    {

        $model = new NodeAltInterfaceActions();
        $model->setAttributes($_POST);

        try {

            if( !$model->validate() ) {
                throw new \Exception('Invalid params');
            }

            $result = $model->run();

            if ($result) {
                \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->node_id));
                $response = ['status' => 'success', 'msg' => ''];
            }
            elseif (!$result && $model->action_type == 'setPrimary') {
                $response = [
                    'status'     => 'error',
                    'error_type' => 'wrong_subnet',
                    'msg'        => Yii::t('network', 'IP-address {0} does not belong to chosen subnet. Please choose new subnet from list.', $model->alt_ip)
                ];
            }
            else {
                $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while editing record')];
            }

        } catch (\Exception $e) {
            $response = ['status' => 'error', 'msg' => $e->getMessage()];
        }

        return Json::encode($response);

    }


    /**
     * Load plugin widget in node via Ajax
     *
     * @param  int $node_id
     * @param  string $plugin
     *
     * @return string
     */
    public function actionAjaxLoadWidget($node_id, $plugin)
    {
        try {

            /** Init plugin widget */
            $class  = 'app\\modules\\plugins\\' . strtolower(Inflector::camelize($plugin)) . '\\widgets\\' . Inflector::camelize($plugin) . 'Widget';
            $object = (new \ReflectionClass($class))->newInstance();

            $response = [
                'status' => 'success',
                'data'   => $object::widget(['node_id' => $node_id])
            ];

        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'data'   => $e->getMessage()
            ];
        }

        return Json::encode($response);
    }


    /**
     * Set node prepend location via Ajax
     *
     * @param  int $node_id
     * @param  string $prepend_location
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSetPrependLocation($node_id, $prepend_location)
    {

        $model = $this->findModel($node_id);
        $model->prepend_location = $prepend_location;

        if ($model->validate(['prepend_location']) && $model->save(false)) {
            $response = ['status' => 'success', 'msg' => Yii::t('app', 'Action successfully finished')];
        }
        else {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while processing your request')];
        }

        return Json::encode($response);

    }


    /**
     * Set node credentials via Ajax
     *
     * @param  int $node_id
     * @param  int $credential_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSetNodeCredentials($node_id, $credential_id)
    {

        $model = $this->findModel($node_id);
        $model->credential_id = $credential_id;

        if ($model->validate('credential_id')) {

            if ($model->save()) {
                \Y::flash('success', Yii::t('node', 'Node credentials have been successfully changed'));
            } else {
                \Y::flash('danger', Yii::t('node', 'An error occurred while changing node credentials'));
            }

            $response = ['status' => 'success', 'msg' => ''];

        } else {
            $response = ['status' => 'error', 'msg' => $model->errors['credential_id']];
        }

        return Json::encode($response);

    }


    /**
     * Run node backup via Ajax
     *
     * @param  int $node_id
     * @param  int $device_id
     * @return string
     */
    public function actionAjaxBackupNode($node_id, $device_id)
    {
        try {

            /** Check if task "backup" is assigned */
            Node::checkNodeAssignment($node_id, $device_id);

            $command  = (new NetSsh())->init()->schedulerExec("cbackup backup {$node_id} -json");
            $response = ['status' => 'success', 'msg' => Yii::t('network', 'Node backup successfully started in background. This may take a while.')];

            /** Throw exception if error occurs */
            if (!$command['success']) {
                throw new \Exception($command['exception']);
            }

            /** Show warning if something went wrong */
            if ($command['success'] && !$command['object']) {
                $response = ['status' => 'warning', 'msg' => Yii::t('network', 'Something went wrong. Java response: {0}', $command['message'])];
            }

        } catch (\Exception $e) {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'Error: {0}', $e->getMessage())];
        }

        return Json::encode($response);
    }


    /**
     * Set node protected flag via Ajax
     *
     * @param  int $node_id
     * @param  int $protect_status
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxProtectNode($node_id, $protect_status)
    {
        $model = $this->findModel($node_id);
        $model->protected = intval($protect_status);

        if ($model->validate(['protected']) && $model->save(false)) {
            $response = ['status' => 'success', 'msg' => Yii::t('app', 'Action successfully finished')];
        }
        else {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while processing your request')];
        }

        return Json::encode($response);
    }

    public function actionUpdateNodesDb() {
      $csv = Yii::getAlias('@webroot').'/output.csv';
      $device_data = ['D-link' => ['device_id' => [3, 7, 9, 11, 12, 13, 14, 15, 16, 18, 20, 21, 22, 23, 24, 25, 27, 29, 30, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42], 'credentials_id' => 3],
                      'Huawei' => ['device_id' => [1, 8, 17, 19, 26], 'credentials_id' => 1],
                      'Orion'  => ['device_id' => [4], 'credentials_id' => 4],
                      'SNR'    => ['device_id' => [2], 'credentials_id' => 2],
                      'Cisco'  => ['device_id' => [10], 'credentials_id' => 7]];

      $zabbix_db = [];

      if (($handle = fopen($csv, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000)) !== false) {
          $zabbix_db[] = $row[1];
        }
      }

      $nodes = Node::find()->all();
      $i = 0;

      foreach($nodes as $node) {
        if(!in_array($node->ip, $zabbix_db)) {
          $i++;
          echo $node->ip."<br>";
        }
      }

      echo $i;

      /*if (($handle = fopen($csv, 'r')) !== false) {
          $i = 0; $j = 0;
          echo "<html><body><div>";
          //rows: 0 - hostname, 1 - ip, 2 - device
          while (($row = fgetcsv($handle, 1000)) !== false) {
            $model = Node::findOne(['ip' => $row[1]]);
            if(!empty($model)) {
              if(!in_array($model->device_id, $device_data[$row[2]]['device_id'])) {
                //echo "'".$model->ip."', ";
                $i++;
              }
            } else {
              echo $row[0]." ".$row[1]." ".$row[2]."<br>";
              $j++;
            }
          }
          echo "</div><br>".$i." ".$j;
          fclose($handle);
      }*/
      /*if (($handle = fopen($csv, 'r')) !== false) {
          $i = 0;
          $j = 0;
          echo "<pre>";
          //rows: 0 - hostname, 1 - ip, 2 - device
          while (($row = fgetcsv($handle, 1000)) !== false) {
            $model = Node::findOne(['hostname' => $row[0]]);
            if(!empty($model)) {
              if($model->hostname !== $row[0]) {
                echo $model->hostname." ".$model->ip." ".$row[1]." ".$model->device_id." ".$device_data[$row[2]]['device_id']."\n";
                $i++;
              }
            }
            else $j++;
          }
          echo "\n".$i." ".$j;
          fclose($handle);
      } */

        exit();
    }


    /**
     * Finds the Node model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return Node the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Node::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
