<?php
/**
 * @version   $Id: integer.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformfield');
gantry_import('core.config.gantryhtmlselect');
require_once('list.php');

require_once(dirname(__FILE__) . '/list.php');


class GantryFormFieldInteger extends GantryFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'integer';
	protected $basetype = 'none';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$first = (int)$this->element['first'];
		$last  = (int)$this->element['last'];
		$step  = (int)$this->element['step'];

		// Sanity checks.
		if ($step == 0) {
			// Step of 0 will create an endless loop.
			return $options;
		} else if ($first < $last && $step < 0) {
			// A negative step will never reach the last number.
			return $options;
		} else if ($first > $last && $step > 0) {
			// A position step will never reach the last number.
			return $options;
		}

		// Build the options array.
		for ($i = $first; $i <= $last; $i += $step) {
			$options[] = GantryHTMLSelect::option($i);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}