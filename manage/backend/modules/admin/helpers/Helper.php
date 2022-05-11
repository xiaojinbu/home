<?php

namespace app\modules\admin\helpers;

use common\models\admin\AuthTable;
use common\models\admin\RoleData;
use Yii;
use yii\web\User;
use yii\helpers\ArrayHelper;
use common\models\admin\Field;
use common\models\admin\RoleField;

/**
 * Description of Helper
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.3
 */
class Helper
{
    private static $_userRoutes = [];
    private static $_defaultRoutes;
    private static $_routes;

    public static function getRegisteredRoutes()
    {
        if (self::$_routes === null) {
            self::$_routes = [];
            $manager = Yii::$app->getAuthManager();
            foreach ($manager->getPermissions() as $item) {
                if ($item->name[0] === '/') {
                    self::$_routes[$item->name] = $item->name;
                }
            }
        }
        return self::$_routes;
    }

    /**
     * Get assigned routes by default roles
     * @return array
     */
    protected static function getDefaultRoutes()
    {
        if (self::$_defaultRoutes === null) {
            $manager = Yii::$app->getAuthManager();
            $roles = $manager->defaultRoles;
            $permissions = self::$_defaultRoutes = [];
            foreach ($roles as $role) {
                $permissions = array_merge($permissions, $manager->getPermissionsByRole($role));
            }
            foreach ($permissions as $item) {
                if ($item->name[0] === '/') {
                    self::$_defaultRoutes[$item->name] = true;
                }
            }
        }
        return self::$_defaultRoutes;
    }

    /**
     * Get assigned routes of user.
     * @param integer $userId
     * @return array
     */
    public static function getRoutesByUser($userId)
    {
        if (!isset(self::$_userRoutes[$userId])) {
            $routes = static::getDefaultRoutes();
            $manager = Yii::$app->getAuthManager();
            foreach ($manager->getPermissionsByUser($userId) as $item) {
                if ($item->name[0] === '/') {
                    $routes[$item->name] = true;
                }
            }
            self::$_userRoutes[$userId] = $routes;
        }
        return self::$_userRoutes[$userId];
    }

	/**
	 * 判断路由权限
	 * @param string $route  路由
	 * @param null $user 用户句柄
	 * @return bool 是否有权限
	 */
    public static function checkRoute2($route, $user = null)
	{
		$params = [
			'getParam' => Yii::$app->getRequest()->get(),
			'route' => '',
		];
		return self::checkRoute($route, $params, $user);
	}
    /**
     * Check access route for user.
     * @param string|array $route
     * @param integer|User $user
     * @return boolean
     */
    public static function checkRoute($route, $params = [], $user = null)
    {
        if(!Yii::$app->getUser()->isGuest && Yii::$app->user->identity->getIsAdmin()) {
            return true;
        }

        if (!Yii::$app->getUser()->isGuest && Yii::$app->user->identity->isAssignments('企业管理员')) {
        	return true;
		}
        
//         if(YII_DEBUG) {
//             return true;
//         }
        
        if ($user === null) {
            $user = Yii::$app->getUser();
        }
        
        $userId = $user instanceof User ? $user->getId() : $user;
        
        //路由类型的permission检测
        $r = static::normalizeRoute($route);

        if ($user->can($r, $params)) {//检测get参数
            return true;
        }
        while (($pos = strrpos($r, '/')) > 0) {
            $r = substr($r, 0, $pos);
            if ($user->can($r . '/*', $params)) {
                return true;
            }
        }
        
        return $user->can('/*', $params);
    }

    protected static function normalizeRoute($route)
    {
        if ($route === '') {
            return '/' . Yii::$app->controller->getRoute();
        } elseif (strncmp($route, '/', 1) === 0) {
            return $route;
        } elseif (strpos($route, '/') === false) {
            return '/' . Yii::$app->controller->getUniqueId() . '/' . $route;
        } elseif (($mid = Yii::$app->controller->module->getUniqueId()) !== '') {
            return '/' . $mid . '/' . $route;
        }
        return '/' . $route;
    }

    /**
     * Filter menu items
     * @param array $items
     * @param integer|User $user
     */
    public static function filter($items, $user = null)
    {
        if ($user === null) {
            $user = Yii::$app->getUser();
        }
        return static::filterRecursive($items, $user);
    }

