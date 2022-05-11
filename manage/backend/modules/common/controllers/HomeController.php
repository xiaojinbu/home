<?php

namespace app\modules\common\controllers;

use app\modules\admin\helpers\Helper;
use common\models\common\BaSvRegister;
use common\models\common\SalesRank;
use common\models\common\ShopTypeRate;
use common\models\common\StageCompleRate;
use common\models\common\SvChart;
use common\models\manage\Messenger;
use common\models\manage\MessengerRole;
use common\models\manage\Servicer;
use common\models\manage\ServicerBrand;
use common\models\manage\Shop;
use common\models\sv\Detail;
use Yii;
use yii\web\Response;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\models\system\Company;
use common\models\purchase\CustomerProcurement;
use common\models\purchase\CustomerProcurementDetail;
use common\models\purchase\supply\Supply;
use common\models\purchase\PurchaseOrder;
use common\models\endorsement\TaskDetail;
use common\models\endorsement\Task;
use common\models\endorsement\TaskReceive;
use PHPUnit\Exception;
use common\models\system\NoticePushTool;
use common\models\catalog\Brand;
use Mpdf\Tag\Time;
use common\models\catalog\Product;
use common\models\manage\ShopType;
use common\models\bu\Item;
use common\models\bu\flow\FlowData;
use common\models\broker\BrokerReceive;
use common\models\broker\Broker;

class HomeController extends \app\components\Controller
{
	public function init ()
	{
		parent::init();

		//nothing
	}

	public function actionLanguage ()
	{
		$language = Yii::$app->request->get('lang', 'zh-CN');

		Yii::$app->session->set('lang', $language);

		//切换完语言哪来的返回到哪里
		$this->goBack(Yii::$app->request->headers['Referer']);
	}

	//唯一采用admin-main布局的action
	public function actionIndex ()
	{
		Yii::$app->layout = 'iframe-main';//当前模块使用指定布局
		//iframe主框架
		return $this->render('index', ['a' => 'b']);
	}

	public function actionDefault ()
	{
		$data = $this->getTotalMonth();
		//默认首页
		return $this->render('default', ['data' => $data]);
	}

	/***当月报销趋势图***/
	public function actionSvChart ()
	{
		$time = Helper::indexTimeSlot('this_month');
		$res = SvChart::search($time);
		return $this->asJson($res);
	}

	/***当月渠道销售占比***/
	public function actionShopTypeRate ()
	{
		$time = Helper::indexTimeSlot('this_month');
		$res = ShopTypeRate::search($time);
		return $this->asJson($res);

	}

	/***BA注册及报销趋势图***/
	public function actionBaSvRegister ()
	{
		$time = Helper::indexTimeSlot('seven_days');
		$rs = BaSvRegister::search($time);
		return $this->asJson($rs);
	}

	/***档期完成率***/
	public function actionStageCompleRate ()
	{
		$res = StageCompleRate::search();
		return $this->asJson($res);
	}

	/****服务商报销top10***/
	public function actionServicerRank ()
	{
		$time = Helper::indexTimeSlot('this_month');
		$rs = SalesRank::servicerRank($time);
		return $this->asJson($rs);
	}

	/****服务商报销top10***/
	public function actionShopRank ()
	{
		$time = Helper::indexTimeSlot('this_month');
		$rs = SalesRank::shopRank($time);
		return $this->asJson($rs);
	}

	/****服务商报销top10***/
	public function actionBaRank ()
	{
		$time = Helper::indexTimeSlot('this_month');
		$rs = SalesRank::baRank($time);
		return $this->asJson($rs);
	}

	/****产品报销top10***/
	public function actionProductRank ()
	{
		$time = Helper::indexTimeSlot('this_month');
		$rs = SalesRank::productRank($time);
		return $this->asJson($rs);
	}

	/*** ajax初始化 ***/

