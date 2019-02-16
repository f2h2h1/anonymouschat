<?php
declare(strict_types=1);
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

	# #region 通用

		/**
		 * 主页
		 */
		public function index() : void
		{
			$this->_view($this->router->method);
		}

		/**
		 * 登录
		 */
		public function login() : CI_Loader
		{
			if (IS_GET)
			{
				if ($this->session->role === NULL)
				{
					return $this->_view('login');
				}
				else 
				{
					redirect('Admin/index');
				}
			}
			else
			{
				$username = $this->input->post('username');
				$password = $this->input->post('password');

				if (empty($username) or empty($password))
				{
					$this->set_alert_msg('登录失败，账号或密码错误');
					return $this->_view('login');
				}

				$this->load->model('Admin/user_model');
				$user = $this->user_model->login($username, $password);
				if ($user === NULL)
				{
					$this->set_alert_msg('登录失败，账号或密码错误');
					return $this->_view('login');
				}

				$this->session->set_userdata('role', (int)$user['role']);
				$this->session->set_userdata('username', $user['username']);
				$this->session->set_userdata('userid', (int)$user['id']);

				redirect('Admin/index');
			}
		}

		/**
		 * 注销
		 */
		public function logout() : void
		{
			session_destroy();
			redirect('Admin/login');
		}

		/**
		 * 修改密码
		 */
		public function changepassword() : void
		{
			if (IS_GET)
			{
				$this->_view($this->router->method);
			}
			else
			{
				$old_password = $this->input->post('old_password');
				$new_password = $this->input->post('new_password');
				$new_repassword = $this->input->post('new_repassword');

				if ($new_password != $new_repassword)
				{
					$this->shortcut_error($this->router->method, '新密码不一致');
					return;
				}

				$this->load->model('Admin/user_model');
				$password = $this->user_model->get_password($this->session->userid);
				if ($password === NULL)
				{
					$this->shortcut_error($this->router->method, '修改失败');
					return;
				}

				if ($password != $old_password)
				{
					$this->shortcut_error($this->router->method, '旧密码错误');
					return;
				}

				if ( ! $this->user_model->change_password($this->session->userid, $new_password))
				{
					$this->shortcut_error($this->router->method, '修改失败.');
					return;
				}

				$this->set_alert_msg('密码修改成功', 'success');
				redirect('Admin/index');
			}
		}

	# #endregion 通用

	# #region 用户

		/**
		 * 用户列表
		 */
		public function userlist() : void
		{
			$this->load->model('Admin/user_model');
			$model = $this->user_model->get_list();
			$this->_view($this->router->method, ['model' => $model]);
		}

		/**
		 * 用户详情
		 */
		public function userdetail() : void
		{
			redirect('Admin/userlist');
		}

		/**
		 * 新建用户
		 */
		public function adduser() : void
		{
		}

		/**
		 * 删除用户
		 */
		public function deluser() : void
		{
			$id = (int)$this->input->post('id');
			$this->load->model('Admin/user_model');
			$this->user_model->del_user($id);
			redirect('Admin/userlist');
		}

		/**
		 * 修改用户
		 */
		public function edituser() : void
		{
		}

	# #endregion 用户

	# #region 角色

		/**
		 * 角色列表
		 */
		public function rolelist() : void
		{
		}

		/**
		 * 角色详情
		 */
		public function roledetail() : void
		{
		}

		/**
		 * 新建角色
		 */
		public function addrole() : void
		{
		}

		/**
		 * 删除角色
		 */
		public function delrole() : void
		{}

		/**
		 * 修改角色
		 */
		public function editrole() : void
		{}

	# #endregion 角色

	# #region 路由

		/**
		 * 路由列表
		 */
		public function routelist() : void
		{
		}

		/**
		 * 路由详情
		 */
		public function routedetail() : void
		{
		}

		/**
		 * 新建路由
		 */
		public function addroute() : void
		{
		}

		/**
		 * 删除路由
		 */
		public function delroute() : void
		{}

		/**
		 * 修改路由
		 */
		public function editroute() : void
		{}

	# #endregion 路由

	# #region 菜单

		/**
		 * 菜单列表
		 */
		public function menulist() : void
		{
		}

		/**
		 * 菜单详情
		 */
		public function menudetail() : void
		{
		}

		/**
		 * 新建菜单
		 */
		public function addmenu() : void
		{
		}

		/**
		 * 删除菜单
		 */
		public function delmenu() : void
		{}

		/**
		 * 修改菜单
		 */
		public function editmenu() : void
		{}

	# #endregion 菜单
}
