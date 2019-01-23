<?php

class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		// 请求类型
		$REQUEST_METHOD = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
		// get 请求
		defined('IS_GET')  OR define('IS_GET', $REQUEST_METHOD === 'GET' ? true : false);
		// post 请求
		defined('IS_POST') OR define('IS_POST', $REQUEST_METHOD === 'POST' ? true : false);
		// ajax 请求
		defined('IS_AJAX') OR define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

		if (!($this->router->class === "Admin" && $this->router->method === "login"))
		{
			$this->checkLogined();
		}
		return;
	}

	private function checkLogined() : void
	{
		if ($this->session->role === NULL)
		{
			redirect('Admin/login');
		}
		return;
	}

	protected function _view($template, $data = NULL)
	{
		$base_url = str_replace($this->config->item('index_page'), "", $_SERVER['SCRIPT_NAME']);
		$template = $this->router->class . "/" . $template;
		if ($data === NULL)
		{
			$data = array('base_url' => $base_url);
		}
		else
		{
			$data['base_url'] = $base_url;
		}
		$current_data['tpl'] = $this->load->view($template, $data, TRUE);
		$current_data['base_url'] = $base_url;
		if ($this->session->role !== NULL)
		{
			$current_data['username'] = $this->session->username;
		}
		// $data['alert_msg'] = [
		// 	'type' => 'danger',
		// 	'msg' => 'test'
		// ];
		if (isset($this->session->alert_msg))
		{
			$current_data['alert_msg'] = $this->session->alert_msg;
			$this->session->unset_userdata('alert_msg');
		}
		$this->load->view('public/_layout', $current_data);
	}

	/**
	 * warning
	 * info
	 * success
	 */
	protected function set_alert_msg(string $msg, string $type = 'danger') : void
	{
		$this->session->set_userdata('alert_msg', ['type' => $type, 'msg' => $msg]);
	}

	protected function shortcut_error(string $view_name, string $msg, string $type = 'danger') : void
	{
		$this->set_alert_msg($msg, $type);
		$this->_view($view_name);
	}
}
