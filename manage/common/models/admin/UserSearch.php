<?php

namespace common\models\admin;

use app\modules\admin\helpers\Helper;
use common\models\system\Company;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\admin\User;
/**
 * UserSearch represents the model behind the search form about `common\models\admin\User`.
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'user_group_id', 'created_at', 'updated_at'], 'integer'],
            [['username', 'email', 'phone','company_name'], 'safe'],
        ];
    }
    
    public function beforeValidate()
    {
        //此方法用于覆盖User中的beforeValidate()特殊需求
        return true;
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
        $query = User::find();
        $query = User::find()->select(['user.*', 'company.name as company_name'])->from(['user' => User::tableName()])->with('company')->joinWith([
            'company' => function ($query) {
                //联表别名(匿名函数解决自联问题)
                $query->from(['company' => Company::tableName()]);
            }
        ]);

        $query = $query->self('user');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                //'class' => Pagination::className(),
//                'defaultPageSize' => Yii::$app->params['config_default_page_size'],
                'defaultPageSize' => 50,
            ],
            'sort' => [
                //'class' => Sort::className(),
                'defaultOrder' => [
                    'user_group_id' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            //print_r($this->getErrors());
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user.id' => $this->id,
            'user.status' => $this->status,
            'user.user_group_id' => $this->user_group_id,
            'user.created_at' => $this->created_at,
            'user.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'company.name', $this->company_name])
            ->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['or',['like','user.username',$this->username],['like','user.phone',$this->username]]);

        return $dataProvider;
    }
}
