<?php
/**
 * 状态开关[当然只支持两个值]
 */

namespace common\tools\switchery;

use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\HttpException;

/**
 * 将指定字段修改为指定内容，并跳转到指定路由
*/
 
/*
 * 使用方法：
 * public function actions()
 {
     return [
         'status' => [
             'class' => 'common\tools\switchery\SwitcheryAction',
             'className' => Test::className(),
             'collect' = [Test::STATUS_NO, Test::STATUS_YES],//允许的两个值
             'id' => Yii::$app->getRequest()->get('id'),
             'feild' => 'status',
             'activeValue' => Test::STATUS_YES,//活动状态的值，反之就是禁止状态的值
         ],
     ];
 } */
 
class SwitcheryAction extends Action
{
    public $className;//要切换的模型
    public $id;//主键id值
    public $collect = [];//数据集，只支持两个值！！！
    
    public $feild = 'status';//指定要修改的字段名[可选]
    public $value;//默认值,指定一个值[可选]
    
    public function init()
    {
        parent::init();
        
        //保证以ajax进行访问
        if(!Yii::$app->request->getIsAjax()) {
            throw new HttpException(Yii::t('common', 'SwitcheryAction->Request Type Error,not ajax'));
        }
        
        //目前只做ajax
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
    
    public function run()
    {
        //校验参数
        if(empty($this->className) || empty($this->id) || !in_array($this->value, $this->collect)) {
            throw new InvalidParamException(Yii::t('common', 'Parameter Error.'));
        }
        
        //状态切换
        //$model = $this->findModel($this->id);
        //$model->{$this->feild} = $newValue;
        
        $newValue = (($this->value == $this->collect[0])?$this->collect[1]:$this->collect[0]);//取反值
        $className = $this->className;
        $command = Yii::$app->getDb()->createCommand();
        $params = [];
        $command->update($className::tableName(), [$this->feild => $newValue], ['id' => $this->id], $params);
        $command->execute();
        return ['state' => true, 'msg' => $newValue];//成功返回新值
        
        /*
        if($model->save(false)) {//跳过模型
            return ['state' => true, 'msg' => $newValue];//成功返回新值
        } else {
            return ['state' => false, 'msg' => '修改失败'];
        }
        */
        //return Yii::$app->getResponse()->redirect(Url::to([$this->route]));
    }
    
    /*
    protected function findModel($id)
    {
        $className = $this->className;
        
        if (($model = $className::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    */
}