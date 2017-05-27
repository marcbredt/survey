<?php

namespace core\session;
use core\object\LoggableObject as LoggableObject;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;

class Session extends LoggableObject {

  private $keys = null;

  public function __construct() {

    // setup the logger
    parent::__construct(get_class($this));

    // load valid keys, e.g. for tokens, user, ...
    $this->keys = array();
    $this->load();

  }

  public function has($name = "", $key = "") {

    if(Validator::isa($name,"string") && Validator::isa($key, "string") 
       && !Validator::isempty($name) && !Validator::isempty($key) 
       && $this->isvalid($name))
      return isset($_SESSION[$name][$key]);
    
    if(Validator::isa($name, "string") && !Validator::isempty($name) 
       && $this->isvalid($name))
      return isset($_SESSION[$name]);

    return false;
  }

  public function get($name = "", $key = "") {

    if($this->has($name)) {

      if (isset($_SESSION[$name][$key])) 
        return $_SESSION[$name][$key];
  
      else if(isset($_SESSION[$name])) 
        return $_SESSION[$name];

      else return null;

    }
   
    return null;

  }

  public function set($name = "", $key = null, $value = "") {
    if(Validator::isa($name,"string")) {
      if(Validator::isa($key,"string")) $_SESSION[$name][$key] = $value;
      else $_SESSION[$name] = $value;
    }
  }

  public function uset($name = "", $key = null) {
    if(Validator::isa($name,"string")) {
      if(Validator::isa($key,"string")) unset($_SESSION[$name][$key]);
      else unset($_SESSION[$name]);
    }
  }

  private function load() {
    
    $x = new XMLDocument("../conf/session.xml","../conf/dtd/session.dtd",true);
    // FIXME: xpath() creates some doubles
    $xs = $x->xpath("//session/key",true);
    $this->log(__METHOD__."(".__LINE__."): xs=%", array($xs));

    foreach($xs->get_doc()->documentElement->childNodes as $n) {

      if(Validator::isclass($n, "DOMElement") 
         && !Validator::in($n->getAttribute("name"), $this->keys)) {

        $this->keys = array_merge($this->keys, array($n->getAttribute("name")));

        // only initialize session variables if they are not set yet
        if(!isset($_SESSION[$n->getAttribute("name")])) {
 
          $this->log(__METHOD__."(".__LINE__."): \$_SESSION[ % ]", 
                     array(StringUtil::get_object_string($_SESSION)));

          if(Validator::equals($n->getAttribute("type"),"array"))
            $this->set($n->getAttribute("name"),null,array());
          else if(Validator::equals($n->getAttribute("type"),"string"))
            $this->set($n->getAttribute("name"));

        }

      }

    }

  }

  private function isvalid($name = "") {
    if(Validator::isa($name,"string")) 
      return Validator::in($name, $this->keys);
    return false;
  }

  public function __toString() {

    $session = $_SESSION;

    // black some user credentials
    unset($session["user"]);
    $session["user"] = "****";

    return get_class($this).spl_object_hash($this)."=(".
           "keys = [ ".StringUtil::get_object_string($this->keys)." ], ".
           "\$_SESSION = [ ".StringUtil::get_object_string($session)." ], ".
           " )";
  }

}

?>
