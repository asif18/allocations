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


class DestinationsModel extends CI_Model {
	
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
	 * Get destination
	 *
	 * @param $where
	 * @type Array
	 */
	public function getDestination($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'destinations');
		return $query->row_array();
	}

	/**
	 * Get all destinations
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllDestinations($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert destination
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertDestination($data) {
		return $this->db->insert($this->tblprefix.'destinations', $data);
  }

  /**
	 * Update destination
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateDestination($data, $where) {
		$this->db->update($this->tblprefix."destinations", $data, $where);
	}
}
