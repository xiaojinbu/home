<?php

namespace common\tools\areapicker;

use common\tools\areapicker\assets\AreaPickerAsset;
use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;


/**
 * ```php
 * 模型模式
 * $form->field($model, 'address')->widget(AreaPickerWidget::className(), [
 *     'provanceAttribute' => 'provance',
 *     'cityAttribute' => 'city',
 *     'areaAttribute' => 'area',
 *     'streetAttribute' => 'street',
 *     
 *     'clientOptions' => [
 *          'openStreet' => false,//开通街道
 *          'openAddress' => true,//开启地址详情
 *     ],
 * ])
 * ```
 *
 * ```php
 * 普通模式
 * AreaPickerWidget::widget([
 *     'name' => 'address',
 *     'provanceName' => 'provance',
 *     'cityName' => 'city',
 *     'areaName' => 'area',
 *     'streetName' => 'street',
 *     
 *     'value' => '科技中路52栋',
 *     'provanceValue' => '广东',
 *     'cityValue' => '深圳',
 *     'areaValue' => '福田',
 *     'streetValue' => '',
 *     
 *     'clientOptions' => [
 *         'openStreet' => false,//开通街道
 *         'openAddress' => true,//开启地址详情
 *     ],
 * ])
 * ```
 * 
 * @author jorry
 */
class AreaPickerWidget extends \yii\widgets\InputWidget
{
    //模型
    //     public $model;
    //     public $attribute;
    //普通
    //     public $name;
    //     public $value;
    //input的属性
    //     public $options = [];
    //js插件配置
    //     public $clientOptions = [];
    
    //默认属性
    public $provanceAttribute = 'provance';
    public $cityAttribute = 'city';
    public $areaAttribute = 'area';
    public $streetAttribute = 'street';
    
    //配置选项
    public $clientOptions = [];
    
    private $provanceId;
    private $cityId;
    private $areaId;
    private $streetId;
    
    //普通模式调用参数
    public $provanceName;
    public $cityName;
    public $areaName;
    public $streetName;
    
    public $provanceValue;
    public $cityValue;
    public $areaValue;
    public $streetValue;
    
    /**
     * Initializes the widget.
     */
    public function init() {
        $this->id = ($this->hasModel() ? Html::getInputId($this->model, $this->attribute) : 'address');//address
        
        $this->provanceId = ($this->hasModel() ? Html::getInputId($this->model, $this->provanceAttribute) : $this->provanceAttribute);
        $this->cityId = ($this->hasModel() ? Html::getInputId($this->model, $this->cityAttribute) : $this->cityAttribute);
        $this->areaId = ($this->hasModel() ? Html::getInputId($this->model, $this->areaAttribute) : $this->areaAttribute);
        $this->streetId = ($this->hasModel() ? Html::getInputId($this->model, $this->streetAttribute) : $this->streetAttribute);
        
        //名
        $this->provanceName = ($this->hasModel() ? Html::getInputName($this->model, $this->provanceAttribute) : $this->provanceName);
        $this->cityName = ($this->hasModel() ? Html::getInputName($this->model, $this->cityAttribute) : $this->cityName);
        $this->areaName = ($this->hasModel() ? Html::getInputName($this->model, $this->areaAttribute) : $this->areaName);
        $this->streetName = ($this->hasModel() ? Html::getInputName($this->model, $this->streetAttribute) : $this->streetName);
        
        //值
        $this->provanceValue = ($this->hasModel() ? Html::getAttributeValue($this->model, $this->provanceAttribute) : $this->provanceValue);
        $this->cityValue = ($this->hasModel() ? Html::getAttributeValue($this->model, $this->cityAttribute) : $this->cityValue);
        $this->areaValue = ($this->hasModel() ? Html::getAttributeValue($this->model, $this->areaAttribute) : $this->areaValue);
        $this->streetValue = ($this->hasModel() ? Html::getAttributeValue($this->model, $this->streetAttribute) : $this->streetValue);

        // 默认配置
        $options = [
            'provanceId' => $this->provanceId,
            'cityId' => $this->cityId,
            'areaId' => $this->areaId,
            'streetId' => $this->streetId,
            
            //'extid' => '',//级联后缀
            'openStreet' => false,//是否打开街道
            'openAddress' => true,//开启地址详情
            'xmlfileBase' => Yii::$app->getAssetManager()->getPublishedUrl((new AreaPickerAsset())->sourcePath).'/xml/',
        ];
        
        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
        
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run() {
        $this->registerClientScript();
        
        $str = '';
        
        //省
        $options = ArrayHelper::merge($this->options, ['id' => $this->provanceId, 'class' => 'form-control', 'style' => 'display: inline;width: 130px;margin-right: 10px;', 'onchange' => 'selectCity();']);
        if ($this->hasModel()) {
            $str .= Html::activeDropDownList($this->model, $this->provanceAttribute, ['' => '省/直辖市'], $options);
        } else {
            $str .= Html::dropDownList($this->provanceName, $this->provanceValue, ['' => '省/直辖市'], $options);
        }
        
        //市
        $options = ArrayHelper::merge($this->options, ['id' => $this->cityId, 'class' => 'form-control', 'style' => 'display: inline;width: 130px;margin-right: 10px;', 'onchange' => 'selectcounty();']);
        if ($this->hasModel()) {
            $str .= Html::activeDropDownList($this->model, $this->cityAttribute, ['' => '请选择'], $options);
        } else {
            $str .= Html::dropDownList($this->cityName, $this->cityValue, ['' => '请选择'], $options);
        }
        
        //区
        $options = ArrayHelper::merge($this->options, ['id' => $this->areaId, 'class' => 'form-control', 'style' => 'display: inline;width: 130px;margin-right: 10px;', 'onchange' => 'selectstreet();']);
        if ($this->hasModel()) {
            $str .= Html::activeDropDownList($this->model, $this->areaAttribute, ['' => '请选择'], $options);
        } else {
            $str .= Html::dropDownList($this->areaName, $this->areaValue, ['' => '请选择'], $options);
        }
        
        //街道
        if($this->clientOptions['openStreet']) {
            $options = ArrayHelper::merge($this->options, ['id' => $this->streetId, 'class' => 'form-control', 'style' => 'display: inline;width: 130px;margin-right: 10px;']);
            if ($this->hasModel()) {
                $str .= Html::activeDropDownList($this->model, $this->streetAttribute, ['' => '请选择'], $options);
            } else {
                $str .= Html::dropDownList($this->streetName, $this->streetValue, ['' => '请选择'], $options);
            }
        }
        
        //详情
        if($this->clientOptions['openAddress']) {
            $str .= '<div style="margin-top: 10px;"></div>';
            $options = ArrayHelper::merge($this->options, ['id' => $this->id, 'class' => 'form-control']);
            if ($this->hasModel()) {
                $str .= Html::activeTextInput($this->model, $this->attribute, $options);
            } else {
                $str .= Html::textInput($this->name, $this->value, $options);
            }
        }
        
        return $str;
    }
    
    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        AreapickerAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);
        
        $script = <<<EOF
            cascdeInit($clientOptions, "$this->provanceValue","$this->cityValue","$this->areaValue","$this->streetValue", "time()");
EOF;
        
        $this->view->registerJs($script);
    }
}
