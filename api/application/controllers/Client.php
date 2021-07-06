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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require APPPATH . '/libraries/REST_Controller.php';

class Client extends REST_Controller {

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
   * URL: /client/saveClient
   * Method: POST
   */
  public function saveClient_post() {
    log_message('info', 'saveClient_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('name*', 'businessName*', 'email*', 'phone*', 'username*', 'password*', 'smsGateway*', 'smsLimit*',
      'otpLogin*', 'fbLogin*', 'smsCampaign*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $isExist = $this->UserModel->getUser(array('username' => $input['username']));

    if ($isExist && count($isExist) > 0) {
      log_message('info', 'saveClient_post username exist - '.$input['username']);
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
      'name' => $input['name'],
      'business_name' => $input['businessName'],
      'email' => $input['email'],
      'phone' => $input['phone'],
      'username' => $input['username'],
      'password' => $password,
      'role' => CLIENTADMIN,
      'type' => 'CLIENT',
      'otp_login' => $input['otpLogin'],
      'fb_login' => $input['fbLogin'],
      'sms_campaign' => $input['smsCampaign'],
      'sms_gateway' => $input['smsGateway'],
      'sms_limit' => $input['smsLimit'],
      'status' => 'ACT',
      'created_by' => $userInfo['id'],
      'datetime' => $this->timenow
    );

    $status = $this->UserModel->insertUser($insertData);
    log_message('info', 'saveClient_post client inserted - '.$input['username']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /client/updateClient
   * Method: POST
   */
  public function updateClient_post($id = null) {
    log_message('info', 'saveClient_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('name*', 'businessName*', 'email*', 'phone*', 'smsGateway*', 'smsLimit*', 'otpLogin*', 'fbLogin*',
      'smsCampaign*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
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
        {$this->tblprefix}users
      WHERE 
        type = 'CLIENT' AND
        id = '{$id}'";

    $clients = $this->UserModel->getAllUsers($query);

    if (count($clients) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $updatetData = array(
      'name' => $input['name'],
      'business_name' => $input['businessName'],
      'email' => $input['email'],
      'phone' => $input['phone'],
      'otp_login' => $input['otpLogin'],
      'fb_login' => $input['fbLogin'],
      'sms_campaign' => $input['smsCampaign'],
      'sms_gateway' => $input['smsGateway'],
      'sms_limit' => $input['smsLimit'],
      'updated_by' => $userInfo['id'],
      'updated_on' => $this->timenow
    );

    $where = array('id' => $id);
    $status = $this->UserModel->updateUser($updatetData, $where);
    
    log_message('info', 'updateClient_post client updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /client/getClient
   * Method: GET
   */
  public function getClient_get($id = null) {
    log_message('info', 'getClient_get');
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }
    
    $id = base64_decode($id);
    
    if (!is_numeric($id)) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $query = "SELECT
      id,  
      name, 
      business_name, 
      email,
      phone,
      username,
      otp_login,
      fb_login,
      sms_campaign,
      sms_gateway,
      sms_limit
      FROM
        {$this->tblprefix}users
      WHERE 
        type = 'CLIENT' AND
        id = '{$id}'";

    $clients = $this->UserModel->getAllUsers($query);

    $clientData = [];
    if (count($clients) > 0) {
      foreach ($clients as $key) {
        $clientData['id'] = (INT)$key['id'];
        $clientData['name'] = $key['name'];
        $clientData['businessName'] = $key['business_name'];
        $clientData['email'] = $key['email'];
        $clientData['phone'] = $key['phone'];
        $clientData['otpLogin'] = $this->utility->parseTinyIntToBoolean($key['otp_login']);
        $clientData['fbLogin'] = $this->utility->parseTinyIntToBoolean($key['fb_login']);
        $clientData['smsCampaign'] = $this->utility->parseTinyIntToBoolean($key['sms_campaign']);
        $clientData['smsGateway'] = $key['sms_gateway'];
        $clientData['smsLimit'] = $key['sms_limit'];
      }
    }

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => $clientData);
    $this->response($output, $httpCode);
  }

  /**
   * URL: /client/getClients
   * Method: POST
   */
  public function getClients_post($exportAsExcel = false) {
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
      parent_id, 
      name, 
      business_name, 
      username,
      otp_login,
      fb_login,
      sms_campaign,
      sms_gateway,
      sms_limit, 
      status, 
      settings, 
      DATE_FORMAT(datetime,'%b %d, %Y') AS date
      FROM
        {$this->tblprefix}users
      WHERE 
        type = 'CLIENT'");
      
    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (name LIKE '%{$input['searchBy']}%' OR business_name LIKE '%{$input['searchBy']}%' OR phone LIKE '%{$input['searchBy']}%' OR 
        username LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);

    $clients = $this->UserModel->getAllUsers($query);

    foreach ($clients as $key => $value) {
      $clients[$key]['id'] = (INT)$clients[$key]['id'];
      $clients[$key]['settings'] = json_decode($clients[$key]['settings'], true);
      $clients[$key]['instances'] = $this->InstanceModel->getAllInstances("SELECT 
        id,
        mik_ip AS mikIp, 
        mik_port AS mikPort, 
        status AS mikStatus
        FROM
          {$this->tblprefix}instances
        WHERE 
          user_id = {$clients[$key]['id']}");

      $userCount = $this->UserModel->getAllUsers("SELECT COUNT(id) as usersCount FROM {$this->tblprefix}users
        WHERE type = 'USER' AND parent_id = {$clients[$key]['id']}")[0]['usersCount'];
      $clients[$key]['usersCount'] = (INT)$userCount;
      $clients[$key]['otp_login'] = $this->utility->parseTinyIntToBoolean($clients[$key]['otp_login']);
      $clients[$key]['fb_login'] = $this->utility->parseTinyIntToBoolean($clients[$key]['fb_login']);
      $clients[$key]['sms_campaign'] = $this->utility->parseTinyIntToBoolean($clients[$key]['sms_campaign']);
    }

    if ($exportAsExcel == 'export') {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Id');
      $sheet->setCellValue('B1', 'Name');
      $sheet->setCellValue('C1', 'BusinessName');
      $sheet->setCellValue('D1', 'Username');
      $sheet->setCellValue('E1', 'Instances');
      $sheet->setCellValue('F1', 'Users');
      $sheet->setCellValue('G1', 'OTP Login');
      $sheet->setCellValue('H1', 'Facebook Login');
      $sheet->setCellValue('I1', 'SMS Campaign');
      $sheet->setCellValue('J1', 'SMS Gateway');
      $sheet->setCellValue('K1', 'SMS Limit');
      $sheet->setCellValue('L1', 'Status');
      $sheet->setCellValue('M1', 'CreatedDate');    
      $rows = 2;
      foreach ($clients as $val){
        $sheet->setCellValue('A' . $rows, $val['id']);
        $sheet->setCellValue('B' . $rows, $val['name']);
        $sheet->setCellValue('C' . $rows, $val['business_name']);
        $sheet->setCellValue('D' . $rows, $val['username']);
        $sheet->setCellValue('E' . $rows, count($val['instances']));
        $sheet->setCellValue('F' . $rows, $val['usersCount']);
        $sheet->setCellValue('G' . $rows, $val['otp_login']);
        $sheet->setCellValue('H' . $rows, $val['fb_login']);
        $sheet->setCellValue('I' . $rows, $val['sms_campaign']);
        $sheet->setCellValue('J' . $rows, $val['sms_gateway']);
        $sheet->setCellValue('K' . $rows, $val['sms_limit']);
        $sheet->setCellValue('L' . $rows, $val['status']);
        $sheet->setCellValue('M' . $rows, $val['date']);
        $rows++;
      }
      $fileName = $this->utility->generateRandomString('clients-export-sheet-'.date('dmY')) . '.xlsx';
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      $writer->save('php://output');
      exit;

    } else {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array(
        'status' => true,
        'data' => array('items' => $clients));
      $this->response($output, $httpCode);
    }
  }
}
