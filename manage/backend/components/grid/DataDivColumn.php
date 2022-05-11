<?php

namespace app\components\grid;

use yii\helpers\Html;

//在表格td中添加固定宽度的div
class DataDivColumn extends \yii\grid\DataColumn {
    
    public $divOptions = [];
    
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            $value = $this->grid->formatter->format($this->getDataCellValue($model, $key, $index), $this->format);

            if($this->divOptions) {
                $options = $this->divOptions;
                $value =  Html::tag('div', $value, $options);
            }
            return $value;

        } else {
            return parent::renderDataCellContent($model, $key, $index);
        }
    }
}