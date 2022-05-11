<?php

namespace app\assets;

use yii\web\AssetBundle;

class Echarts3DAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/echarts/';

    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';

    //public $css = [];
    public $js = [
        'js/echarts-new.js',
        'js/echarts-gl.min.js'
//        'js/echarts-gl.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}