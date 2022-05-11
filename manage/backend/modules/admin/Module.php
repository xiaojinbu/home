<?php

namespace app\modules\admin;

use Yii;
use app\modules\admin\helpers\Helper;

/**
 * 每一个模块生成一个对应的菜单
 */
class Module extends \app\components\Module
{
    public function init()
    {
        parent::init();
        //nothing
    }
    
    /**
     * Get avalible menu.
     * @return array
     */
    public static function renderMenus($menus)
    {
        $user = Yii::$app->user;
        $params = [
            'getParam' => Yii::$app->getRequest()->get(),
            'route' => '',
        ];
      
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            //nothing
            
            return true;
        }
        return false;
    }
}
