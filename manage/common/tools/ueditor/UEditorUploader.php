<?php

namespace common\tools\ueditor;

use Yii;
use yii\web\UploadedFile;
use common\models\tools\Attachment;
use yii\web\HttpException;
use yii\helpers\Json;

class UEditorUploader {
    private $field; // 文件域名
    private $config; // 配置信息
    private $folder;
    
    private $originalName; // 原始文件名
    private $title; // 新文件名
    private $url; // 完整文件名,即从当前配置目录开始的URL
    private $size; // 文件大小
    private $type; // 文件类型
    private $state = 'SUCCESS'; // 上传状态信息,默认为成功
    
    /**
     * 构造函数
     * 是否解析base64编码，可省略。若开启，则$field代表的是base64编码的字符串表单名
     * @param $field 表单名称            
     * @param $config 配置项            
     * @param string $type
     */
    public function __construct($field, $config, $folder, $mode = 'upload') {
        $this->field = $field;
        $this->config = $config;
        $this->folder = $folder;
        
        if($mode == 'remote') {
            $this->saveRemote();//抓取远程图片
        } else if($mode == 'base64') {
            $this->uploadBase64(); // 涂鸦编码
        } else if($mode == 'upload') {
            $this->uploadFile(); // 正常的文件
        }
    }
    
    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    protected function uploadFile() {
        $uploadedFile = UploadedFile::getInstanceByName($this->field);
        
        //文件未找到
        if(!$uploadedFile) {
            $this->state = Yii::t('common', 'Not found file.');
            return false;
        }
        
        //上传过程中的错误
        if($uploadedFile->getHasError()) {
            $this->state = $this->getServerError($uploadedFile->error);
            return false;
        }
        
        //配置文件大小限制
        if($uploadedFile->size > $this->config['maxSize']) {
            $this->state = Yii::t('common', 'File size beyond ueditor limit');
            return false;
        }
        
        //文件类型检查
        if(!in_array('.'.$uploadedFile->getExtension(), $this->config['allowFiles'])) {
            $this->state = Yii::t('common', 'UEditor not allowed file types');
            return false;
        }
        
        $this->originalName = $uploadedFile->name;
        $this->size = $uploadedFile->size;
        $this->type = $uploadedFile->type;//image/jpeg
        
        $content = file_get_contents($uploadedFile->tempName);
        $md5 = md5($content.$this->folder);//内容+文件夹
        $time = time();
        //上传到aliyun
        $model = $this->putFileAliyunOss($content, $this->folder, $md5, $time, $uploadedFile->extension);//返回Attachment Model
        $this->url = $model->getObject(Yii::$app->aliyunoss->getFullPath());
        $this->title = $model->name;
    }
    
    /**
     * 处理base64编码的图片上传
     * 
     * @return mixed
     */
    protected function uploadBase64() {
        $base64Data = Yii::$app->getRequest()->post($this->field);
        if($base64Data) {
            $img = base64_decode($base64Data);//图片内容，png
            
            $this->size = strlen($img);
            //配置文件大小限制
            if($this->size > $this->config['maxSize']) {
                $this->state = Yii::t('common', 'File size beyond ueditor limit');
                return false;
            }
            
            $this->originalName = $this->config['oriName'];//涂鸦原始名称
            $this->type = 'image/png';
            
            $uid = uniqid(time(), true);
            $fileName = $uid . '.png';
            $object = $this->folder.'/scrawl/'.date('Y_m_d').'/'.$fileName;//涂鸦文件夹
            $object = Yii::$app->aliyunoss->addTestToName($object);
                
            //上传到aliyun
            Yii::$app->aliyunoss->putFile($object, $img);
            
            $fullUrl = Yii::$app->aliyunoss->getFullPath().$object;
            
            $this->url = $fullUrl;//重命名文件
            $pathinfo = pathinfo($this->url);
            $this->title = $pathinfo['basename'];
        } else {
            $this->state = Yii::t('common', 'Not found file..');
            return false;
        }
    }
    
