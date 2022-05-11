<?php

namespace common\tools\areapicker\assets;
use yii\web\AssetBundle;

class AreaPickerAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@common/tools/areapicker/assets/areapicker/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
//     public $css = [
//         'css/areapicker.min.css',
//     ];
    public $js = [
        'js/cascade.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}