<?php
/**
 * @version   $Id: fusionmenu.php 2381 2012-08-15 04:14:26Z btowles $
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
class GantryFeatureFusionMenu extends GantryFeature
{
	var $_feature_name = 'fusionmenu';
	var $_feature_prefix = 'menu';
	var $_menu_selector = 'menu-type';

	function isEnabled()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$menu_enabled  = $gantry->get('menu-enabled');
		$selected_menu = $gantry->get($this->_menu_selector);

		$cookie = 0;
		if ($gantry->browser->platform == 'iphone' || $gantry->browser->platform == 'android') {
			$prefix     = $gantry->get('template_prefix');
			$cookiename = $prefix . $gantry->browser->platform . '-switcher';
			$cookie     = $gantry->retrieveTemp('platform', $cookiename);
		}

		if (1 == (int)$menu_enabled && $selected_menu == $this->_feature_name && $cookie == 0) return true;
		return false;
	}

	function isOrderable()
	{
		return false;
	}


	function render($position)
	{
		/** @var $gantry Gantry */
		global $gantry;

		$renderer = $gantry->document->loadRenderer('module');
		$options  = array('style' => "raw");
		$module   = JModuleHelper::getModule('mod_roknavmenu', '_z_empty');

		$params = $gantry->getParams($this->_feature_prefix . "-" . $this->_feature_name, true);
		$reg    = new JRegistry();
		foreach ($params as $param_name => $param_value) {
			$reg->set($param_name, $param_value['value']);
		}
		$module->params = $reg->toString();
		$rendered_menu  = $renderer->render($module, $options);
		return $rendered_menu;
	}
}
