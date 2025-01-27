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

namespace app\modules\rbac\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\modules\rbac\models\AuthItem;
use app\modules\rbac\models\AuthItemSearch;
use app\models\search\CustomNodeSearch;
use app\models\Device;
use app\models\Network;
use app\models\AuthItemNode;


/**
 * @package app\modules\rbac\controllers
 */
class AccessController extends Controller
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
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'ajax-delete' => ['post']
                ],
            ],
        ];
    }

    /**
     * List of access items
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new AuthItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'item_types'   => $searchModel->getTypeDefinition()
        ]);
    }


    /**
     * Add new auth item
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new AuthItem();

        if (isset($_POST['AuthItem'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('rbac', 'New authorization item was successfully added'));
                    } else {
                        \Y::flash('danger', Yii::t('rbac', 'An error occurred while adding authorization item'));
                    }

                    return $this->redirect(['/rbac/access/list']);

                }
            }
        }

        return $this->render('_add_form', [
            'model'      => $model,
            'item_types' => $model->getTypeDefinition()
        ]);

    }


    /**
     * Edit auth item
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\db\Exception
     */
    public function actionEdit($name)
    {

        $model = $this->findModel($name);

        $deniedNodes  = new CustomNodeSearch();
        $dataProvider = $deniedNodes->searchByRole(Yii::$app->request->queryParams);
        $deniedNodes->role_name = $name;
        $data = $dataProvider->getModels();

        $roleHasNodes = [];

        foreach ($data as $key => $entry) {
            $denied_exists = AuthItemNode::find()->where(['node_id' => $entry['id'], 'auth_item_name' => Yii::$app->request->queryParams['name']])->exists();
            $roleHasNodes[$entry->id] = ($denied_exists) ? true : false;
        }

        if (isset($_POST['AuthItem'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();

                    try {

                        $auth_items     = ArrayHelper::merge($model->roles, $model->permissions);
                        $old_auth_items = ArrayHelper::merge($model->_children['roles'], $model->_children['permissions']);

                        /** Update auth item children */
                        if ($old_auth_items != $auth_items) {
                            $model->updateElement($auth_items);
                        }

                        if ($model->save()) {
                            $transaction->commit();
                            \Y::flash('success', Yii::t('rbac', 'Authorization item {0} was successfully edited', strtoupper($model->name)));
                        }
                        else {
                            $transaction->rollBack();
                            \Y::flash('danger', Yii::t('rbac', 'An error occurred while editing authorization item {0}', strtoupper($model->name)));
                        }

                        return $this->redirect(['/rbac/access/list']);

                    }
                    catch (\Exception $e) {
                        $transaction->rollBack();
                        \Y::flash('warning', $e->getMessage());
                        $this->refresh();
                        Yii::$app->end();
                    }

                }

            }

        }

        return $this->render('_edit_form', [
            'model'         => $model,
            'item_name'     => $model->getAuthItemReadable(),
            'roles'         => $model->getAllRoles(),
            'permissions'   => $model->getAllPermissions(),
            'data'          => $data,
            'dataProvider'  => $dataProvider,
            'searchModel'   => $deniedNodes,
            'devices_list'  => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor'),
            'networks_list' => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'name'          => $name,
            'roleHasNodes'  => $roleHasNodes,
        ]);

    }

    /**
     * Assign nodes to role via Ajax
     *
     * @return string
     */
    public function actionAjaxAssignNodes()
    {

        $msg_status = 'error';
        $msg_text   = Yii::t('app', 'An error occurred while processing your request');

        $save_status   = [];
        $delete_status = [];

        if (Yii::$app->request->isAjax && isset($_POST['NodeRoles'])) {

            $_post = $_POST['NodeRoles'];

            foreach ($_post as $node_id => $data) {

                /** Find record in AuthItemNode */
                $record = AuthItemNode::find()->where(['node_id' => $node_id, 'auth_item_name' => $data['role_name']]);

                /** Add new record if it doesn't exists and set_node is set 1 */
                if (!$record->exists() && $data['set_node'] == '1') {
                    $model                 = new AuthItemNode();
                    $model->node_id        = $node_id;
                    $model->auth_item_name = $data['role_name'];
                    $save_status[]         = ($model->save()) ? true : false;
                }
                else {
                    $save_status[] = true;
                }

                /** Remove record if it exists and set node is set to 0 */
                if ($record->exists() && $data['set_node'] == '0') {
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
     * Delete auth item via GridView
     *
     * @param  string $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($name)
    {

        $name  = urlencode($name);

        /**
         * Protection from system authorization item deleting
         * Permanent authorization items can be set in params system.rights
         */
        if (in_array(mb_strtolower($name), array_map('mb_strtolower', \Y::param('system.rights')))) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('rbac', 'You cannot delete system authorization item <b>{0}</b>!', strtoupper($name))
            ]);
        }

        $model = $this->findModel($name);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('rbac', 'Authorization item <b>{0}</b> was successfully deleted', strtoupper($model->name))
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('rbac', 'An error occurred while deleting authorization item <b>{0}</b>', strtoupper($model->name))
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
