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


class WifiUsageModel extends CI_Model {
	
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
	 * Insert new wifi usage log
	 *
	 * @param $data
	 * @type Array
	 */
	public function insertWifiUsageLog($data) {
		$this->db->insert($this->tblprefix.'wifi_usage_log', $data);
	}
	
	/**
	 * Get All WiFi Usage Logs
	 *
	 * @param $query
	 * @type String
	 */
	public function getAllWifiUsageLogs($query) {
		$query = $this->db->query($query);
		return $query->result_array();
	}
}
