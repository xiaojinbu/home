<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12 0012
 * Time: 下午 4:32
 */

namespace app\helpers\commission;

use common\models\manage\MessengerRole;
use Yii;
use common\models\sv\Commission;
use common\models\sv\ShopCommission;
use common\models\sv\ShopDetail;
use common\models\analysis\statistics\Shop as StaShop;
use common\models\stage\StageDetail;

class BaseCommission
{
    public $start_time;//开始时间,时间戳
    public $end_time;//结束时间，时间戳
    public $shop_type_id;//渠道ID
    public $servicer_id;//服务商ID
    public $shop_id;//店铺ID

    public $shopSalesList;//店铺真实销量
    public $reportShopList;//有上报的店铺

    public $errMsg;//错误提示
    public $isUpdate;//是否更新

    //public $insert_sql_field_list;//批量插入数据库的字段名，数组格式
    public $insert_data_list;//批量插入数据
    //public $update_sql_field_list;//批量更新数据库的字段名，数组格式
    public $update_data_list;//批量更新数据
    public $delete_id_list;//批量删除的数据ID

    private $oldShopCommList;//旧提成数据集合
    private $ba;  //BA角色
    private $servicer; //服务商角色
    private $supervisor; //区域经理角色

    function __construct()
    {
        //角色
        $this->ba=MessengerRole::getBa();
        $this->servicer=MessengerRole::getServicer();
        $this->supervisor=MessengerRole::getSupervisor();
        $this->initData();
    }


