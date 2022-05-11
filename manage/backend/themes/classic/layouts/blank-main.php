<?php
//注意：
if(Yii::$app->getUser()->getIsGuest()) {
    return;//如果是游客，则布局模板中的用户登录状态是无法使用的，直接不渲染，最终会跳出
}
?>

<?= $content ?>