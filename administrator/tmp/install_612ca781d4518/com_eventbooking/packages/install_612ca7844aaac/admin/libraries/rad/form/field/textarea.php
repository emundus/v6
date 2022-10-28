<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a textarea inut.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldTextarea extends RADFormField
{
	protected $type = 'Textarea';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   EventbookingTableField  $row    the table object store form field definitions
	 * @param   string                  $value  the initial value of the form field
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);

		if ($row->place_holder)
		{
			$this->attributes['placeholder'] = $row->place_holder;
		}

		if ($row->max_length)
		{
			$this->attributes['maxlength'] = $row->max_length;
		}

		if ($row->rows > 0)
		{
			$this->attributes['rows'] = $row->rows;
		}

		if ($row->cols > 0)
		{
			$this->attributes['cols'] = $row->cols;
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput($bootstrapHelper = null)
	{
		// Add uk-textarea if UIKit3 is used
		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('uk-textarea'))
		{
			$this->addClass('uk-textarea');
		}

		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-control'))
		{
			$this->addClass('form-control');
		}

		$data = [
			'name'       => $this->name,
			'value'      => $this->value,
			'attributes' => $this->buildAttributes(),
			'row'        => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/textarea.php', $data);
	}
}
