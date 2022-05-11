<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

//配置原则：能在Application、Moudule中配置的，都要在main中配置，谢谢...
return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'name' => '供应商测试',
    'version' => '1.0',
    'charset' => 'UTF-8',
    'sourceLanguage' => 'en-US', // 默认源语言
    'language' => 'zh-CN', // 默认当前环境使用的语言
    'controllerNamespace' => 'app\\modules\\common\\controllers',//默认预加载控制器类的命名空间
    'defaultRoute' => 'system/supplier/index', // 默认路由，后台默认首页
    'layout' => 'main', // 默认布局
    //'viewPath' => '@app/themes/classic',
    //'layoutPath' => '@app/themes/classic/layouts',//View组件中可配配置

    //系统初始化时预处理核心组件后，调用此组件的接口bootstrap()方法
    'bootstrap' => [
        'log',
        'queue',//队列
        'app\bootstrap\BootstrapConfig'
    ],
    'modules' => [
        'admin' => [//管理员及权限管理
            'class' => 'app\modules\admin\Module',
            //'defaultRoute' => 'assignment/index',//模块的默认路由
        ],
        'common' => [//主要解决公共页面的展示，iframe主框架、404、503、首页等
            'class' => 'app\modules\common\Module',
            //'defaultRoute' => 'home/index',//默认进入home控制器，在全局已配置
        ],
        'system' => [//系统相关模块，配置、绑定、第三方等
            'class' => 'app\modules\system\Module',
            //'defaultRoute' => 'default/index',
        ],
    ],
    'components' => [

        'queue' => [
            'class' => 'common\components\queue\Queue',
            'db' => 'db', // connection ID
            'tableName' => '{{%manage_queue}}', // table
            'channel' => 'default', // queue channel
            'mutex' => 'yii\mutex\MysqlMutex', // Mutex used to sync queries
            'serializer' => 'yii\queue\serializers\JsonSerializer',//存储格式
        ],
       
        'export' => [//office文档导出，excel、csv、txt
            'class' => 'common\components\phpoffice\DataExport',
        ],
        'input' => [//office文档导入，excel、csv、txt
            'class' => 'common\components\phpoffice\DataInput',
        ],
		
		'request' => [
            'class' => 'yii\web\Request',
            'cookieValidationKey' => 'OUX1YppF-bHW9cm86EAmg4MwmBQ6Xvni',
            'csrfParam' => '_csrf-backend',
            'enableCsrfValidation' => true,//默认开启csrf验证（前提）
            'enableCsrfCookie' => true,//默认开启了基于cookie的csrf，否则将以session传递验证数据
            'enableCookieValidation' => true,//默认配合上面启用验证
        ],
        'user' => [// 用户持久组件配置
            // 'class' => 'yii\web\User',//默认
            // 身份认证模型
            'identityClass' => 'common\models\admin\User',
            // 重点，当开始基于cookie登录时，这个数组就是初始化cookie的值
            // 即专为身份验证的cookie配置专用的cookie对象，以下就是对象的初始化参数，cookie对象已经实现了ArrayIterator操作
            'identityCookie' => [
                'name' => '_identity-backend',
                'httpOnly' => true
            ], // 可以实现如子站点同时登录
            // 是否启用基于cookie的登录，即保持cookie和session的相互恢复，所以它是基于session
            'enableAutoLogin' => true,
            // 是否基于会话，如果是restful，那么关闭使用无状态验证访问
            'enableSession' => true,
            // 登录的有效时间，也叫验证的有效时间，如果没有设置则以seesion过期时间为准
            // 即，用户在登录状态下未操作的时间间隔有效为authTimeout，超过就退出，Note that this will not work if [[enableAutoLogin]] is true.
            // 并返回超时提示
            'authTimeout' => null,
            // 设置一个绝对的登出时间，过期时间不会自动延期，到点儿就失效
            'absoluteAuthTimeout' => null,
            // 持久层是否延续最新时间，使cookie保持最新
            'autoRenewCookie' => true,
            // 基于loginRequired()，不可为null
            'loginUrl' => [
                '/admin/user/login'
            ],

            // 以下是以session来存储相关的参数值的
            'authTimeoutParam' => '__sales_volume_expire', // 过期时间session标识
            'idParam' => '__sales_volume_id', // 用户登录会话id的session标识
            'absoluteAuthTimeoutParam' => '__sales_volume_absoluteExpire',
            'returnUrlParam' => '__sales_volume_returnUrl' // 这个是重点，实现无权访问再登录后跳转到原来的rul，这个url就是__returnUrl，记录在session中
        ],
        'formatter' => [
            // 处理本地化格式，包括时间、货币、语言习惯
            'class' => 'yii\i18n\Formatter',
            'timeZone' => 'Asia/Shanghai', // 上海时间（app默认也有个时区，被覆盖）
            'defaultTimeZone' => 'UTC', // 使用协调世界时
            'nullDisplay' => 0,//未设置时的默认值
            'dateFormat' => 'php:Y-m-d',
            'timeFormat' => 'short',
            'datetimeFormat' => 'short',
            'currencyCode' => 'CNY',
        ],
        'authManager' => [// 使用数据库存储权限(默认是文件)
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
            'itemTable' => '{{%auth_item}}',
            'itemChildTable' => '{{%auth_item_child}}',
            'assignmentTable' => '{{%auth_assignment}}',
            'ruleTable' => '{{%auth_rule}}',
            //对所有员工，默认的就是这些角色，即员工已经有了这些角色！！！
            //这个配置在系统的BootstrapConfig中初始化配置
            //'defaultRoles' => ['超级管理员角色', '基础访客角色', '默认测试角色'],
        ],
        'session' => [// session配置
            'class' => 'yii\web\DbSession',//也可以转移到memcache缓存，CacheSession
            'name' => 'phpsession',//session name
            'sessionTable' => '{{%session}}',
            'timeout' => 3600, // 超时设置
            /*
			readCallback' => function ($fields) {
                //回调，把更多信息从session读出
                return [
                    'user_id' => $fields['user_id'],
                    'ip' => $fields['ip'],
                    'is_trusted' => $fields['is_trusted'],
                    'expireDate' => Yii::$app->formatter->asDate($fields['expire']),
                ];
            }
            'writeCallback' => function ($session) {
                //回调，把更多的信息存储在了session新的字段中中
                return [
                    'user_id' => Yii::$app->user->id,
                    'ip' => Yii::$app->request->getUserIP(),//$_SERVER['REMOTE_ADDR'],
                    'is_trusted' => $session->get('is_trusted', false),//是否受信任is_trusted，用于风控系统标识
                ];
            }
            */
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
//            'hostname' => 'sunsult1680.redis.rds.aliyuncs.com',
			'hostname' => '112.74.201.26',
			'port' => '6379',
			'password' => 'SiSabpt530560',
			'database' => 2
        ],
        'view' => [
            // 主题配置(module目录下的views > 根目录下的views > 主题下的模板)
            'class' => 'app\components\View',
            'theme' => [
                'class' => 'yii\base\Theme',
                'basePath' => '@app/themes/classic',//主题所在文件路径
                'baseUrl' => '@app/themes/classic',//与主题相关的url资源路径
                'pathMap' => [
                    // 这里可以优先使用指定主题，也可以指定最小单位主题
                    // '@app/views' => [
                        // '@app/themes/default',//替换为default主题
                        // '@app/themes/classic',//默认主题
                    // ],
                    '@app/modules' => '@app/themes/classic/modules',//模板
                    '@app/widgets' => '@app/themes/classic/widgets',//部件
                    '@app/views' => '@app/themes/classic',//布局
                ],
            ],
            //'renderers'//定义模板引擎，默认twig
        ],
        'assetManager' => [//前端资源管理
            'class' => 'yii\web\AssetManager',
            //'linkAssets' => true,//软链接发布资源，即资源一直保持最新，apache要配置为Options FollowSymLinks
            //'appendTimestamp' => true,//资源请求时，加时间戳参数
            'hashCallback' => function ($path) {
                //"md5", "sha256", "haval160"
                return hash('crc32', $path.Yii::$app->version);//整合进项目版本号[这里是回调，Yii有效]
            },
            //强制更换核心资源的版本，配合hplus主题
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => '@app/assets/hplus/jquery/',
                    'js' => [
                        'js/jquery.min.js'
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'sourcePath' => '@app/assets/hplus/bootstrap/',
                    'css' => [
                        'css/bootstrap.min.css'
                    ]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'sourcePath' => '@app/assets/hplus/bootstrap/',
                    'js' => [
                        'js/bootstrap.min.js'
                    ]
                ],
            ],
        ],
        'log' => [// 不同等级的日志，以不同的方式发给不同的人
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',//发送的Target对象
                    'levels' => ['error', 'warning'],//info可以在开发时做性能优化
                    'logVars' => [],//日志默认打印信息包含$_GET, $_POST,$_FILES, $_COOKIE, $_SESSION, $_SERVER这些信息，设置格式'logVars' => ['_SERVER','_FILES']
                ],
                //[
                    //'class' => 'yii\log\EmailTarget',//发送邮件
                    //'levels' => ['error', 'warning'],
                    //'message' => [
                        //'from' => ['xiayouqiao@sunsult.com'],
                        //'to' => ['980522557@qq.com', '19146400@qq.com'],
                        //'subject' => '您有一个新bug，来自erp.sunsult.com',
                    //],
                //],
                //[
                    //'class' => 'yii\log\DbTarget',//使用数据库记录日志（注意：还可以将文件日志迁移到数据库）
                    //'levels' => ['info', 'error', 'warning']
                //]
            ],
        ],
        // 错误句柄配置
        'errorHandler' => [
            /*
             * 描述：此异常（错误）句柄的配置，整体考虑到了：用户界面（Prod），开发者界面（Dev），测试界面（Test）
             * 这三种身份需要分别处理相关异常，所以我们在此处配置的是三种身份的错误界面。
             * 1.Prod环境，用户级别只执行errorAction，系统异常执行errorView和exceptionView
             * 2.Dev环境，只执行errorView和exceptionView
             * 3.Test环境，直接返回错误字符串
             * 另外，要认识三者的区别，还需要了解yii系统的异常对象的继承关系，整个系统分为两层：一是系统级别，即程序运行时因代码
             * 或者需要的数据达不到要求抛出的异常。二是用户级别，即面向用户操作的异常，Exception->UserException->HttpException+InvalidRouteException->...
             * 基本可以认为是，由用户以各种方式发出的请求传入的数据不合理导致的。
             *
             * 原理：以上这种配置组合能给普通用户一个好的自定义显示页面的同时，又可以很好的隐藏敏感信息！
             * 特别是在ajax请求时可以控制的很好。
             */
            'maxSourceLines' => 19,
            'maxTraceSourceLines' => 13,
            //非debug或者是用户异常时有效，以正常的路由执行来显示错误【用户异常即http请求过程中与用户操作相关的所有http异常】
            'errorAction' => 'common/site/error',
            //debug下有效，暂时使用绝对路径
            'errorView' => '@app/themes/classic/modules/common/views/site/sys_error.php',
            'exceptionView' => '@app/themes/classic/modules/common/views/site/sys_exception.php',
//             'callStackItemView' => '@yii/views/errorHandler/callStackItem.php',//局部模板
//             'previousExceptionView' => '@yii/views/errorHandler/previousException.php',//局部模板
        ],
        /*
         * 注意：cache的配置已经在commmon/config.php中配置过了
        'cache' => [// memcache缓存，也可以使用其它的存储驱动，也可以改写组件id，如cache1同时启用多个不同类型的驱动
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => 'localhost1',
                    'port' => 11211,
                    'weight' => 40,
                ], [
                    'host' => 'localhost2',
                    'port' => 11211,
                    'weight' => 60,
                ],
            ],
        ],
        */
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
        'i18n' => [// 多语言组件配置，每个应用app都有独立的多语言配置，减少相关度
            'translations' => [
                //只配置一个应用
                'backend'=> [// 匹配所有翻译//通用配置，使用*配置所有应用，再以fileMap分隔
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath'=>'@app/messages',//统一存储为一个翻译包
                    //'sourceLanguage' => Yii::$app->sourceLanguage,
                    /*
                    'fileMap'=>[// 简单的映射
                        'common'=>'common.php',
                        'backend'=>'backend.php',//控制中心
                        'console'=>'console.php',//控制台
                        'declare'=>'declare.php',//海关申报
                        'merchant'=>'merchant.php',//所有类型的商户
                        'supply'=>'supply.php',//供应商
                        'yii' => 'yii.php',
                    ],
                    */
                    'on missingTranslation' => ['app\events\TranslationEventHandler', 'handleMissingTranslation'],//事件解决，获取未翻译内容
                ],
                'common'=> [// 匹配所有翻译//通用配置，使用*配置所有应用，再以fileMap分隔
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath'=>'@common/messages',//统一存储为一个翻译包
                    //'sourceLanguage' => Yii::$app->sourceLanguage,
                    'on missingTranslation' => ['app\events\TranslationEventHandler', 'handleMissingTranslation'],//事件解决，获取未翻译内容
                ],
                //以上分开来配置，而不是使用*，可以有效的保证应用分离，功能独立理解清楚
            ]
        ],
        /*
        'catchAll'=>[// 配置维护模式配置（此配置由控制库后台进行管理，写在总控制器中）
             'common/site/offline',//系统维护模式路由
             'title'=>'标题',
             'content'=>'内容说明',
        ],
        */
    ],

    //以as 的方式，行为以过滤器的方式被绑定到了App对象上
    'as lang' => [
        'class' => 'app\filters\LangSeletorFilter',
        'defaultLang' => $params['config_default_language'],
    ],
    'as rbac' => [// 以过滤器AccessControl的方式控制访问节点，过滤器是行为的一种实现
        'class' => 'app\modules\admin\filters\RbacFilter',
        //'except' => [],
        'except' => $params['config_access_action'],
        'denyCallback' => function($action) {
        }
     ],
	 //日志记录
     'as logBehavior' => [
         'class' => 'app\behaviors\LogBehavior',
     ],
    'params' => $params,
];

