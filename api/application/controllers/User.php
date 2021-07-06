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

class User extends REST_Controller {

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
   * URL: /user/getUsers
   * Method: POST
   */
  public function getUsers_post($exportAsExcel = false) {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && 
      $userInfo['role'] !==  CLIENTADMIN && $userInfo['role'] !== CLIENTADMIN_STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $query = array("SELECT 
      name, 
      email,
      phone, 
      status, 
      DATE_FORMAT(datetime,'%b %d, %Y') AS date
      FROM
        {$this->tblprefix}users
      WHERE 
        type = 'USER'");
    
    if ($userInfo['role'] === CLIENTADMIN || $userInfo['role'] === CLIENTADMIN_STAFF) {
      if (is_null($userInfo['parent_id'])) {
        array_push($query, "AND created_by = '${userInfo['id']}'");
      } else {
        array_push($query, "AND created_by = '${userInfo['parent_id']}'");
      }
    }
    if ($input['searchBy']) {
      array_push($query, 
        "AND (name LIKE '%{$input['searchBy']}%' OR email LIKE '%{$input['searchBy']}%' OR phone LIKE '%{$input['searchBy']}%' OR 
        username LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);

    $clients = $this->UserModel->getAllUsers($query);

    if ($exportAsExcel == 'export') {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Name');
      $sheet->setCellValue('B1', 'Email');
      $sheet->setCellValue('C1', 'Phone');
      $sheet->setCellValue('D1', 'Status');
      $sheet->setCellValue('E1', 'CreatedDate');    
      $rows = 2;
      foreach ($clients as $val){
        $sheet->setCellValue('A' . $rows, $val['name']);
        $sheet->setCellValue('B' . $rows, $val['email']);
        $sheet->setCellValue('C' . $rows, $val['phone']);
        $sheet->setCellValue('D' . $rows, $val['status']);
        $sheet->setCellValue('E' . $rows, $val['date']);
        $rows++;
      }
      $fileName = $this->utility->generateRandomString('users-export-sheet-'.date('dmY')) . '.xlsx';
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
