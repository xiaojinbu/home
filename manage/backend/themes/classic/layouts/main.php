<?php

use yii\helpers\Html;

use app\widgets\Alert;
use yii\base\Widget;
use yii\web\YiiAsset;
use yii\bootstrap\BootstrapPluginAsset;
use app\assets\BootstrapHplusAppThemeAsset;
use app\assets\WebAsset;
use app\assets\CookieAsset;
use common\models\manage\MessengerRole;

/* @var $this \yii\web\View */
/* @var $content string */

YiiAsset::register($this);
BootstrapPluginAsset::register($this);
CookieAsset::register($this);
BootstrapHplusAppThemeAsset::register($this);
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
        <title><?=Yii::$app->name?></title>
      
        <?php $this->head() ?>
    </head>
    <body class="gray-bg sunsult-app-skin">
    <?php $this->beginBody() ?>
    
    <?php 
    echo Alert::widget();
    ?>
    
    <?= $content ?>
    <?php $this->endBody(); ?>
    </body>
</html>
<?php $this->endPage(); ?>