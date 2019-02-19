<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anonymouschat_model extends CI_Model {

	private $table_name = 'anonymouschat';

	public function __construct()
	{
		$this->load->database();
		// $anoymouschat_config = $this->config->load('anonymouschat');
		$this->load->model('anonymouschat_config_model', 'anonymouschat_config');
	}

	/**
	 * 判断是否已进入聊天模式
	 */
	public function is_joinchat($openid) : bool
	{
		$result = $this->get_openid_info($openid);
		// 用户未进入聊天模式
		if ($result === NULL)
		{
			return FALSE;
		}

		// 用户已进入聊天模式
		return TRUE;
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

		return $this->db->insert($this->table_name, $data);
	}

	/**
	 * 获取用户信息
	 */
	public function get_openid_info(string $openid) : ?array
	{
		$query = $this->db
				->where('openid =', $openid)
				->where('state  !=', 5)
				->order_by('create_time', 'DESC')
				->limit(1)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->first_row('array');
		if ($result === NULL)
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $result;
	}

	/**
	 * 设置用户性别
	 */
	public function set_sex(int $id, int $sex) : bool
	{
		$timestamp = time();
		$data = array(
			'sex' => $sex,
			'state' => 1,
			'update_time' => $timestamp
		);

		return $this->db->set($data)->where('id', $id)->update($this->table_name);
	}

	/**
	 * 设置目标性别
	 */
	public function set_tag_sex(int $id, int $sex) : bool
	{
		$timestamp = time();
		$data = array(
			'tag_sex' => $sex,
			'state' => 2,
			'update_time' => $timestamp
		);

		return $this->db->set($data)->where('id', $id)->update($this->table_name);
	}

	/**
	 * 获取目标的openid
	 */
	public function get_openid($id) : ?string
	{
		$query = $this->db->select('openid')->where('id', $id)->limit(1)->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->first_row('array');
		if ($result === NULL or !isset($result['openid']))
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $result['openid'];
	}

	/**
	 * 退出聊天
	 */
	public function exit_chat(array $user_info) : bool
	{
		$id = $user_info['id'];
		$tag_id = $user_info['tag_id'];

		$timestamp = time();

		$data = array(
			'state' => 5,
			'update_time' => $timestamp
		);

		$this->db->where('id', $id);

		if ( ! empty($tag_id))
		{
			$this->db->or_where('id', $tag_id);
		}

		return $this->db->set($data)->update($this->table_name);
	}

	// 匹配
	public function match(array $user_info) : ?array
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
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}


		$result = $query->first_row('array');
		$sql = $this->db->last_query();

		// 匹配失败
		if ($result === NULL)
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		// 匹配成功
		$timestamp = time();
		$tag_id = $result['id'];
		$tag_openid = $result['openid'];

		$data = array(
			'state' => 3,
			'update_time' => $timestamp
		);
		$data['tag_id'] = $tag_id;
		$ret1 = $this->db->where('id', $id)->set($data)->update($this->table_name);

		$data['tag_id'] = $id;
		$ret2 = $this->db->where('id', $tag_id)->set($data)->update($this->table_name);

		if ( ! $ret1 or ! $ret2)
		{
			return NULL;
		}
		else
		{
			return array($openid, $tag_openid);
		}
	}

	// 判断是否已经匹配成功
	public function is_match($id)
	{
		$query = $this->db->select('state')
				->where('id =', $id)
				->where('state =', 3)->limit(1)
				->get($this->table_name);
				$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();

		// 未匹配
		if (empty($row) or ! isset($row[0]['state']) or $row[0]['state'] != 3)
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
		$wait_time_out = $this->anonymouschat_config->get_config('wait_time_out'); // 匹配超时
		$timestamp = $timestamp - $wait_time_out;
		$query = $this->db->select('id, openid, ghid, sex, tag_sex')
			->where('update_time >', $timestamp)->where('state', 2)
			->get($this->table_name);
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
	public function get_match_failed_record(string $gh_id, int $wait_time_out) : ?array
	{
		$timestamp = time();
		$timestamp = $timestamp - $wait_time_out;
		$query = $this->db->select('id, openid, ghid, state')
				->group_start()
					->where('ghid =', $gh_id)
					->where('update_time <', $timestamp)->where('state !=', 3)
				->group_end()
				->or_group_start()
					->where('state', 5)
				->group_end()
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->result_array();

		return $result;
	}

	/**
	 * 获取需要提醒聊天超时的记录
	 */
	public function get_need_reminding_record(string $gh_id, int $chat_time_out) : ?array
	{
		$timestamp = time();
		$reminding_time = $chat_time_out - 120;
		$timestamp = $timestamp - $reminding_time;
		$query = $this->db->select('id, openid, ghid, state, update_time')
					->where('ghid =', $gh_id)
					->where('update_time <', $timestamp)->where('state =', 3)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->result_array();

		return $result;
	}

	/**
	 * 获取聊天超时记录
	 */
	public function get_chat_time_out_record(string $gh_id, int $chat_time_out) : ?array
	{
		$timestamp = time();
		$timestamp = $timestamp - $chat_time_out;
		$query = $this->db->select('id, openid, ghid, state, update_time')
				->where('ghid =', $gh_id)
				->where('update_time <', $timestamp)->where('state =', 3)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->result_array();

		return $result;
	}

	public function del_useless_record($id_list)
	{
		// $id_list = implode(",", $id_list);
		$this->db->where_in('id', $id_list);
		$this->db->delete($this->table_name);
	}
}
