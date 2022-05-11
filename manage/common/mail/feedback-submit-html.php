<p>
    新客户咨询，请关注！
</p>

<h1>问题建议与反馈</h1>

<ul>
	<li>客户名称：<?= $merchant_name ?></li>
    <li>客户类型：<?= $type ?></li>
    <li>电话号码：<?= $phone ?></li>
    <li>咨询内容：<?= $content ?></li>
    <li>来源：http://vip.baopintao.com</li>
    <li>提交时间：<?= Yii::$app->getFormatter()->asDatetime($created_at) ?></li>
</ul>