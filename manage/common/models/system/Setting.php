<?php

namespace common\models\system;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidParamException;
use yii\base\ErrorException;
use common\models\kingdee\KingdeeCompany;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @property string $id
 * @property string $code
 * @property string $key
 * @property string $value
 * @property integer $serialized
 */
class Setting extends \app\components\ActiveRecordCompany
{
    const SETTING_ACTIVE = 1;
    const SETTING_FORBID = 0;
    
    const CACHE_KEY = 'setting_models0';

    const TAX_RATE_KEY = 'config_tax_rate';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_array', 'is_visible'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['key'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'code' => Yii::t('common', 'code'),
            'key' => Yii::t('common', 'Configuration items'),
            'value' => Yii::t('common', 'Configuration values'),
            'is_array' => Yii::t('common', 'Serialization or not'),
        ];
    }
    
    /**
     * 批量更新配置数据
     * @param array $post
     * @return boolean
     */
    public function batchSave(array $post)
    {
    	$user = Yii::$app->user->identity;

        foreach ($post as $key=>$value) {
            $model = (new Setting)->findOne(['key'=>$key,'company_id' => $user->company_id]);
            if($model) {
                $model->setAttribute('value', $value);
                $model->setAttribute('company_id', $user->company_id);
                if($model->getOldAttribute('value') != $model->getAttribute('value')) {
                    $model->save(false);//不验证保存
                }
            } else {
                throw new InvalidParamException(Yii::t('common', 'Can\'t find the data,Please create the configuration items in the database!'));
            }
        }
        $this->updateCache();
        
        return true;
    }
    
    /**
     * 更新配置缓存
     * @return boolean
     */
    public function updateCache()
    {
    	$user = Yii::$app->user->identity;
		if (!$user) return true;
        $cache = Yii::$app->getCache();
        $models = Setting::find()->andWhere(['company_id' => $user->company_id])->all();//整个缓存表，包括不可见内容
        //处理数组形式的数据
        foreach ($models as $key=>$model) {
            if($model->is_array) {
                $values = explode(',', $model->value);
                $newValues = [];
                if($values) {
                    foreach ($values as $value) {
                        $v = explode('=>', $value);
                        $newValues[$v[0]] = $v[1];
                    }
                }
                $models[$key]->value = $newValues;
            }
        }
        $data = Json::encode(ArrayHelper::map($models, 'key', 'value'));//获取数据

        if($cache->exists(self::CACHE_KEY)) {
            $cache->delete(self::CACHE_KEY);
        }
        
        $cache->set(self::CACHE_KEY, $data);
        return true;
    }
    
    /**
     * 获取配置缓存
     * @return string
     */
    public function getCache()
    {
        $cache = Yii::$app->getCache();
        if($cache->get(self::CACHE_KEY)) {
            return Json::decode($cache->get(self::CACHE_KEY));//返回数组
        } else {
            if($this->updateCache()) {//就地更新
                return Json::decode($cache->get(self::CACHE_KEY)?:Json::encode([]));//返回数组
            } else {
                //更新失败
                throw new ErrorException(Yii::t('common', 'Update Setting Model Cache Failure!'));
            }
        }
    }
    //创建配置

    public static function getWeek()
    {
        return [
            1 => Yii::t('common', 'Monday'),
            2 => Yii::t('common', 'Tuesday'),
            3 => Yii::t('common', 'Wednesday'),
            4 => Yii::t('common', 'Thursday'),
            5 => Yii::t('common', 'Friday'),
            6 => Yii::t('common', 'Saturday'),
            7 => Yii::t('common', 'Sunday'),
        ];

    }
    //创建金蝶配置
    public static function getKingdeeCompany(){
        $companydb=[];
        $company=KingdeeCompany::find()->asArray()->all();
        foreach ($company as $key=>$value){
            $companydb[$value['fdbname']]=$value['facctName'];
        }
        return $companydb;
    }
    
    /**
     * 删除配置缓存
     * @return boolean
     */
    public function deleteCache()
    {
        //
        return true;
    }
}







