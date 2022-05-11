<?php

namespace common\tools\webuploader;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use yii\helpers\Url;
use yii\web\View;

use common\tools\webuploader\assets\WebuploaderAsset;

class WebuploaderWidget extends InputWidget
{
    public $clientOptions = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        
        // 默认配置，这里是常用定制配置项
        $options = [
            // 选完文件后，是否自动上传。
            'auto' => true,
            // 文件接收服务端。
            'server' => Url::to(['webuploader']),
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            'pick' => [
                'id' => '#filePicker',
                'label' => '点击选择图片',
                'multiple' => true,//多文件
            ],
            'formData' => [
                //附加数据，指定上传的文件夹，默认cms
            ],
            // 只允许选择图片文件。
            'accept' => [
                'title' => 'Images',
                'extensions' => 'gif,jpg,jpeg,bmp,png',
                'mimeTypes' => 'image/*',
            ],
            // 'runtimeOrder' => 'flash',
            'dnd' => '#dndArea',
            'paste' => '#uploader',
            'chunked' => false,
            'chunkSize' => 512 * 1024,
            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            'disableGlobalDnd' => true,
            'fileNumLimit' => 300,
            'fileSizeLimit' => 200 * 1024 * 1024,    // 200 M
            'fileSingleSizeLimit' => 50 * 1024 * 1024,    // 50 M
        ];
        
        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
        
