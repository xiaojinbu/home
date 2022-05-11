<?php

namespace app\assets;

use yii\web\AssetBundle;

class BsDatePickerAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/bs-datepicker/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    public $js = [
        'moment.min.js',
        'daterangepicker.js',
    ];
    
    public $css = [
        'daterangepicker.css',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}