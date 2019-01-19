<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wechat_lib {

	private $CI;
	private $anonymouschat_config;
	private $token;
	private $app_id;
	private $app_secret;

	function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('wechat_cache_model', 'wechat_cache');
		$this->CI->load->model('openid2unionid_model', 'openid2unionid');
	}

	public function test()
	{
		echo 1;
		logger('测试');
	}

	public function init($token, $app_id, $app_secret)
	{
		$this->token = $token;
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;
	}

	/**
	 * 验证Token
	 */
	public function check_token()
	{
		$signature = $this->CI->input->get("signature"); // 微信加密签名
		$timestamp = $this->CI->input->get("timestamp"); // 时间戳
		$nonce     = $this->CI->input->get("nonce");     // 随机数
		$echoStr   = $this->CI->input->get("echostr");   // 随机字符串

		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if ($tmpStr == $signature)
		{
			if ( ! isset($echoStr))
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
			fatal_error("token 验证失败", array('input_get'=>$this->CI->input->get(), '_GET'=>$_GET));
			return false;
		}
	}

	/**
	 * 接收微信的消息
	 */
	public function wechat_massage()
	{
		$postObj = simplexml_load_string(file_get_contents("php://input"), 'SimpleXMLElement', LIBXML_NOCDATA);
		if ( ! $postObj or $postObj->FromUserName === NULL or $postObj->ToUserName === NULL)
		{
			fatal_error("微信消息错误", $postObj);
		}

		return $postObj;
	}

	/**
	 * 使用微信消息的格式输出错误信息
	 */
	public function echo_error($postObj, $err = "系统繁忙，请稍后再试")
	{
		// 构造回复的字符串，xml格式
		$resultStr = $this->transmit_text($postObj, $err);
		// 输出回复
		echo $resultStr;
		exit;
	}

	/**
	 * 获取access_token
	 */
	private function get_access_token()
	{
		$key = "access_token@".$this->app_id;
		$ret = $this->CI->wechat_cache->get_cache($key);
		if ($ret === -1 or $ret === 0 or $ret['expire_time'] < time())
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
			$access_token_json = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->app_id."&secret=".$this->app_secret."");
			$ret = json_decode($access_token_json, TRUE);
			if ( ! ($ret === NULL or !isset($ret['access_token'])))
			{
				$key = "access_token@".$this->app_id;
				$this->CI->wechat_cache->del_cache($key);
				$access_token = $ret['access_token'];
				$expire_time = time() + 7000;
				$this->CI->wechat_cache->set_cache($key, $access_token, $expire_time);
				return 1;
			}
			logger("access_token获取失败", $ret);
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
			$ret = https_request($url, urldecode(json_encode($msg)));
			$ret = json_decode($ret, TRUE);

			$err_str = "客服消息发送失败";
			if ($ret === NULL or !isset($ret['errcode']))
			{
				$failure_count++;
				logger($err_str, $ret);
				continue;
			}
			$errcode = $ret['errcode'];
			if ($errcode === 0)
			{
				// 调用成功
				return 1;
			}
			else if ($errcode === 45047 or $errcode === 45009)
			{
				/**
				 * 45047 客服接口下行条数超过上限
				 * 在用户没有回复的情况下，最多能向用户发送20条消息
				 * 用户点击菜单或者回复消息之后，又可以发送20条
				 */
				// 45009 接口调用超过限制
				logger($err_str, $ret);
				return 0;
			}
			else if ($errcode === 40001 or $errcode === 40002 or $errcode === 40014)
			{
				// access_token错误
				// 获取 access_token 时 AppSecret 错误，或者 access_token 无效。
				// 40002 不合法的凭证类型
				// 40014 不合法的凭证类型
				logger($err_str, $ret);
				$this->refresh_get_access_token();
			}
			else if ($errcode === -1)
			{
				// 系统繁忙
				logger($err_str, $ret);
				sleep(1);
			}
			else
			{
				logger($err_str, $ret);
			}
			$failure_count++;
		}

		return 0;
	}

	/**
	 * 发送客服消息，文本消息
	 */
	public function send_custom_message_text($touser, $content)
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

	/**
	 * 发送客服消息，语音消息
	 */
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

	/**
	 * 发送客服消息，图片消息
	 */
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

	/**
	 * 回复文本消息
	 */
	public function transmit_text($object, $content)
	{
		if ( ! isset($content) or empty($content))
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

	/**
	 * 获取unionid
	 */
	public function get_unionid($openid)
	{
		// 搜索数据库中是否存在unionid
		$unionid = $this->CI->openid2unionid->get_unionid($openid);
		if ($unionid === -1)
		{
			return $unionid;
		}
		else if ($unionid !== '')
		{
			return $unionid;
		}
		$unionid = $this->openid2unionid($openid);
		if ($unionid === '')
		{
			return $unionid;
		}
		$ret = $this->CI->openid2unionid->add($openid, $unionid);
		if ( ! $ret)
		{
			return $ret;
		}
		return $unionid;
	}
	private function openid2unionid($openid)
	{
		$access_token = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
		$user_info_json = file_get_contents($url);
		$user_info_arr = json_decode($user_info_json, TRUE);
		if ($user_info_arr === NULL)
		{
			logger('unionid获取失败', array('url' => $url, 'json_last_error_msg' => json_last_error_msg()));
			return '';
		}
		else if (empty($user_info_arr['unionid']))
		{
			logger('unionid获取失败', array('url' => $url, 'user_info_json' => $user_info_json));
			return '';
		}
		return $user_info_arr['unionid'];
	}

	/**
	 * 创建临时二维码
	 */
	public function create_qrcode($scene_str)
	{
		$access_token = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
		$data = '{"expire_seconds": 604800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scene_str.'"}}}';
		$opts = array(
			'http'=>array(
				'method' => "POST",
				'header' => "Content-type: application/json;charset=UTF-8",
				'content' => $data
			)
		);
		$context = stream_context_create($opts);
		logger('context', $context);
		$ret_raw = file_get_contents($url, false, $context);
		$ret = json_decode($ret_raw, TRUE);
		if ($ret === NULL)
		{
			logger('unionid获取失败', array('url' => $url, 'opts' => $opts, 'json_last_error_msg' => json_last_error_msg()));
			return '';
		}
		else if (empty($ret['ticket']) or empty($ret['url']))
		{
			logger('unionid获取失败', array('url' => $url, 'ret_raw' => $ret_raw, 'json_last_error_msg' => json_last_error_msg()));
			return '';
		}
		return $ret;
	}

	/**
	 * 上传临时素材-图片
	 */
	public function add_temp_material_img($binary, $file_name = '')
	{
		$img_info = getimagesizefromstring($binary);
		if (empty($img_info[2]) or empty($img_info['mime']))
		{
			throw new \Exception("Invalid image file");
		}

		if ($file_name === '')
		{
			$file_name = time().image_type_to_extension($img_info[2]);
		}
		$file_type = $img_info['mime'];
		$type = 'image';
		return $this->add_temp_material($file_name, $file_type, $type, $binary);
	}

	/**
	 * 上传临时素材
	 */
	public function add_temp_material($file_name, $file_type, $type, $binary)
	{
		// 拼接请求头
		$boundary = time().mt_rand(10000, 99999);
		$br = "-----------------------------".$boundary;
		$nl = "\r\n";

		$data = $br.$nl.'Content-Disposition: form-data; name="media"; filename="'.$file_name.'"'.$nl;
		$data .= "Content-Type: ".$file_type.$nl.$nl;
		$data .= $binary.$nl;
		$data .= $br."--".$nl.$nl;

		$Length = strlen($data);
		$header = array(
			'Content-Length'=>$Length,
			'Content-Type'=>'multipart/form-data; boundary=---------------------------'.$boundary,
		);

		$header_str = '';
		foreach ($header as $k => $v) {
			$header_str .= $k.": ".$v.$nl;
		}
		// 发送请求
		$opts = array(
			'http'=>array(
				'method' => "POST",
				'header' => $header_str,
				'content' => $data
			)
		);
		$context = stream_context_create($opts);
		$access_token = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
		$ret = file_get_contents($url, false, $context);
		$err = 'upload temp material.';
		if ($ret === FALSE)
		{
			$err .= ' request fail';
			throw new \Exception($err);
		}
		$ret = json_decode($ret, TRUE);
		if ($ret === NULL)
		{
			$err .= ' json decode fail.';
			$err .= 'json_last_error_msg'.json_last_error_msg();
			throw new \Exception($err);
		}
		if (empty($ret['media_id']))
		{
			throw new \Exception('empty media_id');
		}
		return $ret;
	}
}
