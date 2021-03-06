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
    $acceptedKeys = array('id*', 'containerNumber*', 'destination*', 'yard*', 'to*', 'chassisNumber*', 'sealNumber*', 
      'dropDate*', 'allocationStatus*', 'isRailBill*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $query = "SELECT
      id 
      FROM
        {$this->tblprefix}allocations
      WHERE 
        container_number = '{$input['containerNumber']}'
        AND (status = 'NAL' OR status = 'ALC')";
    
    if ($input['id'] != null) {
      $query.= " AND id != ".base64_decode($input['id']);
    }

    
    $isExist = $this->AllocationsModel->getAllAllocations($query);

    if (count($isExist) > 0) {
      log_message('info', 'saveAllocation_post container exist - '.$input['containerNumber']);
      $output = array(
        'status' => false,
        'message' => 'cotainer already exist');
      $httpCode = REST_Controller::HTTP_OK;
      $this->response($output, $httpCode);
    }

    $data = array(
      'container_number'      => $input['containerNumber'],
      'destination_id'        => $input['destination'],
      'yard_id'               => $input['yard'],
      'to'                    => $input['to'],
      'chassis_number'        => $input['chassisNumber'],
      'seal_number'           => $input['sealNumber'],
      'drop_date'             => $input['dropDate'],
      'allocation_status_id'  => $input['allocationStatus'],
      'is_rail_bill'          => $input['isRailBill']
    );

    if ($input['id'] != null) {
      $data['last_updated_by'] = $userInfo['id'];
      $data['last_updated_on'] = $this->timenow;

      $allocationId = base64_decode($input['id']);
      $this->AllocationsModel->updateAllocation($data, array('id' => $allocationId));
      log_message('info', 'saveAllocation_post allocation updated - '.$allocationId);
    } else {
      $data['status'] = 'NAL';
      $data['created_by'] = $userInfo['id'];
      $data['datetime'] = $this->timenow;
      $allocationId = $this->AllocationsModel->insertAllocation($data);
      log_message('info', 'saveAllocation_post new allocation created - '.$allocationId);
    }
    
    
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

    $updatetData = array('status' => 'DEL');

    $where = array('id' => $id);
    $status = $this->AllocationsModel->updateAllocation($updatetData, $where);
    
    log_message('info', 'removeAllocation_get allocation removed - '.$id);
    $output = array(
      'status' => true,
      'message' => 'removed successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /allocations/allocate
   * Method: POST
   */
  public function allocate_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('ids*', 'openDate*', 'expiryDate*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $updateData = array(
      'open_date'         => $input['openDate'],
      'expiry_date'       => $input['expiryDate'],
      'status'            => 'ALC',
      'last_updated_by'   => $userInfo['id'],
      'last_updated_on'   => $this->timenow
    );

    foreach ($input['ids'] as $id) {
      $this->AllocationsModel->updateAllocation($updateData, array('id' => $id));
      log_message('info', 'allocate_post allocation allocated - '.$id);
    }
    
    $output = array(
      'status' => true,
      'message' => 'allocated successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /allocations/markAsDelivered
   * Method: POST
   */
  public function markAsDelivered_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('ids*', 'deliveryDate*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $updateData = array(
      'delivery_date'       => $input['deliveryDate'],
      'status'              => 'DLY',
      'last_updated_by'     => $userInfo['id'],
      'last_updated_on'     => $this->timenow,
      'delivery_updated_by' => $userInfo['id'],
      'delivery_updated_on' => $this->timenow
    );

    foreach ($input['ids'] as $id) {
      $this->AllocationsModel->updateAllocation($updateData, array('id' => $id));
      log_message('info', 'markAsDelivered_post allocation - '.$id);
    }
    
    $output = array(
      'status' => true,
      'message' => 'marked as delivered');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /allocations/markAsNotAllocated
   * Method: POST
   */
  public function markAsNotAllocated_post() {
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('ids*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      $this->response($output, $httpCode);
    }

    $updateData = array(
      'delivery_date'       => null,
      'status'              => 'NAL',
      'last_updated_by'     => $userInfo['id'],
      'last_updated_on'     => $this->timenow,
      'delivery_updated_by' => null,
      'delivery_updated_on' => null
    );

    foreach ($input['ids'] as $id) {
      $this->AllocationsModel->updateAllocation($updateData, array('id' => $id));
      log_message('info', 'markAsNotAllocated_post allocation - '.$id);
    }
    
    $output = array(
      'status' => true,
      'message' => 'moved to not allocated list');
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
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
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
   * URL: /allocations/getAllocation
   * Method: GET
   */
  public function getAllocation_get($id = null) {
    log_message('info', 'getAllocation_get');
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
      container_number,
      destination_id,
      yard_id, 
      `to`, 
      chassis_number, 
      seal_number, 
      drop_date, 
      is_rail_bill, 
      allocation_status_id 
      FROM
        {$this->tblprefix}allocations
      WHERE 
        id = '{$id}'";

    $allocation = $this->AllocationsModel->getAllAllocations($query)[0];

    if (count($allocation) <= 0) {
      $httpCode = REST_Controller::HTTP_BAD_REQUEST;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    $allocation['is_rail_bill'] = $this->utility->parseTinyIntToBoolean($allocation['is_rail_bill']);
    
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => $allocation);
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
    
    if ($userInfo['role'] !==  SUPERADMIN && $userInfo['role'] !== SUPERADMIN_STAFF && $userInfo['role'] !== STAFF) {
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
      al.drop_date, 
      al.delivery_date, 
      al.open_date, 
      al.expiry_date, 
      al.is_rail_bill, 
      asd.name as allocationStatus, 
      al.status, 
      u.name AS createdBy, 
      ud.name AS deliveryUpdatedBy, 
      al.delivery_updated_on, 
      al.last_updated_on, 
      ul.name AS lastUpdatedBy, 
      al.datetime  AS created_datetime 
      FROM
        {$this->tblprefix}allocations al 
        LEFT JOIN {$this->tblprefix}destinations ds ON ds.id = al.destination_id 
        LEFT JOIN {$this->tblprefix}yards ys ON ys.id = al.yard_id 
        LEFT JOIN {$this->tblprefix}allocation_statuses asd ON asd.id = al.allocation_status_id 
        LEFT JOIN {$this->tblprefix}users u ON u.id = al.created_by 
        LEFT JOIN {$this->tblprefix}users ud ON ud.id = al.delivery_updated_by AND al.delivery_updated_by IS NOT NULL 
        LEFT JOIN {$this->tblprefix}users ul ON ul.id = al.last_updated_by AND al.last_updated_by IS NOT NULL");

    if (is_array($input['searchBy'])) {
      array_push($query, " WHERE ");
      if (strlen($input['searchBy']['status']) > 0) {
        array_push($query, "al.status = '{$input['searchBy']['status']}' ");
      }
      if (strlen($input['searchBy']['containerNumber']) > 0) {
        array_push($query, "AND al.container_number = '{$input['searchBy']['containerNumber']}' ");
      }
      if (strlen($input['searchBy']['destination']) > 0) {
        array_push($query, "AND al.destination_id = '{$input['searchBy']['destination']}' ");
      }
      if (strlen($input['searchBy']['to']) > 0) {
        array_push($query, "AND al.to = '{$input['searchBy']['to']}' ");
      }
      if (strlen($input['searchBy']['yard']) > 0) {
        array_push($query, "AND al.yard_id = '{$input['searchBy']['yard']}' ");
      } 
    }
    if ($input['sortBy']) array_push($query, "ORDER BY al.{$input['sortBy']} {$input['sortDirection']}");
    if (is_numeric($input['startFrom'])) array_push($query, "LIMIT {$input['startFrom']}");
    if (is_numeric($input['endTo'])) array_push($query, ", {$input['endTo']}");
    
    $query = implode(' ', $query);

    $allocations = $this->AllocationsModel->getAllAllocations($query);
    if (count($allocations) > 0) {
      $allocations = array_map(function($allocation) {
        $allocation['is_rail_bill'] = $this->utility->parseTinyIntToBoolean($allocation['is_rail_bill']);
        return $allocation;
      },  $allocations);
    }

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
      $sheet->setCellValue('I1', 'IsRailBill');
      $sheet->setCellValue('J1', 'Alocation Status');
      $sheet->setCellValue('K1', 'Status');
      $sheet->setCellValue('L1', 'Drop Date');
      $sheet->setCellValue('M1', 'Delivery Date');
      $sheet->setCellValue('N1', 'Open Date');
      $sheet->setCellValue('O1', 'Expiry Date');
      $sheet->setCellValue('P1', 'Allocation Status');
      $sheet->setCellValue('Q1', 'Delivery Updated By');
      $sheet->setCellValue('R1', 'Delivery Updated On');
      $sheet->setCellValue('S1', 'Created By');
      $sheet->setCellValue('T1', 'Created On');
      $sheet->setCellValue('U1', 'Last Updated By');
      $sheet->setCellValue('V1', 'Last Updated On');
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
        $sheet->setCellValue('I' . $rows, $val['is_rail_bill']);
        $sheet->setCellValue('J' . $rows, $val['allocationStatus']);
        $sheet->setCellValue('K' . $rows, $val['status']);
        $sheet->setCellValue('L' . $rows, $val['drop_date']);
        $sheet->setCellValue('M' . $rows, $val['delivery_date']);
        $sheet->setCellValue('N' . $rows, $val['open_date']);
        $sheet->setCellValue('O' . $rows, $val['expiry_date']);
        $sheet->setCellValue('P' . $rows, $val['allocationStatus']);
        $sheet->setCellValue('Q' . $rows, $val['deliveryUpdatedBy']);
        $sheet->setCellValue('R' . $rows, $val['delivery_updated_on']);
        $sheet->setCellValue('S' . $rows, $val['createdBy']);
        $sheet->setCellValue('T' . $rows, $val['created_datetime']);
        $sheet->setCellValue('U' . $rows, $val['lastUpdatedBy']);
        $sheet->setCellValue('V' . $rows, $val['last_updated_on']);
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
