<?php
use yii\bootstrap\Html;

//$custom_data = Json::htmlEncode([
//'aa' => 'bb',
//]);
//$this->registerJs("var _custom_data = {$custom_data};");//js全局数据
$this->registerJs($this->render('_script.js'));
$options = ['data-url' => $url, 'class' => 'dynamic-input', 'label' => false, 'style' => 'width: 100%;'];

if (!is_null($model)) {
    //真实值转化为boolean值
    echo Html::activeTextInput($model, $attribute, $options);
} else {
    echo Html::textInput($name, $value, $options);
}