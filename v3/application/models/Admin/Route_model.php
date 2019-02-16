<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Route_model extends CI_Model {

	public $id;
	public $name;

	private $table_name = 'route';

	public function __construct()
	{
		$this->load->database();
	}
}
