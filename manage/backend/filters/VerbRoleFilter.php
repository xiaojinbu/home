<?php

namespace app\filters;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use app\modules\bu\Module;

/**
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'verbs' => [
 *             'class' => app\filters\VerbRoleFilter::className(),
 *             'actions' => ['index', 'view', 'create', 'update', 'delete'],
 *         ],
 *     ];
 * }
 * ```
 * 强制ajax请求
 */
class VerbRoleFilter extends Behavior
{
    /**
     * For example,
     * ```php
     * [
     *   'create', 'update', 'delete', '*'
     * ]
     * ```
     */
    public $actions = [];
    
    public $role;


    /**
     * Declares event handlers for the [[owner]]'s events.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    /**
     * @param ActionEvent $event
     * @return bool
     * @throws ForbiddenHttpException when the request method is not allowed.
     */
    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (!empty($this->actions)) {
            if(in_array('*', $this->actions) || in_array($action, $this->actions)) {
                
            }
        }
        
        //为空或者不在指定范围内，不受限制，直接返回
        return $event->isValid;
    }
}
