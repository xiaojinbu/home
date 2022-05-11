<?php

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapHplusAppThemeAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/base/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    public $css = [
        'css/font-awesome.min.css',
        'css/animate.css',
        'css/style.css',
    ];
    
    public $js = [];
    
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}