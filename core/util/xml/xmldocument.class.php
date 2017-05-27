<?php

namespace core\util\xml;
use \DOMDocument as DOMDocument;
use \DOMXpath as DOMXpath;
use \DOMImplementation as DOMImplementation;
use core\object\LoggableObject as LoggableObject;
use core\util\param\Validator as Validator;
use core\exception\xml\XMLNotValidException as XMLNotValidException;
use core\exception\xml\XMLNoValidDTDException as XMLNoValidDTDException;
use core\exception\xml\xpath\UnresolvedXPathException as UnresolvedXPathException;
use core\exception\xml\xpath\InvalidXPathExpressionException  
    as InvalidXPathExpressionException;

/**
 * This class is used to read XML documents. 
 * It belongs to the core. As it is used by AccessibleObject
 * AccessChecker respectively it could not extend AccessibleObject
 * otherwise there are probably loops generated.
 * @author Marc Bredt
 * @see <a href="http://php.net/manual/en/class.domdocument.php">DOMDocument</a>
 */ 
class XMLDocument extends LoggableObject {

  /**
   * Store the XML document as DOMDocument.
   */
  private $doc = null;
  
  /**
   * Load the XML file into a DOMDocument.
   * @param $xmlfile path to the XML file to be loaded.
   * @param $dtdfile path to dtd file to validate the XML
   * @param $validate flag to enable validation against a dtd, defaults to true
   */
  public function __construct($xmlfile = null, $dtdfile = null, $validate = true) {

    // setup the filelogger
    parent::__construct(get_class($this));

    // initialize the document
    if(!Validator::isa($xmlfile,"null") && file_exists($xmlfile)
       && Validator::equals(mime_content_type($xmlfile),"application/xml")) {

      // create a validatable DOMDocument first and
      // run validation on the xmlfile using the dtd provided
      if(!Validator::isa($dtdfile,"null") && file_exists($dtdfile)
         && Validator::equals($validate,true)) { 
        
        $this->log(__METHOD__.": creating validatable XMLDocument.", array());
        $dvalidatable = $this->create_doc($xmlfile,$dtdfile);

        if(!Validator::isa($dvalidatable,"null") && @$dvalidatable->validate()) { 
          $this->doc = $dvalidatable;
          $dvalidatable = null;

        } else {
          $this->log(__METHOD__."%",array(new XMLNotValidException("XML not valid",2)),"ERR");
          throw(new XMLNotValidException("XML not valid",2));
        }
      
      // skip validation
      } else if(Validator::equals($validate,false)) {

        $this->log(__METHOD__.": creating invalidatable XMLDocument.",
          array(),"WARNING");
        $this->doc = $this->create_doc($xmlfile,null,false);

      // if the dtd file passed is not valid
      } else {
        $this->log(__METHOD__.": %",array(new XMLNoValidDTDException()),"ERR");
        throw(new XMLNoValidDTDException());

      }
  
    // if the xmlfile is null at least an instance should be created to be able
    // to construct an XMLDocument from string using create_doc
    } else if(Validator::isa($xmlfile,"null")) {

    // if an invalid xml file was passed
    } else {
      $this->log(__METHOD__.": % (%,%,%)",
          array(new XMLNotValidException(),
                Validator::isa($xmlfile,"null"),
                @file_exists($xmlfile),
                Validator::equals(@mime_content_type($xmlfile),"application/xml")
          ),
        "ERR");
      throw(new XMLNotValidException());
    
    }

  }

  /**
   * Create document with corresponding document description.
   * @param $xml XML file name or XML string to create a document from
   * @param $dtd DTD file to create document from
   * @param $validate flag to create a validatable document using $dtdfile
   * @return DOMDocument with $dtdfile attached
   */
  public function create_doc($xml = null, $dtd = null, $validate = true) {

    $d = null;
    $dx = new DOMDocument();

    // if a valid xml file was passed 
    if(@file_exists($xml) 
       && Validator::equals(mime_content_type($xml),"application/xml")
       && Validator::isa($xml,"string")) {
      $dx->load($xml);

    // otherwise assume it is an xml string
    } else if (Validator::isa($xml,"string")) {
      // wrap it to definetly make any string a valid xml string
      $dx->loadXML("<root>".trim($xml)."</root>");
 
    // create an xml from dataset
    } else if(Validator::isclass($xml,"core\db\data\DataSet")){
 
      // TODO: create an xml from data set - multidimensional array
 
    // otherwise throw an exception
    } else {
      $this->log(__METHOD__.": % (%,%,%)",
          array(new XMLNotValidException(),
                Validator::isa($xml,"null"),
                @file_exists($xml),
                Validator::equals(@mime_content_type($xml),"application/xml")
          ),
        "ERR");
      throw(new XMLNotValidException());
   
    }

    $di = new DOMImplementation();
    if($validate === true && file_exists($dtd)) {
      $dtd = $di->createDocumentType($dx->documentElement->tagName,'',$dtd);
      $d = $di->createDocument("",$dx->documentElement->tagName,$dtd);
    } else {
      $d = $di->createDocument("",$dx->documentElement->tagName);
    }

    // set some document markers/flags
    $d->xmlStandalone = false;
    $d->xmlVersion = "1.0";
    $d->formatOutput = true;
 
    // set the main document element 
    $d->removeChild($d->documentElement);
    $d->appendChild($d->importNode($dx->documentElement->cloneNode(true),true));

    return $d;

  }
  
  /**
   * Get the currently loaded XML document.
   * @return the currently loaded XML document.
   */
  public function get_doc() {
    return $this->doc;
  }