	public function actionMerchantMail ()
	{
		Yii::$app->getResponse()->format = Response::FORMAT_JSON;

		$channel = 'merchant_channel';//商户通道

		//Yii::$app->queue->tableName//商户队列表

		// pushed_at 加入队列
		// reserved_at 发送中，保留
		// attempt 发送的次数（如果超过次数，同样会done），暂时就大于1即为失败!
		// done_at 完成时间

		//等待发送
		$c1 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel, 'reserved_at' => null, 'attempt' => null, 'done_at' => null])->count('id', Yii::$app->db);

		//正在发送（保留）
		$c2 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel, 'reserved_at' => null])
			->andWhere(['not', ['done_at' => null]])->count('id', Yii::$app->db);

		//已经发送，暂时定为发送一次成功
		$c3 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel, 'attempt' => 1])
			->andWhere(['not', ['reserved_at' => null, 'done_at' => null]])->count('id', Yii::$app->db);

		//已经发送，暂时定为发送一次成功
		$c4 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel])->andWhere(['>', 'attempt', 1])
			->andWhere(['not', ['reserved_at' => null, 'done_at' => null]])->count('id', Yii::$app->db);

		return [
			'state' => true,
			'msg' => [
				['value' => $c1, 'name' => Yii::t('common', 'Waiting to send')],
				['value' => $c2, 'name' => Yii::t('common', 'In process of sending')],
				['value' => $c3, 'name' => Yii::t('common', 'Have Sent')],
				['value' => $c4, 'name' => Yii::t('common', 'Mail hold up')]
			]
		];
	}


	public function actionBackendMail ()
	{
		Yii::$app->getResponse()->format = Response::FORMAT_JSON;

		$channel = 'backend_channel';//商户通道

		//等待发送
		$c1 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel, 'reserved_at' => null, 'attempt' => null, 'done_at' => null])->count('id', Yii::$app->db);

		//正在发送（保留）
		$c2 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel, 'reserved_at' => null])
			->andWhere(['not', ['done_at' => null]])->count('id', Yii::$app->db);

		//已经发送，暂时定为发送一次成功
		$c3 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel, 'attempt' => 1])
			->andWhere(['not', ['reserved_at' => null, 'done_at' => null]])->count('id', Yii::$app->db);

		//已经发送，暂时定为发送一次成功
		$c4 = (new Query())
			->from(Yii::$app->queue->tableName)
			->andWhere(['channel' => $channel])->andWhere(['>', 'attempt', 1])
			->andWhere(['not', ['reserved_at' => null, 'done_at' => null]])->count('id', Yii::$app->db);

		return [
			'state' => true,
			'msg' => [
				['value' => $c1, 'name' => Yii::t('common', 'Waiting to send')],
				['value' => $c2, 'name' => Yii::t('common', 'In process of sending')],
				['value' => $c3, 'name' => Yii::t('common', 'Have Sent')],
				['value' => $c4, 'name' => Yii::t('common', 'Mail hold up')]
			]
		];
	}

	public function getTotalMonth ()
	{
		$time = Helper::indexTimeSlot('this_month');

		//昨日开始时间戳
		$beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		//昨日结束时间戳
		$endYesterday = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;

		//用户启用模块
		$modules = [];
		$company = [];

		if (Yii::$app->user->identity->isAdmin) {

			$modules = array_keys(Company::GetModulesName());
		} else {
			$company = Company::find()->andWhere(['id' => Yii::$app->user->identity->company_id])->asArray()->one();

			$modules = explode(',', $company['use_modules']);
		}

		$res = [
			//使用模块
			'modules' => $modules,
		];

		return $res;
	}
	

	//品牌占比图
	public function actionSvBrandReport ()
	{
	    $environment=Yii::$app->params['environment'];
		$catch = Yii::$app->cache;
		$company_id = Yii::$app->user->identity->company_id;
		$catch_key = $environment.'sv_brand_report' . Yii::$app->user->id . '|' . $company_id;
		$catchValue = $catch->get($catch_key);
		if (empty($catchValue)) {
			//获取当前时间
			$time = time();
			//30天时间
			$before = $time - 86400 * 365; //30天前的时间
			$details = Detail::find()
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->self()->all();
			$brand_ids = ArrayHelper::getColumn($details, 'brand_id');
			// return $this->asJson(['brand_ids'=>$brand_ids,'state'=>true]);
			$ids = array_count_values($brand_ids);
			$brand_names = Brand::find()->andWhere(['id' => array_keys($ids)])->all();
			$names = ArrayHelper::map($brand_names, 'id', 'name');
			$brands = [];
			foreach ($ids as $key => $value) {
				foreach ($names as $k => $v) {
					if ($key == $k) {
						$brands[$names[$key]] = $value;
					}
				}
			}
			$catch->set($catch_key, $brands, 60 * 60 * 24);
			return $this->asJson(['state' => true, 'brand' => $brands]);
		} else {
			return $this->asJson(['state' => true, 'brand' => $catchValue]);
		}
		//$brands=$ids;

	}

	//报销量趋势图
	public function actionSvTrendReport ()
	{
		//获取当前时间
		$time = time();
		//30天时间
		$before = $time - 86400 * 30; //30天前的时间
		$num = [];
		$total = [];
		$j = 0;
		for ($i = $before; $i <= $time; $i = $i + 86400) {
			$k = date('m/d', $i);
			$num[$k] = 0;  //初始化数量
			$total[$k] = 0; //初始化金额
			$times[$j] = $k; //初始化时间
			$j++;
		}
		//前30天报销量订单
		$detail = Detail::find()
			->andWhere(['>', 'created_at', $time])
			->andWhere(['<', 'created_at', $before])
			->self()->all();
		
			$ret = [];
			$ret_money=[];
			foreach ($detail as $key => $value) {
			    foreach ($value as $k => $v) {
			        if ($k == 'created_at') {
			            $p = date('m/d', $v);
			            $detail[$key]['created_at'] = $p;
			        }
			        if ($k == 'self_num') {
			            $n = number_format($v, 2);
			            $detail[$key]['self_num'] = floatval($n);
			        }
			        if ($k =='self_money'){
			            $c = number_format($v, 2);
			            $detail[$key]['self_money'] = floatval($c);
			        }
			    }
			}
			foreach ($detail as $v) {
			    if (!isset($ret[$v['created_at']])) {
			        $ret[$v['created_at']] = $v['self_num'];
			    } else {
			        $ret[$v['created_at']]['self_num'] += $v['self_num'];
			    }
			    if (!isset($ret_money[$v['created_at']])){
			        $ret_money[$v['created_at']]=$v['self_money'];
			    }else{
			        $ret_money[$v['created_at']]['self_money'] += $v['self_money'];
			    }
			    
			}
			$c_self_money = ArrayHelper::map(array_values($ret_money), 'created_at', 'self_money');
			$c_self_num = ArrayHelper::map(array_values($ret), 'created_at', 'self_num');
			$new_self_num = array();
			$new_self_money=array();
			
			$num = array_values(array_merge($num, $c_self_num));
			$total = array_values(array_merge($total, $c_self_num));
		    return $this->asJson(['state' => true, 'times' => $times, 'num' => $num, 'total' => $total]);
	}

	//采购订单渠道占比图
	public function actionOrderShopReport ()
	{
		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_key = $environment.'order_shop_report' . Yii::$app->user->id . '|' . $company_id;
		$catchValue = $catch->get($catch_key);
		if (empty($catchValue)) {
			$customProcurement = CustomerProcurement::find()->self()->all();
			$shoptypeids = ArrayHelper::getColumn($customProcurement, 'shop_type_id');
			$ids = array_count_values($shoptypeids);
			$shoptype = ShopType::find()->andWhere(['id' => array_keys($ids)])->all();
			$names = ArrayHelper::map($shoptype, 'id', 'name');
			$shops = [];
			foreach ($ids as $key => $value) {
				foreach ($names as $k => $v) {
					if ($key == $k) {
						$shops[$names[$key]] = $value;
					}
				}
			}
			$catch->set($catch_key, $shops, 60 * 60 * 24);
			return $this->asJson(['state' => true, 'shops' => $shops]);
		} else {
			return $this->asJson(['state' => true, 'shops' => $catchValue]);
		}


	}


	//采购订单增长趋势图
	public function actionOrderNumReport ()
	{
		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_key = $environment.'order_home_num_reportsssss' . Yii::$app->user->id . '|' . $company_id;
		$catch_value = $catch->get($catch_key);
		if (empty($catch_value)) {
			$time = time(); //当前时间
			$before = $time - 86400 * 30; //30天前的时间
			//查询近30天的订单量数据
			$customerProcurement = CustomerProcurement::find()
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->self()->all();
				$nums=$this->getChartData($customerProcurement, $before);
			//查询近30天的订单金额数据
			$customerProcurementDetail = CustomerProcurementDetail::find()
				->select(['created_at', 'money_self'])
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->self()->asArray()->all();
			$newdata = $this->getChartTotalData($customerProcurementDetail, $before, 'money_self');
			$data['times'] = $nums['times'];
			$data['num'] = $nums['data'];
			$data['total'] = $newdata['data'];
			$catch->set($catch_key, $data, 60 * 60);
			return $this->asJson(['state' => true, 'times' => $nums['times'], 'num' => $nums['data'], 'total' => $newdata['data']]);
		} else {
			return $this->asJson(['state' => true, 'total' => $catch_value['total'], 'times' => $catch_value['times'], 'num' => $catch_value['num']]);
		}
	}

	//30天内采购需求产品占比图
	public function actionOrderProductReport ()
	{
		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_key = $environment.'order_products_report' . Yii::$app->user->id . '|' . $company_id;
		$catchValue = $catch->get($catch_key);
		if (empty($catchValue)) {
			$time = time(); //当前时间
			$before = $time - 86400 * 30; //30天前的时间
			$customerProcurementDetail = CustomerProcurementDetail::find()
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->self()->all();
			$product_ids = ArrayHelper::getColumn($customerProcurementDetail, 'product_id');
			$ids = array_count_values($product_ids);
			$products = Product::find()->andWhere(['id' => array_keys($ids)])->all();
			$names = ArrayHelper::map($products, 'id', 'name');
			$prod = [];
			foreach ($ids as $key => $value) {
				foreach ($names as $k => $v) {
					if ($key == $k) {
						$prod[$names[$key]] = $value;
					}
				}
			}
			$catch->set($catch_key, $prod, 60 * 60);
			return $this->asJson(['state' => true, 'products' => $prod]);
		} else {
			return $this->asJson(['state' => true, 'products' => $catchValue]);
		}

	}

	//采购需求产品数量趋势图
	public function actionOrderProductNumReport ()
	{

		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_key = $environment.'order_product_numm_reportsss' . Yii::$app->user->id . '|' . $company_id;
		$catchValue = $catch->get($catch_key);
		if (empty($catchValue)) {
		    $time = time(); //当前时间
		    $before = $time - 86400 * 30; //30天前的时间
		    $result = CustomerProcurementDetail::find()
		    ->select(['cp.created_at', 'cp.num_self', 'p.name'])
		    ->alias('cp')->leftJoin('s_product p', 'p.id=cp.product_id')
		    ->andWhere(['<', 'cp.created_at', $time])
		    ->andWhere(['>', 'cp.created_at', $before])
		    ->self('cp')->asArray()->all();
		    $data=$this->getChartGroupTotalData($result, $before,'num_self','name');
			$catch->set($catch_key, $data, 60);
			return $this->asJson(['state' => true, 'products' => $data['data'], 'times' => $data['times']]);
		} else {
			$times = $catchValue['times'];
			$new = $catchValue['data'];
			return $this->asJson(['state' => true, 'products' => $new, 'times' => $times]);
		}
	}
	
	public function actionEndorsementVisibleReport(){
	    $catch = Yii::$app->cache;
	    $environment=Yii::$app->params['environment'];
	    $company_id = Yii::$app->user->identity->company_id;
	    $catch_key = $environment.'task_receive_fans_report' . Yii::$app->user->id . '|' . $company_id;
	    $catchValue = $catch->get($catch_key);
	    if (empty($catchValue)){
    	    $time=time(); //当前时间
    	    $before =$time-86400*30;
    	    $result=TaskReceive::find()
    	    ->select(['cp.created_at','s.fans_num','t.title'])
    	    ->alias('cp')->leftJoin('s_task t','t.id=cp.task_id')
    	    ->leftJoin('s_social_info s','s.messenger_id=receive_messenger_id')
    	    ->andWhere(['<', 'cp.created_at', $time])
    	    ->andWhere(['>', 'cp.created_at', $before])
    	    ->self('cp')->asArray()->all();
    	    $datas=$this->getChartGroupTotalData($result, $before, 'fans_num', 'title');
    	    $fans_num=$datas['data'];
    	    $times=$datas['times'];
     	    $catch->set($catch_key, $datas);
     	    return $this->asJson(['state'=>true,'data'=>$fans_num,'times'=>$times]);
	    }else{
	        return $this->asJson(['state'=>true,'data'=>$catchValue['data'],'times'=>$catchValue['times']]);
	    }
	    
	}


	//咖客增长趋势图
	public function actionEndorsementNumReport ()
	{
		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_key = $environment.'endorsement_home_new_num_report' . Yii::$app->user->id . '|' . $company_id;
		$catch_value = $catch->get($catch_key);
		if (empty($catch_value)) {
			//获取当前时间
			$time = time();
			//30天时间
			$before = $time - 86400 * 30; //30天前的时间
			//查询近30天的咖客数据
			$messenger = Messenger::find()
				->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'role' => MessengerRole::ROLE_MEMBER])
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->self()->all();
			$chartData=$this->getChartData($messenger, $before);
			$data['times'] = $chartData['times'];
			$data['num'] = $chartData['data'];
			$catch->set($catch_key, $data, 60 * 60);
			return $this->asJson(['state' => true, 'times' => $chartData['times'], 'num' => $chartData['data']]);
		} else {
			return $this->asJson(['state' => true, 'times' => $catch_value['times'], 'num' => $catch_value['num']]);
		}
	}

	//咖客活跃度占比
	public function actionEndorsementActiveReport ()
	{
		$activeNums = [];
		$actives = array_values(Messenger::GetActiveContent());
		$activeSums = array_values(Messenger::GetGakeActiveSum());
		return $this->asJson(['state' => true, 'activeNums' => $activeSums, 'name' => $actives]);
	}

	//所有任务品牌占比
	public function actionEndorsementBrandReport ()
	{
		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_keys = $environment.'endorsement_brand_report' . Yii::$app->user->id . '|' . $company_id;
		$catch_values = $catch->get($catch_keys);
		if (empty($catch_values)) {
			$task = Task::find()->self()->all();
			$brand_ids = ArrayHelper::getColumn($task, 'brand_id');
			$new_brand_ids = array_count_values($brand_ids);
			//查询所有品牌
			$brands = Brand::find()->andWhere(['id' => array_keys($new_brand_ids)])->all();
			$brand_names_count = [];
			foreach ($brands as $key => $value) {
				foreach ($new_brand_ids as $k => $v) {
					if ($k == $value->id) {
						$brand_names_count[$value->name] = $v;
					}
				}
			}
			$data['brands'] = $brand_names_count;
			$catch->set($catch_keys, $data, 60 * 60 * 24);
			return $this->asJson(['state' => true, 'brands' => $brand_names_count]);
		} else {
			return $this->asJson(['state' => true, 'brands' => $catch_values['brands']]);
		}
	}

	//近30天任务领取趋势图
	public function actionEndorsementReceiveReport ()
	{
		$catch = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$company_id = Yii::$app->user->identity->company_id;
		$catch_keys = $environment.'endorsement_receive_reporthgh' . Yii::$app->user->id . '|' . $company_id;
		$catch_values = $catch->get($catch_keys);
		if (empty($catch_values)) {
			//获取当前时间
			$time = time();
			//30天时间
			$before = $time - 86400 * 30; //30天前的时间
			//任务领取
			$taskReceive = TaskReceive::find()
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->self()->all();
		    $taskFinishied = TaskReceive::find()
				->andWhere(['<', 'created_at', $time])
				->andWhere(['>', 'created_at', $before])
				->andWhere(['status' => [TaskReceive::STATUS_CHECK_PENDING,TaskReceive::STATUS_CHECK_GRAND_OK,TaskReceive::STATUS_FINISHED]])
				->self()->all();
			$receive=$this->getChartData($taskReceive, $before);
			$finished=$this->getChartData($taskFinishied, $before);
			$data['times'] = $receive['times'];
			$data['receive'] = $receive['data'];
			$data['finished'] = $finished['data'];
			$catch->set($catch_keys, $data, 60 * 60 * 24);
			return $this->asJson(['state' => true, 'times' => $receive['times'], 'finished'=>$finished['data'],'receive' => $receive['data']]);
		} else {
			return $this->asJson(['state' => true, 'times' => $catch_values['times'],'finished'=>$catch_values['finished'], 'receive' => $catch_values['receive']]);
		}
	}
	
	
	//经纪人增长趋势图
	public function actionBrokerNumReport ()
	{
	    $catch = Yii::$app->cache;
	    $environment=Yii::$app->params['environment'];
	    $company_id = Yii::$app->user->identity->company_id;
	    $catch_key = $environment.'broker_home_new_num_report' . Yii::$app->user->id . '|' . $company_id;
	    $catch_value = $catch->get($catch_key);
	    if (empty($catch_value)) {
	        //获取当前时间
	        $time = time();
	        //30天时间
	        $before = $time - 86400 * 30; //30天前的时间
	        //查询近30天的经纪人数据
	        $messenger = Messenger::find()
	        ->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'role' => MessengerRole::ROLE_BROKER])
	        ->andWhere(['<', 'created_at', $time])
	        ->andWhere(['>', 'created_at', $before])
	        ->self()->all();
	        $chartData=$this->getChartData($messenger, $before);
	        $data['times'] = $chartData['times'];
	        $data['num'] = $chartData['data'];
	        $catch->set($catch_key, $data, 60 * 60);
	        return $this->asJson(['state' => true, 'times' => $chartData['times'], 'num' => $chartData['data']]);
	    } else {
	        return $this->asJson(['state' => true, 'times' => $catch_value['times'], 'num' => $catch_value['num']]);
	    }
	}
	
	//经纪人活跃度占比
	public function actionBrokerActiveReport ()
	{
	    $activeNums = [];
	    $actives = array_values(Messenger::GetActiveContent());
	    $activeSums = array_values(Messenger::GetBrokerActiveSum());
	    return $this->asJson(['state' => true, 'activeNums' => $activeSums, 'name' => $actives]);
	}
	
	//万能经纪所有任务品牌占比
	public function actionBrokerBrandReport ()
	{
	    $catch = Yii::$app->cache;
	    $environment=Yii::$app->params['environment'];
	    $company_id = Yii::$app->user->identity->company_id;
	    $catch_keys = $environment.'broker_brand_report' . Yii::$app->user->id . '|' . $company_id;
	    $catch_values = $catch->get($catch_keys);
	    if (empty($catch_values)) {
	        $task = Broker::find()->self()->all();
	        $brand_ids = ArrayHelper::getColumn($task, 'brand_id');
	        $new_brand_ids = array_count_values($brand_ids);
	        //查询所有品牌
	        $brands = Brand::find()->andWhere(['id' => array_keys($new_brand_ids)])->all();
	        $brand_names_count = [];
	        foreach ($brands as $key => $value) {
	            foreach ($new_brand_ids as $k => $v) {
	                if ($k == $value->id) {
	                    $brand_names_count[$value->name] = $v;
	                }
	            }
	        }
	        $data['brands'] = $brand_names_count;
	        $catch->set($catch_keys, $data, 60 * 60 * 24);
	        return $this->asJson(['state' => true, 'brands' => $brand_names_count]);
	    } else {
	        return $this->asJson(['state' => true, 'brands' => $catch_values['brands']]);
	    }
	}
	//万能经纪近30天领取记录
	public function actionBrokerReceiveReport(){
	    $catch = Yii::$app->cache;
	    $environment=Yii::$app->params['environment'];
	    $company_id = Yii::$app->user->identity->company_id;
	    $catch_keys = $environment.'broker_receive_report' . Yii::$app->user->id . '|' . $company_id;
	    $catch_values = $catch->get($catch_keys);
	    if (empty($catch_values)) {
	        //获取当前时间
	        $time = time();
	        //30天时间
	        $before = $time - 86400 * 30; //30天前的时间
	        //任务领取
	        $taskReceive = BrokerReceive::find()
	        ->andWhere(['<', 'created_at', $time])
	        ->andWhere(['>', 'created_at', $before])
	        ->self()->all();
	        $taskFinishied = BrokerReceive::find()
	        ->andWhere(['<', 'created_at', $time])
	        ->andWhere(['>', 'created_at', $before])
	        ->andWhere(['status' => [BrokerReceive::STATUS_CHECK_PENDING,BrokerReceive::STATUS_CHECK_GRAND_OK,BrokerReceive::STATUS_FINISHED]])
	        ->self()->all();
	        $receive=$this->getChartData($taskReceive, $before);
	        $finished=$this->getChartData($taskFinishied, $before);
	        $data['times'] = $receive['times'];
	        $data['receive'] = $receive['data'];
	        $data['finished'] = $finished['data'];
	        $catch->set($catch_keys, $data, 60 * 60 * 24);
	        return $this->asJson(['state' => true, 'times' => $receive['times'], 'finished'=>$finished['data'],'receive' => $receive['data']]);
	    } else {
	        return $this->asJson(['state' => true, 'times' => $catch_values['times'],'finished'=>$catch_values['finished'], 'receive' => $catch_values['receive']]);
	    }
	}
	/**
	 * 对查询记录同一天的记录数量汇总
	 * @param  $result 查询结果
 	 * @param  $before 前面的时间
	 * @return $data array  包含图表显示的信息
	 */
	private function getChartData($result,$before){
	    //获取当前时间
	    $time = time();
	    $receive = [];
	    //初始化数组
	    $j = 0;
	    for ($i = $before; $i <= $time; $i = $i + 86400) {
	        $k = date('m/d', $i);
	        $receive[$k] = 0;  //初始化数量
	        $times[$j] = $k; //初始化时间
	        $j++;
	    }

	    $createds = ArrayHelper::getColumn($result, 'created_at');
	    $newCreateds = [];
	    foreach ($createds as $key => $value) {
	        $newCreateds[$key] = date('m/d', $value);
	    }
	    $created_ats = array_count_values($newCreateds);
	    $final_created_at = array_values(array_merge($receive, $created_ats));
	    $data['data']=$final_created_at;
	    $data['times']=$times;
	    return $data;
	}
	/**
	 * 对查询结果的某一字段进行统计汇总
	 */
	private function getChartTotalData($result,$before,$field){
	    $time = time(); //当前时间
	    $num = [];
	    $times = [];
	    $j = 0;
	    for ($i = $before; $i <= $time; $i = $i + 86400) {
	        $k = date('m/d', $i);
	        $num[$k] = 0;  //初始化数量
	        $times[$j] = $k; //初始化时间
	        $j++;
	    }
	    $ret = [];
	    foreach ($result as $key => $value) {
	        foreach ($value as $k => $v) {
	            if ($k == 'created_at') {
	                $p = date('m/d', $v);
	                $result[$key]['created_at'] = $p;
	            }
	            if ($k == $field) {
	                $n = number_format($v, 2);
	                $result[$key][$field] = floatval($n);
	            }
	        }
	    }
	    foreach ($result as $v) {
	        if (!isset($ret[$v['created_at']])) {
	            $ret[$v['created_at']] = $v;
	        } else {
	            $ret[$v['created_at']][$field] += $v[$field];
	        }
	    }
	    
	    $cust = ArrayHelper::map(array_values($ret), 'created_at', $field);
	    $new = array();
	    $new = array_values(array_merge($num, $cust));
	    $data['times'] = $times;
	    $data['data'] = $new;
	    return $data;
	}
	
	/**
	 * 对分组查询记录的某个字段进行汇总统计
	 * @param  $result
	 * @param  $before
	 */
	private function getChartGroupTotalData($result,$before,$field,$group){
	    $time = time(); //当前时间
	    $num = [];
	    $times = [];
	    $j = 0;
	    for ($i = $before; $i <= $time; $i = $i + 86400) {
	        $k = date('m/d', $i);
	        $num[$k] = 0;  //初始化数量
	        $times[$j] = $k; //初始化时间
	        $j++;
	    }
	    $ret = [];
	    foreach ($result as $key => $value) {
	        foreach ($value as $k => $v) {
	            if ($k == 'created_at') {
	                $p = date('m/d', $v);
	                $result[$key]['created_at'] = $p;
	            }
	            if ($k == $field) {
	                $n = number_format($v, 2);
	                $result[$key][$field] = floatval($n);
	            }
	        }
	    }
	    foreach ($result as $v) {
	        if (!isset($ret[$v[$group] . '_' . $v['created_at']])) {
	            $ret[$v[$group] . '_' . $v['created_at']] = $v;
	        } else {
	            $ret[$v[$group] . '_' . $v['created_at']][$field] += $v[$field];
	        }
	    }
	    $product_names = ArrayHelper::getColumn($result, $group);
	    $cust = ArrayHelper::map(array_values($ret), 'created_at', $field, $group);
	    $new = array();
	    //$names = array_flip(array_flip($product_names));
	    foreach ($cust as $key => $value) {
	        $new[$key] = array_values(array_merge($num, $value));
	    }
	    $data['data'] = $new;
	    $data['times'] = $times;
	    return $data;
	}

	public function actionAll ()
	{
		$time = Helper::indexTimeSlot('this_month');
		//昨日开始时间戳
		$beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		//昨日结束时间戳
		$endYesterday = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
		//用户启用模块
		$company = [];
		$modules = [];
		if (Yii::$app->user->identity->isAdmin) {

			$modules = array_keys(Company::GetModulesName());
		} else {

			$company = Company::find()->andWhere(['id' => Yii::$app->user->identity->company_id])->asArray()->one();

			$modules = explode(',', $company['use_modules']);
		}
		$start_date = date('Y-m-d', time());
		$end_date = date('Y-m-d', strtotime("next month"));
		//报销量模块的数据初始化
		$shops = 0; //店铺数量
		$servers = 0; //servers
		$messengers = 0; //messengers
		$money = 0; //当月销售金额
		$yesterday_money = 0; //昨日金额
		$yesterday_num = 0; //昨日数量
		$auditing = 0;     //正在审核的数量

		//智能采购模块相关信息初始化
		$purchase_month_total = 0; //当月客户采购金额
		$purchase_month_num = 0; //当月采购数量
		$purchase_supply_total = 0; //供应商总数
		$purchase_self = 0; //自身采购订单数
		$purchase_yesterday_money = 0; //昨日采购金额
		$purchase_yesterday_num = 0; //昨日采购数量
		$purchase_audit = 0;  //未审核的采购订单
		$purchase_send = 0; //未发货订单


		//咖客种草模块相关信息初始化
		$endorsement_sum = 0; //咖客总数

		$endorsement_num = 0; //任务总数

		$endorsement_operation = 0; //正在运行的任务数量

		$endorsement_total = 0.00; //已发放的总佣金金额

		$endorsement_yesterday_num = 0; //昨日领取任务数量

		$endorsement_yesterday_total = 0.00; //昨日发放的佣金金额

		$endorsement_yesterday_gake = 0;   //昨日新增咖客数

		$endorsement_audit = 0;     //未审核的订单
		
		//万能经纪任务模块相关信息初始化
		$broker_sum = 0; //咖客总数
		
		$broker_num = 0; //任务总数
		
		$broker_operation = 0; //正在运行的任务数量
		
		$broker_total = 0.00; //已发放的总佣金金额
		
		$broker_yesterday_num = 0; //昨日领取任务数量
		
		$broker_yesterday_total = 0.00; //近30天代理收入
		
		$broker_yesterday_gake = 0;   //昨日新增咖客数
		
		$broker_audit = 0;     //未审核的订单

		$data = [];
		$cache = Yii::$app->cache;
		$environment=Yii::$app->params['environment'];
		$id = Yii::$app->user->identity->company_id;

		$cache_key = (Yii::$app->user->identity->isAdmin) ? $environment.'cache_allData_key_isAdmin' : $environment.'cache_allData_key_neswsssss' . $id;

		$res = $cache->get($cache_key);
		try {
			if ($res === false) {
				foreach ($modules as $module) {
					//报销量
					if ($module == Company::M_SV) {
						//店铺数量
						$shops = Shop::find()->andWhere(['is_del' => Shop::IS_NOT_DEL])
							->dataAuth('manage_shop_type', 'type')->self()->count();

						//servers数量
						if (Yii::$app->user->identity->isAdmin) {
							$servers = Servicer::find()
								->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'status' => Servicer::SERVICER_ON])
								->self()->count();
						} else {
							$servers = ServicerBrand::find()
								->dataAuth('manage_shop_type', 'shop_type_id')
								->groupBy(['servicer_id'])->self()->count();
						}

						//messengers的数量
						$messengers = Messenger::find()
							->andWhere(['is_del' => Messenger::IS_NOT_DEL, 'status' => Messenger::STATUS_OK, 'role' => MessengerRole::ROLE_BA])
							->dataAuth('manage_shop_type', 'channel_id')->self()->count();

						//报销金额
						$money = Detail::find()->select(['sum(money_self) as money_self'])
							->andWhere(['>=', 'sales_day', $time['start_time']])
							->andWhere(['<=', 'sales_day', $time['end_time']])
							->dataAuth('manage_shop_type', 'shop_type_id')
							->self()->asArray()->one()['money_self'];

						//昨日报销金额
						$yesterday_money = Detail::find()->select(['sum(money_self) as money_self'])
							->andWhere(['>=', 'sales_day', $beginYesterday])
							->andWhere(['<=', 'sales_day', $endYesterday])
							->dataAuth('manage_shop_type', 'shop_type_id')
							->self()->asArray()->one()['money_self'];

						//昨日的报销数量
						$yesterday_num = Detail::find()->select(['sum(num_self) as num_self'])
							->andWhere(['>=', 'sales_day', $beginYesterday])
							->andWhere(['<=', 'sales_day', $endYesterday])
							->dataAuth('manage_shop_type', 'shop_type_id')
							->self()->asArray()->one()['num_self'];

						//正在审核数量
						$auditing = Detail::find()->andWhere(['status' => Detail::STATUS_0])
							->dataAuth('manage_shop_type', 'shop_type_id')
							->self()->count();
						//构造data数组
						$data['shops'] = $shops; //店铺数量
						$data['servers'] = $servers; //服务商数量
						$data['messengers'] = $messengers; //经销商数量
						$data['money'] = Yii::$app->formatter->asCurrency($money);//报销金额
						$data['yesterday_money'] = Yii::$app->formatter->asCurrency($yesterday_money); //昨日报销金额
						$data['yesterday_num'] = $yesterday_num; //昨日报销数量
						$data['auditing'] = $auditing;       //正在审核的数量
						//bu模块
					} else if ($module == Company::M_BU) {
						//现金流数据查询
						$company_id = Yii::$app->user->identity->company_id;
                    
						$datas=$this->getBalanceFlow($company_id, time());

						
						$data['money_flow'] = $datas;

						//智能采购
					} else if ($module == Company::M_PURCHASE) {
						//获取客户当月采购数量和金额
						$ids = CustomerProcurement::find()->select('id')
							->andWhere(['>=', 'sales_day', $time['start_time']])
							->andWhere(['<=', 'sales_day', $time['end_time']])
							->dataAuth('manage_shop_type', 'shop_type_id')
							->self()->asArray()->all();
						foreach ($ids as $id) {
							$detailList = CustomerProcurementDetail::find()->andWhere(['id' => $id])->all();
							foreach ($detailList as $detail) {
								$purchase_month_num += $detail->num_self;
								$purchase_month_total += $detail->money_self;
							}
						}

						//供应商总数
						$purchase_supply_total = Supply::find()->self()->count();

						//自身采购订单数
						$purchase_self = PurchaseOrder::find()->andWhere(['status' => [PurchaseOrder::STATUS_WAIT, PurchaseOrder::STATUS_ACTIVE]])
							->self()->count();

						//昨日采购金额&数量
						$ids = CustomerProcurement::find()->select('id')
							->andWhere(['>=', 'sales_day', $beginYesterday])
							->andWhere(['<=', 'sales_day', $endYesterday])
							->self()->asArray()->all();
						foreach ($ids as $id) {
							$detailList = CustomerProcurementDetail::find()->andWhere(['id' => $id])->all();
							foreach ($detailList as $detail) {
								$purchase_yesterday_num += $detail->num_self;
								$purchase_yesterday_money += $detail->money_self;
							}
						}

						//未审核的采购订单数
						$purchase_audit = CustomerProcurement::find()->andWhere(['status' => CustomerProcurement::STATUS_0])
							->self()->count();

						//未发货的采购订单数
						$purchase_send = CustomerProcurement::find()
							->andWhere(['status' => CustomerProcurement::STATUS_1])
							->andWhere(['pay_status' => CustomerProcurement::PAY_STATUS_YES])
							->self()->count();
						//构造data数据
						$data['purchase_month_num'] = $purchase_month_num;
						$data['purchase_month_total'] = Yii::$app->formatter->asCurrency($purchase_month_total);
						$data['purchase_supply_total'] = $purchase_supply_total;
						$data['purchase_self'] = $purchase_self;
						$data['purchase_yesterday_num'] = $purchase_yesterday_num;
						$data['purchase_yesterday_money'] = Yii::$app->formatter->asCurrency($purchase_yesterday_money);
						$data['purchase_audit'] = $purchase_audit;
						$data['purchase_send'] = $purchase_send;
						


					} else if ($module == Company::M_ENDORSEMENT) {
						//咖客总数
						$endorsement_sum = Messenger::find()
							->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'role' => MessengerRole::ROLE_MEMBER])
							->self()->count();
						//总任务数
						$endorsement_num = Task::find()->self()->count();
						//正在运行的任务
						$endorsement_operation = Task::find()->andWhere(['status' => Task::STATUS_ACTIVE])
							->self()->count();
						//已发放的任务总佣金
						$tasks = Task::find()->self()->all();
						if (!empty($tasks)) {
							foreach ($tasks as $task) {
								$details = TaskDetail::find()->andWhere(['task_id' => $task->id])
									->all();
								if (!empty($details)) {
									foreach ($details as $detail) {
										$endorsement_total += $detail->commission;
									}
								}

							}
						}
						
						//昨日任务领取数量
						$endorsement_yesterday_num = TaskReceive::find()->andWhere(['>=', 'created_at', $beginYesterday])
							->andWhere(['<=', 'created_at', $endYesterday])
							->self()->count();
						//昨日佣金发放金额
						$receive = TaskReceive::find()
							->andWhere(['>=', 'created_at', $beginYesterday])
							->andWhere(['<=', 'created_at', $endYesterday])
							->andWhere(['payment_status' => TaskReceive::TASK_PAY_STATUS_GRANT])
							->self()->all();
						if (!empty($receive)) {
							foreach ($receive as $fafangtask) {
								$Taskdetails = TaskDetail::find()->andWhere(['id' => $fafangtask->task_detail_id])
									->all();
								if (!empty($Taskdetails)) {
									foreach ($Taskdetails as $detail) {
										$endorsement_yesterday_total += $detail->commission;
									}
								}
							}
						}
						//昨日新增咖客
						$endorsement_yesterday_gake = Messenger::find()
							->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'role' => MessengerRole::ROLE_MEMBER])
							->andWhere(['>=', 'created_at', $beginYesterday])
							->andWhere(['<=', 'created_at', $endYesterday])
							->self()->count();
						//未审核
						$endorsement_audit = TaskReceive::find()
							->andWhere(['status' => TaskReceive::STATUS_CHECK_PENDING])
							->self()->count();
						$data['endorsement_sum'] = $endorsement_sum;
						$data['endorsement_num'] = $endorsement_num;
						$data['endorsement_operation'] = $endorsement_operation;
						$data['endorsement_total'] = Yii::$app->formatter->asCurrency($endorsement_total);
						$data['endorsement_yesterday_num'] = $endorsement_yesterday_num;
						$data['endorsement_yesterday_total'] = Yii::$app->formatter->asCurrency(number_format($endorsement_yesterday_total, 2));
						$data['endorsement_yesterday_gake'] = $endorsement_yesterday_gake;
						$data['endorsement_audit'] = $endorsement_audit;
					}else if ($module == Company::M_BROKER){
					    //咖客总数
					    $broker_sum = Messenger::find()
					    ->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'role' => MessengerRole::ROLE_BROKER])
					    ->self()->count();
					    //总任务数
					    $broker_num = Broker::find()->self()->count();
					    //正在运行的任务
					    $broker_operation = Broker::find()->andWhere(['status' => Broker::STATUS_ACTIVE])
					    ->self()->count();
					    //已发放的任务总佣金
					    $tasks = Broker::find()->self()->all();
					    $brokerReceive=BrokerReceive::find()->andWhere(['payment_status'=>2])->self()->all();
					    if (empty($brokerReceive)){
					        $broker_total=0;
					    }else{
    					    foreach ($brokerReceive as $receive){
    					        $broker_total+=$receive->commission;
    					    }
					    }
					    //昨日任务领取数量
					    $broker_yesterday_num = BrokerReceive::find()->andWhere(['>=', 'created_at', $beginYesterday])
					    ->andWhere(['<=', 'created_at', $endYesterday])
					    ->self()->count();
					    //近30日代理收入金额
					    $current_time=time();
					    $before_time=$current_time-86400*30;
					    /**
					     * 查询近30天已经完成的任务
					     */
					    $brokerReceive=BrokerReceive::find()
					    ->andWhere(['>=','created_at',$before_time])
					    ->andWhere(['<=','created_at',$current_time])
					    ->andWhere(['status'=>[BrokerReceive::STATUS_CHECK_GRAND_OK,BrokerReceive::STATUS_FINISHED]])
					    ->self()->all();
					    $agent_cost=0.00;
					    if (!empty($brokerReceive)){
    					    foreach ($brokerReceive as $receive){
    					        $task_id=$receive->task_id;
    					        $bro=Broker::find()->andWhere(['id'=>$task_id])->one();
    					        $agent_cost+=$bro->agent_cost; //计算近30天的代理收入
    					    }
    					    $broker_yesterday_total=$agent_cost;
					    }else{
					        $broker_yesterday_total=0;
					    }
