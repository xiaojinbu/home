<?php
namespace common\components\queue;

use Yii;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: zpc
 * Date: 2019/12/14
 * Time: 20:29
 */

class Queue extends \yii\queue\db\Queue
{
	/**
	 * @var string table name
	 */
	public $tableName = '{{%manage_queue}}';

	public $company_id;

	public function init ()
	{
		parent::init(); // TODO: Change the autogenerated stub
		if (!Yii::$app->user->isGuest) {
			$this->company_id = Yii::$app->user->identity->company_id;
		}

	}

	/**
	 * @inheritdoc
	 */
	protected function pushMessage($message, $ttr, $delay, $priority)
	{
		$this->db->createCommand()->insert($this->tableName, [
			'channel' => $this->channel,
			'job' => $message,
			'pushed_at' => time(),
			'ttr' => $ttr,
			'delay' => $delay,
			'priority' => $priority ?: 1024,
			'company_id' => $this->company_id,
		])->execute();

		$tableSchema = $this->db->getTableSchema($this->tableName);
		return $this->db->getLastInsertID($tableSchema->sequenceName);
	}
}