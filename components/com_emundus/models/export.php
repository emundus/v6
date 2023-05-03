<?php
/**
 * Export  Model for eMundus Component
 *
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

use Gotenberg\Gotenberg;
use Gotenberg\Stream;
 
class EmundusModelExport extends JModelList {

	var $_db = null;
    var $_user = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct(){
		parent::__construct();
        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getSession()->get('emundusUser');

        JLog::addLogger(['text_file' => 'com_emundus.export.php'], JLog::ERROR);
	}

    /*
    * 	export file to PDF
    *	@param file_src 		path to file source
    *	@param file_dest 		path to file dest
    *	@param file_src_format 	default office, html for other
    *	@param fnum 		    Application file number
    * 	@return Object
    */
	function toPdf($file_src, $file_dest, $file_src_format = null, $fnum = null)
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $gotenberg_activation = $eMConfig->get('gotenberg_activation', 0);
        $gotenberg_url = $eMConfig->get('gotenberg_url', 'http://localhost:3000');
        $gotenberg_ssl = (bool)$eMConfig->get('gotenberg_ssl', 1);

        $res = new stdClass();

        if ($gotenberg_activation != 1) {
            $res->status = false;
            $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_API_DESACTIVATED');
            return json_encode($res);
        }

        $user_id = !empty($fnum) ? (int)substr($fnum, -7) : null;
		$em_user = JFactory::getSession()->get('emundusUser');

        if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum) || $fnum == $em_user->fnum) {
            require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';

            $src   = $file_src;
	        $file = explode('/', $file_src);
	        $file  = end($file);

	        $dest = explode('/', $file_dest);
	        $dest_file  = array_pop($dest);
			$dest_path = implode('/',$dest);

            try {
                if ($file_src_format != 'html') {
	                $request = Gotenberg::libreOffice($gotenberg_url)
		                ->outputFilename($dest_file)
		                ->convert(
			                Stream::path($file_src)
		                );

	                Gotenberg::save($request, $dest_path .'/');
                } else {
	                $request = Gotenberg::chromium($gotenberg_url)
		                ->html(Stream::string('my.html', $src));

	                Gotenberg::save($request, $dest_path .'/');
                }
				$res->file = $dest_path .'/' . $dest_file . '.pdf';
            } catch (\Gotenberg\Exceptions\GotenbergApiErroed $e) {
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_MARGIN').' GOTEMBERG ERROR ('.$e->getCode().'): '.$e->getResponse();
                JLog::add($res->msg, JLog::ERROR, 'com_emundus.export');
                return json_encode($res);
            }

            $res->status = true;
            return $res;
        }
        else
        {
            $res->status = false;
            $res->msg = JText::_('ACCESS_DENIED');
            JLog::add($res->msg, JLog::ERROR, 'com_emundus.export');
            return $res;
        }
    }
}
