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

class Auth extends REST_Controller {

  public function __construct() {
    parent::__construct();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }

    $this->load->model('UserModel');
  }

  /**
   * URL: /auth
   * Method: POST
   */
  public function index_post() {
    $acceptedKeys = array('username*', 'password*', 'remember');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $httpCode = REST_Controller::HTTP_OK;
    $userInfo = $this->UserModel->getUser(array('username' => $input['username']));
    $isValidPassword = password_verify(base64_decode($input['password']), $userInfo['password']);

    if(!$userInfo || !$isValidPassword) {
      $output = array(
        'status' => false,
        'message' => 'invalid credentials');
      $this->response($output, $httpCode);
    }

    if ($userInfo['status'] !== 'ACT') {
      $output = array(
        'status' => false,
        'message' => 'Your account is blocked neither invalid');
      $this->response($output, $httpCode);
    }

    $tokenData = array(
      'id' => $userInfo['id'],
      'timestamp' => now()
    );
    $output = array(
      'status' => true,
      'accessToken' => AUTHORIZATION::generateToken($tokenData),
      'message' => 'authentication successfull'
    );
    $this->response($output, $httpCode);
  }

  /**
   * URL: /auth/verifyPageAccess
   * Method: GET
   */
  public function verifyPageAccess_get($pageName = null) {

    if (is_null($pageName)) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      log_message('error', 'invalid page name passed - ' . $pageName);
      $this->response($output, $httpCode);
    }

    $decodedToken = AUTHORIZATION::validateToken();

    if (!$decodedToken) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      log_message('error', 'invalid token passed - ' . AUTHORIZATION::getAuthorizationHeader());
      $this->response($output, $httpCode);
    }

    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $pagesAndRoles = $this->getPagesAndRequiredRoles();

    if (is_array($userInfo) && 
      isset($pagesAndRoles[$pageName]) && 
      in_array($userInfo['role'], $pagesAndRoles[$pageName])) {
      $output = array('status' => true);
      $httpCode = REST_Controller::HTTP_OK;
      $this->response($output, $httpCode);
    }
    
    $output = array('status' => false);
    $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
    log_message('error', 'tried to tresspass page - ' . $pageName .' - '. AUTHORIZATION::getAuthorizationHeader());
    $this->response($output, $httpCode);
  }

  /**
   * Private getPagesAndRequiredRoles
   * Description: returns page names and its required access role
   */
  private function getPagesAndRequiredRoles() {
    return array(
      'dashboard'         => [SUPERADMIN, SUPERADMIN_STAFF, STAFF],
      'allocations'       => [SUPERADMIN, SUPERADMIN_STAFF, STAFF],
      'allocationsList'   => [SUPERADMIN, SUPERADMIN_STAFF, STAFF],
      'yards'             => [SUPERADMIN, SUPERADMIN_STAFF],
      'destinations'      => [SUPERADMIN, SUPERADMIN_STAFF],
      'generalSettings'   => [SUPERADMIN, SUPERADMIN_STAFF]
    );
  }

  /**
   * Private getAccessLevels
   * Description: return access levels for user roles
   */
  private function getAccessLevels($role) {
    return array(
      'canRemoveRoom' => ($role === SUPERADMIN || $role === CLIENTADMIN)
    );
  }

  /**
   * URL: /auth/getUserInfo
   * Method: GET
   */
  public function getUserInfo_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if (!$decodedToken) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      log_message('error', 'invalid token passed - ' . AUTHORIZATION::getAuthorizationHeader());
      $this->response($output, $httpCode);
    }

    $settings = json_decode($userInfo['settings'], true);
    

    $userData = array(
      'id'        => (int)$userInfo['id'],
      'name'      => $userInfo['name'],
      'email'     => $userInfo['email'],
      'username'  => $userInfo['username'],
      'role'      => $userInfo['role'],
      'type'      => $userInfo['type'],
      'access'    => $this->getAccessLevels($userInfo['role'])
    );
    $output = array(
      'status' => true,
      'data' => $userData
    );
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }

  /**
   * URL: /auth/getMenuItems
   * Method: GET
   */
  public function getMenuItems_get() {
    $decodedToken = AUTHORIZATION::validateToken();
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);

    if (!$decodedToken) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_UNAUTHORIZED;
      log_message('error', 'invalid token passed - ' . AUTHORIZATION::getAuthorizationHeader());
      $this->response($output, $httpCode);
    }

    $menuItems = array(
      array(
        'order' => 1,
        'name' => 'dashboard',
        'path' => '/panel/dashboard',
        'caption' => 'Dashboard',
        'icon' => 'dashboard',
        'class' => '',
        'isDisabled' => false,
        'options' => array('exact' => true)
      ),
      array(
        'order' => 2,
        'path' => null,
        'caption' => 'Allocations',
        'icon' => 'local_shipping',
        'class' => '',
        'isDisabled' => false,
        'isSubMenuOpen' => false,
        'subMenus' => array(
          array(
            'name' => 'allocations',
            'path' => '/panel/allocations',
            'caption' => 'Allocations',
            'icon' => 'photo_filter',
            'class' => '',
            'isDisabled' => false
          ),
          array(
            'name' => 'allocationsList',
            'path' => '/panel/allocations-list',
            'caption' => 'Allocations list',
            'icon' => 'meeting_room',
            'class' => '',
            'isDisabled' => false
          ),
        )
      ),
      array(
        'order' => 13,
        'path' => null,
        'caption' => 'Settings',
        'icon' => 'settings_applications',
        'class' => '',
        'isDisabled' => false,
        'isSubMenuOpen' => false,
        'subMenus' => array(
          array(
            'name' => 'generalSettings',
            'path' => '/panel/general-settings',
            'caption' => 'General',
            'icon' => 'keyboard_hide',
            'class' => '',
            'isDisabled' => false
          )
        )
      )
    );

    if ($userInfo['role'] === SUPERADMIN || $userInfo['role'] === SUPERADMIN_STAFF) {
      array_push($menuItems, array(
        'order' => 3,
        'name' => 'yards',
        'path' => '/panel/yards',
        'caption' => 'Yards',
        'icon' => 'pages',
        'class' => '',
        'isDisabled' => false,
        'options' => array('exact' => true)
      ),
      array(
        'order' => 4,
        'name' => 'destinations',
        'path' => '/panel/destinations',
        'caption' => 'Destinations',
        'icon' => 'location_city',
        'class' => '',
        'isDisabled' => false,
        'options' => array('exact' => true)
      ));
    }
    
    $output = array(
      'status' => true,
      'data' => $menuItems
    );
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }
}
