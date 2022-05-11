<?php

namespace app\components\grid;

use yii\helpers\Html;

//在表格td中添加固定宽度的div

class ActionDivColumn extends \yii\grid\ActionColumn {
    
    public $divOptions = [];
    
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];
    
            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }
    
            if ($isVisible && isset($this->buttons[$name])) {
                $url = $this->createUrl($name, $model, $key, $index);
                return call_user_func($this->buttons[$name], $url, $model, $key);
            } else {
                return '';
            }
        }, $this->template);
        
        /**by jorry**/
        if($this->divOptions) {
            $options = $this->divOptions;
            $value = Html::tag('div', $value, $options);
        }
        return $value;
        /***end***/
    }
}