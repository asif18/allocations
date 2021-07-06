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

class Host extends REST_Controller {

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
   * URL: /host/getHosts
   * Method: GET
   */
  public function getHosts_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $this->routerosapi->write('/ip/hotspot/host/getall');
    $profiles = $this->routerosapi->read();
    $this->disconnect();

    foreach ($profiles as $key => $value) {
      $profiles[$key]['bytes-in'] = $this->utility->formatBytes($profiles[$key]['bytes-in']);
      $profiles[$key]['bytes-out'] = $this->utility->formatBytes($profiles[$key]['bytes-out']);
    }

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $profiles));
    $this->response($output, $httpCode);
  }
}
