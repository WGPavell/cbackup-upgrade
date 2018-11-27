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
 * @var $this          yii\web\View
 * @var $model         app\models\search\CustomNodeSearch
 * @var $form          yii\widgets\ActiveForm
 */
?>

<div class="col-md-12">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-lock"></i> <?=Yii::t('network', 'Denied for roles') ?>
            </h3>
        </div>
        <div class="role-search-form">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        $form = ActiveForm::begin(['action' => ['edit'], 'method' => 'get', 'enableClientValidation' => false]);
                        echo Html::hiddenInput('id', $model->id)
                    ?>
                    <div class="search-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?php
                                    echo $form->field($searchModel, 'name')->textInput([
                                        'class'       => 'form-control',
                                        'placeholder' => FormHelper::label($searchModel, 'name')
                                    ]);
                                ?>
                            </div>
                            <div class="col-md-2">
                                <?php
                                    echo $form->field($searchModel, 'page_size')->dropDownList(\Y::param('page_size'), ['class' => 'select2']);
                                ?>
                            </div>
                            <div class="col-md-2">
                                <div style="padding-top: 30px">
                                    <?= Html::submitButton(Yii::t('app', 'Search'), ['id' => 'spin_btn', 'class' => 'btn bg-light-blue ladda-button', 'data-style' => 'zoom-in']) ?>
                                    <?= Html::a(Yii::t('app', 'Reset'), ['edit', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="box-inner-separator" id="denied_roles_form">
          <?php Pjax::begin(['id' => 'node-pjax']); ?>
              <?php if (empty($data)): ?>
                  <div class="callout callout-info" style="margin: 15px;">
                      <p><?= Yii::t('network', 'Nothing to show here') ?></p>
                  </div>
              <?php else: ?>
                <?php $form = ActiveForm::begin(['id' => 'assign_form', 'action' => ['ajax-assign-roles'], 'enableClientValidation' => false]); ?>
                    <table class="table table-bordered" style="margin-bottom: 0">
                        <thead>
                            <tr>
                                <th width="3%">
                                    <?= Html::checkbox('check_all', false, ['id' => 'check_all_box', 'class' => 'set-node-box']) ?>
                                </th>
                                <th width="90%"><?= Yii::t('rbac', 'Role')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $key => $role): ?>
                                <tr>
                                    <td>
                                        <?php
                                            echo Html::hiddenInput("Role[{$role['name']}][node_id]", $searchModel->node_id);
                                            echo Html::hiddenInput("Role[{$role['name']}][set_role]", '0');
                                            echo Html::checkbox("Role[{$role['name']}][set_role]", $role['role_has_node'],[
                                               'class' => 'set-node-box check_node_box'
                                            ]);
                                        ?>
                                    </td>
                                    <td><?= (!empty($role['name'])) ? $role['name'] : Yii::t('yii', '(not set)') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5">
                                    <?php
                                        echo Html::submitButton(Yii::t('rbac', 'Assign roles'), [
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
