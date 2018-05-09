小程序知识点整理
---

### 索引

- 开发者工具
    - [如何快速创建页面结构？]()
    - [如何调试？]()
- js
    - [如何设置全局变量？]() 
    - [如何做页面数据绑定？]()
    - [如何实现页面跳转？]()
- json
- wxss
    - [支持哪些 CSS 选择器？]()
    - [关于 rpx 如何换算？]()
- wxml
- 其它


---

### Q: 如何快速创建页面结构？

使用开发者工具，打开项目根目录下的 `app.json` 文件，pages 里追加一条页面配置，保存后，在对应 `/pages` 目录下就会生成相应的页面结构：`page.js、page.json、page.wxml、page.wxss`。

### Q: 如何调试？

在开发者工具中：

1. 单击【详情】并勾选 `不校验合法域名、web-view（业务域名）、TLS 版本以及 HTTPS 证书`；
2. 单击【远程调试】按钮；
3. 使用手机微信扫描二维码打开小程序。

**注意**：【预览】按钮必须使用带有 https 的服务端，上面步骤 1 的选项是否勾选对其不会产生影响。

### Q: 如何设置全局变量？
    
1. 在 app.js 中定义一个属性，如：

    ```
    App({
        globalData: {
            apiURI: 'http://192.168.2.14:8080/test.php'
        }
    })
    ```

2. 在具体页面对应带 {page}.js 中，通过 `const app = getApp()` 获取 App 实例，然后通过 `app.globalData.apiURI` 获取、更新设置的全局变量。


### Q: 如何做页面数据绑定？

1. 在对应页面的 {page}.js 文件中，如下设置：

```
Page({

    /**
     * 页面的初始数据
     */
    data: {
        user: '',
        obj1: {
          a: 1,
          b: 2
        }
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.setData({ user: '小明' }); // 更新页面绑定的数据
    }
})
```

2. 在对应页面的 {page}.wxml 文件中，如下引用：

```
<view>
    <text>姓名：{{user}}</text>
</view>
```

**注意**：

- 变量需要用双大括号包起来，如 `{{var}}`；
- 如果变量用于组件属性控制，这需要放在属性对应的双括号中，如 `<view id="item-{{id}}"> </view>`；
- 如果变量是一个对象，可以使用符合 `...` 将其展开，如：

    ```
    <template is="objectCombine" data="{{...obj1}}"></template>
    ```
    
    到了 template 对应的文件中，就可以直接使用 obj1 中的属性名，如：
    
    ```
    <!-- objectCombine 模版文件 -->
    <template name="objectCombine">
        <text>a: {{a}}</text>
        <text>b: {{b}}</text>
    </template>
    ```

- 双大括号里面可以使用

### Q: 如何实现页面跳转？

- **方式一：API**

    <table>
        <tr>
            <th>API</th>
            <th>目标页面类型</th>
            <th>说明</th>
            <th>其它</th>
        </tr>
        <tr>
            <td>wx.navigateTo</td>
            <td>非 tabBar 页面</td>
            <td>保留当前页面，跳转到应用内的某个页面</td>
            <td>使用 <code>wx.navigateBack</code> 可以返回到原页面</td>
        </tr>
        <tr>
            <td>wx.redirectTo</td>
            <td>非 tabBar 页面</td>
            <td>关闭当前页面，跳转到应用内的某个页面</td>
            <td></td>
        </tr>
        <tr>
            <td>wx.reLaunch</td>
            <td>所有</td>
            <td>关闭所有页面，打开到应用内的某个页面</td>
            <td>如果跳转的页面路径是 tabBar 页面则不能带参数；基础库版本 1.1.0+</td>
        </tr>
        <tr>
            <td>wx.switchTab</td>
            <td>tabBar 页面</td>
            <td>关闭其他所有非 tabBar 页面</td>
            <td>在 app.json 的 tabBar 字段定义的页面，路径后不能带参数</td>
        </tr>
    </table>

    注：
    
    > 参数与路径之间使用 ? 分隔，参数键与参数值用 = 相连，不同参数用 & 分隔，例如 'path?key=value&key2=value2'

- **方式二：navigator 组件**

    示例：
    
    ```
    <view class="btn-area">
      <navigator url="/page/navigate/navigate?title=navigate" hover-class="navigator-hover">跳转到新页面</navigator>
      <navigator url="../../redirect/redirect/redirect?title=redirect" open-type="redirect" hover-class="other-navigator-hover">在当前页打开</navigator>
      <navigator url="/page/index/index" open-type="switchTab" hover-class="other-navigator-hover">切换 Tab</navigator>
    </view>
    ```
 
    其中 open-type 有效值：
    
    值 | 说明
    ---|---
    navigate | 对应 wx.navigateTo 的功能
    redirect | 对应 wx.redirectTo 的功能
    switchTab | 对应 wx.switchTab 的功能
    reLaunch | 对应 wx.reLaunch 的功能，基础库版本 1.1.0+
    navigateBack | 对应 wx.navigateBack 的功能，基础库版本 1.1.0+

   注：
   
   > navigator-hover 默认为 `{background-color: rgba(0, 0, 0, 0.1); opacity: 0.7;}`, <navigator/> 的子节点背景色应为透明色
    


### Q: 关于 rpx 如何换算？

rpx（responsive pixel）是新增的尺寸单位， 可以根据屏幕宽度进行自适应，它是以 375 个物理像素为基准，也就是在一个宽度为 375 物理像素的屏幕下，1rpx = 1px。 

**推荐以 iPhone6 屏幕（宽度为 375px，共 750 个物理像素）为设计基准，此时 1rpx = 375 / 750px = 0.5px**


### Q: 支持哪些 CSS 选择器？

<table>
    <tr>
        <th>类型</th>
        <th>选择器</th>
        <th>示例</th>
        <th>说明</th>
    </tr>
    <tr>
        <td>类选择器</td>
        <td>.class</td>
        <td>.intro</td>
        <td>选择所有拥有 class="intro" 的组件</td>
    </tr>
    <tr>
        <td>id选择器</td>
        <td>#id</td>
        <td>#firstname</td>
        <td>选择拥有 id="firstname" 的组件</td>
    </tr>
    <tr>
        <td>元素选择器</td>
        <td>element</td>
        <td>view checkbox</td>
        <td>选择所有文档的 view 组件和所有的 checkbox 组件</td>
    </tr>
    <tr>
        <td>伪元素选择器</td>
        <td>::after</td>
        <td>view::after</td>
        <td>在 view 组件后边插入内容</td>
    </tr>
    <tr>
        <td>伪元素选择器</td>
        <td>::before</td>
        <td>view::before</td>
        <td>在 view 组件前边插入内容</td>
    </tr>
</table>