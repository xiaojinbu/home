<?php

namespace common\tools\webuploader\assets;

use yii\web\AssetBundle;

class WebuploaderAsset extends AssetBundle
{
    public $sourcePath = '@common/tools/webuploader/assets/webuploader/';
    
    public $js = [
        'dist/webuploader.min.js',
    ];
    
    public $css = [
        'dist/webuploader.css',
        'demo/style.css',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
