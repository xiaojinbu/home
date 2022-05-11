<?php
/**
 * @author xia.jorry
 * 
 * 注意：文件上传时，需要做上传记录，但取文件时，不需要走任何的验证！
 * 
 */
namespace common\tools\fileupload;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\jui\InputWidget;
use yii\helpers\Html;
// use yii\jui\JuiAsset;
use app\assets\JqueryUiNoDateAsset;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

use common\tools\fileupload\assets\JQueryFileUploadPlusAsset;
// use common\models\tools\Attachment;

/**
 * 图片上传
<div class="form-group merchant-merchant_pic">
    <label for="merchant-merchant_address" class="control-label col-sm-2">测试多文件</label>
    <div class="col-sm-9">
        <?= JQueryFileUploadWidget::widget([
            'model' => $model,
            'attribute' => 'merchant_address',
            'options' => ['class' => 'form-control', 'readonly' => true],
            
            'url' => ['multipletest', 'param' => 'value'],
            'uploadName' => 'merchant_address',
            'fileOptions' => ['accept' => 'image/*', 'multiple' => true],//多文件
            'clientOptions' => [
                'maxFileSize' => 20000000,//200kb
                'dataType' => 'json',
                'acceptFileTypes' => new yii\web\JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
                //'maxNumberOfFiles' => 5,
            ],
        ]) ?>
    </div>
</div>

<?= $form->field($model, 'merchant_idcard_image1', [
    'options' => ['class' => 'form-group'],
    'wrapperOptions' => ['class' => 'col-sm-8'],
    'labelOptions' => ['class' => 'control-label col-sm-4'],
    'inputOptions' => ['class' => 'form-control'],
    'horizontalCssClasses' => [
        'error' => 'col-sm-offset-4 col-sm-8',
        'hint' => 'col-sm-8',
    ],
])->widget(JQueryFileUploadWidget::className(), [
    'model' => $model,
    'attribute' => 'merchant_idcard_image1',
    'options' => ['class' => 'form-control', 'readonly' => true],
    
    'url' => ['fileupload1', 'param' => 'value'],
    'uploadName' => 'merchant_idcard_image1',
    'fileOptions' => ['accept' => 'image/*', 'multiple' => false],//单图
    'clientOptions' => [
        'maxFileSize' => 200000,//200kb
        'dataType' => 'json',
        'acceptFileTypes' => new yii\web\JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
    ],
]) ?>
 *
 * 文件上传
 * <?= $form->field($model, 'pic_url', [])->widget(JQueryFileUploadWidget::className(), [
        'model' => $model,
        'attribute' => 'pic_url',
        'options' => ['class' => 'form-control', 'readonly' => true],
        'url' => ['fileupload', 'param' => 'value'],
        'uploadName' => 'pic_url',
        'fileOptions' => [
            'accept' => '*',//选择文件时的windows过滤器
            'multiple' => false,//单文件
            'isImage' => false,//非图片文件
        ],//单图
        'clientOptions' => [
            'acceptFileTypes' => new JsExpression('/(\.|\/)(gif|jpe?g|png|ai|txt|xls|xlsx|docx|doc|pdf|zip|rar|tar)$/i'),//限制上传的后缀名
        ],
    ]) ?>
 *
 *
 *
 */
