<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Anonymouschat extends CI_Controller
{
	private $appId;
	private $appSecret;
	private $token;
	private $anonymouschat_config;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Shanghai');
		$anoymouschat_config = $this->config->load('anonymouschat');
		$this->load->model('wechat_cache_model', 'wechat_cache');
		$this->load->model('anonymouschat_model', 'anonymouschat');
		$this->anonymouschat_config = $this->config->item('anonymouschat');
		$this->token = $this->anonymouschat_config['wechat_token'];
	}

	public function joinchat()
	{
		$this->checkToken();

		// 接收来自微信的消息
		$postObj = $this->wechat_massage();

		// 获取openid
		$openid = $postObj->FromUserName;

		// 获取公众号id
		$ghid = $postObj->ToUserName;

		// 判断是否已进入聊天模式
		$ret = $this->anonymouschat->is_joinchat($openid);
		if ($ret === -1)
		{
			$content = "进入聊天模式失败";
			$this->logger($content, $ret);
		}
		else if ($ret === 1)
		{
			$content = "你已进入匿名聊天，请勿重复发送关键词";
		}
		else if ($ret === 0)
		{

			// 判断是否在有效时间内
			if (isset($this->anonymouschat_config['active_time']))
			{
				$active_time = $this->anonymouschat_config['active_time'];
				$start_time_str = $active_time[0];
				$end_time_str = $active_time[1];
				$start_time = strtotime(date('Y-m-d').' '.$start_time_str);
				$end_time = strtotime(date('Y-m-d').' '.$end_time_str);
				$now_time = time();
				if ($now_time < $start_time || $now_time > $end_time)
				{
					$err = "同学你好，现在重磅推出匿名CP配对交友活动。\n
					为增加体验乐趣以及匹配成功率，活动仅每晚".date('g', $start_time)."-".date('g', $end_time)."点开放CP聊天，\n
					同学们不要错过时间。脱单黑科技，告别单身狗，欢迎奔走相告，拉同学一起来玩。";
					// $err = "匿名聊天暂未开启，开启时间为\n".$start_time_str." — ".date('H:i:s', $end_time);
					// $err .= "\n".$now_time."\n".$start_time."\n".$end_time;
					$this->echo_error($postObj, $err);
				}
			}

			// 把用户加入到聊天模式
			$ret = $this->anonymouschat->joinchat($openid, $ghid);
			if (!$ret)
			{
				$content = "把用户加入到聊天模式";
				$this->logger($content, $ret);
			}
			else
			{
				$content = "请选择你的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
			}
		}

		// 构造回复的字符串，xml格式
		$resultStr = $this->transmitText($postObj, $content);
		// 输出回复
		echo $resultStr;
	}

	public function chat()
	{
		$this->checkToken();

		// 接收来自微信的消息
		$postObj = $this->wechat_massage();

		// 获取openid
		$openid = $postObj->FromUserName;

		// 获取公众号id
		$ghid = $postObj->ToUserName;

		// 获取用户信息
		$user_info = $this->anonymouschat->get_openid_info($openid);
		if ($user_info === -1)
		{
			$this->logger("获取用户信息失败", $user_info);
			$this->echo_error($postObj);
		}

		// 判断用户状态
		if ($user_info === 0 || !isset($user_info[0]))
		{
			// $this->echo_error($postObj, "该用户未进入匿名聊天模式");
			exit;
		}

		$user_info = $user_info[0];
		$state = $user_info['state'];
		$id = $user_info['id'];
		$tag_id = $user_info['tag_id'];
		$keyword = (string)$postObj->Content;

		// 退出聊天
		if ($keyword === "exit")
		{
			$this->anonymouschat->exit_chat($user_info);
			// echo $this->db->last_query();
			$ret = $this->anonymouschat->get_openid($user_info['tag_id']);
			if (!($ret === -1 || $ret === 0))
			{
				$tag_openid = $ret;
				$keyword = "对方已退出聊天";
				$this->send_custom_message_text($tag_openid, $keyword);
			}

			$this->echo_error($postObj, "已退出聊天");
		}

		switch ($state)
		{
			case 0: // 新用户
				if ($keyword === "1" || $keyword === "2")
				{
					$sex = $keyword;
					$ret = $this->anonymouschat->set_sex($id, $sex);
					if (!$ret)
					{
						$err_str = "设置性别失败";
						$this->logger($err_str, $ret);
						$this->echo_error($postObj, $err_str);
					}
					$content = "请选择聊天对象的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
				}
				else
				{
					$content = "请选择你的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出".$keyword."\n".$state;
				}
				break;
			case 1: // 已选择性别
				if ($keyword === "1" || $keyword === "2")
				{
					$sex = $keyword;
					$ret = $this->anonymouschat->set_tag_sex($id, $sex);
					if (!$ret)
					{
						$err_str = "设置聊天对象的性别失败";
						$this->logger($err_str, $ret);
						$this->echo_error($postObj, $err_str);
					}
					$user_info['tag_sex'] = $keyword;
					$ret = $this->anonymouschat->match($user_info);
					$temp = NULL;
					// var_dump($ret);
					if (is_array($ret))
					{
						// $temp = "\n".$ret[0]."\n".$ret[1];
						$keyword = "匹配成功".$temp;
						$this->send_custom_message_text($ret[0], $keyword);
						$this->send_custom_message_text($ret[1], $keyword);

						exit;
					}
					$content = "聊天匹配中，请稍等".$temp;
				}
				else
				{
					$content = "请选择聊天对象的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
				}
				break;
			case 2: // 已选择目标性别（等待匹配中）
				$content = "聊天匹配中，请稍等";
				break;
			case 3: // 聊天中
				// $this->anonymouschat->send_msg($user_info, $keyword);

				// 获取目标的openid
				$openid = $this->anonymouschat->get_openid($tag_id);
				if ($openid === -1)
				{
					$this->echo_error($postObj, "错误1");
				}
				else if ($openid === 0)
				{
					$this->echo_error($postObj, "错误2");
				}

				// 判断消息类型
				$RX_TYPE = trim($postObj->MsgType);
				if ($RX_TYPE == "text")
				{
					$this->send_custom_message_text($openid, $keyword);
				}
				else if ($RX_TYPE == "voice")
				{
					$this->send_custom_message_voice($openid, $postObj->MediaId);
				}
				else if ($RX_TYPE == "image")
				{
					$this->send_custom_message_image($openid, $postObj->MediaId);
				}
				else
				{
					$this->send_custom_message_text($openid, $keyword);
				}
				exit;
				break;
			default:
				// $this->echo_error($postObj);
				break;
		}

		echo $this->transmitText($postObj, $content);
	}

	private function wechat_massage()
	{
		$postObj = simplexml_load_string(file_get_contents("php://input"), 'SimpleXMLElement', LIBXML_NOCDATA);
		if (!$postObj || $postObj->FromUserName === NULL || $postObj->ToUserName === NULL)
		{
			$this->logger("微信消息错误", $postObj);
			exit(1);
		}

		$official_accounts = $this->anonymouschat_config['official_accounts'];
		foreach ($official_accounts as $item)
		{
			if ($item['ghid'] == $postObj->ToUserName)
			{
				$this->appId = $item['appId'];
				$this->appSecret = $item['appSecret'];
				return $postObj;
			}
		}

		$err_str = "配置文件错误";
		$this->logger($err_str, array($postObj, $official_accounts));
		$this->echo_error($postObj, $err_str);
	}

	private function echo_error($postObj, $err = "系统繁忙，请稍后再试")
	{
		// 构造回复的字符串，xml格式
		$resultStr = $this->transmitText($postObj, $err);
		// 输出回复
		echo $resultStr;
		exit;
	}

	// 获取access_token
	private function get_access_token()
	{
		$key = "access_token@".$this->appId;
		$ret = $this->wechat_cache->get_cache($key);
		if ($ret === -1 || $ret === 0 || $ret['expire_time'] < time())
		{
			$access_token = $this->refresh_get_access_token();
		}
		else
		{
			$access_token = $ret['value'];
		}
		return $access_token;
	}
	private function refresh_get_access_token()
	{
		$failure_count = 0;
		while ($failure_count < 10)
		{
			$access_token_json = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret."");
			$ret = json_decode($access_token_json, TRUE);
			if (!($ret === NULL || !isset($ret['access_token'])))
			{
				$key = "access_token@".$this->appId;
				$this->wechat_cache->del_cache($key);
				$access_token = $ret['access_token'];
				$expire_time = time() + 7000;
				$this->wechat_cache->set_cache($key, $access_token, $expire_time);
				return 1;
			}
			$this->logger("access_token获取失败", $ret);
			$failure_count++;
		}

		return 0;
	}

	// 发送客服消息
	private function send_custom_message($msg)
	{
		$failure_count = 0;
		while ($failure_count < 10)
		{
			$access_token = $this->get_access_token();
			$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
			$ret = $this->https_request($url, urldecode(json_encode($msg)));
			$ret = json_decode($ret, TRUE);

			$err_str = "客服消息发送失败";
			if ($ret === NULL || !isset($ret['errcode']))
			{
				$failure_count++;
				$this->logger($err_str, $ret);
				continue;
			}
			$errcode = $ret['errcode'];
			if ($errcode === 0)
			{
				// 调用成功
				return 1;
			}
			else if ($errcode === 45047 || $errcode === 45009)
			{
				/**
				 * 45047 客服接口下行条数超过上限
				 * 在用户没有回复的情况下，最多能向用户发送20条消息
				 * 用户点击菜单或者回复消息之后，又可以发送20条
				 */
				// 45009 接口调用超过限制
				$this->logger($err_str, $ret);
				return 0;
			}
			else if ($errcode === 40001 || $errcode === 40002 || $errcode === 40014)
			{
				// access_token错误
				// 获取 access_token 时 AppSecret 错误，或者 access_token 无效。
				// 40002 不合法的凭证类型
				// 40014 不合法的凭证类型
				$this->logger($err_str, $ret);
				$this->refresh_get_access_token();
			}
			else if ($errcode === -1)
			{
				// 系统繁忙
				$this->logger($err_str, $ret);
				sleep(1);
			}
			else
			{
				$this->logger($err_str, $ret);
			}
			$failure_count++;
		}

		return 0;
	}

	// 发送客服消息，文本消息
	private function send_custom_message_text($touser, $content)
	{
		$msg = array(
			'touser' => "$touser",
			'msgtype' => "text",
			'text' => array(
				'content' => urlencode("$content"),
			)
		);
		return $this->send_custom_message($msg);
	}

	// 回复文本消息
	private function transmitText($object, $content)
	{
		if (!isset($content) || empty($content))
		{
			return "";
		}

		$xmlTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
				</xml>";
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);

		return $result;
	}

	// 发送客服消息，语音消息
	public function send_custom_message_voice($touser, $media_id)
	{
		$msg = array(
			'touser' => "$touser",
			'msgtype' => "voice",
			'voice' => array(
				'media_id' => "$media_id",
			)
		);
		return $this->send_custom_message($msg);
	}

	// 发送客服消息，图片消息
	public function send_custom_message_image($touser, $media_id)
	{
		$msg = array(
			'touser' => "$touser",
			'msgtype' => "image",
			'image' => array(
				'media_id' => "$media_id",
			)
		);
		return $this->send_custom_message($msg);
	}

	// 验证Token
	private function checkToken()
	{
		$signature = $_GET["signature"]; // 微信加密签名
		$timestamp = $_GET["timestamp"]; // 时间戳
		$nonce = $_GET["nonce"]; // 随机数
		$echoStr = $_GET["echostr"]; // 随机字符串

		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if ($tmpStr == $signature)
		{
			if (!isset($echoStr))
			{
				return true;
			}
			else
			{
				echo $echoStr;
				exit;
			}
		}
		else
		{
			//die("token验证失败");
			return false;
		}
	}

	// 用于post数据的curl函数
	private function https_request($url, $data = null, $headers = array("Content-Type: text/xml; charset=utf-8"))
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if (!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

	/**
	 * 通过公众号的ghid，从配置文件获取获取公众号的appId和appSecret
	 */
	private function set_official_accounts($ghid)
	{
		$official_accounts = $this->anonymouschat_config['official_accounts'];
		foreach ($official_accounts as $item)
		{
			if ($item['ghid'] == $ghid)
			{
				$this->appId = $item['appId'];
				$this->appSecret = $item['appSecret'];
				return true;
			}
		}

		$err_str = "配置文件错误";
		$this->logger($err_str, array($ghid, $official_accounts));
		return false;
	}

	/**
	 * 在cli环境下运行
	 */
	public function watch()
	{
		echo 123;
		// 死循环
		while (1)
		{
			// 每15秒运行一次
			sleep(15);

			// 需要删除的记录
			$id_list = array();

			// 获取匹配超时的记录
			$ret = $this->anonymouschat->get_match_failed_record();
			if (!($ret === -1 || empty($ret)))
			{
				echo sprintf("match_failed_record\n%s\n", var_export($ret, TRUE));
				// 发送匹配失败的消息
				foreach ($ret as $item)
				{
					if ($item['state'] != 5)
					{
						if (in_array($item['state'], array(0, 1, 2)))
						{
							if ($this->set_official_accounts($item['ghid']))
							{
								$content = "匹配失败，已退出聊天模式";
								$this->send_custom_message_text($item['openid'], $content);
							}
						}
					}
					array_push($id_list, $item['id']);
				}
				// // 删除匹配失败的记录
				// $this->anonymouschat->del_useless_record($id_list);
			}
			// if ($ret === -1)
			// {
			// 	sleep(15);
			// 	continue;
			// }
			// if (empty($ret))
			// {
			// 	continue;
			// }


			// 获取需要提醒聊天超时的记录
			$ret = $this->anonymouschat->get_need_reminding_record();
			if (!($ret === -1 || empty($ret)))
			{
				echo sprintf("need_reminding_record\n%s\n", var_export($ret, TRUE));
				// 发送提醒聊天超时的消息
				foreach ($ret as $item)
				{
					if ($item['state'] == 3)
					{
						if ($this->set_official_accounts($item['ghid']))
						{
							$chat_time_out = $this->anonymouschat_config['chat_time_out'];
							$start_time = $item['update_time'];
							$remaining_time = (int)(($start_time + $chat_time_out - time()) / 60);
							if ($remaining_time > 0)
							{
								$content = "温馨提示：聊天时间剩余不足".$remaining_time."分钟";
								$this->send_custom_message_text($item['openid'], $content);
							}
						}
					}
				}
			}

			// 获取超时的记录
			$ret = $this->anonymouschat->get_chat_time_out_record();
			if (!($ret === -1 || empty($ret)))
			{
				echo sprintf("chat_time_out_record\n%s\n", var_export($ret, TRUE));
				// 发送聊天超时的消息
				foreach ($ret as $item)
				{
					if ($item['state'] == 3)
					{
						if ($this->set_official_accounts($item['ghid']))
						{
							$content = "温馨提示：聊天已超时\n聊天模式已退出";
							$this->send_custom_message_text($item['openid'], $content);
						}
						array_push($id_list, $item['id']);
					}
				}
				// // 删除匹配失败的记录
				// $this->anonymouschat->del_useless_record($id_list);
			}

			if (!empty($id_list))
			{
				$this->anonymouschat->del_useless_record($id_list);
			}
		}
	}

	/**
	 * 日志记录
	 */
	private function logger($describe, $data = NULL)
	{
		$log_file = date("Y-m-d").".log";
		$dir = "anonymous_log";
		if (!is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}
		$path = $dir."/".$log_file;
		$file = fopen($path, "a");
		if (!$file)
		{
			return;
		}
		$br = "\r\n";
		$txt = "[time]".date('H:i:s').$br;
		$txt .= "[describe]".$describe.$br;
		if (!empty($data))
		{
			$txt .= "[data]".$br;
			$txt .= sprintf("%s", var_export($data, TRUE)).$br;
		}
		$txt .= $br;
		fwrite($file, $txt);
		fclose($file);
	}
}