    /**
     * 执行
     */
    public function execute()
    {
        $this->initData();
        //检查
        $this->checkUpdate();
        if (!empty($this->errMsg) && count($this->errMsg)) {
            return;
        }
        //获取有上报的店铺,既是需要计算的店铺信息,数据取自BA自报销量
        $this->reportShopList = $this->reportShop();
        if (empty($this->reportShopList) || (!empty($this->errMsg) && count($this->errMsg))) {
            return;
        }
        //获取真实销量数据
        $this->shopSalesList = $this->shopSales();
        if (empty($this->shopSalesList)) {
            $this->errMsg[] = '没有需要计算佣金的数据！';
            return;
        }
        foreach ($this->shopSalesList as $shopDetail) {
            //检查该店铺是否有报销量
            $reportShop = array();
            foreach ($this->reportShopList as $reportShopTmp) {
                $report_day = strtotime($reportShopTmp['sales_day']);
                if ($report_day == $shopDetail['sales_day'] && $reportShopTmp['shop_type_id'] == $shopDetail['shop_type_id'] && $reportShopTmp['shop_id'] == $shopDetail['shop_id']) {
                    $reportShop = $reportShopTmp;
                    break;
                }
            }
            //检查是否有报销量数据，没有数据则跳出计算提成
            if (empty($reportShop) || !is_array($reportShop)) {
                continue;
            }

            $shopTypeId = $shopDetail['shop_type_id'];//渠道ID
            $servicerId = $reportShop['servicer_id'];//服务商ID
            $shopId = $shopDetail['shop_id'];//店铺ID

            //获取对应档期详情
            $stageDetail = StageDetail::QueryStageDetail($this->shop_type_id, $shopId, $shopDetail['brand_id'], $shopDetail['product_id'], 0, $shopDetail['sales_day']);
            if (empty($stageDetail)) {
                $this->errMsg[] = $reportShop['shop_name'] . '(' . $shopId . ')' . '在' . $reportShop['sales_day'] . '品牌：' . $shopDetail['brand_id'] . ',产品：' . $shopDetail['product_id'] . '没有对应的档期详情。';
                continue;
            }
            //获取对应的提成设置
            $commissionSet = Commission::QueryCommissionDetail($shopTypeId, $servicerId, $shopDetail['brand_id'], $shopDetail['product_id'], $stageDetail->stage_id);
            if (empty($commissionSet)) {
                $this->errMsg[] = $reportShop['shop_name'] . '(' . $shopId . ')' . '在' . $reportShop['sales_day'] . '渠道：' . $reportShop['channel_name'] . ','.$this->servicer.'：' . $reportShop['servicer_name'] . ',品牌：' . $shopDetail['brand_id'] . ',产品：' . $shopDetail['product_id'] . ',档期：' . $stageDetail->stage->name . '没有对应的提成设置。';
                continue;
            }

            //提成信息
            $info='';
            //计算提成
            $servicer_money = 0.00;//服务商提成金额
            $ba_money = 0.00;//BA提成金额
            $manage_money = 0.00;//管理者提成金额
            $commissionType=1;//提成类型，1为金额，2为贴花
            $servicer_rate = $commissionSet->servicer_commission_rate;
            $ba_rate = $commissionSet->ba_commission_rate;
            $manage_rate = $commissionSet->manage_commission_rate;
            if ($stageDetail->commission_rate_type == StageDetail::RATE_TYPE_NUMBER) {
                $commissionType=1;
                //按固定金额分成
                $servicer_money = $stageDetail->commission_rate_money * $servicer_rate / ($servicer_rate + $ba_rate+$manage_rate);
                $servicer_money = round($servicer_money, 2);
                $ba_money = $stageDetail->commission_rate_money * $ba_rate / ($servicer_rate + $ba_rate+$manage_rate);
                $ba_money = round($ba_money, 2);
                $manage_money = $stageDetail->commission_rate_money - $servicer_money-$ba_money;
                //乘以数量
                $ba_money=$ba_money*$shopDetail['num'];
                $servicer_money=$servicer_money*$shopDetail['num'];
                $manage_money=$manage_money*$shopDetail['num'];
                $info='总提成：'.$stageDetail->commission_rate_money.'元，'.$this->servicer.'：'.$servicer_rate.'%，'.$this->ba.'：'.$ba_rate.'%，'.$this->supervisor.'：'.$manage_rate.'%';
            } else if ($stageDetail->commission_rate_type == StageDetail::RATE_TYPE_MONEY) {
                $commissionType=1;
                $servicer_rate_tmp = $stageDetail->commission_rate * $servicer_rate / ($servicer_rate + $ba_rate+$manage_rate);
                $servicer_rate_tmp = round($servicer_rate_tmp, 2);
                $ba_rate_tmp = $stageDetail->commission_rate * $ba_rate / ($servicer_rate + $ba_rate+$manage_rate);
                $ba_rate_tmp = round($ba_rate_tmp, 2);
                $manage_rate_tmp = $stageDetail->commission_rate - $servicer_rate_tmp - $ba_rate_tmp;
                $servicer_money = $shopDetail['money'] * $servicer_rate_tmp/100;
                $servicer_money = round($servicer_money, 2);
                $ba_money = $shopDetail['money'] * $ba_rate_tmp/100;
                $ba_money = round($ba_money, 2);
                $manage_money = $shopDetail['money'] * $manage_rate_tmp/100;
                $manage_money = round($manage_money, 2);
                $info='总提成：'.$stageDetail->commission_rate.'%，'.$this->servicer.'：'.$servicer_rate_tmp.'%，'.$this->ba.'：'.$ba_rate_tmp.'%，'.$this->supervisor.'：'.$manage_rate_tmp.'%';
            } else if ($stageDetail->commission_rate_type == StageDetail::RATE_TYPE_APPLIQUE) {
                $commissionType=2;
                //按贴花分成
                $servicer_money = $stageDetail->commission_applique * $servicer_rate / ($servicer_rate + $ba_rate+$manage_rate);
                $servicer_money = round($servicer_money, 2);
                $ba_money = $stageDetail->commission_applique * $ba_rate / ($servicer_rate + $ba_rate+$manage_rate);
                $ba_money = round($ba_money, 2);
                $manage_money = $stageDetail->commission_applique - $servicer_money-$ba_money;
                //乘以数量
                $ba_money=$ba_money*$shopDetail['num'];
                $servicer_money=$servicer_money*$shopDetail['num'];
                $manage_money=$manage_money*$shopDetail['num'];
                $info='总贴花：'.$stageDetail->commission_applique.'个，'.$this->servicer.'：'.$servicer_rate.'个，'.$this->ba.'：'.$ba_rate.'个，'.$this->supervisor.'：'.$manage_rate.'个';
            }


            $shopCom = null;
            if ($this->isUpdate) {
                foreach ($this->oldShopCommList as $oldComm) {
                    if ($oldComm['source_id'] == $shopDetail['id']) {
                        $shopCom = array();
                        $shopCom['num']=$shopDetail['num'];
                        $shopCom['money']=$shopDetail['money'];
                        $shopCom['commision_servicer'] = $servicer_money;
                        $shopCom['commision_ba'] = $ba_money;
                        $shopCom['commision_manage'] = $manage_money;
                        $shopCom['commission_type'] = $commissionType;
                        $shopCom['info'] = $info;
                        $shopCom['updated_at'] = time();
                        $this->update_data_list[$oldComm['id']] = $shopCom;
                        break;
                    }
                }
            }
            if (!empty($shopCom)) {
                continue;
            }

            $shopCom = array();
            //'sales_day', 'servicer_id', 'stage_id', 'shop_number', 'shop_id', 'shop_type_id', 'product_code', 'product_id', 'brand_id', 'num', 'money', 'commision_servicer', 'commision_ba', 'company_id', 'updated_at', 'created_at', 'status','info','source_id','commission_type'
            $shopCom[0] = $shopDetail['sales_day'];
            $shopCom[1] = $servicerId;
            $shopCom[2] = $stageDetail->stage_id;
            $shopCom[3] = $reportShop['shop_number'];
            $shopCom[4] = $shopId;
            $shopCom[5] = $shopTypeId;
            $shopCom[6] = $shopDetail['product_code'];
            $shopCom[7] = $shopDetail['product_id'];
            $shopCom[8] = $shopDetail['brand_id'];
            $shopCom[9] = $shopDetail['num'];
            $shopCom[10] = $shopDetail['money'];
            $shopCom[11] = $servicer_money;
            $shopCom[12] = $ba_money;
            $shopCom[13] = $manage_money;
            $shopCom[14] = $shopDetail['company_id'];
            $shopCom[15] = time();
            $shopCom[16] = time();
            $shopCom[17] = ShopCommission::STATUS_UNAUDITED;
            $shopCom[18] = $info;
            $shopCom[19] = $shopDetail['id'];
            $shopCom[20] = $commissionType;
            $this->insert_data_list[]=$shopCom;
        }

        //检查需要删除的
        if($this->isUpdate){
            foreach ($this->oldShopCommList as $oldCommTmp) {
                $updateFlag=false;
                foreach ($this->update_data_list as $key=>$updateitem){
                    if($key==$oldCommTmp['id']){
                        $updateFlag=true;
                        break;
                    }
                }
                if(!$updateFlag){
                    $this->delete_id_list[]=$oldCommTmp['id'];
                }
            }
        }

        //执行删除
        $this->deleted();
        //执行插入
        $this->insert();
        //执行更新
        $this->update();
    }

