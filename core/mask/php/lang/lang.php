<?php 
  global $language, $session;
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\session\token\Tokenizer as Tokenizer;
  use core\db\data\DataGetter as DataGetter;

  $token = Tokenizer::get();
  $session->set("tokens","changelang",$token);

?>

<table cellpadding="0" cellspacing="0">
<tr><td class="width_max"></td><td>

<table cellpadding="0" cellspacing="0" class="table_inner text_white">
<form name="mlang" action="" method="post">
        
  <tr>
    <td><?php echo $language->get("language"); ?>:</td>
    <td>

      <select name="language" size="1" onchange="this.form.submit();">
      <?php
        $x = new XMLDocument("../conf/statements.xml",
                             "../conf/dtd/statements.dtd",true);
        $xs = $x->xpath("//statement[@name='languages']",true);

        // avoid redirect cycles on pdo exceptions
        if(!Validator::equals($session->get("error","exception"),"PDOException")) {

          $dg = new DataGetter($xs);
        
          $data = $dg->get(array(),false);
          foreach($data as $r) {

          // set selected tag for default or cookie language
          $selected = "";
          if(Validator::equals($r['lab'],$session->get("language"))) 
            $selected = "selected";

          // print it
          echo "<option value=\"".$r['lab']."\" ".$selected.">".
                 $r['text'].
               "</option>\n";
          }
        }
      ?>
      </select>

    </td>
   
    <td>

      <script>
        document.write("<input name=\"action\" type=\"hidden\" " + 
                              "value=\"changelang\" />");
      </script>
      <noscript>
        <button name="action" type="submit" value="changelang"
                class="text_white button_link">
          <?php echo $language->get('change'); ?>
        </button>
      </noscript>

      <input name="token" type="hidden" 
             value="<?php echo $session->get("tokens","changelang"); ?>" />

    </td>

  </tr>

</form>

</table>

</td></tr></table>

