<?php
/**
 * @version        $Id: gantryformgroup.class.php 2325 2012-08-13 17:46:48Z btowles $
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * original copyright
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformitem');

abstract class GantryFormGroup extends GantryFormItem
{
	/**
	 * @var array
	 */
	protected $fields = array();

	protected $prelabel_function = null;

	protected $postlabel_function = null;

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
		// Make sure there is a valid JFormField XML element.
		if (!($element instanceof GantrySimpleXMLElement) || (string)$element->getName() != 'fields') {
			return false;
		}

		if (!parent::setup($element, $value, $group)) return false;

		$this->fields = $this->form->getSubFields($this->element);

		foreach ($this->fields as $field) {
			if ($field->variance) $this->variance = true;
		}
		return true;
	}

	public function setLabelWrapperFunctions($prelabel_function = null, $postlabel_function = null)
	{
		$this->prelabel_function  = $prelabel_function;
		$this->postlabel_function = $postlabel_function;
	}

	protected function preLabel($field)
	{
		if ($this->prelabel_function == null || !function_exists($this->prelabel_function)) return '';
		return call_user_func_array($this->prelabel_function, array($field));
	}

	protected function postLabel($field)
	{
		if ($this->postlabel_function == null) return '';
		return call_user_func_array($this->postlabel_function, array($field));
	}
}
