<?php
/**
 * @version   $Id: touchmenu.php 12549 2013-08-09 16:52:04Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('GANTRY_VERSION') or die();
gantry_import('core.gantryfeature');


/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureTouchMenu extends GantryFeature
{
	var $_feature_name = 'touchmenu';

	function isEnabled()
	{
		/** @var $gantry Gantry */
		global $gantry;
		if (!isset($gantry->browser)) return false;
		if ($gantry->browser->platform != 'iphone' && $gantry->browser->platform != 'ipad' && $gantry->browser->platform != 'android') return false;

		$menu_enabled = $gantry->get('touchmenu-enabled');

		$prefix     = $gantry->get('template_prefix');
		$cookiename = $prefix . $gantry->browser->platform . '-switcher';
		$cookie     = $gantry->retrieveTemp('platform', $cookiename);


		if (1 == (int)$menu_enabled && $cookie == 1 && $gantry->get($gantry->browser->platform . '-enabled')) return true;
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
		$selected_menu = $gantry->get('menu-type');

		if ($gantry->get('iphone-enabled') && $gantry->browser->platform == 'iphone' || $gantry->browser->platform == 'ipad' || $gantry->browser->platform == 'android') {
			$position = $gantry->get('touchmenu-position', 'mobile-navigation');
			//$gantry->set('menu-type', 'touchmenu');
			$gantry->set('touchmenu-position', $position);
			$gantry->set('touchmenu-theme', 'touch');
			$gantry->addInlineScript("var animation = '" . $gantry->get('touchmenu-animation', 'cube') . "';");
			$gantry->addScript('imenu.js');
		}
	}

	function isInPosition($position)
	{
		if ($this->getPosition() == $position) return true;
		return false;
	}


	function render($position)
	{


		/** @var $gantry Gantry */
		global $gantry;

		JHTML::_('behavior.framework', true);

		if ($gantry->browser->platform != 'iphone' && $gantry->browser->platform != 'ipad' && $gantry->browser->platform != 'android') return false;
		gantry_import('facets.menu.gantrymenu');

		$params        = $gantry->getParams($this->_feature_name, true);
		$module_params = '';
		foreach ($params as $param_name => $param_value) {
			$module_params .= $param_name . "=" . $param_value['value'] . "\n";
		}
		$passing_params = new GantryRegistry();
		$passing_params->loadString($module_params, 'INI');
        $gantrymenu = new GantryMenu($passing_params);

		return $gantrymenu->render($passing_params);

	}
}