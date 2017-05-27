<?php

namespace core\exception\auth;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of login errors.
 * @author Marc Bredt
 */
class LoginException extends Exception {

  const LOGIN_INVALID_CREDENTIALS = 0;

  public $eid = "0006";

  /**
   * Create a LoginException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Authentication error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." LOGIN_INVALID_CREDENTIALS)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." LOGIN_INVALID_CREDENTIALS)", 
                                  $code, $previous); break;
    }

  }

  /**
   * Get string representation and stack trace for this exception.
   * @return exception message and stack trace
   */
  public function __toString() {
    return __CLASS__." [".$this->code."]: { ".$this->message." }";
  }

}

?>
