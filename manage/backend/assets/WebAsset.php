<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 * 引入hplus主题
 */
class WebAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/site.css'
    ];
    
    public $js = [
        'js/site.js'
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
