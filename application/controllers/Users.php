<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

      private $end_point;

      public function __construct() {
          parent::__construct();
          $this->load->helper('url');
          $this->load->database();
          $this->end_point = $this->config->item('api_url') . '/users';
      }

      public function index(){

          $this->validateToken();

          if ($this->input->method(true) === 'GET'){

                if($this->uri->segment(2)==null || $this->uri->segment(3)==null){
                    $url = $this->end_point;
                    if(!empty($this->uri->segment(2))) {
                      $userid = $this->uri->segment(2);
                      $url .= "/". $userid;
                    }

                    $jwt = $this->getJWT();
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                      'Content-Type: application/x-www-form-urlencoded',
              				'Authorization: Bearer '.$jwt->token
                    ));

                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response_json = curl_exec($ch);
                    curl_close($ch);
                    echo $response_json;
              }
              else if($this->uri->segment(3)=="courses"){
                  //$this->getEnrolledCourses();
                  $userid = $this->uri->segment(2);

                  if($this->getUser($userid))
                      $this->getEnrolledCourses($userid);
                  else {
                       $data["success"]=false;
                       $data["data"]= new StdClass();
                       $data["data"]->message = "User does not exist";

                       echo json_encode($data);
                       set_status_header(409);
                       exit();
                  }
              }
              else if($this->uri->segment(3)=="link"){
                $userid = $this->uri->segment(2);

                if($this->getUser($userid))
                    $this->getLink($userid,true);
                else {
                     $data["success"]=false;
                     $data["data"]= new StdClass();
                     $data["data"]->message = "User does not exist";

                     echo json_encode($data);
                     set_status_header(409);
                     exit();
                }
              }

          }
          else if($this->input->method(true) === 'POST'){

              $userid = 0;
              if(!empty($this->uri->segment(2))) {
                  $userid = $this->uri->segment(2);

                  if(!empty($this->uri->segment(3)) && $this->uri->segment(3) == "enrol") {
                      if($this->getUser($userid))
                          $this->enrol($userid);
                      else {
                           $data["success"]=false;
                           $data["data"]= new StdClass();
                           $data["data"]->message = "User does not exist";

                           echo json_encode($data);
                           set_status_header(409);
                           exit();
                      }
                  }
                  else {
                      set_status_header(405);
                  }
              }
              else {
                  $this->addUser();
              }

          }
          else{
              //405 Method Not Allowed
              set_status_header(405);
          }
      }

      private function getJWT(){

            $url = 'https://dev.retailschool.it/wsite/wp-json/jwt-auth/v1/token';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, (array( "username" => "admin", "password" => "rTl20_sC20hl" )));

            $response_json = curl_exec($ch);
            curl_close($ch);

            $JWT_data = json_decode($response_json);

            return $JWT_data;
      }

      private function searchUser($email){
          $url = $this->end_point.'?search='.$email;
          $JWT = $this->getJWT();
          $ch = curl_init($url);

          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer '.$JWT->token
          ));
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

          $response_json = curl_exec($ch);
          $user_obj = json_decode($response_json);

          if(!empty($user_obj)){
              return $user_obj[0];
          }

          return $user_obj;
      }

      private function getUser($id){
          $url = $this->end_point.'/'.$id;
          $JWT = $this->getJWT();
          $ch = curl_init($url);

          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer '.$JWT->token
          ));
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

          $response_json = curl_exec($ch);
          $user_obj = json_decode($response_json);

          if(!isset($user_obj->id)){
              return null;
          }

          return $user_obj;
      }

      private function addUser(){
          $data=array();

          $url = $this->end_point;




          if($this->input->post('email')==null){
              $data["success"]=false;
              $data["data"]= new StdClass();
              $data["data"]->message = "Missing email";

              echo json_encode($data);
              set_status_header(409);
              exit();
          }

          if(!filter_var($this->input->post('email'), FILTER_VALIDATE_EMAIL)){
              $data["success"]=false;
              $data["data"]= new StdClass();
              $data["data"]->message = "Invalid email";

              echo json_encode($data);
              set_status_header(409);
              exit();
          }

          $jwt = $this->getJWT();
          $user = $this->searchUser($this->input->post('email'),$jwt->token);
          $new_user=false;

          if(empty($user)){
            if($this->input->post('firstname')==null){
                $data["success"]=false;
                $data["data"]= new StdClass();
                $data["data"]->message = "Missing firstname";

                echo json_encode($data);
                set_status_header(409);
                exit();
            }

            if($this->input->post('lastname')==null){
                $data["success"]=false;
                $data["data"]= new StdClass();
                $data["data"]->message = "Missing lastname";

                echo json_encode($data);
                set_status_header(409);
                exit();
            }

              $new_user=true;
              $gen_password = bin2hex(random_bytes(64));
              $body_params = array();
              $body_params['first_name'] = $this->input->post('firstname');
              $body_params['last_name'] = $this->input->post('lastname');
              $body_params['email'] = $this->input->post('email');
              $body_params['username'] = $this->input->post('email');
              $body_params['password'] = $gen_password;
            //  $body_params['meta'] = new StdClass;
            $body_params['meta']=array();
              if($this->input->post('address'))
                $body_params['meta'][]= array("address"=>$this->input->post('address'));
              $body_params['meta'][]= array("source"=>"DM_USER");
              //$body_params['meta']['source'] = "DM_USER";
              $ch = curl_init($url);

              curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer '.$jwt->token
              ));
              curl_setopt($ch, CURLOPT_POST, true);
              curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body_params));
              curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
              $response_json = curl_exec($ch);
              $response = (array) json_decode($response_json);
              curl_close($ch);

              $data = array();





          }
          else {
              $response = (array) $user;
          }

          $user_fields = new StdClass();
          if(!isset($response["id"])) {
              set_status_header(409);
              $data["success"]=false;
              $user_fields = $response;
          }
          else{
              $data["success"]=true;

              $user_fields->id = $response["id"];
              $user_fields->name = $response["name"];


              //$user_fields->id = $response["id"];
              //$user_fields->id = $response["id"];


              if($new_user){
                  $user_fields->password = $gen_password;
                  $user_fields->new_user = true;
                  $user_link = $this->getLink($response["id"],false);
                  $user_fields->link = $user_link["data"]->link;
                //  die(var_dump($user_link["data"]->restore_link));
                //  $response["link"] = $user_link["data"]->restore_link;
                //  $response["password"] = $gen_password;
                //  $response["new_user"] = true;
              }
          }

          $data["data"]=$user_fields;
          echo json_encode($data);
      }

      private function getEnrolledCourses($userid){
            $url = 'https://dev.retailschool.it/wsite/wp-json/ldlms/v1/users/'.$userid.'/courses';
            //$url = 'wp-json/wp/v2/ldlms/v1/users/'.$userid.'/courses';

            $ch = curl_init($url);
            $jwt = $this->getJWT();

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/x-www-form-urlencoded',
              'Authorization: Bearer '.$jwt->token
            ));

            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);
            curl_close($ch);

            if(isset($response->data->status) && $response->data->status!=200){
                $data["success"]=false;
                set_status_header(409);
            }
            else
                $data["success"]=true;
            $data["data"]=$response;

            echo json_encode($data);
      }

      private function getLink($userid,$json_print=false){

            $url = 'https://dev.retailschool.it/wsite/wp-json/em-custom/v1/pwdlink/'.$userid;

            $ch = curl_init($url);
            $jwt = $this->getJWT();

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/x-www-form-urlencoded',
              'Authorization: Bearer '.$jwt->token
            ));

            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);
            curl_close($ch);

            if(isset($response->data->status) && $response->data->status!=200){
                $data["success"]=false;
                set_status_header(409);
            }
            else
                $data["success"]=true;
            $data["data"]=$response;

            if($json_print)
              echo json_encode($data);
            else
              return $data;

      }

      private function enrol($userid){
            $url = 'https://dev.retailschool.it/wsite/wp-json/ldlms/v1/users/'.$userid.'/courses';
            //$url = 'wp-json/wp/v2/ldlms/v1/users/'.$userid.'/courses';

            $ch = curl_init($url);
            $jwt = $this->getJWT();

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/x-www-form-urlencoded',
              'Authorization: Bearer '.$jwt->token
            ));

            if($this->input->post('course_id')==null){
                $data["success"]=false;
                $data["data"]= new StdClass();
                $data["data"]->message = "Missing course_id";

                echo json_encode($data);
                set_status_header(409);
                exit();
            }

            $courseid = $this->input->post('course_id');
            $body_params = array();
            $body_params['course_ids'] = array($courseid);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body_params));
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $response_json = curl_exec($ch);
            $response = json_decode($response_json);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if($httpcode!=200){
                $data["success"]=false;
                set_status_header(409);
            }
            else
                $data["success"]=true;
            $data["data"]=$response;

            echo json_encode($data);

      }

      private function validateToken(){
          $auth_token = $this->input->get_request_header('Em-Token', TRUE);

          if($auth_token){
              $this->db->select('token.*');
              $this->db->from('token');
              $this->db->where("access_token",$auth_token);
              $query = $this->db->get();
              //$query = $this->db->get_where('token','access_token',$auth_token);

              if($query) {
                  $token = $query->row();
                  if($token){
                    //  die(var_dump());
                      if(time() - strtotime($token->timestamp_upd) < $this->config->item('max_seconds')){

                      }
                      else {
                          $data["success"]=false;
                          $data["data"]= new StdClass();
                          $data["data"]->message = "access_token exipired";

                          echo json_encode($data);
                          set_status_header(401);
                          exit();
                      }
                  }
                  else {
                      $data["success"]=false;
                      $data["data"]= new StdClass();
                      $data["data"]->message = "access_token not valid";

                      echo json_encode($data);
                      set_status_header(401);
                      exit();
                  }
              }
              else {
                  $data["success"]=false;
                  $data["data"]= new StdClass();
                  $data["data"]->message = "access_token not valid";

                  echo json_encode($data);
                  set_status_header(401);
                  exit();
              }
          }
          else {
              $data["success"]=false;
              $data["data"]= new StdClass();
              $data["data"]->message = "access_token is missing";

              echo json_encode($data);
              set_status_header(401);
              exit();
          }
      }



}
