<?php 

  // namespace stuff, creating some abbreviations
  namespace app;
  use core\autoloader\AutoLoader as AutoLoader;
  use core\autoloader\Devisor as Devisor;
  use core\util\log\FileLogger as FileLogger;
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\string\StringUtil as StringUtil;
  use core\lang\Language as Language;
  use core\mask\MaskLoader as MaskLoader;
  use core\session\Session as Session;
  use core\session\User as User;
  use core\session\handler\ActionHandler as ActionHandler;
  use core\session\auth\Authenticator as Authenticator;
  use core\db\connect\MySQLConnector as MySQLConnector;
  use core\db\exec\Executor as Executor;
  use core\db\stmt\Statement as Statement;
  use core\db\stmt\StatementBatch as StatementBatch;  
  use core\exception\handler\ExceptionHandler as ExceptionHandler;

  // start output buffering to be able to redirect on exception handled in
  // ExceptionHandler::handle, beware of exceptions that can generate cycles
  // e.g. database/PDO exceptions
  ob_start();

  // headers

  // the following files are the only files which need to be required explicitly
  // as long as PHP >= 5.1.2 introducing the spl_autoload_* functions is used
  require_once("../core/autoloader/autoloader.class.php");
  require_once("../core/autoloader/devisor.class.php");
  $alp = dirname(__FILE__)."/..";
  $al = new AutoLoader(false, $alp);
  $al->expand(".class.php,.interface.php,.include.php,.php");
  $al->load();

  // session stuff
  session_start();
  $session = new Session();

  // setup a filelogger
  $filelogger = new FileLogger("index.php");
  $filelogger->log("autoloader=%", array($al));
  $filelogger->log("filelogger=%", array($filelogger), "DEBUG");

  // setup an exception handler for this session 
  $eh = new ExceptionHandler();

  // TODO: mass validation first, $_SERVER, $_COOKIE, $_GET, $_POST, ...
  Validator::validate("globals",$GLOBALS);
  Validator::validate("_server",$_SERVER);
  Validator::validate("_get",$_GET);
  Validator::validate("_post",$_POST);
  Validator::validate("_files",$_FILES);
  Validator::validate("_request",$_REQUEST);
  Validator::validate("_session",$_SESSION);
  Validator::validate("_env",$_ENV);
  Validator::validate("_cookie",$_COOKIE);

  // TODO: Authenticate all parameters passed, and remove them if they do
  //       belong to a target that needs to be required, after that redirect
  // ../conf/authenticates.xml

  // user stuff
  // restore the user from session, or initiate an empty one
  if($session->has("user") && !Validator::isempty($session->get("user"))) { 
    $user = unserialize($session->get("user"));
  } else { 
    $user = new User(); 
    $session->set("user",null,serialize($user)); 
  }

  // action evaluation
  if(isset($_POST["action"])) ActionHandler::handle($_POST["action"]);

  // language stuff
  $language = new Language($session->get("language"));
  $language->load();

  // log some important stuff
  $filelogger->log("(".__LINE__."): session=%", array($session));
 
?>
<html>

  <head>
    <title>magix-survey</title>
    <link rel="stylesheet" type="text/css" href="css/layout.css" />
    <link rel="stylesheet" type="text/css" 
          href="https://fonts.googleapis.com/css?family=Montserrat" />
  </head>

  <body>

    <table cellpadding="0" cellspacing="0" class="width_max height_max">
      <tr>
        <td class="bar">

        <table cellpadding="0" cellspacing="0" class="width_max">
          <tr>

            <!-- auth -->
            <?php if(!Authenticator::isauthenticated()) { ?>
            <td class="left">
              <?php MaskLoader::load("login","login","php"); ?>
            </td>
            <?php } else if(Authenticator::isauthenticated()) { ?>
            <td width="300" class="left">
              <?php MaskLoader::load("logout","logout","php"); ?>
            </td>
            <?php } ?>
            
            <!-- survey -->
            <?php if(Authenticator::isauthenticated()) { ?>
            <td width="80" class="left">
              <?php MaskLoader::load("survey","link","php"); ?>
            </td>
            <?php } ?>

            <!-- language -->
            <td class="right">
              <?php MaskLoader::load("lang","lang","php"); ?>
            </td>

          </tr>
        </table>    

        </td>
      </tr>
      
      <?php 
        if(isset($_SESSION["error"]["eid"])){ 
      ?>
      <tr>
        <td id="error">
        <?php 
          // get default message for eid from config
          echo ExceptionHandler::get_message($session->get("error","eid"));

          // print trace message generated from ExceptionHandler
          if ( $session->get("error","trace")===true ) {
            echo "<br><br>".$session->get("error","tracemsg");
          }

          // unset the displayed error
          $session->uset("error","eid");
          $session->uset("error","exception");
          $session->uset("error","trace");
          $session->uset("error","tracemsg");

        ?>
        </td>
      </tr>
      <?php } ?>

      <tr>
        <td valign="top" id="contents">

        <?php 
 
	  if(isset($_GET['mask']) && Validator::equals($_GET['mask'],"survey")) {

            // set the sid if it was passed in conjunction with "mask=survey"
            if(isset($_GET['sid'])) 
              $session->set("survey","sid",$_GET["sid"]); 
            else
              $session->uset("survey","sid"); 

            //echo "session=".$session."<br>";

            // if a survey name is set continue with this one
            if(!$session->has("survey","sid")) {
              $session->uset("survey","sid");
              $session->uset("survey","page");
              $session->uset("survey","answers");
              MaskLoader::load("survey","list","php");

            } else if($session->has("survey","sid")) {
              MaskLoader::load("survey","survey","php");

            }

          }

        ?>
        </td>
      </tr>

      <tr>
        <td class="bar"></td>
      </tr>

    </table>

  </body>
</html>
<?php

  // send data to th client
  ob_end_flush();

?>
