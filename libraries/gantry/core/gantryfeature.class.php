<?php
/**
 * @version   $Id: gantryfeature.class.php 2387 2012-08-15 05:36:16Z btowles $
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
class GantryFeature
{
	var $_feature_name = '';

	var $_feature_prefix = '';
	var $_enabled = null;
	var $_position = null;

	function isEnabled()
	{
		if (!isset($this->_enabled)) {
			$this->_enabled = (int)$this->get('enabled') == 1;
		}

		return $this->_enabled;
	}

	function getPosition()
	{
		if (!isset($this->_position)) {
			$this->_position = $this->get('position');
		}

		return $this->_position;
	}

	function isInPosition($position)
	{
		if ($this->getPosition() == $position) return true;
		return false;
	}

	function isOrderable()
	{
		return true;
	}

	function setPrefix($prefix)
	{
		$this->_feature_prefix = $prefix;
	}

	function get($param, $prefixed = true)
	{
		/** @var $gantry Gantry */
		global $gantry;

		$gantry_param = '';
		$gantry_param .= ($prefixed && !empty($this->_feature_prefix)) ? $this->_feature_prefix . '-' : '';
		$gantry_param .= $this->_feature_name . '-' . $param;
		$value = $gantry->get($gantry_param);
		return $value;
	}

	function init()
	{

	}

	function render($position)
	{

	}

	function finalize()
	{

	}


}