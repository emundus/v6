<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @copyright	Copyright (C) 2016 eMundus SAS. All rights reserved.
 * @license    GNU/GPL
 * @author     eMundus SAS - Benjamin Rivalland
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * campaign View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusViewCampaign extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getSession()->get('emundusUser');
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {
		/*
		$menu=JFactory::getApplication()->getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) die("You are not allowed to access to this page.");
		*/

		$active_campaigns = $this->get('ActiveCampaign');
		$this->assignRef('active_campaigns', $active_campaigns);
		
		$my_campaigns = $this->get('MyCampaign');
		$this->assignRef('my_campaigns', $my_campaigns);
		
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
		
		$state = $this->get( 'state' );
		$lists['filter_order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['filter_order']     = $state->get( 'filter_order' );
        
		$this->assignRef( 'lists', $lists );
		
		parent::display($tpl);
    }
}
?>