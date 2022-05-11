<?php

namespace app\assets;

use yii\web\AssetBundle;

class JqueryUiAsset extends AssetBundle
{
    //这个属性是设置不能被web访问资源
    public $sourcePath = '@app/assets/hplus/jquery-ui/';
    
    //这两个则是设置外部资源或者web可访问资源
//     public $basePath = '@webroot';
//     public $baseUrl = '@web';
    
    public $css = [
        'css/jquery-ui.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'js/jquery-ui.js',
		'js/jquery.ui.js',
		
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}