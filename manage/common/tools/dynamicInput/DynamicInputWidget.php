<?php

namespace common\tools\dynamicInput;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Url;

/**
 * ```php
 * echo DynamicInputWidget::widget([
 *     'route' => '',
 *     'model' => $model,
 *     'attribute' => 'order',
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 * ```php
 * echo DynamicInputWidget::widget([
 *     'route' => '',
 *     'id' => '',
 *     'name' => 'order',//直接生成一个input，不进行初始化，$model也不参与
 *     'value' => '',
 * ]);
 * ```
 *
 * You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'status')->widget(\common\tools\dynamicInput\DynamicInputWidget::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @author jorry
 */
class DynamicInputWidget extends \yii\widgets\InputWidget
{
    public $route;
    
    public $activeValue;//活动值，对应是禁止值
    
    public $id;
    
    /**
     * Initializes the widget.
     */
    public function init() {
        //参数校验
        if(is_null($this->route)) {
            throw new InvalidParamException('DynamicInputWidget::route必填！');
        }
        
        if(!$this->hasModel() && (is_null($this->id))) {//name已经检验
            throw new InvalidParamException('DynamicInputWidget::id必填！');
        }
        
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run() {
        $data = [];
        $data['url'] = Url::to([$this->route, 'id' => ($this->hasModel())?$this->model->id:$this->id]);
        $data['activeValue'] = $this->activeValue;
        
        //依赖model
        $data['model'] = $this->model;
        $data['attribute'] = $this->attribute;
        
        //不依赖model
        $data['name'] = $this->name;
        $data['value'] = $this->value;//初始值
        
        return $this->render('dynamic-input', $data);
    }
}
