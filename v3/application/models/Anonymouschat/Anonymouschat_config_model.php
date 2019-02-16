<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anonymouschat_config_model extends CI_Model {

	private $table_name = 'anonymouschat_config';

	public function set_config(int $userid, string $key, string $value) : bool
	{
		return $this->db->where('userid', $userid)->set([$key => $value])->update($this->table_name);
	}

	public function get_config(int $userid, string $key) : ?string
	{
		$query = $this->db
				->select($key)
				->where('userid', $userid)
				->limit(1)->get($this->table_name);
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

	public function del_config(int $userid, string $key) : bool
	{
		if ($this->db->where('userid', $userid)->delete($this->table_name) !== TRUE)
		{
			db_result_null(__FILE__, __LINE__, $this->db->error());
			return FALSE;
		}

		return TRUE;
	}

	public function get_userid(string $ghid) : ?string
	{
		$key = 'userid';
		$query = $this->db
				->select($key)
				->where('subscription_account_ghid', $ghid)
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

	public function get_config_all(string $ghid) : ?array
	{
		$query = $this->db
				->where('subscription_account_ghid', $ghid)
				->limit(1)
				->get($this->table_name);
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
}
