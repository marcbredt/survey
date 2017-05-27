<?php

namespace core\object;
use core\util\log\Loggable as Loggable;
use core\util\log\FileLogger as FileLogger;

/**
 * Class to pin logging functionality onto any objects.
 * This is an intermediate stage as PHP does not support multiple inheritages.
 * @author MarcBredt
 * @throws ParamNotArrayException
 */
class LoggableObject extends FileLogger implements Loggable {

  public function __construct($class = "Unknown") {
    parent::__construct($class);
  }

}

?>
