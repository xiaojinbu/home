<?php

return [
    'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(dirname(__DIR__))) . '/vendor',
    'bootstrap' => [
        'queue',//前台系统队列
        'b_queue',//后台系统队列
    ],
    'components' => [
       
        //mysql锁机制
        'mutex' => [
            'class' => 'yii\mutex\MysqlMutex',
        ],

        'mail' => [
            'class' => 'app\components\aliyunmail\AliyunMailJob',
            'account_name' => 'sunsult@vip.baopintao.com',
            'keyId' => 'LTAIKw5qyC8ymO1U',
            'keySecret' => '4poWwqejzOSNTyPR2ibMdtPdm0gxJp'
        ],
        //商户平台队列
        'queue' => [
            //'class' => 'yii\queue\file\Queue',//队列类型
            //'path' => '@common/runtime/queue',//存储路径

            'class' => 'yii\queue\db\Queue',//队列类型
            'channel' => 'merchant_channel',//队列通道
            'db' => 'db',//对接的数据库资源为db库
            'mutex' => 'mutex',//锁机制
            'deleteReleased' => false,//清除发布的信息
            'serializer' => 'yii\queue\serializers\JsonSerializer',//存储格式
            'ttr' => 300,//重试停留时间
            'attempts' => 1,//默认重试次数
        ],
        //后台队列
        'b_queue' => [
            'class' => 'yii\queue\db\Queue',//队列类型
            'channel' => 'backend_channel',//队列通道
            'db' => 'db',//对接的数据库资源为db库
            'mutex' => 'mutex',//锁机制
            'deleteReleased' => false,//清除发布的信息
            'serializer' => 'yii\queue\serializers\JsonSerializer',//存储格式
            'ttr' => 300,//重试停留时间
            'attempts' => 1,//默认重试次数
        ],
        
        'cache' => [
			//'class' => 'yii\caching\FileCache',    //注释掉文件缓存
			'class'=>'yii\redis\Cache',    //使用redis缓存作为项目缓存
			'redis'=>[        //配置redis
//                'hostname' => 'sunsult1680.redis.rds.aliyuncs.com',
				'hostname' => '112.74.201.26',
				'port' => '6379',
				'password' => 'SiSabpt530560',
				'database' => 2
			],
        ],
        //配置这个组件是为了console控制台应用中可以运行rbac数据库升级程序，公共配置一下，具体应用中会被覆盖
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'formatter' => [
            // 处理本地化格式，包括时间、货币、语言习惯
            'class' => 'yii\i18n\Formatter',
            'timeZone' => 'Asia/Shanghai', // 上海时间（app默认也有个时区，被覆盖）
            'defaultTimeZone' => 'UTC', // 使用协调世界时
            'nullDisplay' => '(未设置)',//未设置时的默认值
            'dateFormat' => 'yyyy年MM月dd日',
            'timeFormat' => 'HH:mm:ss',
            'datetimeFormat' => 'yyyy年MM月dd日 HH:mm:ss'
            // currencyCode
        ],
        'mailer' => [// 公共发送邮件配置
            'class' => 'yii\swiftmailer\Mailer',//这个类中配置模板
            'viewPath' => '@app/mail',
            //开发调试用的（邮件在@runtime/mail目录下）
            'useFileTransport' => false,//这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'fileTransportPath' => '@app/runtime/mail',//配合FileTransport，指定邮件内容的缓冲位置
            'enableSwiftMailerLogging' => true,//开启log
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.sunsult.com',
                'username' => 'vip@sunsult.com',
                'password' => 'Aa123456789',
                'port' => '25',
                //'encryption' => 'tls',//tls or ssl(tls可以认为是ssl的升级版)
            ],
            'messageConfig'=>[//这部分可以在send发邮件时临时配置
                'charset'=>'UTF-8',
                'from'=>['vip@sunsult.com'=>'本商技术平台通知'],
                //'bcc' => ['aaa@163.com'=>'aaa'],//加密超送，(cc为普通超送)
            ],
        ],
        'urlManager' => [
           'enablePrettyUrl' => true,	//开启伪静态URL模式
            'showScriptName' => true,	//生成的网址里不带入口脚本名称
            //'enableStrictParsing' => true,	//开启严格伪静态模式
            //'rules' => [
            //伪静态规则配置，下面会讲解如何填写
            //],

        ],
    ],
];
