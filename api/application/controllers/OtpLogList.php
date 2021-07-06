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

class OtpLogList extends REST_Controller {

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
    $this->load->model('OtpModel');
  }

  /**
   * URL: /getOtpLogs
   * Method: POST
   */
  public function getOtpLogs_post($exportAsExcel = false) {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $query = array("SELECT
        wu.name AS receiver,
        wu.mobile_number AS mobileNumber,
        ol.otp, 
        ol.username, 
        ol.password, 
        ol.profile, 
        ol.sms_message AS smsMessage,
        u.name AS sentBy,
        c.business_name AS businessName,
        DATE_FORMAT(ol.datetime,'%b %d, %Y %H:%i:%S') AS datetime
        FROM
          {$this->tblprefix}otp_log ol
        LEFT JOIN {$this->tblprefix}wifi_users wu ON (ol.wifi_user_id  IS NOT NULL AND  wu.id = ol.wifi_user_id)
        LEFT JOIN {$this->tblprefix}users u ON (ol.sent_by IS NOT NULL AND u.id = ol.sent_by)
        INNER JOIN {$this->tblprefix}users c ON c.id = ol.client_id
        WHERE ");

    if ($userInfo['role'] === SUPERADMIN || $userInfo['role'] === SUPERADMIN_STAFF) {
      array_push($query, " 1=1 ");
    }

    if ($userInfo['role'] === CLIENTADMIN || $userInfo['role'] === CLIENTADMIN_STAFF) {
      if (is_null($userInfo['parent_id'])) {
        array_push($query, "ol.sent_by = '${userInfo['id']}'");
      } else {
        array_push($query, "ol.sent_by = '${userInfo['parent_id']}'");
      }
    }
    
    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (ol.otp LIKE '%{$input['searchBy']}%' OR ol.username LIKE '%{$input['searchBy']}%' OR ol.profile LIKE '%{$input['searchBy']}%' OR 
        ol.sms_message LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY ol.{$input['sortBy']} {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);

    $otpLogs = $this->OtpModel->getAllOtpLogs($query);

    foreach ($otpLogs as $key => $value) {
      $otpLogs[$key]['password'] = base64_encode($otpLogs[$key]['password']);
    }

    if ($exportAsExcel == 'export') {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Receiver');
      $sheet->setCellValue('B1', 'OTP');
      $sheet->setCellValue('C1', 'Username');
      $sheet->setCellValue('D1', 'Password');
      $sheet->setCellValue('E1', 'Profile');
      $sheet->setCellValue('F1', 'Mobile Number');
      $sheet->setCellValue('G1', 'SMS');
      $sheet->setCellValue('H1', 'Sent By');
      $sheet->setCellValue('I1', 'Business Name');
      $sheet->setCellValue('J1', 'Date');
      $rows = 2;
      foreach ($otpLogs as $val){
        $sheet->setCellValue('A' . $rows, $val['receiver']);
        $sheet->setCellValue('B' . $rows, $val['otp']);
        $sheet->setCellValue('C' . $rows, $val['username']);
        $sheet->setCellValue('D' . $rows, $val['password']);
        $sheet->setCellValue('E' . $rows, $val['profile']);
        $sheet->setCellValue('F' . $rows, $val['mobileNumber']);
        $sheet->setCellValue('G' . $rows, $val['smsMessage']);
        $sheet->setCellValue('H' . $rows, $val['sentBy']);
        $sheet->setCellValue('I' . $rows, $val['businessName']);
        $sheet->setCellValue('J' . $rows, $val['datetime']);
        $rows++;
      }
      $fileName = $this->utility->generateRandomString('otp-log-export-sheet-'.date('dmY')) . '.xlsx';
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      $writer->save('php://output');
      exit;

    } else {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array(
        'status' => true,
        'data' => array('items' => $otpLogs));
      $this->response($output, $httpCode);
    }
  }
}
