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

class Staff extends REST_Controller {

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
    $this->load->model('StaffModel');
  }
  
  /**
   * URL: /staff/saveStaff
   * Method: POST
   */
  public function saveStaff_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('role', 'name*', 'email*', 'username*', 'password*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $isExist = $this->StaffModel->getStaff(array('username' => $input['username']));

    if ($isExist && count($isExist) > 0) {
      log_message('info', 'saveStaff_post username exist - '.$input['username']);
      $output = array(
        'status' => false,
        'message' => 'username already exist');
      $httpCode = REST_Controller::HTTP_OK;
      $this->response($output, $httpCode);
    }

    $options = [
      'cost' => 12,
    ];
    $password = password_hash(base64_decode($input['password']), PASSWORD_BCRYPT, $options);

    $insertData = array(
      'parent_id' => $userInfo['id'],
      'name' => $input['name'],
      'email' => $input['email'],
      'username' => $input['username'],
      'password' => $password,
      'role' => $input['role'],
      'type' => 'STAFF',
      'status' => 'ACT',
      'created_by' => $userInfo['id'],
      'datetime' => $this->timenow
    );

    $this->StaffModel->insertStaff($insertData);
    
    log_message('info', 'saveStaff_post new staff created - '.$input['username']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /staff/enableDisableStaff
   * Method: GET
   */
  public function enableDisableStaff_get($id = null) {
    log_message('info', 'enableDisableStaff_get');
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
      id,
      status  
      FROM
        {$this->tblprefix}users
      WHERE 
        id = '{$id}'";

    $staff = $this->StaffModel->getAllStaffs($query);

    if (count($staff) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $status = ($staff[0]['status'] === 'ACT') ? 'IAT' : 'ACT';
    $updatetData = array('status' => $status);

    $where = array('id' => $id);
    $this->StaffModel->updateStaff($updatetData, $where);
    
    log_message('info', 'enableDisableStaff_get stafff updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'updated successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /staff/getStaffs
   * Method: POST
   */
  public function getStaffs_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $query = array("SELECT
      id,
      name,
      email,
      username,
      role,
      status
      FROM
        {$this->tblprefix}users
      WHERE
        parent_id = '{$userInfo['id']}'");

    if ($input['searchBy']) {
      array_push($query, 
        "AND (name LIKE '%{$input['searchBy']}%' OR username LIKE '%{$input['searchBy']}%' OR email LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);
    $usageLimits = $this->StaffModel->getAllStaffs($query);
  
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $usageLimits));
    $this->response($output, $httpCode);
  }
}
