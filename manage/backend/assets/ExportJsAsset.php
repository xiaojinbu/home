<?php

namespace app\assets;

use yii\web\AssetBundle;

class ExportJsAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/export-js/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/excel-gen.js',
        'js/FileSaver.js',
        'js/jszip.min.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}