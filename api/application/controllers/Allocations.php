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

class Allocations extends REST_Controller {

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
    $this->load->model('AllocationsModel');
  }
  
  /**
   * URL: /allocations/saveAllocation
   * Method: POST
   */
  public function saveAllocation_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('containerNumber*', 'destination*', 'yard*', 'to*', 'chassisNumber*', 'sealNumber*', 
      'deliveryDate*', 'allocationStatus*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $insertData = array(
      'container_number'      => $input['containerNumber'],
      'destination_id'        => $input['destination'],
      'yard_id'               => $input['yard'],
      'to'                    => $input['to'],
      'chassis_number'        => $input['chassisNumber'],
      'seal_number'           => $input['sealNumber'],
      'delivery_date'         => $input['deliveryDate'],
      'allocation_status_id'  => $input['allocationStatus'],
      'status'                => 'A',
      'created_by'            => $userInfo['id'],
      'datetime'              => $this->timenow
    );

    $allocationId = $this->AllocationsModel->insertAllocation($insertData);
    
    log_message('info', 'saveAllocation_post new allocation created - '.$allocationId);
    $output = array(
      'status' => true,
      'message' => 'saved successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /allocations/removeAllocation
   * Method: GET 
   */
  public function removeAllocation_get($id = null) {
    log_message('info', 'removeAllocation_get');
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
        {$this->tblprefix}allocations
      WHERE 
        id = '{$id}'";

    $allocation = $this->AllocationsModel->getAllAllocations($query);

    if (count($allocation) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $updatetData = array('status' => 'D');

    $where = array('id' => $id);
    $status = $this->AllocationsModel->updateAllocation($updatetData, $where);
    
    log_message('info', 'removeAllocation_get yard updated - '.$id);
    $output = array(
      'status' => true,
      'message' => 'removed successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /allocations/getAllocationStatuses
   * Method: POST
   */
  public function getAllocationStatuses_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('searchBy', 'startFrom', 'endTo', 'sorttBy', 'sortDirection');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF 
        && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }
    
    $query = array("SELECT
      `id`,
      `name` 
      FROM
        {$this->tblprefix}allocation_statuses
      WHERE
        status = 'A' ");

    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (`name` LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);
    $statuses = $this->AllocationsModel->getAllStatuses($query);
  
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => array('items' => $statuses));
    $this->response($output, $httpCode);
  }

  /**
   * URL: /allocations/getAllocations
   * Method: POST
   */
  public function getAllocations_post($exportAsExcel = false) {
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
      al.id,
      al.container_number,
      ds.code AS destinationCode,
      ds.name AS destinationName,
      ys.code AS yardCode, 
      ys.name AS yardName, 
      al.to, 
      al.chassis_number, 
      al.seal_number, 
      al.delivery_date, 
      asd.name as allocationStatus, 
      al.status, 
      u.name AS createdBy,  
      al.datetime 
      FROM
        {$this->tblprefix}allocations al 
        LEFT JOIN {$this->tblprefix}destinations ds ON ds.id = al.destination_id 
        LEFT JOIN {$this->tblprefix}yards ys ON ys.id = al.yard_id 
        LEFT JOIN {$this->tblprefix}allocation_statuses asd ON asd.id = al.allocation_status_id 
        LEFT JOIN {$this->tblprefix}users u ON u.id = al.created_by  
      WHERE
        al.status = 'A' ");

    if (is_string($input['searchBy'])) {
      array_push($query, 
        "AND (al.container_number LIKE '%{$input['searchBy']}%' OR al.to LIKE '%{$input['searchBy']}%' 
        OR al.chassis_number LIKE '%{$input['searchBy']}%' OR al.seal_number LIKE '%{$input['searchBy']}%')");
    }
    if ($input['sortBy']) array_push($query, "ORDER BY `{$input['sortBy']}` {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);

    $allocations = $this->AllocationsModel->getAllAllocations($query);
  
    if ($exportAsExcel == 'export') {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Container#');
      $sheet->setCellValue('B1', 'destination Name');
      $sheet->setCellValue('C1', 'destination Code');
      $sheet->setCellValue('D1', 'Yard Name');
      $sheet->setCellValue('E1', 'Yard Code');
      $sheet->setCellValue('F1', 'To');
      $sheet->setCellValue('G1', 'Chassis#');
      $sheet->setCellValue('H1', 'Seal#');
      $sheet->setCellValue('I1', 'Delivery Date');
      $sheet->setCellValue('J1', 'Allocation Status');
      $sheet->setCellValue('K1', 'created By');
      $sheet->setCellValue('L1', 'Created On');
      $rows = 2;
      foreach ($allocations as $val){
        $sheet->setCellValue('A' . $rows, $val['container_number']);
        $sheet->setCellValue('B' . $rows, $val['destinationName']);
        $sheet->setCellValue('C' . $rows, $val['destinationCode']);
        $sheet->setCellValue('D' . $rows, $val['yardName']);
        $sheet->setCellValue('E' . $rows, $val['yardCode']);
        $sheet->setCellValue('F' . $rows, $val['to']);
        $sheet->setCellValue('G' . $rows, $val['chassis_number']);
        $sheet->setCellValue('H' . $rows, $val['seal_number']);
        $sheet->setCellValue('I' . $rows, $val['delivery_date']);
        $sheet->setCellValue('J' . $rows, $val['allocationStatus']);
        $sheet->setCellValue('K' . $rows, $val['createdBy']);
        $sheet->setCellValue('L' . $rows, $val['datetime']);
        $rows++;
      }
      $fileName = $this->utility->generateRandomString('allocations-export-sheet-'.date('dmY')) . '.xlsx';
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      $writer->save('php://output');
      exit;

    } else {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array(
        'status' => true,
        'data' => array('items' => $allocations));
      $this->response($output, $httpCode);
    }
  }
}
