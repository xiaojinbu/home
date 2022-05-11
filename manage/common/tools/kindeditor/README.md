KindEditor中文手册
===========

# 使用方法

##控制器:
在控制器中加入这个方法：
```php
public function actions()
{
    return [
        'kindeditor' => [
            'class' => 'common\tools\kindeditor\kindeditor\KindEditorAction',
        ]
    ];
}
```

##视图:  
先在视图中加入

```php

echo \common\tools\kindeditor\kindeditor\KindEditor::widget([]);
```

或者：

```php
echo $form->field($model,'content')->widget('common\tools\kindeditor\kindeditor\KindEditor',[]);
```

或者：
```php
<?= $form->field($model, 'content')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'clientOptions' => [
        'allowFileManager' => 'true',
        'allowUpload' => 'true',
    ],
]); 
?>
```
## 具体相关功能配置

编辑器相关配置，请在`view 中配置，参数为`clientOptions，比如定制菜单，编辑器大小等等，具体参数请查看[KindEditor官网文档](http://kindeditor.net/doc.php)。

### `editorType`配置
1. 配置为富文本编辑器，默认配置
 
 示例：
 
```php
<?= $form->field($model, 'content')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'clientOptions' => [
        'allowFileManager' => 'true',
        'allowUpload' => 'true',
    ],
]);
 ?>
```
 
2. 这时候配置kindeditor为上传文件按钮，可以自动上传文件到服务器
 示例：
 
```php
<?= $form->field($model, 'article_pic')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'clientOptions' => [
        'allowFileManager' => 'true',
        'allowUpload' => 'true',
    ],
    'editorType' => 'uploadButton',
]);
?>
```
3. 配置kindeditor为取色器
 示例：

```php
<?= $form->field($model, 'content')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'editorType' => 'colorpicker',
]);
?>
```
4. 配置kindeditor为文件管理器，可以查看和选着其上传的文件。
 示例：

```php
<?= $form->field($model, 'article_pic')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'clientOptions' => [
        'allowFileManager' => 'true',
        'allowUpload' => 'true',
    ],
    'editorType' => 'file-manager',
]);
?>
```
5. 配置kindeditor为图片上传对话框。
 示例：

```php
<?= $form->field($model, 'article_pic')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'clientOptions' => [
        'allowFileManager' => 'true',
        'allowUpload' => 'true',
    ],
    'editorType' => 'image-dialog',
]);
?>
```

6.  配置kindeditor为文件上传对话框。
 示例：

```php
<?= $form->field($model, 'article_pic')->widget('common\tools\kindeditor\kindeditor\KindEditor', [
    'clientOptions' => [
        'allowFileManager' => 'true',
        'allowUpload' => 'true',
    ],
    'editorType' => 'file-dialog',
]);
?>
```

简单 示例:
```php
use \common\tools\kindeditor\kindeditor\KindEditor;
echo KindEditor::widget([
    'clientOptions' => [
        //编辑区域大小
        'height' => '500',
        //定制菜单
        'items' => [
            'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
            'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
            'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
            'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
            'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
            'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
            'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
            'anchor', 'link', 'unlink', '|', 'about'
        ],
    ],
]);
```
