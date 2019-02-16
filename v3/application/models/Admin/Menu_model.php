<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model {

	public $id;
	public $name;
	public $sort;
	public $pid;
	public $hide;
	public $createtime;
	public $updatetime;

	private $table_name = 'menu';

	public function __construct()
	{
		$this->load->database();
	}
}