    /**
     * 获取真实销量数据
     */
    protected function shopSales()
    {
        $salesList = array();
        ShopDetail::allSales($this->shop_type_id, $this->shop_id, 0, 0, $this->start_time, $this->end_time, $salesList);
        return $salesList;
    }

    /**
     * 获取有上报的店铺,既是需要计算的店铺信息,数据取自BA自报销量
     * @return 结果信息
     */
    protected function reportShop()
    {
        if (empty($this->start_time) || empty($this->end_time) || empty($this->shop_type_id)) {
            $this->errMsg[] = '计算佣金失败，开始时间、结束时间和渠道都不能为空！';
            return null;
        }
        $search = array();
        $search['start_time'] = date('Y-m-d', $this->start_time);
        $search['end_time'] = date('Y-m-d', $this->end_time);
        if (!empty($this->servicer_id)) $search['servicer_id'] = $this->servicer_id;
        if (!empty($this->shop_type_id)) $search['channel_id'] = $this->shop_type_id;
        if (!empty($this->shop_id)) $search['shop_id'] = $this->shop_id;

        $staShop = StaShop::Report($search);//获取有报销量的店铺
        if (!$staShop['state']) {
            $this->errMsg[] = '计算佣金失败！' . $staShop['msg'];
            return null;
        }
        $shopList = $staShop['data'];
        if (empty($shopList)) {
            $this->errMsg[] = '没有对应的'.$this->ba.'自报销量数据，计算佣金失败！';
            return null;
        }
        return $shopList;
    }

