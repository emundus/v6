<?php
/**
 * @version   $Id: gantryurlparamoverride.class.php 7297 2013-02-06 17:32:03Z btowles $
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
class GantryUrlParamOverride extends GantryParamOverride
{
	public static function populate()
	{
		/** @global $gantry Gantry */
		global $gantry;
		// get any url param overrides and set to that
		// set preset values
		foreach ($gantry->_preset_names as $param_name) {
			if (in_array($param_name, $gantry->_setbyurl) && array_key_exists($param_name, $_GET)) {
				$param             =& $gantry->_working_params[$param_name];
				$url_value         = htmlentities(JFactory::getApplication()->input->get->getString($param['name'], ''));
				$url_preset_params = $gantry->getPresetParams($param['name'], $url_value);
				foreach ($url_preset_params as $url_preset_param_name => $url_preset_param_value) {
					if (!is_null($url_preset_param_value)) {
						$gantry->_working_params[$url_preset_param_name]['value'] = $url_preset_param_value;
						$gantry->_working_params[$url_preset_param_name]['setby'] = 'url';
					}
				}
			}
		}
		// set individual values
		foreach ($gantry->_param_names as $param_name) {
			if (in_array($param_name, $gantry->_setbyurl) && array_key_exists($param_name, $_GET)) {
				$param     =& $gantry->_working_params[$param_name];
				$url_value = htmlentities(JFactory::getApplication()->input->get->getString($param['name'], ''));
				if (!empty($url_value)) {
					$gantry->_working_params[$param['name']]['value'] = $url_value;
					$gantry->_working_params[$param['name']]['setby'] = 'url';
				}
			}
		}
	}
}