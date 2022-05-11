<?php

namespace app\assets;

use yii\web\AssetBundle;

class VueElementAsset extends AssetBundle
{
	//这个属性是设置不能被web访问资源
	public $sourcePath = '@app/assets/hplus/vue-element/';

	public $css = [
		'https://unpkg.com/element-ui/lib/theme-chalk/index.css'
	];

	public $js = [
		'https://unpkg.com/element-ui/lib/index.js'
	];

	public $depends = [
		'app\assets\VueAsset',
	];
}