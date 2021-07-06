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


class SmsModel extends CI_Model {
	
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
	 * Get SMS Template
	 *
	 * @param $where
	 * @type Array
	 */
	public function getSmsTemplate($where) {
		$query = $this->db->select('*')
			->where($where)
			->get($this->tblprefix.'sms_templates');
		return $query->row_array();
	}

	/**
	 * Log SMS Count
	 */
	public function logSmsCount($clientId, $smsFor, $smsCount, $smsVendor) {

		$query = $this->db->select('id')
			->where(array(
				'client_id' => $clientId,
				'sms_for' => $smsFor,
				'sms_vendor' => $smsVendor,
				'date' => date('Y-m-d', strtotime($this->utility->timenow()))
			))
			->get($this->tblprefix.'sms_counter');
		$row = $query->row_array();

		if (is_array($row) && count($row) > 0) {
			$this->db->set('sms_count', 'sms_count+'.$smsCount, FALSE);
			$this->db->where('id', $row['id']);
			$this->db->update($this->tblprefix.'sms_counter');
		} else {
			$data = array(
				'client_id' => $clientId,
				'sms_for' => $smsFor, 
				'sms_count' => $smsCount,
				'sms_vendor' => $smsVendor,
				'date' => date('Y-m-d', strtotime($this->utility->timenow()))
			);
			$this->db->insert($this->tblprefix.'sms_counter', $data);
		}
	}
}
