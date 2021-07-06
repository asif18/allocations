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

class RouterAuth extends REST_Controller {

  private $timenow;
  private $tblprefix;
  private $allocatedUsage = '524288000';

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
    $this->load->model('SmsModel');
    $this->load->model('OtpModel');
    $this->load->model('WifiUsageModel');
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
   * URL: /routerAuth/dependecyDetails
   * Method: POST
   */
  public function dependecyDetails_post() {
    log_message('info', 'dependecyDetails_post');
    $acceptedKeys = array('instanceId*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    
    $instance = $this->InstanceModel->getInstance('id = '.$input['instanceId']);
    $clientQuery = "SELECT otp_login FROM " . $this->tblprefix . "users WHERE id = ".$instance['user_id'];
    $client = $this->UserModel->getAllUsers($clientQuery);

    if (count($client) !== 1) {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }
    
    $dependanceData = array(
      'isOtpLoginEnabled' => $this->utility->parseTinyIntToBoolean($client[0]['otp_login']),
      'mikLanIp' => $instance['mik_lan_ip'],
			'destinationUrl' => $instance['destination'],
			'loginScreenSettings' => json_decode($instance['wifi_user_settings'])
    );
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'data' => $dependanceData);
    $this->response($output, $httpCode);
  }

  /**
   * URL: /routerAuth/login
   * Method: POST
   */
	public function login_post() {
    log_message('info', 'login_post');
    $acceptedKeys = array('instanceId*', 'name', 'mobileNumber*', 'email', 'macAddress*', 'cache*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    
    $instance = $this->InstanceModel->getInstance('id = '.$input['instanceId']);
    $clientQuery = "SELECT id, parent_id, role, sms_gateway FROM " . $this->tblprefix . "users WHERE id = ".$instance['user_id'];
		$client = $this->UserModel->getAllUsers($clientQuery);
		$wifiUserSettings = json_decode($instance['wifi_user_settings'], true);
		
		if (count($client) !== 1) {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }
    
    if($input['mobileNumber'] === '' || $input['macAddress'] === '') {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false);
      $this->response($output, $httpCode);
		}

		//Set null for undefined values from POST
		forEach($input as $key => $value) {
			if (!$input[$key]) {
				$input[$key] = null;
			}
		}
		
		/**
		 * Generate Password
		 */
		$password = $this->utility->generateRandomString();
		
		/**
		 * Generate OTP
		 */
		$otp = substr(strtotime(date('Y-m-d H:i:s')), 5, 10);
		
		/**
		 * Generate Username for Mik
		 */
		$userMikUsername = $input['mobileNumber']. '_' .str_replace(":", "", $input['macAddress']);
    $this->connect($instance['mik_ip'], $instance['mik_username'], base64_decode($instance['mik_password']));
    $mikUser = $this->getMikUser($userMikUsername);

		/**
		 * User Duplication Control
		 *
		 * Fetch the user by MAC Address
		 */
		$condition = "mac_address = '" . $input['macAddress'] . "' AND client_id = " .$client[0]['id'];
    $wifiUser = $this->UserModel->getWiFiUser($condition);
		
		if(!isset($wifiUser)) {
			/**
			 * Insert user data to wifi user table
			 */
			$insertData = array(
								'client_id' => $client[0]['id'], 
								'mac_address' => $input['macAddress'],
								'username' => $userMikUsername, 
								'mobile_number' => $input['mobileNumber'], 
								'email' => $input['email'], 
								'name' => $input['name'], 
                'password' => $password, 
                'otp' => $otp,
								'status' => 'A',
								'last_visit_date' => $this->timenow,
								'created_date' 	=> $this->timenow
							);
			$wifiUserId = $this->UserModel->insertWiFiUser($insertData);
		} else {
			if(isset($mikUser['uptime'])) {
        // Check the user already logged-in on a single day more than 1.5 hours
			  $last_visit_date = explode(' ', $wifiUser['last_visit_date']);
				$uptime = $mikUser['uptime'];
				$time = explode(':', $uptime);
				$usage = ($time[0]*60) + $time[1];
				
				if($usage >= 90 && ( date('Y-m-d') == $last_visit_date[0] ) ) {
					$httpCode = REST_Controller::HTTP_OK;
          $output = array(
            'status' => false,
            'data' => 'You have exceeded your daily limit (90 Mins)'
          );
          $this->response($output, $httpCode);
				}
			}
			
			/**
			 * Update user data
			 */
			$updateData = array(
								'username' => $userMikUsername, 
								'client_id' => $instance['user_id'], 
								'mobile_number' => $input['mobileNumber'], 
								'email' => $input['email'], 
								'name' => $input['name'], 
                'password' => $password,
                'otp' => $otp,
								'last_visit_date'	=> $this->timenow
							);
			
			$condition  = array('mac_address' => $input['macAddress']);
			$wifiUserId = $this->UserModel->updateWiFiUser($updateData, $condition);
		}
		
		/**
		 * Add/Update user in MikroTik
		 */
		// Set limit-uptime to 30 Mins
		$time = strtotime(date('H:i:s'));
		$limitUptime = date("H:i:s", strtotime('+30 minutes', $time));
		
		/**
     * Convert dataUsageLimit to bytes
     */
    $dataUsageLimitSplit = explode(' ', $wifiUserSettings['dataUsageLimit']);
    
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

		$startDate = date('M/d/Y', strtotime('+ 1 days'));
		$validTill = date('M/d/Y', strtotime('+ ' . $wifiUserSettings['validTill']));
		$comment = 'zzz ' . strtolower($startDate) . ' ' . strtolower($validTill) . ' ' . explode(' ', $wifiUserSettings['validTill'])[0];
		
		if (count($mikUser) <= 0) {
			/**
			 * Insert into MikroTik
			 */
			$mikData = array(
								'name' => $userMikUsername,
								'password' => $password, 
								'email' => $input['email'],
								'profile' => $wifiUserSettings['profile'],
								'limit-bytes-total' => $dataUsageLimitInBytes,
								'comment' => $comment
							);
			
			$this->addMikUser($mikData);

			/**
			 * Hit DNS API if a new user created in router box
			 */
			if ($instance['mik_dns_ip'] !== '') {
				$dnsPort = '';
				if ($instance['mik_dns_port']) {
					$dnsPort = ':' . $instance['mik_dns_port'];
        }
				file_get_contents('http://' . $instance['mik_dns_ip'] . $dnsPort . '/api/user/add/' . $userMikUsername . '/122.0.0.0');
			}
			
			/**
			 * Then enable that user
			 */
			$this->enableMikUser($userMikUsername);
		} else {
			/**
			 * Then remove that active user
			 */
			$this->removeActiveMikUser($userMikUsername);
			
			/**
			 * Then enable that user
			 */
			$this->enableMikUser($userMikUsername);
			
			/**
			 * Update user with latest data
			 */
			$mikData = array(
        'name' => $userMikUsername,
				'password' => $password,
				'profile' => $wifiUserSettings['profile'],
				'limit-bytes-total' => $dataUsageLimitInBytes,
				'comment' => $comment
      );
			
			$this->updateMikUser($mikData);
		}
		
		// Disconnect from MikroTik
		$this->disconnect();
  
    $smsTemplateData  = array('otp' => $otp);
    
    /**
     * Fetch the SMS template
     */
    $smsTemplates  = $this->SmsModel->getSmsTemplate(array(
      'template_for' => 'SMS',
      'client_id' => $client[0]['id'],
      'status' => 'ACT'
    ));

    $smsTemplate = (is_array($smsTemplates) && count($smsTemplates) > 0) ? $smsTemplates['template'] : 
      'OTP is [otp] : Welcome to http://iberrywifi.in';
    $message = str_replace('[otp]', $smsTemplateData['otp'], $smsTemplate);
    $this->sms->sendSms($client[0], 'OTP', $input['mobileNumber'], $message);
    log_message('info', 'checkIn_post OTP sent for - '.$userMikUsername);

    /**
     * Insert SMS/OTP Log
     */
    $smsMessageLogData = array(
			'client_id' => $client[0]['id'],
			'otp' => $otp,
      'wifi_user_id' => $wifiUserId,
      'username' => $userMikUsername,
      'password' => $password,
      'room_no' => null,
			'profile' => $wifiUserSettings['profile'],
			'allocated_usage' => $dataUsageLimitInBytes,
      'sms_message' => $message,
      'sent_by' => $client[0]['id'],
      'datetime' => $this->timenow
    );

    $this->OtpModel->insertLog($smsMessageLogData);
    
    $httpCode = REST_Controller::HTTP_OK;
    $output = array(
      'status' => true,
      'message' => 'OTP Sent successfully'
    );
    $this->response($output, $httpCode);
  }

