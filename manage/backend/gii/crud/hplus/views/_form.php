<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<p>
    <?= "<?= " ?>Html::a('<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i> '.<?= $generator->generateString('返回') ?>, ['index'], ['class' => 'btn btn-default btn-rounded return btn-sm']) ?>
</p>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
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

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
        echo "    <div class=\"hr-line-dashed\"></div>\n\n";
    }
} ?>
    <div class="form-group">
        <div class="col-sm-4 col-sm-offset-2">
            <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('创建') ?> : <?= $generator->generateString('更新') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= "<?= " ?>Html::resetButton(<?= $generator->generateString('重置') ?>, ['class' => 'btn btn-white']) ?>
        </div>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