// 					    /**
// 					    $brokerReceive = BrokerReceive::find()
// 					    ->andWhere(['>=', 'created_at', $beginYesterday])
// 					    ->andWhere(['<=', 'created_at', $endYesterday])
// 					    ->andWhere(['payment_status' => 2])
// 					    ->self()->all();
// 					    **/
// 					    if (!empty($brokerReceive)) {
// 					        foreach ($brokerReceive as $receive){
// 					            $broker_yesterday_total+=$receive->commission;
// 					        }
// 					    }else{
// 					        $broker_yesterday_total=0;
// 					    }
					    //昨日新增咖客
					    $broker_yesterday_gake = Messenger::find()
					    ->andWhere(['is_del' => Servicer::IS_NOT_DEL, 'role' => MessengerRole::ROLE_BROKER])
					    ->andWhere(['>=', 'created_at', $beginYesterday])
					    ->andWhere(['<=', 'created_at', $endYesterday])
					    ->self()->count();
					    //未审核
					    $broker_audit = BrokerReceive::find()
					    ->andWhere(['status' => BrokerReceive::STATUS_CHECK_PENDING])
					    ->self()->count();
					    $data['broker_sum'] = $broker_sum;
					    $data['broker_num'] = $broker_num;
					    $data['broker_operation'] = $broker_operation;
					    $data['broker_total'] = Yii::$app->formatter->asCurrency($broker_total);
					    $data['broker_yesterday_num'] = $broker_yesterday_num;
					    $data['broker_yesterday_total'] = Yii::$app->formatter->asCurrency(number_format($broker_yesterday_total, 2));
					    $data['broker_yesterday_broker'] = $broker_yesterday_gake;
					    $data['broker_audit'] = $broker_audit;
					}
				}

				Yii::$app->cache->set($cache_key, $data, 60 * 60 * 10);

				return $this->asJson(['state' => true, 'msg' => '请求成功', 'data' => $data, 'modules' => $modules]);
			}
			return $this->asJson(['state' => true, 'msg' => '请求成功', 'data' => $res, 'modules' => $modules]);
		} catch (Exception $e) {
			return $this->asJson(['state' => false, 'msg' => '请求失败']);
		}

	}
    //获取当前时间的现金流
    private function getBalanceFlow($company_id,$time){
        $year=date('Y',$time);
        $items = Item::find()->andWhere(['company_id' => $company_id])->andWhere(['year' => $year])->all();
        /** @var Item $item */
        $i=0;
        $data=[];
        foreach ($items as $item) {
            $key = 'bu-flow-for-item-id-' . $item->item_id;
            
            $cacheData = Yii::$app->cache->get($key);
            
            $nowFlow = $cacheData['nowFLow'];
            $end_time=strtotime(($year+1).'-01-01')-86339; //本年最后一天
            
            //数组合并处理
            foreach ($nowFlow as $mon=> $dflow){
                foreach ($dflow as $day=>$d){
                    if (isset($newData[$year][$mon][$day])){
                        $newData[$year][$mon][$day]=array_merge_recursive($newData[$year][$mon][$day],$d);
                    }else{
                        $newData[$year][$mon][$day]=$d;
                    }
                }
            }
        }
        while ($time <= $end_time) {
            $d=date("Y-m-d", $time);
            $date = explode('-', $d);
            
            if(!empty($newData[$date[0]][$date[1]][$date[2]])){
                $flowDatas= $newData[$date[0]][$date[1]][$date[2]];
                foreach ($flowDatas as $flowData){
                    if ($flowData->money==0){continue;}
                    $i++;
                    $flowData->money=Yii::$app->formatter->asCurrency($flowData->money);
                    $flowData->occurrenceMode = FlowData::getOccurrenceModeType()[$flowData->occurrenceMode];
                    $data[$d][]=$flowData;
                    if ($i==10){
                        break;
                    }
                }
                
            }
            if ($i==10){
                break;
            }
            $time += 86400;
        }
        return $data;
    }

   



}
