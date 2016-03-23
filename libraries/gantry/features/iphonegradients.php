<?php
/**
 * @version   $Id: iphonegradients.php 2381 2012-08-15 04:14:26Z btowles $
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
class GantryFeatureIphoneGradients extends GantryFeature
{
	var $_feature_name = 'iphonegradients';

	function isEnabled()
	{
		/** @var $gantry Gantry */
		global $gantry;

		if (!$gantry->browser) return false;

		$prefix     = $gantry->get('template_prefix');
		$cookiename = $prefix . $gantry->browser->platform . '-switcher';

		$cookie = $gantry->retrieveTemp('platform', $cookiename);
		if ($cookie != '' && !$cookie || !$gantry->get($gantry->browser->platform . '-enabled')) return false; else return true;
	}

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry;

		$filtered = array_filter($gantry->_param_names, array($this, '_filtering'));
		$css      = "";
		foreach ($filtered as $filter) {
			$prefix   = str_replace('-from', '', $filter);
			$position = str_replace('iphone-', '', $filter);
			$position = str_replace('-gradient-from', '', $position);

			$type     = $gantry->get($prefix . '-gradient', 'linear');
			$dirStart = str_replace("-", " ", $gantry->get($prefix . '-direction_start'));
			$dirEnd   = str_replace("-", " ", $gantry->get($prefix . '-direction_end'));
			$from     = $gantry->get($prefix . '-from');
			$to       = $gantry->get($prefix . '-to');
			$opacity  = array(
				'from' => (float)$gantry->get($prefix . '-fromopacity'),
				'to'   => (float)$gantry->get($prefix . '-toopacity')
			);
			$css .= "#rt-" . $position . " .rt-container, #rt-" . $position . " .rt-container {background: -webkit-gradient(" . $type . ", " . $dirStart . ", " . $dirEnd . ", from(rgba(" . $this->_hex2rgb($from) . ", " . $opacity['from'] . ")), to(rgba(" . $this->_hex2rgb($to) . ", " . $opacity['to'] . "))) !important;}\n";
		}

		$gantry->addInlineStyle($css);

	}

	function _filtering($key)
	{
		return (stripos($key, '-gradient-from') !== false && stripos($key, 'iphone-') !== false && !stripos($key, 'opacity') === true);

	}

	function _hex2rgb($color)
	{
		$color = str_replace('#', '', $color);
		if (strlen($color) == 3) $color = str_repeat($color, 2);
		if (strlen($color) != 6) {
			return "0, 0, 0";
		}

		$rgb = array();
		for ($x = 0; $x < 3; $x++) {
			$rgb[$x] = hexdec(substr($color, (2 * $x), 2));
		}
		return implode(", ", $rgb);
	}

}