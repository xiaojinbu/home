<?php

namespace common\tools\webuploader;

use Yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\UploadedFile;
use common\components\aliyunoss\AliyunOss;

class WebuploaderAction extends Action
{
    public $name = '';//接收上传的字段
    
    public $folder = '';//上传到的目录，AliyunOss中有规定
    
    public function init()
    {
        parent::init();
        
        //open csrf
        Yii::$app->getRequest()->enableCsrfValidation = true;
        
        $this->name = empty($this->name)?'file':$this->name;//默认是file
        $this->folder = empty($this->folder)?AliyunOss::OSS_CMS:$this->folder;//默认是cms
    }

    /**
     * 执行并处理action
     */
    public function run()
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->name);
        
        $object = Yii::$app->aliyunoss->putFile($this->folder, $uploadedFile);
        $fullUrl = Yii::$app->aliyunoss->getFullPath().$object;
        
        //$rule = '?x-oss-process=image/resize,m_pad,w_100,h_100,limit_0/auto-orient,0/quality,q_90';//不使用缩略图
        
        return Json::encode([
            'name' => $uploadedFile->name,
            'size' => $uploadedFile->size,
            'relativeUrl' => $object,//相对路径，存储在数据库
            'fullUrl' => $fullUrl,//全路径，显示出来
            //'thumbnailUrl' => $fullUrl.$rule,//缩略图//缩略图在url上自动设置！！！！！
        ]);
    }
}