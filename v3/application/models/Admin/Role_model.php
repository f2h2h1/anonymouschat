<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends MY_Model {

	public $id;
	public $name;
	public $list;
	public $createtime;
	public $updatetime;

	private $table_name = 'role';
}
