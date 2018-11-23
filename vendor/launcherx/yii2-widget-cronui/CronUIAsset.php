<?php

namespace launcherx\cronui;

use yii\web\AssetBundle;

/**
 * Class CronUIAsset
 *
 * @package launcherx\cronui
 */
class CronUIAsset extends AssetBundle
{

    public $sourcePath = __DIR__ . '/assets';

    public $css = [
        'css/cronui.css',
    ];

    public $js = [
        'js/jquery.cronui.js',
    ];

    public function init()
    {
        parent::init();
        $this->js[] = 'js/i18n/jquery.cronui-' .  \Yii::$app->language . '.js';
    }

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset'
    ];

}
