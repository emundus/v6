<?php

/**
 * Form Field class for the Joomla RAD.
 * Supports a text input.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */
class RADFormFieldRange extends RADFormFieldText
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Range';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   EventbookingTableField  $row    the table object store form field definitions
	 * @param   mixed                   $value  the initial value of the form field
	 */
	public function __construct($row, $value = null)
	{
		parent::__construct($row, $value);

		if (is_numeric($row->min))
		{
			$this->attributes['min'] = $row->min;
		}

		if (is_numeric($row->max))
		{
			$this->attributes['max'] = $row->max;
		}

		if (is_numeric($row->step))
		{
			$this->attributes['step'] = $row->step;
		}
	}
}
