<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CX Controller - For Api
 *
 * Controller API for application for CodeIgniter 3
 *
 * @package     CX Controller
 * @author      Hamza Yamine
 * @copyright   Copyright (c) 2022
 */

class CX_Controller extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}

	protected function validateToken(){
		$auth_token = $this->input->get_request_header('CX-Token', TRUE);

		if(!empty($auth_token)){
			
			$token = $this->db->get_where('auth_oidc_token', array('token'=>$auth_token))->row();

            if($token){

                if(time() - $token->first_access < $token->duration){
					// Do nothing let the access
                }
                else {
					/*
					$data["success"]=false;
					$data["data"]= new StdClass();
					$data["data"]->message = "access_token exipired";

					echo json_encode($data);
					set_status_header(401);
					*/
					$this->send_response(false,"access_token exipired",401);
					exit();
                }
            }
            else {
				/*
				$data["success"]=false;
				$data["data"]= new StdClass();
				$data["data"]->message = "access_token not valid";

				echo json_encode($data);
				set_status_header(401);
				*/
				$this->send_response(false,"access_token not valid",401);
				exit();
            }
             
		}
		else {
			/*
			$data["success"]=false;
			$data["data"]= new StdClass();
			$data["data"]->message = "access_token is missing";
  
			echo json_encode($data);
			set_status_header(401);
			*/
			$this->send_response(false,"access_token is missing",401);
			exit();
		}

	}

	protected function send_response($success,$message_data,$status_code=200){
		$data = array();
		$data["success"] = $success;
		$data["data"] = new StdClass();
		$data["data"]->message=$message_data;
		echo json_encode($data);
		set_status_header($status_code);
	}	
	
}
