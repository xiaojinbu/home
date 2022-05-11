<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\VerbFilter;
use common\models\admin\form\Login;
use app\components\Controller;


/**
 * User controller
 */
class UserController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    

    /**
     * Login
     * @return string
     */
    public function actionLogin()
    {
        Yii::$app->layout = 'login-main';//当前模块使用指定布局
      
        if (!Yii::$app->getUser()->isGuest) {
            return $this->goHome();//正常到登录后的首页
        }

        $model = new Login();
        $model->load(Yii::$app->getRequest()->post());
        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {//登录操作
           
            return $this->goBack();
        } else {
        
            //登录界面
            return $this->render('login', [
                    'model' => $model,
            ]);
        }
    }

    /**
     * Logout
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        
        return Yii::$app->response->redirect(Yii::$app->user->loginUrl);
    }


}
