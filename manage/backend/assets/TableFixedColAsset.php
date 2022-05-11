<?php

namespace app\assets;

use yii\web\AssetBundle;

class TableFixedColAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/table-fixed/';

    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';

    public $css = [
        'css/fixed_table_rc.css'
    ];
    public $js = [
        'js/fixed_table_rc.js',
        'js/sortable_table.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}