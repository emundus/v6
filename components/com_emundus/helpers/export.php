<?php
/**
 * @version		$Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
 
class EmundusHelperExport
{
	
	public static function buildFormPDF($fnumInfos, $sid, $fnum, $form_post = 1 )
	{
		$file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_'.$fnumInfos['training'].'.php';

		if (!file_exists($file))
		{
			$file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php';
			$application_form_pdf = 'application_form_pdf';
		} else
		{
			$application_form_pdf = 'application_form_pdf_'.str_replace('-', '_', $fnumInfos['training']);
		}
		if (!file_exists(EMUNDUS_PATH_ABS.$sid))
		{
			mkdir(EMUNDUS_PATH_ABS.$sid);
			chmod(EMUNDUS_PATH_ABS.$sid, 0755);
		}

		require_once($file);

		application_form_pdf($sid, $fnum, false, $form_post);
		return  EMUNDUS_PATH_ABS.$sid.DS.$fnum.'_application.pdf';
	}

    /**
     * Check whether pdf is encrypted or password protected.
     * @param <type> $form
     * @param <type> $form_state
     */
    public static function pdftest_is_encrypted($file)
    {
        require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');

        $pdf = new ConcatPdf();
        $pdf->setSourceFile($file);

        if ($pdf->currentParser->isEncrypted())
            return false;
        else
            return true;
    }

    public static function get_pdf_prop($file)
	{
	    $f = fopen($file,'rb');
	    if(!$f)
	        return false;

	    //Read the last 16KB
	    fseek($f, -16384, SEEK_END);
	    $s = fread($f, 16384);

	    //Extract cross-reference table and trailer
	    if(!preg_match("/xref[\r\n]+(.*)trailer(.*)startxref/s", $s, $a))
	        return false;
	    $xref = $a[1];
	    $trailer = $a[2];

	    //Extract Info object number
	    if(!preg_match('/Info ([0-9]+) /', $trailer, $a))
	        return false;
	    $object_no = @$a[1];

	    //Extract Info object offset
	    $lines = preg_split("/[\r\n]+/", $xref);
	    $line = @$lines[1 + $object_no];
	    $offset = (int)$line;
	    if($offset == 0)
	        return false;

	    //Read Info object
	    fseek($f, $offset, SEEK_SET);
	    $s = fread($f, 1024);
	    fclose($f);

	    //Extract properties
	    if(!preg_match('/<<(.*)>>/Us', $s, $a))
	        return false;
	    $n = preg_match_all('|/([a-z]+) ?\((.*)\)|Ui', $a[1], $a);
	    $prop = array();
	    for($i=0; $i<$n; $i++)
	        $prop[$a[1][$i]] = $a[2][$i];

	    return $prop;
	}

    public static  function  isEncrypted($file) {
        $f = fopen($file,'rb');
        if(!$f)
            return false;

        //Read the last 320KB
        fseek($f, -323840, SEEK_END);
        $s = fread($f, 323840);
        //Extract Info object number
        return preg_match('/Encrypt ([0-9]+) /', $s);
    }

	public static function getAttchmentPDF(&$exports, &$tmpArray, $files, $sid)
	{
		foreach($files as $file)
		{
            if (strrpos($file->filename,"application_form")=== false) {
                $exFileName = explode('.', $file->filename);
                $filePath = EMUNDUS_PATH_ABS.$file->user_id.DS.$file->filename;
                if(file_exists($filePath)) {
                    if (strtolower($exFileName[1]) != 'pdf') {
                        $fn = EmundusHelperExport::makePDF($file->filename, $exFileName[1], $sid);
                        $exports[] = $fn;
                        $tmpArray[] = $fn;
                    } else {
                        /*$prop = EmundusHelperExport::get_pdf_prop($filePath);
                        echo "<pre>";
            var_dump($prop); die();*/
                        if (EmundusHelperExport::isEncrypted($filePath)) { 
                            $fn = EmundusHelperExport::makePDF($file->filename, $exFileName[1], $sid);
                            $exports[] = $fn;
                            $tmpArray[] = $fn;
                        } else
                            $exports[] = $filePath;
                    }
                }
			}
		}
	}

    public static function getEvalPDF(&$exports, $fnum)
    {
        $fn = EmundusHelperExport::evalPDF($fnum);
        $exports[] = $fn;

       // $exports[] = EMUNDUS_PATH_ABS.$file->user_id.DS.$file->filename;
    }

	public static function makePDF($fileName, $ext, $aid)
	{
		require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
		$imgExt = array('jpeg', 'jpg', 'png', 'gif', 'svg');
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Decision Publique');
		$pdf->SetTitle($fileName);
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 8);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->AddPage();
		if(in_array(strtolower($ext), $imgExt))
		{
			$pdf->setJPEGQuality(75);
			if($ext == 'svg')
			{
				$pdf->ImageSVG(EMUNDUS_PATH_ABS.$aid.DS.$fileName, '', '', '', '', '', '', '', true, 300, '', false, false, 0, false, false, true);
			}
			else
			{
				$pdf->Image(EMUNDUS_PATH_ABS.$aid.DS.$fileName, '', '', '', '', '', '', '', true, 300, '', false, false, 0, false, false, true);
			}
		}
		else
		{
			$htmlData = JText::_('ENCRYPTED_FILE').' : ';
			$htmlData .= '<a href="'.JURI::base().EMUNDUS_PATH_REL.DS.$aid.DS.$fileName.'">'.JURI::base().EMUNDUS_PATH_REL.DS.$aid.DS.$fileName.'</a>';
			$pdf->startTransaction();
			$start_y = $pdf->GetY();
			$start_page = $pdf->getPage();
			$pdf->writeHTMLCell(0,'','',$start_y,$htmlData,'B', 1);
		}
		$tmpName = JPATH_BASE.DS.'tmp'.DS."$aid-$fileName.pdf";
		$pdf->Output($tmpName, 'F');
		return $tmpName;
	}
    public static function evalPDF($fnum)
    {
        require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Decision Publique');
        $pdf->SetTitle('Evaluation');
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();

        $htmlData = '';
        $data = @EmundusHelperFiles::getEvaluation('html', $fnum);
        foreach ($data as $fnums => $evals) {
            foreach ($evals as $user => $html) {
                $htmlData .= $html;
            }
        }
        $pdf->startTransaction();
        $start_y = $pdf->GetY();
        $start_page = $pdf->getPage();
        $pdf->writeHTMLCell(0,'','',$start_y,$htmlData,'B', 1);

        $tmpName = JPATH_BASE.DS.'tmp'.DS."evaluation.pdf";
        $pdf->Output($tmpName, 'F');
        return $tmpName;
    }

}