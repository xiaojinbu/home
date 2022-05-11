<?php
namespace app\assets;
use yii\web\AssetBundle;

/**
 * Created by PhpStorm.
 * User: zpc
 * Date: 2019/12/27
 * Time: 10:16
 */

class DragulaAsset extends AssetBundle
{
	//这个属性是设置不能被web访问资源
	public $sourcePath = '@app/assets/hplus/dragula/';

	public $js = [
		'dist/dragula.min.js',
	];

	public $css = [
		'dist/dragula.min.css',
		'dist/style.css',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];
}