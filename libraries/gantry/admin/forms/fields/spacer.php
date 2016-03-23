<?php
/**
 * @version   $Id: spacer.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformfield');


class GantryFormFieldSpacer extends GantryFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'spacer';
	protected $basetype = 'none';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	public function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return    string    The field label markup.
	 * @since    1.6
	 */
	public function getLabel()
	{
		echo '<div class="clr"></div>';
		if ((string)$this->element['hr'] == 'true') {
			return '<hr />';
		} else {
			return parent::getLabel();
		}
		echo '<div class="clr"></div>';
	}

}