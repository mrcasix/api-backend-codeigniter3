<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

  public function __construct() {
      parent::__construct();
      $this->load->helper('url');
  }
  public function index(){
      echo "Hello to Hamza Yamine api v1";
  }
}
