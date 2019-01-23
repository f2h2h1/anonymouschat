<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

	public $id;
	public $name;
	public $list;
	public $createtime;
	public $updatetime;

	private $table_name = 'role';

	public function __construct()
	{
		$this->load->database();
	}
}
