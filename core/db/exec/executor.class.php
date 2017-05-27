<?php

namespace core\db\exec;
use \PDO as PDO;
use core\object\LoggableObject as LoggableObject;
use core\util\param\Validator as Validator; 
use core\util\string\StringUtil as StringUtil;
use core\db\stmt\Statement as Statement;
use core\exception\db\DatabaseException as DatabaseException;

class Executor extends LoggableObject {

  /**
   * Connection to execute statements on.
   */
  private $connection = null;

  /**
   * (Prepared) Statement to run it multiple times without prepaing it again.
   */
  private $statement = null;

  /**
   * Options for (prepared) PDOStatements.
   */
  private $dops = array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY);

  /**
   * Create an Executor.
   * @param $c Connection to operate on
   * @see Connection
   */
  public function __construct($c = null) {

    // setup the logger
    parent::__construct(get_class($this));
 
    // setup the executor 
    if(Validator::isclass($c,"core\db\connect\Connection")) {
      $this->connection = $c;
    } else {
      $this->log(__METHOD__."(".__LINE__."): %, c=%", 
                 array(new DatabaseException("invalid connection"),get_class($c)));
      throw(new DatabaseException("ivalid connection"));
    }

  }
 
  /**
   * Execute a statement.
   * @param $sql sql (prepared) statement
   * @param $params parameters for the (prepared) $sql statement
   * @return an array containing the fetched results
   * @throws DatabaseException
   */
  public function execute($stmt = null) {

    $this->log(__METHOD__."(".__LINE__."): stmt=%", array($stmt));

    if(!Validator::isclass($stmt,"core\db\stmt\Statement")) {
      $this->log(__METHOD__."(".__LINE__."): batch=%, exception=%", 
                 array($stmtb, new DatabaseException("not a Statement",3)));
      throw(new DatabaseException("not a Statement",3));
    }

    $sql = $stmt->get_statement();
    $params = $stmt->get_params();

    // setup prepared statement and execute it
    if(Validator::isa($sql,"string") && Validator::isa($params,"array") 
       && count($params)>0) {
    
      $this->statement = $this->connection->get()->prepare($sql,$this->dops);
      $psexe = $this->statement->execute($params);

      if(Validator::equals($this->statement,"false") 
         || Validator::equals($psexe,false)) {
        $this->log(__METHOD__."(".__LINE__."): %, this=%, stmt=%", 
                   array(new DatabaseException(
                           "executing prepared statement failed",3),
                         $this, $stmt));
        throw(new DatabaseException("executing prepared statement failed",3));
      }

      return $this->statement->fetchAll();

    // return immediate sql execution
    } else if(Validator::isa($sql,"string") && Validator::isa($params,"array") 
              && count($params)==0) {

      return $this->connection->get()->query($sql)->fetchAll();

    // return previously set prepared statement execution
    } else if(Validator::isa($sql,"null") && Validator::isa($params,"array") 
              && count($params)>0) {

      $psexe = $this->statement->execute($params);
      if(Validator::equals($psexe,false)) {
        $this->log(__METHOD__."(".__LINE__."): %, this=%", 
                   array(new DatabaseException(
                           "executing prepared statement failed",3),
                         $this));
        throw(new DatabaseException("executing prepared statement failed",3));
      }
      return $this->statement->fetchAll();

    // otherwise there is something wrong with the parameters
    } else {
      $this->log(__METHOD__."(".__LINE__."): %, this=%", 
                 array(new DatabaseException("invalid parameters",1),$this));
      throw(new DatabaseException("invalid parameters",1));

    }

  }

  /**
   * Run a set of sql statements.
   * @param $stmtb statement batch
   * @param $transact run all statements as a transaction, defaults to true
   * @return array containing all results
   */
  public function batch($stmtb = null, $transact = true) {

    if(!Validator::isclass($stmtb,"core\db\stmt\StatementBatch")) {
      $this->log(__METHOD__."(".__LINE__."): batch=%, exception=%", 
                 array($stmtb, new DatabaseException("not a StatementBatch",4)));
      throw(new DatabaseException("not a StatementBatch",4));

    } else if(!Validator::isa($transact,"boolean")) {
      $this->log(__METHOD__."(".__LINE__."): batch=%, exception=%", 
                 array($stmtb, new ParamNotValidException("not a boolean")));
      throw(new ParamNotValidException("not a boolean"));
    }
   
    $results = array();
    
    // start a transaction
    if($transact === true) $this->connection->get()->beginTransaction();

    try {

      $last_statement = null;
      $last_results = array();
      $updated_params = array();

      foreach($stmtb->get() as $s) {

        $updated_params = array_merge( $updated_params,
          $this->get_updated_params($s, $last_statement,  $last_results) );
        $last_statement = $s;
 
        $init = true;
        foreach($updated_params as $up) {

          // get the parameter pair
          $up = array_shift($updated_params);
          // but discard the invocation dummy
          if(array_key_exists(":dummy", $up)) $up = array(); 

          $this->log(__METHOD__."(".__LINE__."): ups=%, params=%", 
                     array(StringUtil::get_object_string($updated_params),
                           StringUtil::get_object_string($up)));
          $s->set_params($up);

          if($init===true) { 
            $init = false; 
            // provides the sql for $this->statement
            $last_results = $this->execute($s); 
            // set the statements sql to null, further executions will then 
            // use $this->statement
            $s->set_statement();
          } else {
            $last_results = $this->execute($s);
          }

          $this->log(__METHOD__."(".__LINE__."): lrs=%", 
                     array(StringUtil::get_object_string($last_results)));
          $results = array_merge($results, $last_results);

        }
  
      }

    // catch any exceptions and rollback if batch is run as transaction 
    } catch(Exception $e) {

      if($this->connection->get()->inTransaction()) 
        $this->connection->get()->rollBack();

      throw($e); // pass the exception on to the ExceptionHandler 

    }
 
    // finally commit a transaction if batch was run as
    if($this->connection->get()->inTransaction()) 
        $this->connection->get()->commit();

    return $results;

  }

  /**
   * Dump this Executor.
   * @return string representing this executor.
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=(".
           "con=".$this->connection.", ".
           "pstmt=".StringUtil::get_object_string($this->statement).",".
           ")";
  }

  private function get_updated_params($stmt = null, $last_stmt = null, 
                                      $last_results = null) {

    // (probably a multidimensional) array containing param-tupel for the 
    // upcoming statement execution
    $next_statement_params = array();
    if(count($last_results)==0) return array($stmt->get_params());

    // build param-tupel for each result
    foreach($last_results as $lr) {
 
      if(!Validator::isa($last_stmt,"null")) {
        // set each param that each resultset provides
        $ptupel = array();
        foreach($last_stmt->get_provides() as $p) {
          $this->log(__METHOD__."(".__LINE__."): p=%, lr=%",
            array($p,StringUtil::get_object_string($lr))); 
          if(array_key_exists($p,$lr)) $ptupel[":".$p] = $lr[$p];
        }

        // add params-tupel  
        array_push($next_statement_params,$ptupel);
      }

    }
   
    return $next_statement_params;

  }

}

?>
