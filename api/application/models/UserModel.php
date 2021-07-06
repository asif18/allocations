<?php
/**
 * Copyrights Allocations 2021. All rights reserved
 * 
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your 
 * business/personal perpose and not to sell or modify
 *
 * @description: Model contains DB operation about users
 * 
 * Developed by: Mohamed Asif
 * Date: 25/05/2021
 * Email: mohamedasif18@gmail.com
 */


class UserModel extends CI_Model {
	
	/**
	 * Var declarations
	 */
	private $tblprefix;
	
	/**
	 * Class Contructor
	 */
	public function __construct() {
		$this->tblprefix = $this->db->tblprefix;
	}
	
	/**
	 * Get user
	 *
	 * @param $where
	 * @type Array
	 */
	public function getUser($where) {
		$query = $this->db->select('id, parent_id, name, business_name, email, phone, username, password, role, type, status, 
			sms_gateway, settings')
			->where($where)
			->get($this->tblprefix.'users');
		return $query->row_array();
	}

	/**
	 * Get all users
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllUsers($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert user
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertUser($data) {
		$this->db->insert($this->tblprefix.'users', $data);
		return $this->db->insert_id();
	}

	/**
	 * Update User
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateUser($data, $where) {
		$this->db->update($this->tblprefix.'users', $data, $where);
		$user = $this->getWiFiUser($where);
		return $user['id'];
	}

	/**
	 * Get WiFi User
	 *
	 * @param $where
	 * @type Array
	 */
	public function getWiFiUser($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'wifi_users');
		return $query->row_array();
	}

	/**
	 * Insert wiFi user
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertWiFiUser($data) {
		$this->db->insert($this->tblprefix.'wifi_users', $data);
		return $this->db->insert_id();
	}

	/**
	 * Update WiFi User
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateWiFiUser($data, $where) {
		$this->db->update($this->tblprefix.'wifi_users', $data, $where);
	}
}
