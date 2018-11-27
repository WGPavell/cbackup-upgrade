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

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\helpers\FormHelper;
use yii\widgets\Pjax;

/**
 * @var $this        yii\web\View
 * @var $model       app\modules\rbac\models\AuthItem
 * @var $form        yii\bootstrap\ActiveForm
 * @var $item_name   string
 * @var $roles       array
 * @var $permissions array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);

$this->registerJs(
    /** @lang JavaScript */
    "
       /** Select2 init */
       $('.select2').select2({
            minimumResultsForSearch: '-1',
            width: '100%'
       });
    ", \yii\web\View::POS_READY
);

$page_name   = Yii::t('rbac', 'Edit item');
$this->title = Yii::t('app', 'Access rights');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['/user/list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Access rights'), 'url' => ['/rbac/access/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

$this->registerJs(
    /** @lang JavaScript */
    "

       /** Select2 with clear init */
       $('#nodes_denied_form .select2-clear').select2({
           minimumResultsForSearch: -1,
           allowClear: true,
           width : '100%'
       });

       /** Select2 with search */
       $('#nodes_denied_form .select2-search').select2({
           width : '100%'
       });

       /** Select with minimum and clear init */
       $('#nodes_denied_form .select2-min').select2({
           minimumInputLength: 4,
           allowClear: true,
           width: '100%'
       });

       /** Node search form submit and reload gridview */
        $('#nodes_denied_form .node-search-form form').submit(function(e) {
            e.stopImmediatePropagation(); // Prevent double submit
            gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
            $.pjax.reload({container:'#node-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
            return false;
        });

        /** Init JS on document:ready and pjax:end */
        $(document).on('ready pjax:end', function() {

            /** Init iCheck */
            $('.set-node-box').iCheck({
                checkboxClass: 'icheckbox_minimal-green'
            }).on('ifChanged', function (event) {
                $(event.target).trigger('change');
            });

            /** Check/uncheck all nodes on page */
            $('#check_all_box').change(function() {
                if ($('#check_all_box').is(':checked')) {
                    $('.check_node_box').prop('checked', true).iCheck('update');
                } else {
                    $('.check_node_box').prop('checked', false).iCheck('update');
                }
            });

            /** Check/uncheck check all box based on node checked values */
            $('.check_node_box').change(function() {
                var input = $('.check_node_box');
                if(input.length === input.filter(':checked').length){
                    $('#check_all_box').prop('checked', true).iCheck('update');
                } else {
                    $('#check_all_box').prop('checked', false).iCheck('update');
                }
            }).change();

        });

        /** Submit assign form */
        $(document).on('submit', '#assign_form', function (e) {
            e.stopImmediatePropagation(); // Prevent double submit

            var form     = $(this);
            var btn_lock = Ladda.create(document.querySelector('#assign_btn'));

            //noinspection JSUnusedGlobalSymbols
            /** Submit form */
            $.ajax({
                url    : form.attr('action'),
                type   : 'post',
                data   : form.serialize(),
                beforeSend: function() {
                    btn_lock.start();
                },
                success: function (data) {
                    if (isJson(data)) {
                        showStatus(data);
                    } else {
                        toastr.warning(data, '', {timeOut: 0, closeButton: true});
                    }
                    $.pjax.reload({container: '#node-pjax', url: $(location).attr('href'), timeout: 10000});
                },
                error : function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            }).always(function(){
                btn_lock.stop();
            });

            return false;
        });

    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-plus"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'access_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php
                                    echo Html::activeLabel($model, 'name', ['class' => 'control-label']);
                                    echo Html::textInput('', $model->name, ['class' => 'form-control',  'disabled' => true]);
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php
                                    echo Html::activeLabel($model, 'type', ['class' => 'control-label']);
                                    echo Html::textInput('', $item_name, ['class' => 'form-control',  'disabled' => true]);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'description')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'description'),
                                    'style'        => 'resize: vertical'
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <?php if ($model->type == 1):?>
                            <div class="col-md-6">
                                <?php
                                    echo $form->field($model, 'roles')->dropDownList($roles, [
                                        'multiple'         => true,
                                        'class'            => 'select2',
                                        'data-placeholder' => FormHelper::label($model, 'roles'),
                                    ]);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    echo $form->field($model, 'permissions')->dropDownList($permissions, [
                                        'multiple'         => true,
                                        'class'            => 'select2',
                                        'data-placeholder' => FormHelper::label($model, 'permissions')
                                    ]);
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <?php
                                    echo $form->field($model, 'permissions')->dropDownList($permissions, [
                                        'multiple'         => true,
                                        'class'            => 'select2',
                                        'data-placeholder' => FormHelper::label($model, 'permissions')
                                    ]);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/rbac/access'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save changes'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>

            <!-- Запрещенные узлы -->

            <div class="box-header with-border">
                <i class="fa fa-filter"></i><h3 class="box-title"><?= Yii::t('network', 'Filter records')?></h3>
            </div>
            <div class="box-body no-padding" id="nodes_denied_form">
                <?php
                    echo $this->render('_adv_search', [
                        'model'         => $searchModel,
                        'devices_list'  => $devices_list,
                        'networks_list' => $networks_list
                    ]);
                ?>
                <div class="box-inner-separator">
                    <div class="box-header">
                        <i class="fa fa-list"></i><h3 class="box-title"><?= Yii::t('rbac', 'Denied nodes')?></h3>
                    </div>
                    <?php Pjax::begin(['id' => 'node-pjax']); ?>
                        <?php if (empty($data)): ?>
                            <div class="callout callout-info" style="margin: 15px;">
                                <p><?= Yii::t('network', 'Nothing to show here') ?></p>
                            </div>
                        <?php else: ?>
                          <?php $form = ActiveForm::begin(['id' => 'assign_form', 'action' => ['ajax-assign-nodes'], 'enableClientValidation' => false]); ?>
                              <table class="table table-bordered" style="margin-bottom: 0">
                                  <thead>
                                      <tr>
                                          <th width="3%">
                                              <?= Html::checkbox('check_all', false, ['id' => 'check_all_box', 'class' => 'set-node-box']) ?>
                                          </th>
                                          <th width="15%"><?= Yii::t('network', 'Network')?></th>
                                          <th width="30%"><?= Yii::t('network', 'Hostname')?></th>
                                          <th width="15%"><?= Yii::t('network', 'IP address')?></th>
                                          <th width="30%"><?= Yii::t('network', 'Device')?></th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php foreach ($data as $key => $node): ?>
                                          <tr>
                                              <td>
                                                  <?php
                                                      echo Html::hiddenInput("NodeRoles[{$node['id']}][role_name]", $searchModel->role_name);
                                                      echo Html::hiddenInput("NodeRoles[{$node['id']}][set_node]", '0');
                                                      echo Html::checkbox("NodeRoles[{$node['id']}][set_node]", $roleHasNodes[$node['id']],[
                                                         'class' => 'set-node-box check_node_box'
                                                      ]);
                                                  ?>
                                              </td>
                                              <td><?= (!empty($node['network']['network'])) ? $node['network']['network'] : Yii::t('yii', '(not set)') ?></td>
                                              <td><?= (!empty($node['hostname'])) ? $node['hostname'] : Yii::t('yii', '(not set)') ?></td>
                                              <td><?= $node['ip'] ?></td>
                                              <td><?= "{$node['device']['vendor']} - {$node['device']['model']}" ?></td>
                                          </tr>
                                      <?php endforeach; ?>
                                      <tr>
                                          <td colspan="5">
                                              <?php
                                                  echo Html::submitButton(Yii::t('network', 'Assign nodes'), [
                                                      'id'         => 'assign_btn',
                                                      'class'      => 'btn btn-sm btn-primary ladda-button',
                                                      'data-style' => 'zoom-in'
                                                  ]);
                                              ?>
                                          </td>
                                      </tr>
                                  </tbody>
                              </table>
                              <div class="box-footer">
                                  <div class="col-md-3">
                                      <div class="summary">
                                          <?= Yii::t('network', 'Showing <b>{0}</b> of <b>{1}</b>.', [$dataProvider->getCount(), $dataProvider->getTotalCount()]) ?>
                                      </div>
                                  </div>
                                  <div class="box-tools pull-right">
                                      <?php
                                          /** @noinspection PhpUnhandledExceptionInspection */
                                          echo \yii\widgets\LinkPager::widget([
                                              'pagination' => $dataProvider->pagination,
                                              'options' => [
                                                  'class' => 'pagination pagination-sm inline'
                                              ]
                                          ]);
                                      ?>
                                  </div>
                              </div>
                          <?php ActiveForm::end(); ?>
                        <?php endif; ?>
                      <?php Pjax::end(); ?>
                </div>
        </div>
    </div>
</div>
