百度Ueditor

### 应用


```
public function actions()
{
    return [
        'upload' => [
            'class' => 'common\tools\ueditor\UeditorAction',
        ]
    ];
}
```

view:  

```
echo \common\tools\ueditor\Ueditor::widget([]);
```

或者：

```
echo $form->field($model,'colum')->widget('common\tools\ueditor\Ueditor',[]);
```

简单实例:  
```php
use \common\tools\ueditor\Ueditor;
echo Ueditor::widget([
    'clientOptions' => [
        //编辑区域大小
        'initialFrameHeight' => '200',
        //设置语言
        'lang' =>'en', //中文为 zh-cn
        //定制菜单
        'toolbars' => [
            [
                'fullscreen', 'source', 'undo', 'redo', '|',
                'fontsize',
                'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'removeformat',
                'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|',
                'forecolor', 'backcolor', '|',
                'lineheight', '|',
                'indent', '|'
            ],
        ]
]);
```

简单实例:  
```php
public function actions()
{
    return [
        'upload' => [
            'class' => 'common\tools\ueditor\UeditorAction',
            'config' => [
                "imageUrlPrefix"  => "http://www.baidu.com",//图片访问路径前缀
                "imagePathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}" //上传保存路径
            ],
        ]
    ];
}
```
