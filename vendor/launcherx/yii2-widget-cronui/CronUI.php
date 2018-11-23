<?php
/**
 * @author Imants Cernovs <imantscernovs@inbox.lv>
 * @package yii2-widgets
 * @subpackage yii2-cronui
 * @version 1.0.2
 */

namespace launcherx\cronui;

use Yii;
use yii\widgets\InputWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Yii2 extension for generating simple cron string.
 *
 * You can use this widget in an [[yii\bootstrap\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * Usage:
 *
 * <?= $form->field($model, 'item_id')->widget(\launcherx\cronui\CronUI::classname(), [
 *     'options' => [
 *          'class' => 'form-control',
 *      ],
 *      'pluginOptions' => [
 *          'dropDownMultiple' => true,
 *          'dropDownStyled'   => true
 *      ]
 * ]) ?>
 *
 * <?= $form->field($model, 'item_id')->widget(\launcherx\cronui\CronUI::classname(), [
 *     'options' => [
 *          'type'  => 'hidden'
 *      ],
 *      'pluginOptions' => [
 *          'dropDownStyled'   => true
 *      ]
 * ]) ?>
 *
 * @author Imants Cernovs <imantscernovs@inbox.lv>
 * @since 1.0.0
 * @package app\widgets\cronui
 */
class CronUI extends InputWidget
{

    /**
     * @var string
     */
    public $initial = '* * * * *';

    /**
     * @var bool
     */
    public $dropDownMultiple = false;

    /**
     * @var bool
     */
    public $dropDownStyled = false;

    /**
     * @var bool
     */
    public $dropDownStyledFlat = false;

    /**
     * @var string
     */
    public $dropDownSizeClass = 'col-md-2';

    /**
     * @var array
     */
    public $pluginOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();

        /** Set output field readonly */
        if (empty($this->options['readonly'])) {
            $this->options['readonly'] = true;
        }

        $this->renderWidget();
    }


    /**
     * Render widget inputs
     */
    public function renderWidget()
    {
        $this->registerAssets();
        $output  = Html::beginTag('div', ['class' => 'row', 'style' => 'margin-bottom: 15px']);
        $output .= Html::tag('div', '', ['class' => 'cron-ui']);
        $output .= Html::endTag('div');
        $output .= Html::activeTextInput($this->model, $this->attribute, $this->options);

        echo $output;
    }


    /**
     * Register plugin asset
     */
    public function registerAssets()
    {

        $view = $this->getView();

        /** Get dropDownStyled value */
        $styled = ArrayHelper::getValue($this->pluginOptions, 'dropDownStyled', $this->dropDownStyled);

        /** Register Bootstrap Select asset if dropDownStyled set to true */
        if ($styled) {
            BootstrapSelectAsset::register($view);
        }

        /** Register CronUI asset */
        CronUIAsset::register($view);

        /** Set plugin options */
        $options = Json::encode([
            'initial'            => ArrayHelper::getValue($this->pluginOptions, 'initial', $this->initial),
            'dropDownMultiple'   => ArrayHelper::getValue($this->pluginOptions, 'dropDownMultiple', $this->dropDownMultiple),
            'dropDownStyled'     => $styled,
            'dropDownStyledFlat' => ArrayHelper::getValue($this->pluginOptions, 'dropDownStyledFlat', $this->dropDownStyledFlat),
            'dropDownSizeClass'  => ArrayHelper::getValue($this->pluginOptions, 'dropDownSizeClass', $this->dropDownSizeClass),
            'resultOutputId'     => $this->options['id'],
            'lang'               => Yii::$app->language
        ]);

        /** Get attribute value */
        $value = Html::getAttributeValue($this->model, $this->attribute);
        $value = isset($value) ? $value : $this->initial;

        $view->registerJs(
            /** @lang JavaScript */
            "
                /** Init cronui plugin */
                var c = $('.cron-ui').cronui($.parseJSON('{$options}'));
                
                /** Set value */
                c.cronui('setValue', '{$value}');
            "
        );

    }

}
