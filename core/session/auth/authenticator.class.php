<?php 

namespace core\session\auth;
use core\object\LoggableObject as LoggableObject;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\util\xml\XMLDocument as XMLDocument;
use core\session\User as User;
use core\session\Session as Session;
use core\session\ActionHandler as ActionHandler;
use core\db\data\DataGetter as DataGetter;
use core\exception\auth\LoginException as LoginException;

class Authenticator {

  public static function authenticate() {

    global $session, $filelogger;
    $user = unserialize($session->get("user"));

    if(Validator::isclass($user, "core\session\User")) {

      $x = new XMLDocument("../conf/statements.xml",
                           "../conf/dtd/statements.dtd",true);
      $xs = $x->xpath("//statement[@name='checkcred']",true);

      // avoid redirect cycles on pdo exceptions
      if(!Validator::equals($session->get("error","exception"),"PDOException")) {
        $dg = new DataGetter($xs);
        $data = $dg->get(array(":pass"=>$user->get_pass(),
                               ":email"=>$user->get_email()), false);

        if(count($data)>0 && $data[0]["valid"]==true) {
          $user->set_fname($data[0]["firstname"]);
          $user->set_lname($data[0]["lastname"]);
          $user->set_auth(true);
 
        } else {
          $filelogger->log(__METHOD__."(".__LINE__."): %", 
                           array(new LoginException("invalid credentials",0)));
          throw (new LoginException("invalid credentials",0));

        } 

        $session->set("user",null,serialize($user));

      }

    }

  }

  public static function isauthenticated() {
    global $session, $filelogger;
    $filelogger->log(__METHOD__."(".__LINE__."): session=%", array($session));
    $user = unserialize($session->get("user"));
    return (!Validator::isa($user,"null") && $user->get_auth()); 
  }

}

?>
