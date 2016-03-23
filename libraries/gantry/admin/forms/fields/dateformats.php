<?php
/**
 * @version   $Id: dateformats.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();

/**
 * @package     gantry
 * @subpackage  admin.elements
 */

gantry_import('core.utilities.gantrydate');

require_once(dirname(__FILE__) . '/selectbox.php');

class GantryFormFieldDateFormats extends GantryFormFieldSelectBox
{
	var $_name = 'DateFormats';

	protected $type = 'dateformats';
	protected $basetype = 'select';

	protected function getOptions()
	{
		$now = new GantryDate();

		// Initialize variables.
		$options     = array();
		$translation = $this->element['translation'] ? $this->element['translation'] : true;

		foreach ($this->element->children() as $option) {

			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}


			// Create a new option object based on the <option /> element.
			$tmp = GantryHtmlSelect::option((string)$option['value'], (string)$now->toFormat($option['value']), 'value', 'text', ((string)$option['disabled'] == 'true'));

			// Set some option attributes.
			$tmp->class = (string)$option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string)$option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}
		reset($options);

		return $options;
	}
}

?>