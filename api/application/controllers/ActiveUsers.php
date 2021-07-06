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

class ActiveUsers extends REST_Controller {

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
   * URL: /activeUsers/removeActiveUser
   * Method: POST
   */
  public function removeActiveUser_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('id*', 'name*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    
    $this->routerosapi->write('/ip/hotspot/active/remove',false);
    $this->routerosapi->write('=.id='.$input['id']);
    $this->routerosapi->read();
    
    /**
     * Disable User
     */
    $this->routerosapi->write('/ip/hotspot/user/print', false);
    $this->routerosapi->write('=.proplist=.id', false);
    $this->routerosapi->write('?name='.$input['name']);
    
    $user = $this->routerosapi->read();
    $user = $user[0];
    $this->routerosapi->write('/ip/hotspot/user/set', false);
    $this->routerosapi->write('=.id='.$user['.id'], false);
    $this->routerosapi->write('=disabled=yes');
    $status = is_array($this->routerosapi->read());

    log_message('info', 'removeActiveUser_post active user removed- '.$input['id']);
    $output = array(
      'status' => $status,
      'message' => $status ? 'Active user removed successfully' : 'error while removing active user');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /activeUsers/getActiveUsers
   * Method: GET
   */
  public function getActiveUsers_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $this->routerosapi->write('/ip/hotspot/active/getall');
    $activeUsers = $this->routerosapi->read();
    $this->disconnect();

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $activeUsers));
    $this->response($output, $httpCode);
  }
}
