Status
------
* done
  * deploy script
  * utilities
    * filelogger, xmldocument, validator, stringutil
  * autoloader
  * language
  * mask loader
    * login, logout, lang, survey, page
  * serialization
  * db
    * executor, statement, connector
  * exception 
    * handler and various exceptions
  * login
  * session
    * user 
    * (action) token
    * action handling
  * authentication 
    * session bound user
  * layout
  
* nyi
  * validator
    * validating/discarding parameters on request (_get,_post,...)
  * authentication
    * query inject
  * survey
    * submit, confirm evaluation, (transactional) insertion
    * validating statements for already accomplished surveys
  * form
    * builder, evaluator

Demonstration
-------------
* exception handling, error generation/display
  * as probably not for all kind of errors you can think of an exception 
    is thrown try to throw a PDOException by e.g. shutting down the mysql
    database
  * you then should see an error popping up
* test users
  * max@mustermann.de - passwort: "a"
  * rudi@testmann.com - passwort: "b"

Conventions
-----------
* class file names as follows
  * case sensitive class name + class type suffix + .php
  * e.g. autooader.class.php, loggable.interface.php
* namespace needs to represent the directory structure under main/../
  * e.g. core\util\string => core/util/string  

Requirements
------------
* PHP >= 5.1.2, spl_autoload_* functions are required
* PHP >= 5.1.0, class Exception
* PHP >= 5.1.0, PDO module (as mysql is deprecated) from php-mysql enabled by
                default
* php-xml, for class XMLDocument
  * enabled by default but on *nix provided through an additional package
* php(5)-myql
  * db modules - mysql/mysqli/pdo_mysql
  * innodb - default storage system as of mysql 5.5 (*nix), 5.0 (win)
             adjustable with TYPE or ENGINE
* openssl >= 0.9.6
  * required for the tokenizer

Virtualhost configuration
-------------------------
* the document root must point to the deployed "main/" directory
* classes/configuration used will not be accessible from the outside as 
  the directories "conf/" and "core/" are siblings to the main directory
* if you want to adjust the folder structure provided use soft links for
  the "core/" and "conf/" directories to keep the application working 
  gathering files via relative pathes

MySQL
-----
* currently no indexing due to the project's database size
  * furtherimprovement on indexing should consider table langtext for 
    searching or any key(pair) from any table which is used to order data

Others
------
* automatic documentation generation with doxygen removed
* automatic (scenario) test execution with phpunit/hinject

