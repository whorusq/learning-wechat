<?php

namespace Wechat\Controller;

use Wechat\Controller\WechatController as Wechat;
use Wechat\Controller\WechatmsgController as Wechatmsg;
use EasyWeChat\Message\News;

/**
 * 微信公众号事件处理
 * @author whoru.S.Q
 * @date
 */
class WechateventController extends WechatController {

    /**
     * 请求消息体
     *
     * 基本属性：
     *     $message->ToUserName    接收方帐号（该公众号 ID）
     *     $message->FromUserName  发送方帐号（OpenID, 代表用户的唯一标识）
     *     $message->CreateTime    消息创建时间（时间戳）
     *     $message->MsgId         消息 ID（64位整型）
     *
     * @var null
     */
    protected $message = null;

    /**
     * 来源用户
     * @var null
     */
    protected $user = null;


    public function __construct($message, $user)
    {
        parent::__construct();
        $this->message = $message;
$this->user = $user;
    }

    /**
     * 关注公众号
     * @param  object $message  消息体
     * @param  object $fromUser 来源用户
     * @return
     */
    protected function onSubscribe()
    {
        $msg = C('WECHAT_MSG_ONSUBSCRIBE');
        $openid = $this->message->FromUserName;
        $fromUser = $this->user->get($openid);
        $responseMsg = $msg ? $msg : $fromUser->nickname . " 您好！";
        $handleMsg = $this->_bindOpenid();
        return $responseMsg;
    }

    /**
     * 取消关注公众号
     * @return
     */
    protected function onUnsubscribe() {}

    /**
     * 扫描带参数的二维码
     * $message->EventKey 创建二维码时的二维码scene_id
     * @return
     */
    protected function onScan()
    {
        $handleMsg = $this->_bindOpenid();
        if ($handleMsg != '') {
            return $handleMsg;
        }
    }

    protected function onScancode_push() {
        // sys_log(json_encode($this->message), 'wechat', '事件onScancode_push-->');
        return '';
    }

    /**
     * 点击菜单拉取消息
     * $message->EventKey 自定义菜单接口中对应的KEY值
     * @return
     */
    protected function onClick()
    {
        if ($this->message->EventKey == 'btn_more') {
            return (new Wechatmsg())->choice(); // 显示功能列表
        }
    }

    /**
     * 点击菜单跳转链接
     * $message->EventKey 设置的跳转URL
     * @return
     */
    protected function onView() {}

    /**
     * 执行业务系统后台用户与微信账户的绑定
     * @return string 绑定结果
     */
    private function _bindOpenid()
    {
        $fromUser = $this->user->get($openid);
        $para['userid'] = str_replace('qrscene_','', $this->message->EventKey);
        $para['openid'] = $this->message->FromUserName;
        if ($para['userid'] && $para['openid']) {

            // 保存云平台账号与公众号对照关系
            $result = sys_gearman('user.update_weixinid', $para);
            if ($result['status'] == 200) {
                sys_log(json_encode($para), 'wechat', 'INFO:绑定成功');
                return '与业务系统后台账号 ' . $result['retData']['username'] . ' 绑定成功，请输入 0 查看更多功能';
            } else {
                sys_log(json_encode($para) . '|' . json_encode($result), 'wechat', 'ERR:绑定失败');
                $resultReason = $result['retData']['reason'];
                if (strpos('xx' . $resultReason, '已绑定') > 0) {
                    return '与业务系统后台账号绑定失败，该微信号已经绑定 ' . $result['retData']['username'];
                } else {
                    return '与业务系统后台账号绑定失败：' . $resultReason;
                }
            }
        }
    }
}