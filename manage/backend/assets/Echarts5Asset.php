<?php

namespace app\assets;

use yii\web\AssetBundle;

class Echarts5Asset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/echarts/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    //public $css = [];
    public $js = [
        'js/5.0.2/echarts.min.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}