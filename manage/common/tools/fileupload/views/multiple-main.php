<?php
use yii\helpers\Html;
use common\components\aliyunoss\AliyunOss;
use common\models\tools\Attachment;

$context = $this->context;

$name_ = ($context->hasModel()) ? Html::getInputName($context->model, $context->attribute) : $context->name;
$basePath = Yii::$app->aliyunoss->getFullPath();
?>
<div class="fileupload-buttonbar">
    <div class="input-group">
        <?= Html::textInput('jorry', '批量上传文件', $context->options)//只显示  ?>
        
        <span class="input-group-btn">
            <span class="btn btn-primary fileinput-button">
                <span>添加文件</span>
                <?php 
                echo Html::fileInput($context->uploadName, '', $context->fileOptions);
                ?>
            </span>
        </span>
    </div>
    
    <div class="fileupload-multi-progress">
        <div class="progress">
            <div class="progress-bar progress-bar-success"></div>
        </div>
    </div>
    
    <div class="input-group fileupload-img-multi-preview multi-img-details ui-sortable">
        <?php foreach ($names as $key => $name) { ?>
        <div class="multi-item">
            <input type="hidden" value="<?= $name ?>" name="<?= $name_ ?>[<?= $key ?>]">
            <img class="img-responsive img-thumbnail" title="<?= $name ?>" src="<?= Attachment::getStaticObject($basePath, $name, AliyunOss::OSS_STYLE_NAME90X90) ?>">
            <em title="删除这张图片" class="close">×</em>
        </div>
        <?php } ?>
    </div>
    <div class="help-block image-block"><i class="fa fa-info-circle" aria-hidden="true"></i> 如果是图片，则第一张为缩略图，建议为正方型，建议尺寸为800左右，并保持图片大小一致，<br />可拖拽排序</div>
</div>




        