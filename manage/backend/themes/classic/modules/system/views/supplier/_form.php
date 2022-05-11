<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\system\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>

<p>
    <?= Html::a('<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i> '.'返回', ['index'], ['class' => 'btn btn-default btn-rounded return btn-sm']) ?>
</p>

<div class="supplier-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}\n{error}",
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-4',
                'wrapper' => 'col-sm-7',
                'error' => 'col-sm-3',
                'hint' => '',
            ],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="hr-line-dashed"></div>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <div class="hr-line-dashed"></div>

    <?= $form->field($model, 't_status')->dropDownList([ 'ok' => 'Ok', 'hold' => 'Hold', ], ['prompt' => '']) ?>

    <div class="hr-line-dashed"></div>

    <div class="form-group">
        <div class="col-sm-4 col-sm-offset-2">
            <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-white']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
