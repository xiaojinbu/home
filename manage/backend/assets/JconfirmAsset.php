<?php

namespace app\assets;

use yii\web\AssetBundle;

class JconfirmAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/jconfirm/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    public $css = [
        'css/jquery-confirm.min.css',
    ];
    public $js = [
        'js/jquery-confirm.min.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}