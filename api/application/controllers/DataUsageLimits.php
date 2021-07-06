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

class DataUsageLimits extends REST_Controller {

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
    $this->load->model('DataUsageLimitsModel');
    $this->load->model('InstanceModel');
  }
  
  /**
   * URL: /datausagelimits/saveDataUsageLimit
   * Method: POST
   */
  public function saveDataUsageLimit_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('value*', 'size*');
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

    $isExist = $this->DataUsageLimitsModel->getDataUsageLimit(array(
      'value' => $input['value'], 
      'size' => $input['size'],
      'status' => 'A',
      'user_id' => $userInfo['id']
      )
    );

    if ($isExist && count($isExist) > 0) {
      log_message('info', 'saveDataUsageLimit_post size exist - '.$input['value'].'-'.$input['size']);
      $output = array(
        'status' => false,
        'message' => 'limit already exist');
      $httpCode = REST_Controller::HTTP_OK;
      $this->response($output, $httpCode);
    }

    $insertData = array(
      'user_id' => $userInfo['id'],
      'value' => $input['value'],
      'size' => $input['size'],
      'status' => 'A'
    );

    $instanceId = $this->DataUsageLimitsModel->insertDataUsageLimit($insertData);
    
    log_message('info', 'saveDataUsageLimit_post new usage limit created - '.$input['value'].' - '.$input['size']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /datausagelimits/removeDataUsageLimit
   * Method: GET
   */
  public function removeDataUsageLimit_get($id = null) {
    log_message('info', 'removeDataUsageLimit_get');
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
        {$this->tblprefix}data_usage_limits
      WHERE 
        id = '{$id}'";

    $dataUsageLimt = $this->DataUsageLimitsModel->getAllDataUsageLimits($query);

    if (count($dataUsageLimt) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $updatetData = array('status' => 'D');

    $where = array('id' => $id);
    $status = $this->DataUsageLimitsModel->updateDataUsageLimit($updatetData, $where);
    
    log_message('info', 'removeDataUsageLimit_get data usage limit updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'removed successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /datausagelimits/getDataUsageLimits
   * Method: POST
   */
  public function getDataUsageLimits_post() {
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
      `size` 
      FROM
        {$this->tblprefix}data_usage_limits
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
        "AND (`value` LIKE '%{$input['searchBy']}%' OR `size` LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);
    $usageLimits = $this->DataUsageLimitsModel->getAllDataUsageLimits($query);
  
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $usageLimits));
    $this->response($output, $httpCode);
  }
}
