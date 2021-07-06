<?php

/**
 * Sms
 * @type Class (Library)
 * @name 'Sms'
 * @description: SMS Library
 * 
 * Developed by: Mohamed Asif
 * Date: 08/30/2021
 * Email: mohamedasif18@gmail.com
 */

class Sms {
	
	/**
	 * Send SMS
	 * @param $clientInfo, $smsFor, $to, $message
	 * @type Array, String, Number(10), String(50)
	 */
	public function sendSms($clientInfo, $smsFor, $to, $message) {
		
		switch($clientInfo['sms_gateway']) {
			
			case 'TEXTLOCAL':
				$this->sendSmsViaTextLocal($to, $message);
			break;

			case 'VIDEOCON':
				$this->sendSmsViaVideocon($to, $message);
			break;
		}

		$clientId = ($clientInfo['role'] === SUPERADMIN_STAFF || $clientInfo['role'] == CLIENTADMIN_STAFF) 
      ? $clientInfo['parent_id'] : $clientInfo['id'];
		$this->logSmsCount($clientId, $smsFor, 1, $clientInfo['sms_gateway']);
	}

	/**
	 * Log SMS Count
	 * @param $clientId, $smsFor, $smsVendor
	 * @type Array, Number, String
	 */
	private function logSmsCount($clientId, $smsFor, $smsCount, $smsVendor) {
		$CI =& get_instance();
		$CI->load->model('SmsModel');
		$CI->SmsModel->logSmsCount($clientId, $smsFor, $smsCount, $smsVendor);
	}

	/**
	 * Send SMS Via Videocon
	 * 
	 * @param $to, $message
	 * @type Number(10), String(50)
	 */
	private function sendSmsViaVideocon ($to, $message) {
		$username = 'iberrytransac';
		$password = 'iberry@123';
		$senderId = 'IBERRY';
		$messageType = 'text';
		$message	= urlencode($message);

		$arrContextOptions = array(
			'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
				),
		);
		$url_sms = 'https://bulksmsapi.vispl.in/?username='.$username.'&password='.$password.'&messageType='.$messageType.'&mobile='.$to.'&senderId='.$senderId.'&message='.$message;
		return file_get_contents($url_sms, false, stream_context_create($arrContextOptions));
	}

	/**
	 * Send SMS Via Text Local
	 * @param $to, $message
	 * @type Number(10), String(50)
	 */
	private function sendSmsViaTextLocal($to, $message) {
		$apiKey = urlencode('SJ08SZeT7Yg-u5xuhtV0Cl6XuafPzkOaiVfpDS2PCC');
		$sender = urlencode('IBERRY');
		
		$numbers = $to;
		if (is_array($numbers)) {
			$numbers = implode(',', $numbers);
		}
		$numbers = urlencode($numbers);
		$message = rawurlencode($message);
		$data = 'apikey=' . $apiKey . '&numbers=' . $numbers . "&sender=" . $sender . "&message=" . $message;
		$ch = curl_init('https://api.textlocal.in/send/?' . $data);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);
		return $response;
	}
}

?>
