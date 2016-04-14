<?php
/**
 * @version   $Id: gantrymodulesrenderer.class.php 30226 2016-03-29 12:24:07Z matias $
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
class GantryModulesRenderer
{
	// wrapper for modules display
	public static function display($positionStub, $layout = 'standard', $chrome = 'standard', $gridsize = GRID_SYSTEM, $pattern = null)
	{
		/** @var $gantry Gantry */
		global $gantry;

		$document = JFactory::getDocument();
		if (!$document instanceof JDocumentHtml) {
			return '';
		}

		if (array_key_exists($positionStub, $gantry->_aliases)) {
			return GantryModulesRenderer::display($gantry->_aliases[$positionStub], $layout, $chrome, $gridsize, $pattern);
		}

		$output           = '';
		$index            = 0;
		$poscount         = 1;
		$positions        = $gantry->getPositions($positionStub, $pattern);
		$position_renders = array();

		$count = $gantry->countModules($positionStub, $pattern);

		$showAllParam = $gantry->get($positionStub . '-showall');
		$showMaxParam = $gantry->get($positionStub . '-showmax');

		if ($showAllParam == 1) $count = $showMaxParam;

		//first loop through for information
		$actualPositions = array();

		foreach ($positions as $position) {
			if ($showAllParam == 1 and $poscount++ > $showMaxParam) break;

			$contents = '';
			$aliasKey = array_search($position, $gantry->_aliases);
			$features = $gantry->getFeaturesForPosition($position);

			if ($aliasKey) {
				$alias = $gantry->getFeaturesForPosition($aliasKey);
				if (count($alias)) {
					foreach ($alias as $a) {
						array_push($features, $a);
					}
				}
			}

			$modules = JModuleHelper::getModules($position);

			if (!empty($features) && count($features) > 0) {
				foreach ($features as $feature_name) {
					$feature          = $gantry->getFeature($feature_name);
					$rendered_feature = $feature->render($position);
					if (!empty($rendered_feature)) {
						$contents .= $rendered_feature . "\n";
					}
				}
			}

			if (!empty($modules) && count($modules) > 0) {
				$shortname = $gantry->getShortName($position);
				$contents .= '<jdoc:include type="modules" name="' . $position . '" style="' . $chrome . '" />' . "\n";
			}

			$position_renders[$position] = $contents;
		}

		$position_renders = array_filter($position_renders, create_function('$o', 'return !empty($o);'));


		$position_renders_keys = array_keys($position_renders);
		$end   = end($position_renders_keys);
		$start = reset($position_renders_keys);

		$prefixCount = 0;

		// If RTL then flip the array
		if ($gantry->document->direction == 'rtl' && $gantry->get('rtl-enabled')) {
			$positions = array_reverse($positions);

			if ($showAllParam == 1 && count($positions) > $showMaxParam) {
				$new_positions = array();
				for ($i = $showMaxParam; $i > 0; $i--) {
					$poped = array_pop($positions);
					array_unshift($new_positions, $poped);
				}
				$positions =& $new_positions;
			}
		}


		foreach ($positions as $position) {
			$contents = "";
			if (array_key_exists($position, $position_renders)) {
				$contents = $position_renders[$position];
			}

			$extraClass = '';
			if ($position == $start) $extraClass = " rt-alpha";
			if ($position == $end) $extraClass = " rt-omega";
			if ($position == $start && $position == $end) $extraClass = " rt-alpha rt-omega";


			if ($showAllParam == 1 && empty($contents)) {
				$prefixCount += $gantry->getPositionSchema($position, $gridsize, $count, $index);
				$index++;
			} else if (!empty($contents)) {
				// Apply chrome and render module
				$paramSchema = $gantry->getPositionSchema($position, $gridsize, $count, $index);
				if ($paramSchema) $output .= $gantry->renderLayout('mod_' . $layout, array('contents'  => $contents,
				                                                                          'gridCount'  => $paramSchema,
				                                                                          'prefixCount'=> $prefixCount,
				                                                                          'extraClass' => $extraClass
				                                                                     ));
				$prefixCount = 0; // reset prefix count
				$index++;
			}
		}
		return $output;
	}
}
