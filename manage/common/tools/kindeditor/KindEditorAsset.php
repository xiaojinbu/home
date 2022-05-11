<?php

namespace common\tools\kindeditor;

class KindEditorAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@common/tools/kindeditor/assets/';
    
    public $js=[
        'kindeditor-min.js',
        'lang/zh_CN.js',
    ];

    public $css=[
        'themes/default/default.css'
    ];

//     public $jsOptions=[
//         'charset'=>'utf8',
//     ];
}