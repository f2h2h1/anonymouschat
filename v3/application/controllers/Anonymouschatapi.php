<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Anonymouschatapi extends CI_Controller {

	private $wechat_lib;

	function __construct()
	{
		date_default_timezone_set('Asia/Shanghai');
		define('DDEBUGER', TRUE);
		parent::__construct();

		$this->load->model('Anonymouschat/Anonymouschat_config_model', 'anonymouschat_config');
		$this->load->model('Anonymouschat/anonymouschat_user_model');
		$this->load->model('Anonymouschat/anonymouschat_model', 'anonymouschat');
		$this->load->model('Anonymouschat/match_log_model', 'match_log');
		$this->load->model('Anonymouschat/openid2unionid_model', 'openid2unionid');
		$this->load->model('Anonymouschat/Share_log_model', 'share_log');

		require_once APPPATH.'third_party/wechatlib.php';
		$conf = [
			'token' => 'weixinweixin',
			'temp_path' => APPPATH.'cache/wechat',
			'cache_type' => 'file',
			'log_path' => APPPATH.'logs/anonymous_log/wechat'
		];
		$this->wechat_lib = new app\simple\lib\wechatlib($conf);
	}

	/**
	 * 进入聊天模式
	 */
	public function joinchat()
	{
		define('IN_WECHAT', TRUE);

		if ( ! $this->wechat_lib->check_token())
		{
			logger("token 验证失败", $_GET);
			exit;
		}

		try
		{
			// 接收来自微信的消息
			$postObj = $this->wechat_lib->wechat_massage();

			// 获取openid
			$openid = (string)$postObj->FromUserName;
			define('OPENID', $openid);

			// 获取公众号id
			$ghid = (string)$postObj->ToUserName;
			define('GHID', $ghid);

			$gh_config = $this->set_wechat_config($ghid);
			if ($gh_config === NULL)
			{
				echo $this->log_echo_error('获取公众号的详细信息失败', (array)$postObj, $postObj);
				return;
			}

			// 获取该公众号在本系统的userid
			$userid = $gh_config['userid'];

			// 获取用户信息
			$user_info = $this->anonymouschat->get_openid_info($openid);

			// 判断是否在聊天模式中
			if ($user_info !== NULL)
			{
				$content = '你已进入匿名聊天，请勿重复发送关键词';
				echo $this->wechat_lib->transmit_text($postObj, $content);
				return;
			}

			// 判断能否进入聊天模式

			// 判断是否在时间限制之内
			$start_time_str = $this->anonymouschat_config->get_config($userid, 'active_time_start');
			$end_time_str = $this->anonymouschat_config->get_config($userid, 'active_time_end');
			$start_time = strtotime(date('Y-m-d').' '.$start_time_str);
			$end_time = strtotime(date('Y-m-d').' '.$end_time_str);
			$now_time = time();
			if ($now_time < $start_time or $now_time > $end_time)
			{
				$content = "同学你好，现在重磅推出匿名CP配对交友活动。\n
				为增加体验乐趣以及匹配成功率，活动仅每晚".date('g', $start_time)."-".date('g', $end_time)."点开放CP聊天，\n
				同学们不要错过时间。脱单黑科技，告别单身狗，欢迎奔走相告，拉同学一起来玩。";
				echo $this->wechat_lib->transmit_text($postObj, $content);
				return;
			}

			// 判断是否需要分享
			$user_info = $this->match_log->user_info($openid);
			if ($user_info !== NULL)
			{
				$chat_superior_limit = $this->anonymouschat_config->get_config($userid, 'chat_superior_limit');
				$share_number = $this->anonymouschat_config->get_config($userid, 'share_number');
				$match_count = $user_info['match_count'];
				$share_count = $user_info['share_count'];

				/**
				 * 如果 （成功匹配次数 - （已分享次数 / 需要分享次数 + 一天上限）） 大于 零
				 * 则 需要分享
				 * 否则 可以加入聊天
				 */
				if (($match_count - (floor($share_count / $share_number) + $chat_superior_limit)) >= 0)
				{
					// 还需要分享的次数 = (（成功匹配次数 - 一天上限） + 1) * 需要分享次数 - 已分享次数
					$need_share_number = (($match_count - $chat_superior_limit) + 1) * $share_number - $share_count;
					// 需要分享一定次数才能进入聊天
					$unionid = $this->get_unionid($openid);
					if ($unionid === NULL)
					{
						echo $this->log_echo_error('获取unionid失败', (array)$postObj, $postObj);
						return;
					}
					$media_id = $this->create_share_qrcode($unionid);
					$keyword = "你需要邀请{$need_share_number}位好友才能进入聊天";
					$this->wechat_lib->send_custom_message_text($openid, $keyword);
					$this->wechat_lib->send_custom_message_image($openid, $media_id);
					exit;
				}
			}

			// 把用户加入到聊天模式
			$ret = $this->anonymouschat->joinchat($openid, $ghid);
			if ( ! $ret)
			{
				$content = "把用户加入到聊天模式失败";
				logger($content, $ret);
			}
			else
			{
				$content = "请选择你的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
			}
			echo $this->wechat_lib->transmit_text($postObj, $content);
		}
		catch (app\simple\lib\WechatException $e)
		{
			logger('微信接口错误', $e);
			wechat_exit();
		}
		catch (\Throwable $e)
		{
			logger('未知错误', $e);
			wechat_exit();
		}
	}

	/**
	 * 聊天模式
	 */
	public function chat()
	{
		define('IN_WECHAT', TRUE);

		if ( ! $this->wechat_lib->check_token())
		{
			logger("token 验证失败", $_GET);
			return;
		}

		try
		{
			// 接收来自微信的消息
			$postObj = $this->wechat_lib->wechat_massage();

			// 获取openid
			$openid = $postObj->FromUserName;
			define('OPENID', $openid);

			// 获取公众号id
			$ghid = $postObj->ToUserName;
			define('GHID', $ghid);

			$gh_config = $this->set_wechat_config($ghid);
			if ($gh_config === NULL)
			{
				echo $this->log_echo_error('获取公众号的详细信息失败', (array)$postObj, $postObj);
				return;
			}

			// 获取该公众号在本系统的userid
			$userid = $gh_config['userid'];

			// 获取用户信息
			$user_info = $this->anonymouschat->get_openid_info($openid);

			// 判断是否在聊天模式中
			if ($user_info === NULL)
			{
				// 用户未进入聊天模式，不响应
				echo $this->wechat_lib->do_not_respond();
				return;
			}

			$state = $user_info['state'];
			$id = (int)$user_info['id'];
			$tag_id = (int)$user_info['tag_id'];
			$keyword = (string)$postObj->Content;

			// 退出聊天
			if ($keyword === "exit")
			{
				$this->anonymouschat->exit_chat($user_info);
				$tag_openid = $this->anonymouschat->get_openid($tag_id);
				if ($tag_openid !== NULL)
				{
					$keyword = "对方已退出聊天";
					$this->wechat_lib->send_custom_message_text($tag_openid, $keyword);
				}

				echo $this->wechat_lib->transmit_text($postObj, "已退出聊天");
				return;
			}

			$MALE = "1";
			$FEMALE = "2";

			if (defined('DDEBUGER') && DDEBUGER)
			{
				if ($keyword === "235")
				{
					$keyword = $MALE;
				}
				else if ($keyword === "236")
				{
					$keyword = $FEMALE;
				}
			}

			switch ($state)
			{
				case 0: // 新用户
					if ($keyword === $MALE or $keyword === $FEMALE)
					{
						$sex = (int)$keyword;
						$ret = $this->anonymouschat->set_sex($id, $sex);
						if ( ! $ret)
						{
							$err_str = "设置性别失败";
							echo $this->log_echo_error($err_str, (array)$postObj, $postObj);
							return;
						}
						$content = "请选择聊天对象的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
					}
					else
					{
						$content = "请选择你的性别\n回复 1 为男生\n回复 2 为女生\n回复 exit 退出";
					}
					break;
				case 1: // 已选择性别
					if ($keyword === $MALE or $keyword === $FEMALE)
					{
						$sex = (int)$keyword;
						$ret = $this->anonymouschat->set_tag_sex($id, $sex);
						if ( ! $ret)
						{
							$err_str = "设置聊天对象的性别失败";
							echo $this->log_echo_error($err_str, (array)$postObj, $postObj);
							return;
						}
						$user_info['tag_sex'] = $keyword;
						$ret = $this->match($user_info);
						// $temp = NULL;
						// // var_dump($ret);
						if (is_array($ret))
						{
							// $temp = "\n".$ret[0]."\n".$ret[1];
							$keyword = "匹配成功".$temp;
							$ret1 = $this->wechat_lib->send_custom_message_text($ret[0], $keyword);
							$ret2 = $this->wechat_lib->send_custom_message_text($ret[1], $keyword);
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
					if ($openid === NULL)
					{
						echo $this->log_echo_error('获取目标的openid失败', (array)$postObj, $postObj);
						return;
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
					return;
					break;
				default:
					// $this->echo_error($postObj);
					break;
			}

			echo $this->wechat_lib->transmit_text($postObj, $content);
		}
		catch (app\simple\lib\WechatException $e)
		{
			logger('微信接口错误', $e);
			wechat_exit();
		}
		catch (\Throwable $e)
		{
			logger('未知错误', $e);
			wechat_exit();
		}
	}

	/**
	 * 响应关注事件
	 */
	public function subscribe()
	{
		define('IN_WECHAT', TRUE);

		if ( ! $this->wechat_lib->check_token())
		{
			logger("token 验证失败", $_GET);
			return;
		}

		try
		{
			// 接收来自微信的消息
			$postObj = $this->wechat_lib->wechat_massage();

			// 获取openid
			$openid = $postObj->FromUserName;
			define('OPENID', $openid);

			// 获取公众号id
			$ghid = $postObj->ToUserName;
			define('GHID', $ghid);

			$gh_config = $this->set_wechat_config($ghid);
			if ($gh_config === NULL)
			{
				echo $this->log_echo_error('获取公众号的详细信息失败', (array)$postObj, $postObj);
				return;
			}

			// 获取该公众号在本系统的userid
			$userid = $gh_config['userid'];

			$wechat_msg_obj = (array)$postObj;

			// 判断是否为关注消息
			$event = $wechat_msg_obj['Event'];
			if ($event !== 'subscribe')
			{
				return;
			}

			// 判断是不是来自匿名聊天的分享
			$event_key = $wechat_msg_obj['EventKey'];
			if ( ! empty($event_key))
			{
				// 从 EventKey 中获取分享用户的unionid
				$tag = 'qrscene_';
				$key = strpos($event_key, $tag);
				if ($key === False or $key !== 0)
				{
					echo $this->log_echo_error('Invalid event_key', (array)$postObj, $postObj);
					return;
				}
				$share_unionid = substr($event_key, strlen($tag) - strlen($event_key));
				if (empty($share_unionid))
				{
					echo $this->log_echo_error('empty share_unionid', (array)$postObj, $postObj);
					return;
				}

				// 从分享用户的 unionid 中获取分享用户的 openid
				$share_openid = $this->openid2unionid->get_openid($share_unionid);
				if ($share_openid === NULL)
				{
					echo $this->log_echo_error('通过unionid获取openid失败', (array)$postObj, $postObj);
					return;
				}

				$this->db->trans_begin();

				// 获取unionid
				$unionid = $this->wechat_lib->openid2unionid($openid);
				// $unionid = $this->wechat_lib->get_unionid($openid);
				// if ($unionid === -1 or $unionid === '')
				// {
				// 	$this->db->trans_rollback();
				// 	exit;
				// }

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

				$content = '你已成功分享了一次.';
				$this->wechat_lib->send_custom_message_text($share_openid, $content);
			}

			// 判断是否为关注消息
			// 判断是不是来自匿名聊天的分享
			// 从 EventKey 中获取分享用户的unionid
			// 从分享用户的 unionid 中获取分享用户的 openid
			// 获取该用户的unionid
			// 更新 openid2unionid 表
			// 更新 share_log 表
			// 更新 match_log 表

			$content = "嘿，你来啦：\n回复“交友”，开始召唤神秘的对方吧！.";
			// $content = "欢迎来到匿名CP情人节专场，回复“交友”即可开始";
			// 构造回复的字符串，xml格式
			$resultStr = $this->wechat_lib->transmit_text($postObj, $content);
			// 输出回复
			echo $resultStr;
		}
		catch (\WechatException $e)
		{
			logger('微信接口错误', $e);
			wechat_exit();
		}
		catch (\Throwable $e)
		{
			logger('未知错误', $e);
			wechat_exit();
		}
	}

	/**
	 * 在cli环境下运行
	 */
	public function watch()
	{
		echo "v3\n";

		// 死循环
		while (1)
		{
			// 每15秒运行一次
			sleep(15);
			try
			{
				// 获取全部公众号的信息
				$user_list = $this->anonymouschat_config->get_config_list();
				if ($user_list === NULL)
				{
					throw  new \Exception('empty user_list');
				}
				foreach ($user_list as $gh_config)
				{
					// 通过公众号id获取公众号的详细信息
					$this->wechat_lib->set_app_id($gh_config['subscription_account_app_id']);
					$this->wechat_lib->set_app_secret($gh_config['subscription_account_app_secret']);
		
					// 获取该公众号在本系统的userid
					$userid = $gh_config['userid'];

					// 需要删除的记录
					$id_list = array();

					// 获取匹配超时的记录
					$ret = $this->anonymouschat->get_match_failed_record($userid);
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
									$content = "匹配失败，已退出聊天模式";
									$this->wechat_lib->send_custom_message_text($item['openid'], $content);
								}
							}
							array_push($id_list, $item['id']);
						}
					}

					// 获取超时的记录
					$ret = $this->anonymouschat->get_chat_time_out_record($userid);
					if ( ! ($ret === -1 or empty($ret)))
					{
						echo sprintf("chat_time_out_record\n%s\n", var_export($ret, TRUE));
						// 发送聊天超时的消息
						foreach ($ret as $item)
						{
							if ($item['state'] == 3)
							{
								$content = "温馨提示：聊天已超时\n聊天模式已退出";
								$this->wechat_lib->send_custom_message_text($item['openid'], $content);

								array_push($id_list, $item['id']);
							}
						}
					}

					if ( ! empty($id_list))
					{
						$this->anonymouschat->del_useless_record($id_list);
					}

					// 获取需要提醒聊天超时的记录
					$ret = $this->anonymouschat->get_need_reminding_record($userid);
					if ( ! ($ret === -1 or empty($ret)))
					{
						echo sprintf("need_reminding_record\n%s\n", var_export($ret, TRUE));
						// 发送提醒聊天超时的消息
						foreach ($ret as $item)
						{
							if ($item['state'] == 3)
							{
								$start_time = $item['update_time'];
								$remaining_time = (int)(($start_time + $chat_time_out - time()) / 60);
								if ($remaining_time > 0)
								{
									$content = "温馨提示：聊天时间剩余不足".$remaining_time."分钟";
									$this->wechat_lib->send_custom_message_text($item['openid'], $content);
								}
							}
						}
					}
				}
			}
			catch (\Throwable $e)
			{
				logger('未知错误', $e);
				echo sprintf("error\n%s\n", var_export($e, TRUE));
				sleep(10);
			}
		}
	}

	/**
	 * 匹配
	 */
	private function match(array $user_info) : ?array
	{
		$this->db->trans_begin();

		$openid_arr = $this->anonymouschat->match($user_info);
		if ( ! is_array($openid_arr))
		{
			$this->db->trans_rollback();
			return NULL;
		}

		$ret1 = $this->match_log->match_add($openid_arr[0]);
		$ret2 = $this->match_log->match_add($openid_arr[1]);

		if ( ! $ret1 or ! $ret2)
		{
			$this->db->trans_rollback();
			return NULL;
		}

		$ret3 = $this->db->trans_status();
		if ($ret3 === FALSE)
		{
			$this->db->trans_rollback();
			return NULL;
		}
		else
		{
			$this->db->trans_commit();
			return $openid_arr;
		}
	}

	/**
	 * 生成分享二维码
	 */
	private function create_share_qrcode(string $unionid) : ?string
	{
		$scene_str = $unionid;
		$qrcode_info = $this->wechat_lib->create_qrcode($scene_str);
		$path = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$qrcode_info['ticket'];
		$type = 'image';
		// $this->wechat_lib->add_temp_material($path, $type);

		require_once APPPATH.'third_party/phpqrcode.php';

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
		$bg_binary = file_get_contents("bg2.png"); // x 120 y 220
		// $bg_binary = file_get_contents("bg3.png"); // x 120 y 800 h 150 w 150

		// 生成gd图象资源
		$qr_img = imagecreatefromstring($out);
		$bg_img = imagecreatefromstring($bg_binary);

		// $qr_img = $this->scalePic($qr_img, 150, 150); // 等比例缩放

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

	/**
	 * 获取unionid
	 */
	private function get_unionid($openid) : ?string
	{
		// 搜索数据库中是否存在unionid
		$unionid = $this->openid2unionid->get_unionid($openid);
		if ($unionid !== NULL)
		{
			return $unionid;
		}

		$unionid = $this->wechat_lib->openid2unionid($openid);

		$ret = $this->openid2unionid->add($openid, $unionid);
		if ( ! $ret)
		{
			return NULL;
		}

		return $unionid;
	}

	private function set_wechat_config(string $ghid) : ?array
	{
		$this->wechat_lib->set_gh_id($ghid);

		// 通过公众号id获取公众号的详细信息
		$gh_config = $this->anonymouschat_config->get_config_all($ghid);
		if ($gh_config === NULL)
		{
			// echo $this->log_echo_error('获取公众号的详细信息失败', (array)$postObj, $postObj);
			return NULL;
		}

		// 设置 appid 和 appsecret
		$this->wechat_lib->set_app_id($gh_config['subscription_account_app_id']);
		$this->wechat_lib->set_app_secret($gh_config['subscription_account_app_secret']);

		return $gh_config;
	}

	/**
	 * 使用微信消息的格式输出错误信息
	 */
	private function echo_error(\SimpleXMLElement $postObj, string $err = "系统繁忙，请稍后再试") : string
	{
		return $this->wechat_lib->transmit_text($postObj, $err);
	}

	private function log_echo_error(string $describe, array $data, \SimpleXMLElement $postObj, string $err = "系统繁忙，请稍后再试") : string
	{
		logger($describe, $data);
		return $this->echo_error($postObj, $err);
	}

	/**
	 * @function 等比缩放函数
	 * @param Resource $src 被缩放的处理图片源
	 * @param Integer $maxX 缩放后图片的最大宽度
	 * @param Integer $maxY 缩放后图片的最大高度
	 * @return Resource 处理完后的图片源
	 */
	private function scalePic($src, $maxX = 800, $maxY = 800) {

		$width = imagesx($src);
		$height = imagesy($src);

		// 计算缩放比例
		$scaleX = ($width > $maxX) ? $maxX / $width : 1;
		$scaleY = ($height > $maxY) ? $maxY / $height : 1;
		$scale = $scaleX > $scaleY ? $scaleY : $scaleX;

		// 计算缩放后的尺寸
		$sWidth = floor($width * $scale);
		$sHeight = floor($height * $scale);

		// 创建目标图像资源
		$nim = imagecreatetruecolor($sWidth, $sHeight);

		// 等比缩放
		$ret = imagecopyresampled($nim,$src,0,0,0,0,$sWidth,$sHeight,$width,$height);

		return $nim;
	}
}
