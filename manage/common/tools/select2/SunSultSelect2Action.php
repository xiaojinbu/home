<?php
namespace common\tools\select2;
/**
 * Notes:重写select2搜索，搜索字段修改为多条件模糊查询，显示字段修改为数据，进行拼接显示
 * 参数格式：showField['showField1','showField2']
 * 显示格式：（showField[0]）showField[1]
 * User: Gavin
 * Date: 2019/07/12
 * Time: 16:44
 */
class SunSultSelect2Action extends Select2Action
{
    public $showField = ['name'];
    public function run()
    {
        $className = $this->className;
        $this->page = empty($this->page)?1:$this->page;
        $query = $className::find();

        if (count($this->where) > 0) {
            $query = $query->andFilterWhere($this->where);
        }
        $or_where= ['or'];
        foreach ($this->searchFields as $field) {
            $or_where[] = ['like',$field,$this->keyword];
        }
        $query = $query->andFilterWhere($or_where);
        $count = $query->count();//总数
        $models = $query->limit($this->limit)->offset($this->limit*($this->page-1))->all();//分页

        $this->showField = is_array($this->showField)? $this->showField : ['name'];
        $results = [];

        foreach ($models as $model) {
            $text = '';
            if ( count($this->showField) >= 2){
                $text = '('.$model->{$this->showField[0]}.')'.$model->{$this->showField[1]};
            }else{
                $text = $model->{$this->showField[0]};
            }
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