<?php

$config['anonymouschat'] = array(
	'wait_time_out' => 300, // 匹配超时 60*5=300 5分钟
	'chat_time_out' => 600, // 聊天超时 60*10=600 10分钟
	'reminding_time' => 480, // 需要提醒聊天超时 60*8=480 8分钟
	'active_time' => array( // 有效时间
		'20:00:00', // 开始时间 晚上8点
		'24:00:00' // 结束时间 晚上12点
	),
	'wechat_token' => 'weixinwexin', // 微信token
	'official_accounts' => array(
		array(
			'appId' => '',
			'appSecret' => '',
			'ghid' => '' // 公众号原始id
		),
	)
);
