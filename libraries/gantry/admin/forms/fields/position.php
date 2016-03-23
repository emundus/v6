<?php
/**
 * @version   $Id: position.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die;

require_once(dirname(__FILE__) . '/selectbox.php');

/**
 * Supports an HTML select list of menu
 *
 * @package        Joomla.Framework
 * @subpackage     Form
 * @since          1.6
 */
class GantryFormFieldPosition extends GantryFormFieldSelectBox
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'position';
	protected $basetype = 'select';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	protected function getOptions()
	{

		// Merge any additional options in the XML definition.
		/** @var $gantry Gantry */
		global $gantry;
		$options = parent::getOptions();

		$unique = $this->getBool('unique', false);

		if ($unique) $positions = $gantry->getUniquePositions(); else $positions = $gantry->getPositions();

		$hide_mobile = $this->getBool('hide_mobile', false);

		$options = array();
		foreach ($positions as $position) {
			$positionInfo = $gantry->getPositionInfo($position);
			if ($hide_mobile && $positionInfo->mobile) {
				continue;
			}

			$val       = $position;
			$text      = $position;
			$tmp       = GantryHtmlSelect::option($val, $text, 'value', 'text', false);
			$options[] = $tmp;
		}
		return $options;
	}
}