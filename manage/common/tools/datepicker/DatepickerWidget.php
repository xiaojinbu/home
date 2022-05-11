<?php

namespace common\tools\datepicker;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use common\tools\datepicker\assets\DatePickerAsset;

/**
 * ```php
 * echo DatepickerWidget::widget([
 *     'clientOptions' => [],
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 * ```php
 * echo DatepickerWidget::widget([
 *     'clientOptions' => [],
 * ]);
 * ```
 *
 * You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'status')->widget(\common\tools\datepicker\DatepickerWidget::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @author jorry
 */
class DatepickerWidget extends \yii\widgets\InputWidget
{
    //配置选项
    public $clientOptions = [];
    
    /**
     * Initializes the widget.
     */
    public function init() {
        
        parent::init();
        
        //生成唯一id，每个widget都有一个id属性
        $this->id = ($this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id);
        
        // 默认配置
        $options = [
            //后期添加
            'language' => Yii::$app->language,
            'autoclose' => true,
            'format' => "yyyy-mm-dd",
        ];
        
        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
    }

    /**
     * Renders the widget.
     */
    public function run() {
        $this->registerClientScript();
        
        $options = ArrayHelper::merge($this->options, ['id' => $this->id, 'class' => 'form-control']);
        
        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $options);
         } else {
            return Html::textInput($this->id, $this->value, $options);
         }
    }
    
    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        DatePickerAsset::register($this->view);
        
        $clientOptions = Json::encode($this->clientOptions);

        $script = "$('#{$this->id}').datepicker({$clientOptions})";

//        echo "<PRE>";print_r($script);die();
        $this->view->registerJs($script);//, yii\web\View::POS_READY
    }
}
