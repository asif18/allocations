<?php

/**
 * REST
 * @type Class (Library)
 * @name 'REST'
 * @description: REST API Library
 * 
 * Developed by: Mohamed Asif
 * Date: 05/09/2021
 * Email: mohamedasif18@gmail.com
 */

class REST {

  /**
   * Var declarations
   */
  private $ci;

  /**
   * Class Contructor
   */
	public function __construct () {
	  $this->ci =& get_instance();
	}

  private function getHTTPMethod() {
    return $this->ci->input->method();
  }

  public function output($output, $httpStatusCode = 200, $contentType = 'application/json') {
    return $this->ci->output
        ->set_content_type($contentType)
        ->set_status_header($httpStatusCode)
        ->set_output($output);
  }
}
?>