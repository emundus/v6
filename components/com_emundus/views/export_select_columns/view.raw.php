<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewExport_select_columns extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();


		parent::__construct($config);
	}
	
	
    function display($tpl = null)
    {
		//$document = JFactory::getDocument();
		//$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
        $jinput = JFactory::getApplication()->input;
        $prg = $jinput->get('code', null);
        $code = array();
        $code[] = $prg;
		$current_user = JFactory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die(JText::_('ACCESS_DENIED'));

        //@TODO fix bug when a different application form is created for the same programme. Need to now the campaign id, then associated profile and menu links...
		$elements = EmundusHelperFiles::getElements($code);
		$this->assignRef('elements', $elements);
		
		parent::display($tpl);
    }
}
?>