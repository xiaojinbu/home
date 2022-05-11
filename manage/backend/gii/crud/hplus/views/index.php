<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
// use yii\grid\SerialColumn;
// use yii\grid\DataColumn;
// use yii\grid\ActionColumn;
use app\components\grid\SerialDivColumn;
use app\components\grid\DataDivColumn;
use app\components\grid\ActionDivColumn;
use app\modules\admin\helpers\Helper;

use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="category-index wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= "<?= " ?>Html::encode($this->title) ?> <small>
                    <?= "<?php " ?>if (isset($this->params['breadcrumbs'])) {
                        $params = [
                            'tag' => 'ol',
                            'encodeLabels' => true, // 转义
                            'homeLink' => [
                                'label' => <?= $generator->generateString('首页') ?>,
                                'url' => ['index']
                            ],
                            'links' => $this->params['breadcrumbs'],
                        ];
                        
                        echo Breadcrumbs::widget($params);
                    }
                    ?>
                    </small></h5>
                    
                    <div class="ibox-tools">
                        <a href="javascript:;" data-route="<?= "<?= " ?>$this->context->getRoute() ?>" class="btn btn-outline btn-info btn-xs advanced-search-btn"><i class="fa fa-search" aria-hidden="true"></i> <?= "<?= " ?><?= $generator->generateString('高级搜索') ?>?></a>
                        <a href="javascript: location.reload();" class="btn btn-outline btn-success btn-xs btn-refresh"><?= "<?= " ?><?= $generator->generateString('刷新') ?>?></a>
                    </div>
                </div>
                
                <div class="ibox-content">
<?php if(!empty($generator->searchModelClass)): ?>
<?= "                    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

                    <p>
                        <?= "<?php " ?>if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/create'?>')) { <?= "?>" ?>

    						<?= "<?= " ?>Html::a(<?= $generator->generateString('创建 ')  ?> <?=". " ?>$this->title, ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                        <?= "<?php " ?> } <?= "?>" ?>

                    </p>
                    <?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
                    <?= "\n" ?>
                    <?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
                            'dataProvider' => $dataProvider,
                            'options' => ['style'=>'word-wrap: break-word;'],
                            'pager' => ['options' => ['class' => 'pagination pull-right']],
                            //'layout' => '{items}<div class="row"><div class="col-sm-6">{summary}</div><div class="col-sm-6">{pager}</div></div>',
                            'layout' => '<div class="table-box" style="overflow-x: auto;margin-bottom: 15px;">{items}</div><div class="row"><div class="col-sm-6">{summary}</div><div class="col-sm-6">{pager}</div></div>',
                            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover data-table'],
                            'filterPosition' => GridView::FILTER_POS_HEADER,
                            <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n                            'columns' => [\n" : "'columns' => [\n"; ?>
                                [
                                    'class' => SerialDivColumn::class,
                                    'header' => <?= $generator->generateString('Serial') ?>,
                                    'headerOptions' => ['class' => 'text-center'],
                                    'contentOptions' => ['class' => 'text-center'],
                                    'divOptions' => [
                                        'class' => 'w50',
                                    ],
                                ],
                    
                    <?php
                    $count = 0;
                    if (($tableSchema = $generator->getTableSchema()) === false) {
                        foreach ($generator->getColumnNames() as $name) {
                            if (++$count < 6) {?>
    <?php echo "// '" . $name . "',\n";?>
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => '<?= $name ?>',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
                                ],
                            <?php } else {?>
    <?php echo "// '" . $name . "',\n";?>
                                /*
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => '<?= $name ?>',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
                                ],
                                */
                            <?php }
                        }
                    } else {
                        foreach ($tableSchema->columns as $column) {
                            $format = $generator->generateColumnFormat($column);
                            if (++$count < 6) {?>
    <?php echo "// '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";?>
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => '<?= $column->name ?>',
                                    //'format' => '<?= $format ?>',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
                                ],
                            <?php } else {?>
    <?php echo "// '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";?>
                                /*
                                [
                                    'class' => DataDivColumn::class,
                                    'attribute' => '<?= $column->name ?>',
                                    //'format' => '<?= $format ?>',
                                    'divOptions' => [
                                        'class' => 'w100',
                                    ],
                                ],
                                */
                            <?php }
                        }
                    }
                    ?>
                    
                                [
                                    'class' => ActionDivColumn::class,
                                    'header' => <?= $generator->generateString('操作') ?>,
                                    'template' => '{view} {update} {delete}',
                                    'buttonOptions' => [],//统一控制
                                    'divOptions' => [
                                        'class' => 'w120',
                                    ],
                                    'buttons' => [
                                        'view' => function ($url, $model, $key) {
                                            if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/view'?>')) {
                                                return Html::a(<?= $generator->generateString('查看') ?>, $url, ['class' => 'btn btn-outline btn-primary btn-xs']);
                                            }
                                            return '';
                                        },
                                        'update' => function ($url, $model, $key) {
                                            if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/update'?>')) {
                                                return Html::a(<?= $generator->generateString('更新') ?>, $url, ['class' => 'btn btn-outline btn-info btn-xs']);
                                            }
                                            return '';
                                        },
                                        'delete' => function ($url, $model, $key) {
                                            if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/delete'?>')) {
                                                $options = [
                                                    'data-confirm' => <?= $generator->generateString('你确定要删除这个项目吗?') ?>,
                                                    'data-method' => 'post',
                                                    'class' => 'btn btn-outline btn-danger btn-xs',
                                                ];
                                                return Html::a(<?= $generator->generateString('删除') ?>, $url, $options);
                                            }
                                            return '';
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>
                    <?php else: ?>
    <?= "<?= " ?>ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemOptions' => ['class' => 'item'],
                            'itemView' => function ($model, $key, $index, $widget) {
                                return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
                            },
                        ]) ?>
                    <?php endif; ?>
<?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : '' ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>