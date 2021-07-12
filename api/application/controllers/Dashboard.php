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

class Dashboard extends REST_Controller {

  /**
	 * Var declarations
	 */
  private $tblprefix;

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }

    $this->tblprefix = $this->db->tblprefix;
    $this->load->model('AllocationsModel');
    $this->load->model('DestinationsModel');
    $this->load->model('YardsModel');
  }
  
  /**
   * URL: /dashboard
   * Method: GET
   */
  public function index_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $query = "SELECT COUNT(1) as count FROM {$this->tblprefix}allocations WHERE status != 'DEL' ";
    $allAllocations = $this->AllocationsModel->getAllAllocations($query);

    $query = "SELECT COUNT(1) as count FROM {$this->tblprefix}allocations WHERE status = 'NAL' ";
    $notAllocated = $this->AllocationsModel->getAllAllocations($query);

    $query = "SELECT COUNT(1) as count FROM {$this->tblprefix}allocations WHERE status = 'ALC' ";
    $allocated = $this->AllocationsModel->getAllAllocations($query);

    $query = "SELECT COUNT(1) as count FROM {$this->tblprefix}allocations WHERE status = 'DLY' ";
    $delivered = $this->AllocationsModel->getAllAllocations($query);

    $query = "SELECT COUNT(1) as count FROM {$this->tblprefix}yards WHERE status = 'A' ";
    $yards = $this->YardsModel->getAllYards($query);

    $query = "SELECT COUNT(1) as count FROM {$this->tblprefix}destinations WHERE status = 'A' ";
    $destinations = $this->DestinationsModel->getAllDestinations($query);

    $data = array(
      'allAllocationsCount' => (INT)$allAllocations[0]['count'],
      'notAllocatedCount' => (INT)$notAllocated[0]['count'],
      'allocatedCount' => (INT)$allocated[0]['count'],
      'deliveredCount' => (INT)$delivered[0]['count'],
      'yardsCount' => (INT)$yards[0]['count'],
      'destinationsCount' => (INT)$destinations[0]['count'],
    );
    
    $output = array(
      'status' => true,
      'data' => $data
    );

    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }
}
