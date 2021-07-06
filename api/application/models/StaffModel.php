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

class StaffModel extends CI_Model {
	
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
	 * Get staff
	 *
	 * @param $where
	 * @type Array
	 */
	public function getStaff($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'users');
		return $query->row_array();
	}

	/**
	 * Get all staffs
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllStaffs($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert staff
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertStaff($data) {
		return $this->db->insert($this->tblprefix.'users', $data);
	}
	
	/**
	 * Update staff
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateStaff($data, $where) {
		$this->db->update($this->tblprefix."users", $data, $where);
	}
}
