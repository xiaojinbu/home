<?php

namespace app\components\grid;

use yii\helpers\Html;

//在表格td中添加固定宽度的div
//注意：此类为产品价格管控专门定制的！！！
class DataDiv2Column extends \yii\grid\DataColumn {
    
    public $divOptions = [];
    
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            $value = $this->grid->formatter->format($this->getDataCellValue($model, $key, $index), $this->format);

            /**by jorry**/
            if($this->divOptions) {
                $options = $this->divOptions;
                $options['data-id'] = $model->id;
                $value = Html::tag('div', $value, $options);
            }
            return $value;
            /***end***/
        } else {
            return parent::renderDataCellContent($model, $key, $index);
        }
    }
}