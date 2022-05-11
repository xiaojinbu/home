<?php
namespace app\bootstrap;

use yii;
use yii\base\Event;
use common\models\adminlog\AdminLog;
use common\models\adminlog\AdminLogTemplate;
use yii\db\ActiveRecord;

class BootstrapAdminLog extends \yii\base\Component implements \yii\base\BootstrapInterface
{

    private static $adminLog;

    public function bootstrap($app)
    {
        if (Yii::$app->user->isGuest)
            return;
        $request = Yii::$app->request;
//         if ($request->isAjax)
//             return;
// fb(urldecode($request->queryString));
        if ($request->queryParams)
            $route = $request->queryParams['r'];
        if (! isset($route) || empty($route) || in_array($route, Yii::$app->params['config_admin_log']))
            return;
        if ($request->isPost)
            $type = AdminLogTemplate::VISIT_TYPE_POST;
        else
            $type = AdminLogTemplate::VISIT_TYPE_GET;
        $currentAdminLogTemplate = AdminLogTemplate::find()->findByRoute($route, $type)->one();
        if (! $currentAdminLogTemplate) {
            $adminLogTemplate = new AdminLogTemplate();
            $adminLogTemplate->route = $route;
            $adminLogTemplate->visit_times = AdminLogTemplate::VISIT_TIMES_DEFAULT;
            $adminLogTemplate->visit_type = $type;
            $adminLogTemplate->save();
        } else {
            if ($currentAdminLogTemplate->status == AdminLogTemplate::STATUS_NO)
                return;
            $currentAdminLogTemplate->visit_times = $currentAdminLogTemplate->visit_times + 1;
            $currentAdminLogTemplate->update();
        }
        self::$adminLog = new AdminLog();
        self::$adminLog->admin_id = Yii::$app->user->identity->id;
        self::$adminLog->ip = $request->userIP;
        self::$adminLog->created_at = time();
        self::$adminLog->route = $route;
        if ($currentAdminLogTemplate)
            self::$adminLog->log_details = $currentAdminLogTemplate->log_describe;
        self::$adminLog->save();
    }
    // 有修改model内容时写入日志表
    public static function writeLog($event)
    {
        // fb($event);
        if ($event->sender instanceof \common\models\adminlog\AdminLog) {
            return;
        }
        $details = '';
        if ($event->name == ActiveRecord::EVENT_AFTER_INSERT) {
            $details = 'insert   id=' . $event->sender->getAttribute('id');
        } elseif ($event->name == ActiveRecord::EVENT_AFTER_UPDATE) {
            $details = 'update   id=' . $event->sender->getAttribute('id') . '  ';
            if (! empty($event->changedAttributes)) {
                foreach ($event->changedAttributes as $name => $value) {
                    if ($value != $event->sender->getAttribute($name)) {
                        $details .= $name . ':' . $value . '=>' . $event->sender->getAttribute($name) . ',';
                    }
                }
            }
        } else {
            $details = 'delete   id=' . $event->sender->getAttribute('id');
        }
        self::$adminLog->change_data = $details;
        self::$adminLog->update();
    }
}