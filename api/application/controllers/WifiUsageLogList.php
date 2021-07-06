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

class WifiUsageLogList extends REST_Controller {

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
    $this->load->model('WifiUsageModel');
  }

  /**
   * URL: /getWifiUsageLogs
   * Method: POST
   */
  public function getWifiUsageLogs_post($exportAsExcel = false) {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $query = array("SELECT
        wul.username, 
        wu.name, 
        wu.mobile_number AS mobileNumber,
        wul.profile,
        wul.mac_address AS macAddress,
        DATE_FORMAT(wul.login_time,'%b %d, %Y %H:%i:%S') AS loginTime,
        DATE_FORMAT(wul.logout_time,'%b %d, %Y %H:%i:%S') AS logoutTime,
        wul.uptime AS upTime,
        wul.bytes_in AS bytesIn,
        wul.bytes_out AS bytesOut,
        wul.total_usage AS totalUsage,
        wul.allocated_usage AS allocatedUsage,
        DATE_FORMAT(wul.datetime,'%b %d, %Y %H:%i:%S') AS datetime
        FROM
          {$this->tblprefix}wifi_usage_log wul 
          LEFT JOIN {$this->tblprefix}wifi_users wu ON wu.id = wul.wifi_user_id
        WHERE ");

    if ($userInfo['role'] === SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF) {
      array_push($query, " 1=1 ");
    }

    if ($userInfo['role'] === CLIENTADMIN || $userInfo['role'] === CLIENTADMIN_STAFF) {
      if (is_null($userInfo['parent_id'])) {
        array_push($query, "wul.client_id = '${userInfo['id']}'");
      } else {
        array_push($query, "wul.client_id = '${userInfo['parent_id']}'");
      }
    }
    
    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (wul.username LIKE '%{$input['searchBy']}%' OR wu.name LIKE '%{$input['searchBy']}%' OR wu.mobile_number 
        LIKE '%{$input['searchBy']}%' OR wul.profile LIKE '%{$input['searchBy']}%' OR wul.mac_address 
        LIKE '%{$input['searchBy']}%' OR wul.logout_time LIKE '%{$input['searchBy']}%' OR wul.login_time 
        LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY wul.{$input['sortBy']} {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);

    $wifiUsageLogs = $this->WifiUsageModel->getAllWifiUsageLogs($query);

    foreach ($wifiUsageLogs as $key => $value) {
      $wifiUsageLogs[$key]['upTime'] = gmdate('H:i:s', $wifiUsageLogs[$key]['upTime']);
    }

    if (count($wifiUsageLogs) > 0) {
      foreach ($wifiUsageLogs as $key => $value) {
        $wifiUsageLogs[$key]['bytesIn'] = $this->utility->formatBytes($wifiUsageLogs[$key]['bytesIn']);
        $wifiUsageLogs[$key]['bytesOut'] = $this->utility->formatBytes($wifiUsageLogs[$key]['bytesOut']);
        $wifiUsageLogs[$key]['totalUsage'] = $this->utility->formatBytes($wifiUsageLogs[$key]['totalUsage']);
        $wifiUsageLogs[$key]['allocatedUsage'] = $this->utility->formatBytes($wifiUsageLogs[$key]['allocatedUsage']);
      }
    }

    if ($exportAsExcel == 'export') {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Username');
      $sheet->setCellValue('B1', 'Name');
      $sheet->setCellValue('C1', 'Mobile');
      $sheet->setCellValue('D1', 'Profile');
      $sheet->setCellValue('E1', 'Mac Address');
      $sheet->setCellValue('F1', 'Login Time');
      $sheet->setCellValue('G1', 'Logout Time');
      $sheet->setCellValue('H1', 'Up Time');
      $sheet->setCellValue('I1', 'Bytes In');
      $sheet->setCellValue('J1', 'Bytes Out');
      $sheet->setCellValue('K1', 'Total Usage');
      $sheet->setCellValue('L1', 'Allocated Usage');
      $rows = 2;
      foreach ($wifiUsageLogs as $val){
        $sheet->setCellValue('A' . $rows, $val['username']);
        $sheet->setCellValue('B' . $rows, $val['name']);
        $sheet->setCellValue('C' . $rows, $val['mobileNumber']);
        $sheet->setCellValue('D' . $rows, $val['profile']);
        $sheet->setCellValue('E' . $rows, $val['macAddress']);
        $sheet->setCellValue('F' . $rows, $val['loginTime']);
        $sheet->setCellValue('G' . $rows, $val['logoutTime']);
        $sheet->setCellValue('H' . $rows, $val['upTime']);
        $sheet->setCellValue('I' . $rows, $val['bytesIn']);
        $sheet->setCellValue('J' . $rows, $val['bytesOut']);
        $sheet->setCellValue('K' . $rows, $val['totalUsage']);
        $sheet->setCellValue('L' . $rows, $val['allocatedUsage']);
        $rows++;
      }
      $fileName = $this->utility->generateRandomString('wifi-usage-log-export-sheet-'.date('dmY')) . '.xlsx';
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      $writer->save('php://output');
      exit;

    } else {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array(
        'status' => true,
        'data' => array('items' => $wifiUsageLogs));
      $this->response($output, $httpCode);
    }
  }
}
