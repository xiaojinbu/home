<?php
/**
 * Created by PhpStorm.
 * User: LiuRongKe
 * Date: 2019-10-28
 * Time: 下午 3:42
 */

namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;

class NoCsrf extends Behavior
{
    public $actions = [];
    public $controller;
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }
    public function beforeAction($event)
    {
        $action = $event->action->id;
        if(in_array($action, $this->actions)){
            $this->controller->enableCsrfValidation = false;
        }
    }
}