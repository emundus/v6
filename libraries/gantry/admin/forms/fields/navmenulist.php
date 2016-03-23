<?php
/**
 * @version   $Id: navmenulist.php 6564 2013-01-16 17:13:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformfield');
gantry_import('core.config.gantryhtmlselect');
require_once(dirname(__FILE__) . '/list.php');


class GantryFormFieldNavMenuList extends GantryFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'navmennulist';
	protected $basetype = 'select';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		/** @global $gantry Gantry */
		global $gantry;
		$options = array();
		$options = parent::getOptions();

		$menus = wp_get_nav_menus();

		foreach ($menus as $menu) {
			// Create a new option object based on the <option /> element.
			$tmp       = GantryHtmlSelect::option($menu->slug, $menu->name, 'value', 'text', false);
			$options[] = $tmp;
		}
		return $options;
	}
}