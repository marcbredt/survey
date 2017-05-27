<?php 
  global $language, $session; 
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\param\Validator as Validator;
  use core\util\string\StringUtil as StringUtil;
  use core\session\token\Tokenizer as Tokenizer;
  use core\db\data\DataGetter as DataGetter;

?>
<table cellpadding="0" cellspacing="0" class="table_inner text_black">
  <?php
  
    $x = new XMLDocument("../conf/statements.xml",
                         "../conf/dtd/statements.dtd",true);
    $xs = $x->xpath("//statement[@name='surveys']",true);
    $dg = new DataGetter($xs);
    $data = $dg->get(array(":lab"=>$session->get("language")),false);

    foreach($data as $r) {
      echo "<tr>\n".
           "  <td>".$r["text"]."</td>\n".
           "  <td>\n".
           "    <a href=\"".$_SERVER["PHP_SELF"]."?mask=survey&amp;sid=".
                   $r["sid"]."\">".$language->get("start")."</a>\n"; 
           "  </td>\n".
           "</tr>\n"; 
    }
   
  ?>
</table>
