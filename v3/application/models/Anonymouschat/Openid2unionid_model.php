<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Openid2unionid_model extends CI_Model {

	private $table_name = 'openid2unionid';

	public function __construct()
	{
		$this->load->database();
	}

	public function get_unionid(string $openid) : ?string
	{
		$query = $this->db->where('openid =', $openid)
				->limit(1)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->first_row('array');
		if ($result === NULL or !isset($result['unionid']))
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $result['unionid'];
	}

	public function get_openid($unionid)
	{
		$query = $this->db->where('unionid =', $unionid)
				->limit(1)
				->get($this->table_name);
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

	public function add(string $openid, string $unionid) : bool
	{
		$data = array(
			'openid' => $openid,
			'unionid' => $unionid,
			'create_time' => time()
		);
		return $this->db->insert($this->table_name, $data);
	}
}
