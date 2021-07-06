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

class Room extends REST_Controller {

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }
    $this->load->model('InstanceModel');
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
   * URL: /room/addRoom
   * Method: POST
   */
  public function addRoom_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('profile*', 'username*', 'password*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $cmdResponse = $this->routerosapi->comm('/ip/hotspot/user/add', array(
      'name'      => $input['username'],
      'password'  => base64_decode($input['password']),
      'profile'   => $input['profile']
    ));

    if ($instance['mik_dns_ip'] !== '') {
      $dnsPort = '';
      if ($instance['mik_dns_port']) {
        $dnsPort = ':' . $instance['mik_dns_port'];
      }
      file_get_contents('http://' . $instance['mik_dns_ip'] . $dnsPort . '/api/user/add/' . $input['username'] . '/122.0.0.0');
    }
    
    $this->disconnect();

    if (is_array($cmdResponse)) {
      $status = false;
    } else {
      $status = true;
      log_message('info', 'addRoom_post user/room created - '.$input['username']);
    }
    
    $output = array(
      'status' => $status,
      'message' => $status ? 'saved successfully' : $cmdResponse['!trap'][0]['message']);
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /room/removeRoom
   * Method: POST
   */
  public function removeRoom_get($id = null) {
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
    $this->routerosapi->write("/ip/hotspot/user/remove", false);
    $this->routerosapi->write("=.id=".$id);
    $status = is_array($this->routerosapi->read());

    log_message('info', 'removeRoom_get user/room removed - '.$id);
    $output = array(
      'status' => $status,
      'message' => $status ? 'removed successfully' : 'error while removing');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /room/checkOutRoom
   * Method: POST
   */
  public function checkOutRoom_get($id = null) {
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
    
    $this->routerosapi->write('/ip/hotspot/active/remove', false);
    $this->routerosapi->write('=.id='.$id);
    $this->routerosapi->read();

    $this->routerosapi->write('/ip/hotspot/user/disable', false);
    $this->routerosapi->write('=.id='.$id);
    $status = is_array($this->routerosapi->read());

    log_message('info', 'checkOutRoom_get user/room checked out - '.$id);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Checked out successfully' : 'error while checkout');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /room/getRooms
   * Method: GET
   */
  public function getRooms_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $this->routerosapi->write('/ip/hotspot/user/getall');
    $rooms = $this->routerosapi->read();
    $this->disconnect();

    foreach ($rooms as $key => $value) {
      $rooms[$key]['disabled'] = ($rooms[$key]['disabled'] === 'true');
      $rooms[$key]['canRemove'] = ($rooms[$key]['name'] !== 'default-trial');
      $rooms[$key]['bytes-in'] = $this->utility->formatBytes($rooms[$key]['bytes-in']);
      $rooms[$key]['bytes-out'] = $this->utility->formatBytes($rooms[$key]['bytes-out']);
      $rooms[$key]['packets-out'] = $this->utility->formatBytes($rooms[$key]['packets-out']);
      $rooms[$key]['packets-out'] = $this->utility->formatBytes($rooms[$key]['packets-out']);
      $rooms[$key]['limit-bytes-total'] = isset($rooms[$key]['limit-bytes-total']) ? 
        $this->utility->formatBytes($rooms[$key]['limit-bytes-total']) : null;
      unset($rooms[$key]['password']);
    }

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $rooms));
    $this->response($output, $httpCode);
  }
}
