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

class Instance extends REST_Controller {

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
    $this->load->model('InstanceModel');
  }

  /**
	 * Connect to Instance
	 *
	 * @paraam $ip, $username, $password
	 * @type IP, String, $String
	 */
	private function connect($ip, $username, $password) {
    log_message('info', 'instance - connecting to - '.$ip['username']);
		$this->routerosapi->debug = (ENVIRONMENT === 'development');
		return $this->routerosapi->connect($ip, $username, $password);
  }
  
  /**
   * URL: /instance/saveInstance
   * Method: POST
   */
  public function saveInstance_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('client*', 'name*', 'ipAddress*', 'port*', 'username*', 'password*', 'wifiDefaultPassword*',
     'lanIpAddress*', 'dnsIpAddress', 'dnsPort', 'destinationUrl');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $insertData = array(
      'name' => $input['name'],
      'mik_ip' => $input['ipAddress'],
      'mik_port' => $input['port'],
      'mik_username' => $input['username'],
      'mik_password' => $input['password'],
      'mik_default_password' => $input['wifiDefaultPassword'],
      'mik_lan_ip' => $input['lanIpAddress'],
      'mik_dns_ip' => $input['dnsIpAddress'],
      'mik_dns_port' => $input['dnsPort'],
      'user_id' => $input['client'],
      'created_by' => $userInfo['id'],
      'destination' => $input['destinationUrl'],
      'status' => 'ACT',
      'datetime' => $this->timenow
    );

    $instanceId = $this->InstanceModel->insertInstance($insertData);
    $settings = $this->utility->updateJSON($userInfo['settings'], array('activeInstanceId' => $instanceId));
    $this->UserModel->updateUser(array('settings' => $settings), array('id' => $input['client']));

    log_message('info', 'saveInstance_post mik instance created - '.$input['name']);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /instance/updateInstance
   * Method: POST
   */
  public function updateInstance_post($id = null) {
    log_message('info', 'saveInstance_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('name*', 'ipAddress*', 'port*', 'username*', 'password*', 'wifiDefaultPassword*', 'lanIpAddress*', 
      'lanIpAddress', 'dnsPort', 'destinationUrl');
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
        {$this->tblprefix}instances
      WHERE 
        id = '{$id}'";

    $instances = $this->InstanceModel->getAllInstances($query);

    if (count($instances) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $updatetData = array(
      'name' => $input['name'],
      'mik_ip' => $input['ipAddress'],
      'mik_port' => $input['port'],
      'mik_username' => $input['username'],
      'mik_password' => $input['password'],
      'mik_default_password' => $input['wifiDefaultPassword'],
      'mik_lan_ip' => $input['lanIpAddress'],
      'mik_dns_ip' => $input['dnsIpAddress'],
      'mik_dns_port' => $input['dnsPort'],
      'destination' => $input['destinationUrl']
    );

    $where = array('id' => $id);
    $status = $this->InstanceModel->updateInstance($updatetData, $where);
    
    log_message('info', 'updateInstance_post instance updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /instance/getInstance
   * Method: GET
   */
  public function getInstance_get($id = null) {
    log_message('info', 'getInstance_get');
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    $id = base64_decode($id);
    
    if (!is_numeric($id)) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $query = "SELECT
      id, 
      name,
      mik_ip,
      mik_port,
      mik_username,
      mik_password,
      mik_default_password,
      mik_lan_ip,
      mik_dns_ip,
      mik_dns_port,
      destination
      FROM
        {$this->tblprefix}instances
      WHERE 
        id = '{$id}'";

    $instances = $this->InstanceModel->getAllInstances($query);

    $instanceData = [];
    if (count($instances) > 0) {
      foreach ($instances as $key) {
        $instanceData['id'] = (INT)$key['id'];
        $instanceData['name'] = $key['name'];
        $instanceData['ipAddress'] = $key['mik_ip'];
        $instanceData['port'] = $key['mik_port'];
        $instanceData['username'] = $key['mik_username'];
        $instanceData['password'] = $key['mik_password'];
        $instanceData['wifiDefaultPassword'] = $key['mik_default_password'];
        $instanceData['lanIpAddress'] = $key['mik_lan_ip'];
        $instanceData['dnsIpAddress'] = $key['mik_dns_ip'];
        $instanceData['dnsPort'] = $key['mik_dns_port'];
        $instanceData['destinationUrl'] = $key['destination'];
      }
    } else {
      $instanceData = null;
    }

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => $instanceData);
    $this->response($output, $httpCode);
  }

  /**
   * URL: /instance/getInstances
   * Method: POST
   */
  public function getInstances_post($exportAsExcel = false) {
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
      id, 
      name,
      mik_ip AS mikIp, 
      mik_port AS mikPort, 
      mik_username AS mikUsername,
      mik_password AS mikPassword,
      mik_lan_ip AS mikLanIp,
      mik_dns_ip AS mikDnsIp,
      mik_dns_port AS mikDnsPort,
      user_id,
      destination, 
      DATE_FORMAT(datetime,'%b %d, %Y') AS date
      FROM
        {$this->tblprefix}instances
      WHERE");

    if ($userInfo['role'] === SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      array_push($query, "1 = 1");
    }
    
    if ($userInfo['role'] === CLIENTADMIN || $userInfo['role'] === CLIENTADMIN_STAFF) {
      if (is_null($userInfo['parent_id'])) {
        array_push($query, "user_id = '${userInfo['id']}'");
      } else {
        array_push($query, "user_id = '${userInfo['parent_id']}'");
      }
    }

    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (name LIKE '%{$input['searchBy']}%' OR mik_ip LIKE '%{$input['searchBy']}%' OR mik_port LIKE '%{$input['searchBy']}%' OR 
        mik_username LIKE '%{$input['searchBy']}%' OR mik_lan_ip LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);
    $instances = $this->InstanceModel->getAllInstances($query);

    foreach ($instances as $key => $value) {
      $instances[$key]['id'] = (INT)$instances[$key]['id'];
      $instances[$key]['businessName'] = $this->UserModel->getUser(array('id' => $instances[$key]['user_id']))['business_name'];
      unset($instances[$key]['user_id']);
    }

    if ($exportAsExcel == 'export') {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Instance Name');
      $sheet->setCellValue('B1', 'Business Name');
      $sheet->setCellValue('C1', 'IP');
      $sheet->setCellValue('D1', 'PORT');
      $sheet->setCellValue('E1', 'Username');
      $sheet->setCellValue('F1', 'Password');
      $sheet->setCellValue('G1', 'LAN IP');
      $sheet->setCellValue('G1', 'DNS IP');
      $sheet->setCellValue('H1', 'DNS Port');
      $sheet->setCellValue('I1', 'Destination');
      $sheet->setCellValue('J1', 'CreatedDate');    
      $rows = 2;
      foreach ($instances as $val) {
        $sheet->setCellValue('A' . $rows, $val['name']);
        $sheet->setCellValue('B' . $rows, $val['businessName']);
        $sheet->setCellValue('C' . $rows, $val['mikIp']);
        $sheet->setCellValue('D' . $rows, $val['mikPort']);
        $sheet->setCellValue('E' . $rows, $val['mikUsername']);
        $sheet->setCellValue('F' . $rows, base64_decode($val['mikPassword']));
        $sheet->setCellValue('G' . $rows, $val['mikLanIp']);
        $sheet->setCellValue('G' . $rows, $val['mikDnsIp']);
        $sheet->setCellValue('H' . $rows, $val['mikDnsport']);
        $sheet->setCellValue('I' . $rows, $val['destination']);
        $sheet->setCellValue('J' . $rows, $val['date']);
        $rows++;
      }
      $fileName = $this->utility->generateRandomString('instances-export-sheet-'.date('dmY')) . '.xlsx';
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      $writer->save('php://output');
      exit;

    } else {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array(
        'status' => true,
        'data' => array('items' => $instances));
      $this->response($output, $httpCode);
    }
  }
}
