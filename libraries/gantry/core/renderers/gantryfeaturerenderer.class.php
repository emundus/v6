<?php
/**
 * @version   $Id: gantryfeaturerenderer.class.php 2383 2012-08-15 05:03:39Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  core.renderers
 */
class GantryFeatureRenderer
{
	/**
	 * @static
	 *
	 * @param        $feature_name
	 * @param string $layout
	 */
	public static function display($feature_name, $layout = 'basic')
	{
		/** @var $gantry Gantry */
		global $gantry;
		$feature          = $gantry->getFeature($feature_name);
		$rendered_feature = "";
		if (method_exists($feature, 'isEnabled') && $feature->isEnabled() && method_exists($feature, 'render')) {
			$rendered_feature = $feature->render();
		}
		$contents = $rendered_feature . "\n";
		$output   = $gantry->renderLayout('feature_' . $layout, array('contents' => $contents));
		return $output;
	}
}