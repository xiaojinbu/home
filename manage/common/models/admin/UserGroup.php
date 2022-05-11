<?php

namespace common\models\admin;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_group}}".
 *
 * @property string $id
 * @property string $name
 * @property string $desc
 * @property string $parent_id
 * @property string $updated_at
 * @property string $created_at
 * @property string $company_id
 */
class UserGroup extends \app\components\ActiveRecordCompany
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_group}}';
    }
    
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc'], 'string'],
            [['name', 'order'], 'required'],
            [['parent_id', 'order' , 'company_id'], 'integer'],
            [['name'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'name' => Yii::t('backend', 'Name'),
            'desc' => Yii::t('backend', 'Desc'),
            'parent_id' => Yii::t('backend', 'Parent ID'),
            'order' => Yii::t('backend', 'Order'),
            'updated_at' => Yii::t('backend', 'Updated At'),
            'created_at' => Yii::t('backend', 'Created At'),
            'company_id' => Yii::t('backend', 'Company Id'),
        ];
    }

    /**
     * @inheritdoc
     * @return UserGroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserGroupQuery(get_called_class());
    }
}
