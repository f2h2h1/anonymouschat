<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Project_config_model.php';

class Anonymouschat_config_model extends Project_config_model {

	private $config;
	private $config_name;

	public function __construct()
	{
		parent::__construct();
		$this->config_name = 'anonymouschat_config';
		$config_str = parent::get_config($this->config_name);
		// var_dump($config_str);
		$config_arr = json_decode($config_str['value'], TRUE);
		if ($config_arr === NULL)
		{
			$err = array('config_str' => $config_str, 'json_err' => json_last_error_msg());
			fatal_error("get {$this->config_name} failed", $err);
		}
		$this->config = $config_arr;
	}

	public function set_config($key, $value)
	{
		if (isset($this->config[$key]))
		{
			$this->config[$key] = $value;
			return parent::set_config($this->config_name, $this->config);
		}
		return 0;
	}

	public function get_config($key)
	{
		return $this->config[$key];
	}

	public function del_config($key)
	{
		$value = '';
		return $this->set_config($key, $value);
	}
}
