<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;
/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 */
/**
 * Class EmundusControllerTrombinoscope
 */
class EmundusControllerTrombinoscope extends EmundusController {

	protected $app;

    /**
     * @param array $config
     */
    public function __construct($config = array()) {
        parent::__construct($config);

		$this->app = Factory::getApplication();
    }


	/**
	 * @param $string_fnums
	 *
	 * @return array
	 *
	 * @since version
	 */
    public function fnums_json_decode($string_fnums) {
        $fnums_obj = (array) json_decode(stripslashes($string_fnums), false, 512, JSON_BIGINT_AS_STRING);
        if (@$fnums_obj[0] == 'all') {
            $m_files = $this->getmodel('Files');
            $assoc_tab_fnums = true;
            $fnums = $m_files->getAllFnums($assoc_tab_fnums);
        } else {
            $fnums = array();
            foreach ($fnums_obj as $value) {
                if (@$value->sid > 0) {
                    $fnums[] = array( 'fnum' => @$value->fnum,
                        'applicant_id' => @$value->sid,
                        'campaign_id' => @$value->cid
                    );
                }
            }
        }
        return $fnums;
    }


	/**
	 * Génération de code HTML pour l'affichage de la 1ère page de prévisualisation
	 *
	 * @throws Exception
	 * @since version
	 */
    public function generate_preview() {
        
        $gridL = $this->input->get('gridL');
        $gridH = $this->input->get('gridH');
        $margin = $this->input->get('margin');
        $template = $this->input->post->get('template', null, 'raw');
        $string_fnums = $this->input->post->get('string_fnums', null, 'raw');
        $generate = $this->input->get('generate');
        $border = $this->input->get('border');
        $fnums = $this->fnums_json_decode($string_fnums);
        $headerHeight = $this->input->get('headerHeight');
        // Génération du HTML
        $html_content = $this->generate_data_for_pdf($fnums, $gridL, $gridH, $margin, $template, false, false, $generate, false, false, $border, $headerHeight);
        $value =  array(
            'html_content' => $html_content
        );
        $return = json_encode($value);
        echo $return;
        exit;
    }


    /**
     * Génération du code HTML qui sera envoyé soit pour cosntruire le pdf, soit pour afficher la prévisualisation
     *
     * @param         $fnums
     * @param         $gridL
     * @param         $gridH
     * @param         $margin
     * @param         $template
     * @param         $templHeader
     * @param         $templFooter
     * @param         $generate
     * @param   bool  $preview
     * @param   bool  $checkHeader
     * @param         $border
     *
     * @return string
     *
     * @throws Exception
     * @since version
     */
    public function generate_data_for_pdf($fnums, $gridL, $gridH, $margin, $template, $templHeader, $templFooter,  $generate, $preview = false, $checkHeader = false, $border = null, $headerHeight = null) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $m_files = $this->getModel('Files');
        // Traitement du nombre de colonnes max par ligne
        $nb_col_max = $gridL;
        $nb_li_max = $gridH;
        $tab_margin = explode(',', $margin);
        // Il faut ajouter px à la fin
        for ($i = 0; $i < count($tab_margin); $i++) {
            $tab_margin[$i] .= 'px';
        }

        if (count($tab_margin) > 1) {
            // L'utilisateur a séparé les marges par des virgules, il faut les séparer par des espaces pour le css
            $marge_css_top = $tab_margin[0];
            $marge_css_left = $tab_margin[1];
            $marge_css_right = $tab_margin[2];
            $marge_css_bottom = $tab_margin[3];
        } else {
            $marge_css_top = $tab_margin[0];
            $marge_css_left = $tab_margin[0];
            $marge_css_right = $tab_margin[0];
            $marge_css_bottom = $tab_margin[0];
        }
        // Génération du HTML
        include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');

        $emails = $this->getModel('Emails');
        $body = '';
        $nb_cell = 0;
        $tab_body = array();
        $fnumInfo = $m_files->getFnumInfos($fnums[0]['fnum']);

