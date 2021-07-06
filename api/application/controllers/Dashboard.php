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

use function _\invokeMap;

class Dashboard extends REST_Controller {

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }
  }
  
  /**
   * URL: /dashboard/?token=TOKEN&interface=INTERFACE
   * Method: GET
   */
  public function index_get() {
    $token = base64_decode($this->get('token'));
    $decodedToken = AUTHORIZATION::validateToken($token);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    
    $lineChartData = array(
      'rx' => [],
      'tx' => [],
      'status' => false,
      'message' => null
    );

    $data = array(
      'lineChartData' => $lineChartData
    );
    
    $output = array(
      'status' => true,
      'data' => $data
    );

    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /dashboard/getInterfaces
   * Method: GET
   */
  public function getInterfaces_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => []);
    $this->response($output, $httpCode);
  }
}
