<?php

namespace common\tools\kindeditor;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use common\tools\kindeditor\KindEditorAsset;

class KindEditorWidget extends \yii\widgets\InputWidget
{
    //配置选项，参阅KindEditor官网文档(定制菜单等)
    public $clientOptions = [];
    
    /*
     * 定义编辑器的类型，
     * 默认为textEditor;
     * uploadButton：自定义上传按钮
     * dialog:弹窗
     * colorpicker:取色器
     * file-manager浏览服务器
     * image-dialog 上传图片
     * multiImageDialog批量上传图片
     * fileDialog 文件上传
     */
    public $editorType;
    
    //默认配置
    protected $_options;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        $this->_options = [
            'fileManagerJson' => Url::to(['kindeditor', 'action' => 'fileManagerJson']),
            'uploadJson' => Url::to(['kindeditor', 'action' => 'uploadJson']),
            'width' => '100%',
            'height' => '400',
            'items' => [
                'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
                'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                'superscript', '/',
                
                'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', 'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
                'flash', 'media', 'insertfile', '/',
                
                'table', 'hr', 'emoticons', 'baidumap', 'pagebreak', 'anchor', 'link', 'unlink', '|', 'about',
            ],
            //kindeditor支持一下语言：en,zh_CN,zh_TW,ko,ar
            //'langType' => (strtolower(Yii::$app->language) == 'en-us') ? 'en' : 'zh_cn',
            'buttonId' => str_replace('_', '-', $this->attribute).'-btn',
            'buttonName' => Yii::t('common', 'Select File'),
        ];
        $this->clientOptions = ArrayHelper::merge($this->_options, $this->clientOptions);
        
