<?php

namespace core\lang;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\param\Validator as Validator;
use core\object\LoggableObject as LoggableObject;
use core\exception\lang\LanguageException as LanguageException;

class Language extends LoggableObject {

  private $lxfile = null;

  private $ldfile = null;

  private $lang = null;

  private $langset = null;
 
  public function __construct($lang = null) {

    global $session, $filelogger;

    // load default/available/active languages from core config
    $x = new XMLDocument("../conf/core.xml","../conf/dtd/core.dtd");
    $default = $x->xpath("string(//languages/@default)");
    $languages = preg_replace("/name=|\"/", "", 
                   preg_replace("/ /", "|",
                     $x->xpath("//languages/language[@active='y']/@name")));

    // if an invalid lang was passed set the configured default
    if(Validator::isa($lang,"null")) $this->lang = $default; 
    $this->lang = $lang;

    // if an invalid language string was passed or 
    if(!Validator::matches($this->lang, "/[a-z]{2}/") 
       || !Validator::matches($this->lang, "/(".$languages.")/")
       || !file_exists("../conf/lang/".$this->lang.".xml")) {
      $filelogger->log(__METHOD__."(".__LINE__."): %, lang '%' not found in ".
                   "langs=(%), defaulting to '%'", 
                 array(new LanguageException("language not available"),
                       $this->lang, $languages, $default),"ERR");

      // if a wrong default language was configured choos the first from lang
      if(!Validator::matches($default,"/(".$languages.")/")) 
        $this->lang = substr($languages,0,2);
      else $this->lang = $default;
    }

    // log language information
    $this->lxfile = "../conf/lang/".$this->lang.".xml";
    $this->ldfile = "../conf/dtd/lang.dtd";
    $filelogger->log(__METHOD__."(".__LINE__."): languages=(%), ".
                 "lang set to '%', lxfile=%, ldfile=%", 
               array($languages, $this->lang, $this->lxfile, $this->ldfile));

    // store the language into the session for further processing
    $session->set("language",null,$this->lang);
  }

  public function load() {
  
    global $filelogger; 
    $this->langset = array();
    
    $x = new XMLDocument($this->lxfile,$this->ldfile);
    $ed = $x->xpath("//language[@id='".$this->lang."']",true)->get_doc();
    foreach($ed->documentElement->childNodes[0]->childNodes as $node) {
      $filelogger->log(__METHOD__."(".__LINE__."): lang element, name=%, value=%",
                 array($node->getAttribute("name"), trim($node->textContent)));
      $this->langset[trim($node->getAttribute("name"))] = trim($node->textContent);
    }

  }

  public function get($element = null) {

    if(Validator::isa($this->lang,"null"))
      $this->load();

    return (isset($this->langset[$element]) ? $this->langset[$element] : "-");

  }

  public function set($key = "", $value = "") {

    global $filelogger; 

    if(Validator::isa($key,"string") && Validator::isa($value,"string")
       && !Validator::isempty($key) && !Validator::isempty($value)) {
      $filelogger->log(__METHOD__."(".__LINE__."): setting language key='%' with value='%'", 
                 array($key,$value));
      array_push($this->langset, $key);

    } else {
      $filelogger->log(__METHOD__."(".__LINE__."): %", 
                 array(new LanguageException("loading '".$key."'='".$value.
                         "' failed",2)));
      throw(new LanguageException("loading '".$key."'='".$value."' failed",2));

    }

  }

}

?>
