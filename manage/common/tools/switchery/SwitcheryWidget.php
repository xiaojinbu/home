<?php

namespace common\tools\switchery;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Url;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

use common\tools\switchery\assets\SwitcheryAsset;

/**
 * ```php
 * echo SwitcheryWidget::widget([
 *     'clientOptions' => [],
 *     'route' => '',
 *     'activeValue' => '',
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 * ```php
 * echo SwitcheryWidget::widget([
 *     'clientOptions' => [],
 *     'route' => '',
 *     'activeValue' => '',
 * ]);
 * ```
 *
 * You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'status')->widget(\common\tools\switchery\SwitcheryWidget::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @author jorry
 */
class SwitcheryWidget extends \yii\widgets\InputWidget
{
    //配置选项
    public $clientOptions = [];
    
    public $route;
    
    public $activeValue;//活动值，对应是禁止值
    
    /**
     * Initializes the widget.
     */
    public function init() {
        //参数校验
        if(is_null($this->route) || is_null($this->activeValue)) {
            throw new InvalidParamException('SwitcheryWidget::route、SwitcheryWidget::activeValue必填！');
        }
        if(!$this->hasModel() && (is_null($this->id) || is_null($this->value))) {//name已经检验
            throw new InvalidParamException('SwitcheryWidget::id、SwitcheryWidget::value必填！');
        }
        
        //生成唯一id，每个widget都有一个id属性
        $this->id = ($this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id).'-'.$this->model->id;
        
        // 默认配置
        $options = [
            'color'          => '#64bd63',  //开关打开的颜色
            'secondaryColor' => '#dfdfdf',  //开关关闭的颜色
            'className'      => 'switchery',//开关的名称
            'disabled'       => false,      //开关是否可用
            'disabledOpacity'=> 0.5,        //开关的透明度
            'speed'          => '0.1s',     //开关的动画时间
            'size'           => 'small',
        ];
        
        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
        
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run() {
        $this->registerClientScript();
        
        $options = ArrayHelper::merge($this->options, ['id' => $this->id, 'label' => false]);
        
        if ($this->hasModel()) {
            $options['data-value'] = $this->model->{$this->attribute};//真实值
            //真实值转化为boolean值
            $this->model->{$this->attribute} = ($this->activeValue == $this->model->{$this->attribute});
            return Html::activeCheckbox($this->model, $this->attribute, $options);
         } else {
            $options['data-value'] = $this->value;
            return Html::checkbox($this->id, ($this->activeValue == $this->value), $options);
         }
    }
    
    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        SwitcheryAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);
        
        $url = Url::to([$this->route, 'id' => ($this->hasModel())?$this->model->id:$this->id]);
        
        $script = <<<EOF
        new Switchery(document.querySelector('#$this->id'), $clientOptions);//它仅仅是个皮肤，与下面的jquery操作基本无关！
        $('#$this->id').next().on('click', function() {
            var checkButton = $(this).prev();
            var data = {value: checkButton.attr('data-value')};
            //data[yii.getCsrfParam()] = yii.getCsrfToken();//基于jquery和yii的post不需要提交csrf参数，已经由yii.js自动创建了
            $.ajax({
                url: '{$url}',
                type: 'POST',
                dataType: 'json',
                context: checkButton,
                cache: false,
                data: data,
                success: function(data) {
                    if(data.state) {
                        $(this).attr('data-value', data.msg);
                        //二次重置一下状态
                        $(this).attr("checked", data.state);
                    } else {
                        alert(data.msg);
                    }
                }
            });
        });
EOF;

        $this->view->registerJs($script);//, yii\web\View::POS_READY
    }
}
