<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\system\SupplierSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supplier-search advanced-search-box" style="display: none;">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-4 col-xs-6">
            <?= $form->field($model, 'id') ?>

            <?= $form->field($model, 'name') ?>

            <?= $form->field($model, 'code') ?>

            <?= $form->field($model, 't_status') ?>

        </div>
    </div>

    <div class="form-group pull-right m-t-n-xs">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default btn-sm']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
