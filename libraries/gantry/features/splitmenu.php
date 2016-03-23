<?php
/**
 * @version   $Id: splitmenu.php 2381 2012-08-15 04:14:26Z btowles $
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
class GantryFeatureSplitMenu extends GantryFeature
{
	var $_feature_name = 'splitmenu';
	var $_feature_prefix = 'menu';
	var $_menu_picker = 'menu-type';

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$gantry->addStyle('splitmenu.css');
	}

	function isEnabled()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$menu_enabled  = $gantry->get('menu-enabled');
		$selected_menu = $gantry->get($this->_menu_picker);
		$cookie        = 0;
		if ($gantry->browser->platform == 'iphone' || $gantry->browser->platform == 'android') {
			$prefix     = $gantry->get('template_prefix');
			$cookiename = $prefix . $gantry->browser->platform . '-switcher';
			$cookie     = $gantry->retrieveTemp('platform', $cookiename);
		}
		if (1 == (int)$menu_enabled && $selected_menu == $this->_feature_name && $cookie == 0) return true;
		return false;
	}

	function isInPosition($position)
	{
		if ($this->get('mainmenu-position') == $position || $this->get('submenu-position') == $position) return true;
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
		$output   = '';
		$renderer = $gantry->document->loadRenderer('module');
		$options  = array('style' => "raw");

		$params           = array();
		$group_params     = $gantry->getParams($this->_feature_prefix . "-" . $this->_feature_name, true);
		$group_params_reg = new JRegistry();
		foreach ($group_params as $param_name => $param_value) {
			$group_params_reg->set($param_name, $param_value['value']);
		}

		if ($position == $this->get('mainmenu-position')) {
			$params = $gantry->getParams($this->_feature_prefix . "-" . $this->_feature_name . "-mainmenu", true);
			$module = JModuleHelper::getModule('mod_roknavmenu', '_z_empty');
			$reg    = new JRegistry();
			foreach ($params as $param_name => $param_value) {
				$reg->set($param_name, $param_value['value']);
			}
			$reg->merge($group_params_reg);
			$module->params = $reg->toString();
			$output .= $renderer->render($module, $options);
		}

		if ($position == $this->get('submenu-position')) {
			$params  = $gantry->getParams($this->_feature_prefix . "-" . $this->_feature_name . "-submenu", true);
			$options = array('style' => "submenu");
			$module  = JModuleHelper::getModule('mod_roknavmenu', '_z_empty');
			$reg     = new JRegistry();
			foreach ($params as $param_name => $param_value) {
				$reg->set($param_name, $param_value['value']);
			}
			$reg->merge($group_params_reg);
			$module->params = $reg->toString();
			$output .= $renderer->render($module, $options);
		}
		return $output;
	}
}
