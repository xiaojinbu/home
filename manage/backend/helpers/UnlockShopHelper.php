<?php
/**
 * Created by PhpStorm.
 * User: LiuRongKe
 * Date: 2019-05-10
 * Time: 下午 5:22
 */
namespace app\helpers;

use common\models\manage\Messenger;
use common\models\manage\MessengerRole;
use common\models\manage\Servicer;
use common\models\manage\ServicerBrand;
use common\models\manage\Shop;
use common\models\test\UnlockShop;


class UnlockShopHelper
{
    /**
     * 解锁店铺
     */
    public static function unlock(){

        $allUnlockShops=UnlockShop::find()->all();
        if(empty($allUnlockShops)){
            return ['state' =>false, 'err' => null, 'msg' => '没有需要解绑的店铺！'];
        }
        $dataArr=array();//错误提示
        foreach ($allUnlockShops as $unshop){
            $shop=Shop::find()->where(['shop_number'=>$unshop->shop_number,'type'=>$unshop->shop_type_id])->one();
            if(empty($shop)){
                $dataArr[]='渠道ID：'.$unshop->shop_type_id.',店编：'.$unshop->shop_number.'的店铺不存在！';
                continue;
            }
            $servicer=Servicer::find()->where(['code'=>$unshop->servicer_code])->self()->one();
            if(empty($servicer)){
                $dataArr[]=MessengerRole::getServicer().'：'.$unshop->servicer_code.'不存在！';
                continue;
            }

            //检查BA是否存在
            if(empty($unshop->username) || !Messenger::find()->where(['shop_id'=>$shop->id,'servicer_id'=>$servicer->id,'username'=>$unshop->username])->andWhere(['in','status',[Messenger::STATUS_OK,Messenger::STATUS_UPDATE]])->delstate(Messenger::IS_NOT_DEL)->exists()){
                $dataArr[]='渠道ID：'.$unshop->shop_type_id.',店编：'.$unshop->shop_number.'(ID:'.$shop->id.'),'.MessengerRole::getServicer().'id：'.$servicer->id.'没有绑定到具体的'.MessengerRole::getBa().'('.$unshop->username.')！';
                continue;
            }
            //检查是否有其他BA绑定该店铺
            $messengercount = Messenger::find()->where(['shop_id'=>$shop->id,'servicer_id'=>$servicer->id])
                ->andWhere(['in','status',[Messenger::STATUS_OK,Messenger::STATUS_UPDATE]])->delstate(Messenger::IS_NOT_DEL)->count();
            Messenger::updateAll(['status'=>Messenger::STATUS_INACTIVE],['username'=>$unshop->username,'channel_id'=>$unshop->shop_type_id,'shop_id'=>$shop->id]);
            $dataArr[]='BA：'.$unshop->username.'已经修改状态为禁止！';
            if($messengercount==1){
                ServicerBrand::deleteAll(['servicer_id'=>$servicer->id,'shop_id'=>$shop->id,'shop_type_id'=>$unshop->shop_type_id]);
                $dataArr[]='店铺：'.$shop->name.'(店编：'.$shop->shop_number.')已经从'.MessengerRole::getServicer().'：'.$servicer->name.'(编码：'.$servicer->code.')解绑！';
            }
        }
        return ['state' =>true, 'data' => $dataArr, 'msg' => '解绑店铺成功！'];
    }

}