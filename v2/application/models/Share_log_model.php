<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Share_log_model extends CI_Model {

	private $table_name = 'share_log';

	public function __construct()
	{
		$this->load->database();
	}

	public function add($unionid_form, $unionid_to)
	{
		// 插入一条新的记录
		$data = array(
			'unionid_form' => $unionid_form,
			'unionid_to' => $unionid_to,
			'create_time' => time()
		);
		return $this->db->insert($this->table_name, $data);
	}
}
