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
	
	public static function buildFormPDF($fnumInfos, $sid, $fnum)
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

		$application_form_pdf($sid, $fnum, false);
		return  EMUNDUS_PATH_ABS.$sid.DS.$fnum.'_application.pdf';
	}

	public static function getAttchmentPDF(&$exports, &$tmpArray, $files, $sid)
	{
		foreach($files as $file)
		{
			$exFileName = explode('.', $file->filename);
			if(file_exists(EMUNDUS_PATH_ABS.$file->user_id.DS.$file->filename))
			{
				if(strtolower($exFileName[1]) != 'pdf')
				{
					$fn = EmundusHelperExport::makePDF($file->filename, $exFileName[1], $sid);
					$exports[] = $fn;
					$tmpArray[]= $fn;
				}
				else
				{
					$exports[] = EMUNDUS_PATH_ABS.$file->user_id.DS.$file->filename;
				}
			}
		}
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

			$htmlData = '<a href="'.JURI::base().EMUNDUS_PATH_REL.DS.$aid.DS.$fileName.'">'.JURI::base().EMUNDUS_PATH_REL.DS.$aid.DS.$fileName.'</a>';
			$pdf->startTransaction();
			$start_y = $pdf->GetY();
			$start_page = $pdf->getPage();
			$pdf->writeHTMLCell(0,'','',$start_y,$htmlData,'B', 1);
		}
		$tmpName = JPATH_BASE.DS.'tmp'.DS."$aid-$fileName.pdf";
		$pdf->Output($tmpName, 'F');
		return $tmpName;
	}
}