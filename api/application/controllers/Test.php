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

    each([1, 2, 3], function (int $item) {
      var_dump($item);
    });
 }
}
