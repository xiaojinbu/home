<?php
/**
 * @link http://www.sunsult.com/
 * @copyright Copyright (c) 2008 sunsult Software LLC
 * @license
 */

namespace app\bootstrap;

use Yii;
use yii\base\InvalidConfigException;
use common\models\system\Setting;
use yii\helpers\ArrayHelper;


/**
 * @author jorry
 * @since 1.0
 */

//普通的导入类方式
class BootstrapConfig extends \yii\base\Component implements \yii\base\BootstrapInterface
{
    /**
     * @param Application $app
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        if(!$this->initc()) {
            throw new InvalidConfigException(Yii::t('backend', 'InitConfig::initc Failed To Initialize!'));
        }
    }
    
    /**
     * 初始化系统配置
     */
    protected function initc()
    {
        Yii::$app->params = ArrayHelper::merge(Yii::$app->params, (new Setting)->getCache());
        
        //配置系统的权限模式
        //采用正式的rbac授权模式
//         if(Yii::$app->params['config_permission_mode'] == 'user_group') {//配置为按组授权模式
//             $auth = Yii::$app->authManager;
//             //默认将所有的包含有员工组的角色设置为默认！！！
//             foreach ($auth->getRoles() as $rule) {
//                 if($rule->ruleName == Yii::$app->params['config_user_group_rule']) {
//                     $auth->defaultRoles[] = $rule->name;
//                 }
//             }
//         }
        return true;
    }
}






