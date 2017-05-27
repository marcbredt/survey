Notes
-----

* D autoloader
  * namespace class loading from separated doc root
 
* data
  * json - db data responses
  * xml - config
  * cdn - icons

* verifyers
  * tokenizer
  * D validator
  * request

* language
  * en, de

* html
  * form builder
  * form element names

* session
  * combine with auth
  * cookies

* authenticator
  * login
  * page access
  * db access

* database
  * connector - pdo
  * statement - validate/builder
  * executor - 
  * table engine != MyISAM => transactions, see confirm/submit

* survey
  * as static xml with lang extension via survey-LANG.xml or
    * <survey id="sid" name="name">
        <qs>
          <q id="1">
            <as type="check,radio,select">
              <a>1</a>
            </as>
          </q>
        </qs>
      </survey>

  * a dynamic version via db 
    * D user        	-uid-    	        	(uid,name,mail,pass,...)
    * D accomplished	-acid/uid/sid/qid/aid-		(-"-)
    * D surveys      	-sid/name/ltid-			(sid,name,ltid)
    * D questions   	-qid/sid/ltid-			(-"-)
    * answers     	-aid/paid/sid/qid/itype/ltid-   (-"-)
    * atypes            -atid/name-                     (-"-)
    * D language    	-lab-				(lab,text)
    * D langtext       	-lab/ltid-			(ltid,lab,text,ref)

  * answer types
    * radio, check, select, input
    * hierarchie combinations - radio radio - ja:{modem,isdn,dsl,sonstige}
    * answer hierarchies through paid, highest => paid = null
      * (null,aid1)
             (paid,aid)
                  (paid,aid)
      * (null,aid2)
             (paid,aid)
                  (paid,aid)

  * multiple pages
    * cookies - tmp storage
    * token

  * survey checker
  * confirm/submit
    * insert as transaction
    * inside this transaction, acid counter to avoid double inserts

* security
  * xss - validator/escapor
  * session hijack - 
  * xsrf - tokenizer
  * referer - referer/hostname mod before exception handling - origin
  * sql - statements/validator
