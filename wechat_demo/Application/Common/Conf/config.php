<?php

// 微信公众平台
C('WECHAT_DOMAIN', 'http://fe028d06.ngrok.io'); // 公众号服务端对应的外网域名（测试用）
C('WECHAT', [
    'debug'     => true, // 当值为 false 时，所有的日志都不会记录
    'app_id'    => 'xxxxxxxx', // AppID
    'secret'    => 'xxxxxxxxxxxxx', // AppSecret
    'token'     => 'xxxxxxxxx',
    // 'aes_key'   => '', // EncodingAESKey，安全模式下必填
    'log'       => [
        'level' => 'error', // debug/info/notice/warning/error/critical/alert/emergency
        'file'  => RUNTIME_PATH . 'Logs/Wechat/wechat.log', // 绝对路径
    ],
    'oauth'     => [
        'scopes'   => ['snsapi_userinfo'],
        'callback' => '/Wechat/User/authLoginCallback', // 授权回调地址
    ],
    'guzzle' => [
        // 'timeout' => 3.0, // 超时时间（秒）
        'verify' => false, // 暂时关掉 SSL 认证（部分机器报错 http://t.cn/R9WhuLv）
    ],
]);
C('WECHAT_MSG_ONSUBSCRIBE', ''); // 首次关注时的欢迎信息，使用 \n 换行

    // 其它配置...

return [

	//'配置项'=>'配置值'

    'VERSION'           => '170829003', // 版本
    'TMPL_PARSE_STRING' => [
        '__WECHAT__'     => __ROOT__.'/Public/Wechat', // 微信模块资源文件目录
    ],

    // 模块
    'MODULE_ALLOW_LIST' => ['Wechat'], // 模块列表
    'DEFAULT_MODULE'    => 'Wechat', // 默认模块

	// URL
	'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL'            => 2, // URL模式
	'VAR_URL_PARAMS'       => '_URL_', // PATHINFO URL参数变量
	'URL_PATHINFO_DEPR'    => '/', // PATHINFO URL分割符
    'URL_HTML_SUFFIX'      => '',

	// Redis Session配置
	'SESSION_TYPE'          =>  'Redis',    //session类型
	'SESSION_REDIS_HOST'    =>  REDIS_SERVER_HOST, //分布式Redis,默认第一个为主服务器
	'SESSION_REDIS_PORT'    =>  REDIS_SERVER_PORT,           //端口,如果相同只填一个,用英文逗号分隔
	'SESSION_EXPIRE'		=>  20000,		//session有效期(单位:秒) 0表示永久缓存

    // Geaman Server
    'GEAMAN_SERVER_HOST' 	=>  GEAMAN_SERVER_HOST,
	'GEAMAN_SERVER_PORT' 	=>  GEAMAN_SERVER_PORT,

];