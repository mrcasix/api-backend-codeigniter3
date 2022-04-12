<?php

defined('BASEPATH') OR exit('No direct script access allowed');

define("TIMEOUT_TOKEN", 3600);


class Auth extends CI_Controller{
	public function __construct(){
		parent::__construct();
        $this->load->database();
        $this->load->helper('url'); 
		$this->load->library('shared_functions');
	}

	public function index(){
		$shared_functions = new shared_functions();
		$log_record_content = $shared_functions->sf_get_request_content();
		$log_user_upd = $this->input->post('debug') !==null ? 'DEBUG' : 'APILOG';
		$ret_msg = $shared_functions->sf_save_log('api_log',__CLASS__ .'/'.__FUNCTION__,'request',$log_record_content,$log_user_upd);

		if ($this->input->method(true) === 'POST'){
			$auth_token = $this->input->get_request_header('CX-Authorization', TRUE);
			if($auth_token){

				$api_response = $this->_get_token($auth_token);

				if(!$api_response['success'] === true){
					set_status_header(500,'Error generating token.');
					$api_response['success'] = false;
					$api_response['data'] = '500 Error generating token.';
				}

				//START FIX - documentazione errata
				//Nella documentazione abbiamo scritto che la chiave contenente il token si chama "token"
				//dal server OAUTH arriva come "access_token"
				//if(array_key_exists('access_token', $api_response['data']))
				//{
				//	$api_response['data']['token'] = $api_response['data']['access_token'];
				//	unset($api_response['data']['access_token']);
				//}
				//END FIX

				//echo json_encode($api_response,JSON_PRETTY_PRINT);
			}
			else{
				//400 Bad Request
				set_status_header(400,'Missing CX-Authorization header or post data.');
				$api_response['success'] = false;
				$api_response['data'] = '400 Missing CX-Authorization header or post data.';
			}
		}
		else{
			//405 Method Not Allowed
			set_status_header(405);
			$api_response['success'] = false;
			$api_response['data'] = '405 Method Not Allowed.';
		}
		
		$log_record_content = json_encode($api_response,JSON_PRETTY_PRINT);
		$log_user_upd = $this->input->post('debug') !==null ? 'DEBUG' : 'APILOG';
		$ret_msg = $shared_functions->sf_save_log('api_log',__CLASS__ .'/'.__FUNCTION__,'response',$log_record_content,$log_user_upd);
		
		echo json_encode($api_response,JSON_PRETTY_PRINT);
	}
  
	
	function _get_token($authorization_hash = null){
		//DEBUG: simulazione errori

		//CX-Authorization = YmZkN2RlOWRmNDg3ODM5MGNiY2UzNjFjOjY3YTJlYTI5YTU1NmMzMGU5MGYzZjhmOWJlMDFmN2NlYWQ0YzEzOTI1YjM1OWJiNzY2YTAyNGYzYTlhMzRhZDk6aXBob25lSGFtemEtMTMz


		//$client_id = 'MAZ1_c383b8e7-930b-481b-a5bc-5dde5181fee5';
		//$client_secret = '1o6iKtaALRvC6DMK2I0VYUliDhoQBFnzFDdAlPMU';
		//$device_id = '';
		//Em-Authorization = 'YzM4M2I4ZTctOTMwYi00ODFiLWE1YmMtNWRkZTUxODFmZWU1OjFvNmlLdGFBTFJ2QzZETUsySTBWWVVsaURob1FCRm56RkRkQWxQTVU=';
		
		$decoded_authorization_hash = explode(':',base64_decode($authorization_hash));
		$client_id = $decoded_authorization_hash[0];
		$client_secret = $decoded_authorization_hash[1];
		$device_info = $decoded_authorization_hash[2];

		$response = $this->_oidc_get_token($client_id, $client_secret, $device_info);
		$data = $response;
		
		$em_response = array();
		if(isset($data['access_token']))
			$em_response['success'] = true;
		else
			$em_response['success'] = false;
		
		$em_response['data'] = $data;

		/*
		if(isset($em_response['data']['access_token'])){
			$response['client_id'] = $client_id;
			$ret_msg = $this->_save_token('token',$response,'APILOG');
		}
		*/
		
		return $em_response;
	}	
	
	function _oidc_get_token($p_client_id, $p_client_secret, $p_device_info){

		$credentials = $this->db->get_where('auth_app_access', array('client_id' => $p_client_id, 'client_secret' => $p_client_secret))->row();
		$data = array();

		$data['access_token'] = "";
		$data['status'] = "failed";

		if($credentials){

			$auth_device_access = $this->db->get_where('auth_device_access', array('device_info' => $p_device_info, 'auth_app_access' => $credentials->id))->row();

			if(empty($auth_device_access)){
				$auth_device_access = new StdClass();
				$auth_device_access->device_info = $p_device_info;
				$auth_device_access->auth_app_access = $credentials->id;
				$this->db->insert('auth_device_access',$auth_device_access);
				$auth_device_access->id = $this->db->insert_id();
			}

			$auth_oidc_token = array();
			$auth_oidc_token['auth_device_access'] = $auth_device_access->id;
			$auth_oidc_token['token'] = $this->generate_oidc_token();
			$auth_oidc_token['first_access'] = time();
			$auth_oidc_token['duration'] = TIMEOUT_TOKEN;
			$this->db->insert('auth_oidc_token', $auth_oidc_token);

			$data['status'] = 'success';
			$data['access_token'] = $auth_oidc_token['token'];
			$data['client_id'] = $credentials->client_id;
			$data['first_access'] = $auth_oidc_token['first_access'];
			$data['duration'] = $auth_oidc_token['duration'];
			$data['device_info'] = $auth_device_access->device_info;

		}
			
		return $data;

	}
	
	/*
	function _save_token($table_name, $record_content, $user_upd = null){
		$token_record = array();
		$token_record['client_id'] = $record_content['client_id'];
		$token_record['access_token'] = $record_content['access_token'];
		//$token_record['token_type'] = $record_content['token_type'];
		$token_record['expires_in'] = $record_content['duration'];
		//$token_record['user_upd'] = $user_upd;
		$token_record['timestamp_upd'] = date('Y-m-d H:i:s');
		
		$this->db->insert($table_name, $token_record);
		
		return true;
	}
	*/

	function generate_oidc_token(){
		return bin2hex(random_bytes(32));
	}

	public function print_secret_id(){
		$secret = $this->generate_oidc_token();
		$id = bin2hex(random_bytes(12));
		echo "client id:".$id."<br>";
		echo "client secret:".$secret."<br>";
	}
}
