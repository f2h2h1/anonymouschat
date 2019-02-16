<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_model {

	public $id;
	public $username;
	public $password;
	public $role;
	public $createtime;
	public $updatetime;

	private $table_name = 'user';

	public function login(string $username, string $password) : ?array
	{
		$query = $this->db
				->select('id, username, role')
				->where('username =', $username)
				->where('password =', $password)
				->limit(1)->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->first_row('array');
		if ($result === NULL)
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $result;
	}

	public function get_password(int $userid) : ?string
	{
		$key = 'password';
		$query = $this->db
				->select($key)
				->where('id =', $userid)
				->limit(1)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		$result = $query->first_row('array');
		if ($result === NULL or !isset($result[$key]))
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $result[$key];
	}

	public function change_password(int $userid, string $new_password) : bool
	{
		return $this->db->where('id', $userid)->set(['password' => $new_password])->update($this->table_name);
	}

	public function get_list() : ?array
	{
		$query = $this->db
				->select('id,username,role,createtime,updatetime')
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $query->result_array();
	}

	public function del_user(int $userid) : bool
	{
		if ($this->db->where('id', $userid)->delete($this->table_name) !== TRUE)
		{
			return FALSE;
		}

		return TRUE;
	}

	public function add_user(string $username, string $password, int $role) : bool
	{
		$timestamp = time();
		$data = array(
			'username' => $username,
			'password' => $password,
			'role' => $role,
			'updatetime' => $timestamp,
			'createtime' => $timestamp
		);

		return $this->db->insert($this->table_name, $data);
	}

	public function get_list_role($role) : ?array
	{
		$query = $this->db
				->select('id,username,role,createtime,updatetime')
				->where('role =', $role)
				->get($this->table_name);
		if ( ! $query instanceof CI_DB_result)
		{
			failed_query(__FILE__, __LINE__, $this->db->error());
			return NULL;
		}

		return $query->result_array();
	}
}
