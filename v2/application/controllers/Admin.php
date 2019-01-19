<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'Backend.php';

class Admin extends Backend {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('anonymouschat_config_model', 'anonymouschat_config');
		$this->load->model('project_config_model', 'project_config');
	}

	public function index()
	{
		$this->_view("index");
	}

	public function setup()
	{
		if (IS_GET)
		{
			// $anonymouschat_config = $this->anonymouschat_config->get_config('anonymouschat_config');
			// $anonymouschat_config = $anonymouschat_config['value'];
			// // var_dump($anonymouschat_config);
			// $anonymouschat_config = json_decode($anonymouschat_config, true);
			// var_dump($anonymouschat_config);
			$this->_set_page_data('active_time_start', $this->anonymouschat_config->get_config('active_time_start'));
			$this->_set_page_data('active_time_end', $this->anonymouschat_config->get_config('active_time_end'));
			$this->_set_page_data('wait_time_out', $this->anonymouschat_config->get_config('wait_time_out'));
			$this->_set_page_data('chat_time_out', $this->anonymouschat_config->get_config('chat_time_out'));
			$this->_set_page_data('chat_superior_limit', $this->anonymouschat_config->get_config('chat_superior_limit'));
			$this->_set_page_data('share_number', $this->anonymouschat_config->get_config('share_number'));
			$this->_set_page_data('subscription_account_app_id', $this->anonymouschat_config->get_config('subscription_account_app_id'));
			$this->_set_page_data('subscription_account_app_secret', $this->anonymouschat_config->get_config('subscription_account_app_secret'));
			$this->_set_page_data('subscription_account_ghid', $this->anonymouschat_config->get_config('subscription_account_ghid'));
			$this->_set_page_data('service_account_app_id', $this->anonymouschat_config->get_config('service_account_app_id'));
			$this->_set_page_data('service_account_app_secret', $this->anonymouschat_config->get_config('service_account_app_secret'));
			$this->_set_page_data('service_account_ghid', $this->anonymouschat_config->get_config('service_account_ghid'));
			$this->_view();
		}
		else
		{
			// var_dump($this->input->post());
			// 匿名聊天设置
			$active_time_start   = $this->input->post('active_time_start');
			$active_time_end     = $this->input->post('active_time_end');
			$wait_time_out       = $this->input->post('wait_time_out');
			$chat_time_out       = $this->input->post('chat_time_out');
			$chat_superior_limit = $this->input->post('chat_superior_limit');
			$share_number        = $this->input->post('share_number');
			// 公众号设置
			$subscription_account_app_id     = $this->input->post('subscription_account_app_id');
			$subscription_account_app_secret = $this->input->post('subscription_account_app_secret');
			$subscription_account_ghid       = $this->input->post('subscription_account_ghid');
			$service_account_app_id          = $this->input->post('service_account_app_id');
			$service_account_app_secret      = $this->input->post('service_account_app_secret');
			$service_account_ghid            = $this->input->post('service_account_ghid');

			// $active_time_start .= ":00";
			// $active_time_end .= ":00";
			$temp = explode(":", $active_time_start);
			if ($temp[0] == "00")
			{
				$active_time_start = "24:".$temp[1];
			}
			$temp = explode(":", $active_time_end);
			if ($temp[0] == "00")
			{
				$active_time_end = "24:".$temp[1];
			}

			// $wait_time_out = $wait_time_out * 60;
			// $chat_time_out = $chat_time_out * 60;

			// var_dump($active_time_start);
			// var_dump($active_time_end);
			// var_dump($wait_time_out);
			// var_dump($chat_time_out);
			// var_dump($chat_superior_limit);
			// var_dump($share_number);
			// var_dump($subscription_account_app_id);
			// var_dump($subscription_account_app_secret);
			// var_dump($subscription_account_ghid);
			// var_dump($service_account_app_id);
			// var_dump($service_account_app_secret);
			// var_dump($service_account_ghid);

			$anonymouschat_config = array(
				'active_time_start'               => $active_time_start,
				'active_time_end'                 => $active_time_end,
				'wait_time_out'                   => $wait_time_out,
				'chat_time_out'                   => $chat_time_out,
				'chat_superior_limit'             => $chat_superior_limit,
				'share_number'                    => $share_number,
				'subscription_account_app_id'     => $subscription_account_app_id,
				'subscription_account_app_secret' => $subscription_account_app_secret,
				'subscription_account_ghid'       => $subscription_account_ghid,
				'service_account_app_id'          => $service_account_app_id,
				'service_account_app_secret'      => $service_account_app_secret,
				'service_account_ghid'            => $service_account_ghid,
			);

			$anonymouschat_config = json_encode($anonymouschat_config);
			// echo $anonymouschat_config;

			$this->project_config->set_config('anonymouschat_config', $anonymouschat_config);
			// var_dump($this->project_config->get_config('anonymouschat_config'));
			redirect('Admin/setup');
		}
		
	}

	public function test1()
	{
		// $this->input->get();
		// $this->input->get('lastname');
		// log_message('error', 'Some variable was correctly set');
		// var_dump($this->get_this_class_methods(__class__));
		// var_dump($this->getThisClassPublicMethods(__class__));
		// $this->_set_page_data("qwe", "123");
		// $this->_view("test1");
		// $this->_view();
		$this->load->library('wechat_lib');
		$this->wechat_lib->test();
		echo $this->anonymouschat_config->get_config('service_account_ghid');

		
	}

	public function test2()
	{
		$this->load->database();
		$this->db->where_in('id', 5);
		var_dump($this->db->delete('match_log'));
		var_dump($this->db->affected_rows());

		// delete insert update 这三个函数成功都是返回true 失败都是返回false
		// 返回受影响行数 delete insert update
		// $this->db->affected_rows();
	}
}
