<?php
namespace common\tools\select2;
use common\models\agent\AgentFranchise;
use common\models\manage\Messenger;

/**
 * Notes:重写select2搜索，搜索字段修改为多条件模糊查询，显示字段修改为数据，进行拼接显示
 * 参数格式：showField['showField1','showField2']
 * 显示格式：（showField[0]）showField[1]
 * User: Gavin
 * Date: 2019/07/12
 * Time: 16:44
 */
class AgentSelect2Action extends Select2Action
{
    public $type = 1;//1为代理商，2为加盟商
    public function run()
    {
        $className = $this->className;
        if($this->type==2){
            $className = AgentFranchise::class;
        }
        $this->page = empty($this->page)?1:$this->page;
        $query = $className::find();

        $query = $query->select(['agent.messenger_id as id','agent.sign_body as sign_body','messenger.real_name as messenger_name'])->from(['agent' => $className::tableName()])
            ->with('messenger')
            ->joinWith([
                'messenger' => function($query){
                    $query->from(['messenger' => Messenger::tableName()]);
                }
            ])->groupBy(['agent.messenger_id','agent.shop_type_id']);

        if (count($this->where) > 0) {
            $query = $query->andFilterWhere($this->where);
        }
        $query->andFilterWhere(['like', 'messenger.real_name', $this->keyword]);
        $count = $query->count();//总数
        $models = $query->limit($this->limit)->offset($this->limit*($this->page-1))->all();//分页

        $results = [];

        foreach ($models as $model) {
            $text = '';
            $name='messenger_name';
            $sign='sign_body';

            $text = $model->{$name}.'('.$model->{$sign}.')';

            $results[] = [
                'id' => $model->{$this->valField},
                'text' => $text,
            ];
        }

        return [
            'status'=>0,
            'msg'=>$results,
            'total_count' => $count,
            //'incomplete_results' => true,
        ];
    }
}