        parent::init();
    }

    public function run()
    {
        //注入模态框【仅注入一次】
        Yii::$app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderModalView']);
        
        //注入相关js
        $this->registerClientScript();
        
        $options = ArrayHelper::merge($this->options, ['id' => $this->id, 'class'=>'form-control']);
        
        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $options);
        } else {
            return Html::textInput($this->id, $this->value, $options);
        }
    }
    
    //渲染一个modal，且只能渲染全局一个
    protected function renderModalView()
    {
        //引入一个app级别的状态量，保证每次执行，保证只加载一次
        if(Yii::$app->params['webuploader.flag']) {
            return;
        } else {
            Yii::$app->params['webuploader.flag'] = true;
        }
        
        //注入公共modal模板
        $view = <<<EOF
            <div class="modal fade" id="modal-uploader" tabindex="-1" role="dialog"  aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-body" style="padding: 10px;background: white;border-radius: 3px;">
                            <div class="panel blank-panel">
                                <button class="close" aria-hidden="true" data-dismiss="modal" type="button">×</button>
                                <div class="panel-heading">
                                    <div class="panel-options">
                                        <ul class="nav nav-tabs">
                                            <li class=""><a data-toggle="tab" id="upload-file" href="#upload-file-content"><i class="fa fa-laptop"></i>上传图片</a></li>
                                            <li class=""><a data-toggle="tab" id="explore-file" href="#explore-file-content"><i class="fa fa-signal"></i>图片列表</a></li>
                                            <li class=""><a data-toggle="tab" id="web-file" href="#web-file-content"><i class="fa fa-desktop"></i>网络图片</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div id="upload-file-content" class="tab-pane"></div>
                                        <div id="explore-file-content" class="tab-pane"><div style="width: 100%; height: 100px; text-align: center;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">加载中...</span></div></div>
                                        <div id="web-file-content" class="tab-pane">开发中...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
EOF;
        
        echo $view;
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        $tokenName = Yii::$app->getRequest()->csrfParam;
        $tokenValue = Yii::$app->getRequest()->getCsrfToken();
        $this->clientOptions['formData'][$tokenName] = $tokenValue;
        
        $baseUrl = WebuploaderAsset::register($this->view)->baseUrl;
        $this->clientOptions['swf'] = $baseUrl.'/dist/Uploader.swf';
        
        $clientOptions = Json::encode($this->clientOptions);
        
        //缩略图初始化
        if ($this->hasModel()) {
            $images = empty($this->model->{$this->attribute})?[]:explode(',', $this->model->{$this->attribute});
        } else {
            $images = empty($this->value)?[]:explode(',',  explode(',', $this->value));
        }
        
        $fullUrl = (Yii::$app->params['config_aliyunoss_use_https']?'https://':'http://').
        (empty(Yii::$app->params['config_aliyunoss_get_domain'])?(Yii::$app->aliyunoss->bucket.'.'.Yii::$app->aliyunoss->endpoint):Yii::$app->params['config_aliyunoss_get_domain']).
        '/';
        
        $init_images_str = '';
        foreach ($images as $image) {
            $init_images_str .= '<div class="multi-item">' . 
            '<img class="img-responsive img-thumbnail" data-src="'. $image .'" src="'. $fullUrl.$image .'">' . 
            '<em title="删除这张图片" class="close">×</em>' . 
            '</div>';
        }
        
        $exploreUrl = Url::to(['/tools/attachment/explore']);
        
        // 初始化Web Uploader
        $script = <<<EOF
//将uploadObject做个最简单的对象整理，不做标准处理，主要解决作用域的问题
var uploadObject = {
    init: function() {
        //通用初始化//删除文件//更新到表单（延迟绑定）
        $('#{$this->id}').parent('.input-group').next('.multi-img-details').html('{$init_images_str}').on('click', '.close', function() {
            $(this).parent('.multi-item').remove();//移除
            
            //写入到表单
            var srcs = [];
            $('#{$this->id}').parent('.input-group').next('.multi-img-details').find('img').each(function(i) {//更新，需要重新扫描
                srcs[i] = $(this).attr('data-src');
            });
            $('#{$this->id}').val(srcs.join());//以逗号连接
        });
    },
    
    //全局变量
    selectImage: null,//点击上传按钮的$(this)
    imagesList: [],//新上传成功的文件
    
    exploreBind: false,//explore是否已经绑定过
    exploreRun: function() {
        this.exploreBind = true;//已经绑定
        $.ajax({
            type: 'POST',
            dataType: 'html',
            //context: $(this),
            url: '{$exploreUrl}',
            success: function(html){
                $('#explore-file-content').html(html);
            }
        });
    },
    
    uploadBind: false,//upload是否已经绑定过
    uploaderView: function() {//通用上传视图模板
        return '<div id="uploader">' +
            '<div class="queueList">' +
                '<div id="dndArea" class="placeholder">' +
                    '<div id="filePicker"></div>' +
                    '<p>或将照片拖到这里，单次最多可选300张</p>' +
                '</div>' +
            '</div>' +
            '<div class="status-bar" style="display:none;">' +
                '<div class="modal-footer" style="text-align: left;">' +
                    '<div class="progress">' +
                        '<span class="text">0%</span>' +
                        '<span class="percentage"></span>' +
                    '</div>' +
                    '<div class="info pull-left">共0张（0B），已上传0张</div>' +
                    '<div class="btns pull-right">' +
                        '<button type="button" class="btn btn-primary pull-right fresh-btn">刷新</button>' +
                        '<button type="button" class="btn btn-primary pull-right use-btn">使用</button>' +
                        '<button type="button" class="btn btn-primary pull-right upload-btn" style="margin-right: 5px;margin-left: 5px;">上传</button>' +
                        '<div class="pull-right" id="filePicker2"></div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    },
    uploadRun: function() {
        this.uploadBind = false;//每次都要初始化uploader所以不绑定
        
        $('#upload-file-content').html(this.uploaderView());//转入uploader模板，只需要载入一次
        
        // 实例化
        var wrap = $('#uploader'),
            // 上传按钮
            upload = wrap.find('.upload-btn'),
            // 没选择文件之前的内容。
            placeHolder = wrap.find('.placeholder'),
            // 状态栏，包括进度和控制按钮
            statusBar = wrap.find('.status-bar'),
            // 文件总体选择信息。
            info = statusBar.find('.info'),
            //进度条
            progress = statusBar.find('.progress').hide(),
            //使用按钮
            useBtn = statusBar.find('.use-btn'),
            // 图片容器
            queue = $('<ul class="filelist"></ul>').appendTo( wrap.find('.queueList') ),
            // 添加的文件数量
            fileCount = 0,
            // 添加的文件总大小
            fileSize = 0,
            // 优化retina, 在retina下这个值是2
            ratio = window.devicePixelRatio || 1,
            // 缩略图大小
            thumbnailWidth = 110 * ratio,
            thumbnailHeight = 110 * ratio,
            // 可能有pedding, ready, uploading, confirm, done.
            state = 'pedding',
            // 所有文件的进度信息，key为file id
            percentages = {},
            // 判断浏览器是否支持图片的base64
            isSupportBase64 = ( function() {
                var data = new Image();
                var support = true;
                data.onload = data.onerror = function() {
                    if( this.width != 1 || this.height != 1 ) {
                        support = false;
                    }
                }
                data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
                return support;
            } )(),
            supportTransition = (function() {
                var s = document.createElement('p').style,
                    r = 'transition' in s ||
                            'WebkitTransition' in s ||
                            'MozTransition' in s ||
                            'msTransition' in s ||
                            'OTransition' in s;
                s = null;
                return r;
            })(),
            uploader;// WebUploader实例
            
        // 实例化
        //var clientOptions = $clientOptions;
        //clientOptions.pick.multiple = uploadObject.selectImage.prev().attr('data-multiple');//每次初始化uploader时，都修正这个值
        uploader = WebUploader.create($clientOptions);
        console.log('初始化uploader');
        
        uploader.on('uploadSuccess', function(file, response) {//当前文件信息和服务器返回信息
            uploadObject.imagesList.push(response);
        });
            
        // 拖拽时不接受 js, txt 文件。
        uploader.on('dndAccept', function( items ) {
            var denied = false,
                len = items.length,
                i = 0,
                // 修改js类型
                unAllowed = 'text/plain;application/javascript ';
            
            for ( ; i < len; i++ ) {
                // 如果在列表里面
                if ( ~unAllowed.indexOf( items[ i ].type ) ) {
                    denied = true;
                    break;
                }
            }
            
            return !denied;
        });
            
        // 添加“添加文件”的按钮，如果是多文件上传，则显示此按钮
        if(uploader.options.pick.multiple) {
            uploader.addButton({
                id: '#filePicker2',
                label: '添加'
            });
        }
            
        //uploader进入全局
    //     uploader.on('ready', function() {
    //         window.uploader = uploader;
    //     });
            
        // 当有文件添加进来时执行，负责view的创建
        function addFile( file ) {
            var li = $('<li id="' + file.id + '">' +
                    '<p class="title">' + file.name + '</p>' +
                    '<p class="imgWrap"></p>'+
                    '<p class="progress" style="display: none;"><span></span></p>' +
                    '</li>'),
                prgress = li.find('p.progress span'),
                wrap = li.find('p.imgWrap'),
                info = $('<p class="error"></p>'),
            
                showError = function( code ) {
                    switch( code ) {
                        case 'exceed_size':
                            text = '文件大小超出';
                            break;
                        case 'interrupt':
                            text = '上传暂停';
                            break;
                        default:
                            text = '上传失败，请重试';
                            break;
                    }
                    info.text( text ).appendTo( li );
                };
            
            if ( file.getStatus() === 'invalid') {
                showError( file.statusText );
            } else {
                // @todo lazyload
                wrap.text('预览中');
                uploader.makeThumb( file, function( error, src ) {
                    var img;
            
                    if ( error ) {
                        wrap.text('不能预览');
                        return;
                    }
            
                    if( isSupportBase64 ) {
                        img = $('<img src="'+src+'">');
                        wrap.empty().append( img );
                    } else {
                        console.log('不支持ie7、8');
                        /*
                        $.ajax('../../server/preview.php', {
                            method: 'POST',
                            data: src,
                            dataType:'json'
                        }).done(function( response ) {
                            if (response.result) {
                                img = $('<img src="'+response.result+'">');
                                wrap.empty().append( img );
                            } else {
                                wrap.text("预览出错");
                            }
                        });
                        */
                    }
                }, thumbnailWidth, thumbnailHeight );
            
                percentages[ file.id ] = [ file.size, 0 ];
                file.rotation = 0;
            }
            
            file.on('statuschange', function( cur, prev ) {
                if ( prev === 'progress') {
                    prgress.parent().hide().width(0);
                } else if ( prev === 'queued') {
                    li.off('mouseenter mouseleave');
                }
            
                // 成功
                if ( cur === 'error' || cur === 'invalid') {
                    console.log( file.statusText );
                    showError( file.statusText );
                    percentages[ file.id ][ 1 ] = 1;
                } else if ( cur === 'interrupt') {
                    showError('interrupt');
                } else if ( cur === 'queued') {
                    percentages[ file.id ][ 1 ] = 0;
                } else if ( cur === 'progress') {
                    info.remove();
                    prgress.parent().css('display', 'block');
                } else if ( cur === 'complete') {
                    li.append('<span class="success"></span>');
                }
            
                li.removeClass('state-' + prev ).addClass('state-' + cur );
            });
            
            li.appendTo( queue );
        }
            
        // 负责view的销毁
        function removeFile( file ) {
            var li = $('#'+file.id);
            delete percentages[ file.id ];
            updateTotalProgress();
            li.off().find('.file-panel').off().end().remove();
        }
            
        function updateTotalProgress() {
            var loaded = 0,
                total = 0,
                spans = progress.children(),
                percent;
            
            $.each( percentages, function( k, v ) {
                total += v[ 0 ];
                loaded += v[ 0 ] * v[ 1 ];
            } );
            
            percent = total ? loaded / total : 0;
            
            spans.eq( 0 ).text( Math.round( percent * 100 ) + '%');
            spans.eq( 1 ).css('width', Math.round( percent * 100 ) + '%');
            updateStatus();
        }
            
        function updateStatus() {
            var text = '', stats;
            
            if ( state === 'ready') {
                text = '选中' + fileCount + '张图片，共' + WebUploader.formatSize( fileSize ) + '。';
            } else if ( state === 'confirm') {
                stats = uploader.getStats();
                if ( stats.uploadFailNum ) {
                    text = '已成功上传' + stats.successNum+ '张照片至XX相册，'+ stats.uploadFailNum + '张照片上传失败，<br /><a class="retry" href="javascript:;">重新上传</a>失败图片或<a class="ignore" href="javascript:;">忽略</a>'
                }
            } else {
                stats = uploader.getStats();
                text = '共' + fileCount + '张（' + WebUploader.formatSize( fileSize )  + '），已上传' + stats.successNum + '张';
            
                if ( stats.uploadFailNum ) {
                    text += '，失败' + stats.uploadFailNum + '张';
                }
            }
            
            info.html( text );
        }
            
        function setState( val ) {
            var file, stats;
            
            if ( val === state ) {
                return;
            }
            
            upload.removeClass('state-' + state );
            upload.addClass('state-' + val );
            state = val;
            
            switch ( state ) {
                case 'pedding':
                    placeHolder.removeClass('element-invisible');
                    queue.hide();
                    statusBar.addClass('element-invisible');
                    uploader.refresh();
                    break;
            
                case 'ready':
                    placeHolder.addClass('element-invisible');
                    $('#filePicker2').removeClass('element-invisible');
                    queue.show();
                    statusBar.removeClass('element-invisible');
                    uploader.refresh();
                    break;
            
                case 'uploading':
                    $('#filePicker2').addClass('element-invisible');
                    progress.show();
                    upload.text('暂停');
                    break;
            
                case 'paused':
                    progress.show();
                    upload.text('添加');
                    break;
            
                case 'confirm':
                    progress.hide();
                    $('#filePicker2').removeClass('element-invisible');
                    upload.text('上传');
            
                    stats = uploader.getStats();
                    if ( stats.successNum && !stats.uploadFailNum ) {
                        setState('finish');
                        return;
                    }
                    break;
                case 'finish':
                    stats = uploader.getStats();
                    if ( stats.successNum ) {
                        //alert('上传成功');
                        console.log('上传成功');
                    } else {
                        // 没有成功的图片，重设
                        state = 'done';
                        location.reload();
                    }
                    break;
            }
            
            updateStatus();
        }
            
        uploader.onUploadProgress = function( file, percentage ) {
            var li = $('#'+file.id), percent = li.find('.progress span');
            percent.css('width', percentage * 100 + '%');
            percentages[ file.id ][ 1 ] = percentage;
            updateTotalProgress();
        };
            
        uploader.onFileQueued = function( file ) {
            fileCount++;
            fileSize += file.size;
            if ( fileCount === 1 ) {
                placeHolder.addClass('element-invisible');
                statusBar.show();
            }
            addFile( file );
            setState('ready');
            updateTotalProgress();
        };
            
        uploader.onFileDequeued = function( file ) {
            fileCount--;
            fileSize -= file.size;
            if ( !fileCount ) {
                setState('pedding');
            }
            removeFile( file );
            updateTotalProgress();
        };
            
        uploader.on('all', function( type ) {
            var stats;
            switch( type ) {
                case 'uploadFinished':
                    setState('confirm');
                    break;
                case 'startUpload':
                    setState('uploading');
                    break;
                case 'stopUpload':
                    setState('paused');
            }
        });
            
        uploader.onError = function( code ) {
            alert('Eroor: ' + code );
        };
            
        upload.on('click', function() {
            if ( $(this).hasClass('disabled') ) {
                return false;
            }
            if ( state === 'ready') {
                uploader.upload();
            } else if ( state === 'paused') {
                uploader.upload();
            } else if ( state === 'uploading') {
                uploader.stop();
            }
        });
            
        info.on('click', '.retry', function() {
            uploader.retry();
        } );
            
        info.on('click', '.ignore', function() {
            $('#modal-uploader').modal('hide');//隐藏
        } );
            
        upload.addClass('state-' + state );
        updateTotalProgress();
            
        //使用图片
        useBtn.on('click', function() {
            var str = '';
            for(i = 0; i < uploadObject.imagesList.length; i++) {
                str += '<div class="multi-item">' +
                    '<img class="img-responsive img-thumbnail" data-src="'+ uploadObject.imagesList[i].relativeUrl +'" src="'+ uploadObject.imagesList[i].fullUrl +'">' +
                    '<em title="删除这张图片" class="close">×</em>' +
                '</div>';
            }
            
            //multi-img-details
            //多文件上传，直接追加，否则直接覆盖
            var multi_img_details = uploadObject.selectImage.parent('.input-group').next('.multi-img-details');
            if(uploader.options.pick.multiple) {
                multi_img_details.append(str);
            } else {
                multi_img_details.html(str);//单图
            }
            //写入到表单
            var srcs = [];
            multi_img_details.find('img').each(function(i) {
                srcs[i] = $(this).attr('data-src');
            });
            $('#{$this->id}').val(srcs.join());//以逗号连接
            
            $('#modal-uploader').modal('hide');//隐藏
            uploader = null;//手动销毁
        });
        
        //刷新按钮
        $('.status-bar .fresh-btn').on('click', function() {
            uploadObject.uploadRun();
        });
    },
    
    webBind: false,//web是否已经绑定过
    webRun: function() {
        this.webBind = true;//已经绑定
    },
    
    tabBind: false,//web是否已经绑定过
    tabRun: function() {
        this.tabBind = false;//已经绑定，每次执行，不绑定
        $('#upload-file').tab('show');//首次展示指定的tab，每次显示modal时都会执行此动作，并打开指定tab
    }
};//uploadObject完.....

//初始化
uploadObject.init();

//运程文件浏览功能
$('#explore-file').on('shown.bs.tab', function (e) {
    if(!uploadObject.exploreBind) {
        uploadObject.exploreRun();
    }
});

//上传文件功能
$('#upload-file').on('shown.bs.tab', function (e) {
    if(!uploadObject.uploadBind) {
        uploadObject.uploadRun();
    }
});

//抓取web文件
$('#web-file').on('shown.bs.tab', function (e) {
    if(!uploadObject.webBind) {
        uploadObject.webRun();
    }
});

//将tab绑定到modal//
$('#modal-uploader').on('shown.bs.modal', function (e) {
    if(!uploadObject.tabBind) {
        uploadObject.tabRun();
    }
});

//所有的内容都放置在作用域以内，防止全局变量！！！！
$('#{$this->id}').next().on('click', function() {//点击上传按钮
    uploadObject.selectImage = $(this);//记录弹出点！
    uploadObject.imagesList = [];//初始化
    
    $('#modal-uploader').modal('show');//弹出模态框
});
EOF;
        
        $this->view->registerJs($script);//显示时的效果好一些//, yii\web\View::POS_READY
    }
}