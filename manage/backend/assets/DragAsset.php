<?php

namespace app\assets;

use yii\web\AssetBundle;

class DragAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/drag/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    public $js = [
        //'js/Tdrag.js',
        'js/jquery.dad.min.js',
    ];
    
    public $css = [
        'css/jquery.dad.css',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}