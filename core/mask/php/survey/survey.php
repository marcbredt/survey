<?php 
  global $language, $session;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\string\StringUtil as StringUtil;
  use core\db\data\DataGetter as DataGetter;
  use core\mask\MaskLoader as MaskLoader;
  use core\util\param\Validator as Validator;
  use core\session\token\Tokenizer as Tokenizer;

  $token = Tokenizer::get();
  $session->set("tokens","abortsurvey",$token);
  $token = Tokenizer::get();
  $session->set("tokens","submitsurvey",$token);

  $session->set("pagesize",null,3); //could be set via config, but for now static

?>
<table border="0" cellpadding="0" cellspacing="0" class="table_inner text_black">
<form action="" method="get">
  <tr>
    <td class="width_max">
      <?php
 
        // if a survey was selected, get the survey questions 
        if ($session->has("survey") && $session->has("survey","sid")) {
          
          // num questions
          $x = new XMLDocument("../conf/statements.xml",
                               "../conf/dtd/statements.dtd",true);
          $xs = $x->xpath("//statement[@name='survey_num_questions']",true);
          $dg = new DataGetter($xs);
          $data = $dg->get(array(
                             ":sid" => $session->get("survey","sid"),
                           ),false);
          // set catalog size
          $session->set("catasize",null,$data[0]["qnum"]);

          // survey title
          $x = new XMLDocument("../conf/statements.xml",
                               "../conf/dtd/statements.dtd",true);
          $xs = $x->xpath("//statement[@name='survey_title_only']",true);
          $dg = new DataGetter($xs);
          $data = $dg->get(array(
                             ":sid" => $session->get("survey","sid"),
                             ":lab" => $session->get("language")
                           ),false);

          // calculate from/to
          if(isset($_GET["page"]) 
             && intval($_GET["page"]) <= 
                ceil(intval($session->get("catasize"))
                       /intval($session->get("pagesize"))))
            $session->set("page",null,$_GET["page"]);
          else $session->set("page",null,1);
          $from = (intval($session->get("page"))-1) 
                    * intval($session->get("pagesize"));
          $to = intval($session->get("page")) 
                  * intval($session->get("pagesize")) - 1;

          echo $data[0]["stitle"]." (".$language->get("page")." ".
                                       $session->get("page").")";
      ?>
    </td>
  </tr>
  <tr>
    <td>
      <?php
      
        // questions data
        $x = new XMLDocument("../conf/statements.xml",
                             "../conf/dtd/statements.dtd",true);
        $xs = $x->xpath("//statement[@name='survey_questions_only']",true);
        $dg = new DataGetter($xs);
        $data = $dg->get(array(
                           ":sid" => $session->get("survey","sid"),
                           ":lab" => $session->get("language")
                         ),false);

        // questions
        for($i=$from; $i<=$to; $i++) {

        ?>
        <table border="0" cellpadding="0" cellspacing="0" 
               class="table_inner text_black">
          <tr><td><?php echo $data[$i]["text"]; ?></td></tr>  
          <?php
  
            // answers data
            //print_r($data[$i]);
            $xa = new XMLDocument("../conf/statements.xml",
                                 "../conf/dtd/statements.dtd",true);
            $xsa = $xa->xpath("//statement[@name='survey_answers_only']",true);
            $dga = new DataGetter($xsa);
            $dataa = $dga->get(array(
                               ":sid" => $session->get("survey","sid"),
                               ":qid" => $data[$i]["qid"],
                               ":lab" => $session->get("language")
                             ),false);
            // answers
            $hasselect=false;
            foreach($dataa as $da) {

              //print_r($da);

              switch($da["type"]) { 

                case "radio": 
                  if(gettype($da["paid"])!==null)
                    echo "<tr><td>".
                         "<input type=\"radio\" name=\"qid_".$da["qid"].
                         "_paid_".$da["paid"]."\" value=\"aid_".$da["aid"]."\"/>".
                         "".$da["text"]."</td></tr>"; 
                  else 
                    echo "<tr><td><input type=\"radio\" name=\"qid_".$da["qid"].
                         "\" value=\"aid_".$da["aid"]."_paid_".$da["paid"]."\"/>".
                         " ".$da["text"]."</td></tr>"; 
                break;

                case "checkbox":  
                  echo "<tr><td><input type=\"checkbox\" name=\"qid_".$da["qid"].
                       "\" value=\"aid_".$da["aid"]."_paid_".$da["paid"]."\"/>".
                       " ".$da["text"]."</td></tr>"; 
                break;

                case "text": break;

                case "number": 
                  echo "<tr><td><input type=\"number\" size=\"1\" maxlength=\"1\"".
                       "min=\"1\" max=\"5\"".
                       "name=\"qid_".$da["qid"]."_aid_".$da["aid"]."_paid_".$da["paid"].
                       "\"/>".
                       " ".$da["text"]."</td></tr>"; 
                break; 

                case "option": 
                  if($hasselect===false) {
                    echo "<tr><td><select name=\"qid_".$da["qid"]."\" size=\"1\">\n".
                         "<option value=\"aid_".$da["aid"]."_paid_".$da["paid"]."\">".
                         $da["text"]."</option>\n";
                    $hasselect = true;
                  } else {
                    echo "<option value=\"aid_".$da["aid"]."_paid_".$da["paid"]."\">".
                         $da["text"]."</option>";
                  }
                break; 
              }

            }
            if($hasselect===true) echo "</select></td></tr>\n";
          ?>
        </table>   
        <?php 

        }

      }

      ?>  
    </td>
  </tr>
  <tr>
    <td>
      <?php
    
        // if a survey was selected, get the ability to abort or submit
        if (!Validator::isa($session->get("survey"),"null")
            && !Validator::isa($session->get("survey","sid"),"null")){
      ?>
   
      <!-- abort button -->
      <button name="action" type="submit" value="abortsurvey" class="button_link">
        <?php echo $language->get("abort"); ?></button>
      <input name="token" type="hidden" 
           value="<?php echo $session->get("tokens","abortsurvey"); ?>" />
       
      <!-- confirm button -->
      <button name="action" type="submit" value="submitsurvey" class="button_link">
        <?php echo $language->get("submit"); ?></button>
      <input name="token" type="hidden" 
           value="<?php echo $session->get("tokens","submitsurvey"); ?>" />

      <?php

        }

      ?>
  </tr>
  <tr>
    <?php MaskLoader::load("page","page","php"); ?>
  </tr>
</form>
</table>
