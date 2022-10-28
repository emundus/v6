<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a message form field
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldMessage extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'Message';

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return string The field input markup.
	 */
	public function getInput($bootstrapHelper = null)
	{
		$controlGroupAttributes = 'id="field_' . $this->name . '" ';

		if ($this->hideOnDisplay)
		{
			$controlGroupAttributes .= ' style="display:none;" ';
		}

		$data = [
			'controlGroupAttributes' => $controlGroupAttributes,
			'description'            => $this->description,
			'row'                    => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/message.php', $data);
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 * @param   string                       $controlId
	 *
	 *
	 * @return  string  A string containing the html for the control goup
	 */
	public function getControlGroup($bootstrapHelper = null, $controlId = null)
	{
		return $this->getInput($bootstrapHelper);
	}

	/**
	 * Get output used for displaying on email and the detail page
	 *
	 * @see RADFormField::getOutput()
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
		if ($tableLess)
		{
			return $this->getInput($bootstrapHelper);
		}

		return '<tr>' . '<td class="eb-message" colspan="2">' . $this->description . '</td></tr>';
	}
}
