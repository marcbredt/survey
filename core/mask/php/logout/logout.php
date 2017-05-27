<?php 
  
  global $language, $session; 
  use core\util\param\Validator as Validator;
  use core\session\token\Tokenizer as Tokenizer;

  $token = Tokenizer::get();
  $session->set("tokens","logout",$token);

?>
<table cellpadding="0" cellspacing="0" class="table_inner">
<form name="mlogout" action="" method="post">
  <tr>
    <td class="width_max">
      <?php 
        $user = unserialize($session->get("user"));
        echo $language->get("hello")." ".$user->get_fname()." ".
               $user->get_lname().", ";
      ?>
    </td>
    <td>
      <button name="action" type="submit" value="logout"
              class="text_white button_link">
        <?php echo $language->get('logout'); ?></button>
      <input name="token" type="hidden" 
             value="<?php echo $session->get("tokens","logout"); ?>" />
    </td>
  </tr>
</form>
</table>
