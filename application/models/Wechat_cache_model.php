<?php
class Wechat_cache_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function set_cache($key, $value, $expire_time)
	{
		$data = array(
			'key' => $key,
			'value' => $value,
			'expire_time' => $expire_time,
			'create_time' => time()
		);

		return $this->db->insert('wechat_cache', $data);
	}

	public function get_cache($key)
	{
		$query = $this->db->select('value, expire_time')->where('key', $key)->limit(1)->get('wechat_cache');
		$row = NULL;
		if ($query === FALSE)
		{
			return -1;
		}

		$row = $query->result_array();
		if (empty($row))
		{
			return 0;
		}

		return $row[0];
	}

	public function del_cache($key)
	{
		$this->db->where('key', $key);
		$this->db->delete('wechat_cache');
	}
}
