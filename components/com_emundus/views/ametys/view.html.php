<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
//error_reporting(E_ALL);
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewAmetys extends JViewLegacy
{
	protected $itemId;
	protected $task;
	protected $token;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

    public function display($tpl = null)
    {
    	// translation to load in javacript file ; /media/com_emundus/em_files.js
    	// put it in com_emundus/emundus.php

    	$current_user = JFactory::getUser();
		if( !EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id) )
			die( JText::_('RESTRICTED_ACCESS') );

		$document = JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css" );
		// overide css
		$menu = @JSite::getMenu();
        $current_menu = $menu->getActive();
        $menu_params = $menu->getParams($current_menu->id);

		$page_heading = $menu_params->get('page_heading', '');
		$pageclass_sfx = $menu_params->get('pageclass_sfx', '');
		if (!empty($page_heading)) {
			$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$page_heading.".css" );
		}

	    $this->itemId = $current_menu->id;
	    $this->task = JFactory::getApplication()->input->getInt('task', null);
	    $this->token = JFactory::getApplication()->input->getInt('token', null);
		
		parent::display($tpl);
	}

}
?>

