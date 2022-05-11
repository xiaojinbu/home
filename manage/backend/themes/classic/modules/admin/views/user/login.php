<?php

use app\assets\ToastrAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

ToastrAsset::register($this);

$this->title = Yii::t('backend', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
	.f-label {
		padding-top:7px;
		color:#676a6c;
	}
</style>
<div class="signinpanel">
	<div class="row">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3" style="text-align: center">
            <div class="login-text">
                <span>供应商测试</span>
            </div>
			<div class="login-form">
                <div class="login-element-img pull-left">
                    <img src="<?=Yii::getAlias('@web')?>/images/login/element1.png" style="height: 400px">
                </div>
                <div class="login-form2 pull-left">
					<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                    <img class="boss-logo" src="">
					<?= $form->field($model, 'phone', ['inputOptions' => ['placeholder' => Yii::t('common', 'account')]])->textInput(['class' => 'form-control uname'])->label(false) ?>
					<?= $form->field($model, 'password', ['inputOptions' => ['placeholder' => Yii::t('common', 'password')]])->passwordInput(['class' => 'form-control pword m-b'])->label(false) ?>

					<?= Html::submitButton(Yii::t('backend', 'Login'), ['class' => 'login-button', 'name' => 'login-button', 'style' => 'margin-bottom: 1rem']) ?>
                    <br>
					<?php ActiveForm::end(); ?>
                </div>
			</div>

            <div style="clear: both"></div>

		</div>
	</div>

</div>

