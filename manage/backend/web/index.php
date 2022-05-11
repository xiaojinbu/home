<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');//开发模式，还有prod、test
//defined('YII_ENABLE_ERROR_HANDLER', false);//调试模式默认开启

//这个仅仅是第三方类库加载器
require(__DIR__ . '/../../../vendor/autoload.php');

//这个是yii专用加载器（yii的类库全是这个来加载），包括预加载和后期加载
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');

//公共和私有启动文件
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');//创建公有的目录别名

$config = yii\helpers\ArrayHelper::merge(
    //公共线上和公共线下配置
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    //私有线上和私有线下配置
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

$app = (new yii\web\Application($config));

// echo '<pre>';
// print_r($app->getComponents());
// exit;
//$app->language = 'en';

$app->run();