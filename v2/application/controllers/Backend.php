<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Backend extends CI_Controller {

	private $page_data;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->page_data = array();
		// 请求类型
		// get 请求
		defined('IS_GET')  OR define('IS_GET', strtolower($_SERVER['REQUEST_METHOD']) == 'get');
		// post 请求
		defined('IS_POST') OR define('IS_POST', strtolower($_SERVER['REQUEST_METHOD']) == 'post');
		// ajax 请求
		defined('IS_AJAX') OR define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

		if ($this->router->class != "Login")
		{
			$this->check_logined();
		}
		return;
	}

	private function check_logined()
	{
		// if (empty($this->session->admin))
		// {
		// 	redirect('Login/index');
		// }
		if ($this->session->admin == NULL)
		{
			redirect('Login/index');
		}
		return;
	}

	protected function _set_page_data($name, $value)
	{
		$this->page_data[$name] = $value;
	}

	protected function _view($template = NULL)
	{
		if (strpos($template, "/") === FALSE)
		{
			$template = $template === NULL ? $this->router->method : $template;
			$template = $this->router->class . "/" . $template;
		}
		$current_data['tpl'] = $this->load->view($template, $this->page_data, TRUE);
		$this->load->view('public/_layout', $current_data);
	}
}
