<p>
    用户：
    <span style="padding: 3px 8px;border-radius: 4px;color: #fff;background: #f8ac59;font-weight: bold"><?=$merchant_name?></span>
    提交提现申请，提现金额：
    <span style="padding: 3px 8px;border-radius: 4px;color: #fff;background: #ed5565;font-weight: bold">￥<?=$cash_sum?></span>，
    提现类型：
    <span style="padding: 3px 8px;border-radius: 4px;color: #fff;background: #1ab394;font-weight: bold"><?=$type?></span>，
    请前往后台查看
    <a style="padding:3px 8px;border-radius: 4px;color:#fff;background: #1c84c6;font-weight:bold;text-decoration: none;" href="<?=Yii::$app->params['admin_domain_name']?>">点击前往</a>
</p>
<h1>订单号：<?=$order_id?></h1>