    /**检查是否更新
     * @throws \Throwable
     */
    final private function checkUpdate()
    {
        $updateFlag = ShopCommission::find()
            ->where(['shop_type_id' => $this->shop_type_id])
            ->andWhere(['>=', 'sales_day', $this->start_time])
            ->andWhere(['<=', 'sales_day', $this->end_time])
            ->andFilterWhere(['servicer_id' => $this->servicer_id, 'shop_id' => $this->shop_id])
            ->andFilterWhere(['in', 'status', [ShopCommission::STATUS_AUDIT_PASS, ShopCommission::STATUS_UNISSUED, ShopCommission::STATUS_GIVE_OUT]])
            ->self()
            ->exists();
        if ($updateFlag) {
            $this->errMsg[] = '该时间段已经存在“审核通过/未发放/已发放”等状态的佣金数据，无法再次进行计算佣金！';
            return;
        }

        //获取未审核的数据，如果没有则为新数据
        $this->oldShopCommList = array();
        ShopCommission::allShopCommission($this->shop_type_id, $this->servicer_id, $this->shop_id, 0, 0, 0, $this->start_time, $this->end_time, [ShopCommission::STATUS_UNAUDITED], $this->oldShopCommList);

        if (!empty($this->oldShopCommList)) {
            $this->isUpdate = true;
        }
    }

    /**
     * 批量插入提成数据
     */
    final private function insert()
    {
        if (empty($this->insert_data_list)) {
            return;
        }
        //$start_time = microtime(true);
        $transaction = Yii::$app->db->beginTransaction();
        $i = 0;
        $temp = [];
        $sql_field_list = ['sales_day', 'servicer_id', 'stage_id', 'shop_number', 'shop_id', 'shop_type_id', 'product_code', 'product_id', 'brand_id', 'num', 'money', 'commision_servicer', 'commision_ba', 'commision_manage', 'company_id', 'updated_at', 'created_at', 'status','info','source_id','commission_type'];
        $dataCount = count($this->insert_data_list);
        foreach ($this->insert_data_list as $row) {
            $temp[] = $row;
            if ($i > 15000 || $i === ($dataCount-1)) {
                try {
                    Yii::$app->db->createCommand()->batchInsert(ShopCommission::tableName(), $sql_field_list, $temp)->execute();
                } catch (\Exception $e) {
                    $this->errMsg[] = '批量插入佣金数据出错了！';
                    $transaction->rollBack();
                }
                $temp = [];
                $i = 0;
                continue;
            }
            $i++;
        }
        $transaction->commit();
        //$end_time = microtime(true);
    }

    /**
     * 批量更新提成数据
     */
    final private function update()
    {
        if (empty($this->update_data_list)) {
            return;
        }
        //$start_time = microtime(true);
        foreach ($this->update_data_list as $key => $row) {
            ShopCommission::updateAll($row, ['id' => $key]);
        }
        //$end_time = microtime(true);
    }

    /**
     * 批量删除
     */
    final private function deleted()
    {
        if (!empty($this->delete_id_list) && is_array($this->delete_id_list)) {
            ShopCommission::deleteAll(['in', 'id', $this->delete_id_list]);
        }
    }

    /**
     * 初始化数据
     */
    private function initData()
    {
        $this->shopSalesList = array();
        $this->reportShopList = array();
        $this->insert_data_list = array();
        $this->update_data_list = array();
        $this->delete_id_list = array();
        $this->errMsg = array();
        $this->isUpdate = false;
    }


}