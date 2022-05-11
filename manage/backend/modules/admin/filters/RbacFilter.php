<?php

namespace app\modules\admin\filters;

use Yii;
use yii\web\User;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

use app\modules\admin\helpers\Helper;

/**
 * 用法与用量：过滤器中只有以下返回：1.return;    2.return true;     3.return false;   三种
 * AccessControl provides simple access control based on a set of rules.
 * RBAC权限过滤器
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'access' => [
 *             'class' => \app\modules\admin\filters\RbacFilter::className(),
 *         ],
 *     ];
 * }
 * ```
 */
class RbacFilter extends ActionFilter
{
    /**
     * ```php
     * function ($action)
     * ```
     */
    public $denyCallback;
    
    public function init()
    {
        parent::init();
        
        //nothing
    }
    
    /**
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     * 上述过滤器激活，isActive返回true时，过滤器有效执行beforeAction
     */
    public function beforeAction($action)//返回true下一个filter继续执行，返回false导致action和下一个filter都没有机会执行
    {
        if(!Yii::$app->getUser()->isGuest && Yii::$app->getUser()->getIdentity()->getIsAdmin()) {
            return true;
        }
        
        //判断路由或者自定义标识
        $param = [
            'getParam' => Yii::$app->getRequest()->get(),
            'route' => $this->getActionId($action),
        ];
        if (Helper::checkRoute('/' . $this->getActionId($action), $param, Yii::$app->user)) {
            return true;
        }
        
        //未审核，回调记录点什么。。。。。
        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, $action);
        }
        
        //临时使用
//         if(YII_DEBUG) {
//             return true;
//         }
        
        $user = Yii::$app->user;
        if ($user->getIsGuest()) {//未登录
            //标识response为跳转，注意整个框架不仅没有在此处中断，而且还完整的执行了响应，只是这个响应是个header跳转而已
            //这个响应的过程同样要完成正常的layout而已和action执行，因为在layout和所有view模板中，遇到user is guest时，都要return;
            $user->loginRequired(true, false);
            return true;
        }
        
        throw new ForbiddenHttpException(Yii::t('backend', Yii::t('backend', 'You are not allowed to perform this action.')));
    }
    
    /**
     * @inheritdoc
     * 过滤器是否为激活状态：
     * 仅代表filter是否执行，即是否允许将要执行的filter执行！它并不终止action的执行
     */
    protected function isActive($action)
    {
        //allowAction，任何控制器中定义了allowAction方法，然后返回对应的数组控制器，同样可以实现避开权限认证
        //allowAction方法返回路由的全路径数组！
        if ($action->controller->hasMethod('allowAction') && in_array($action->id, $action->controller->allowAction())) {
            return false;//允许，则过滤器被忽略
        } else {
            return parent::isActive($action);//使only和expect保持有效
        }
    }
}