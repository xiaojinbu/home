<?php
// 原理：有些参数是跟随系统本身的，这类配置项不需要在系统后台进行配置，直接配置在此文件中即可、
// 配置在系统后台可管理的所有参数，在配置文件中必须要有一一对应的值，而且数据库配置优先级最高。
return [
    
    // setting后台可配置
    'config_default_page_size' => 10,
    
    
    // 访问控制相关的配置
    'config_access_action' => [
        'admin/user/login', // 登录
        'admin/user/logout', // 登出
    ],
    
    // 多语言相关配置//国际标准码
    'config_languages' => [
        'zh-CN' => '简体中文',
        'en-US' => 'English'
    ],
    // 日志过滤路由
    'config_admin_log' => [
      
    ],
    'config_default_language' => 'zh-CN',
    'config_site_default_hits' => '100',
	
	//超级管理员id
	'config_admin_user_list' => [4],
	
	//管理员列表
    'config_bu_administrators' => [4],

    //缓存时间-短,5分钟
    'cache_time_short' => 300,
    //缓存时间-中，10分钟
    'cache_time_middle' => 600,
    //缓存时间-长，20分钟
    'cache_time_long' => 1200,

];
