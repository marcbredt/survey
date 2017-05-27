<?php

namespace core\db\data;
use core\object\LoggableObject as LoggableObject;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\param\Validator as Validator;
use core\db\connect\MySQLConnector as MySQLConnector;
use core\db\stmt\Statement as Statement;
use core\db\stmt\StatementBatch as StatementBatch;
use core\db\exec\Executor as Executor;

class DataGetter extends LoggableObject {

  private $c = null;
  
  private $e = null;

  private $b = null;

  public function __construct($object = null) {

    // setup the logger 
    parent::__construct(get_class($this));
 
    $x = new XMLDocument("../conf/databases.xml",
                         "../conf/dtd/databases.dtd",
                         true);

    $this->c = new MySQLConnector($x,"local");
    $this->e = new Executor($this->c->get_connection());

      /*
      $s1 = new Statement("select version();");
      $res1 = $e->execute($s1);

      $s2 = new Statement("select * from langtext ".
                          "where lab = :lang and ltid >= 69;");
      $s2->set_params(array(":lang" => "de"));
      $res2 = $e->execute($s2);

      // set $s2->sql= null to use $e->statement
      $s2->__construct(null,array(":lang" => "en"));
      $res3 = $e->execute($s2);

      $sb = new StatementBatch();
      $sb->add($s1);
      $s2->__construct("select * from langtext ".
                       "where lab = :lang and ltid >= 69;",
                       array(":lang" => "de"));
      $sb->add($s2);
      $s2->set_params(array(":lang" => "en"));
      $sb->add($s2);
      $res4 = $e->batch($sb, false);
      */

    // create a statement batch for statement trees, $object is an XMLDocument
    if(Validator::isclass($object, "core\util\xml\XMLDocument")) {
      $this->b = new StatementBatch();
      $this->log(__METHOD__."(".__LINE__."): xd=%", array($object));
      $nodes = $object->get_doc()->documentElement;
      $this->build_batch($nodes);
      $this->log(__METHOD__."(".__LINE__."): batch=%", array($this->b));
    }

  }

  private function build_batch($nodes = null) {

   foreach($nodes->childNodes as $n) {

     if(Validator::isclass($n,"DOMText") 
        && !Validator::isempty(trim($n->wholeText))) { 

       $this->log(__METHOD__."(".__LINE__."): processing statement=%, ".
                    "query=%, provides=%",
                  array($n->parentNode->getAttribute("name"), 
                        trim($n->wholeText),
                        ($n->parentNode->hasAttribute("provides") ? 
                           $n->parentNode->getAttribute("provides") : "")));
       
       // create a statement with empty parameters for the moment
       $s = new Statement(trim($n->wholeText),array(),
                          explode(",", $n->parentNode->getAttribute("provides")));

       $this->b->add($s);
     }

     if($n->hasChildNodes()) $this->build_batch($n);
     
    }

  }

  public function get($params = array(), $transact = true) {

    // setup initial dummy parameters to invoke the batch execution for empty
    // arrays as batch itertion iterates over parmeter arrays
    if(!Validator::isa($params,"array") || count($params)==0) 
      $this->b->get()[0]->set_params(array(":dummy"=>":dummy"));
    else $this->b->get()[0]->set_params($params);

    // execute the (cumber)batch :)
    return $this->e->batch($this->b, $transact);  

  }

}

?>
