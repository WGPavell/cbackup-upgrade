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
 * @var $model       app\models\Node
 * @var $form        yii\bootstrap\ActiveForm
 * @var $networks    array
 * @var $credentials array
 * @var $devices     array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action      = $this->context->action->id;
$page_name   = ($action == 'add') ? Yii::t('node', 'Add node') : Yii::t('node', 'Edit node');
$this->title = Yii::t('node', 'Nodes');

if ($action == 'add') {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes'), 'url' => ['/node']];
    $this->params['breadcrumbs'][] = ['label' => $page_name];
} else {
    $node_name = empty($model->hostname) ? $model->ip : $model->hostname;
    $this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes'), 'url' => ['/node']];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Node') . ' ' . $node_name, 'url' => ['/node/view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = ['label' => $page_name];
}

$this->registerJs(/** @lang JavaScript */"
    var ajaxUrl  = '".\yii\helpers\Url::to(['/node/inquire'])."';
");


/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 */
        $('.select2').select2({
            width: '100%'
        });

        /** Init select2 with clear */
        $('.select2-clear').select2({
            allowClear: true,
            width: '100%'
        });

        $('#snmp_inquire').click(function() {

            var btn = $(this);
            btn.button('loading');

            //noinspection JSUnresolvedVariable
            $.ajax({
                url:      ajaxUrl,
                method:   'post',
                cache:    false,
                data:     {ip: $('#node-ip').val(), cid: $('#node-credential_id').val(), nid: $('#node-network_id').val()},
                dataType: 'json'
            }).done(function(data) {
                //noinspection JSUnresolvedVariable
                $('#node-contact').val(data.contact).parent().addClass('has-success');
                //noinspection JSUnresolvedVariable
                $('#node-sys_description').val(data.descr).parent().addClass('has-success');
                $('#node-hostname').val(data.name).parent().addClass('has-success');
                $('#node-location').val(data.location).parent().addClass('has-success');
                $('#node-mac').val(data.mac).parent().addClass('has-success');
            }).fail(function(data, textStatus, xhr) {
                toastr.error(data.responseText, '', {timeOut: 5000, progressBar: true, closeButton: true});
            }).always(function() {
                btn.button('reset');
            });

        });
    "
);

$this->registerJs(
    /** @lang JavaScript */
    "

       /** Select2 with clear init */
       $('#denied_roles_form .select2-clear').select2({
           minimumResultsForSearch: -1,
           allowClear: true,
           width : '100%'
       });

       /** Select2 with search */
       $('#denied_roles_form .select2-search').select2({
           width : '100%'
       });

       /** Select with minimum and clear init */
       $('#denied_roles_form .select2-min').select2({
           minimumInputLength: 4,
           allowClear: true,
           width: '100%'
       });

       /** Node search form submit and reload gridview */
        $('.role-search-form form').submit(function(e) {
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
    <div class="col-md-8">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'node_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'ip')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'ip')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'network_id')->dropDownList($networks,[
                                    'prompt'           => '',
                                    'class'            => 'select2-clear',
                                    'data-placeholder' => Yii::t('network', 'Choose network')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'credential_id')->dropDownList($credentials,[
                                    'prompt'           => '',
                                    'class'            => 'select2-clear',
                                    'data-placeholder' => Yii::t('network', 'Choose credential')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'device_id')->dropDownList($devices,[
                                    'prompt'           => '',
                                    'class'            => 'select2',
                                    'data-placeholder' => Yii::t('network', 'Choose device')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'mac')->textInput([
                                    'class'        => 'form-control',
                                    'value'        => (isset($model->mac)) ? join(':', str_split($model->mac, 2)) : '',
                                    'placeholder'  => FormHelper::label($model, 'mac')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'serial')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'serial')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'hostname')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'hostname')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'location')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'location')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'contact')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'contact')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'sys_description')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'description')
                                ]);
                            ?>
                        </div>
                    </div>

                </div>
                <div class="box-footer text-right">
                    <?php
                        echo Html::a(Yii::t('app', 'Cancel'), $model->isNewRecord ? ['/node/list'] : ['/node/view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-default pull-left'
                        ]);
                        echo Html::button(Yii::t('app', 'SNMP inquire'), [
                            'class' => 'btn btn-sm btn-default',
                            'id'    => 'snmp_inquire'
                        ]).' ';
                        echo Html::submitButton(Yii::t('app', 'Save'), [
                            'class' => 'btn btn-sm btn-primary'
                        ]);
                    ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Information') ?></h3>
            </div>
            <div class="box-body text-justify">
                <p><?= Yii::t('app', 'Enter node IP-address and choose on of two options to assign proper credentials: either "Network" or "Credentials" from corresponding dropdowns. If both dropdowns will be selected, then credentials will have the upper hand.') ?></p>
                <p><?= Yii::t('app', 'Also you can check if you entered valid data and right device by pressing "SNMP Inquire" button. This will trigger SNMP polling for entered IP using assigned credentials and will try to fill up some form fields.') ?></p>
            </div>
        </div>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?= Yii::t('app', 'Shortcuts') ?></h3>
            </div>
            <table class="table table-hover">
                <tr><td><?= Html::a(Yii::t('network', 'Add device'), ['/network/device/add'], ['target' => '_blank']) ?></td></tr>
                <tr><td><?= Html::a(Yii::t('network', 'Add subnet'), ['/network/subnet/add'], ['target' => '_blank']) ?></td></tr>
                <tr><td><?= Html::a(Yii::t('network', 'Add credential'), ['/network/credential/add'], ['target' => '_blank']) ?></td></tr>
            </table>
        </div>
    </div>
    <?php if(isset($searchModel)): ?>
    <?php
        echo $this->render('_denied_roles_form', [
            'model'               => $model,
            'searchModel'         => $searchModel,
            'data'                => $data,
            'dataProvider'        => $dataProvider,
            ]);
        ?>
    <?php endif; ?>
</div>
