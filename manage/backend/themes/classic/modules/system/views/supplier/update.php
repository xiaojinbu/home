<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model common\models\system\Supplier */

$this->title = '更新'.Yii::t('backend','supplier').': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' =>Yii::t('backend','supplier'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';
?>
<div class="supplier-update wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= Html::encode($this->title) ?> <small>
                    <?php                     if (isset($this->params['breadcrumbs'])) {
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
                    	<a href="javascript: location.reload();" class="btn btn-outline btn-success btn-xs btn-refresh">刷新</a>
                    </div>
                </div>
                <div class="ibox-content">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>