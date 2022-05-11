<?php

namespace app\assets;

use yii\web\AssetBundle;

//这个js库，在jquery中已经自带了

class ValidationAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/validation/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    //public $css = [];
    public $js = [
        'js/jquery.validate.min.js',
        'js/localization/messages_zh.js',
    ];
    
    //public $depends = ['yii\web\JqueryAsset',];
}