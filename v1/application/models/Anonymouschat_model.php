<?php
class Anonymouschat_model extends CI_Model {

	private $anonymouschat_config;

	public function __construct()
	{
		$this->load->database();
		$anoymouschat_config = $this->config->load('anonymouschat');
		$this->anonymouschat_config = $this->config->item('anonymouschat');
		$this->token = $this->anonymouschat_config['wechat_token'];
	}

	/**
	 * 判断是否已进入聊天模式
	 */
	public function is_joinchat($openid)
	{
		$row = $this->get_openid_info($openid);
		if ($row === -1)
		{
			return $row;
		}

		if (empty($row))
		{
			// 用户未进入聊天模式
			return 0;
		}

		// 用户已进入聊天模式
		return 1;
	}

	/**
	 * 进入聊天模式
	 */
	public function joinchat($openid, $ghid)
	{
		$timestamp = time();
		$data = array(
			'openid' => $openid,
			'ghid' => $ghid,
			'sex' => 0,
			'tag_sex' => 0,
			'tag_id' => '0',
			'state' => 0,
			'update_time' => $timestamp,
			'create_time' => $timestamp
		);

		return $this->db->insert('anonymouschat', $data);
	}

	/**
	 * 获取用户信息
	 */
	public function get_openid_info($openid)
	{
		$query = $this->db->where('openid =', $openid)->where('state !=', 5)->order_by('create_time', 'DESC')->limit(1)->get('anonymouschat');
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();

		return $row;
	}

	/**
	 * 设置用户性别
	 */
	public function set_sex($id, $sex)
	{
		$timestamp = time();
		$data = array(
			'sex' => $sex,
			'state' => 1,
			'update_time' => $timestamp
		);

		return $this->db->set($data)->where('id', $id)->update('anonymouschat');
	}

	/**
	 * 设置目标性别
	 */
	public function set_tag_sex($id, $sex)
	{
		$timestamp = time();
		$data = array(
			'tag_sex' => $sex,
			'state' => 2,
			'update_time' => $timestamp
		);

		return $this->db->set($data)->where('id', $id)->update('anonymouschat');
	}

	/**
	 * 获取目标的openid
	 */
	public function get_openid($id)
	{
		$query = $this->db->select('openid')->where('id', $id)->limit(1)->get('anonymouschat');
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();

		if (empty($row) || !isset($row[0]['openid']))
		{
			return 0;
		}

		return $row[0]['openid'];
	}

	/**
	 * 退出聊天
	 */
	public function exit_chat($user_info)
	{
		$id = $user_info['id'];
		$tag_id = $user_info['tag_id'];

		$timestamp = time();

		$data = array(
			'state' => 5,
			'update_time' => $timestamp
		);

		$this->db->where('id', $id);

		if (!empty($tag_id))
		{
			$this->db->or_where('id', $tag_id);
		}

		return $this->db->set($data)->update('anonymouschat');
	}

	// 匹配
	public function match($user_info)
	{
		$id = $user_info['id'];
		$openid = $user_info['openid'];
		$ghid = $user_info['ghid'];
		$tag_sex = $user_info['tag_sex'];
		$timestamp = time();
		$timestamp = $timestamp - 300; // 60*5=300 分钟
		$query = $this->db->select('id, openid')
				->where('ghid =', $ghid)
				->where('sex =', $tag_sex)
				->where('state =', 2)
				->where('id !=', $id)
				->where('create_time >', $timestamp)
				->order_by('update_time', 'ASC')->limit(1)
				->get('anonymouschat');
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();
		// echo $this->db->last_query();
		// var_dump($row);
		// 匹配失败
		if (empty($row) || !isset($row[0]['id']) || !isset($row[0]['openid']))
		{
			return 0;
		}

		// 匹配成功
		$timestamp = time();
		$tag_id = $row[0]['id'];
		$tag_openid = $row[0]['openid'];
		$this->db->trans_begin();

		$data = array(
			'state' => 3,
			'update_time' => $timestamp
		);
		$data['tag_id'] = $tag_id;
		$ret1 = $this->db->where('id', $id)->set($data)->update('anonymouschat');

		$data['tag_id'] = $id;
		$ret2 = $this->db->where('id', $tag_id)->set($data)->update('anonymouschat');

		$ret3 = $this->db->trans_status();

		// var_dump($ret1);
		// var_dump($ret2);
		// var_dump($ret3);

		if ($ret3 === FALSE)
		{
			$this->db->trans_rollback();
			return 0;
		}
		else
		{
			$this->db->trans_commit();
			return array($openid, $tag_openid);
		}
	}

	// 判断是否已经匹配成功
	public function is_match($id)
	{
		$query = $this->db->select('state')
				->where('id =', $id)
				->where('state =', 3)->limit(1)
				->get('anonymouschat');
				$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();

		// 未匹配
		if (empty($row) || !isset($row[0]['state']) || $row[0]['state'] != 3)
		{
			return 0;
		}

		// 已匹配
		return 1;

	}

	// 获取待匹配的记录
	public function get_waiting_match()
	{
		$timestamp = time();
		$wait_time_out = $this->anonymouschat_config['wait_time_out']; // 匹配超时
		$timestamp = $timestamp - $wait_time_out;
		$query = $this->db->select('id, openid, ghid, sex, tag_sex')
			->where('update_time >', $timestamp)->where('state', 2)
			->get('anonymouschat');
		echo $this->db->last_query();
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();
		return $row;
	}

	/**
	 * 获取匹配超时的记录
	 */
	public function get_match_failed_record()
	{
		$timestamp = time();
		$wait_time_out = $this->anonymouschat_config['wait_time_out']; // 匹配超时
		$timestamp = $timestamp - $wait_time_out;
		$query = $this->db->select('id, openid, ghid, state')
				->group_start()
					->where('update_time <', $timestamp)->where('state !=', 3)
				->group_end()
				->or_group_start()
					->where('state', 5)
				->group_end()
				->get('anonymouschat');
		// echo $this->db->last_query();
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();

		return $row;
	}

	/**
	 * 获取需要提醒聊天超时的记录
	 */
	public function get_need_reminding_record()
	{
		$timestamp = time();
		$reminding_time = $this->anonymouschat_config['reminding_time']; // 匹配超时
		$timestamp = $timestamp - $reminding_time;
		$query = $this->db->select('id, openid, ghid, state, update_time')
					->where('update_time <', $timestamp)->where('state =', 3)
				->get('anonymouschat');
		// echo $this->db->last_query();
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();

		return $row;
	}

	/**
	 * 获取聊天超时记录
	 */
	public function get_chat_time_out_record()
	{
		$timestamp = time();
		$chat_time_out = $this->anonymouschat_config['chat_time_out']; // 匹配超时
		$timestamp = $timestamp - $chat_time_out;
		$query = $this->db->select('id, openid, ghid, state, update_time')
					->where('update_time <', $timestamp)->where('state =', 3)
				->get('anonymouschat');
		// echo $this->db->last_query();
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();

		return $row;
	}

	public function del_useless_record($id_list)
	{
		// $id_list = implode(",", $id_list);
		$this->db->where_in('id', $id_list);
		$this->db->delete('anonymouschat');
	}
}
