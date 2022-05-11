<?php

namespace common\tools\datetimepicker\assets;

use yii\web\AssetBundle;

class DateTimePickerAsset extends AssetBundle
{
    public $sourcePath = '@common/tools/datetimepicker/assets/datetimepicker/';
    
    public $css = [
        'css/bootstrap-datetimepicker.min.css',
    ];
    
    public $js = [
        //注意语言包的顺序，一定要在后台引入
        'js/bootstrap-datetimepicker.min.js',
        'js/locales/bootstrap-datetimepicker.zh-CN.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}


