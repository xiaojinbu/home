<?php

namespace common\tools\fileupload;

use Yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\UploadedFile;
use common\components\aliyunoss\AliyunOss;
use common\models\tools\Attachment;
use yii\helpers\FileHelper;
use yii\base\Exception;

class FileUploadAction extends Action
{
    public $uploadName= 'file';//接收上传的字段
    
    public $folder = '';//上传到的目录，AliyunOss中有规定
    
    public $safe = false;//是否存储在阿里云公共目录
    
    public  $_safeFolder;
    
    public function init()
    {
        parent::init();
        
        //open csrf
        Yii::$app->getRequest()->enableCsrfValidation = true;
        $this->folder = empty($this->folder)?AliyunOss::OSS_DEFAULT:$this->folder;//默认是default
        
        //创建安全目录（读写，不执行）
        $this->_safeFolder=empty($this->_safeFolder)?Yii::getAlias(Attachment::SAFE_FOLDER):$this->_safeFolder;
        //$this->_safeFolder = Yii::getAlias(Attachment::SAFE_FOLDER);
        if(!is_dir($this->_safeFolder)) {
            FileHelper::createDirectory($this->_safeFolder, 0644);
        }
    }

    /**
     * 执行并处理action
     */
    public function run()
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->uploadName);
        $content = file_get_contents($uploadedFile->tempName);
        $md5 = md5($content.$this->folder);//内容+文件夹
        $time = time();
       // print_r($time);
        //$a=Yii::getAlias(Attachment::SAFE_FOLDER);
        //print_r($a);
        //1.判断本地是否有文件
        if($model = Attachment::find()->where(['md5' => $md5])->one()) {//本地是否存在
            //生成model
           // print_r($time);
        } else {
            //2.上传文件
            try {
           //     print_r($this->safe);
                if($this->safe) {
                    //上传至本地服务器
                    $dir = $this->_safeFolder.$this->folder.'/'.date('Y_m_d', $time).'/';
                    if(!is_dir($dir)) FileHelper::createDirectory($dir);
                    $uploadedFile->saveAs($dir.$md5 . '.' . $uploadedFile->extension);
                } else {//上传是开放oss
                    Yii::$app->aliyunoss->putFile($content, $this->folder, $time, $md5, $uploadedFile->extension);//return boolean
                }
                
                //入库
                $model = new Attachment();
                $model->name = $uploadedFile->name;//中文名
                $model->md5 = $md5;
                $model->auther_table = 'user';//表模型对应的标识
                $model->auther = Yii::$app->user->identity->username;
                $model->auther_id = Yii::$app->user->identity->id;//user是当前merchant的持久层
                $model->folder = $this->folder;
                $model->size = $uploadedFile->size;//bit
                $model->type = $uploadedFile->type;
                $model->ext = $uploadedFile->extension;
                $model->safe = $this->safe;
                $model->created_at = $time;//这个时间，不使用行为behavior生成
                $model->save(false);
                
            } catch (Exception $e) {//HttpException
                return Json::encode(['state' => false, 'msg' => $e->getMessage()]);
            }
        }
        
        $basePath = Yii::$app->aliyunoss->getFullPath();
        $msg = [
            'name' => $model->name,
            'size' => $model->size,//bit
            'type' => $model->type,
            'url' => $this->safe?$model->getObject():$model->getObject($basePath),//保密文件通常是不显示的文件类型
            'thumbnailUrl' => $this->safe?$model->getObject():$model->getObject($basePath, AliyunOss::OSS_STYLE_NAME90X90),
            'objectUrl' => $model->getObject(),//保密文件中，只用到这里的object来获取后缀名
        ];
        
        return Json::encode(['state' => true, 'msg' => $msg]);
    }
}