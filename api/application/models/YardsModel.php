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


class YardsModel extends CI_Model {
	
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
	 * Get usage yard
	 *
	 * @param $where
	 * @type Array
	 */
	public function getYard($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'yards');
		return $query->row_array();
	}

	/**
	 * Get all yards
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllYards($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * Insert yard
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertYard($data) {
		return $this->db->insert($this->tblprefix.'yards', $data);
  }

  /**
	 * Update yard
	 *
	 * @param $data, $where
	 * @type Array, Array
	 */
	public function updateYard($data, $where) {
		$this->db->update($this->tblprefix."yards", $data, $where);
	}
}
