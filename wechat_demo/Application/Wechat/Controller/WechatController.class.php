<?php

namespace Wechat\Controller;

use Think\Controller;

require APP_PATH . '../vendor/autoload.php';
use EasyWeChat\Foundation\Application;

use Wechat\Controller\WechateventController as Wechatevent;
use Wechat\Controller\WechatmsgController as Wechatmsg;

/**
 * 微信公众号服务入口
 *
 * 基于非官方微信 SDK 扩展 EasyWeCaht 开发
 *     - 源码：https://github.com/overtrue/wechat
 *     - 文档：https://easywechat.org/zh-cn/docs/
 * 本地开发内网穿透映射到外网工具 Ngrok （免费版不是很稳定，收费版较贵）
 *     - 官网：https://ngrok.com
 *     - 使用：https://my.oschina.net/antsky/blog/1499799
 * 本地开发内网穿透映射到外网工具 NATAPP （收费便宜，购买套餐并绑定二级域名，稳定高效）
 *     - 官网：https://natapp.cn
 *     - 教程：https://natapp.cn/article/natapp_newbie
 *     - 套餐：5元、10元、15元每月等，九折优惠 3302C8FE
 * 公众号
 *     - 测试账号申请：https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421137522
 *     - 开发文档：https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1445241432
 *
 * @author whoru.S.Q
 * @date 2017-08-02 13:34:22
 */
class WechatController extends Controller {

    /**
     * 全局服务实例
     * @var null
     */
    public $app = null;


    /**
     * 请求地址前缀（协议+域名+端口号）
     * @var null
     */
    public $domain = null;


    /**
     * 公众号服务初始化
     * @return
     */
    protected function _initialize()
    {
        $this->domain = $this->_getDomain(C('WECHAT_DOMAIN'));
        $this->app = new Application(C('WECHAT'));
    }

    /**
     * 公众号服务请求入口
     * @return
     */
    public function index()
    {

        $server = $this->app->server;
        $user = $this->app->user;

        // 重复请求检查
        $prevCreateTime = cookie('prevCreateTime');
        if ($prevCreateTime != null) {
            if ($prevCreateTime != $msgArr['CreateTime']) {
                $msgArr = $server->getMessage();
                cookie('prevCreateTime', $msgArr['CreateTime'], 100);
            } else {
                return ''; // 不处理重复请求（短时间内多次请求）
            }
        }

        // 接收、处理各类消息
        $server->setMessageHandler(function($message) use ($user) {

            // 这里 $user->get() 将调用获取用户基本信息接口 https://api.weixin.qq.com/cgi-bin/user/info
            // 公众号需通过认证，否则提示：api unauthorized hint（接口没有授权 ）
            // $fromUser = $user->get($message->FromUserName);
            $responseMsg = '';

            // 处理事件消息
            if ($message->MsgType == 'event') {
                $actionName = 'on' . ucfirst(strtolower($message->Event));
                $wechatEvent = new Wechatevent($message, $fromUser);
                if(method_exists($wechatEvent, $actionName)) {
                    $responseMsg = $wechatEvent->$actionName();
                }
            }

            // 处理其它消息
            $msgType = ['text', 'image', 'voice', 'video', 'location', 'link'];
            if (in_array($message->MsgType, $msgType)) {
                $actionName = 'response' . ucfirst(strtolower($message->MsgType));
                $wechatMsg = new Wechatmsg($message, $fromUser);
                if(method_exists($wechatMsg, $actionName)) {
                    $responseMsg = $wechatMsg->$actionName();
                }
            }

            // .....

            return $responseMsg ?: '功能开发中，敬请期待...';
        });

        $server->serve()->send();
    }

    /**
     * 发起网页授权示例
     *
     * 注意：首先需要配置【网页授权域名】，否则报错：redirect_url 参数错误
     * 参见：https://easywechat.org/zh-cn/docs/troubleshooting.html#redirect-url-参数错误
     *
     * @return
     */
    public function auth()
    {
        $oauth = $this->app->oauth;
        // $_SESSION['target_url'] = $this->domain . session('request_uri');
        sys_log('', 'wechat', '开始执行授权');
        $oauth->redirect()->send();
    }

    /**
     * 授权回调示例
     * @return
     */
    public function auth_callback()
    {
        $oauth = $this->app->oauth;
        $user = $oauth->user();
        // $param['openid'] = $user->id;
        $targetUrl = empty(session('request_uri')) ? '/' : session('request_uri');
        sys_log('', 'wechat', '请求地址：' . $targetUrl);
        $this->redirect($targetUrl);
    }

    /**
     * 生成关注二维码（带场景参数，用于账户绑定）
     * @param integer $sceneId 二维码携带的场景参数
     * @return
     */
    public function generateQrcode($sceneId = 1234567, $isForever = 0)
    {
        $qrcode = $this->app->qrcode;
        if (!$isForever) { // 临时二维码
            $result = $qrcode->temporary($sceneId, 29 * 24 * 3600); // 这里设置有效期 29 天（最大 30）
        } else { // 永久二维码

        }
        return $qrcode->url($result->ticket);
        // 写入文件
        // $content = file_get_contents($qrcode->url($result->ticket));
        // file_put_contents(APP_PATH . '../code.jpg', $content); // 写入文件
    }

    /**
     * 根据 OpenID 获取关注用户的基本信息：昵称、头像等
     * @param  string $openid
     * @return object|array
     */
    public function getUserInfo($openid)
    {
        $user = null;
        try {
            $user = $this->app->user->get($openid);
        } catch(\Exception $e) {
            $user = $e->getMessage();
        }
        return $user;
    }

    /**
     * 长链接转短链接
     * @param  string $url 要缩短的链接
     * @return
     */
    public function shortenUrl($url)
    {
        if ($url && is_string($url)) {
            return $this->app->$url->shorten($url);
        }
    }


    /**
     * 获取当前服务所属域名，用于需要绝对地址的链接
     * @param  string $host 返回一个测试域名
     * @return
     */
    private function _getDomain()
    {
        if (!$host) {
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        } else {
            return $host;
        }
    }
}