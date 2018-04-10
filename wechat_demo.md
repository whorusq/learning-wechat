基于 PHP 的微信公众号开发基本结构示例
---

> 这是一个微信开发的基本结构示例，只包含最基本的sdk及常用功能示例，比如授权登录、生成带参数二维码、菜单管理等。 

提示：**本文仅用于演示，若用于生产环境，请自行修改、完善。**

本示例依赖：

- Composer（PHP包管理工具）
- overtrue/wechat（第三方微信接口sdk）
	 - 包地址：[https://packagist.org/packages/overtrue/wechat](https://packagist.org/packages/overtrue/wechat)
    - 项目地址：[https://github.com/overtrue/wechat](https://github.com/overtrue/wechat)
    - 在线文档：[https://www.easywechat.com/docs/master](https://www.easywechat.com/docs/master)
    - 示例 demo 基于3.x 版本；demo2（待添加） 基于最新 4.x 版本，依赖 **PHP >= 7.0**
- ThinkPHP
	- 示例 [wechat_demo](./wechat_demo) 基于 TP3.2.3
- Gearman（一个分发任务的程序框架）

示例 [wechat_demo](./wechat_demo) 目录结构类似如下：

```
.
├── Application
│   ├── Common
│   │   ├── Common
│   │   │   ├── function.php <----- 公共函数文件
│   │   │   └── index.html
│   │   ├── Conf
│   │   │   ├── config.php <----- 公共配置文件
│   │   │   └── index.html
│   │   └── index.html
│   ├── Wechat
│   │   ├── Conf
│   │   ├── Controller
│   │   │   ├── BaseController.class.php <----- 基类
│   │   │   ├── IndexController.class.php <----- 默认功能模块
│   │   │   ├── UserController.class.php <----- 用户功能模块
│   │   │   ├── WechatController.class.php <----- 微信操作入口类
│   │   │   ├── WechateventController.class.php <----- 微信事件处理
│   │   │   ├── WechatmenuController.class.php <----- 公众号自定义菜单处理
│   │   │   ├── WechatmsgController.class.php <----- 微信消息处理
│   │   │   ├── WechatmsgtxtController.class.php <----- 文本消息响应处理
│   │   │   └── index.html
│   │   └── View
│   │       ├── Index
│   │       ├── User
│   │       └── index.html
│   └── index.html
├── README.md
├── ThinkPHP
├── composer.json <----- Composer 配置
├── composer.lock
├── index.php
└── vendor <----- Composer 安装的包
```

### 1. 相关资源

- 公众平台测试账号申请：[https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421137522](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421137522)
- 公众平台开发文档：[https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1445241432](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1445241432)

### 2. 基于 Composer 安装 overtrue/wechat

- 在根目录的 composer.json 文件中，require 下面追加如下：

```
"require": {
    "php": ">=5.3.0",
    "overtrue/wechat": "~3.1"
},
```

- 打开终端或命令行，同样定位到根目录，执行如下命令安装指定的第三方包：

```bash
➜  composer update
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 14 installs, 0 updates, 0 removals
  - Installing symfony/polyfill-mbstring (dev-master 7c8fae0): Cloning 7c8fae0ac1 from cache
  - Installing symfony/http-foundation (3.4.x-dev eb635ed): Cloning eb635edacc from cache
  - Installing psr/http-message (dev-master f6561bf): Cloning f6561bf28d from cache
  - Installing symfony/psr-http-message-bridge (dev-master b209840): Cloning b2098405d8 from cache
  - Installing guzzlehttp/psr7 (dev-master 811b676): Cloning 811b676fba from cache
  - Installing guzzlehttp/promises (dev-master 09e549f): Cloning 09e549f553 from cache
  - Installing guzzlehttp/guzzle (6.3.0): Loading from cache
  - Installing doctrine/cache (1.6.x-dev eb152c5): Cloning eb152c5100 from cache
  - Installing overtrue/socialite (dev-master db75a57): Cloning db75a57256 from cache
  - Installing psr/log (dev-master 4ebe3a8): Cloning 4ebe3a8bf7 from cache
  - Installing monolog/monolog (1.x-dev fd8c787): Cloning fd8c787753 from cache
  - Installing psr/container (dev-master 2cc4a01): Cloning 2cc4a01788 from cache
  - Installing pimple/pimple (dev-master b5e5c18): Cloning b5e5c1809f from cache
  - Installing overtrue/wechat (3.3.14): Downloading (100%)         
symfony/psr-http-message-bridge suggests installing zendframework/zend-diactoros (To use the Zend Diactoros factory)
monolog/monolog suggests installing aws/aws-sdk-php (Allow sending log messages to AWS services like DynamoDB)
monolog/monolog suggests installing doctrine/couchdb (Allow sending log messages to a CouchDB server)
monolog/monolog suggests installing ext-amqp (Allow sending log messages to an AMQP server (1.0+ required))
monolog/monolog suggests installing ext-mongo (Allow sending log messages to a MongoDB server)
monolog/monolog suggests installing graylog2/gelf-php (Allow sending log messages to a GrayLog2 server)
monolog/monolog suggests installing mongodb/mongodb (Allow sending log messages to a MongoDB server via PHP Driver)
monolog/monolog suggests installing php-amqplib/php-amqplib (Allow sending log messages to an AMQP server using php-amqplib)
monolog/monolog suggests installing php-console/php-console (Allow sending log messages to Google Chrome)
monolog/monolog suggests installing rollbar/rollbar (Allow sending log messages to Rollbar)
monolog/monolog suggests installing ruflin/elastica (Allow sending log messages to an Elastic Search server)
monolog/monolog suggests installing sentry/sentry (Allow sending log messages to a Sentry server)
Writing lock file
Generating autoload files
```


### 3. 配置公众号服务端

#### 3.1. 将本地项目映射到外网

这里用到的是一个内网穿透工具 ngrok，具体使用方式参照另一篇文章[《好用的内网穿透端口映射工具 ngrok》](https://my.oschina.net/antsky/blog/1499799)。

> **提示**：ngrok 免费版并不稳定，经常掉线，而付费套餐又比较贵。遂找到另一个替代方案 natapp，地址：[https://natapp.cn](https://natapp.cn)，每月5元的套餐即可满足测试需要。

#### 3.2. 配置测试账号的服务端

这里以测试账号为例，需要使用微信扫码登录

地址在这里：[https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421137522](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421137522)

进去之后，在页面中配置如下：

![image](./screenshots/Jietu20170913-110344.png)

**这里的 URL 对应公众号服务端入口地址**。

并且记录这里的**测试账号信息**，将**appID**和**appsecret**填入系统配置文件 `/Application/Common/Conf/config.php` 中的 WECHAT 下，详细配置说明参见：[https://easywechat.org/zh-cn/docs/configuration.html](https://easywechat.org/zh-cn/docs/configuration.html)


#### 3.3. 测试公众号功能

使用微信扫码（上面的测试账号里）关注公众号，并回复一些消息看看效果。

想查看其它功能demo，或追加新功能，请参照以 **Wechat** 开头的 PHP 文件，并结合 sdk 官方文档。

### 4. 授权登录

如果想使用授权登录功能，还需要在 2.2 中的测试用户页面中，找到如下位置：

![image](./screenshots/Jietu20170913-132134.png)

单击修改，追加类似如下域名：

![image](./screenshots/Jietu20170913-132310.png)

单击确定保存配置成功后，再开始后面的业务逻辑。

#### 4.1 用户登录检测

在 `Application/Wechat/Controller/BaseController.class.php` 文件中追加如下方法：

```PHP
/**
 * 初始操作
 * @return
 */
public function _initialize()
{

    // 检查是否登录
    $userData = session('user');
    if (empty($userData)) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $isFromWechat = (strpos($userAgent, 'MicroMessenger')) ? 1 : 0;
        if ($isFromWechat) {
            session('request_uri', $_SERVER['REQUEST_URI']); // 记录原始请求地址
            (new User())->authLogin(); // 发起网页授权
            exit();
        } else { // 不是来自微信端的访问，直接跳转默认登录页
            $this->error('您还没有登录！', U('User/login'));
        }
    }

    // 其它公用操作 ...

}
```

**后面所有需要验证登录的业务操作，可以直接继承 `BaseController`**。

#### 4.2. 发起微信端网页授权

在 `Application/Wechat/Controller/UserController.class.php` 文件中追加如下方法：

```PHP
/**
 * 发起微信网页授权
 * @return
 */
public function authLogin()
{
    $oauth = (new Wechat())->app->oauth;
    sys_log('', 'wechat', '发起授权');
    $oauth->redirect()->send();
}
```

#### 4.3. 处理授权回调

在 `Application/Wechat/Controller/UserController.class.php` 文件中追加如下方法：

```PHP
/**
 * 处理授权回调
 *
 * 1. 如果微信号已经绑定业务系统后台账号，则直接自动登录并获取、记录登录信息，跳转原请求地址
 * 2. 如果未绑定，返回错误信息页面，提示登录或其它
 *
 * @return
 */
public function authLoginCallback()
{
    $oauth = (new Wechat())->app->oauth;
    $user = $oauth->user(); // 根据授权结果获取用户信息
    $openid = $user->id;

    // 请求接口，登录业务系统后台
    $ret = sys_gearman('user.weixinLogin', ['openid' => $openid]);
    sys_log('', 'wechat', json_encode($ret, true));
    if ($ret['status'] == 200) { // 已经绑定业务系统后台账号

        // 缓存用户信息
        session('user', $ret['retData']);

        // 跳转原请求地址
        $targetUrl = empty(session('request_uri')) ? '/' : session('request_uri');
        sys_log('', 'wechat', '请求地址：' . $targetUrl);
        $this->redirect($targetUrl);
    } else { // 未绑定业务系统后台账号
        $message = $result['message'] . '' . $ret['retData']['reason'];
        sys_log('', 'wechat', $message);
        $this->redirect(U('User/authLoginFailed'));
    }
}
```

### 5. 测试

在微信或公众号中打个一个页面，比如业务系统首页：`http://fe028d06.ngrok.io/wechat/index/index`

业务处理过程描述：

- 1. 程序首先检查是否登录，若未登录直接发起网页授权，并记录原始请求 url。
- 2. 通过授权回调获取用户微信账号对应的 openid，调用接口检查是否与业务系统账号绑定：
    - 如果绑定，直接自动登录返回用户信息，并跳转原请求地址；
    - 如果未绑定，提示错误，或直接跳转默认登录页。
- 3. 继续其它操作


