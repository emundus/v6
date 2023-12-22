<?php
use setasign\Fpdi\Tcpdf\Fpdi;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Css;

function pdf_evaluation($user_id, $fnum = null, $output = true, $name = null, $options = []) {
    jimport( 'joomla.html.parameter' );
    set_time_limit(0);
	require_once (JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'date.php');

    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
    include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
    include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
    include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

    $m_profile = new EmundusModelProfile;
    $m_files = new EmundusModelFiles;

    $db = JFactory::getDBO();
    $app = JFactory::getApplication();
    $config = JFactory::getConfig();
    $user = $m_profile->getEmundusUser($user_id);
    $fnum = empty($fnum) ? $user->fnum : $fnum;

    $infos = $m_profile->getFnumDetails($fnum);
    $campaign_id = $infos['campaign_id'];

	$anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);


	// Users informations
    $query = 'SELECT u.id AS user_id, c.firstname, c.lastname, a.filename AS avatar, p.label AS cb_profile, c.profile, esc.label, esc.year AS cb_schoolyear, esc.training, u.id, u.registerDate, u.email, epd.gender, epd.nationality, epd.birth_date, ed.user, ecc.date_submitted
                FROM #__emundus_campaign_candidature AS ecc
                LEFT JOIN #__users AS u ON u.id=ecc.applicant_id 
                LEFT JOIN #__emundus_users AS c ON u.id = c.user_id
                LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = '.$campaign_id.'  
                LEFT JOIN #__emundus_uploads AS a ON a.user_id=u.id AND a.attachment_id = '.EMUNDUS_PHOTO_AID.' AND a.fnum like '.$db->Quote($fnum).' 
                LEFT JOIN #__emundus_setup_profiles AS p ON p.id = esc.profile_id
                LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = u.id AND epd.fnum like '.$db->Quote($fnum).' 
                LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id AND ed.fnum like '.$db->Quote($fnum).' 
                WHERE ecc.fnum like '.$db->Quote($fnum).' 
                ORDER BY esc.id DESC';
    try {

        $db->setQuery($query);
        $item = $db->loadObject();

    } catch (Exception $e) {
        throw $e;
    }

    //get logo
    $template = $app->getTemplate(true);
    $params = $template->params;

    if (!empty($params->get('logo')->custom->image)) {
        $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
        $logo = !empty($logo['path']) ? JPATH_ROOT.DS.$logo['path'] : "";
    } else {
        $logo_module = JModuleHelper::getModuleById('90');
        preg_match('#src="(.*?)"#i', $logo_module->content, $tab);

	    $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";
	    if ((bool) preg_match($pattern, $tab[1])) {
		    $tab[1] = parse_url($tab[1], PHP_URL_PATH);
	    }

        $logo = JPATH_SITE.DS.$tab[1];
    }

	// manage logo by programme
	$ext = substr($logo, -3);
	$logo_prg = substr($logo, 0, -4) . '-' . $item->training . '.' . $ext;
	if (is_file($logo_prg)) {
		$logo = $logo_prg;
	}
	$type = pathinfo($logo, PATHINFO_EXTENSION);
	$data = file_get_contents($logo);
	$logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	/* END LOGO */

    //get title
	$htmldata = '';
	if (!$anonymize_data) {
		$title = strtoupper(@$item->lastname) . ' ' . @$item->firstname;
	} else {
		$title = $config->get('sitename');
	}

	$htmldata .= '<html>
				<head>
				  <title>'.$title.'</title>
				  <meta name="author" content="eMundus">
				</head>
				<body>';
	$htmldata .= '<header><table style="width: 100%"><tr><td><img src="'. $logo_base64 .'" width="auto" height="60"/></td><td style="text-align: right">';

	$allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);

	if ($options[0] != "0" && !$anonymize_data && ($allowed_attachments === true || in_array('10', $allowed_attachments))) {
		$date_submitted = (!empty($item->date_submitted) && strpos($item->date_submitted, '0000') === false) ? EmundusHelperDate::displayDate($item->date_submitted) : JText::_('NOT_SENT');

		// Create an date object
		$date_printed = new Date();
		//Use helper date function to set timezone an format
		$date_printed = HtmlHelper::date($date_printed, Text::_('DATE_FORMAT_LC2'));

		if (!$anonymize_data) {
			$htmldata .= '<p><b>' . JText::_('PDF_HEADER_INFO_CANDIDAT') . ' :</b> ' . @$item->firstname . ' ' . strtoupper(@$item->lastname) . '</p>';
		}

		if (!$anonymize_data && (in_array("aemail", $options) || empty($options))) {
			$htmldata .= '<p><b>' . JText::_('EMAIL') . ' :</b> ' . @$item->email . '</p>';
		}
		if (in_array("afnum", $options) || empty($options)) {
			$htmldata .= '<p><b>' . JText::_('FNUM') . ' :</b> ' . $fnum . '</p>';
		}
		$htmldata .= '</td></table><hr/></header>';

		$htmldata .= '<table width="100%"><tr>';

		//$htmldata .= '<td><h3>' . JText::_('PDF_HEADER_INFO_CANDIDAT') . '</h3></td></tr>';
		if(!empty($item->avatar) && is_image_ext($item->avatar))
		{
			if (file_exists(EMUNDUS_PATH_ABS . @$item->user_id . '/tn_' . @$item->avatar))
			{
				$avatar        = EMUNDUS_PATH_ABS . @$item->user_id . '/tn_' . @$item->avatar;
			}
			elseif (file_exists(EMUNDUS_PATH_ABS . @$item->user_id . '/' . @$item->avatar) && !empty($item->avatar) && is_image_ext($item->avatar))
			{
				$avatar        = EMUNDUS_PATH_ABS . @$item->user_id . '/' . @$item->avatar;
			}

			if(!empty($avatar))
			{
				$type          = pathinfo($avatar, PATHINFO_EXTENSION);
				$data          = file_get_contents($avatar);
				$avatar_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

				$htmldata .= '<tr><td><img style="border-radius: 50%" src="'. $avatar_base64 .'" width="auto" height="60" align="right"/></td></tr>';
			}
		}

		if (in_array("aid", $options) || empty($options)) {
			$htmldata .=
				'<tr>
                                    <td class="idcandidat"><b>' . JText::_('ID_CANDIDAT') . ' :</b> ' . @$item->user_id . '</td>
                                </tr>';
		}

		$htmldata .= '<tr><td><h3>' . JText::_('PDF_HEADER_INFO_DOSSIER') . '</h3></td></tr><tr><td class="name">' . @$item->label . ' (' . @$item->cb_schoolyear . ')</td></tr>';

		if (in_array("afnum", $options) || empty($options)) {
			$htmldata .= '<tr class="nationality"><td><b>' . JText::_('FNUM') . ' :</b> ' . $fnum . '</td></tr>';
		}

		if (in_array("aapp-sent", $options) || empty($options)) {
			$htmldata .= '<tr><td class="statut"><b>' . JText::_('APPLICATION_SENT_ON') . ' :</b> ' . $date_submitted . '</td></tr>';
		}

		if (in_array("adoc-print", $options) || empty($options)) {
			$htmldata .= '<tr class="sent"><td><b>' . JText::_('DOCUMENT_PRINTED_ON') . ' :</b> ' . $date_printed . '</td></tr>';
		}

		if (in_array("status", $options)) {
			$status = $m_files->getStatusByFnums(explode(',', $fnum));
			$htmldata .= '<tr class="sent"><td><b>' . JText::_('COM_EMUNDUS_EXPORTS_PDF_STATUS') . ' :</b> ' . $status[$fnum]['value'] . '</td></tr>';
		}

		$htmldata .= '</table>';

		if (in_array("tags", $options)) {
			$tags = $m_files->getTagsByFnum(explode(',', $fnum));
			$htmldata .= '<table style="margin-top: 8px"><tr><td> ';
			foreach ($tags as $tag) {
				$htmldata .= '<span class="label ' . $tag['class'] . '">' . $tag['label'] . '</span>&nbsp;';
			}
			$htmldata .= '</td></tr></table>';
		}
	} else {
		$htmldata .= '</td></table><hr/></header>';
	}

    /*** Applicant ***/
	$htmldata .= "
			<style>
					@page { margin: 130px 25px; }
					header { position: fixed; top: -120px; left: 0px; right: 0px; }
					header hr {
						border: none;
						height: 1px;
						background-color: #A4A4A4;
					}
					.page-break { page-break-before: always; }
					hr {
						border: solid 1px black;
					}
					h2 {
						font-size: 18px;
						line-height: 16px;
						margin-top: 16px;
						margin-bottom: 12px;
					}
					h2.pdf-page-title{
					    background-color: #EAEAEA;
					    padding: 10px 12px;
					    border-radius: 2px;
					    margin-right: 16px;
					}
					h3 {
					  font-style: normal;
					  font-weight: 600;
					  font-size: 16px;
					  line-height: 14px;
					  margin-bottom: 8px;
                    }
                    h3.group{
                      padding-left: 16px;
                    }
                    td{
                    	font-size: 12px;
                    }
                    .pdf-forms{
                   	   border-spacing: 0;
                    }
                    .pdf-repeat-count{
                       margin-top: 12px;
                       margin-bottom: 6px;
                       padding-left: 16px; 
                    }
                    .pdf-forms th{
                       font-size: 12px;
                       font-weight: 400;
                    }
                    .pdf-forms th.background{
                       background-color: #EDEDED;
                       border-top: solid 1px #A4A4A4;
                       border-left: solid 1px #A4A4A4;
                       border-right: solid 1px #A4A4A4;
                    }
                    table.pdf-forms{
                       width: 100%;
                       page-break-inside:auto;
                       padding: 0 16px;
                    }
                    .pdf-forms tr{
                       page-break-inside:avoid; 
                       page-break-after:auto
                    }
                    .pdf-forms td{
                       border-collapse: collapse;
                       padding: 8px;
                       width: 100%;
                       border-left: solid 1px #A4A4A4;
  					   border-top: solid 1px #A4A4A4;
                    }
                    .pdf-forms tr td:first-child {
  					   width: 30%;
					}
                    .pdf-forms tr td:nth-child(2){
                       width:70%; 
                       border-right: solid 1px #A4A4A4;
                    }
                    .pdf-forms td.background-light{
                       width: auto;
                    }
                    .pdf-forms tr td[colspan='2']{
                       border-right: solid 1px #A4A4A4;
                    }
                    .pdf-forms tr:last-child td{
                       border-bottom: solid 1px #A4A4A4;
                    }
                    .pdf-forms tr:last-child td.background-light{
                       border-right: solid 1px #A4A4A4 !important;
                    }
                    .pdf-attachments{
                       font-size: 14px;
                    }
                    .pdf-attachments li {
                       margin-bottom: 6px;
                    }
                    @media print {
                        .breaker{
                            page-break-before: always;
                        }
                    }
                    .label {color:black;padding: 6px 12px;border-radius: 4px;}
		            .label-default {background-color:#999999;}
		            .label-primary {background-color:#337ab7;}
		            .label-success {background-color:#5cb85c;}
		            .label-info    {background-color:#033c73;}
		            .label-warning {background-color:#dd5600;}
		            .label-danger  {background-color:#c71c22;}
		            .label-lightpurple { background-color: #DCC6E0 }
		            .label-purple { background-color: #947CB0 }
		            .label-darkpurple {background-color: #663399 }
		            .label-lightblue { background-color: #6bb9F0 }
		            .label-blue { background-color: #19B5FE }
		            .label-darkblue { background-color: #013243 }
		            .label-lightgreen { background-color: #00E640 }
		            .label-green { background-color: #3FC380 }
		            .label-darkgreen { background-color: #1E824C }
		            .label-lightyellow { background-color: #FFFD7E }
		            .label-yellow { background-color: #FFFD54 }
		            .label-darkyellow { background-color: #F7CA18 }
		            .label-lightorange { background-color: #FABE58 }
		            .label-orange { background-color: #E87E04 }
		            .label-darkorange {background-color: #D35400 }
		            .label-lightred { background-color: #EC644B }
		            .label-red { background-color: #CF000F }
		            .label-darkred { background-color: #96281B }
		            .label-lightpink { background-color: #e08283; }
		            .label-pink { background-color: #d2527f; }
		            .label-darkpink { background-color: #db0a5b; }
			</style>";

    if (!function_exists('exif_imagetype')) {
        function exif_imagetype($filename) {
            if (( list($width, $height, $type, $attr) = getimagesize( $filename )) !== false) {
                return $type;
            }
        return false;
        }
    }

    /**  END APPLICANT   ****/

    // get evaluation
    $evaluation = new EmundusHelperFiles();
    $data = $evaluation->getEvaluation('html',$fnum);
	if (empty($data)) {
		$htmldata .= '<p>'.JText::_('COM_EMUNDUS_NO_EVALUATIONS_FOUND').'</p>';
	} else {
		foreach ($data as $evals) {
			foreach ($evals as $user => $html) {
				$htmldata .= $html;
			}
		}
	}

	if (!file_exists(EMUNDUS_PATH_ABS . @$item->user_id)) {
		mkdir(EMUNDUS_PATH_ABS . $item->user_id, 0777, true);
		chmod(EMUNDUS_PATH_ABS . $item->user_id, 0777);
	}

	$htmldata .= '<script type="text/php">
			        if ( isset($pdf) ) {
			            $x = 570;
			            $y = 760;
			            $text = "{PAGE_NUM} / {PAGE_COUNT}";
			            $font = $fontMetrics->get_font("helvetica", "bold");
			            $size = 8;
			            $color = array(0,0,0);
			            $word_space = 0.0;  //  default
			            $char_space = 0.0;  //  default
			            $angle = 0.0;   //  default
			            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
			        }
    			</script>';
	$htmldata .= '</body></html>';

	if (is_null($name)) {
		$filename = EMUNDUS_PATH_ABS.$item->user_id.DS.'evaluations.pdf';
	} else {
		$filename = $name;
	}

	/** DOMPDF */
	$options = new Options();
	$options->set('defaultFont', 'helvetica');
	$options->set('isPhpEnabled', true);
	$dompdf = new Dompdf($options);

	try {
		$dompdf->loadHtml($htmldata);
		$dompdf->render();

		if($output) {
			$dompdf->stream($filename, array("Attachment" => false));
		} else {
			file_put_contents($filename, $dompdf->output());
			return $filename;
		}
	}
	catch (Exception $e) {
		JLog::add('Error when export following file to PDF : ' . $fnum . ' with error ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		return false;
	}
	/** END */
}
