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

class AccessPointManager extends REST_Controller {

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
   * URL: /profile/saveAccessPointManager
   * Method: POST
   */
  public function saveAccessPointManager_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('host*', 'comment*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    
    $status = $this->routerosapi->comm('/tool/netwatch/add', array(
      'host' => $input['host'],
      'comment' => $input['comment']
    ));

    log_message('info', 'saveAccessPointManager_post APM added - '.$input['host']);
    $output = array(
      'status' => $status,
      'message' => $status ? 'APM added successfully' : 'error while adding APM');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /accessPointManager/removeAccessPointManager
   * Method: GET
   */
  public function removeAccessPointManager_get($id = null) {
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
    
    $this->routerosapi->write('/tool/netwatch/remove', false);
    $this->routerosapi->write('=.id='.$id);
    $status = is_array($this->routerosapi->read());

    log_message('info', 'removeAccessPointManager_get APM removed- '.$id);
    $output = array(
      'status' => $status,
      'message' => $status ? 'APM removed successfully' : 'error while removing APM');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /accessPointManager/getAccessPointManagers
   * Method: GET
   */
  public function getAccessPointManagers_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $this->routerosapi->write('/tool/netwatch/getall');
    $accessPointManagers = $this->routerosapi->read();
    $this->disconnect();
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $accessPointManagers));
    $this->response($output, $httpCode);
  }
}
