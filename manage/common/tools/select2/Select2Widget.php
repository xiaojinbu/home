<?php

namespace common\tools\select2;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use common\tools\select2\assets\Select2Asset;

/**
 * ```php
 * echo Select2Widget::widget([
 *     'route' => [],
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 * ```php
 * echo Select2Widget::widget([
 *     'route' => [],
 * ]);
 * ```
 *
 * You can also use this widget in an [[yii\widgets\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'status')->widget(\common\tools\select2\Select2Widget::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @author jorry
 */
class Select2Widget extends \yii\widgets\InputWidget
{
    //配置选项
    public $clientOptions = [];
    
    public $route = '';//路由
    
    public $className;//关联表的类
    public $primaryKey = 'id';//默认关联表主键
    public $showField = 'name';//要显示的关联表字段
    
    //默认配置
    protected $_options;
    
    /**
     * Initializes the widget.
     */
    public function init() {
        
        parent::init();
        
        //生成唯一id，每个widget都有一个id属性
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        
        $this->_options = [
            'theme' => 'default',
            'placeholder' => Yii::t('common', 'Select a state'),
            'width' => '100%',
            'allowClear' => true,
            'minimumInputLength' => 0,//最小输入字符
            'allowClear' => true,
            'tags' => false,//是否添加自定义tag
            'tokenSeparators' => [',', ' '],
        ];
        
        $this->clientOptions = ArrayHelper::merge($this->_options, $this->clientOptions);
    }

    /**
     * Renders the widget.
     */
    public function run() {
        $this->registerClientScript();
        
        $options = ArrayHelper::merge(['id' => $this->id, 'class' => 'form-control'], $this->options);
        
        //是否初始化
        $items = [];
        
        if(empty($options['multiple'])) {
            $className = $this->className;
            $query = $className::find();
            if($this->hasModel()) {
                $value = $this->model->{$this->attribute};
                $model = $query->where([$this->primaryKey => $value])->one();
                if($model)
                    $items = [$value => $model->{$this->showField}];
            } else {
                $model = $query->where(['id' => $this->value])->one();
                if($model)
                    $items = [$this->value => $model->{$this->showField}];
            }
        } else {
            $value = $this->model->{$this->attribute};
            $items = is_array($value)?$value:explode(',', $value);
            foreach ($items as $key => $item) {
                $items[$item] = $item;
                unset($items[$key]);
            }
            $this->model->{$this->attribute} = $items;//重
        }
        
        if ($this->hasModel()) {
            return Html::activeDropDownList($this->model, $this->attribute, $items, $options);
         } else {
            return Html::dropDownList($this->name, $this->value, $items, $options);
         }
    }
    
    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        Select2Asset::register($this->view);
        
        $url = Url::to([$this->route]);
        
        $ajax = <<<EOF
        {
            url: '{$url}',
            dataType: 'json',
            //delay: 250,
            data: function (params) {
                return {
                    keyword: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.msg,
                    pagination: {
                        more: (params.page * 20) < data.total_count
                    }
                };
            },
            cache: true
        }
EOF;
        $clientOptions = Json::encode($this->clientOptions);

        $script = <<<EOF
        var config = $clientOptions;
        config.ajax = $ajax;
        $('#{$this->id}').select2(config).on('change', function (e) {
            // nothing
        });
EOF;
        
        $this->view->registerJs($script);//, yii\web\View::POS_READY
    }
}