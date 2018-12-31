## 简介
- 这是一个能让用户在公众号界面匿名聊天的简单的公众号应用
- 能发送文本，语音和图片
- 公众号必须是认证过的公众号
- 使用了公众号的客服消息接口
- 使用了 CodeIgniter 框架

## 如何使用
1. 准备公众号的 appid, secret 和 公众号的原始id
2. 修改 application/config 文件夹下的配置文件 anonymouschat.php
3. 修改 application/config 文件夹下的数据库配置 database.php
4. 导入数据库
5. 在微信公众号的后台添加一个关键词回复，作为进入聊天的标记
6. 在微信公众号的后台设置文本全转发，语音全转发，图片全转发的链接
7. 在 cli 下运行 Anonymouschat.php 的 watch 方法

## 目录结构
- 这里只列出了相对于框架本身，添加的文件或修改过的文件
```
├─application                   项目文件
│  ├─config                     配置文件目录
│    ├─anonymouschat.php        匿名聊天配置文件
│    ├─database.php             数据库配置文件
│    ...
│  ├─controllers                控制器文件夹
│    ├─Anonymouschat.php        匿名聊天控制器
│  ├─models                     模型文件夹
│    ├─Anonymouschat_model.php  匿名聊天模型
│    ├─Wechat_cache_model.php   微信缓存模型
├─localhost.sql    数据库脚本
...
```