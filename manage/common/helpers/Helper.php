<?php
namespace common\helpers;

use yii\base\BaseObject;
use yii\data\Pagination;
use yii\db\ActiveQuery;

/**
 * Created by PhpStorm.
 * User: zpc
 * Date: 2019/9/6
 * Time: 14:17
 */
class Helper extends BaseObject
{
	/**
	 * 将数组转化为字符串，
	 * 格式为 $key=>$value|
	 * @param array $array
	 * @return string
	 */
	public static function arrayToString($array) {
		if (!empty($array['response'])) {
			unset($array['response']);
		}
		$string = '';
		foreach ($array as $key => $value) {
			try {  // 如果拼接字符串出错，那么则跳过，
				$string .= '_'.$value;
			} catch (\Exception $e) {
				$string .= '_';
				continue;
			}
//			if (is_array($value)) continue;  // 如果其中的值为数组，那么则跳过
//			$string .= '_'.$value;
		}
		return $string;
	}

	/**
	 * 自动分页, 将查询句柄传入进来， 根据页数，和每页显示数量自动分页， 返回我们约定好的数据格式
	 * @param ActiveQuery $query 查询句柄
	 * @param integer $page_no   页码
	 * @param integer $page_size  每页大小
	 * @return array  我们约定好的数据格式
	 */
	public static function autoPage($query, $page_no, $page_size)
	{
		$countQuery = clone $query;

		$pages = new Pagination([
			'totalCount' => $countQuery->count(),
			'defaultPageSize' => $page_size,
			'page' => $page_no-1,
		]);

		// 查询出来的数据
		$datas = $query->offset($pages->offset)
			->limit($pages->limit)
			->all();

		return [
			'page' => [
				'pageCount' => $pages->pageCount,
				'pageSize' => $pages->pageSize,
				'dataCount' => (int) $countQuery->count(),
			],
			'page_data' => $datas
		];
	}

    /**
     * 生成22位的订单号
     * @param $orderHead 订单类型，从"common\models\finance\Finance里面取，常量开头"ORDER_HEAD_"
     * @return string 22位的订单号
     * @throws \Throwable
     */
    public static function createOrderNo($orderHead) {
        $companyId=\Yii::$app->getUser()->getIdentity()->id;
        $companyIdStr=strval($companyId);
        $companyIdStr= sprintf('%09s', $companyIdStr);
        $companyIdStr=substr($companyIdStr,0,6);
        $orderNo=$orderHead.time().$companyIdStr.rand(1,9);
        return $orderNo;
    }

    /**
	 * 切割字符串，将字符串中的 大写“，”改为小写“,”; 主要用于用户填写的手机号，邮箱用逗号分隔的情况
	 */
    public static function ToggleCaseFormComma($string)
	{
		$str = explode('，', $string);
		return implode(',', $str);
	}
}