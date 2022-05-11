<?php

namespace common\models\system;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\system\Supplier;

/**
 * SupplierSearch represents the model behind the search form about `common\models\system\Supplier`.
 */
class SupplierSearch extends Supplier
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'code', 't_status'], 'safe'],
        ];
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
        $query = Supplier::find();

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
                    //'parent_id' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
		switch($this->id){
		  case'1':
			$query->andFilterWhere(['>','id',10]);
		  break;
		  case'2':
			 $query->andFilterWhere(['>=','id',10]);
		  break;
		  case'3':
			 $query->andFilterWhere(['<','id',10]);
		  break;
		  case'4':
			 $query->andFilterWhere(['<=','id',10]);
		  break;
		  default:
		  break;
		}
        // grid filtering conditions
        

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 't_status', $this->t_status]);

        return $dataProvider;
    }
}
