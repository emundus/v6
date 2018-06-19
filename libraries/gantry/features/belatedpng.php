<?php
/**
 * @version   $Id: belatedpng.php 30564 2017-04-26 07:39:28Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Reaction Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureBelatedPNG extends GantryFeature
{
	var $_feature_name = 'belatedPNG';

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
			$fixes = isset($gantry->belatedPNG) ? $gantry->belatedPNG : null;

			$gantry->addScript('belated-png.js');
			$gantry->addInlineScript($this->_belatedPNG($fixes));
		}
	}

	function _belatedPNG($fixes)
	{
		if (!is_array($fixes) || count($fixes) == 0) $fixes = array('.png');
		$fixes = implode("', '", $fixes);

		$js = "
			window.addEvent('domready', function() {
				var pngClasses = ['$fixes'];
				pngClasses.each(function(fixMePlease) {
					DD_belatedPNG.fix(fixMePlease);
				});
			});
		";

		return $js;
	}
}