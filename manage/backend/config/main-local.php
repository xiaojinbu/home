<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'OUX1YppF-bHW9cm86EAmg4MwmBQ6Xvni',
        ],
        'redis' => [
            'database' => 113,
        ],
		'charset'=>'UTF-8'
    ],
];

//非测试环境下，且在本地启用debug和gii模块
//线上这两个模块不需求，测试环境下为了获取高性能，这两个模块也不需要！
//所以这两个模块出现的位置足够说明它们完全是服务于开发者的辅助功能。
if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
   
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [//重点，为gii添加新模板
            'model' => [
                'class' => 'yii\gii\generators\model\Generator',
                'templates' => ['hplus' => '@backend/gii/model/hplus']
            ],
            'crud' => [
                'class' => 'yii\gii\generators\crud\Generator',
                'templates' => ['hplus' => '@backend/gii/crud/hplus']
            ],
            'controller' => ['class' => 'yii\gii\generators\controller\Generator'],
            'form' => [
                'class' => 'yii\gii\generators\form\Generator',
                'templates' => ['hplus' => '@backend/gii/form/hplus']//填写别名路径
            ],
            'module' => ['class' => 'yii\gii\generators\module\Generator'],
            'extension' => ['class' => 'yii\gii\generators\extension\Generator'],
        ]
    ];
}

return $config;
