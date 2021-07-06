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


class AllocationsModel extends CI_Model {
	
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
	 * Get all allocation statuses
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllStatuses($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Get all allocations
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllAllocations($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert allocation
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertAllocation($data) {
		return $this->db->insert($this->tblprefix."allocations", $data);
  }

  /**
	 * Update allocation
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateAllocation($data, $where) {
		$this->db->update($this->tblprefix."allocations", $data, $where);
	}
}
