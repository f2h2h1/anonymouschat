<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Openid2unionid_model extends CI_Model {

	private $table_name = 'openid2unionid';

	public function __construct()
	{
		$this->load->database();
	}

	public function get_unionid($openid)
	{
		$query = $this->db->where('openid =', $openid)
				->limit(1)
				->get($this->table_name);
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();
		if (empty($row[0]['unionid']))
		{
			return '';
		}
		return $row[0]['unionid'];
	}

	public function get_openid($unionid)
	{
		$query = $this->db->where('unionid =', $unionid)
				->limit(1)
				->get($this->table_name);
		if ($query === FALSE)
		{
			return -1;
		}
		$row = $query->result_array();
		if (empty($row[0]['openid']))
		{
			return '';
		}
		return $row[0]['openid'];
	}

	public function add($openid, $unionid)
	{
		$data = array(
			'openid' => $openid,
			'unionid' => $unionid,
			'create_time' => time()
		);
		return $this->db->insert($this->table_name, $data);
	}
}