    /**
     * Filter menu recursive
     * @param array $items
     * @param integer|User $user
     * @return array
     */
    protected static function filterRecursive($items, $user)
    {
        $result = [];
        foreach ($items as $i => $item) {
            $url = ArrayHelper::getValue($item, 'url', '#');
            $allow = is_array($url) ? static::checkRoute($url[0], array_slice($url, 1), $user) : true;

            if (isset($item['items']) && is_array($item['items'])) {
                $subItems = self::filterRecursive($item['items'], $user);
                if (count($subItems)) {
                    $allow = true;
                }
                $item['items'] = $subItems;
            }
            if ($allow) {
                $result[$i] = $item;
            }
        }
        return $result;
    }

    /**
     * Filter action column button. Use with [[yii\grid\GridView]]
     * ```php
     * 'columns' => [
     *     ...
     *     [
     *         'class' => 'yii\grid\ActionColumn',
     *         'template' => Helper::filterActionColumn(['view','update','activate'])
     *     ]
     * ],
     * ```
     * @param array|string $buttons
     * @param integer|User $user
     * @return string
     */
    public static function filterActionColumn($buttons = [], $user = null)
    {
        if (is_array($buttons)) {
            $result = [];
            foreach ($buttons as $button) {
                if (static::checkRoute($button, [], $user)) {
                    $result[] = "{{$button}}";
                }
            }
            return implode(' ', $result);
        }
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($user) {
            return static::checkRoute($matches[1], [], $user) ? "{{$matches[1]}}" : '';
        }, $buttons);
    }

	/**
	 * 判断字段权限
	 * @param string $field  验证字段
	 * @param string $parent 父级字段
	 * @return array
	 */
    public static function checkAuthField($field,$parent)
    {

        if (Yii::$app->user->identity->getIsAdmin() || Yii::$app->user->identity->isAssignments('企业管理员')){
            return ['state' => true,'is_view' => true,'is_update' => true];
        }
        $company_id = Yii::$app->getUser()->identity->company_id;
        $user_id = Yii::$app->getUser()->getId();
        $cache_key = 'auth_fields_'.$field.'_'.$parent.'_'.$company_id.'_'.$user_id;

        return Yii::$app->cache->getOrSet($cache_key, function () use ($field, $parent,$company_id) {
			//查询父级id
			$parent_id = Field::find()->select('id')->where(['field' => $parent,'pId'=>0])->asArray()->one();

			$field_id = Field::find()->select('id')->where(['field' => $field ,'pId' => $parent_id['id']])->asArray()->one();
			$user_id = Yii::$app->user->id;
			$role_res = Yii::$app->authManager->getAssignments($user_id);
			$role = array_keys($role_res);
			$orWhere = ['or'];
			foreach ($role as $value){
				$orWhere[] = ['role'=>$value];
			}
			$role_data = RoleField::find()->andWhere($orWhere)->andWhere(['auth_field_id' => $field_id['id'],'company_id'=>$company_id])->asArray()->all();
			$data = ['state' => false, 'is_view' => false,'is_update' => false];

			if ($role_data){
				$data['state'] = true;

				foreach ($role_data as $v){
					if ($v['is_view']){
						$data['is_view'] = true;
					}
					if ($v['is_update']){
						$data['is_update'] = true;
					}
				}
			}
			return $data;
		}, Yii::$app->params['bu_cache_time']);

    }

    /**
     * 打印输出调试信息
     * @param string $data
     * @param bool|true $exit
     * @param bool|true $php_code
     */
    public static function tbug($data = '', $exit = true, $php_code = false)
    {
//        header('Content-type: text/html; charset=utf-8');
        echo '<pre/>';
        if (is_array($data) && $php_code) {
            echo 'Array('.count($data).') =><br>';
            echo '[<br>';
            foreach ($data as $k => $v) {
                echo sprintf('    \'%s\' => \'%s\',<br>', $k, $v);
            }
            echo '];';
        }elseif (is_bool($data)){
            echo '('.gettype($data).')';
            echo $data ? 'true':'false';
        }elseif (is_string($data) || is_int($data)){
            echo 'Leng('.strlen($data).') = (' .gettype($data).')'.$data;
        } else {
            echo 'Count() =>'.count($data).'<br>';
            print_r($data);
        }
        !$exit || exit ();
    }

    /**
	 * 验证数据权限（返回数组）
     * @param string $table 数据所属表
     * @param string $table_alias 连表别名
     * @param string $field 查询字段
     * @return array
     */
    public static function dataAuthArray($table)
    {
        //管理员拥有全部查询权限
        if(!Yii::$app->getUser()->isGuest && (Yii::$app->getUser()->getIdentity()->getIsAdmin() || Yii::$app->user->identity->isAssignments('企业管理员'))) {
            return ['is_root' => true, 'data' =>[1=>1]];
        }
        $company_id = Yii::$app->getUser()->getIdentity()->company_id;

        $cache_key = 'auth_array_'.$table.'_'.$company_id.'_'.Yii::$app->user->id;

        return Yii::$app->cache->getOrSet($cache_key, function () use ($table) {
			//获取当前登录用户角色
			$role_res = Yii::$app->authManager->getAssignments(Yii::$app->user->id);
			$role = array_keys($role_res);

			$table = Yii::$app->db->tablePrefix.$table;
			$table_res = AuthTable::find()->select(['id'])->where(['table_name' => $table])->asArray()->one();
			$auth_role_data = RoleData::find()->where(['role' => $role,'table_id' => $table_res['id']])->asArray()->self()->all();

			if (!$auth_role_data){
				return ['is_root' => true, 'data' => [1=>1]];
			}

			$data_id = array_column($auth_role_data,'data_id');

			return ['is_root' => false, 'data' =>$data_id];

		}, Yii::$app->params['bu_cache_time']);
    }

    /**判断字符串开头
     * @param string $str  源字符串
     * @param string $key  搜索关键词
     * @return bool
     */
    public static function startwith($str,$key) {
        if(strpos($str,$key) === 0)
            return true;
        else
            return false;
    }

    /**
     * @param string $type(this_month|当月 seven_days|七天)时间周期
     * @return array
     */
    public static function indexTimeSlot($type='this_month')
    {
        $data = [];

        if ($type == 'seven_days'){
            $end_day = date('Y-m-d',time());
            $time = strtotime($end_day);
            $start_time = strtotime("-6 day",$time);
            $data['start_time'] = $start_time;
            $data['start_day'] = date('Y-m-d',$start_time);
            $data['end_time'] = $time + 86399;
            $data['end_day'] = $end_day;
        }
        if ($type == 'this_month'){
            $start_day = date('Y-m',time()).'-1';

            $data['start_time'] = strtotime($start_day);
            $data['end_time'] = time();
            $data['start_day'] = $start_day;
            $data['end_day'] = date('Y-m-d', time());
        }
        return $data;
    }
    /**x轴坐标时间
     * @param $start_time 开始时间
     * @param $end_time 结束时间
     * @return $time_axis 时间轴
     */
    public static function timeAxis($start_time,$end_time)
    {
        $time_axis = [];
        $days = ($end_time - $start_time)/86400;
        for ($i = 0; $i < $days; $i++){
            $time_axis['day'][] = date('Y-m-d',$i*86400 + $start_time);
            $time_axis['time'][] = $i*86400 + $start_time;
        }
      return $time_axis;
    }

    /**数组转化为字符串，格式化输出
     * @param $array
     */
    public static function arrayToStringFormat($array)
    {
       $res = '';
       if (is_array($array)){
           foreach ($array as $key => $value){
               if (is_array($value)){
                   foreach ($value as $k => $v){
                       $res.='['.$v.']';
                   }
               }else{
                   $res.='['.$value.']';
               }
           }
       }else{
           $res .='['.$array.']';
       }

       return $res;
    }
    /**
     * 生成22位的订单号,必须要有公司ID
     * @param $orderHead 订单类型，从"common\models\finance\Finance里面取，常量开头"ORDER_HEAD_"
     * @return string 22位的订单号
     * @throws \Throwable
     */
    public static function createOrderNo($orderHead) {
        $companyId=Yii::$app->getUser()->getIdentity()->company_id;
        $companyIdStr=strval($companyId);
        $companyIdStr= sprintf('%06s', $companyIdStr);
        $companyIdStr=substr($companyIdStr,0,6);
        $orderNo=$orderHead.time().$companyIdStr.rand(1000,9999);
        return $orderNo;
    }

}
