<?php
/**
 * @version   $Id: selectivizr.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');
/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureSelectivizr extends GantryFeature
{
	var $_feature_name = 'selectivizr';

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

		if ($gantry->browser->name == 'ie' && $gantry->browser->shortversion <= '8') {
			if ($this->get('enabled')) {
				$gantry->addScript('selectivizr-min.js');
			}
		}
	}

}