<?php

namespace common\tools\datepicker\assets;

use yii\web\AssetBundle;

class DatePickerAsset extends AssetBundle
{
    public $sourcePath = '@common/tools/datepicker/assets/datepicker/';
    
    public $css = [
        'css/datepicker3.css',
    ];
    
    public $js = [
        //注意语言包的顺序，一定要在后台引入
        'js/bootstrap-datepicker.js',
        'locales/bootstrap-datepicker.zh-CN.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}