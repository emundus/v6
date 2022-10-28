<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a heading.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldHeading extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'Heading';

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return string The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$controlGroupAttributes = 'id="field_' . $this->name . '" ';

		if ($this->hideOnDisplay)
		{
			$controlGroupAttributes .= ' style="display:none;" ';
		}

		$data = [
			'controlGroupAttributes' => $controlGroupAttributes,
			'title'                  => $this->title,
			'row'                    => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/heading.php', $data);
	}

	/**
	 * Get control group used to display on form
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 * @param   string                       $controlId
	 *
	 * @return string
	 */
	public function getControlGroup($bootstrapHelper = null, $controlId = null)
	{
		return $this->getInput($bootstrapHelper = null);
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

		return '<tr>' . '<td class="eb-heading" colspan="2">' . $this->title . '</td></tr>';
	}
}
