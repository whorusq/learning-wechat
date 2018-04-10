<?php

namespace Wechat\Controller;

use Think\Controller;
use Wechat\Controller\WechatController as Wechat;

/**
 * 用户相关操作，不检查是否登录
 */
class UserController extends Controller {



    /**
     * 默认登录页及逻辑
     * @return
     */
    public function login()
    {
        $this->display();
    }


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

    /**
     * 处理授权失败
     * @return
     */
    public function authLoginFailed()
    {
        $this->display('auth_failed');
    }
}