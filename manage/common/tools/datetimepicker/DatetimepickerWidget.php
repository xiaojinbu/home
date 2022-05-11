<?php

namespace common\tools\datetimepicker;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use common\tools\datetimepicker\assets\DateTimePickerAsset;

/**
 * ```php
 * echo DatetimepickerWidget::widget([
 *     'clientOptions' => [],
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 * ```php
 * echo DatetimepickerWidget::widget([
 *     'clientOptions' => [],
 * ]);
 * ```
 *
 * You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'status')->widget(\common\tools\datetimepicker\DatetimepickerWidget::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @author jorry
 */
class DatetimepickerWidget extends \yii\widgets\InputWidget
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
            'language' => Yii::$app->language,
            'format' => 'yyyy-mm-dd hh:ii',// P
            'showMeridian' => 1,//开启上下午选择项
            'todayBtn' => 1,//当天按钮
            'todayHighlight' => 1,
            'autoclose' => 1,//选中后，自动关闭
            'pickerPosition' => "top-left",//面板位置
//             'startDate' => '',//开始日期
//             'endDate' => '',//结束日期
//             'startView' => '',//首次显示的是哪个面板：年/月/日/上午下午/时/分
//             'initialDate' => '',//初始化默认日期
//             'weekStart' => 1,//显示多少周
//             'forceParse' => 0,//强制转化
//             'linkField' => 'mirror_field',//关联表单
//             'linkFormat' => 'yyyy-mm-dd hh:ii',//关联表单的显示格式
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
        DateTimePickerAsset::register($this->view);
        
        $clientOptions = Json::encode($this->clientOptions);
        
        $script = "$('#{$this->id}').datetimepicker({$clientOptions})";

        $this->view->registerJs($script);//, yii\web\View::POS_READY
    }
}
