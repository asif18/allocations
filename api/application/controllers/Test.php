<?php
/**
 * Copyrights Allocations 2021. All rights reserved
 * 
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your 
 * business/personal perpose and not to sell or modify
 */
use function _\each;

class Test extends CI_Controller {
  
  /**
   * Class Constructor
   */
  public function __construct() {
    parent::__construct();
  }
   
  public function index() {
   
    $options = [
      'cost' => 12,
    ];
    echo $pass = password_hash('admin', PASSWORD_BCRYPT, $options);
    echo '<br/>';
    echo password_verify('admin', $pass);
    phpinfo();
    echo $this->forgotPasswordEmailTemplate('name', '2114');
 }

 public function forgotPasswordEmailTemplate($name, $password) {
  return '<style>
    .mail {
      font-family:Arial, Helvetica, sans-serif;
      font-size:13px;
      color:#000000;
      background:#FFF;
      border:dashed 2px #ec407a;
      padding:5px;
      width:100%;
    }
    .line {
      border-top:3px solid #1794b7;
    }
    .talign_center {
      text-align:center;
    }
    </style>
    <table class="mail">
    <tr>
      <td>Hi '.$name.',</td>
    </tr>
    <tr>
      <td>Below is your new password</td>
    </tr>
    <tr>
      <td><p>&nbsp;</p></td>
    </tr>
    <tr>
      <td colspan="2"><strong>'.$password.'</strong></td>
    </tr>
    <tr>
      <td><p>&nbsp;</p></td>
    </tr>
    <tr>
      <td colspan="2"><div class="line"></div></td>
    </tr>
    </table>';
  }
}
