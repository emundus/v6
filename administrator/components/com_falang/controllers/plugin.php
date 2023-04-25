<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * The Falang Tasker manages the general tasks within the Falang admin interface
 *
 */
class PluginController extends JControllerLegacy  {

	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask( 'show',  'display' );
	}

	/**
	 * Standard display control structure
	 * 
	 */
    function display($cachable = false, $urlparams = array())
	{
		// test if any plugins are installed - if not divert to installation screen
		$db = JFactory::getDBO();
		$query = 'SELECT COUNT(*)'
			. ' FROM #__extensions AS p'
			. ' WHERE p.folder = '.$db->Quote("falang")
//sbou
                        . ' AND type = "plugin"'
//fin sbou
			;
		$db->setQuery( $query );
		$total = $db->loadResult();
		if ($total>0){
			$link = 'index.php?option=com_plugins&filter_type=falang';
			$msg = "";
		}
		else {
			$link = 'index.php?option=com_installer';
			$msg = JText::_("No FaLang plugins installed yet");
		}
		$this->setRedirect($link, $msg);
	}
	
}
