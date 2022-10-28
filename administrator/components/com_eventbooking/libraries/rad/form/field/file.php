<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a file input.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldFile extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'File';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   EventbookingTableField  $row    the table object store form field definitions
	 * @param   mixed                   $value  the initial value of the form field
	 */
	public function __construct($row, $value = null)
	{
		parent::__construct($row, $value);

		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$data = [
			'name'       => $this->name,
			'value'      => $this->value,
			'attributes' => $this->buildAttributes(),
			'row'        => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/file.php', $data);
	}
}
