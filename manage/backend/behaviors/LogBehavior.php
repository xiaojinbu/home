<?php

namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Application;
use yii\rbac\Permission;
use common\models\admin\Log;
use yii\helpers\Json;

class LogBehavior extends Behavior
{
    public $refreshTime = 1000;//多少ms算重复请求不计入日志
    
    public $isGetData = true;//是否要插入get请求数据到日志
    
    public $isPostData = true;//是否要插入post请求数据到日志
    
    public $exceptModule = ['debug', 'gii', 'log', 'queue', 'notify'];//忽略模块
    
    public $exceptController = [];//忽略控制器
    
    public $exceptAction = [];//忽略动作
    
    public function init()
    {
        parent::init();
        
        //print_r('test');
        
    }
    
    public function events()
    {
        return [
            Application::EVENT_BEFORE_ACTION => 'beforeAction',//view yii\base\ActionEvent
            Application::EVENT_AFTER_ACTION => 'afterAction',//view yii\base\ActionEvent
        ];
    }
    
    public function beforeAction($event)
    {
        $auth = Yii::$app->getAuthManager();
        
        $event->isValid = true;// 后续有效
        //$event->result;
        
        $action = $event->action;
        $controller = $event->action->controller;
        $module = $event->action->controller->module;
        
        $route = '/'.$controller->route;
        $croute = '/'.$module->id.'/'.$controller->id.'/*';
        $mroute = '/'.$module->id.'/*';
        
        //从小范围到大范围
        if($permission = $auth->getPermission($route)) {
            //
        } elseif($permission = $auth->getPermission($croute)) {
            //
        } elseif($permission = $auth->getPermission($mroute)) {
            //
        } else {
            Yii::warning($route.' 此路由没有配置权限！', '操作日志');
        }
        
        if(!is_null($permission)) {
            //写入操作日志
            if($this->except($permission->name) || Yii::$app->getUser()->isGuest) {
                return;
            } else {
                $this->addLog($permission);
            }
        }
    }
    
    /**
     * 此方法对外开发，主要是处理那些未登录之前，比如login的动作
     * @param Permission $perm
     */
    public function addLog(Permission $perm)
    {
        $request = Yii::$app->request;
        
        $ip = $request->getUserIP();
        $agent = $request->getUserAgent();
        $md5 = md5($agent.$ip.(is_null($perm->name)?'空':$perm->name).Yii::$app->getUser()->id);//代理+IP+权限名+当前操作者
        
        $logCache = Yii::$app->cache->get('log_duration'.(is_null($perm->name)?'空':$perm->name));
        if($logCache && ($logCache['md5'] == $md5) && (microtime(true)*1000 - $logCache['t'] < $this->refreshTime)) {//采用调整缓存，不用数据库查询
            //重复刷新，nothing
        } else {
            $model = new Log();
            
            $model->user_id = Yii::$app->getUser()->id;
            $model->user_name = Yii::$app->getUser()->getIdentity()->username;
            
            $model->route = is_null($perm->name)?'空':$perm->name;
            $model->name = is_null($perm->description)?'空':$perm->description;
            
            $model->method = $request->method;
            $model->get_data = $this->isGetData?Json::encode($request->get()):'';
            $model->post_data = ($request->isPost && $this->isPostData)?Json::encode($request->post()):'';
            $model->ip = $ip;
            $model->agent = $agent;
            $model->md5 = $md5;
            $model->created_at = microtime(true);
            
            $model->save(false);//不用验证，直接入库
            
            Yii::$app->cache->set('log_duration'.(is_null($perm->name)?'空':$perm->name), [
                't' => microtime(true)*1000,
                'md5' => $md5
            ]);
        }
    }
    
    protected function except($route)
    {
        foreach ($this->exceptModule as $module) {//忽略的模块
            if(strstr($route, '/'.$module.'/')) {
                return true;
            }
        }
        
        foreach ($this->exceptController as $controller) {//忽略控制器
            if(strstr($route, '/'.$controller.'/')) {
                return true;
            }
        }
        
        foreach ($this->exceptAction as $action) {//忽略动作
            if(strstr($route, '/'.$action)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function afterAction($event)
    {
        $event->isValid = true;// 后续有效
        
        $action = $event->action;
        $controller = $event->action->controller;
        $module = $event->action->controller->module;
        
        $route = $controller->route;
        
        $event->result;
        
        //var_dump($route);
        
        //var_dump($module->id.'/'.$controller->id.'/'.$action->id);
    }
}