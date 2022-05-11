<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
// use yii\grid\SerialColumn;
// use yii\grid\DataColumn;
// use yii\grid\ActionColumn;
use app\components\grid\SerialDivColumn;
use app\components\grid\DataDivColumn;
use app\components\grid\ActionDivColumn;
use app\modules\admin\helpers\Helper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\CheckboxColumn;
/* @var $this yii\web\View */
/* @var $searchModel common\models\system\SupplierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title =Yii::t('backend','supplier');
$this->params['breadcrumbs'][] = $this->title;

$exportPath=Url::to(['export']);
$js=<<<JS
   $('.btn-export').click(function(){
     var ids = $("#grid").yiiGridView("getSelectedRows");
	 location.href="{$exportPath}?ids="+ids;
   })

   var checkedCount=0;
   $('input.select-on-check-all').click(function(){
     if($(this).prop('checked')){
		 checkedCount=$("#grid input[type=checkbox]").not('input.select-on-check-all').length;
	 }else{
		 checkedCount=0;
	 }
	  if(checkedCount){
	    $('#grid-filters td').first().addClass('text-center').html('已选'+checkedCount);
	  }else{
	     $('#grid-filters td').first().html('&nbsp');
	  }
   })
   $("#grid input[type=checkbox]").not('input.select-on-check-all').click(function(){
      if($(this).prop('checked')){
	    checkedCount++;
	  }else{
	    checkedCount--;
	  }
	  if(checkedCount){
	    $('#grid-filters td').first().addClass('text-center').html('已选'+checkedCount);
	  }else{
	     $('#grid-filters td').first().html('&nbsp');
	  }
   })

JS;
$this->registerJs($js);
?>

<div class="category-index wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= Html::encode($this->title) ?> <small>
                    <?php if (isset($this->params['breadcrumbs'])) {
                        $params = [
                            'tag' => 'ol',
                            'encodeLabels' => true, // 转义
                            'homeLink' => [
                                'label' => '首页',
                                'url' => ['index']
                            ],
                            'links' => $this->params['breadcrumbs'],
                        ];
                        
                        echo Breadcrumbs::widget($params);
                    }
                    ?>
                    </small></h5>
                    
                    <div class="ibox-tools">
					    <?= Html::a(Yii::t('backend', 'logout'), ['/admin/user/logout'], ['data-method' => 'post','class'=>'btn btn-outline btn-info btn-xs'])?>
                        <a href="javascript: location.reload();" class="btn btn-outline btn-success btn-xs btn-refresh"><?= '刷新'?></a>
                    </div>
                </div>
                
                <div class="ibox-content">
   
                    <p>
                        <?php if (Helper::checkRoute2('/###/supplier/create')) { ?>
    						<?= Html::a('创建',['create'], ['class' => 'btn btn-success btn-sm']) ?>
							<?= Html::a('导出CSV', 'javascript:;', ['class' => 'btn btn-primary btn-sm btn-export']) ?>
                        <?php  } ?>
                    </p>
                                        
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'options' => ['style'=>'word-wrap: break-word;','id'=>'grid'],
                            'pager' => ['options' => ['class' => 'pagination pull-right']],
                            //'layout' => '{items}<div class="row"><div class="col-sm-6">{summary}</div><div class="col-sm-6">{pager}</div></div>',
                            'layout' => '<div class="table-box" style="overflow-x: auto;margin-bottom: 15px;">{items}</div><div class="row"><div class="col-sm-6">{summary}</div><div class="col-sm-6">{pager}</div></div>',
                            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover data-table'],
                            'filterPosition' => GridView::FILTER_POS_HEADER,
                            'filterModel' => $searchModel,
                            'columns' => [
                                [
                                    'class' => CheckboxColumn::class,
									'name'=>'id',
                                    'headerOptions' => ['class' => 'text-center'],
                                    'contentOptions' => ['class' => 'text-center'],
                                    'checkboxOptions' => function ($model, $key, $index, $column) {
										return ['value' => $model->id];
									 }
                                ],
                    
                        // 'id',
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => 'id',
                                    //'format' => 'text',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
									'filter' =>['1'=>'大于10','2'=>'大于等于10','3'=>'小于10','4'=>'小于等于10'],
                                    'filterInputOptions' => ['class' => 'form-control', 'value' => null, 'prompt' =>'全部'],
                                ],
                                // 'name',
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => 'name',
                                    //'format' => 'text',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
                                ],
                                // 'code',
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => 'code',
                                    //'format' => 'text',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
                                ],
                                // 't_status',
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => 't_status',
                                    //'format' => 'text',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
									'filter' =>['ok'=>'ok','hold'=>'hold'],
                                    'filterInputOptions' => ['class' => 'form-control', 'value' => null, 'prompt' =>'全部'],
                                ],
                                                
                                [
                                    'class' => ActionDivColumn::class,
                                    'header' => '操作',
                                    'template' => '{view} {update} {delete}',
                                    'buttonOptions' => [],//统一控制
                                    'divOptions' => [
                                        'class' => 'w120',
                                    ],
                                    'buttons' => [
                                        'view' => function ($url, $model, $key) {
                                            if (Helper::checkRoute2('/###/supplier/view')) {
                                                return Html::a('查看', $url, ['class' => 'btn btn-outline btn-primary btn-xs']);
                                            }
                                            return '';
                                        },
                                        'update' => function ($url, $model, $key) {
                                            if (Helper::checkRoute2('/###/supplier/update')) {
                                                return Html::a('更新', $url, ['class' => 'btn btn-outline btn-info btn-xs']);
                                            }
                                            return '';
                                        },
                                        'delete' => function ($url, $model, $key) {
                                            if (Helper::checkRoute2('/###/supplier/delete')) {
                                                $options = [
                                                    'data-confirm' => '你确定要删除这个项目吗?',
                                                    'data-method' => 'post',
                                                    'class' => 'btn btn-outline btn-danger btn-xs',
                                                ];
                                                return Html::a('删除', $url, $options);
                                            }
                                            return '';
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>
                                        
                </div>
            </div>
        </div>
    </div>
</div>