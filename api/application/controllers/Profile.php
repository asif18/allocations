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

class Profile extends REST_Controller {

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
   * URL: /profile/saveProfile
   * Method: POST
   */
  public function saveProfile_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('name*', 'sharedUsers*', 'rateLimit*', 'sessionTimeOut*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    
    $status = $this->routerosapi->comm('/ip/hotspot/user/profile/add', array(
      'name' => $input['name'],
      'shared-users'  => $input['sharedUsers'],
      'rate-limit'   => $input['rateLimit'],
      'session-timeout' => $input['sessionTimeOut'],
      'on-logout' => 'usagelog',
      'on-login' => 'dnsUser'
    ));

    log_message('info', 'saveProfile_post profile added - '.$input['name']);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Profile added successfully' : 'error while adding profile');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /profile/removeProfile
   * Method: POST
   */
  public function removeProfile_get($id = null) {
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
    
    $this->routerosapi->write('/ip/hotspot/user/profile/remove', false);
    $this->routerosapi->write('=.id='.$id);
    $status = is_array($this->routerosapi->read());

    log_message('info', 'removeProfile_post profile removed- '.$id);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Profile removed successfully' : 'error while removing profile');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /profile/getProfiles
   * Method: GET
   */
  public function getProfiles_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $this->routerosapi->write('/ip/hotspot/user/profile/getall');
    $profiles = $this->routerosapi->read();
    $this->disconnect();
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $profiles));
    $this->response($output, $httpCode);
  }
}
