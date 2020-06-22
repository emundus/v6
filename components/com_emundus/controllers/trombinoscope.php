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


    /**
     * @param array $config
     */
    public function __construct($config = array()) {
        parent::__construct($config);
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
        $jinput = JFactory::getApplication()->input;
        $gridL = $jinput->get('gridL');
        $gridH = $jinput->get('gridH');
        $margin = $jinput->get('margin');
        $template = $jinput->post->get('template', null, 'raw');
        $string_fnums = $jinput->post->get('string_fnums', null, 'raw');
        $generate = $jinput->get('generate');
        $border = $jinput->get('border');
        $fnums = $this->fnums_json_decode($string_fnums);
        // Génération du HTML
        $html_content = $this->generate_data_for_pdf($fnums, $gridL, $gridH, $margin, $template, false, false, $generate, false, false, $border);
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
    public function generate_data_for_pdf($fnums, $gridL, $gridH, $margin, $template, $templHeader, $templFooter,  $generate, $preview = false, $checkHeader = false, $border) {
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
        include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

        $emails = new EmundusModelEmails();
        $body = '';
        $nb_cell = 0;
        $tab_body = array();
        foreach ($fnums as $fnum) {
            $post = array('FNUM' => $fnum['fnum']);
            $tags = $emails->setTags($fnum["applicant_id"], $post);
            $body_tags = preg_replace($tags['patterns'], $tags['replacements'], $template);
            $body_tmp = $emails->setTagsFabrik($body_tags, array($fnum["fnum"]));
            $body .= $body_tmp;
            $tab_body[] = $body_tmp;
            $nb_cell++;
        }
        require_once (JPATH_COMPONENT.DS.'models'.DS.'trombinoscope.php');
        $trombi = new EmundusModelTrombinoscope();
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
        $trombi = new EmundusModelTrombinoscope();
        $htmlLetters = $trombi->selectHTMLLetters();
        $templ = [];
        foreach ($htmlLetters as $letter){
            $templ[$letter['attachment_id']] = $letter;
        }

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
    whitespace: no-wrap;
    line-height: 1;
    width: '.$cell_width.'px;
    height: '.$cell_height.'px;
    margin: '. implode(' ', $tab_margin).';
  
}

/** Define now the real margins of every page in the PDF **/
.em-body {
    margin-top: 330px;
}

/** Define the header rules **/
header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 330px;
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
            $header = preg_replace_callback('/< *img[^>]*src *= *["\']?([^"\']*)/i', function ($match) {
            	$src = $match[1];
	            if (substr($src, 0, 1) === '/') {
	            	$src = substr($src, 1);
	            }
                return '<img src="'.JURI::base().$src;
            }, $templHeader);
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
        $jinput = JFactory::getApplication()->input;
        $gridL = $jinput->get('gridL');
        $gridH = $jinput->get('gridH');
        $margin = $jinput->get('margin');
        $template = $jinput->post->get('template', '', 'raw');
        $header = $jinput->post->get('header', '', 'raw');
        $footer = $jinput->post->get('footer', '', 'raw');
        $string_fnums = $jinput->post->get('string_fnums', null, 'raw');
        $generate = $jinput->get('generate');
        $checkHeader = $jinput->get('checkHeader');
        $format = $jinput->get('format');
        $border = $jinput->get('border');
        
        $fnums = $this->fnums_json_decode($string_fnums);
        $html_content = $this->generate_data_for_pdf($fnums, $gridL, $gridH, $margin, $template, $header, $footer, $generate, false, $checkHeader, $border);

        require_once (JPATH_COMPONENT.DS.'models'.DS.'trombinoscope.php');
        $m_trombinoscrope = new EmundusModelTrombinoscope();
        $generated_pdf_url = $m_trombinoscrope->generate_pdf($html_content, $format);

        $return_value = json_encode(array(
            'pdf_url' => $generated_pdf_url
        ));
        echo $return_value;
        exit();
    }
}