    /**
     * 拉取远程图片
     * 
     * @return mixed
     */
    protected function saveRemote() {
        $imgUrl = htmlspecialchars($this->field);
        $imgUrl = str_replace('&amp;', '&', $imgUrl);
        
        // http开头验证
        if(strpos($imgUrl, 'http') !== 0) {
            $this->state = Yii::t('common', 'Http link error');
            return false;
        }
        
        // 获取请求头并检测死链
        //解析http head
        $imgHeader = [];
        $fp = fopen($imgUrl, 'r');
        $heads = stream_get_meta_data($fp);
        $heads = $heads['wrapper_data'];
        
        //获取请求头并检测死链
        if(!(stristr($heads[0], '200') && stristr($heads[0], 'OK'))) {
            $this->state = Yii::t('common', 'Http head error');
            return false;
        }
        
        //格式验证(扩展名验证)
        $ext = strtolower(strrchr($imgUrl, '.'));
        if(!in_array($ext, $this->config['allowFiles'])) {
            Yii::t('common', 'UEditor not allowed file types');
            return false;
        }
        
        // 打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create([ 
                'http' =>[ 
                    'follow_location' => false 
                ] // don't follow redirects
        ]);
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        
        preg_match('/[\/]([^\/]*)[\.]?[^\.\/]*$/', $imgUrl, $m);
        
        $this->size = strlen($img);
        //配置文件大小限制
        if($this->size > $this->config['maxSize']) {
            $this->state = Yii::t('common', 'File size beyond ueditor limit');
            return false;
        }
        
        $this->originalName = $m?$m[1]:'';
        $this->type = $ext;
        
        $md5 = md5($img.$this->folder);//内容+文件夹
        $time = time();
        //上传到aliyun
        $model = $this->putFileAliyunOss($img, $this->folder, $md5, $time, $ext);//返回Attachment Model
        
        $this->url = $model->getObject(Yii::$app->aliyunoss->getFullPath());
        $this->title = $model->name;
    }
    
    /**
     * 获取服务器级别错误
     * @param $errCode 来自服务器上的错误代码
     * @return string
     */
    private function getServerError($errCode)
    {
        $info = [
            UPLOAD_ERR_OK => Yii::t('common', 'Upload success'),
            UPLOAD_ERR_INI_SIZE => Yii::t('common', 'File size over server size configuration'),
            UPLOAD_ERR_FORM_SIZE => Yii::t('common', 'File size over form size configuration'),
            UPLOAD_ERR_PARTIAL => Yii::t('common', 'Local server error'),
            UPLOAD_ERR_NO_FILE => Yii::t('common', 'Not found upload file'),
            UPLOAD_ERR_NO_TMP_DIR => Yii::t('common', 'Not found tmp file'),
            UPLOAD_ERR_CANT_WRITE => Yii::t('common', 'Tmp file cannot be read'),
            UPLOAD_ERR_EXTENSION => Yii::t('common', 'File type was limited by the server'),
        ];
        
        return !empty($info[$errCode])?$info[$errCode]:Yii::t('common', 'Unknown Error');
    }
    
    /**
     * 获取当前上传成功文件的各项信息
     * 
     * @return array
     */
    public function getFileInfo() {
        return array(
            'state' => $this->state,//上传状态，上传成功时必须返回'SUCCESS'
            'url' => $this->url,//返回的真实文件地址
            'title' => $this->title,//新文件名
            'original' => $this->originalName,//原始文件名
            'type' => $this->type,//文件类型
            'size' => $this->size, //文件大小
        );
    }
    
    /**
     * 过滤所有通过此插件上传的文件，
     * 从而实现localhost和域名的形式都可以正常显示文件
     */
    public static function filterContentSrc($content)
    {
        return $content;
    }
    
    protected function putFileAliyunOss($content, $folder, $md5, $time, $ext)
    {
        //1.判断本地是否有文件
        if($model = Attachment::find()->where(['md5' => $md5])->one()) {//本地是否存在
            //生成model
        } else {
            //2.上传文件
            try {
                Yii::$app->aliyunoss->putFile($content, $folder, $time, $md5, $ext);//return boolean
                //入库
                $model = new Attachment();
                $model->name = $this->originalName;//中文名
                $model->md5 = $md5;
                $model->auther_table = 'user';//表模型对应的标识
                $model->auther = Yii::$app->user->identity->username;
                $model->auther_id = Yii::$app->user->identity->id;//user是当前merchant的持久层
                $model->folder = $folder;
                $model->size = $this->size;//bit
                $model->type = $this->type;
                $model->ext = $ext;
                $model->created_at = $time;//这个时间，不使用行为behavior生成
                $model->save(false);
                
            } catch (HttpException $e) {
                $this->state = $e->getMessage();
                return false;
            }
        }
        
        return $model;
    }
}