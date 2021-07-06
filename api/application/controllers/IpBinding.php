<?php
/**
 * Copyrights Allocations 2021. All rights reserved
 * 
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your 
 * business/personal purpose and not to sell or modify
 * 
 * Developed by: Mohamed Asif
 * Date: 25/05/2021
 * Email: mohamedasif18@gmail.com
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class IpBinding extends REST_Controller {

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }
  }

  /**
	 * Connect to Instance
	 *
	 * @paraam $ip, $username, $password
	 * @type IP, String, $String
	 */
	private function connect($ip, $username, $password) {
    log_message('info', 'instance - connecting to - '.$ip);
		$this->routerosapi->debug = (ENVIRONMENT === 'development');
		return $this->routerosapi->connect($ip, $username, $password);
  }

  /**
	 * Disconnect from Instance
	 *
	 * @param null
	 */
	private function disconnect() {
		$this->routerosapi->disconnect();
  }
  
  /**
   * URL: /ipbinding/saveIpBinding
   * Method: POST
   */
  public function saveIpBinding_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('macAddress*', 'address', 'toAddress', 'type*', 'comment*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    
    $status = $this->routerosapi->comm('/ip/hotspot/ip-binding/add', array(
      'mac-address' => $input['macAddress'],
      'address' => ($input['address'] ? $input['address'] : '0.0.0.0'),
      'to-address'=> ($input['toAddress'] ? $input['toAddress'] : '0.0.0.0'),
      'type' => $input['type'],
      'comment' => $input['comment']
    ));

    if ($instance['mik_dns_ip'] !== '') {
      $dnsPort = '';
      if ($instance['mik_dns_port']) {
        $dnsPort = ':' . $instance['mik_dns_port'];
      }
      file_get_contents('http://' . $instance['mik_dns_ip'] . $dnsPort . '/api/user/add/' . $input['comment'] . '/122.0.0.0');
    }

    log_message('info', 'saveIpBinding_post new ip binding added - '.$input['macAddress']);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Added successfully' : 'error while adding');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /ipbinding/removeIpBinding
   * Method: POST
   */
  public function removeIpBinding_get($id = null) {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $id = base64_decode($id);
    
    if (!is_string($id)) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    
    $this->routerosapi->write('/ip/hotspot/ip-binding/remove', false);
    $this->routerosapi->write('=.id='.$id);
    $status = is_array($this->routerosapi->read());

    log_message('info', 'removeIpBinding_get ip binding removed- '.$id);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Removed successfully' : 'error while removing');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /ipbinding/updateStatus
   * Method: GET
   */
  public function updateStatus_get($id = null, $action) {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $id = base64_decode($id);
    
    if (!is_string($id)) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    
    $this->routerosapi->write('/ip/hotspot/ip-binding/'.$action, false);
    $this->routerosapi->write('=.id='.$id);
    $status = is_array($this->routerosapi->read());

    log_message('info', 'updateStatus_get ip binding removed- '.$id);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Updated successfully' : 'error while updating');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /ipbinding/getIpBindings
   * Method: GET
   */
  public function getIpBindings_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $this->routerosapi->write('/ip/hotspot/ip-binding/getall');
    $profiles = $this->routerosapi->read();
    $this->disconnect();

    foreach ($profiles as $key => $value) {
      $profiles[$key]['disabled'] = $profiles[$key]['disabled'] != 'false';
    }

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $profiles));
    $this->response($output, $httpCode);
  }
}
