<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a text input.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldText extends RADFormField
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Text';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   EventbookingTableField  $row    the table object store form field definitions
	 * @param   string                  $value  the initial value of the form field
	 */
	public function __construct($row, $value = null)
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

		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @var EventbookingHelperBootstrap $bootstrapHelper
	 *
	 */
	public function getInput($bootstrapHelper = null)
	{
		// Add uk-input if UIKit3 is used
		if ($bootstrapHelper && $bootstrapHelper->getBootstrapVersion() === 'uikit3')
		{
			if ($this->type == 'Range')
			{
				$class = 'uk-range';
			}
			else
			{
				$class = 'uk-input';
			}

			$this->addClass($class);
		}

		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-control'))
		{
			$this->addClass('form-control');
		}

		$data = [
			'type'       => strtolower($this->type),
			'name'       => $this->name,
			'value'      => $this->value,
			'attributes' => $this->buildAttributes(),
			'row'        => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/text.php', $data);
	}
}
