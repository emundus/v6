<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Field;

use FOF30\Form\FieldInterface;
use FOF30\Form\Form;
use FOF30\Model\DataModel;
use FOF30\Tests\Helpers\TravisLogger;
use \JHtml;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('calendar');

/**
 * Form Field class for the FOF framework
 * Supports a calendar / date field.
 */
class Calendar extends \JFormFieldCalendar implements FieldInterface
{
	/**
	 * @var  string  Static field output
	 */
	protected $static;

	/**
	 * @var  string  Repeatable field output
	 */
	protected $repeatable;

	/**
	 * The Form object of the form attached to the form field.
	 *
	 * @var    Form
	 */
	protected $form;

	/**
	 * A monotonically increasing number, denoting the row number in a repeatable view
	 *
	 * @var  int
	 */
	public $rowid;

	/**
	 * The item being rendered in a repeatable form field
	 *
	 * @var  DataModel
	 */
	public $item;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			// ATTENTION: Redirected getInput() to getStatic()
			case 'input':
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->repeatable;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		return $this->getCalendar('static');
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		return $this->getCalendar('repeatable');
	}

	/**
	 * Method to get the calendar input markup.
	 *
	 * @param   string  $display  The display to render ('static' or 'repeatable')
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   2.1.rc4
	 */
	protected function getCalendar($display)
	{
		// Initialize some field attributes.
		$format  = $this->format ? $this->format : '%Y-%m-%d';
		$class   = $this->class ? $this->class : '';
		$default = $this->element['default'] ? (string) $this->element['default'] : '';

		// Get some system objects.
		$config = $this->form->getContainer()->platform->getConfig();
		$user   = $this->form->getContainer()->platform->getUser();

		// Check for empty date values
		if (empty($this->value) || $this->value == $this->form->getContainer()->platform->getDbo()->getNullDate() || $this->value == '0000-00-00')
		{
			$this->value = $default;
		}

		// Handle the special case for "now".
		if (strtoupper($this->value) == 'NOW')
		{
			$this->value = strftime($format);
		}

		// If a known filter is given use it.
		switch (strtoupper($this->filter))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ((int) $this->value)
				{
					// Get a date object based on the correct timezone.
					$date = \JFactory::getDate($this->value, 'UTC');
					$date->setTimezone(new \DateTimeZone($config->get('offset')));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}

				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ((int) $this->value)
				{
					// Get a date object based on the correct timezone.
					$date = \JFactory::getDate($this->value, 'UTC');

					$date->setTimezone(new \DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}

				break;
		}

		if ($display == 'static')
		{
			// Build the attributes array.
			$attributes = array();

			if ($this->size)
			{
				$attributes['size'] = $this->size;
			}

			if ($this->maxlength)
			{
				$attributes['maxlength'] = $this->maxlength;
			}

			if ($this->class)
			{
				$attributes['class'] = $this->class;
			}

			if ($this->readonly)
			{
				$attributes['readonly'] = 'readonly';
			}

			if ($this->disabled)
			{
				$attributes['disabled'] = 'disabled';
			}

			if ($this->onchange)
			{
				$attributes['onchange'] = $this->onchange;
			}

			if ($this->required)
			{
				$attributes['required'] = 'required';
				$attributes['aria-required'] = 'true';
			}

			// Including fallback code for HTML5 non supported browsers.
			JHtml::_('jquery.framework');
			JHtml::_('script', 'system/html5fallback.js', false, true);

			return JHtml::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
		}
		else
		{
			if (!$this->value
				&& (string) $this->element['empty_replacement'])
			{
				$value = $this->element['empty_replacement'];
			}
			else
			{
				$jDate = new \JDate($this->value);
				$value = strftime($format, $jDate->getTimestamp());
			}

			return '<span class="' . $this->id . ' ' . $class . '">' .
			htmlspecialchars($value, ENT_COMPAT, 'UTF-8') .
			'</span>';
		}
	}
}
