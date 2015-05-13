<?php 
require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
require_once(JPATH_LIBRARIES.DS.'FPDI'.DS.'fpdi.php');
 
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
}
?>
