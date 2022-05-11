<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Breadcrumbs;
use app\modules\admin\helpers\Helper;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view wrapper wrapper-content">
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
                        <a href="javascript: location.reload();" class="btn btn-outline btn-success btn-xs btn-refresh"><?= "<?= " ?><?= $generator->generateString('刷新') ?>?></a>
                    </div>
                </div>
                <div class="ibox-content">
                <p>
                    <?= "<?= " ?>Html::a('<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i> '.<?= $generator->generateString('返回') ?>, ['index'], ['class' => 'btn btn-default btn-rounded return btn-sm']) ?>

					<?= "<?php " ?>if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/update'?>')) {<?= "?>" ?>

    					<?= "<?= " ?>Html::a(<?= $generator->generateString('更新') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary btn-sm']) ?>
					<?= "<?php " ?> } <?= "?>" ?>


					<?= "<?php " ?> if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/delete'?>')) {<?= "?>" ?>

                        <?= "<?= " ?>Html::a(<?= $generator->generateString('删除') ?>, ['delete', <?= $urlParams ?>], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => <?= $generator->generateString('确定要删除这个项目吗?') ?>,
                                'method' => 'post',
                            ],
                        ]) ?>
					<?= "<?php " ?> } <?= "?>" ?>


					<?= "<?php " ?> if (Helper::checkRoute2('<?= '/###/'.$generator->controllerID . '/create'?>')) {<?= "?>" ?>

    					<?= "<?= " ?>Html::a(<?= $generator->generateString('创建') ?>, ['create'], ['class' => 'btn btn-success btn-sm']) ?>
					<?= "<?php " ?> } <?= "?>" ?>

                </p>
            
                <?= "<?= " ?>DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            /*
                            [
                                //'attribute' => 'country.name',//可以直接联表
                                //'label' => Yii::t('backend', 'Belong Country'),//修改label标签
                                //ArrayHelper::getValue($this->model, $attributeName);//获取指定属性值的方法
                                //'value' => function($model, $_this) {return $value;},//匿名函数获取值
                                //'format' => 'text',//指定格式化
                                //'visible' => true,//是否显示
                            ],
                            */
                <?php
                if (($tableSchema = $generator->getTableSchema()) === false) {
                    foreach ($generator->getColumnNames() as $name) {
                    echo "                              '" . $name . "',\n";
                    }
                } else {
                    foreach ($generator->getTableSchema()->columns as $column) {
                        $format = $generator->generateColumnFormat($column);
                    echo "                            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                    }
                }
                ?>
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>