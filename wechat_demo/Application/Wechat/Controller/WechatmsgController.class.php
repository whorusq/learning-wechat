<?php

namespace Wechat\Controller;

use Wechat\Controller\WechatController as Wechat;
use Wechat\Controller\WechatmsgtxtController as WechatMsgTxt;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\News;


/**
 * 微信公众号消息处理
 * @author whoru.S.Q
 * @date
 */
class WechatmsgController extends WechatController {

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
     * 来源用户微信账号的基本信息
     *
     * 基本属性：
     *     $fromUser->openid
     *     $fromUser->nickname          昵称
     *     $fromUser->sex               性别
     *     $fromUser->headimgurl        头像
     *     $fromUser->subscribe_time    关注时间
     *     $fromUser->country           国家
     *     $fromUser->province          省份
     *     $fromUser->city              城市
     *
     * @var null
     */
    // protected $fromUser = null;

    protected $user = null;

    /**
     * 当前用户的微信
     * @var null
     */
    protected $openid = null;

    /**
     * 当前类初始化
     * @param object $message
     * @param object $fromUser
     */
    public function __construct($message, $user)
    {
        parent::__construct();
        $this->message = $message;
        $this->user = $user;
        if ($message) {
            // $this->fromUser = $user->get($message->FromUserName);
            $this->openid = $message->FromUserName;
        }
    }

    /**
     * 文字
     *
     * $message->Content  文本消息内容
     *
     * @return
     */
    public function responseText()
    {
        return (new WechatMsgTxt($this->message, $this->user))->handler();
    }

    /**
     * 图片
     *
     * $message->PicUrl   图片链接
     *
     * @return
     */
    public function responseImage() {}

    /**
     * 语音
     *
     * $message->MediaId        语音消息媒体id，可以调用多媒体文件下载接口拉取数据。
     * $message->Format         语音格式，如 amr，speex 等
     * $message->Recognition * 开通语音识别后才有
     * 开通语音识别后，用户每次发送语音给公众号时，微信会在推送的语音消息XML数据包中，增加一个 `Recongnition` 字段
     *
     * @return
     */
    public function responseVoice() {}

    /**
     * 视频
     *
     * $message->MediaId       视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
     * $message->ThumbMediaId  视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
     *
     * @return
     */
    public function responseVideo() {}

    /**
     * 小视频
     *
     * $message->MediaId     视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
     * $message->ThumbMediaId    视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
     *
     * @return
     */
    public function responseShortvideo() {}

    /**
     * 地理位置
     *
     * $message->MediaId     视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
     * $message->ThumbMediaId    视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
     *
     * @return
     */
    public function responselLocation() {}

    /**
     * 连接
     *
     * $message->Title        消息标题
     * $message->Description  消息描述
     * $message->Url          消息链接
     *
     * @return
     */
    public function responseLink() {}

    /**
     * 发送客服消息
     * @return
     */
    public function sendStaffMsg($openid, $message)
    {
        $result = null;
        if ($openid && $message) {
            $result = $this->app->staff->message($message)->to($openid)->send();
        } else {
            $result = ['errcode' => 511, 'errmsg' => 'Invalid openid or message.'];
        }
        sys_log(json_encode($result), 'wechat', 'INFO');
        return $result;
        // echo json_encode($result);
    }

    /**
     * 发送模板消息
     * @param  string $openid     目标用户微信 OpenID
     * @param  string $templateId 消息模板 ID
     * @param  string $url        消息跳转地址
     * @param  array $data       消息模板替换数据
     * @return
     */
    public function sendTplMsg($openid, $msgArr)
    {
        $notice = $this->app->notice;

        $templateId = $msgArr['templateId'];
        $url = $this->domain . $msgArr['url'];
        $data = $msgArr['data'];

        $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($openid)->send();
        sys_log(json_encode($result), 'wechat', 'INFO');
        // echo json_encode($result);
    }

    // 群发消息
    // 订阅号每天1条；服务号每月4条
    public function sendMsgGroup() {}
}