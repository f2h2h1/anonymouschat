<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'wechat.class.php';

class Test extends CI_Controller
{
	public function index()
	{
		$xml = "<xml>
		<ToUserName><![CDATA[gh_204936aea56d]]></ToUserName>
		<FromUserName><![CDATA[ojpX_jig-gyi3_Q9fHXQ4rdHniQs]]></FromUserName>
		<CreateTime>1542450345</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[?]]></Content>
		<MsgId>1234567890abcdef</MsgId>
	</xml>";

		// 接收来自微信的消息
		$postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		// 文本回复内容
		$content = "test";
		// 构造回复的字符串，xml格式
		$resultStr = $this->transmitText($postObj, $content);
		// 输出回复
		echo $resultStr;
	}

	public function test1()
	{
		// 接收来自微信的消息
		$postObj = $this->wechat->receiveMessage();
		// 文本回复内容
		// $content = "test2";
		$content = date("Y-m-d H:i:s",time())."\nOpenID：".$postObj->FromUserName."\n";
		// 构造回复的字符串，xml格式
		$resultStr = $this->transmitText($postObj, $content);
		// 输出回复
		echo $resultStr;
	}

	public function test2()
	{
		echo $this->wechat_cache->get_news();
	}

	public function test3()
	{
		// 接收来自微信的消息
		$postObj = $this->wechat->receiveMessage();
		// 文本回复内容
		// $content = "test2";
		$content = date("Y-m-d H:i:s",time())."\nOpenID：".$postObj->FromUserName."\n";
		$this->anonymouschat->is_joinchat($postObj->FromUserName);


		// 构造回复的字符串，xml格式
		$resultStr = $this->transmitText($postObj, $content);
		// 输出回复
		echo $resultStr;
	}

	public function test4()
	{
		// 获取access_token
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret."";
		echo $url;
		$access_token_json = file_get_contents($url);
		// 把JSON编码的字符串转换为object
		$access_token_object = json_decode($access_token_json);
		$access_token = $access_token_object->access_token;
		var_dump($access_token);
		$touser = "oe-Ih1QMbZQkMfKDR6wfNfocB0Mw";
		$content = "客服消息测试";
		$msg = array(
			'touser' => "$touser",
			'msgtype' => "text",
			'text' => array(
				'content' => urlencode("$content"),
			)
		);

		// $access_token = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
		echo $url;
		var_dump($this->https_request($url, urldecode(json_encode($msg))));
	}

	public function test5()
	{
		$this->load->database();
		$query = $this->db->query("SELECT * FROM `wechat_chache`");
		print_r($query->result_array());
		echo 12345512;
	}
	
	public function test6()
	{
		$anoymouschat_config = $this->config->load('anonymouschat');
		var_dump($anoymouschat_config);
		var_dump($this->config->item('time_out'));
		var_dump($this->config->item('official_accounts'));
	}
}