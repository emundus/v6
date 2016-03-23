<?php
/**
 * @version   $Id: ie6menu.php 2381 2012-08-15 04:14:26Z btowles $
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
class GantryFeatureIE6Menu extends GantryFeature
{
	var $_feature_name = 'ie6menu';

	function isEnabled()
	{
		return true;
	}

	function isInPosition($position)
	{
		return false;
	}

	function isOrderable()
	{
		return false;
	}

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry;
		if ($gantry->browser->name == 'ie' && $gantry->browser->shortversion == '6') {
			$gantry->set('rtl-enabled', false); //disable problematic RTL for ie6
			$selected_menu = $gantry->get('menu-type');

			if ($selected_menu == 'fusionmenu' || $selected_menu == 'suckerfishmenu') {
				$position = $gantry->get('menu-type-fusionmenu-position');
				$gantry->set('menu-type', 'suckerfishmenu');
				$gantry->set('menu-type-suckerfishmenu-position', $position);
			}
		}
	}
}