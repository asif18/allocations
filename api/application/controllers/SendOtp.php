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

use function _\filter;

class SendOtp extends REST_Controller {

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
    $this->load->library('Sms');
    $this->load->model('UserModel');
    $this->load->model('InstanceModel');
    $this->load->model('OtpModel');
    $this->load->model('SmsModel');
  }
  
  /**
	 * Connect to Instance
	 *
	 * @paraam $ip, $username, $password
	 * @type IP, String, $String
	 */
	private function connect($ip, $username, $password) {
    log_message('info', 'instance - connecting to - '.$ip);
		$this->routerosapi->debug = (ENVIRONMENT === 'development');
		return $this->routerosapi->connect($ip, $username, $password);
  }

  /**
	 * Disconnect from Instance
	 *
	 * @param null
	 */
	private function disconnect() {
		$this->routerosapi->disconnect();
  }

  /**
   * URL: /sendOtp
   * Method: POST
   */
  public function index_post() {
    log_message('info', 'sendOtp_post');
    $decodedToken = AUTHORIZATION::validateToken();
    $acceptedKeys = array('room*', 'name*', 'phone*', 'autoPassword*', 'password*', 'profile*', 'dataUsageLimit*', 
      'checkOutTime*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    $userInfo = AUTHORIZATION::validateUser($decodedToken->id);
    $instance = AUTHORIZATION::validateMikInstance($userInfo);

    if(!is_numeric($input['phone']) && (strlen($input['phone']) !== 10)) {
      $output = array('status' => false);
      $httpCode = REST_Controller::HTTP_OK;
      log_message('error', 'invalid phone number passed - ' . $input['phone']);
      $this->response($output, $httpCode);
    }

    /**
     * Convert dataUsageLimit to bytes
     */
    $dataUsageLimitSplit = explode(' ', $input['dataUsageLimit']);
    
    switch($dataUsageLimitSplit[1]) {
      case 'MB':
        $dataUsageLimitInBytes = $dataUsageLimitSplit[0]*1048576;
      break;
      
      case 'GB':
        $dataUsageLimitInBytes = ($dataUsageLimitSplit[0]*1024)*1048576;
      break;

      default:
        $dataUsageLimitInBytes = 0;
      break;
    }

    $password = ($input['autoPassword']) ? $this->utility->generateRandomString(5) : base64_decode($input['password']);

    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));

    /**
     * Remove active user
     */
    $this->routerosapi->write('/ip/hotspot/active/print', true);
    $activeUsers = $this->routerosapi->read(true);
    
    $activeUserElement = filter($activeUsers, ['user' => $input['room']['name']]);
    foreach ($activeUserElement as $key) {
      $this->routerosapi->write('/ip/hotspot/active/remove', false);
      $this->routerosapi->write('=.id='.$key['.id']);
      $this->routerosapi->read();
    }

    /**
     * Reset the counters (usage)
     */
    $this->routerosapi->write("/ip/hotspot/user/reset-counters", false);
    $this->routerosapi->write("=.id=".$input['room']['.id']);
    $this->routerosapi->read();

    $data = array('password' => $password,
								  'profile' => $input['profile'],
                  'limit-bytes-total' => $dataUsageLimitInBytes,
                  '.id' => $input['room']['.id']);
    $this->routerosapi->comm("/ip/hotspot/user/set", $data);
    $this->routerosapi->write("/ip/hotspot/user/enable", false);
    $this->routerosapi->write("=.id=".$input['room']['.id']);
    $this->routerosapi->read();
    
    /**
     * Schedule auto checkout job
     */      
    $bridgeInfo = $this->routerosapi->comm('/system/scheduler/print', array(
      '.proplist' => '.id',
      '?name' => 'checkout_'.$input['room']['name']
    ));
    if (count($bridgeInfo) > 0) {
      $this->routerosapi->comm('/system/scheduler/remove', array(
        '.id' => $bridgeInfo[0]['.id'],
      ));

      $this->routerosapi->comm('/system/scheduler/remove', array(
        'name' => 'checkout_'.$input['room']['name'],
      ));
    }
    
    $params = array(
      'name' => 'checkout_'.$input['room']['name'],
      'start-date' => date('M/d/Y', strtotime($this->timenow.'+'.$input['checkOutTime'])),
      'start-time' => date('H:i:s', strtotime($this->timenow.'+'.$input['checkOutTime'])),
      'interval'=> '0s',
      'on-event' => 
        'ip hotspot active remove [find user='.$input['room']['name'].']; ip hotspot user disable [find name='.$input['room']['name'].']'
    );
    $this->routerosapi->comm('/system/scheduler/add', $params);

    $this->disconnect();

    $clientId = ($userInfo['role'] === SUPERADMIN_STAFF || $userInfo['role'] == CLIENTADMIN_STAFF) 
      ? $userInfo['parent_id'] : $userInfo['id'];
    
    /**
     * Save user info
     */
    $wifiUser = $this->UserModel->getWiFiUser(array(
      'mobile_number' => $input['phone'],
      'client_id' => $clientId
    ));

    if(is_array($wifiUser) && count($wifiUser) > 0) {
      $this->UserModel->updateWiFiUser(array(
        'username' => $input['room']['name'],
        'name' => $input['name'],
        'password' => $password,
        'last_visit_date' => $this->timenow,
        'otp_sent_on' => $this->timenow,
      ),
      array(
        'mobile_number' => $input['phone'],
        'client_id' => $clientId
      ));
      $wifiUserId = $wifiUser['id'];
    } else {
      $wifiUserId = $this->UserModel->insertWiFiUser(array(
        'client_id' => $clientId,
        'vendor_id' => 0,
        'username' => $input['room']['name'],
        'mobile_number' => $input['phone'],
        'name' => $input['name'],
        'password' => $password,
        'last_visit_date' => $this->timenow,
        'otp_sent_on' => $this->timenow,
        'created_date' => $this->timenow
      ));
    }

    $smsTemplateData  = array('username' => $input['room']['name'], 'password' => $password);
    
    /**
     * Fetch the SMS template
     */
    $smsTemplates  = $this->SmsModel->getSmsTemplate(array(
      'template_for' => 'SMS',
      'client_id' => $clientId,
      'status' => 'ACT'
    ));

    $smsTemplate = (is_array($smsTemplates) && count($smsTemplates) > 0) ? $smsTemplates['template'] : 
      'Welcome to iberrywifi.in , Your user name is : [username] , your password is : [password]';
    $message = str_replace('[username]', $smsTemplateData['username'], $smsTemplate);
    $message = str_replace('[password]', $smsTemplateData['password'], $message);
    $this->sms->sendSms($userInfo, 'OTP', $input['phone'], $message);
    log_message('info', 'sendOtp_post OTP sent for - '.$input['room']['name']);

    /**
     * Insert SMS/OTP Log
     */
    $smsMessageLogData = array(
      'client_id' => $clientId,
      'wifi_user_id' => $wifiUserId,
      'username' => $input['room']['name'],
      'password' => ($input['autoPassword']) ? base64_encode($password) : base64_decode($input['password']),
      'room_no' => $input['room']['name'],
      'profile' => $input['profile'],
      'sms_message' => $message,
      'sent_by' => $userInfo['id'],
      'datetime' => $this->timenow
    );

    $this->OtpModel->insertLog($smsMessageLogData);

    $output = array(
      'status' => true,
      'message' => 'Username and Password Sent Successfully');
    $httpCode = REST_Controller::HTTP_OK;
    $this->response($output, $httpCode);
  }
}
