<?php
/**
 * @version   $Id: gantrylayout.class.php 30069 2016-03-08 17:45:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

/**
 * Base class for all Gantry custom features.
 *
 * @package    gantry
 * @subpackage core
 */
class GantryLayout
{
	protected $render_params = array();

	public function render($params = array())
	{
		/** @var $gantry Gantry */
		global $gantry;
		ob_start();
		return ob_get_clean();
	}

	function _getParams($params = array())
	{
		$ret       = new stdClass();
		$ret_array = array_merge($this->render_params, $params);
		foreach ($ret_array as $param_name => $param_value) {
			$ret->{$param_name} = $param_value;
		}
		return $ret;
	}

	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}
}
