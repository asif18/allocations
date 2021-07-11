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

class Yards extends REST_Controller {

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
    $this->load->model('YardsModel');
  }
  
  /**
   * URL: /yards/saveYard
   * Method: POST
   */
  public function saveYard_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('code*', 'name*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $isExist = $this->YardsModel->getYard(array('code' => $input['code'], 'status' => 'A'));

    if ($isExist && count($isExist) > 0) {
      log_message('info', 'yard_post code exist - '.$input['code']);
      $output = array(
        'status' => false,
        'message' => 'Code already exist');
      $httpCode = REST_Controller::HTTP_OK;
      $this->response($output, $httpCode);
    }

    $insertData = array(
      'code' => $input['code'],
      'name' => $input['name'],
      'status' => 'A',
      'created_by' => $userInfo['id'],
      'datetime' => $this->timenow
    );

    $yardId = $this->YardsModel->insertYard($insertData);
    
    log_message('info', 'saveYard_post new yard created - '.$input['code']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /yards/removeYard
   * Method: GET 
   */
  public function removeYard_get($id = null) {
    log_message('info', 'removeYard_get');
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
        {$this->tblprefix}yards
      WHERE 
        id = '{$id}'";

    $yard = $this->YardsModel->getAllYards($query);

    if (count($yard) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $updatetData = array('status' => 'D');

    $where = array('id' => $id);
    $status = $this->YardsModel->updateYard($updatetData, $where);
    
    log_message('info', 'removeYard_get yard updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'removed successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /yard/getYards
   * Method: POST
   */
  public function getYards_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }
    
    $query = array("SELECT
      `id`,
      `code`,
      `name` 
      FROM
        {$this->tblprefix}yards
      WHERE
        status = 'A' ");

    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (`code` LIKE '%{$input['searchBy']}%' OR `name` LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);
    $yards = $this->YardsModel->getAllYards($query);
  
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $yards));
    $this->response($output, $httpCode);
  }
}
