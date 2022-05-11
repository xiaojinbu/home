<?php

namespace common\tools\ueditor;

use common\tools\ueditor\assets\UEditorAsset;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class UEditorWidget extends InputWidget
{
    //配置选项，参阅UEditor官网文档(定制菜单等)
    public $clientOptions = [];
    
    public $readyEvent = '';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        // 默认配置，这里是常用定制配置项
        $options = [
            'serverUrl' => Url::to(['ueditor']),//上传配置
            'initialFrameWidth' => '100%',
            'initialFrameHeight' => '400',
            'lang' => strtolower(Yii::$app->language),
            
            /*
            'toolbars' => [[
                'fullscreen', 'source', '|', 'undo', 'redo', '|',
                'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                'directionalityltr', 'directionalityrtl', 'indent', '|',
                'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
                'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
                'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'insertframe', 'insertcode', 'pagebreak', 'template', 'background', '|',
                'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
                'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                'print', 'preview', 'searchreplace', 'drafts'
            ]],
            */
            
            'toolbars' => [[
                'fullscreen', 'source', 'preview', '|', 'bold', 'italic', 'underline', 'strikethrough', 'forecolor', 'backcolor', '|',
                'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'blockquote', 'emotion',
                'link', 'removeformat', '|', 'rowspacingtop', 'rowspacingbottom', 'lineheight','indent', 'paragraph', 'fontsize', '|',
                'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol',
                'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'anchor', 'map', 'print', 'drafts', 'simpleupload', 'insertimage', 'insertvideo', 'attachment', 'map']],
        ];
        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
        
        parent::init();
    }

    public function run()
    {
        $this->registerClientScript();
        $options = ArrayHelper::merge($this->options, ['id' => $this->id, 'style'=>';']);
        
        if ($this->hasModel()) {
            return Html::activeTextarea($this->model, $this->attribute, $options);
        } else {
            return Html::textarea($this->id, $this->value, $options);
        }
        
//         $value = '';
//         if ($this->hasModel()) {
//             $value = Html::getAttributeValue($this->model, $this->attribute);
//         } else {
//             $value = $this->value;
//         }
//         //return Html::tag('script', $value, ['id'=>$this->id, 'type'=>'text/plain']);
//         return Html::script($value, ['id'=>$this->id, 'type'=>'text/plain']);
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        $tokenName = Yii::$app->getRequest()->csrfParam;
        $tokenValue = Yii::$app->getRequest()->getCsrfToken();
        
        UEditorAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);
        $script = "var ue = UE.getEditor('" . $this->id . "', " . $clientOptions . ");";
        $script .= "ue.ready(function() {ue.execCommand('serverparam', {\"{$tokenName}\": \"{$tokenValue}\"});});";
        if ($this->readyEvent) {
            $script .= "ue.ready(function(e){{$this->readyEvent}});";
        }
        $this->view->registerJs($script, yii\web\View::POS_READY);//显示时的效果好一些
    }
}