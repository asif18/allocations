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


class InstanceModel extends CI_Model {
	
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
	 * Get instance
	 *
	 * @param $where
	 * @type Array
	 */
	public function getInstance($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'instances');
		return $query->row_array();
	}

	/**
	 * Get all instances
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllInstances($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert instance
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertInstance($data) {
		$this->db->insert($this->tblprefix.'instances', $data);
		return $this->db->insert_id();
	}

	/**
	 * Update Instance
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateInstance($data, $where) {
		$this->db->update($this->tblprefix."instances", $data, $where);
	}
}
