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
    $this->load->model('InstanceModel');
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

    $instances = $this->InstanceModel->getAllInstances("SELECT 
      i.name,
      i.id,
      i.mik_ip AS mikIp, 
      i.mik_port AS mikPort, 
      i.status AS mikStatus
      FROM
      {$this->tblprefix}instances i JOIN {$this->tblprefix}users u
      ON i.user_id = u.id
      WHERE {$where}");

    foreach ($instances as $key => $value) {
      $instances[$key]['id'] = (INT)$instances[$key]['id'];
    }

    $settingsData = array(
      'name' => $userInfo['name'],
      'businessName' => $userInfo['business_name'],
      'username' => $userInfo['username'],
      'email' => $userInfo['email'],
      'phone' => $userInfo['phone'],
      'settings' => json_decode($userInfo['settings'], true),
      'instances' => $instances
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
    $acceptedKeys = array('instance*', 'name*', 'businessName*', 'email*', 'phone*', 'password*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    $settings = $this->utility->updateJSON($userInfo['settings'], array('activeInstanceId' => $input['instance']));

    $updateData = array(
      'name' => $input['name'],
      'business_name' => $input['businessName'],
      'email' => $input['email'],
      'phone' => $input['phone'],
      'settings' => $settings
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
  
  /**
   * URL: /settings/getWifiUserSettings
   * Method: GET
   */
  public function getWifiUserSettings_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    $output = array(
      'status' => true,
      'data' => json_decode($instance['wifi_user_settings']));
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }
  
  /**
   * URL: /settings/saveWifiUserSettings
   * Method: POST
   */
  public function saveWifiUserSettings_post() {
    log_message('info', 'saveWifiUserSettings_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('profile*', 'repeatInterval*', 'usageTimeLimit*', 'validTill*', 'dataUsageLimit*',
      'canShowNameField*', 'isNameFieldRequired*', 'canShowEmailField*', 'isEmailFieldRequired*',
      'advertismentImageTargetUrl*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);
    
    $settings = $this->utility->updateJSON($instance['wifi_user_settings'], array(
      'profile' => $input['profile'],
      'repeatInterval' => $input['repeatInterval'],
      'usageTimeLimit' => $input['usageTimeLimit'],
      'validTill' => $input['validTill'],
      'dataUsageLimit' => $input['dataUsageLimit'],
      'canShowNameField' => $input['canShowNameField'],
      'isNameFieldRequired' => $input['isNameFieldRequired'],
      'canShowEmailField' => $input['canShowEmailField'],
      'isEmailFieldRequired' => $input['isEmailFieldRequired'],
      'advertismentImageTargetUrl' => $input['advertismentImageTargetUrl']
    ));

    $updateData = array('wifi_user_settings' => $settings);

    $status = $this->InstanceModel->updateInstance($updateData, array('id' => $instance['id']));
    log_message('info', 'saveWifiUserSettings_post settings update - '.$instance['id']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /settings/uploadAdvertismentImage
   * Method: POST
   */
  public function uploadAdvertismentImage_post() {
    log_message('info', 'uploadAdvertismentImage_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('advertismentImage');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);
    
    $tmp = explode('.', $_FILES['image']['name']);
    $advertismentImageUrl = 'uploads/adv_img_' . date('dmyhis') . '.' . end($tmp);;
    move_uploaded_file($_FILES['image']['tmp_name'], $advertismentImageUrl);

    $settings = $this->utility->updateJSON($instance['wifi_user_settings'], array(
      'advertismentImageUrl' => $advertismentImageUrl
    ));
  
    $updateData = array('wifi_user_settings' => $settings);
    $status = $this->InstanceModel->updateInstance($updateData, array('id' => $instance['id']));
    log_message('info', 'uploadAdvertismentImage_post settings update - '.$instance['id']);
    $output = array(
      'status' => true,
      'data' => array('advertismentImageUrl' => $advertismentImageUrl),
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /settings/removeAdvertismentImage
   * Method: POST
   */
  public function removeAdvertismentImage_get() {
    log_message('info', 'removeAdvertismentImage_get');
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);
    
    $advertismentImageUrl = json_decode($instance['wifi_user_settings'], true)['advertismentImageUrl'];
    unlink($advertismentImageUrl);
    $settings = $this->utility->updateJSON($instance['wifi_user_settings'], array(
      'advertismentImageUrl' => null
    ));
  
    $updateData = array('wifi_user_settings' => $settings);
    $status = $this->InstanceModel->updateInstance($updateData, array('id' => $instance['id']));
    log_message('info', 'removeAdvertismentImage_get settings update - '.$instance['id']);
    $output = array(
      'status' => true,
      'message' => 'removed successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }
  
}