        $template = preg_replace_callback('/< *img[^>]*src *= *["\']?([^"\']*)/i', function ($match) {
            $src = $match[1];
            if (substr($src, 0, 1) === '/') {
                $src = substr($src, 1);
            }
            return '<img src="'.$src;
        }, $template);

        foreach ($fnums as $fnum) {
            $post = [
                'FNUM' => $fnum['fnum'],
                'CAMPAIGN_LABEL' => $fnumInfo['label'],
                'CAMPAIGN_YEAR' => $fnumInfo['year'],
                'CAMPAIGN_START' => $fnumInfo['start_date'],
                'CAMPAIGN_END' => $fnumInfo['end_date'],
                'SITE_URL' => JURI::base()
            ];
            $tags = $emails->setTags($fnum["applicant_id"], $post, $fnum['fnum'], '', $template, true);
            $body_tags = preg_replace($tags['patterns'], $tags['replacements'], $template);
            $body_tmp = $emails->setTagsFabrik($body_tags, array($fnum["fnum"]));
            $body .= $body_tmp;
            $tab_body[] = $body_tmp;
            $nb_cell++;
        }
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'trombinoscope.php');
        $trombi = $this->getModel('Trombinoscope');
        $programme = $trombi->getProgByFnum($post['FNUM']);
        // Marge gauche + droite
        $marge_x = $trombi->pdf_margin_left + $trombi->pdf_margin_right;
        // Marge haut + bas par défaut tcpdf
        $marge_y = $trombi->pdf_margin_header + $trombi->pdf_margin_footer;
        // Nombre de cellules par page
        $nb_cell_par_page = $nb_li_max * $nb_col_max;
        // Nombre de pages
        $nb_page = (int)($nb_cell / $nb_cell_par_page);
        // Dans le cas où le reste de la division n'est pas nul
        if ( ($nb_cell % $nb_cell_par_page) > 0 ) {$nb_page++;}
        // Si l'on est en mode preview, on n'ira pas au-delà d'une page
        $nb_page_max = ($preview) ? 1 : $nb_page;
        // Largeur de la page en pixels (page A4 en 92 DPI)
        $largeur_px = 690;
        // Hauteur de la page en pixels (page A4 en 92 DPI)
        $hauteur_px = 900;
        // Largeur d'une cellule
        $cell_width = (int)(($largeur_px - $marge_x - ( ($marge_css_left * $nb_col_max ) + ($marge_css_right * $nb_col_max)) ) / $nb_col_max -10);
        // Hauteur d'une cellule
        $cell_height = (int)(($hauteur_px - $marge_y - ( ($marge_css_bottom * $nb_li_max) + ($marge_css_top * $nb_li_max)) ) / $nb_li_max -10 );
        //border
        if ($border == 1) {
            $borderCSS = '1px solid';
        } else {
            $borderCSS = '0';
        }
        $trombi = $this->getModel('Trombinoscope');
        $htmlLetters = $trombi->selectHTMLLetters();
        $templ = [];
        foreach ($htmlLetters as $letter){
            $templ[$letter['attachment_id']] = $letter;
        }
        $headerH = (empty($headerHeight))?$trombi->default_header_height:$headerHeight;
        $head = '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="author" lang="fr" content="EMUNDUS SAS - https://www.emundus.fr" />
<meta name="generator" content="EMUNDUS SAS - https://www.emundus.fr" />
<title>'.$programme['label'].'</title>
<style>
body {  
    font-family: "Helvetica";
    font-size: 8pt;
    margin: 0;
}

.div-cell {
    border: '.$borderCSS.';
    display: inline-block;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: no-wrap;
    line-height: 1;
    width: '.$cell_width.'px;
    height: '.$cell_height.'px;
    margin: '. implode(' ', $tab_margin).';
  
}

/** Define now the real margins of every page in the PDF **/
.em-body {
    margin-top: '.$headerH.'px;
}

/** Define the header rules **/
header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: '.$headerH.'px;
}

