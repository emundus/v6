<?php
/**
 * @version        $Id: gantryformfield.class.php 6491 2013-01-15 02:25:56Z btowles $
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * derived from Joomla with original copyright and license
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformitem');

abstract class GantryFormField extends GantryFormItem
{
	/**
	 * The description text for the form field.  Usually used in tooltips.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $description;


	/**
	 * The multiple state for the form field.  If true then multiple values are allowed for the
	 * field.  Most often used for list field types.
	 *
	 * @var        boolean
	 * @since    1.6
	 */
	protected $multiple = false;


	/**
	 * The required state for the form field.  If true then there must be a value for the field to
	 * be considered valid.
	 *
	 * @var        boolean
	 * @since    1.6
	 */
	protected $required = false;


	/**
	 * The validation method for the form field.  This value will determine which method is used
	 * to validate the value for a field.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $validate;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param    object    $element      The JXMLElement object representing the <field /> tag for the
	 *                                   form field object.
	 * @param    mixed     $value        The form field default value for display.
	 * @param    string    $group        The field name group control value. This acts as as an array
	 *                                   container for the field. For example if the field has name="foo"
	 *                                   and the group value is set to "bar" then the full field name
	 *                                   would end up being "bar[foo]".
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function setup(& $element, $value, $group = null)
	{
		/** @global $gantry Gantry */
		global $gantry;

		// Make sure there is a valid JFormField XML element.
		if (!($element instanceof GantrySimpleXMLElement) || (string)$element->getName() != 'field') {
			return false;
		}

		if (!parent::setup($element, $value, $group)) return false;

		$multiple = (string)$element['multiple'];

		// Set the multiple values option.
		$this->multiple = ($multiple == 'true' || $multiple == 'multiple');

		// Allow for field classes to force the multiple values option.
		if (isset($this->forceMultiple)) {
			$this->multiple = (bool)$this->forceMultiple;
		}

		return true;
	}

	protected function getBool($attribute, $default = true, &$xmlelement = null)
	{
		if (null == $xmlelement) $xmlelement =& $this->element;
		$value = $default;
		if ($xmlelement[$attribute]) {
			if (strtolower(trim((string)$xmlelement[$attribute])) == 'true') $value = true;
			if (strtolower(trim((string)$xmlelement[$attribute])) == 'false') $value = false;
		}
		return $value;
	}
}
