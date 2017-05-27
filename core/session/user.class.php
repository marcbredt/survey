<?php

namespace core\session;
use core\object\SerializableObject as SerializableObject;
use core\util\param\Validator as Validator;

class User extends SerializableObject {

  protected $fname = null;

  protected $lname = null;

  protected $email = null;
  
  protected $pass = null;
  
  protected $auth = false;

  public function __construct($user = "", $pass = "") {
    
    if(Validator::isa($user,"string"))
      $this->user = $user;
    if(Validator::isa($pass,"string"))
      $this->pass = $pass;
  
  }

  public function set_auth($auth = false) {
 
    if(Validator::isa($auth,"boolean"))
      $this->auth = $auth;

  }

  public function get_auth() {
    return $this->auth;
  }

  public function get_email() {
    return $this->email;
  }

  public function set_email($email = "") {
    if(Validator::isa($email,"string")) $this->email = $email;
  }

  public function get_pass() {
    return $this->pass;
  }

  public function set_pass($pass = "") {
    if(Validator::isa($pass,"string")) $this->pass = $pass;
  }

  public function get_fname() {
    return $this->fname;
  }

  public function set_fname($fname = "") {
    if(Validator::isa($fname,"string")) $this->fname = $fname;
  }

  public function get_lname() {
    return $this->lname;
  }

  public function set_lname($lname = "") {
    if(Validator::isa($lname,"string")) $this->lname = $lname;
  }

}

?>
