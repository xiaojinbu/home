<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Breadcrumbs;
use app\modules\admin\helpers\Helper;

/* @var $this yii\web\View */
/* @var $model common\models\system\Supplier */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' =>Yii::t('backend','supplier'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-view wrapper wrapper-content">
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
                        <a href="javascript: location.reload();" class="btn btn-outline btn-success btn-xs btn-refresh"><?= '刷新'?></a>
                    </div>
                </div>
                <div class="ibox-content">
                <p>
                    <?= Html::a('<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i> '.'返回', ['index'], ['class' => 'btn btn-default btn-rounded return btn-sm']) ?>

					<?php if (Helper::checkRoute2('/###/supplier/update')) {?>
    					<?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
					<?php  } ?>

					<?php  if (Helper::checkRoute2('/###/supplier/delete')) {?>
                        <?= Html::a('删除', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => '确定要删除这个项目吗?',
                                'method' => 'post',
                            ],
                        ]) ?>
					<?php  } ?>

					<?php  if (Helper::checkRoute2('/###/supplier/create')) {?>
    					<?= Html::a('创建', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
					<?php  } ?>
                </p>
            
                <?= DetailView::widget([
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
                                            'id',
                            'name',
                            'code',
                            't_status',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>