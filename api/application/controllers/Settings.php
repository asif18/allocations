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

class Settings extends REST_Controller {

  private $timenow;
  private $tblprefix;

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }
    
    $this->tblprefix = $this->db->tblprefix;
    $this->timenow = $this->utility->timenow();
    $this->load->model('UserModel');
  }

  /**
   * URL: /settings/getSettings
   * Method: GET
   */
  public function getSettings_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    $where = "i.status = 'ACT' AND i.user_id = ".$userInfo['id'];

    if ($userInfo['role'] === 'SUPERADMIN') {
      $where = "i.status = 'ACT'";
    }

    $settingsData = array(
      'name' => $userInfo['name'],
      'businessName' => $userInfo['business_name'],
      'username' => $userInfo['username'],
      'email' => $userInfo['email'],
      'phone' => $userInfo['phone'],
      'settings' => json_decode($userInfo['settings'], true)      
    );

    $output = array(
      'status' => true,
      'data' => $settingsData);
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /settings/saveSettings
   * Method: POST
   */
  public function saveSettings_post() {
    log_message('info', 'saveSettings_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('name*', 'businessName*', 'email*', 'phone*', 'password*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $updateData = array(
      'name' => $input['name'],
      'business_name' => $input['businessName'],
      'email' => $input['email'],
      'phone' => $input['phone']
    );

    if (!is_null($input['password'])) {
      $options = [
        'cost' => 12,
      ];
      $password = password_hash(base64_decode($input['password']), PASSWORD_BCRYPT, $options);
      $updateData['password'] = $password;
    }

    $status = $this->UserModel->updateUser($updateData, array('id' => $userInfo['id']));
    log_message('info', 'saveSettings_post settings update - '.$userInfo['id']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }  
}
