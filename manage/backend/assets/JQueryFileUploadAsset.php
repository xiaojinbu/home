<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Created by PhpStorm.
 * User: zpc
 * Date: 2018/3/28
 * Time: 17:59
 */
class JQueryFileUploadAsset extends AssetBundle
{
    public $sourcePath = '@bower/blueimp-file-upload';
    public $css = [
        'css/jquery.fileupload.css',
    ];
    public $js = [
        // <!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
        "js/vendor/jquery.ui.widget.js",
        // <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
        "js/jquery.iframe-transport.js",
        // <!-- The basic File Upload plugin -->
        "js/jquery.fileupload.js",
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        //'yii\jui\JuiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}