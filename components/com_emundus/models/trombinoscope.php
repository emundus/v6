<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28/03/2017
 * Time: 01:13
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class EmundusModelTrombinoscope extends JModelLegacy
{

	/* public $trombi_tpl = '
 <table cellpadding="2" style="width: 100%;">
   <tbody>
	 <tr style="border-collapse: collapse;">
	   <td align="center" valign="top" style="text-align: center;">
		 <p style="text-align: center;"><img src="[PHOTO]" alt="Photo" height="100" /> </p>
		 <p style="text-align: center;"><b>[NAME]</b><br /></p>
	   </td>
	 </tr>
   </tbody>
 </table>';


	 public $badge_tpl = '<table width="100%">
   <tbody>
	 <tr>
	   <td width="30%" align="left" valign="middle" style="vertical-align: top; width: 100px;"><img src="[LOGO]" alt="Logo" height="50" /></td>
	   <td width="70%" align="left" valign="top" style="vertical-align: top;"><b>[NAME]</b></td>
	 </tr>
   </tbody>
 </table>
 ';*/
	public $default_margin = '5';
	public $default_header_height = '330';

	public $pdf_margin_top = 0;
	public $pdf_margin_right = 0;
	public $pdf_margin_left = 0;
	public $pdf_margin_header = 0;
	public $pdf_margin_footer = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function fnums_json_decode($string_fnums)
	{
		$fnums_obj = (array) json_decode(stripslashes($string_fnums), false, 512, JSON_BIGINT_AS_STRING);

		if (@$fnums_obj[0] == 'all') {
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus/models', 'EmunusModel');
			$model = JModelLegacy::getInstance('Files', 'EmundusModel', array('ignore_request' => true));

			$assoc_tab_fnums = true;
			$fnums           = $model->getAllFnums($assoc_tab_fnums);
		}
		else {
			$fnums = array();
			foreach ($fnums_obj as $key => $value) {
				if (@$value->sid > 0) {
					$fnums[] = array('fnum'         => @$value->fnum,
					                 'applicant_id' => @$value->sid,
					                 'campaign_id'  => @$value->cid
					);
				}
			}
		}

		return $fnums;
	}

	public function set_template($programme_code, $format = 'trombi')
	{

		if (!empty($programme_code)) {
			$db = JFactory::getDBO();
			if ($format == 'trombi') {
				try {
					$query = 'SELECT tmpl_trombinoscope FROM #__emundus_setup_programmes WHERE code like ' . $db->quote($programme_code);
					$db->setQuery($query);
					$this->trombi_tpl = $db->loadResult();
				}
				catch (Exception $e) {
					$query = "ALTER TABLE `jos_emundus_setup_programmes` ADD `tmpl_trombinoscope` VARCHAR(2048) NULL DEFAULT " . $db->quote($this->trombi_tpl);
					$db->setQuery($query);
					$db->execute();
					error_log($e->getMessage(), 0);
					echo $e->getMessage();
				}
			}
			else {
				try {
					$query = 'SELECT tmpl_badge FROM #__emundus_setup_programmes WHERE code like ' . $db->quote($programme_code);
					$db->setQuery($query);
					$this->badge_tpl = $db->loadResult();
				}
				catch (Exception $e) {
					$query = "ALTER TABLE `#__emundus_setup_programmes` ADD `tmpl_badge` VARCHAR(2048) NULL DEFAULT " . $db->quote($this->badge_tpl);
					$db->setQuery($query);
					$db->execute();
					error_log($e->getMessage(), 0);
					echo $e->getMessage();
				}
			}
		}
	}

	/**
	 * @param $fnum
	 *
	 * @return Exception|mixed|Exception
	 */
	public function getProgByFnum($fnum)
	{
		$db = $this->getDbo();
		try {
			$query = 'select  jesp.id, jesp.code, jesp.label  from #__emundus_campaign_candidature as jecc
                        left join #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
                        left join #__emundus_setup_programmes as jesp on jesp.code like jesc.training
                        where jecc.fnum like ' . $db->quote($fnum);
			$db->setQuery($query);

			return $db->loadAssoc();
		}
		catch (Exception $e) {
			return $e;
		}
	}

	/*
		// TCPDF
		public function generate_pdf($html_value) {

			jimport( 'joomla.html.parameter' );

			set_time_limit(0);
			require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
			require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('www.emundus.fr');
			$pdf->SetFont('helvetica', '', 8);

			// set margins
			$pdf->SetMargins($this->pdf_margin_left, $this->pdf_margin_top, $this->pdf_margin_right);
			$pdf->SetHeaderMargin($this->pdf_margin_header);
			$pdf->SetFooterMargin($this->pdf_margin_footer);

			// Il faut découper $html_value par page, donc on va passer par un tableau
			$tab_html = explode('###', $html_value);

			for ($i=0; $i<count($tab_html); $i++) {
				$pdf->AddPage();
				$pdf->writeHTML($tab_html[$i], true, false, false, false, '');
			}

			$fileName = "trombinoscope-".time().".pdf";
			$tmpName = JPATH_SITE.DS.'tmp'.DS.$fileName;
			$pdf->Output($tmpName, 'F');

			return JURI::base().'tmp'.DS.$fileName;
		}
	*/
	// DOMPDF
	public function generate_pdf($html_value, $format)
	{

		set_time_limit(0);

		require_once(JPATH_LIBRARIES . DS . 'dompdf' . DS . 'dompdf_config.inc.php');
		$lbl = $this->selectLabelSetupAttachments($format);

		$fileName = $lbl['lbl'] . "_" . time() . ".pdf";
		$tmpName  = JPATH_SITE . DS . 'tmp' . DS . $fileName;

		$pdf = new DOMPDF();
		$pdf->set_paper("A4", "portrait");
		$pdf->set_option('enable_remote', true);
		$pdf->set_option('enable_css_float', true);
		$pdf->set_option('enable_html5_parser', true);

		$pdf->load_html($html_value);
		$pdf->render();

		$output = $pdf->output();
		file_put_contents($tmpName, $output);

		return JURI::base() . 'tmp' . DS . $fileName;
	}

	public function selectHTMLLetters()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('title'), $db->quoteName('attachment_id'), $db->quoteName('body'), $db->quoteName('header'), $db->quoteName('footer')))
			->from($db->quoteName('#__emundus_setup_letters'))
			->where($db->quoteName('template_type') . ' = 2');

		$db->setQuery($query);

		return $db->loadAssocList();
	}

	public function selectLabelSetupAttachments($attachment_id)
	{
		$attachment = [];

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('lbl'))
			->from($db->quoteName('#__emundus_setup_attachments', 'esa'))
			->join('INNER', $db->quoteName('#__emundus_setup_letters', 'esl') . ' ON (' . $db->quoteName('esa.id') . ' = ' . $db->quoteName('esl.attachment_id') . ')')
			->where($db->quoteName('esl.attachment_id') . ' = ' . $attachment_id);

		$db->setQuery($query);

		try {
			$attachment = $db->loadAssoc();
		}
		catch (Exception $e) {
			JLog::add('Failed to select attachment attachment label' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $attachment;
	}
}
