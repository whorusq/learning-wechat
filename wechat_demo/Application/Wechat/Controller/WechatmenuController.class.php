<?php

namespace Mobile\Controller;

use Mobile\Controller\WechatController as Wechat;

/**
 * 菜单管理
 * @author sunqiang
 * @date
 */
class WechatmenuController extends WechatController {

    /**
     * 菜单操作对象
     * @var null
     */
    private $_menu = null;

    /**
     * 公众号菜单示例，创建规则：一级 1 ~ 3 个；二级 1 ~ 5 个
     * @var
     */
    private $_menus = [
        [
            'type' => 'view',
            'name' => '链接类型',
            'url'  => 'http://baidu.com'
        ],
        [
            'name'       => '菜单类型',
            'sub_button' => [
                // [
                //     'type' => 'view_limited',
                //     'name' => '图文消息',
                //     'key'  => 'MEDIA_ID2'
                // ],
                [
                    'type' => 'scancode_push',
                    'name' => '扫码推事件',
                    'key'  => 'rselfmenu_0_1'
                ],
                [
                    'type' => 'pic_sysphoto',
                    'name' => '系统拍照发图',
                    'key'  => 'rselfmenu_1_0'
                ],
                [
                    'type' => 'pic_photo_or_album',
                    'name' => '拍照或者相册发图',
                    'key'  => 'rselfmenu_1_1'
                ],
                [
                    'type' => 'location_select',
                    'name' => '发送位置',
                    'key'  => 'rselfmenu_2_0'
                ]
            ]
        ],
        [
            'type' => 'click',
            'name' => '更多...',
            'key'  => 'btn_more'
        ]
    ];

    /**
     * 小程序菜单示例
     * @var
     */
    private $_miniProgramMenus = [
        [
            'type'      => 'miniprogram',
            'name'      => '一键呼叫',
            'appid'     =>  'wxxxxxxxxxxxxxx',
            'pagepath'  =>  'pages/call/phone'
            'url'       =>  'http://xxx.xxx/xxx',
        ]
    ];

    /**
     * 个性化菜单示例
     * @var
     */
    private $_conditionalMenu =[
        'tag_id'                => '2',
        'sex'                   => '1',
        'country'               => '中国',
        'province'              => '广东',
        'city'                  => '广州',
        'client_platform_type'  => '2',
        'language'              => 'zh_CN'
    ];

    /**
     * 当前类初始化
     * @param object $message
     * @param object $fromUser
     */
    public function __construct()
    {
        parent::__construct();
        $this->_menu = $this->app->menu;
    }

    /**
     * 查询所有菜单：menu 默认菜单类型；conditionalmenu 个性菜单类型
     * @return
     */
    public function menuList()
    {
        return $this->_menu->all();
        // $this->ajaxReturn();
    }

    /**
     * 添加菜单
     * @param  array $menus 菜单配置
     * @param  array $conditionalMenu 个性化菜单设置
     * @return
     */
    public function addMenu($menus = null, $conditionalMenu = [])
    {

        // 先销毁原有菜单
        $this->_menu->destroy();

        // 创建菜单
        $menuOptions = $menus ?: $this->_menus;
        if (empty($isConditionalmenu)) {
            $result = $this->_menu->add($menuOptions);
        } else {
            $result = $this->_menu->add($menuOptions, $conditionalMenu);
        }
        return ($result->errcode == 0) ? true : false;
    }

    /**
     * 删除菜单
     * @param  integer  $menuId    菜单ID
     * @param  boolean $isDestroy 是否删除全部
     * @return
     */
    public function removeMenu($menuId = null, $isDestroy = false)
    {
        if (!$menuId) {
            return $this_menu->destroy($menuId);
        } else {
            if ($isDestroy) {
                return $this->_menu->destroy();
            }
        }
    }

    /**
     * 测试个性化菜单
     * @param  integer $userId OpenID或用户微信账号
     * @return
     */
    public function testConditionalMenu($userId)
    {
        $menus = $this->_menu->test($userId);
        return $menus; // 返回与 $userId 匹配的菜单项
    }
}