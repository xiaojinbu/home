<?php

namespace common\tools\switchery\assets;

use yii\web\AssetBundle;

class SwitcheryAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@common/tools/switchery/assets/switchery/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    public $css = [
        'css/switchery.min.css',
    ];
    public $js = [
        'js/switchery.min.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}