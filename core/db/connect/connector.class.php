<?php

namespace core\db\connect;
use \PDO as PDO;
use core\object\LoggableObject as LoggableObject;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;

/**
 * An abstract connector class to provide connections to a database.
 * Although it is declared abstract the connection functions are implemented.
 * The reason for is that any sepcific PDO driver or connector needs some 
 * specific values set before to work properly. 
 * This connector uses the PDO module to provide database functionality.
 * @author Marc Bredt
 */

abstract class Connector extends LoggableObject {

  /**
   * Stores the connector setup.
   */
  private $setup = array();

  /**
   * Options that can be passed onto PDO.
   */
  private $pdooptions = array();

  /**
   * Stores the current connection to the database.
   */
  private $connection = null;

  /**
   * Create a connector.
   * @param $conf complete XMLDocument containing all database configurations
   * @param $name database configuration name to create this connection for
   *        if this value is not available the default database configuration
   *        is used
   */
  public function __construct($conf = null, $name = "") {
  
    // setup the logger
    parent::__construct(get_class($this));

    // if a valid configuration was passed
    if(Validator::isclass($conf,"core\util\xml\XMLDocument")) {
  
      // get specific database config
      if(Validator::isa($name,"string")) {
        $dbc = $conf->xpath("//database[@name=\"".$name."\"]",true);
      } else {
        $dbc_default = $conf->xpath("string(//databases[@default=\"".$name."\"])");
        $dbc = $conf->xpath("//database[@name=\"".$dbc_default."\"]",true);
      }

      // store key, value pairs in $setup and decide which $pdooptions to set 
      // follow the PDO::(MYSQL_)ATTR_* structure while inserting
      foreach($dbc->get_doc()->documentElement->childNodes[0]->childNodes as $t) {

        // store each tag
        $this->setup[trim($t->tagName)] = trim($t->textContent);

        // set attributes for specific tags
        switch(trim($t->tagName)) {
          case "persistent": $this->pdooptions[PDO::ATTR_PERSISTENT] = true; break;
          default: break;
        }

      }

    } else {

      $this->log(__METHOD__.": %", 
                 array(new ParamNotValidException("no XMLDocument")));
      throw(new ParamNotValidException("no XMLDocument"));
 
    }

  }

  /**
   * Connect to a database. This function can be expanded choosing a database
   * or cluster node to establish a connection on.
   * @return 
   */ 
  public function connect() {

    // create a connection
    if(Validator::isa($this->connection,"null")) 
      $this->connection = new Connection($this->setup['type'],
                            $this->setup['host'],$this->setup['port'],
                            $this->setup['db'],$this->setup['user'],
                            $this->setup['pass'],$this->pdooptions);

    // set the connections database handle
    $this->connection->get();
  }

  /**
   * Disconnect from a database.
   * @return true if connection was established successfully, otherwise false
   */ 
  public function disconnect() {
    $this->connection->close();
  }

  /**
   * Get the connection currently set or connect.
   * @return an active connection
   */
  public function get_connection() {
    if(Validator::isa($this->connection,"null")) $this->connect();
    return $this->connection;
  }

}

?>
