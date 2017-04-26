<?php
/**
 * @version   $Id: templateinclude.php 18919 2014-02-20 23:10:04Z kevin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('_JEXEC') or die();
require_once (dirname(__FILE__).'/../BaseRokNavMenuTemplateParams.php');

class JElementTemplateInclude extends JElement
{
	function fetchElement($name, $value, &$node, $control_name)
	{
		global $mainframe;
		$html = "";
		$values = array();
		
		// get the current from side tem

		//Run the template formatter if its there if not run the default formatter
		$tPath = JPATH_ROOT.'/templates/'.$this->_getFrontSideTemplate().'/html/mod_roknavmenu/parameters.php';
		if (file_exists($tPath)) {
			
			// get all the params for the module
			$all_params = $this->_parent;		
			require_once ($tPath);
			$template_params = new RokNavMenuTemplateParams();
			$html .= $template_params->getTemplateParams($name, $control_name, $all_params);
		}
		
		if (strlen($html) == 0) {
			$html = JText::_("
ROKNAVMENU_MSG_NO_TEMPLATE_CONFIG");
		}
		return $html;
	}
	
	function _getFrontSideTemplate() {
		$db =JFactory::getDBO();
		// Get the current default template
        $query = ' SELECT template '
                .' FROM #__template_styles '
                .' WHERE client_id = 0 '
                .' AND home = 1 ';
		$db->setQuery($query);
		$defaultemplate = $db->loadResult();
		return $defaultemplate;
	}
	
}

