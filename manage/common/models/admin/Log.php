<?php

namespace common\models\admin;

use Yii;

/**
 * This is the model class for table "{{%log}}".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $user_name
 * @property string $route
 * @property string $name
 * @property string $method
 * @property string $get_data
 * @property string $post_data
 * @property string $ip
 * @property string $agent
 * @property string $md5
 * @property string $created_at
 */
class Log extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name', 'route', 'name', 'method', 'get_data', 'post_data', 'ip', 'agent', 'md5'], 'default', '空'],
            [['user_id'], 'integer'],
            [['get_data', 'post_data', 'agent', 'searchword'], 'string'],
            [['user_name'], 'string', 'max' => 80],
            [['route'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 150],
            [['method'], 'string', 'max' => 10],
            [['ip', 'created_at'], 'string', 'max' => 50],
            [['md5'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'id'),
            'user_id' => Yii::t('common', 'User').Yii::t('common', 'id'),
            'user_name' => Yii::t('common', 'User name'),
            'route' => Yii::t('common', 'routing'),
            'name' => Yii::t('common', 'name'),
            'method' => Yii::t('common', 'method'),
            'get_data' => Yii::t('backend', 'Get Data'),
            'post_data' => Yii::t('backend', 'Post Data'),
            'ip' => Yii::t('backend', 'IP'),
            'agent' => Yii::t('backend', 'Agent'),
            'md5' => Yii::t('backend', 'Md5'),
            'created_at' => Yii::t('backend', 'Create At'),
        ];
    }

    /**
     * @inheritdoc
     * @return LogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LogQuery(get_called_class());
    }
}
