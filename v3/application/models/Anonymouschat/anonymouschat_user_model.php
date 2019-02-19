<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anonymouschat_user_model extends MY_model {

	const role = 3;

	private $table_name = 'anonymouschat_config';

	function __construct()
	{
		parent::__construct();
		$this->load->model('Admin/user_model');
	}

	public function add_anonymouschat_user(string $username, string $password) : bool
	{
		$this->db->trans_begin();

		if ( ! $this->user_model->add_user($username, $password, self::role))
		{
			$this->db->trans_rollback();
			return false;
		}
		$userid = $this->db->insert_id();

		$timestamp = time();
		if ( ! $this->db->insert($this->table_name, [
					'userid' => $userid,
					'updatetime' => $timestamp,
					'createtime' => $timestamp
				]))
		{
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();

		return true;
	}

	public function anonymouschat_user_list()
	{
		return $this->user_model->get_list_role(self::role);
	}

	public function get_anonymouschat_config(int $userid) : ?array
	{
		$query = $this->db
				->where('userid =', $userid)
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

	public function edit_anonymouschat_config(
			int $userid,
			string $active_time_start,
			string $active_time_end,
			int $wait_time_out,
			int $chat_time_out,
			int $chat_superior_limit,
			int $share_number,
			string $subscription_account_app_id,
			string $subscription_account_app_secret,
			string $subscription_account_ghid,
			string $service_account_app_id,
			string $service_account_app_secret,
			string $service_account_ghid,
			string $invalid_time_text,
			string $subscribe_text
		) : bool
	{
		$timestamp = time();
		$data = [
			'active_time_start' => $active_time_start,
			'active_time_end' => $active_time_end,
			'wait_time_out' => $wait_time_out,
			'chat_time_out' => $chat_time_out,
			'chat_superior_limit' => $chat_superior_limit,
			'share_number' => $share_number,
			'subscription_account_app_id' => $subscription_account_app_id,
			'subscription_account_app_secret' => $subscription_account_app_secret,
			'subscription_account_ghid' => $subscription_account_ghid,
			'service_account_app_id' => $service_account_app_id,
			'service_account_app_secret' => $service_account_app_secret,
			'service_account_ghid' => $service_account_ghid,
			'invalid_time_text' => $invalid_time_text,
			'subscribe_text' => $subscribe_text,
			'updatetime' => $timestamp
		];

		return $this->db->where('userid', $userid)->set($data)->update($this->table_name);
	}

	public function del_anonymouschat_user(int $userid) : bool
	{
		$this->db->trans_begin();

		$this->load->model('Admin/user_model');
		if ($this->user_model->del_user($userid) === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		if ($this->db->where('userid', $userid)->delete($this->table_name) !== TRUE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$this->db->trans_commit();

		return TRUE;
	}
}