/** Define the footer rules **/
footer {
    position: fixed; 
    bottom: 0; 
    left: 0; 
    right: 0;
    height: 50px;
}
.logo{ width: 100px; height: auto; position: relative; margin-top: 20px; display: inline;}
.title{text-align: center;}
</style>
</head>';
        $body = '';

        $ind_cell = 0;
        for ($cpt_page = 0; $cpt_page < $nb_page_max; $cpt_page++) {

            for ($cpt_li=0 ; $cpt_li<$nb_li_max ; $cpt_li++) {
                for ($cpt_col=0 ; $cpt_col<$nb_col_max && $ind_cell<$nb_cell ; $cpt_col++) {
                    $body .= '<div class="div-cell">'.$tab_body[$ind_cell].'</div>';
                    $ind_cell++;
                }
                $body .= '<br>';
            }
            // Si l'on a plus d'une page, il faut insérer un délimiteur de page pour pouvoir ensuite générer le pdf page par page
            if ($cpt_page > 0) {
                $body .= '<div style="page-break-after: always;"></div>';
            }
        }
        if ($generate == 1) {
            $header_tags = preg_replace($tags['patterns'], $tags['replacements'], $templHeader);
            $header_tmp = $emails->setTagsFabrik($header_tags, array($fnum["fnum"]));
            $header = preg_replace_callback('/< *img[^>]*src *= *["\']?([^"\']*)/i', function ($match) {
                $src = $match[1];
                if (substr($src, 0, 1) === '/') {
                    $src = substr($src, 1);
                }
                return '<img src="'.JURI::base().$src;
            }, $header_tmp);
            $footer = preg_replace_callback('/< *img[^>]*src *= *["\']?([^"\']*)/i', function ($match) {
                $src = $match[1];
                if (substr($src, 0, 1) === '/') {
                    $src = substr($src, 1);
                }
                return '<img src="'.JURI::base().$src;
            }, $templFooter);
        }
        if ($checkHeader == 1) {
            return $head.'<body class="em-body"><header>'.$header.'</header><footer>'.$footer.'</footer><main>'.$body.'</main></body></html>';
        } else {
            return $head.'<body>'.$header.$body.$footer.'</body></html>';
        }
    }

	/**
	 *
	 *
	 * @throws Exception
	 * @since version
	 */
    public function generate_pdf() {
        $response = ['status' => false, 'code' => 403, 'msg' => JText::_('ACCESS_DENIED')];
		$current_user = JFactory::getUser();

		if (EmundusHelperAccess::asAccessAction(31, 'c', $current_user->id)) {
			$response['msg'] = JText::_('BAD_REQUEST');
			
			$format = $this->input->get('format');

			if (!empty($format)) {
				$string_fnums = $this->input->post->get('string_fnums', null, 'raw');
				$fnums = $this->fnums_json_decode($string_fnums);

				if (!empty($fnums)) {
					$gridL = $this->input->get('gridL');
					$gridH = $this->input->get('gridH');
					$margin = $this->input->get('margin');
					$template = $this->input->post->get('template', '', 'raw');
					$header = $this->input->post->get('header', '', 'raw');
					$footer = $this->input->post->get('footer', '', 'raw');
					$generate = $this->input->get('generate');
					$checkHeader = $this->input->get('checkHeader');
					$format = $this->input->get('format');
					$border = $this->input->get('border');
					$headerHeight = $this->input->get('headerHeight');
					$html_content = $this->generate_data_for_pdf($fnums, $gridL, $gridH, $margin, $template, $header, $footer, $generate, false, $checkHeader, $border, $headerHeight);

					if (!empty($html_content)) {
						require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.'/models/trombinoscope.php');
						$m_trombinoscrope = $this->getModel('Trombinoscope');
						$response['pdf_url'] = $m_trombinoscrope->generate_pdf($html_content, $format);
						$response['status'] = true;
						$response['code'] = 200;
						$response['msg'] = JText::_('SUCCESS');
					} else {
						$response['code'] = 500;
						$response['msg'] = JText::_('FAIL');
					}
				}
			}
		}

        echo json_encode($response);
        exit();
    }
}
