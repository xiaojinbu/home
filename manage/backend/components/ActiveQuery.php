<?php
/**
 * @link http://www.turen2.com/
 * @copyright Copyright (c) 土人开源CMS
 * @author developer qq:980522557
 */

namespace app\components;

use common\models\admin\AuthTable;
use common\models\admin\RoleData;
use common\models\manage\Servicer;
use Yii;
use common\models\system\CompanyGroup;
use yii\helpers\ArrayHelper;
use common\models\system\Company;
use common\models\admin\User;

class ActiveQuery extends \yii\db\ActiveQuery
{
	/**
	 * 活动状态
	 * @param string $field
	 * @param int $value
	 * @return \app\components\ActiveQuery
	 */
	public function active ($field = 'status', $value = ActiveRecord::STATUS_ACTIVE)
	{
		return $this->andWhere('[[' . $field . ']]=' . $value);
	}

	/**
	 * 指定审核状态
	 * @param string $field
	 * @param int $value
	 * @return \app\components\ActiveQuery
	 */
	public function status ($field = 'status', $value = ActiveRecord::STATUS_ACTIVE)
	{
		return $this->andWhere('[[' . $field . ']]=' . $value);
	}

	/**
	 * 指定删除状态
	 * 默认为已经删除的状态
	 * @return \app\components\ActiveQuery
	 */
	public function delstate ($status = ActiveRecord::IS_DEL)
	{
		return $this->andWhere('[[is_del]]=' . $status);
	}

	/**
	 * 当前语言
	 * @return \app\components\ActiveQuery
	 */
	public function current ()
	{
		return $this->andWhere('[[lang]]=' . '\'' . GLOBAL_LANG . '\'');
	}

	/**
	 * 自己公司的
	 * @param null $tableName
	 * @return ActiveQuery
	 * @throws \Throwable
	 */
	public function self ($tableName = null, $company_id = 0)
	{
		//管理员拥有全部查询权限
		if (!Yii::$app->getUser()->isGuest && Yii::$app->getUser()->getIdentity()->getIsAdmin()) {
			return $this->andWhere('1=1');
		}
		//增加对应的公司id
		$companyId = 0;
		if (!empty($company_id) && $company_id > 0) {
			$companyId = $company_id;
		} else {
			$companyId = Yii::$app->getUser()->getIdentity()->company_id;
		}

		if ($tableName != null && !empty($tableName)) {
			return $this->andWhere('[[' . $tableName . '.company_id]]=' . $companyId);
		}
		if (User::getIsGroupCompanyAdmin()) {
			$user_id = Yii::$app->user->id;
			$catch_key = 'catch_self' . $user_id;
			$catch = Yii::$app->cache;
			$company_ids = '';
			$catch_value = $catch->get($catch_key);
			if ($catch_value) {
				$company_ids = $catch_value;
			} else {
				$companyGroup = CompanyGroup::find()->andWhere(['manage_user_id' => $user_id])->all();
				//集群下的公司
				$company = Company::find()->andWhere(['group_id' => ArrayHelper::getColumn($companyGroup, 'id')])->all();
				$company_ids = ArrayHelper::getColumn($company, 'id');
				$catch->set($catch_key, $company_ids, 60 * 60);
			}
			return $this->andWhere(['company_id' => $company_ids]);
		}
		//return $this->andWhere('[[company_id]]='.$companyId);
		return $this->andWhere(['company_id' => [$companyId, 0]]);
	}

	/**验证数据权限
	 * @param string $table 数据所属表
	 * @param string $table_alias 连表别名
	 * @param string $field 查询字段
	 * @return ActiveQuery
	 * @throws \Throwable
	 */
	public function dataAuth ($table, $field = 'id', $table_alias = null)
	{
		//管理员拥有全部查询权限
		if (!Yii::$app->getUser()->isGuest && (Yii::$app->getUser()->getIdentity()->getIsAdmin() || Yii::$app->user->identity->isAssignments('企业管理员'))) {
			return $this->andWhere('1=1');
		}
		//获取当前登录用户角色
		$role_res = Yii::$app->authManager->getAssignments(Yii::$app->user->id);
		$role = array_keys($role_res);

		$table = Yii::$app->db->tablePrefix . $table;
		$table_res = AuthTable::find()->select(['id'])->where(['table_name' => $table])->asArray()->one();
		$auth_role_data = RoleData::find()->where(['role' => $role, 'table_id' => $table_res['id']])->asArray()->self()->all();

		if (!$auth_role_data) {
			return $this->andWhere('1=1');
		}
		$data_id = array_column($auth_role_data, 'data_id');
		if ($table_alias) {
			$key = $table_alias . '.' . $field;
		} else {
			$key = $field;
		}
		return $this->andWhere(['in', $key, $data_id]);
	}

	/**
	 * 如果当前角色是服务商的话，那么需要考虑当前的搜索条件是否需要增加这个过滤条件
	 */
	public function servicer ($tableName = null)
	{
		//管理员拥有全部查询权限
		if (!Yii::$app->getUser()->isGuest && Yii::$app->getUser()->getIdentity()->getIsAdmin()) {
			return $this->andWhere('1=1');
		}
		if (Yii::$app->user->identity->isAssignments("服务商")) {
			if ($servicer = Servicer::findOne(['user_id' => Yii::$app->user->id])) {
				if ($tableName != null && !empty($tableName))
					return $this->andWhere('[[' . $tableName . '.servicer_id]]=' . $servicer->id);
				return $this->andWhere('[[servicer_id]]=' . $servicer->id);
			} else {
				return $this->andWhere('1=2');
			}
		}
		return $this->andWhere('1=1');
	}


	/**
	 * @inheritdoc
	 * @return Column[]|array
	 */
	public function all ($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Column|array|null
	 */
	public function one ($db = null)
	{
		return parent::one($db);
	}
}