<?php
/* @var $this yii\web\View */
$this->title = Yii::t('backend', 'User Error');
?>
<div class="brand-create wrapper wrapper-content animated fadeInDown">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= $name ?></h5>
                    <div class="ibox-tools">
                        <a href="javascript: location.reload();" class="btn btn-outline btn-success btn-xs btn-refresh"><?= Yii::t('backend', 'Refresh')?></a>
                    </div>
                </div>
                <div class="ibox-content" style="min-height: 570px;">
                    <p>
                        <a href="javascript: history.go(-1);" class="btn btn-default btn-rounded return btn-sm"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i> <?= Yii::t('backend', 'Return')?></a>
                    </p>
                    
                    <div class="middle-box text-center" style="margin-top: 20px;">
                        <h3 class="font-bold"><?= "用户异常{$name}： {$message}" ?></h3>
                    
                        <div class="error-desc">
                            <p>抱歉，页面好像开了个玩笑~</p>
                            <p>如果你真的要继续访问，请联系开发者：技术部</p>
                            <p>路由地址：<?= $this->context->route; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
