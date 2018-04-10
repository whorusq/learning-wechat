<?php

namespace Mobile\Controller;

use Mobile\Controller\WechatmsgController;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\News;

/**
 * 文本消息交互处理
 *
 */
class WechatmsgtxtController extends WechatmsgController
{

    /**
     * 文本消息根据关键字分发给不同的处理方法
     * @return
     */
    public function handler()
    {
        $keywordToHandler = [
            'help' => '0',
            'viewBindInfo' => '1',
            'unbindOpenid' => '2',
        ];
        $keyword = trim($this->message->Content);
        foreach ($keywordToHandler as $key => $value) {
            if ($value == $keyword) {

                // 调用当前类中对应关键字的处理方法
                $actionName = '_responseText' . ucfirst($key);
                if (method_exists($this, $actionName)) {
                    return $this->$actionName();
                }
            }
        }

        // 默认返回帮助功能菜单
        return $this->_responseTextHelp();
    }

    /**
     * 自定义帮助菜单（关键字功能列表）
     * @return
     */
    private function _responseTextHelp()
    {
        $helpList = [
            "帮助菜单",
            "--------------------",
            "1. 查看当前绑定",
            "2. 解除绑定",
            "0. 显示当前功能列表",
            // "【帮助】或【0】返回当前功能列表",
            "--------------------",
            "请输入对应的关键字（输入任意非关键字，返回当前帮助菜单）："
        ];
        return implode("\n", $helpList);
    }


    /**
     * 查看当前微信号对应的业务系统后台账号绑定信息
     * @return
     */
    public function _responseTextViewBindInfo()
    {
        $para['openid'] = $this->fromUser->openid;
        $result = sys_gearman('user.getBindUserInfo', $para);
        if ($result['status'] == 200) {
            // sys_log(json_encode($result), 'wechat', '查询');
            $msg = [
                '已绑账号：' . $result['retData']['username'],
                '注册日期：' . date('Y-m-d H:i:s', strtotime($result['retData']['createAt'])),
                '上次登录：' . date('Y-m-d H:i:s', strtotime($result['retData']['lastSignInAt']))
            ];
            return implode("\n", $msg);
        } else {
            return '查询异常：' . $result['retData']['reason'];
        }
    }

    /**
     * 解除与业务系统后台账号的绑定
     * @return
     */
    public function _responseTextUnbindOpenid()
    {
        $para['openid'] = $this->fromUser->openid;
        $result = sys_gearman('user.unBindOpenid', $para);
        if ($result['status'] == 200) {
            return '解绑成功！';
        } else {
            return '解绑失败：' . $result['retData']['reason'];
        }
    }
}