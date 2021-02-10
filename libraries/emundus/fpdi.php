<?php 
//require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdf.php');
require_once(JPATH_LIBRARIES.DS.'FPDI'.DS.'fpdi.php');
require_once(JPATH_LIBRARIES.DS.'FPDI'.DS.'fpdi_pdf_parser.php');

// implement an own parser to get access to cross-reference information
class MyPdfParser extends fpdi_pdf_parser
{
    /**
     * Checkes if file is encrypted or protected
     */
    public function isEncrypted()
    {
        return isset($this->_xref['trailer'][1]['/Encrypt']) ? true :  false;
    }
    /**
     * Checkes for a compressed cross-reference
     */
    public function isXrefStream()
    {
        return isset($this->_xref['trailer']) ? $this->_xref['trailer'] :  false;
    }
}

class ConcatPdf extends FPDI {
     var $files = array();
     function setFiles($files) {
          $this->files = $files;
     }

     function concat() {
          foreach($this->files AS $file) {

               $pagecount = $this->setSourceFile($file);
               for ($i = 1; $i <= $pagecount; $i++) {
                    $tplidx = $this->ImportPage($i);
                    $s = $this->getTemplatesize($tplidx);
                    if ($s['w'] > $s['h']) {
                         $this->AddPage('L', array($s['w'], $s['h']));
                    } else {
                         $this->AddPage('P', array($s['w'], $s['h']));
                    }
                    $this->useTemplate($tplidx);
               }
          }
     }

     /**
     * Return our parser
     */
    protected function _getPdfParser($filename)
    {
        return new MyPdfParser($filename);
    }
}

?>
