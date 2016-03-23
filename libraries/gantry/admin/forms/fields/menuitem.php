<?php
/**
 * @version   $Id: menuitem.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('GANTRY_VERSION') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

require_once(dirname(__FILE__) . '/selectbox.php');

/**
 * Supports an HTML select list of menu item
 *
 * @package        Joomla.Framework
 * @subpackage     Form
 * @since          1.6
 */
class GantryFormFieldMenuItem extends GantryFormFieldSelectBox
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'menuitem';
	protected $basetype = 'select';

	/**
	 * Method to get the field option groups.
	 *
	 * @return    array    The field option objects as a nested array in groups.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$menuType  = (string)$this->element['menu_type'];
		$published = $this->element['published'] ? explode(',', (string)$this->element['published']) : array();
		$disable   = $this->element['disable'] ? explode(',', (string)$this->element['disable']) : array();

		// Get the menu items.
		$items = MenusHelper::getMenuLinks($menuType, 0, 0, $published);

		// Build group for a specific menu type.
		if ($menuType) {
			// Build the options array.
			foreach ($items as $link) {
				$options[$menuType][] = GantryHtmlSelect::option($link->value, $link->text, 'value', 'text', in_array($link->type, $disable));
			}
		} // Build groups for all menu types.
		else {
			// Build the groups arrays.
			foreach ($items as $menu) {
				// Initialize the group.
				$options[] = GantryHtmlSelect::option($menu->menutype, $menu->title, 'value', 'text', true);
				// Build the options array.
				foreach ($menu->links as $link) {
					$options[] = GantryHtmlSelect::option($link->value, $link->text, 'value', 'text', in_array($link->type, $disable));
				}
			}
		}

		// Merge any additional groups in the XML definition.
		//$groups = array_merge(parent::getGroups(), $groups);

		return $options;
	}
}