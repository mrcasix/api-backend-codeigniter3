<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Resource extends CX_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->helper('url');
  }
   
  public function today(){
      $this->validateToken();
      // put your code here
  }

}
