<?php
/**
 * @version   $Id: menus.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * Supports an HTML select list of menu
 *
 * @package        Joomla.Framework
 * @subpackage     Form
 * @since          1.6
 */
class GantryFormFieldMenus extends GantryFormFieldSelectBox
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'Menu';
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

		$menus = JHtml::_('menu.menus');

		foreach ($menus as $menu) {
			// Create a new option object based on the <option /> element.
			$tmp       = GantryHtmlSelect::option($menu->value, $menu->text, 'value', 'text', false);
			$options[] = $tmp;
		}
		return $options;
	}
}