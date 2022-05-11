<?php

use yii\helpers\Html;

use app\widgets\Alert;
use yii\base\Widget;
use yii\web\YiiAsset;
use yii\bootstrap\BootstrapPluginAsset;
use app\assets\BootstrapHplusAppThemeAsset;
use app\assets\WebAsset;
use app\assets\CookieAsset;
use app\assets\Login2Asset;
//use app\assets\LoginAsset;

/* @var $this \yii\web\View */
/* @var $content string */

YiiAsset::register($this);
BootstrapPluginAsset::register($this);
CookieAsset::register($this);
BootstrapHplusAppThemeAsset::register($this);
//LoginAsset::register($this);
Login2Asset::register($this);
WebAsset::register($this);

$baseUrl = Yii::getAlias('@web');
// $this->registerJs("
//         $(document).ready(function() {
//             //
//         })
// ");
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title.' - '.Yii::t('backend', Yii::$app->name))?></title>
        <link type="image/x-icon" href="./favicon.ico" rel="shortcut icon">
        <!--[if lt IE 9]>
        <meta http-equiv="refresh" content="0;url=/ie.html" />
        <![endif]-->
        <?php $this->head() ?>
    </head>
    <body class="signin sunsult-login-skin animated">
    <?php $this->beginBody() ?>
    
    <?php 
    // 持久信息层
    echo Alert::widget();
    ?>
    
    <?= $content ?>
    <?php $this->endBody(); ?>
    </body>
</html>
<?php $this->endPage(); ?>