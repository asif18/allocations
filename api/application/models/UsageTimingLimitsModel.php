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


class UsageTimingLimitsModel extends CI_Model {
	
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
	 * Get usage timing limit
	 *
	 * @param $where
	 * @type Array
	 */
	public function getUsageTimingLimit($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'usage_timing_limits');
		return $query->row_array();
	}

	/**
	 * Get all usage timing limits
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllUsageTimingLimits($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert usage timing limit
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertUsageTimingLimit($data) {
		return $this->db->insert($this->tblprefix.'usage_timing_limits', $data);
  }
  /**
	 * Update usage timing limit
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateUsageTimingLimit($data, $where) {
		$this->db->update($this->tblprefix."usage_timing_limits", $data, $where);
	}
}
