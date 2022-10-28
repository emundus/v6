<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class EventbookingHelperPdf
{
	/**
	 * Method to generate PDF file
	 *
	 * @param   array   $pages
	 * @param   string  $filePath
	 * @param   array   $options
	 * @param   array   $pageOptions
	 */
	public static function generatePDFFile($pages, $filePath, $options)
	{
		PluginHelper::importPlugin('eventbooking');

		$results = Factory::getApplication()->triggerEvent('onEBBeforeGeneratePDF', [$pages, $filePath, $options]);

		// There is plugin generate the PDF file already, we do not need to process it further
		if (in_array(true, $results, true))
		{
			return;
		}

		$title = isset($options['title']) ? $options['title'] : 'Invoice';

		// Fall back to default implementation using TCPDF
		$pdf = static::getTCPDF($title, $options);

		foreach ($pages as $page)
		{
			$pdf->AddPage();

			if (!empty($page->options))
			{
				$pageOptions = $page->options;
			}
			else
			{
				$pageOptions = $options;
			}

			if (!empty($pageOptions['bg_image']))
			{
				static::setPageBackgroundImage($pdf, $pageOptions);
			}

			$pdf->writeHTML($page->content, true, false, false, false, '');
		}

		$pdf->Output($filePath, 'F');
	}

	/**
	 * Method to return PDF object
	 *
	 * @param   string  $title
	 * @param   array   $options
	 *
	 * @return TCPDF
	 * @throws Exception
	 */
	protected static function getTCPDF($title, $options = [])
	{
		require_once JPATH_ROOT . '/components/com_eventbooking/tcpdf/config/tcpdf_config.php';

		JLoader::register('TCPDF', JPATH_ROOT . '/components/com_eventbooking/tcpdf/tcpdf.php');

		$options = new Registry($options);

		$config = EventbookingHelper::getConfig();
		$pdf    = new TCPDF($options->get('PDF_PAGE_ORIENTATION', PDF_PAGE_ORIENTATION), $options->get('PDF_UNIT', PDF_UNIT), $options->get('PDF_PAGE_FORMAT', PDF_PAGE_FORMAT), true, 'UTF-8', false);
		$pdf->SetCreator($options->get('PDF_CREATOR', 'Events Booking'));
		$pdf->SetAuthor(Factory::getApplication()->get('sitename'));
		$pdf->SetTitle($title);
		$pdf->SetSubject($title);
		$pdf->SetKeywords($title);
		$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
		$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins($config->get('pdf_margin_left', PDF_MARGIN_LEFT), $config->get('pdf_margin_top', 0), $config->get('pdf_margin_right', PDF_MARGIN_RIGHT));
		$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(true, $config->get('pdf_margin_bottom', PDF_MARGIN_BOTTOM));

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$font = empty($config->pdf_font) ? 'times' : $config->pdf_font;

		// True type font
		if (substr($font, -4) == '.ttf')
		{
			$font = TCPDF_FONTS::addTTFfont(JPATH_ROOT . '/components/com_eventbooking/tcpdf/fonts/' . $font, 'TrueTypeUnicode', '', 96);
		}

		$pdf->SetFont($font, '', 8);

		return $pdf;
	}

	/**
	 * Method to set background image for a PDF page
	 *
	 * @param   TCPDF  $pdf
	 *
	 * @param   array  $options
	 */
	protected static function setPageBackgroundImage($pdf, $options)
	{
		$options = new Registry($options);

		// Handle background image
		if ($options->get('bg_image'))
		{
			$backgroundImage  = $options->get('bg_image');
			$backgroundLeft   = $options->get('bg_left', '');
			$backgroundTop    = $options->get('bg_top', '');
			$backgroundWidth  = $options->get('bg_width', 0);
			$backgroundHeight = $options->get('bg_height', 0);

			// Get current  break margin
			$breakMargin = $pdf->getBreakMargin();
			// get current auto-page-break mode
			$autoPageBreak = $pdf->getAutoPageBreak();
			// disable auto-page-break
			$pdf->SetAutoPageBreak(false, 0);
			// set background image
			$pdf->Image($backgroundImage, $backgroundLeft, $backgroundTop, $backgroundWidth, $backgroundHeight);
			// restore auto-page-break status
			$pdf->SetAutoPageBreak($autoPageBreak, $breakMargin);
			// set the starting point for the page content
			$pdf->setPageMark();
		}
	}
}