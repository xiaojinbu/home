<?php

namespace app\modules\common\controllers;

use yii\web\ErrorAction;

class SiteController extends \app\components\Controller
{
    public function actions()
    {
        return [
            'error' => [
                //此提醒是面向用户的：
                //非debug或者是用户异常时有效，以正常的路由执行来显示错误【用户异常即http请求过程中与用户操作相关的所有http异常】
                'class' => ErrorAction::className(),
                'view' => 'user_error',
            ],
        ];
    }
    
    //维护模式
    public function actionOffline()
    {
        return $this->render('offline');
    }
}
