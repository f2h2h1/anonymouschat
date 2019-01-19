<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_config_model extends CI_Model {

	private $tabe_name = 'project_config';

	public function __construct()
	{
		$this->load->database();
	}

	public function set_config($key, $value)
	{
		$data = array(
			'config' => $key,
			'value' => $value,
			'update_time' => time()
		);
		$config = $this->get_config($key);
		if (is_array($config) && count($config) > 0)
		{
			$ret = $this->db->where('id', $config['id'])->set($data)->update($this->tabe_name);
		}
		else
		{
			$ret = $this->db->insert($this->tabe_name, $data);
		}

		return $ret;
	}

	public function get_config($key)
	{
		$query = $this->db->select('id, value')->where('config', $key)->limit(1)->get($this->tabe_name);
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

	public function del_config($key)
	{
		$this->db->where('config', $key);
		$this->db->delete($this->tabe_name);
	}
}