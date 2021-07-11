<?php
/**
 * Copyrights 2019. All rights reserved
 * 
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your 
 * business/personal perpose and not to sell or modify
 */

class AUTHORIZATION {
  
  public static function validateTimestamp() {
    $CI =& get_instance();
    $token = self::validateToken(self::getBearerToken());
    if ($token != false && (now() - $token->timestamp < ($CI->config->item('token_timeout') * 60))) {
       return $token;
    }
    return false;
  }

  /**
   * Validate JWT Token
   */
  public static function validateToken($tokenToValidate = null, $respondBoolean = false) {
    
    if (!$tokenToValidate) $tokenToValidate = self::getBearerToken();
    $CI =& get_instance();
    

    try {
      $token = JWT::decode($tokenToValidate, $CI->config->item('jwt_key'));
    } catch(Exception $e) {
      $token = false;
      log_message('error', 'invalid token passed - ' . $e->getMessage());
    }

    if ($token) {
      if (now() - $token->timestamp < ($CI->config->item('token_timeout') * 60)) return $token;
    }
    
    if (!$respondBoolean) {
      log_message('error', 'invalid token passed - ' . self::getAuthorizationHeader());
      $CI->output->set_status_header(401);
      exit(json_encode(array('status' => false)));
    } 
    
    return false;
  }

  /**
   * Generate JWT Token
   */
  public static function generateToken($data) {
    $CI =& get_instance();
    return JWT::encode($data, $CI->config->item('jwt_key'));
  }

  /** 
   * Get header Authorization
   */
  public static function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER['Authorization']);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
      $requestHeaders = apache_request_headers();
      $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
      if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
      }
    }
    return $headers;
  }

  /**
  * Get Bearer token
  */
  private static function getBearerToken() {
    $headers = self::getAuthorizationHeader();
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }
    return null;
  }

  public static function validateRequestInput($acceptedKeys, $input) {
    $CI =& get_instance();
    $error = null;
    if (count($input) > count($acceptedKeys)) {
      $error = 'invalid input length'.count($input);
    }

    $inputKeys = array_keys($input);
    $requiredAcceptedKeys =_::pull(_::map($acceptedKeys, function($key) {
      return _::endsWith($key, '*') ? rtrim($key, '*') : null;
    }), null);
    $invalidMandatoryKeys = _::difference($requiredAcceptedKeys, $inputKeys);

    if (count($invalidMandatoryKeys) > 0) {
      $error = 'mandatory fields missing - '.implode($invalidMandatoryKeys, ',');
    }
    
    if ($error) {
      log_message('error', $error);
      $CI->output->set_status_header(400);
      exit(json_encode(array('error', $error)));
    }
    return true;
  }

  public static function validateUser($id) {
    $CI =& get_instance();
    $error = null;

    if (!$id) {
      $error = 'invalid user id';
    }

    $CI->load->model('UserModel');
    $userInfo = $CI->UserModel->getUser(array('id' => $id));

    if (!$userInfo) {
      $error = 'invalid user id';
    }

    if ($userInfo['status'] !== 'ACT') {
      $error = 'Not an active user account';
    }

    if ($error) {
      log_message('error', $id.'-'.$error);
      $CI->output->set_status_header(200);
      exit(json_encode(array('status' => false, 'message' => $error)));
    }
    return $userInfo;
  }
}
