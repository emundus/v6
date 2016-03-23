<?php
/**
 * @version   $Id: inactive.php 2473 2012-08-17 17:16:49Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');


/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureInactive extends GantryFeature
{
	var $_feature_name = 'inactive';

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry, $Itemid;

		$enabled = $this->get('enabled');
		if ($enabled) {
			$app   = JFactory::getApplication();
			$menus = $app->getMenu();
			$menu  = $menus->getActive();
			if (null == $menu) {
				$menuitem = $this->get('menuitem');
				$menus->setActive($menuitem);
				$Itemid = $menuitem;
			}
		}
	}
}