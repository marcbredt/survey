<?php

namespace core\exception\handler;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\exception\auth\LoginException as LoginException;

/**
 * This class is used to handle any exception thrown.
 * @author Marc Bredt
 */
class ExceptionHandler {
 
  /**
   * Invoke a logger and register an exception handler. Pins the session id
   * onto the exception handler.
   */
  public function __construct() {

    // set the exception handler
    set_exception_handler(array($this,'handle'));

  }

  public function handle($e = null) {

    // make the language object available to the exception handler 
    global $language, $filelogger, $session;

    // log
    $filelogger->log(__METHOD__.": exception=%, trace=%", 
                    array($e, $e->getTraceAsString()), "ERR");

    // error id 
    $eid = (array_key_exists("eid", 
              get_class_vars(get_class($e))) ? $e->eid : "0000");
    
    // build the complete exception message
    $msg = "";
    if(isset($language)) {
      $msg = $language->get("error")." (".$eid."): ".get_class($e)."<br>". 
             $language->get("sessionid").": ".session_id()."<br>".
             $language->get("trace").": ".
               preg_replace("/#/", "<br>&nbsp;&nbsp;&nbsp;#", 
                            $e->getTraceAsString());
    } else {
      $msg = "Error (".$eid."): ".get_class($e)."<br>". 
             "Session-ID: ".session_id()."<br>".
             "Stack-Trace: ".
               preg_replace("/#/", "<br>&nbsp;&nbsp;&nbsp;#", 
                            $e->getTraceAsString());
    }
    // remove doc root string from trace
    $docroot = substr($_SERVER["DOCUMENT_ROOT"],0,
                      strrpos($_SERVER["DOCUMENT_ROOT"],"/"));
    $docroot = preg_replace("/\//","\\/", $docroot);
    $msg = preg_replace("/".$docroot."/","",$msg);

    // black some critical traces
    $msg = self::black_trace($msg);

    // store errors in the session for further procession
    $session->set("error", "eid", $eid);
    $session->set("error", "exception", get_class($e));
    $session->set("error", "tracemsg", $msg);

    // set "trace" for specific exceptions like pdo but not for credentials
    $filelogger->log(__METHOD__."(".__LINE__."): class=%", array(get_class($e)));
    $traceables = array(
                   "PDOException"
                   // "core\\exception\\auth\\LoginException"
                  );
    if(Validator::in(get_class($e),$traceables)) {
      $session->set("error", "trace", true);
    }

    // flush the output buffer to be able to redirect, otherwise a 
    // header already sent warning/exception is thrown
    ob_end_clean(); 
    // redirect to a clean state beware of loops when an error always
    // occurs e.g. when the database is down
    header("Location: /");

  }

  public static function get_message($eid = "0000") {

    global $session;

    if(!Validator::matches($eid,"/[0-9]{4}/")) $eid = "0000";

    $x = new XMLDocument("../conf/errors.xml","../conf/dtd/errors.dtd", true);
    return $x->xpath("string(//error[@eid=\"".$eid."\"]/".
                       $session->get("language").")");

  }

  private function black_trace($msg = "") {
    $rs = array(
            array("/PDO->__construct\\(.*\\)\\n/","PDO->__construct()\n")
          );
    foreach($rs as $r) {
      $msg = preg_replace($r[0],$r[1],$msg);
    }

    return $msg; 
  }

}

?>
