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

class UsageTimingLimits extends REST_Controller {

  private $timenow;
  private $tblprefix;

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }

    $this->timenow = $this->utility->timenow();
    $this->tblprefix = $this->db->tblprefix;
    $this->load->model('UsageTimingLimitsModel');
    $this->load->model('InstanceModel');
  }
  
  /**
   * URL: /usagetiminglimits/saveUsageTimingLimit
   * Method: POST
   */
  public function saveUsageTimingLimit_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('value*', 'time*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && 
      $userInfo['role'] !== SUPERADMIN_STAFF && 
      $userInfo['role'] !== CLIENTADMIN && 
      $userInfo['role'] !== CLIENTADMIN_STAFF) {

      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    // Remove last letter 'S' from $time if the $value is '1'
    $time  = ($input['value'] == 1) ? rtrim($input['time'], 's') : $input['time'];

    $isExist = $this->UsageTimingLimitsModel->getUsageTimingLimit(array(
      'value' => $input['value'], 
      'time' => $time,
      'status' => 'A',
      'user_id' => $userInfo['id']
      )
    );

    if ($isExist && count($isExist) > 0) {
      log_message('info', 'saveUsageTimingLimit_post size exist - '.$input['value'].'-'.$time);
      $output = array(
        'status' => false,
        'message' => 'Time already exist');
      $httpCode = REST_Controller::HTTP_OK;
      $this->response($output, $httpCode);
    }

    $insertData = array(
      'user_id' => $userInfo['id'],
      'value' => $input['value'],
      'time' => $time,
      'status' => 'A'
    );

    $instanceId = $this->UsageTimingLimitsModel->insertUsageTimingLimit($insertData);
    
    log_message('info', 'saveUsageTimingLimit_post new usage limit created - '.$input['value'].' - '.$time);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /usagetiminglimits/removeUsageTimingLimit
   * Method: GET 
   */
  public function removeUsageTimingLimit_get($id = null) {
    log_message('info', 'removeUsageTimingLimit_get');
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $id = base64_decode($id);
    $id = (int)$this->security->xss_clean($id);

    if (!is_numeric($id)) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $query = "SELECT
      id 
      FROM
        {$this->tblprefix}usage_timing_limits
      WHERE 
        id = '{$id}'";

    $usageTimingLimit = $this->UsageTimingLimitsModel->getAllUsageTimingLimits($query);

    if (count($usageTimingLimit) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $updatetData = array('status' => 'D');

    $where = array('id' => $id);
    $status = $this->UsageTimingLimitsModel->updateUsageTimingLimit($updatetData, $where);
    
    log_message('info', 'removeUsageTimingLimit_get data usage limit updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'removed successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /usagetiminglimits/getUsageTimingLimits
   * Method: POST
   */
  public function getUsageTimingLimits_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    if ($userInfo['role'] !==  SUPERADMIN && 
      $userInfo['role'] !== SUPERADMIN_STAFF && 
      $userInfo['role'] !== CLIENTADMIN && 
      $userInfo['role'] !== CLIENTADMIN_STAFF) {

      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }
    
    $query = array("SELECT
      `id`,
      `value`,
      `time` 
      FROM
        {$this->tblprefix}usage_timing_limits
      WHERE
        status = 'A' 
      AND ");

    if ($userInfo['role'] === SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      $activeInstanceId = json_decode($userInfo['settings'], true)['activeInstanceId'];
      $instance = $this->InstanceModel->getInstance(array('id' => $activeInstanceId));
      array_push($query, "`user_id` = '${instance['user_id']}'");
    }
    
    if ($userInfo['role'] === CLIENTADMIN || $userInfo['role'] === CLIENTADMIN_STAFF) {
      if (is_null($userInfo['parent_id'])) {
        array_push($query, "`user_id` = '${userInfo['id']}'");
      } else {
        array_push($query, "`user_id` = '${userInfo['parent_id']}'");
      }
    }

    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (`value` LIKE '%{$input['searchBy']}%' OR `time` LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);
    $usageLimits = $this->UsageTimingLimitsModel->getAllUsageTimingLimits($query);
  
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $usageLimits));
    $this->response($output, $httpCode);
  }
}
