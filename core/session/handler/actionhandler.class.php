<?php

namespace core\session\handler;
use core\object\LoggableObject as LoggableObject;
use core\util\string\StringUtil as StringUtil;
use core\util\param\Validator as Validator;
use core\session\User as User;
use core\session\Session as Session;
use core\session\auth\Authenticator as Authenticator;

class ActionHandler extends LoggableObject {

  public static function handle($action = ""){
   
    global $session, $filelogger;

    // handle the actions
    switch($action) {

      case "login": 

          // check the action token
          if($session->get("tokens",$action) != $_POST['token']
             && Validator::isa($action,"string")) { 
            $filelogger->log(__METHOD__."(".__LINE__."): invalid token=%, ".
                               "expected=%", 
                             array($_POST['token'], 
                                   $session->get("tokens",$action)));
            return false;
          }
 
          $filelogger->log(__METHOD__."(".__LINE__."): handling action=%, ".
                           "\$_GET[ ".StringUtil::get_object_string($_POST)." ]",
                           array($action)); 

          if(isset($_POST["email"]) && isset($_POST["password"])) {

            // get necessary credentials from a global var $_GET/REQUEST
            $email = $_POST["email"];
            $pass = $_POST["password"];
 
            // pin em onto the sessions user object
            $user = unserialize($session->get("user")); 
            $user->set_email($email);
            $user->set_pass($pass);

            // serialize the user again
            $session->set("user",null,serialize($user));
            
          }

          $filelogger->log(__METHOD__."(".__LINE__."): session=% ", 
                           array($session)); 

          // and try to authenticate it
          Authenticator::authenticate();
          header("Location: /");

        break;
 
      case "logout":
 
          // check the action token
          if($session->get("tokens",$action) != $_POST['token']
             && Validator::isa($action,"string")) { 
            $filelogger->log(__METHOD__."(".__LINE__."): invalid token=%, ".
                               "expected=%", 
                             array($_POST['token'], 
                                   $session->get("tokens",$action)));
            return false;
          }

          // update the user, destroy the session and redirect 
          $user = unserialize($session->get("user")); 
          $user->set_auth(false);
          $session->set("user",null,serialize($user));
          session_destroy();
          header("Location: /");

        break;
            
      case "changelang":

          // check the action token
          if($session->get("tokens",$action) != $_POST['token']
             && Validator::isa($action,"string")) { 
            $filelogger->log(__METHOD__."(".__LINE__."): invalid token=%, ".
                               "expected=%", 
                             array($_POST['token'], 
                                   $session->get("tokens",$action)));
            return false;
          }
 
          // store the language in the session
          $lang = $_POST['language'];
          $session->set("language",null,$lang);
 
        break;
  
      default: break;

    }

    // remove the action token
    $session->uset("tokens",$action);
 
    return true;

  }

}

?>
