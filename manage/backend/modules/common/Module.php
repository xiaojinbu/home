<?php

namespace app\modules\common;

/**
 * common module definition class
 */
class Module extends \app\components\Module
{
    /**
     * @inheritdoc
     */
    //默认命名空间和路由在main配置文件中已经配置过了，这里也可以覆盖
    //public $controllerNamespace = 'app\modules\common\controllers';
    //public $defaultRoute = 'home';
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
