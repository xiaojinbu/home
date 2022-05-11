<?php
return [
    'components' => [

        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'tablePrefix' => '',//前缀
            //Schema
            //'enableSchemaCache' => true,
            //'schemaCacheDuration' => 3600,
            //'schemaCache' => 'cache',//指定存储对象
            //Query
            //'enableQueryCache' => true,
            //'queryCacheDuration' => 3600,
            //'queryCache' => 'cache',//指定存储对象
        ],
    ],
];
