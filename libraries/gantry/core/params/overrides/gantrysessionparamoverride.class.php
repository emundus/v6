<?php
/**
 * @version   $Id: gantrysessionparamoverride.class.php 6306 2013-01-05 05:39:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();


gantry_import('core.params.gantryparamoverride');

/**
 * @package    gantry
 * @subpackage core.params
 */
class GantrySessionParamOverride extends GantryParamOverride
{
	public static function store()
	{
		/** @global $gantry Gantry */
		global $gantry;
		foreach ($gantry->_setinsession as $session_var) {
			if ($gantry->_working_params[$session_var]['setby'] != 'menuitem') {
				if ($gantry->_working_params[$session_var]['value'] != $gantry->_working_params[$session_var]['sitebase'] && $gantry->_working_params[$session_var]['type'] != 'preset') {
					$gantry->session->set($gantry->template_prefix . $gantry->_base_params_checksum . "-" . $session_var, $gantry->_working_params[$session_var]['value']);
				} else {
					$gantry->session->set($gantry->template_prefix . $gantry->_base_params_checksum . "-" . $session_var, null);
				}
			}
		}
	}

	public static function clean()
	{
		/** @global $gantry Gantry */
		global $gantry;
		foreach ($gantry->_setinsession as $session_var) {
			$gantry->session->set($gantry->template_prefix . $gantry->_base_params_checksum . "-" . $session_var, null);
		}
	}

	public static function populate()
	{
		/** @global $gantry Gantry */
		global $gantry;

		// get any session param overrides and set to that
		// set preset values
		foreach ($gantry->_preset_names as $param_name) {
			$session_param_name = $gantry->template_prefix . $gantry->_base_params_checksum . "-" . $param_name;
			if (in_array($param_name, $gantry->_setbysession) && $gantry->session->get($session_param_name)) {
				$param                 =& $gantry->_working_params[$param_name];
				$session_value         = $gantry->session->get($session_param_name);
				$session_preset_params = $gantry->presets[$param_name][$session_value];
				foreach ($session_preset_params as $session_preset_param_name => $session_preset_param_value) {
					if (array_key_exists($session_preset_param_name, $gantry->_working_params) && !is_null($session_preset_param_value)) {
						$gantry->_working_params[$session_preset_param_name]['value'] = $session_preset_param_value;
						$gantry->_working_params[$session_preset_param_name]['setby'] = 'session';
					}
				}
			}
		}
		// set individual values
		foreach ($gantry->_param_names as $param_name) {
			$session_param_name = $gantry->template_prefix . $gantry->_base_params_checksum . "-" . $param_name;
			if (in_array($param_name, $gantry->_setbysession) && $gantry->session->get($session_param_name)) {
				$param         =& $gantry->_working_params[$param_name];
				$session_value = $gantry->session->get($session_param_name);
				if (!is_null($session_value)) {
					$gantry->_working_params[$param['name']]['value'] = $session_value;
					$gantry->_working_params[$param['name']]['setby'] = 'session';
				}
			}
		}
	}
}