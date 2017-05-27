<?php 
  global $language, $session; 
  use core\util\param\Validator as Validator;
  use core\session\token\Tokenizer as Tokenizer;

  $token = Tokenizer::get();
  $session->set("tokens","login",$token);
  
?>
<table cellpadding="0" cellspacing="0" class="table_inner">
<form name="mlogin" action="" method="post">
  <tr>
    <td><?php echo $language->get("user"); ?>:</td>
    <td><input name="email" type="text" /></td>
    <td><?php echo $language->get("pass"); ?>:</td>
    <td><input name="password" type="password" /></td>
    <td><button name="action" type="submit" value="login">
          <?php echo $language->get("login"); ?></button>
    <input name="token" type="hidden" 
           value="<?php echo $session->get("tokens","login"); ?>" />
    </td>
  </tr>
</form>
</table>
