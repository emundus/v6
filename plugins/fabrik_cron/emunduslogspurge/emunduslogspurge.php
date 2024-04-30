<?php
/**
 * A cron task to purge logs older than a certain time
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to purge logs older than a certain time
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emunduslogspurge
 * @since       3.0
 */

class PlgFabrik_Cronemunduslogspurge extends PlgFabrik_Cron{

    /**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
     *
     * @since 6.9.3
	 */
	public function canUse($location = null, $event = null){
		return true;
	}


    /**
     * Do the plugin action
     *
     * @param   array  &$data data
     *
     * @return  int  number of records updated
     *
     * @since 6.9.3
     * @throws Exception
     */
	public function process(&$data, &$listModel) {
		return true;
	}
}
