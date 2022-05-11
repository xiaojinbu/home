<?php
namespace common\tools\ueditor\assets;

use yii\web\AssetBundle;

class UEditorAsset extends AssetBundle
{
    public $sourcePath = '@common/tools/ueditor/assets/ueditor1.4.3.3/';
    
    public $js = [
        'ueditor.config.js',//核心配置
        'ueditor.all.min.js',//默认中文，且css自动加载
    ];
    
    //public $css = [];
    
    public $depends = ['yii\web\JqueryAsset'];

}
