<?php
/**
 * @version   $Id: gantryjformfieldaccessor.class.php 30069 2016-03-08 17:45:33Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');


class GantryJFormFieldAccessor extends JFormField
{

	public function __construct(JFormField &$field)
	{
		$vars = get_object_vars($field);
		foreach ($vars as $var_name => $var_value) {
			$this->{$var_name} = $var_value;
		}
	}

	protected function getInput()
	{
		return '';
	}

	public function addClass($new_class)
	{
		$class = (string)$this->element['class'];
		if ($class) {
			$this->element['class'] = $class . ' ' . $new_class;
		} else {
			@$this->element->addAttribute('class', $new_class);
		}
	}

	public function removeClass($class)
	{
		$set_classes = (string)$this->element['class'];
		if ($set_classes) {
			$all_classes = explode(' ', $set_classes);

			if (($loc = array_search($class, $all_classes)) !== false) {
				unset($all_classes[$loc]);
				$reset_classes = implode(' ', $all_classes);
			}

			if ($set_classes) {
				$this->element['class'] = $reset_classes;
			}
		}
	}

	public function getClasses()
	{
		$ret     = array();
		$classes = (string)$this->element['class'];
		if ($classes) {
			$ret = explode(' ', $classes);
		}
		return $ret;
	}

	public function setElement(object $element)
	{
		$this->element = $element;
	}

	public function getElement()
	{
		return $this->element;
	}

	public function getType()
	{
		return (string)$this->element['type'];
	}
}