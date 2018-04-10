<?php

namespace Wechat\Controller;

use Think\Controller;
use Wechat\Controller\UserController as User;

/**
 * 模块基类，追加对是否登录的检查等一些前置操作
 */
class BaseController extends Controller {


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





}