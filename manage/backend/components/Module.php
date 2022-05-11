<?php

namespace app\components;

/**
 * 全局统一模块
 */
class Module extends \yii\base\Module
{
    protected static $_normalizeMenus = [];//菜单管理
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
    
    public static function renderMenus($menus)
    {
        //子模板参照方法
        return self::$_normalizeMenus;
    }
}
