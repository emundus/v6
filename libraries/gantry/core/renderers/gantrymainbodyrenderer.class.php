<?php
/**
 * @version   $Id: gantrymainbodyrenderer.class.php 11911 2013-07-03 16:21:51Z rhuk $
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
class GantryMainBodyRenderer
{
	/**
	 * wrapper for mainbody display
	 *
	 * @param string $bodyLayout
	 * @param string $sidebarLayout
	 * @param string $sidebarChrome
	 * @param string $contentTopLayout
	 * @param string $contentTopChrome
	 * @param string $contentBottomLayout
	 * @param string $contentBottomChrome
	 * @param null   $grid
	 * @param string $component_content
	 *
	 * @return string
	 */
	public static function display($bodyLayout = 'mainbody', $sidebarLayout = 'sidebar', $sidebarChrome = 'standard', $contentTopLayout = 'standard', $contentTopChrome = 'standard', $contentBottomLayout = 'standard', $contentBottomChrome = 'standard', $grid = null, $component_content = '')
	{
		/** @var $gantry Gantry */
		global $gantry;

		/** Get the running component **/
		$option = JFactory::getApplication()->input->get('option');
		$editmode         = ($option == 'com_content' && JFactory::getApplication()->input->getCmd('task')) == 'edit' ? true : false;


		$position_renders = array();

		if ($grid == null) {
			$grid = GRID_SYSTEM;
		}

		if (!$editmode) {
			//get current sidebar count based on module usages
			$positions    = $gantry->getPositions('sidebar');
			$sidebarCount = $gantry->countModules('sidebar');

			foreach ($positions as $position) {
				$contents = '';
				$features = $gantry->getFeaturesForPosition($position);
				$modules  = JModuleHelper::getModules($position);

				if (!count($modules) and !count($features)) continue;

				foreach ($features as $feature_name) {
					$feature          = $gantry->getFeature($feature_name);
					$rendered_feature = $feature->render($position);
					if (!empty($rendered_feature)) {
						$contents .= $rendered_feature . "\n";
					}
				}
				$position_renders[$position] = $contents;

				if (!count($modules)) continue;

				$shortname = $gantry->getShortName($position);
				$contents .= '<jdoc:include type="modules" name="' . $position . '" style="' . $sidebarChrome . '" />' . "\n";
				$position_renders[$position] = $contents;
			}

			foreach ($position_renders as $position => $contents) {
				if (empty($contents)) {
					$sidebarCount--;
				}
			}
		} else {
			$sidebarCount = 0;
		}

		$columnCount = $sidebarCount + 1;

		// see if the mainbodySchema exists, if not probably old cached file
		if (!isset($gantry->mainbodySchemas[$grid][$columnCount])) {
			// Clear the cache gantry cache
			$cache = JFactory::getCache('', 'callback', 'file');
			$cache->clean('Gantry');
		}

		//here we would see if the mainbody schema was set to soemthing else
		$defaultSchema = $gantry->mainbodySchemas[$grid][$columnCount];

		$position = @unserialize($gantry->get('mainbodyPosition'));

		if (!$position || !isset($position[$grid]) || !array_key_exists($columnCount, $position[$grid])) $schema = $defaultSchema; else {
			$schema = $position[$grid][$columnCount];
		}

		// If RTL then flip the array
		if ($gantry->document->direction == 'rtl' && $gantry->get('rtl-enabled')) {
			$position_renders = array_reverse($position_renders);
			$schema           = $gantry->flipBodyPosition($schema);
		}


		$classKey = $gantry->getKey($schema);
		$pushPull = $gantry->pushPullSchemas[$classKey];

		$output        = '';
		$sidebars      = '';
		$contentTop    = null;
		$contentBottom = null;

		$index = 1;
		// remove the mainbody and use the schema array for grid sizes
		$sidebarSchema = $schema;
		unset ($sidebarSchema['mb']);

		$layoutSidebar = 'modLayout_' . $sidebarLayout;


		foreach ($position_renders as $position => $contents) {
			if (empty($contents)) continue;
			$sidebars .= $gantry->renderLayout('mod_' . $sidebarLayout, array(
			                                                                 'contents' => $contents,
			                                                                 'position' => $position,
			                                                                 'gridCount'=> current($sidebarSchema),
			                                                                 'pushPull' => $pushPull[$index++]
			                                                            ));
			next($sidebarSchema);
		}


		if ($gantry->countModules('content-top')) {
			$contentTop = $gantry->displayModules('content-top', $contentTopLayout, $contentTopChrome, $schema['mb']);
		}

		if ($gantry->countModules('content-bottom')) {
			$contentBottom = $gantry->displayModules('content-bottom', $contentBottomLayout, $contentBottomChrome, $schema['mb']);
		}

		$output = $gantry->renderLayout('body_' . $bodyLayout, array(
		                                                            'schema'       => $schema,
		                                                            'pushPull'     => $pushPull,
		                                                            'classKey'     => $classKey,
		                                                            'sidebars'     => $sidebars,
		                                                            'contentTop'   => $contentTop,
		                                                            'contentBottom'=> $contentBottom
		                                                       ));
		return $output;

	}
}