        parent::init();
    }

    public function run() {
        $this->registerClientScript();
        if ($this->hasModel()) {
            switch ($this->editorType) {
                //单文件上传按钮
                case 'uploadButton':
                    return '<div class="input-group">'.Html::activeInput('text', $this->model, $this->attribute, $this->options).'<span class="input-group-addon"><a href="javascript:;" id="'.$this->clientOptions['buttonId'].'">'.$this->clientOptions['buttonName'].'</a></span></div>';
                    break;
                case 'colorpicker':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="colorpicker" value="打开取色器" />';
                    break;
                case 'file-manager':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="filemanager" value="浏览服务器" />';
                    break;
                case 'image-dialog':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="imageBtn" value="选择图片" />';
                    break;
                    //
                case 'file-dialog':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="insertfile" value="选择文件" />';
                    break;
                //批量上传按钮
                case 'multi-image-dialog':
                    return Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id]) . '<input type="button" id="insertfile" value="选择文件" />';
                    break;
                default:
                    return Html::activeTextarea($this->model, $this->attribute, ['id' => $this->id]);
                    break;
            }
        } else {
            switch ($this->editorType) {
                case 'uploadButton':
                    return '<div class="input-group">'.Html::input('text', $this->id, $this->value, $this->options).'<span class="input-group-addon"><a href="javascript:;" id="'.$this->clientOptions['buttonId'].'">'.$this->clientOptions['buttonName'].'</a></span></div>';
                    break;
                case 'colorpicker':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="colorpicker" value="打开取色器" />';
                    break;
                case 'file-manager':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="filemanager" value="浏览服务器" />';
                    break;
                case 'image-dialog':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="imageBtn" value="选择图片" />';
                    break;
                case 'file-dialog':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="insertfile" value="选择文件" />';
                    break;
                case 'multi-image-dialog':
                    return Html::input('text', $this->id, $this->value, ['id' => $this->id]) . '<input type="button" id="insertfile" value="选择文件" />';
                    break;
                default:
                    return Html::textarea($this->id, $this->value, ['id' => $this->id]);
                    break;
            }
        }
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript() {
        KindEditorAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);

        $fileManagerJson = Url::to(['kindeditor', 'action' => 'fileManagerJson']);
        $uploadJson = Url::to(['kindeditor', 'action' => 'uploadJson']);
        switch ($this->editorType) {
            case 'uploadButton':
                $url = Url::to(['kindeditor', 'action' => 'uploadJson', 'dir' => 'file']);
                $script = <<<EOT
                    KindEditor.ready(function(K) {
                        var uploadbutton = K.uploadbutton({
                            button : K('#{$this->clientOptions['buttonId']}')[0],
                            fieldName : 'imgFile',
                            url : '{$url}',
                            afterUpload : function(data) {
                                if (data.error === 0) {
                                    var url = K.formatUrl(data.url, 'absolute');
                                    K('#{$this->id}').val(url);
                                } else {
                                    alert(data.message);
                                }
                            },
                            afterError : function(str) {
                                alert('自定义错误信息: ' + str);
                            }
                        });
                        uploadbutton.fileBox.change(function(e) {
                            uploadbutton.submit();
                        });
                    });
EOT;

                break;
            case 'colorpicker':
                $script = <<<EOT
                    KindEditor.ready(function(K) {
                        var colorpicker;
                        K('#colorpicker').bind('click', function(e) {
                            e.stopPropagation();
                            if (colorpicker) {
                                colorpicker.remove();
                                colorpicker = null;
                                return;
                            }
                            var colorpickerPos = K('#colorpicker').pos();
                            colorpicker = K.colorpicker({
                                x : colorpickerPos.x,
                                y : colorpickerPos.y + K('#colorpicker').height(),
                                z : 19811214,
                                selectedColor : 'default',
                                noColor : '无颜色',
                                click : function(color) {
                                    K('#{$this->id}').val(color);
                                    colorpicker.remove();
                                    colorpicker = null;
                                }
                            });
                        });
                        K(document).click(function() {
                            if (colorpicker) {
                                colorpicker.remove();
                                colorpicker = null;
                            }
                        });
                    });
EOT;

                break;
            case 'file-manager':
                $script = <<<EOT
                    KindEditor.ready(function(K) {
                        var editor = K.editor({fileManagerJson : '{$fileManagerJson}'});
                        K('#filemanager').click(function() {
                            editor.loadPlugin('filemanager', function() {
                                editor.plugin.filemanagerDialog({
                                    viewType : 'VIEW',
                                    dirName : 'image',
                                    clickFn : function(url, title) {
                                        K('#{$this->id}').val(url);
                                        editor.hideDialog();
                                    }
                                });
                            });
                        });
                    });
EOT;

                break;
            case 'image-dialog':
                $script = <<<EOT
                    KindEditor.ready(function(K) {
                        var editor = K.editor({
                            allowFileManager : true,
                            "uploadJson":"{$uploadJson}",
                            "fileManagerJson":"{$fileManagerJson}",
                        });
                        K('#imageBtn').click(function() {
                            editor.loadPlugin('image', function() {
                                editor.plugin.imageDialog({
                                    imageUrl : K('#{$this->id}').val(),
                                    clickFn : function(url, title, width, height, border, align) {
                                        K('#{$this->id}').val(url);
                                        editor.hideDialog();
                                    }
                                });
                            });
                        });
                    });
EOT;

                break;
            case 'file-dialog':
                $script = <<<EOT
                    KindEditor.ready(function(K) {
                        var editor = K.editor({
                            allowFileManager : true,
                            "uploadJson":"{$uploadJson}",
                            "fileManagerJson":"{$fileManagerJson}",
                        });
                        K('#insertfile').click(function() {
                            editor.loadPlugin('insertfile', function() {
                                editor.plugin.fileDialog({
                                    fileUrl : K('#{$this->id}').val(),
                                    clickFn : function(url, title) {
                                        K('#{$this->id}').val(url);
                                        editor.hideDialog();
                                    }
                                });
                            });
                        });
                    });
EOT;

                break;
            default:
                $script = "KindEditor.ready(function(K) {
                    K.create('#" . $this->id . "', " . $clientOptions . ");
                });";
                break;
        }
        
        $this->view->registerJs($script, View::POS_READY);
    }
}
