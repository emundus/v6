<?php
/**
 * @version   $Id: alias.php 6491 2013-01-15 02:25:56Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.params.gantryparamprocessor');

/**
 *
 */
class GantryParamProcessorAlias extends GantryParamProcessor
{

	/**
	 * @param $gantry
	 * @param $param_name
	 * @param $param_element
	 * @param $data
	 */
	public function postLoad(&$gantry, $param_name, &$param_element, &$data)
	{
		if ($data[$param_name]['type'] == 'alias' && $param_name != $data[$param_name]['value']) {
			$gantry->_aliases[$param_name] = $data[$param_name]['value'];
		}
	}
}
