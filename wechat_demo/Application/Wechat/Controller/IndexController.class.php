<?php

namespace Wechat\Controller;

use Wechat\Controller\BaseController;

/**
 * 模块默认首页
 */
class IndexController extends BaseController {

    /**
     * 当前模块默认首页
     * @return
     */
    public function index()
    {
        $userData = session('user');
        $this->assign('user', $userData);
        $this->display();
    }




}