  /**
   * Set the currently loaded XML document.
   * @param $doc DOMDocument which should be pinned onto this XMLDocument
   */
  protected function set_doc($doc = null) {
 
    if(Validator::isclass($doc,"DOMDocument")) $this->doc = $doc;
 
  }

  /**
   * Get the string representation of the loaded XML document.
   * @return string representation of the XML file.
   */  
  public function __toString() {
    return (!Validator::isa($this->doc,"null") ? $this->doc->saveXML() : ""); 
  }

  /**
   * Get string representation for a DOMDocument.
   * @param $doc DOMDocument to be transformed into string
   * @return string representation for <code>$doc</code> if it is a valid
   *                DOMDocument otherwise an empty string
   */
  public static function get($doc = null) {

    if (!Validator::isa($doc,"null") 
        && Validator::isa($doc,"object") 
        && Validator::isclass($doc,"DOMDocument"))
      return $doc->saveXML(); 

    return "";

  }

  /**
   * Send a xpath query to the XML document <code>$doc</code>.
   * @param $query XPath query
   * @param $raxd return nodes found as XMLDocument, useable when evaluating
   *              further xpathes disabled by default 
   * @return an XMLDocument if $raxd is true, otherwise a string representing 
             the nodes found evaluating $query.
   * @throws InvalidXPathExpressionException
   * @see https://bugs.php.net/bug.php?id=70523
   */
  public function xpath($query = "/", $raxd = false) {

    $nodeeval = "";
    $domx = new DOMXpath($this->doc);
    $nodedoc = new DOMDocument();
    $unresolved = false;    

    // catch some invalid xpath expressions before evaluation
    if(Validator::isa($query,"null")) {
      $this->log(__METHOD__.": %",array( 
        new InvalidXPathExpressionException(
          "Invalid XPath expression - Query=".$query,1)),"ERR");
      throw(new InvalidXPathExpressionException(
        "Invalid XPath expression - Query=".$query,1));

    } else if (Validator::equals($query,"")) {
      $this->log(__METHOD__.": %",array( 
        new InvalidXPathExpressionException(
          "Invalid XPath expression - Query=".$query,2)),"ERR");
      throw(new InvalidXPathExpressionException(
        "Invalid XPath expression - Query=".$query,2));

    }

    // NOTE: $nlist is boolean 'false' if evaluation fails, even for 'false()'
    $nlist = @$domx->evaluate($query);
    if($nlist === false) {
      $this->log(__METHOD__.": %",array( 
        new InvalidXPathExpressionException()),"ERR");
      throw(new InvalidXPathExpressionException());
    }

    // if there were some usable values 
    if(Validator::isa($nlist,"object") 
       && Validator::isclass($nlist,"DOMNodeList")) { 
 
      foreach($nlist as $n) {
 
        if (Validator::isclass($n,"DOMDocument")) {
          $nodeeval = $nodeeval." ".preg_replace("/<\?xml.*"."\?".">/","",
                                                 $n->saveXML());

        } else if (Validator::isclass($n,"DOMElement")) {
          $nodedoc->appendChild($nodedoc->importNode($n->cloneNode(TRUE),TRUE));
          $nodeeval = $nodeeval." ".preg_replace("/<\?xml.*"."\?".">/","",
                                                 $nodedoc->saveXML());
 
        } else if (Validator::isclass($n,"DOMAttr")) {
          $nodeeval = $nodeeval." ".$n->name."=\"".$n->value."\"";
        
        } else if (Validator::isclass($n,"DOMText")) {
          $nodeeval = $nodeeval." ".$n->wholeText."";

        } else {

          $unresolved = true;
          break;

        }

      }
   
    } else if (Validator::isa($nlist,"string")
               || Validator::isa($nlist,"double")) {
      $nodeeval = $nodeeval."".$nlist;

    } else if (Validator::isa($nlist,"boolean")) {
      $nodeeval = $nodeeval."".($nlist ? "true" : "false");

    } else {

      $unresolved = true;

    }

    // throw an exception if there was an object class or return type unresolved
    // by this function
    if($unresolved === true) {

      $this->log(__METHOD__.": %",array(new UnresolvedXPathException(
            "Unresolved XPath expression for ".
            (!Validator::isa($nlist,"object") ? "type " : "").gettype($nlist).
            (Validator::isa($nlist,"object") ? " class " : "").
            (Validator::isa($nlist,"object") ? get_class($nlist) : ""))),
          "ERR");

      throw(new UnresolvedXPathException(
            "Unresolved XPath expression for ".
            (!Validator::isa($nlist,"object") ? "type " : "").
            gettype($nlist).
            (Validator::isa($nlist,"object") ? " class " : "").
            (Validator::isa($nlist,"object") ? get_class($nlist) : ""))); 

    }

    // replace (multiple) white spaces and newline characters
    $nodeeval = preg_replace("/> </","><",
                  preg_replace("/^([ \t])+|([ \t])+$/","",
                    preg_replace("/([ \t])+/"," ",
                      preg_replace("/[\r\n]/"," ",$nodeeval))));

    // create another xmldocument for further xpath queries
    if($raxd === true) {
      $nex = new XMLDocument(null,null,false); 
      $nexd = $nex->create_doc($nodeeval);
      $this->log(__METHOD__.": created DOMDocument from string, str=%, nex=%", 
                 array($nodeeval, $nexd));
      $nex->set_doc($nexd);
      return $nex;
    } 

    return $nodeeval;

  }

}

?>
