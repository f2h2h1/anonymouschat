<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Anonymouschat extends CI_Controller {

	private $appId;
	private $appSecret;
	private $token;
	// private $anonymouschat_config;

	function __construct()
	{
		parent::__construct();
		$this->load->library('wechat_lib');
		$this->load->model('wechat_cache_model', 'wechat_cache');
		$this->load->model('anonymouschat_config_model', 'anonymouschat_config');
		$this->load->model('anonymouschat_model', 'anonymouschat');
		$this->load->model('match_log_model', 'match_log');
		$this->load->model('openid2unionid_model', 'openid2unionid');
		$this->load->model('Share_log_model', 'share_log');

		// $this->load->library('wechat_lib');

		// $anoymouschat_config = $this->config->load('anonymouschat');
		// $this->anonymouschat_config = $this->config->item('anonymouschat');
		// $this->token = $this->anonymouschat_config['wechat_token'];

		$token = 'weixinweixin';
		$app_id = $this->anonymouschat_config->get_config('subscription_account_app_id');
		$app_secret = $this->anonymouschat_config->get_config('subscription_account_app_secret');
		$this->wechat_lib->init($token, $app_id, $app_secret);
	}

	/**
	 * 进入聊天模式
	 */
	public function joinchat()
	{
		$this->wechat_lib->check_token();

		// 接收来自微信的消息
		$postObj = $this->wechat_lib->wechat_massage();
		logger('debug-postObj', $postObj);
		// 获取openid
		$openid = $postObj->FromUserName;

		// 获取公众号id
		$ghid = $postObj->ToUserName;

		// 判断能否进入聊天模式
		$need_share_number = $this->is_can_join($openid);
		if ($need_share_number !== 0)
		{
			logger('debug-need_share_number', $need_share_number);
			// 需要分享一定次数才能进入聊天
			$media_id = $this->create_share_qrcode($openid);
			// $keyword = "你需要邀请{$need_share_number}位好友才能进入聊天";
			$keyword = 'halo同学，您今天的聊天机会已用完，邀请一位好友即可获得一次机会，快快行动吧';
			$this->wechat_lib->send_custom_message_text($openid, $keyword);
			$this->wechat_lib->send_custom_message_image($openid, $media_id);
			exit;
		}

		// 判断是否已进入聊天模式
		$ret = $this->anonymouschat->is_joinchat($openid);
		if ($ret === -1)
		{
			$content = "进入聊天模式失败";
			logger($content, $ret);
		}
		else if ($ret === 1)
		{
			$content = "你已进入匿名聊天，请勿重复发送关键词";
		}
		else if ($ret === 0)
		{

			// 判断是否在有效时间内
			// if (isset($this->anonymouschat_config->get_config('active_time')))
			// {
				// $active_time = $this->anonymouschat_config->get_config('active_time');
				$start_time_str = $this->anonymouschat_config->get_config('active_time_start');
				$end_time_str = $this->anonymouschat_config->get_config('active_time_end');
				$start_time = strtotime(date('Y-m-d').' '.$start_time_str);
				$end_time = strtotime(date('Y-m-d').' '.$end_time_str);
				// 如果结束时间小于开始时间，则结束时间向前加一天
				// if ($end_time < strtotime(date('Y-m-d').' 06:00')) 
				if ($end_time < $start_time)
				{
					$end_time = strtotime('+1 day', $end_time);
				}
				$now_time = time();
				if ($now_time < $start_time or $now_time > $end_time)
				{
					$err = "同学你好，现在重磅推出匿名CP配对交友活动。\n
					为增加体验乐趣以及匹配成功率，活动仅每晚".date('g', $start_time)."-".date('g', $end_time)."点开放CP聊天，\n
					同学们不要错过时间。脱单黑科技，告别单身狗，欢迎奔走相告，拉同学一起来玩。";
					// $err = "匿名聊天暂未开启，开启时间为\n".$start_time_str." — ".date('H:i:s', $end_time);
					// $err .= "\n".$now_time."\n".$start_time."\n".$end_time;
					// $this->wechat_lib->echo_error($postObj, $err);
				}
			// }

			// 把用户加入到聊天模式
			$ret = $this->anonymouschat->joinchat($openid, $ghid);
			if ( ! $ret)
			{
				$content = "把用户加入到聊天模式";
				logger($content, $ret);
			}
			else
			{
				$content = "请选择你的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
			}
		}

		// 构造回复的字符串，xml格式
		$resultStr = $this->wechat_lib->transmit_text($postObj, $content);
		// 输出回复
		echo $resultStr;
	}

	/**
	 * 聊天模式
	 */
	public function chat()
	{
		$this->wechat_lib->check_token();

		// 接收来自微信的消息
		$postObj = $this->wechat_lib->wechat_massage();

		// 获取openid
		$openid = $postObj->FromUserName;

		// 获取公众号id
		$ghid = $postObj->ToUserName;

		// 获取用户信息
		$user_info = $this->anonymouschat->get_openid_info($openid);
		if ($user_info === -1)
		{
			logger("获取用户信息失败", $user_info);
			$this->echo_error($postObj);
		}

		// 判断用户状态
		if ($user_info === 0 or ! isset($user_info[0]))
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
			if ( ! ($ret === -1 or $ret === 0))
			{
				$tag_openid = $ret;
				$keyword = "对方已退出聊天";
				$this->wechat_lib->send_custom_message_text($tag_openid, $keyword);
			}

			$this->wechat_lib->echo_error($postObj, "已退出聊天");
		}

		switch ($state)
		{
			case 0: // 新用户
				if ($keyword === "1" or $keyword === "2")
				{
					$sex = $keyword;
					$ret = $this->anonymouschat->set_sex($id, $sex);
					if ( ! $ret)
					{
						$err_str = "设置性别失败";
						logger($err_str, $ret);
						$this->wechat_lib->echo_error($postObj, $err_str);
					}
					$content = "请选择聊天对象的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
				}
				else
				{
					$content = "请选择你的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出".$keyword."\n".$state;
				}
				break;
			case 1: // 已选择性别
				if ($keyword === "1" or $keyword === "2")
				{
					$sex = $keyword;
					$ret = $this->anonymouschat->set_tag_sex($id, $sex);
					if ( ! $ret)
					{
						$err_str = "设置聊天对象的性别失败";
						logger($err_str, $ret);
						$this->wechat_lib->echo_error($postObj, $err_str);
					}
					$user_info['tag_sex'] = $keyword;
					$ret = $this->match($user_info);
					$temp = NULL;
					// var_dump($ret);
					if (is_array($ret))
					{
						// $temp = "\n".$ret[0]."\n".$ret[1];
						$keyword = "匹配成功".$temp;
						$this->wechat_lib->send_custom_message_text($ret[0], $keyword);
						$this->wechat_lib->send_custom_message_text($ret[1], $keyword);

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
					$this->wechat_lib->echo_error($postObj, "错误1");
				}
				else if ($openid === 0)
				{
					$this->wechat_lib->echo_error($postObj, "错误2");
				}

				// 判断消息类型
				$RX_TYPE = trim($postObj->MsgType);
				if ($RX_TYPE == "text")
				{
					$filter_words_list = require_once(dirname(__FILE__).'/../third_party/tag.php');
					$keyword = strtr($keyword, array_combine($filter_words_list, array_fill(0, count($filter_words_list), '*')));
					$this->wechat_lib->send_custom_message_text($openid, $keyword);
				}
				else if ($RX_TYPE == "voice")
				{
					$this->wechat_lib->send_custom_message_voice($openid, $postObj->MediaId);
				}
				else if ($RX_TYPE == "image")
				{
					$this->wechat_lib->send_custom_message_image($openid, $postObj->MediaId);
				}
				else
				{
					$this->wechat_lib->send_custom_message_text($openid, $keyword);
				}
				exit;
				break;
			default:
				// $this->echo_error($postObj);
				break;
		}

		echo $this->wechat_lib->transmit_text($postObj, $content);
	}

	/**
	 * 响应关注事件
	 */
	public function subscribe()
	{
		$this->wechat_lib->check_token();

		// 接收来自微信的消息
		$postObj = $this->wechat_lib->wechat_massage();

		// 获取openid
		$openid = $postObj->FromUserName;

		// 获取公众号id
		$ghid = $postObj->ToUserName;

		// logger('subscribe', $postObj);

		$wechat_msg_obj = (array)$postObj;
		$event_key = $wechat_msg_obj['EventKey'];
		// logger('debug1', $event_key);
		// logger('debug2', $wechat_msg_obj);
		if ( ! empty($event_key))
		{
			$tag = 'qrscene_';
			$key = strpos($event_key, $tag);
			if ($key === False or $key !== 0)
			{
				fatal_error('Invalid event_key', $event_key);
			}
			$share_unionid = substr($event_key, strlen($tag) - strlen($event_key));
			if (empty($share_unionid))
			{
				fatal_error('empty share_unionid', $event_key);
			}
			// $event_key_arr = explode('_', $event_key);
			// if ($event_key_arr === FALSE
			// 	or count($event_key_arr) != 2
			// 	or $event_key_arr[0] != 'qrscene')
			// {
			// 	fatal_error('Invalid event_key', $event_key_arr);
			// }
			// $share_unionid = $event_key_arr[1];

			$share_openid = $this->openid2unionid->get_openid($share_unionid);
			if ($share_openid === -1)
			{
				fatal_error('数据库错误，openid2unionid->get_openid');
			}
			if ($share_openid === '')
			{
				fatal_error('通过unionid获取openid失败', array('unionid'=>$share_unionid, 'openid'=>$share_openid, 'last_query'=>$this->db->last_query()));
			}

			$this->db->trans_begin();

			// 获取unionid
			$unionid = $this->wechat_lib->get_unionid($openid);
			if ($unionid === -1 or $unionid === '')
			{
				$this->db->trans_rollback();
				exit;
			}

			$ret = $this->share_log->add($share_unionid, $unionid);
			if ( ! $ret)
			{
				logger('数据库错误，share_log->add', $this->db->last_query());
				$this->db->trans_rollback();
				exit;
			}
			$ret = $this->match_log->share_add($share_openid);
			if ( ! $ret)
			{
				logger('数据库错误，match_log->share_add', $this->db->last_query());
				$this->db->trans_rollback();
				exit;
			}
			$this->db->trans_commit();

			$content = '你已成功分享了一次';
			$this->wechat_lib->send_custom_message_text($share_openid, $content);
		}

		// 判断是不是来自匿名聊天的分享
		// 从 EventKey 中获取分享用户的unionid
		// 获取该用户的unionid
		// 更新 openid2unionid 表
		// 更新 share_log 表
		// 更新 match_log 表


		$content = "嘿，你来啦：\n回复“交友”，开始召唤神秘的对方吧！";
		// 构造回复的字符串，xml格式
		$resultStr = $this->wechat_lib->transmit_text($postObj, $content);
		// 输出回复
		echo $resultStr;
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
		logger($err_str, array($ghid, $official_accounts));
		return false;
	}

	/**
	 * 在cli环境下运行
	 */
	public function watch()
	{
		echo 'v2';
		$chat_time_out = $this->anonymouschat_config->get_config('chat_time_out');
		// 死循环
		while (1)
		{
			// 每15秒运行一次
			sleep(15);

			// 需要删除的记录
			$id_list = array();

			// 获取匹配超时的记录
			$ret = $this->anonymouschat->get_match_failed_record();
			if ( ! ($ret === -1 or empty($ret)))
			{
				echo sprintf("match_failed_record\n%s\n", var_export($ret, TRUE));
				// 发送匹配失败的消息
				foreach ($ret as $item)
				{
					if ($item['state'] != 5)
					{
						if (in_array($item['state'], array(0, 1, 2)))
						{
							// if ($this->set_official_accounts($item['ghid']))
							// {
								$content = "匹配失败，已退出聊天模式";
								$this->wechat_lib->send_custom_message_text($item['openid'], $content);
							// }
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
			if ( ! ($ret === -1 or empty($ret)))
			{
				echo sprintf("need_reminding_record\n%s\n", var_export($ret, TRUE));
				// 发送提醒聊天超时的消息
				foreach ($ret as $item)
				{
					if ($item['state'] == 3)
					{
						// if ($this->set_official_accounts($item['ghid']))
						// {

							$start_time = $item['update_time'];
							$remaining_time = (int)(($start_time + $chat_time_out - time()) / 60);
							if ($remaining_time > 0)
							{
								$content = "温馨提示：聊天时间剩余不足".$remaining_time."分钟";
								$this->wechat_lib->send_custom_message_text($item['openid'], $content);
							}
						// }
					}
				}
			}

			// 获取超时的记录
			$ret = $this->anonymouschat->get_chat_time_out_record();
			if ( ! ($ret === -1 or empty($ret)))
			{
				echo sprintf("chat_time_out_record\n%s\n", var_export($ret, TRUE));
				// 发送聊天超时的消息
				foreach ($ret as $item)
				{
					if ($item['state'] == 3)
					{
						// if ($this->set_official_accounts($item['ghid']))
						// {
							$content = "温馨提示：聊天已超时\n聊天模式已退出";
							$this->wechat_lib->send_custom_message_text($item['openid'], $content);
						// }
						array_push($id_list, $item['id']);
					}
				}
				// // 删除匹配失败的记录
				// $this->anonymouschat->del_useless_record($id_list);
			}

			if ( ! empty($id_list))
			{
				$this->anonymouschat->del_useless_record($id_list);
			}
		}
	}

	/**
	 * 匹配
	 */
	private function match($user_info)
	{
		$this->db->trans_begin();

		$openid_arr = $this->anonymouschat->match($user_info);
		if ( ! is_array($openid_arr))
		{
			$this->db->trans_rollback();
			return 0;
		}

		$ret1 = $this->match_log->match_add($openid_arr[0]);
		$ret2 = $this->match_log->match_add($openid_arr[1]);

		if ( ! $ret1 or ! $ret2)
		{
			$this->db->trans_rollback();
			return 0;
		}

		$ret3 = $this->db->trans_status();
		if ($ret3 === FALSE)
		{
			$this->db->trans_rollback();
			return 0;
		}
		else
		{
			$this->db->trans_commit();
			return $openid_arr;
		}
	}

	/**
	 * 判断能否进入聊天模式
	 */
	private function is_can_join($openid)
	{
		$user_info = $this->match_log->user_info($openid);
		if ($user_info === -1)
		{
			fatal_error('数据库错误，match_log->user_info');
		}
		if (is_array($user_info) && count($user_info) === 0)
		{
			return 0;
		}
		$chat_superior_limit = $this->anonymouschat_config->get_config('chat_superior_limit');
		$share_number = $this->anonymouschat_config->get_config('share_number');
		$match_count = $user_info['match_count'];
		$share_count = $user_info['share_count'];

		/**
		 * 如果 （成功匹配次数 - （已分享次数 / 需要分享次数 + 一天上限）） 小于 零
		 * 则 可以加入聊天
		 * 否则 需要分享
		 */
		if (($match_count - (floor($share_count / $share_number) + $chat_superior_limit)) < 0)
		{
			return 0;
		}
		else
		{
			// 还需要分享的次数 = (（成功匹配次数 - 一天上限） + 1) * 需要分享次数 - 已分享次数
			$need_share_number = (($match_count - $chat_superior_limit) + 1) * $share_number - $share_count;
			return $need_share_number;
		}
	}

	/**
	 * 生成分享二维码
	 */
	private function create_share_qrcode($openid)
	{
		// 获取unionid
		$unionid = $this->wechat_lib->get_unionid($openid);
		if ($unionid === -1 or $unionid === '')
		{
			exit;
		}
		$scene_str = $unionid;
		$qrcode_info = $this->wechat_lib->create_qrcode($scene_str);
		$path = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$qrcode_info['ticket'];
		$type = 'image';
		// $this->wechat_lib->add_temp_material($path, $type);
		try
		{

			require dirname(__FILE__).'/../third_party/phpqrcode.php';

			// 生成二维码
			ob_start(); // 缓冲输出
			$text = $qrcode_info['url']; // 二维码扫描出来的文本
			$outfile = false; // 输出到的文件名，如果为 false 则输出到浏览器
			$level = QR_ECLEVEL_H; // 纠错率
			$size = 5; // 二维码尺寸 1=33px 尺寸为 33px*33px
			$margin = 2; // 二维码四周的空白大小
			QRcode::png($text, $outfile, $level, $size, $margin); // 输出图片到缓冲区
			$out = ob_get_clean(); // 把缓冲区输出赋值给变量

			// 合并二维码和背景图
			$bg_binary = file_get_contents("bg2.png");

			// 生成gd图象资源
			$qr_img = imagecreatefromstring($out);
			$bg_img = imagecreatefromstring($bg_binary);

			$dst_image = $bg_img;
			$src_image = $qr_img;

			$src_w = imagesx($src_image);
			$src_h = imagesy($src_image);

			$dst_x = 120;
			$dst_y = 220;

			$src_x = 0;
			$src_y = 0;

			$dst_w = $src_w;
			$dst_h = $src_h;

			imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

			ob_start(); // 缓冲输出
			imagepng($dst_image); // 输出图片到缓冲区
			$out = ob_get_clean(); // 把缓冲区输出赋值给变量

			// $binary = file_get_contents($path);
			// if ($binary === FALSE)
			// {
			// 	throw new \Exception("open file fail");
			// }

			$ret = $this->wechat_lib->add_temp_material_img($out);
			return $ret['media_id'];
		}
		catch (\Exception $e)
		{
			fatal_error('create qrcode fail', $e->__toString());
		}
	}

	/**
	 * 获取unionid
	 */

	public function test1()
	{
		// logger('测试', array('input_get'=>$this->input->get(), '_GET'=>$_GET));
		$token = "weixinweixin";
		$app_id = $this->anonymouschat_config->get_config('subscription_account_app_id');
		$app_secret = $this->anonymouschat_config->get_config('subscription_account_app_secret');
		$this->wechat_lib->init($token, $app_id, $app_secret);
		$this->wechat_lib->check_token();
		$postObj = $this->wechat_lib->wechat_massage();


		// 接收来自微信的消息
		$postObj = $this->wechat_lib->wechat_massage();
		$content = "测试\n".$postObj->FromUserName;
		echo $this->wechat_lib->transmit_text($postObj, $content);
		$this->wechat_lib->send_custom_message_text("oe-Ih1QMbZQkMfKDR6wfNfocB0Mw", "新的客服消息");
	}
}
