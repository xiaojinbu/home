<?php

namespace common\tools\select2\assets;

use yii\web\AssetBundle;

class Select2Asset extends AssetBundle
{
    public $sourcePath = '@common/tools/select2/assets/select2/';
    
    public $css = [
        'css/select2.min.css',
    ];
    
    public $js = [
        //注意语言包的顺序，一定要在后台引入
        'js/select2.min.js',
        'i18n/zh-CN.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}


