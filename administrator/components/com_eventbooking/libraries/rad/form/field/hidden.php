<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a hidden input.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

use Joomla\CMS\Factory;

class RADFormFieldHidden extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'Hidden';

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return string The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$user = Factory::getUser();
		$view = Factory::getApplication()->input->getCmd('view');

		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking') && $view == 'registrant')
		{
			$attributes = $this->buildAttributes();

			return '<input type="text" name="' . $this->name . '" id="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
				'"' . $attributes . $this->row->extra_attributes . ' />';
		}
		else
		{
			return '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" class="eb-hidden-field" />';
		}
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
		$user = Factory::getUser();
		$view = Factory::getApplication()->input->getCmd('view');

		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking') && $view == 'registrant')
		{
			return parent::getControlGroup($bootstrapHelper, $controlId);
		}
		else
		{
			return $this->getInput($bootstrapHelper = null);
		}
	}

	/**
	 * Get output used for displaying on email and the detail page
	 *
	 * @see RADFormField::getOutput()
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
		return '';
	}
}
