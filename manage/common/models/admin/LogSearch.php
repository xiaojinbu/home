<?php

namespace common\models\admin;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\admin\Log;
use yii\helpers\ArrayHelper;

/**
 * LogSearch represents the model behind the search form about `common\models\admin\Log`.
 */
class LogSearch extends Log
{
    public $searchword;
    public $time_title;//时间标识
    public $time_start;//开始时间
    public $time_end;//结束时间
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['searchword', 'time_start', 'time_end', 'method'], 'string'],
            [['searchword', 'time_start', 'time_end', 'method'], 'trim'],
        ];
    }
    
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchword' => '关键词',
            'time_title' => '日志时间范围',
            'time_start' => '开始时间',
            'time_end' => '结束时间',
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Log::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                //'class' => Pagination::className(),
                'defaultPageSize' => Yii::$app->params['config_default_page_size'],
            ],
            'sort' => [
                //'class' => Sort::className(),
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
        
            return $dataProvider;
        }
        
        $query->orFilterWhere(['like', 'route', $this->searchword])
            ->orFilterWhere(['like', 'name', $this->searchword])
            ->orFilterWhere(['like', 'ip', $this->searchword])
            ->orFilterWhere(['like', 'get_data', $this->searchword])
            ->orFilterWhere(['like', 'post_data', $this->searchword]);
        
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'method' => $this->method,
        ]);
        
        if(!empty($this->time_start) && !empty($this->time_end)) {
            $query->andFilterWhere(['between', 'created_at', strtotime($this->time_start), strtotime($this->time_end) + 24*3600 - 1]);
        }
        
        return $dataProvider;
    }
}