  /**
   * URL: /routerAuth/validatOtp
   * Method: POST
   */
	public function validateOtp_post() {
    log_message('info', 'validateOtp_post');
    $acceptedKeys = array('instanceId*', 'macAddress*', 'otp*', 'cache*');
    $input = $this->post();
    AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
    
    $instance = $this->InstanceModel->getInstance('id = '.$input['instanceId']);
    $clientQuery = "SELECT id FROM " . $this->tblprefix . "users WHERE id = ".$instance['user_id'];
    $client = $this->UserModel->getAllUsers($clientQuery);
		
		if (count($client) !== 1) {
			$httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false);
      $this->response($output, $httpCode);
		}

		if( count( $instance ) <= 0 ) {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }
    
    if($input['otp'] === '' || $input['macAddress'] === '') {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false);
      $this->response($output, $httpCode);
    }

    /**
		 * Fetch the user by MAC Address
		 */
		$condition = "mac_address = '" . $input['macAddress'] . "' AND client_id = '" .$client[0]['id'] . "' ORDER BY last_visit_date DESC LIMIT 1";
		$wifiUser = $this->UserModel->getWiFiUser($condition);
		
		if(count($wifiUser) <= 0) {
			$httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false, 'message' => 'Invalid user');
      $this->response($output, $httpCode);
    }
    
    if ($wifiUser['otp'] != $input['otp']) {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false, 'message' => 'Invalid OTP');
      $this->response($output, $httpCode);
    }

    if (time() - strtotime($wifiUser['last_visit_date']) > 15 * 60) {
      // 15 mins has passed
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false, 'message' => 'OTP has been expired. Please create a new one');
      $this->response($output, $httpCode);
    }
    
    if($wifiUser['otp'] == $input['otp']) {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array(
        'status' => true,
        'data' => array(
          'mikLanIp' => $instance['mik_lan_ip'],
          'username' => $wifiUser['username'],
          'password' => $wifiUser['password'],
          'destination' => $instance['destination']
        )
      );
      $this->response($output, $httpCode);
    }
  }
  
  /**
	 * Logout
	 *
	 * @param $_GET
	 */
	public function logout_get() {
    $acceptedKeys = array('instanceId*', 'userName*', 'macAddress*', 'bytesIn*', 'bytesOut*', 'totalBytes*', 'ipAddress*',
      'totalSessionTime*');
		$input = $this->get();
		log_message('info', 'logout_get');
		AUTHORIZATION::validateRequestInput($acceptedKeys, $input);
		
    $instance = $this->InstanceModel->getInstance('id = '.$input['instanceId']);
    $clientQuery = "SELECT id FROM " . $this->tblprefix . "users WHERE id = ".$instance['user_id'];
    $client = $this->UserModel->getAllUsers($clientQuery);
		
		if(empty($instance)) {
      $httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false, 'message' => 'Invalid action');
      $this->response($output, $httpCode);
    }
    
    /**
		 * Fetch the user by MAC Address
		 */
		$condition = "mac_address = '" . $input['macAddress'] . "' AND client_id = " .$client[0]['id'];
		$wifiUser = $this->UserModel->getWiFiUser($condition);
		
		if (empty($wifiUser)) {
			$httpCode = REST_Controller::HTTP_OK;
      $output = array('status' => false, 'message' => 'Invalid wifi user');
      $this->response($output, $httpCode);
    }
		
		$query = "SELECT profile FROM {$this->tblprefix}otp_log WHERE wifi_user_id = " . $wifiUser['id'] . " 
			AND client_id = ". $client[0]['id'] . " ORDER BY datetime DESC LIMIT 1";
		$otpInfo = $this->OtpModel->getAllOtpLogs($query)[0];

		$loggedInTime = date("Y-m-d H:i:s", strtotime($this->timenow) - $input['totalSessionTime']);
	
		$wifiUsageLog = array(
								'client_id' => $client[0]['id'],
								'wifi_user_id' => $wifiUser['id'],
								'username' => $input['userName'],
								'profile' => $otpInfo['profile'],
								'allocated_usage' => $otpInfo['allocated_usage'],
								'bytes_in' => $input['bytesIn'],
								'bytes_out' => $input['bytesOut'],
								'total_usage' => $input['totalBytes'],
								'datetime' => $this->timenow,
								'uptime' => $input['totalSessionTime'],
								'ip_address' => $input['ipAddress'],
								'mac_address'	=> $input['macAddress'],
								'login_time'	=> $loggedInTime,
								'logout_time'	=> $this->timenow
							);
								
		$this->WifiUsageModel->insertWifiUsageLog($wifiUsageLog);
    $httpCode = REST_Controller::HTTP_OK;
    $output = array('status' => true, 'message' => 'Success, User checkedout from WiFi successfully');
    $this->response($output, $httpCode);
  }
  
  /**
	 * Get Mik User
	 *
	 * @param $name
	 * @type String
	 */
	private function getMikUser($name) {
		$this->routerosapi->write('/ip/hotspot/user/print', false);
		$this->routerosapi->write('=.proplist=.id', false);
		$this->routerosapi->write('?name='.$name);
		return $this->routerosapi->read();
  }
  
  /**
	 * Add Mik User
	 *
	 * @param $data
	 * @type Array
	 */
	private function addMikUser($data) {
		$this->routerosapi->comm("/ip/hotspot/user/add", $data);
	}
	
	/**
	 * Update Mik User
	 *
	 * @param $data
	 * @type Array
	 */
	private function updateMikUser($data) {
		$this->routerosapi->write("/ip/hotspot/user/print", false);
		$this->routerosapi->write("=.proplist=.id", false);
		$this->routerosapi->write("?name=".$data["name"]);
		$a = $this->routerosapi->read();
		if(count($a) > 0) {
			$data['.id'] = $a[0]['.id'];
			$this->routerosapi->comm("/ip/hotspot/user/set", $data);
		}	
  }
  
  /**
	 * Remove Active Mik User
	 *
	 * @param $name
	 * @type String
	 */
	private function removeActiveMikUser($name) {
		$this->routerosapi->write("/ip/hotspot/active/print", false);
		$this->routerosapi->write("=.proplist=.id", false);
		$this->routerosapi->write("?user=".$name);
		$a = $this->routerosapi->read();
		if(count($a) > 0) {
			$this->routerosapi->write("/ip/hotspot/active/remove", false);
			$this->routerosapi->write("=.id=".$a[0][".id"]);
			$this->routerosapi->read();
		}
	}
	
	/**
	 * Remove Mik User
	 *
	 * @param $name
	 * @type String
	 */
	private function removeMikUser($name) {
		$this->routerosapi->write("/ip/hotspot/user/print", false);
		$this->routerosapi->write("=.proplist=.id", false);
		$this->routerosapi->write("?name=".$name);
		$a = $this->routerosapi->read();
		if(count($a) > 0) {
			$this->routerosapi->write("/ip/hotspot/user/remove", false);
			$this->routerosapi->write("=.id=".$a[0][".id"]);
			$this->routerosapi->read();
		}
	}
	
	/**
	 * Enable Mik User
	 *
	 * @param $name
	 * @type String
	 */
	private function enableMikUser($name) {
		$this->routerosapi->write('/ip/hotspot/user/print', false);
		$this->routerosapi->write('=.proplist=.id', false);
		$this->routerosapi->write('?name='.$name);
		$a = $this->routerosapi->read();
		if(count($a) > 0) {
			$this->routerosapi->write('/ip/hotspot/user/set', false);
			$this->routerosapi->write('=.id='.$a[0]['.id'], false);
			$this->routerosapi->write('=disabled=no');
			$this->routerosapi->read();
		}
	}
	
	/**
	 * Disable Mik User
	 *
	 * @param $name
	 * @type String
	 */
	private function disableMikUser($name) {
		$this->routerosapi->write('/ip/hotspot/user/print', false);
		$this->routerosapi->write('=.proplist=.id', false);
		$this->routerosapi->write('?name='.$name);
		$a = $this->routerosapi->read();
		if(count($a) > 0) {
			$this->routerosapi->write('/ip/hotspot/user/set', false);
			$this->routerosapi->write('=.id='.$a[0]['.id'], false);
			$this->routerosapi->write('=disabled=yes');
			$this->routerosapi->read();
		}
  }
  
  /**
	 * Disconnect MikroTik
	 *
	 * @param null
	 */
	private function disconnect() {
		$this->routerosapi->disconnect();
	}
}
