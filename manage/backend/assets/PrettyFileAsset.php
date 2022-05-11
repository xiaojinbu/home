<?php

namespace app\assets;

use yii\web\AssetBundle;

class PrettyFileAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/hplus/pretty-file/';
    public $css = [];
    public $js = [
        //注意语言包的顺序，一定要在后台引入
        'js/bootstrap-prettyfile.min.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}


