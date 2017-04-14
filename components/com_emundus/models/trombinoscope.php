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
    public $trombi_tpl = '';
    public $badge_tpl = '';
    public $default_margin = '10';

    public $pdf_margin_top = 15;
    public $pdf_margin_right = 0;
    public $pdf_margin_left = 0;
    public $pdf_margin_header = 0;
    public $pdf_margin_footer = 0;

    public function __construct()
    {
        $this->trombi_tpl = '
<table cellpadding="2" cellspacing="2" style="border: 1px solid #666; border-collapse: collapse; width: 100%;">
  <tbody>
    <tr style="border: 1px solid #666; border-collapse: collapse;">
      <td align="center" valign="top" style="text-align: center;"><p style="text-align: center;"> <img src="[PHOTO]" alt="Photo" /> </p>
        <p style="text-align: center;"> <b>${2964}, ${2963}</b><br />
          ${2967}<br />
          ${2977}<br />
          ${2978} </p></td>
    </tr>
  </tbody>
</table>';
        $this->badge_tpl = '
<table style="border: 1px solid #666; border-collapse: collapse; width: 100%; height: 100%">
    <tbody>
        <tr style="border: 1px solid #666; border-collapse: collapse;">
            <td style="vertical-align: top; border-right: 1px solid #666; width: 30%;"><img src="[LOGO]" alt="" /></td>
            <td style="vertical-align: top; width: 70%;">
                <p>${2964}, ${2963}<br />${2967}<br />${2977}<br />${2978}</p>
            </td>
        </tr>
    </tbody>
</table>';

        parent::__construct();
    }

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
        // On peut alors boucler sur toutes les pages
        //$pdf->AddPage();
        for ($i=0; $i<count($tab_html); $i++) {
            $pdf->AddPage();
            $pdf->writeHTML($tab_html[$i], true, false, false, false, '');
        }

        $fileName = "trombinoscope-".time()."pdf";
        $tmpName = JPATH_BASE.DS.'tmp'.DS.$fileName;
        $pdf->Output($tmpName, 'F');

        return JURI::base().'tmp'.DS.$fileName;
    }
}