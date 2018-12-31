## 简介
- 这是一个能让用户在公众号界面匿名聊天的简单的公众号应用
- 能发送文本，语音和图片
- 公众号必须是认证过的公众号
- 使用了公众号的客服消息接口
- 使用了 CodeIgniter 框架，3.1.5 版本

## 如何使用
1. 准备公众号的 appid, secret 和 公众号的原始id
2. 修改 application/config 文件夹下的配置文件 anonymouschat.php
3. 修改 application/config 文件夹下的数据库配置 database.php
4. 导入数据库
5. 在微信公众号的后台添加一个关键词回复，作为进入聊天的标记
```
    localhost/index.php/Anonymouschat/joinchat
```
6. 在微信公众号的后台设置文本全转发，语音全转发，图片全转发的链接
```
    localhost/index.php/Anonymouschat/chat
```
7. 在 cli 下运行 Anonymouschat.php 的 watch 方法
```
    php index.php Anonymouschat watch
```

## 目录结构
- 这里只列出了相对于框架本身，添加的文件或修改过的文件
```
├─application                   项目文件目录
│  ├─config                     配置文件目录
│    ├─anonymouschat.php        匿名聊天配置文件
│    ├─database.php             数据库配置文件
│    ...
│  ├─controllers                控制器文件目录
│    ├─Anonymouschat.php        匿名聊天控制器
│  ├─models                     模型文件目录
│    ├─Anonymouschat_model.php  匿名聊天模型
│    ├─Wechat_cache_model.php   微信缓存模型
├─localhost.sql                 数据库脚本
├─watch.bat                     运行 watch 方法的脚本
...
```

## 编写的大致思路
1. 当用户回复一个特定的关键词的时候，就在数据库里插入一条记录，标记用户进入聊天状态
2. 然后让用户回复数字 1 或 2 选择自身的性别和聊天对象的性别
3. 当用户选择完聊天对象的性别时，就修改数据库里的状态为代匹配
4. 然后再 cli 下运行的 watch 方法会每 15 秒运行一次，为用户匹配聊天对象
5. 当匹配成功之后就会发送一条客服消息，提醒用户匹配成功，用户之后给公众号的回复，都会以客服消息的形式发送给聊天对象

## 注意
- 微信公众号的客服消息上限时每天 50w 条
- 用户 20 分钟内没有和公众号互动过，客服消息会发送失败，所以聊天时间最好设置在 20 分钟内
