<?php

namespace core\util\param;
use core\util\string\StringUtil as StringUtil;

/**
 * This class provides static methods to verify parameters passed onto any
 * object or method.
 * @author Marc Bredt
 */
class Validator {

  /**
   * This function is used to check the type of any object.
   * @param $object object which' type is going to be checked
   * @param $type type $object should be of
   * @return true if $object matches the $type spec, otherwise false
   */
  public static function isa($object = null, $type = "") {
    return (strncmp(strtolower(gettype($object)),
              strtolower($type),strlen($type))==0);
  }

  /**
   * This function is used to check any parameter against a regular expression.
   * @param $param parameter, object or whatever which should match $regex
   * @param $regex regular expression that $param should match, $regex is 
   *               passed onto preg_match so provide a valid one
   * @return true if $param matches $regex, other false
   */
  public static function matches($param = null, $regex = "//") {
    return ((self::isa($regex,"string") 
             && StringUtil::has_layout("\/*\/",$regex)) ? 
               preg_match($regex, print_r($param,true))===1 : false);
  }

  /**
   * This function is used to check if any kind of list datatype contains
   * a specified value.
   * @param $element element which is searched in the $container provided
   * @param $container container providing (valid) elements
   * @return true if $element is in $container, otherwise false
   */
  public static function in($element = null, $container = array()) {
    return in_array($element, $container, true);
  }

  /**
   * This function is used to check if a parameter is empty.
   * @return true if $element is empty, otherwise false
   */
  public static function isempty($element = null) {
    return empty($element);
  }

  /**
   * Check the class for any object.
   * @param $object object that should be an instance of
   * @return true if $object is an instance of $class 
   */
  public static function isclass($object = null, $class = "") {
    if(self::isa($object,"object"))
      return ($object instanceof $class);
    else 
      return false;
  }
 
  /**
   * Check if two parameter equal.
   * @param $one element one
   * @param $two element two
   * @return true if the elements equal
   */
  public static function equals($one = null, $two = null) {
    return ($one === $two); 
  }

  public static function validate($type = "", $object = null) {
   
    // REMEMBER: to verify each query key againsts the regexp defined
    //           in (currently) core.xml
 
    switch($type) {
   
      case "globals": break;
      case "_server": break;
      case "_get": break;
      case "_post": break;
      case "_files": break;
      case "_request": break;
      case "_session": break;
      case "_env": break;
      case "_cookie": break;

      default: 
          if(!self::isa($object,"null")) unset($obj); 
        break;

    }
 
  }

}

?>
