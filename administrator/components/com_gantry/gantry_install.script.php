<?php
/**
 * @version $Id: gantry_install.script.php 3140 2012-09-03 21:37:24Z btowles $
 * @author RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class Com_GantryInstallerScript
{
	/**
	 * @param $type
	 * @param $parent
	 */
	public function postflight($type, $parent)
	{
		$cache = JFactory::getCache();
        $cache->clean('Gantry');
		$cache->clean('GantryAdmin');
	}
}