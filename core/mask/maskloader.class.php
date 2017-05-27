<?php

namespace core\mask;
use core\object\LoggableObject as LoggableObject;
use core\util\param\Validator as Validator;


/**
 * Includes a mask. It still throws a warning message if include failed.
 * Currently it is suppressed with "@" but further improvement could implement
 * error handling replacement (check the link below).
 * @see http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
 * @author Marc Bredt
 */
class MaskLoader extends LoggableObject {

  public static function load($mask = null, $template = null, $type = "php") {

   
    $target = "../core/mask";
 
    if(Validator::isa($type,"string") && is_dir($target."/".$type))
      $target .= "/".$type;

    if(Validator::isa($mask,"string") && is_dir($target."/".$mask))
      $target .= "/".$mask;

    if(Validator::isa($template,"string") 
       && file_exists($target."/".$template.".".$type)) {
      $target .= "/".$template.".".$type;
    }
 
    // try to include the target, for development reasons "@" currently notset 
    // but it should be in conjunction with the notes above
    try { 
      include($target);

    } catch(Exception $e) {
      $this->log(__METHOD__."(".__LINE__."): %, mask=%, template=%, type=%", 
                 array(new MaskException("mask not found"),$mask,$template,$type));
      throw(new MaskException("mask not found"));
    }
 
  }

}

?>
