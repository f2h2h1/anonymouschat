<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Anonymouschat extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * 用户列表
	 */
	public function anonymouschat_user_list()
	{
		$this->load->model('Anonymouschat/anonymouschat_user_model');
		$model = $this->anonymouschat_user_model->anonymouschat_user_list();
		$this->_view($this->router->method, ['model' => $model]);
	}

	/**
	 * 新增用户
	 */
	public function add_anonymouschat_user()
	{
		if (IS_GET)
		{
			$this->_view($this->router->method);
		}
		else
		{
			$username = $this->input->post('username');
			$password = $this->input->post('password');

			if (empty($username) or empty($password))
			{
				$this->shortcut_error($this->router->method, '关键参数为空');
			}

			$this->load->model('Anonymouschat/anonymouschat_user_model');
			if ( ! $this->anonymouschat_user_model->add_anonymouschat_user($username, $password))
			{
				$this->shortcut_error($this->router->method, '新增失败');
			}

			$this->set_alert_msg('新增成功', 'success');
			// $this->_view($this->router->method);
			redirect('Anonymouschat/anonymouschat_user_list');
		}
	}

	/**
	 * 编辑用户
	 */
	public function edit_anonymouschat_user()
	{
		$userid = (int)$this->uri->segment(3);
		if ( ! ($this->session->userid === $userid or ($this->session->role === 1 or $this->session->role === 2)))
		{
			echo '<script>alert("没有权限");history.back();</script>';
			return;
		}

		$this->load->model('Anonymouschat/anonymouschat_user_model');
		if (IS_POST)
		{
			$active_time_start = $this->input->post('active_time_start');
			$active_time_end = $this->input->post('active_time_end');
			$wait_time_out = (int)$this->input->post('wait_time_out');
			$chat_time_out = (int)$this->input->post('chat_time_out');
			$chat_superior_limit = (int)$this->input->post('chat_superior_limit');
			$share_number = (int)$this->input->post('share_number');
			$subscription_account_app_id = $this->input->post('subscription_account_app_id');
			$subscription_account_app_secret = $this->input->post('subscription_account_app_secret');
			$subscription_account_ghid = $this->input->post('subscription_account_ghid');
			$service_account_app_id = $this->input->post('service_account_app_id');
			$service_account_app_secret = $this->input->post('service_account_app_secret');
			$service_account_ghid = $this->input->post('service_account_ghid');

			$invalid_time_text = (string)$this->input->post('invalid_time_text');
			$subscribe_text = (string)$this->input->post('subscribe_text');

			$wait_time_out = 300;

			$service_account_app_id = $subscription_account_app_id;
			$service_account_app_secret = $subscription_account_app_secret;
			$service_account_ghid = $subscription_account_ghid;

			if ( ! $this->anonymouschat_user_model->edit_anonymouschat_config(
						$userid,
						$active_time_start,
						$active_time_end,
						$wait_time_out,
						$chat_time_out,
						$chat_superior_limit,
						$share_number,
						$subscription_account_app_id,
						$subscription_account_app_secret,
						$subscription_account_ghid,
						$service_account_app_id,
						$service_account_app_secret,
						$service_account_ghid,
						$invalid_time_text,
						$subscribe_text
					)
				)
			{
				$this->set_alert_msg('编辑失败');
				redirect('Anonymouschat/edit_anonymouschat_user');
			}

			$this->set_alert_msg('编辑成功', 'success');
		}

		$model = $this->anonymouschat_user_model->get_anonymouschat_config($userid);
		if ($model === NULL)
		{
			$this->set_alert_msg('获取用户信息失败');
			redirect('Anonymouschat/anonymouschat_user_list');
		}

		$this->_view($this->router->method, ['model' => $model]);
	}

	/**
	 * 删除用户
	 */
	public function del_anonymouschat_user()
	{
		$id = (int)$this->input->post('id');
		$this->load->model('Anonymouschat/anonymouschat_user_model');
		$this->anonymouschat_user_model->del_anonymouschat_user($id);
		redirect('Anonymouschat/anonymouschat_user_list');
	}

	/**
	 * 用户详细
	 */
	public function anonymouschat_user_detail(){}

	/**
	 * 用户设置
	 */

	 /**
	  * 说明
	  */
	public function readme()
	{
		$this->_view($this->router->method);
	}
}
