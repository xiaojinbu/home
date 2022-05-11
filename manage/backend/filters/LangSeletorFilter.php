<?php

namespace app\filters;

use Yii;
use yii\base\ActionFilter;

/**
 * @author jorry
 */
class LangSeletorFilter extends ActionFilter
{
//    public $defaultLang = 'en-US';
    public $defaultLang = 'zh-CN';

    /**
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     * 返回true下一个filter继续执行，返回false导致action和下一个filter都没有机会执行
     */
    public function beforeAction($action)
    {
        $session = Yii::$app->session;

        if (!$session->has('lang')) {
            $session->set('lang', $this->defaultLang);
        }
//        if(Yii::$app->request->getIsPost()) {
//            //检查传递过来的语言是否正确
//            $lang = Yii::$app->request->post('lang');
//            if(in_array($lang, array_keys(Yii::$app->params['config_languages']))) {
//                $session->set('lang', $lang);
//
//                //跳转
//                $controller = $action->controller;
//                $controller->refresh('#lang');//刷新，重置表单提交
//            }
//        }

        Yii::$app->language = $session->get('lang');

        return true;
    }

    /**
     * @inheritdoc
     * 过滤器是否为激活状态
     */
    protected function isActive($action)
    {
        //业务代码

        return parent::isActive($action);
    }
}
