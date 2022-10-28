<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a checkbox list custom field.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class RADFormFieldCheckboxes extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'Checkboxes';

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return string The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$options = (array) $this->getOptions();

		if (is_array($this->value))
		{
			$selectedOptions = $this->value;
		}
		elseif (strpos($this->value, "\r\n"))
		{
			$selectedOptions = explode("\r\n", $this->value);
		}
		elseif (is_string($this->value) && is_array(json_decode($this->value)))
		{
			$selectedOptions = json_decode($this->value);
		}
		else
		{
			$selectedOptions = [$this->value];
		}

		/* Add form-check-input for bootstrap 4*/
		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-check-input'))
		{
			$this->addClass('form-check-input');
		}

		$data = [
			'name'            => $this->name,
			'options'         => $options,
			'selectedOptions' => $selectedOptions,
			'attributes'      => $this->buildAttributes(),
			'bootstrapHelper' => $bootstrapHelper,
			'row'             => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/checkboxes.php', $data);
	}

	/**
	 * Get checkboxes options
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$user = Factory::getUser();

		if (is_array($this->row->values))
		{
			$values = $this->row->values;
		}
		elseif (strpos($this->row->values, "\r\n") !== false)
		{
			$values = explode("\r\n", $this->row->values);
		}
		else
		{
			$values = [$this->row->values];
		}

		$quantityValues = explode("\r\n", $this->row->quantity_values);

		if ($this->row->quantity_field && count($values) && count($quantityValues) && $this->eventId && !$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$optionQuantities = [];

			for ($i = 0, $n = count($values); $i < $n; $i++)
			{
				if (isset($quantityValues[$i]))
				{
					$optionQuantity                      = $quantityValues[$i];
					$optionQuantities[trim($values[$i])] = $optionQuantity;
				}
				else
				{
					$optionQuantities[trim($values[$i])] = 0;
				}
			}

			$quantityValues = $optionQuantities;

			$values = EventbookingHelper::callOverridableHelperMethod('html', 'getAvailableQuantityOptions', [&$values, &$quantityValues, $this->eventId, $this->row->id, true]);
		}

		$config  = EventbookingHelper::getConfig();
		$options = [];

		foreach ($values as $value)
		{
			$optionValue = trim($value);

			if (!$config->show_available_number_for_each_quantity_option || empty($quantityValues[$optionValue]) || $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
			{
				$optionText = $optionValue;
			}
			else
			{
				$optionText = $optionValue . ' ' . Text::sprintf('EB_QUANTITY_OPTION_AVAILABLE', $quantityValues[$optionValue]);
			}

			$options[$optionValue] = $optionText;
		}

		return $options;
	}
}
