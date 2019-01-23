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

	public function login(string $username, string $password)
	{
		$query = $this->db
				->select('id, username, role')
				->where('username =', $username)
				->where('password =', $password)
				->limit(1)->get($this->table_name);
		if ($query === FALSE)
		{
			return -1;
		}
		// return $query->result_array();
		return $query->first_row('array');
	}

	public function changepassword(int $userid, string $old_password, string $new_password)
	{
		$query = $this->db
				->select('password')
				->where('id =', $userid)
				->where('password =', $password)
				->limit(1)->get($this->table_name);
		if ($query === FALSE)
		{
			return -1;
		}
		// return $query->result_array();
		return $query->first_row('array');
	}

	public function get_password(int $userid) : ?string
	{
		$query = $this->db
				->select('password')
				->where('id =', $userid)
				->limit(1)->get($this->table_name);
		if ($query === FALSE)
		{
			return NULL;
		}

		$row = $query->first_row('array');
		if (count($row) == 0)
		{
			return NULL;
		}

		return $row['password'];
	}

	public function change_password(int $userid, string $new_password) : bool
	{
		return $this->db->where('id', $userid)->set(['password' => $new_password])->update($this->table_name);
	}

	public function get_list() : array
	{
		$query = $this->db
				->select('id,username,role,createtime,updatetime')
				->get($this->table_name);
		return $query->result_array();
	}
}
