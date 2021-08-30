<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a Date custom field.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class RADFormFieldDate extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Date';

	/**
	 * Method to get the field input markup.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$config       = EventbookingHelper::getConfig();
		$dateFormat   = $config->get('date_field_format') ?: '%Y-%m-%d';
		$iconCalendar = $bootstrapHelper ? $bootstrapHelper->getClassMapping('icon-calendar') : 'icon-calendar';

		try
		{
			return str_replace('icon-calendar', $iconCalendar, HTMLHelper::_('calendar', $this->value, $this->name, $this->name, $dateFormat, $this->attributes));
		}
		catch (Exception $e)
		{
			return str_replace('icon-calendar', $iconCalendar, HTMLHelper::_('calendar', '', $this->name, $this->name, $dateFormat, $this->attributes)) . ' Value <strong>' . $this->value . '</strong> is invalid. Please correct it with format YYYY-MM-DD';
		}
	}

	/**
	 * Allow set default value to Now
	 *
	 * @param   mixed  $value
	 */
	public function setValue($value)
	{
		if (strtolower($value) === 'now')
		{
			$date  = Factory::getDate('Now', Factory::getApplication()->get('offset'));
			$value = $date->toSql(true);

		}

		parent::setValue($value);
	}


	/**
	 * Get display value for custom field
	 *
	 * @return string
	 */
	public function getDisplayValue()
	{
		try
		{
			$config     = EventbookingHelper::getConfig();
			$dateFormat = $config->date_field_format ? $config->date_field_format : '%Y-%m-%d';
			$dateFormat = str_replace('%', '', $dateFormat);

			$date       = Factory::getDate($this->value);
			$fieldValue = $date->format($dateFormat);
		}
		catch (Exception $e)
		{
			$fieldValue = $this->value;
		}

		return $fieldValue;
	}
}
