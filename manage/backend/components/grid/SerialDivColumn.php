<?php

namespace app\components\grid;

use yii\helpers\Html;

//在表格td中添加固定宽度的div

class SerialDivColumn extends \yii\grid\SerialColumn {
    
    public $divOptions = [];
    
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = '';
        $pagination = $this->grid->dataProvider->getPagination();
        if ($pagination !== false) {
            $value = $pagination->getOffset() + $index + 1;
        } else {
            $value = $index + 1;
        }
        /**by jorry**/
        if($this->divOptions) {
            $options = $this->divOptions;
            $value = Html::tag('div', $value, $options);
        }
        return $value;
        /***end***/
    }
}