class JQueryFileUploadWidget extends InputWidget
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

    public $uploadName = 'file';//上传按钮的名称，接收时也是这个名称，默认file
    
    public $fileOptions = [];
    
    /**
     * @var string|array upload route
     */
    public $url;
    
    private $_webIconUrl;
    
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();//一定要先处理，优化处理了$options和$clientOptions
        
        //初始化配置icon路径
        $this->_webIconUrl = Yii::getAlias('@web/images/icon/');
        
        if(empty($this->url)) {
            throw new InvalidConfigException('"JQueryFileUploadWidget::url" 参数不能为空。');
        }
        
        // 默认配置
        $options = [
            'maxFileSize' => 500*1024,//默认500kb
            'dataType' => 'json',//强制json
            'acceptFileTypes' => new JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),//默认图片相关文件类型
            'messages' => [
                'acceptFileTypes' => Yii::t('common', 'File type not allowed'),
                'maxFileSize' => Yii::t('common', 'File exceeds maximum allowed size of 99MB'),
                'maxNumberOfFiles' => Yii::t('common', 'Maximum number of files exceeded'),
                'minFileSize' => Yii::t('common', 'File is too small'),
                'uploadedBytes' => Yii::t('common', 'Uploaded bytes exceed file size'),
            ],
        ];
        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
        
        if (!isset($this->fileOptions['multiple'])) {//默认多文件
            $this->fileOptions['multiple'] = true;
        }
        if (!isset($this->fileOptions['isImage'])) {//默认为图片类型，即可以展示的文件类型
            $this->fileOptions['isImage'] = true;
        }
        $this->clientOptions['url'] = $this->fileOptions['data-url'] = Url::to($this->url);
        $this->clientOptions['autoUpload'] = true;//强制自动上传
        $this->fileOptions['id'] = 'file-btn-'.$this->options['id'];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        //获取文件相关模型
        $value = ($this->hasModel()) ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
