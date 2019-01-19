<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Backend.php';

class Login extends Backend {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if (IS_GET)
		{
			
			if ($this->session->admin == NULL)
			{
				$this->load->view('login');
			}
			else 
			{
				redirect('Admin/setup');
			}
		}
		else
		{
			$username = $this->input->post('username');
			$password = $this->input->post('password');
			if ($username === "admin" && $password === "123")
			{
				$this->session->set_userdata('admin', 1);
				redirect('Admin/setup');
			}
			else
			{
				$this->load->view('login');
			}
		}
	}
}
