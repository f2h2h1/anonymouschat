<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Match_log_model extends CI_Model {

	private $table_name = 'match_log';
	private $today_timestamp;
	private $tomorrow_timestamp;

	public function __construct()
	{
		$this->load->database();
		$this->today_timestamp = strtotime(date("Y-m-d"), time());
		$this->tomorrow_timestamp = strtotime(date("Y-m-d", strtotime("+1 day")));
	}

	public function match_add(string $openid) : ?bool
	{
		$query = $this->db->where('openid =', $openid)
				->where('create_time >', $this->today_timestamp)
				->where('create_time <', $this->tomorrow_timestamp)
				->order_by('create_time', 'DESC')
				->limit(1)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}
		
		$result = $query->result_array();
		$timestamp = time();
		if (empty($result) or ! isset($result[0]['id']) or ! isset($result[0]['openid']))
		{
			// 插入一条新的记录
			$data = array(
				'openid' => $openid,
				'match_count' => 1,
				'share_count' => 0,
				'update_time' => $timestamp,
				'create_time' => $timestamp
			);
			return $this->db->insert($this->table_name, $data);
		}
		else
		{
			// 更新已有的记录
			$id = $result[0]['id'];
			$match_count = (int) $result[0]['match_count'];
			$data = array(
				'match_count' => ++$match_count,
				'update_time' => $timestamp,
			);
			return $this->db->set($data)->where('id', $id)->update($this->table_name);
		}
	}

	public function share_add($openid)
	{
		$query = $this->db->where('openid =', $openid)
				->where('create_time >', $this->today_timestamp)
				->where('create_time <', $this->tomorrow_timestamp)
				->order_by('create_time', 'DESC')
				->limit(1)
				->get($this->table_name);
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();

		$timestamp = time();
		if (empty($row) or ! isset($row[0]['id']) or ! isset($row[0]['openid']))
		{
			// 插入一条新的记录
			$data = array(
				'openid' => $openid,
				'match_count' => 0,
				'share_count' => 1,
				'update_time' => $timestamp,
				'create_time' => $timestamp
			);
			return $this->db->insert($this->table_name, $data);
		}
		else
		{
			// 更新已有的记录
			$id = $row[0]['id'];
			$share_count = (int) $row[0]['share_count'];
			$data = array(
				'share_count' => ++$share_count,
				'update_time' => $timestamp,
			);
			return $this->db->set($data)->where('id', $id)->update($this->table_name);
		}
	}

	public function match_count($openid)
	{
		$query = $this->db->where('openid =', $openid)
				->where('create_time >', $this->today_timestamp)
				->where('create_time <', $this->tomorrow_timestamp)
				->order_by('create_time', 'DESC')
				->limit(1)
				->get($this->table_name);
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();
		if (empty($row) or ! isset($row[0]['id']) or ! isset($row[0]['openid']))
		{
			return 0;
		}
		else
		{
			return $row[0]['match_count'];
		}
	}

	public function user_info(string $openid) : ?array
	{
		$query = $this->db->where('openid =', $openid)
				->where('create_time >', $this->today_timestamp)
				->where('create_time <', $this->tomorrow_timestamp)
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

	public function share_count($openid)
	{

	}
}