//        echo "<PRE>";print_r($value);die();
        if($this->fileOptions['multiple']) {
            if($value) {
                if (is_array($value)) {
                    $names = $value;
                } else {
                    $names = explode(',', $value);
                }
//                 foreach ($names as $key => $name) {
//                     $names[$key] = substr(basename($name), 0, 32);
//                 }
//                 $names = Attachment::find()->where(['in', 'md5', $names])->all();
            } else {
                $names = [];
            }
        } else {
            if($value) {
                //$name = Attachment::findOne(['md5' => substr(basename($value), 0, 32)]);//校验md5
                $name = $value;
            } else {
                $name = null;
            }
        }
        
        switch (true) {
            case (!$this->fileOptions['isImage'] && $this->fileOptions['multiple'])://多文件
                echo $this->render('multiple-file-main', ['names' => $names]);
                break;
            case (!$this->fileOptions['isImage'] && !$this->fileOptions['multiple'])://单文件
                echo $this->render('file-main', ['name' => $name]);
                break;
            case ($this->fileOptions['isImage'] && !$this->fileOptions['multiple'])://单图
                echo $this->render('main', ['name' => $name]);
                break;
            default://多图
                echo $this->render('multiple-main', ['names' => $names]);
            break;
        }

        $this->registerClientScript();
    }
    
    /**
     * Registers required script for the plugin to work as jQuery File Uploader
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        
        JQueryFileUploadPlusAsset::register($view);
        
        if($this->fileOptions['isImage']) {//图片文件
            if($this->fileOptions['multiple']) {
                JqueryUiNoDateAsset::register($view);//拖拽
                $this->addMultipleScriptAndEvents();//多图片文件js片段
            } else {
                $this->addSimpleScriptAndEvents();//单图文件js片段
            }
        } else {//非图片文件
            if($this->fileOptions['multiple']) {
                JqueryUiNoDateAsset::register($view);//拖拽
                $this->addMultipleFileScriptAndEvents();//多文件js片段
            } else {
                $this->addSimpleFileScriptAndEvents();//单文件js片段
            }
        }

        $id = $this->fileOptions['id'];
        $this->registerClientOptions('fileupload', $id);
        $this->registerClientEvents('fileupload', $id);
    }
    
    //多文件
    protected function addMultipleScriptAndEvents()
    {
        $view = $this->getView();
        $id = $this->options['id'];
        $notPicUrl= Yii::getAlias('@web/images/').'nopic.jpg';
        $fieldName = ($this->hasModel()) ? Html::getInputName($this->model, $this->attribute) : $this->name;

        //删除操作
        $js = <<<EOF
var id = $("#$id");
id.parents('.fileupload-buttonbar').find('.fileupload-img-multi-preview').on('click', '.close', function(){
    $(this).parent().remove();
});
$('.fileupload-buttonbar .fileupload-img-multi-preview.ui-sortable').sortable({
    stop: function() {
        //重新排序
        var field = '{$fieldName}';
        $(this).find('.multi-item').each(function(i) {
            $(this).find('input[type=\'hidden\']').attr('name', (field + '[' + i + ']'));
        });
    }
});
EOF;
        $view->registerJs($js);

        if(empty($this->clientEvents['add']))//添加文件时触发回调
            $this->clientEvents['add'] = "function(e, data) {
            data.filepreview = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-img-multi-preview');//预览
            data.fileprogress = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-multi-progress');//进度
        }";

        if(empty($this->clientEvents['processalways']))//添加文件时检查并回调，还未提交
            $this->clientEvents['processalways'] = "function(e, data) {//自身就是以单个文件为对象遍历调用
            var index = data.index, file = data.files[index];
            if (file.error) {
                alert(file.name + '：' + file.error);
                return false;
                //data.filepreview.find('a').append($('<span class=\"text text-danger\"/>').text(file.error));
            }
            //生成点位缩略图
            if (file.preview) {
                var item = $('<div>').attr('class', 'multi-item');
                //$('<div>').attr('class', 'progress').append($('<div>').attr('class', 'progress-bar progress-bar-success')).appendTo(item);//总体进度
                $(file.preview).attr('class', 'img-responsive img-thumbnail').appendTo(item);
                $('<em>').attr('class', 'close').attr('title','删除这张图片').html('×').appendTo(item);
                data.filepreview.append(item);
            }

//             if (index + 1 === data.files.length) {
//                 data.filepreview.find('button').text('Upload').prop('disabled', !!data.files.error);
//             }
        }";

        if(empty($this->clientEvents['progressall']))//上传时的全局进度
        $this->clientEvents['progressall'] = "function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-multi-progress .progress').show().find('.progress-bar').css('width', progress + '%');
        }";
        
        $id = $this->options['id'];
        if(empty($this->clientEvents['done']))//提交上传完毕回调
            $this->clientEvents['done'] = "function(e, data) {
            if(data.result.state) {
                //var index = data.index, file = data.files[index];
                //找到上传的缩略图，然后替换img
                var ret = data.result.msg;
                var item = $(data.files[0].preview);//.replaceWith($('<img/>').attr('class', 'img-responsive img-thumbnail').prop('src', ret.thumbnailUrl));//也可以不替换
                item.parent().prepend($('<input/>').prop('type', 'hidden').prop('value', ret.objectUrl).prop('name', ''));//隐藏域
            } else {
                alert(data.result.msg);//异常
            }
            
            //重新排序
            var field = '{$fieldName}';
            data.filepreview.find('.multi-item').each(function(i) {
                $(this).find('input[type=\'hidden\']').attr('name', (field + '[' + i + ']'));
            });
            data.fileprogress.find('.progress-bar').css('width', '0%').parent().hide('slow');//隐藏效果
        }";
                        
        if(empty($this->clientEvents['fail']))//上传之后返回的错误
            $this->clientEvents['fail'] = "function(e, data) {
            console.log('fail');
        }";
    }
    
    //单图
    protected function addSimpleScriptAndEvents()
    {
        $view = $this->getView();
        $id = $this->options['id'];
        $notPicUrl= Yii::getAlias('@web').'/images/nopic.jpg';
        
        //删除操作
        $js = <<<EOF
var id = $("#$id");
var notPicUrl = "$notPicUrl";
id.parents('.fileupload-buttonbar').find('.fileupload-img-preview').on('click', '.close', function(){
    $(this).prev().prop('src', notPicUrl);
    id.val('');
});
EOF;
        $view->registerJs($js);

        if(empty($this->clientEvents['add']))//添加文件时触发回调
        $this->clientEvents['add'] = "function(e, data) {
            data.filepreview = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-img-preview');//预览
            data.fileprogress = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-progress');//进度
        }";
        
        if(empty($this->clientEvents['processalways']))//添加文件时检查并回调，还未提交
        $this->clientEvents['processalways'] = "function(e, data) {//自身就是以文件为对象遍历调用
            var index = data.index, file = data.files[index];
            if (file.error) {
                alert(file.name + '：' + file.error);
                return false;
                //data.filepreview.find('a').append($('<span class=\"text text-danger\"/>').text(file.error));
            }
        }";
        
        if(empty($this->clientEvents['progressall']))//上传时的全局进度
        $this->clientEvents['progressall'] = "function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-progress .progress').show().find('.progress-bar').css('width', progress + '%');
        }";
        
        $id = $this->options['id'];
        if(empty($this->clientEvents['done']))//提交上传完毕回调
        $this->clientEvents['done'] = "function(e, data) {
            if(data.result.state) {
                var ret = data.result.msg;
                var link = $('<a>').prop('href', 'javascript:;');
                $('<img/>').attr('class', 'img-responsive img-thumbnail').prop('src', ret.thumbnailUrl).appendTo(link);//追加
                $('<em>').attr('class', 'close').attr('title','删除这张图片').html('×').appendTo(link);//追加
                data.filepreview.html(link);//单图覆盖，以防重复操作。。。
                
                $('#".$id."').val(ret.objectUrl);
            } else {
                alert(data.result.msg);//异常
            }
            data.fileprogress.find('.progress-bar').css('width', '0%').parent().hide('slow');//隐藏效果
        }";
        
        if(empty($this->clientEvents['fail']))//上传之后返回的错误
        $this->clientEvents['fail'] = "function(e, data) {
            console.log('fail......JORRY');
        }";
    }
    
    //多文件
    protected function addMultipleFileScriptAndEvents()
    {
        $view = $this->getView();
        $id = $this->options['id'];
        $notPicUrl= Yii::getAlias('@web').'/images/nopic.jpg';
        $fieldName = ($this->hasModel()) ? Html::getInputName($this->model, $this->attribute) : $this->name;
        
        //删除操作
        $js = <<<EOF
var id = $("#$id");
id.parents('.fileupload-buttonbar').find('.fileupload-img-multi-preview').on('click', '.close', function(){
    $(this).parent().remove();
});
$('.fileupload-buttonbar .fileupload-img-multi-preview.ui-sortable').sortable({
    stop: function() {
        //重新排序
        var field = '{$fieldName}';
        $(this).find('.multi-item').each(function(i) {
            $(this).find('input[type=\'hidden\']').attr('name', (field + '[' + i + ']'));
        });
    }
});
EOF;
        $view->registerJs($js);
        
        if(empty($this->clientEvents['add']))//添加文件时触发回调
        $this->clientEvents['add'] = "function(e, data) {
            data.filepreview = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-img-multi-preview');//预览
            data.fileprogress = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-multi-progress');//进度
        }";
        
        if(empty($this->clientEvents['processalways']))//添加文件时检查并回调，还未提交
        $this->clientEvents['processalways'] = "function(e, data) {//自身就是以单个文件为对象遍历调用
            var index = data.index, file = data.files[index];
            if (file.error) {
                alert(file.name + '：' + file.error);
                return false;
                //data.filepreview.find('a').append($('<span class=\"text text-danger\"/>').text(file.error));
            }
            //生成点位缩略图，使用空白文件file.png代替
            var item = $('<div>').attr('class', 'multi-item');
            //$('<div>').attr('class', 'progress').append($('<div>').attr('class', 'progress-bar progress-bar-success')).appendTo(item);//总体进度
            $('<img/>').attr('class', 'img-responsive img-thumbnail preview').prop('src', '".($this->_webIconUrl)."' + 'file.png').appendTo(item);
            $('<em>').attr('class', 'close').attr('title','删除这张图片').html('×').appendTo(item);
            data.filepreview.append(item);
            
//             if (index + 1 === data.files.length) {
//                 data.filepreview.find('button').text('Upload').prop('disabled', !!data.files.error);
//             }
        }";
        
        if(empty($this->clientEvents['progressall']))//上传时的全局进度
        $this->clientEvents['progressall'] = "function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-multi-progress .progress').show().find('.progress-bar').css('width', progress + '%');
        }";
        
        $id = $this->options['id'];
        if(empty($this->clientEvents['done']))//提交上传完毕回调
        $this->clientEvents['done'] = "function(e, data) {
            if(data.result.state) {
                //var index = data.index, file = data.files[index];
                //找到上传的缩略图，然后替换img
                var ret = data.result.msg;//逐一替换
                var items = data.filepreview.find('.preview');
                if(items.length > 0) {
                    var item = items.eq(0);
                    var ext = ret.objectUrl.split('.').pop().toLowerCase();
                    item.parent().prepend($('<input/>').prop('type', 'hidden').prop('value', ret.objectUrl).prop('name', ''));//隐藏域
                    item.replaceWith($('<img/>').attr('class', 'img-responsive img-thumbnail').prop('title', data.files[0].name).prop('src', '".($this->_webIconUrl)."' + ext + '.png'));
                }
            } else {
                alert(data.result.msg);//异常
            }
            
            //重新排序
            var field = '{$fieldName}';
            data.filepreview.find('.multi-item').each(function(i) {
                $(this).find('input[type=\'hidden\']').attr('name', (field + '[' + i + ']'));
            });
            data.fileprogress.find('.progress-bar').css('width', '0%').parent().hide('slow');//隐藏效果
        }";
        
        if(empty($this->clientEvents['fail']))//上传之后返回的错误
        $this->clientEvents['fail'] = "function(e, data) {
            console.log('fail');
        }";
    }
    
    //单文件
    protected function addSimpleFileScriptAndEvents()
    {
        $view = $this->getView();
        $id = $this->options['id'];
        $notPicUrl= Yii::getAlias('@web').'/images/nopic.jpg';
        
        //删除操作
        $js = <<<EOF
var id = $("#$id");
var notPicUrl = "$notPicUrl";
id.parents('.fileupload-buttonbar').find('.fileupload-img-preview').on('click', '.close', function(){
    $(this).prev().prop('src', notPicUrl);
    id.val('');
});
EOF;
        $view->registerJs($js);
        
        if(empty($this->clientEvents['add']))//添加文件时触发回调
            $this->clientEvents['add'] = "function(e, data) {
            data.filepreview = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-img-preview');//预览
            data.fileprogress = $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-progress');//进度
        }";
        
        if(empty($this->clientEvents['processalways']))//添加文件时检查并回调，还未提交
        $this->clientEvents['processalways'] = "function(e, data) {//自身就是以文件为对象遍历调用
            var index = data.index, file = data.files[index];
            if (file.error) {
                alert(file.name + '：' + file.error);
                return false;
                //data.filepreview.find('a').append($('<span class=\"text text-danger\"/>').text(file.error));
            }
        }";
        
        if(empty($this->clientEvents['progressall']))//上传时的全局进度
        $this->clientEvents['progressall'] = "function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $(e.currentTarget).parents('.fileupload-buttonbar').find('.fileupload-progress .progress').show().find('.progress-bar').css('width', progress + '%');
        }";
        
        $id = $this->options['id'];
        if(empty($this->clientEvents['done']))//提交上传完毕回调
        $this->clientEvents['done'] = "function(e, data) {
            if(data.result.state) {
                var ret = data.result.msg;
                var link = $('<a>').prop('href', 'javascript:;');
                var ext = ret.objectUrl.split('.').pop().toLowerCase();
                $('<img/>').attr('class', 'img-responsive img-thumbnail').prop('title', data.files[0].name).prop('src', '".($this->_webIconUrl)."' + ext + '.png').appendTo(link);//追加
                $('<em>').attr('class', 'close').attr('title','删除这张图片').html('×').appendTo(link);//追加
                data.filepreview.html(link);//单图覆盖，以防重复操作。。。
                            
                $('#".$id."').val(ret.objectUrl);
            } else {
                alert(data.result.msg);//异常
            }
            data.fileprogress.find('.progress-bar').css('width', '0%').parent().hide('slow');//隐藏效果
        }";
        
        if(empty($this->clientEvents['fail']))//上传之后返回的错误
        $this->clientEvents['fail'] = "function(e, data) {
            console.log('fail......JORRY');
        }";
    }
    
    public function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }
}
