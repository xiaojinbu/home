<?php
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user app\modules\admin\models\User */

$resetLink = Url::to(['merchant/common/reset-password','token'=>$password_reset_token], true);
?>
Hello <?= $name ?>,

Follow the link below to reset your password:

<?= $resetLink ?>
