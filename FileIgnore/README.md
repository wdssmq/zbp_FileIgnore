## 投喂支持

爱发电：[https://afdian.net/a/wdssmq](https://afdian.net/a/wdssmq "沉冰浮水正在创作和 z-blog 相关或无关的各种有用或没用的代码 | 爱发电")

哔哩哔哩：[https://space.bilibili.com/44744006](https://space.bilibili.com/44744006 "沉冰浮水的个人空间\_哔哩哔哩\_bilibili")「投币或充电」「[大会员卡券领取 - bilibili](https://account.bilibili.com/account/big/myPackage "大会员卡券领取 - bilibili")」

RSS 订阅：[https://feed.wdssmq.com](https://feed.wdssmq.com "沉冰浮水博客的 RSS 订阅地址") 「[「言说」RSS 是一种态度！！](https://www.wdssmq.com/post/20201231613.html "「言说」RSS 是一种态度！！")」

在更多平台关注我：[https://www.wdssmq.com/guestbook.html#其他出没站点和信息](https://www.wdssmq.com/guestbook.html#%E5%85%B6%E4%BB%96%E5%87%BA%E6%B2%A1%E5%9C%B0%E7%82%B9%E5%92%8C%E4%BF%A1%E6%81%AF "在更多平台关注我")

更多「小代码」：[https://cn.bing.com/search?q=小代码+沉冰浮水](https://cn.bing.com/search?q=%E5%B0%8F%E4%BB%A3%E7%A0%81+%E6%B2%89%E5%86%B0%E6%B5%AE%E6%B0%B4 "小代码 沉冰浮水 - 必应搜索")

<!-- ##################################### -->


## 试用下载

试用下载：[http://demo.wdssmq.com/WebDir/](<http://demo.wdssmq.com/WebDir/> "http://demo.wdssmq.com/WebDir/")

请联系我上传本插件的最新版到试用页，你不说我怎么知道你想要呢/doge；


#### 插件功能：

「水坑」Z-BlogPHP 应用开发中那些可以自动完成的操作\_电脑网络\_沉冰浮水

[https://www.wdssmq.com/post/20200105022.html](<https://www.wdssmq.com/post/20200105022.html> "「水坑」Z-BlogPHP 应用开发中那些可以自动完成的操作_电脑网络_沉冰浮水")


#### 相关推荐

HTML 标记验证助手（W3C） - Z-Blog 应用中心

[https://app.zblogcn.com/?id=23191](<https://app.zblogcn.com/?id=23191> "HTML 标记验证助手（W3C） - Z-Blog 应用中心")

「开发工具」BrowserSync - Z-Blog 应用中心

[https://app.zblogcn.com/?id=27743](<https://app.zblogcn.com/?id=27743> "「开发工具」BrowserSync - Z-Blog 应用中心")


#### 打包排除说明

本插件最初目的是实现在打包应用为 zba 文件时排除指定文件夹或文件，直接使用 .gitignore：

```plaintext
build
config
db
cache/\*

```

前三行会排除掉相应的文件夹及其中的文件，第四行会排除 cache 目录下的文件，然后保留一个空的 cache 文件夹。。【大概是这么用的吧。。

优先级：zbignore.txt > 应用目录下的 .gitignore > FileIgnore 自身目录下的 .gitignore

#### 修锅记录：

2024-06-04：按分组切换插件；注：需要手动复制 /usr_cfg/plugin-group.json 到 /usr/plugin-group.json 并自行修改；

2022-11-03：增加快速切换编辑器；「[视频演示 - 哔哩哔哩](<https://www.bilibili.com/video/BV1eD4y147f3> "视频演示 - 哔哩哔哩")」

2021-09-17：将插件说明发布为一篇文章；

2021-04-01：CSS 和 JS 改为时间+版本号；

2020-12-25：路径拼接修改时忘记分隔符了；

2020-11-30：自动重建系统模块内容；

2020-10-10：启用应用时更新应用文件夹的修改时间属性；

2020-06-17：日期相同时自动变改版本号；

2020-04-04：判断是否存在zbignore.txt并跳过本插件的排除接口；

2020-01-12：启用插件时自动启用依赖插件（如果存在）

2019-06-16：打包时自动更新日期；对于{$app->include}文件内格式为**xxx.css?v=2019-06-16**或**xxx.js?v=2019-06-16**的部分。。自动更新其中的日期部分为最新；

2017-07-05：排除目录的子目录需要进一步检索；

2017-06-18：好麻烦，好麻烦。。

2017-05-09：数组元素移除方式错误！

