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


class DataUsageLimitsModel extends CI_Model {
	
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
	 * Get data usage limit
	 *
	 * @param $where
	 * @type Array
	 */
	public function getDataUsageLimit($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'data_usage_limits');
		return $query->row_array();
	}

	/**
	 * Get all data usage limits
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllDataUsageLimits($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert data usage limit
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertDataUsageLimit($data) {
		return $this->db->insert($this->tblprefix.'data_usage_limits', $data);
  }
	
	/**
	 * Update data usage limit
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateDataUsageLimit($data, $where) {
		$this->db->update($this->tblprefix."data_usage_limits", $data, $where);
	}
}
