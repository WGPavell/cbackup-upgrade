<?php

namespace launcherx\cronui;

use yii\web\AssetBundle;

/**
 * Class BootstrapSelectAsset
 *
 * @package launcherx\cronui
 */
class BootstrapSelectAsset extends AssetBundle
{

    public $sourcePath = '@vendor/bootstrap-select/bootstrap-select/dist';

    public $css = [
        'css/bootstrap-select.min.css',
    ];

    public $js = [
        'js/bootstrap-select.js',
    ];

    public function init()
    {
        parent::init();
        $this->js[] = 'js/i18n/defaults-' .  str_replace('-', '_', \Yii::$app->language) . '.js';
    }

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

}
