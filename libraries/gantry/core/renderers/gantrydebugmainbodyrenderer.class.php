<?php
/**
 * @version   $Id: gantrydebugmainbodyrenderer.class.php 6491 2013-01-15 02:25:56Z btowles $
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
class GantryDebugMainBodyRenderer
{

	/**
	 * wrapper for mainbody display in debug mode
	 * @param string $bodyLayout
	 * @param string $sidebarLayout
	 * @param string $sidebarChrome
	 * @param null   $grid
	 *
	 * @return string
	 */
	public static function display($bodyLayout = 'debugmainbody', $sidebarLayout = 'sidebar', $sidebarChrome = 'standard', $grid = null)
	{
		/** @global $gantry Gantry */
		global $gantry;

		if ($grid == null) {
			$grid = GRID_SYSTEM;
		}

		$columnIndex = 1;
		$counter     = 1;
		$output      = '';
		$sampleText  = "<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>";

		foreach ($gantry->mainbodySchemasCombos[$grid] as $schemas) {
			$columnCount = $columnIndex++;
			foreach ($schemas as $schema) {
				$classKey = $gantry->getKey($schema);
				$pushPull = $gantry->pushPullSchemas[$classKey];

				$sidebars      = '';
				$contentTop    = null;
				$contentBottom = null;

				$index = 1;

				foreach ($schema as $shortname => $cols) {

					//only process for sidebars
					if ($shortname == "mb") continue;
					$position = $gantry->getLongName($shortname);
					$contents = '<div class="rt-block"><h3>' . $position . '</h3>' . $sampleText . '</div>';
					$sidebars .= $gantry->renderLayout('mod_' . $sidebarLayout, array(
					                                                                 'contents' => $contents,
					                                                                 'position' => $position,
					                                                                 'gridCount'=> $schema[$shortname],
					                                                                 'pushPull' => $pushPull[$index++]
					                                                            ));
				}

				$contents = '<h3>Mainbody</h3>' . $sampleText;
				$output .= $gantry->renderLayout('body_' . $bodyLayout, array(
				                                                             'counter' => $counter++,
				                                                             'schema'  => $schema,
				                                                             'pushPull'=> $pushPull,
				                                                             'classKey'=> $classKey,
				                                                             'contents'=> $contents,
				                                                             'sidebars'=> $sidebars
				                                                        ));
			}
		}
		return $output;
	}
}