<?php
/**
 * Abstract Form Field class for the RAD framework
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

use Joomla\CMS\Factory;

abstract class RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type;

	/**
	 * The name (and id) for the form field.
	 *
	 * @var    string
	 */
	protected $name;

	/**
	 * Title of the form field
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Description of the form field
	 * @var string
	 */
	protected $description;

	/**
	 * The current value of the form field.
	 *
	 * @var    mixed
	 */
	protected $value;

	/**
	 * The object store form field definition
	 *
	 * @var EventbookingTableField
	 */
	protected $row;

	/**
	 * The html attributes of the field
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The label for the form field.
	 *
	 * @var    string
	 */
	protected $label;

	/**
	 * The input for the form field.
	 *
	 * @var    string
	 */
	protected $input;

	/**
	 * This field is used in fee calculation or not
	 *
	 * @var bool
	 */
	protected $feeCalculation;

	/**
	 * This field will be hided on first display or not
	 *
	 * @var bool
	 */
	protected $hideOnDisplay = false;

	/**
	 * This field is a master field or not
	 *
	 * @var bool
	 */
	protected $isMasterField = false;

	/**
	 * Id of the event this custom field belong to
	 *
	 * @var null
	 */
	protected $eventId = null;

	/**
	 * Field suffix
	 *
	 * @var string
	 */
	protected $suffix = null;

	/**
	 * Replace data, use to build custom field data if needed
	 *
	 * @var array
	 */
	protected $replaceData = [];

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   EventbookingTableField  $row    the table object store form field definitions
	 * @param   mixed                   $value  the initial value of the form field
	 */
	public function __construct($row, $value = null)
	{
		$this->name        = $row->name;
		$this->title       = $row->title;
		$this->description = $row->description;
		$this->row         = $row;
		$this->value       = $value;
		$cssClasses        = [];

		if ($row->css_class)
		{
			$cssClasses[] = $row->css_class;
		}

		if ($row->validation_rules)
		{
			$cssClasses[] = $row->validation_rules;
		}

		if (count($cssClasses))
		{
			$this->attributes['class'] = implode(' ', $cssClasses);
		}

		if ($row->validation_error_message)
		{
			$this->attributes['data-errormessage'] = str_replace('[FIELD_NAME]', $row->title, $row->validation_error_message);
		}
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'type':
			case 'name':
			case 'title':
			case 'description':
			case 'value':
			case 'row':
			case 'hideOnDisplay':
			case 'isMasterField':
			case 'eventId':
			case 'suffix' :
				return $this->{$name};
				break;
			case 'fee_field':
			case 'fee_formula':
			case 'id':
			case 'depend_on_field_id':
			case 'depend_on_options':
			case 'quantity_field':
			case 'required':
			case 'position':
				return $this->row->{$name};
				break;
			case 'input':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->input))
				{
					$this->input = $this->getInput();
				}

				return $this->input;
				break;
			case 'label':
				// If the label hasn't yet been generated, generate it.
				if (empty($this->label))
				{
					$this->label = $this->getLabel();
				}

				return $this->label;
				break;
		}

		return;
	}

	/**
	 * Simple method to set the value for the form field
	 *
	 * @param   mixed  $value  Value to set
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Set suffix for the form field
	 *
	 * @param   string  $suffix
	 */
	public function setFieldSuffix($suffix)
	{
		$this->suffix = $suffix;
		$this->name   = $this->name . '_' . $suffix;
	}

	/**
	 * Remove the suffix from name of the field
	 */
	public function removeFieldSuffix()
	{
		$pos = strrpos($this->name, '_');

		if ($pos !== false)
		{
			$this->name = substr($this->name, 0, $pos);
		}

		$this->suffix = null;
	}

	/**
	 * Add attribute to the form field
	 *
	 * @param   string  $name
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * Get data of the given attribute
	 *
	 * @param   string  $name
	 *
	 * @return string
	 */
	public function getAttribute($name)
	{
		if (isset($this->attributes[$name]))
		{
			return $this->attributes[$name];
		}

		return '';
	}

	/**
	 * Remove the attribute
	 *
	 * @param $name
	 */
	public function removeAttribute($name)
	{
		if (isset($this->attributes[$name]))
		{
			unset($this->attributes[$name]);
		}
	}

	/**
	 * Mark this field as a fee-affected custom field
	 *
	 * @param   int  $feeCalculation
	 */
	public function setFeeCalculation($feeCalculation)
	{
		$this->feeCalculation = $feeCalculation;
	}

	public function setMasterField($isMasterField)
	{
		$this->isMasterField = $isMasterField;
	}

	/**
	 * Associate this custom field with an event for quantity control
	 *
	 * @param $eventId
	 */
	public function setEventId($eventId)
	{
		$this->eventId = $eventId;
	}

	/**
	 *
	 */
	public function hideOnDisplay()
	{
		$this->hideOnDisplay = true;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	abstract protected function getInput($bootstrapHelper = null);

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 */
	protected function getLabel()
	{
		$data = [
			'name'        => $this->name,
			'title'       => $this->title,
			'description' => $this->description,
			'row'         => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/label.php', $data);
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   EventbookingHelperBootstrap  $bootstrapHelper
	 * @param   string                       $controlId
	 *
	 * @return  string  A string containing the html for the control goup
	 */
	public function getControlGroup($bootstrapHelper = null, $controlId = null)
	{
		$controlGroupAttributes = 'id="field_' . $this->name . '" ';

		if ($this->hideOnDisplay)
		{
			$controlGroupAttributes .= ' style="display:none;" ';
		}

		$classes = [];

		if ($this->feeCalculation)
		{
			$classes[] = 'payment-calculation';
		}

		if ($this->isMasterField)
		{
			if ($this->suffix)
			{
				$classes[] = 'master-field-' . $this->suffix;
			}
			else
			{
				$classes[] = 'master-field';
			}
		}

		$class = implode(' ', $classes);

		if (!empty($class))
		{
			$class = ' ' . $class;
		}

		$data = [
			'name'                   => $this->name,
			'description'            => $this->description,
			'class'                  => $class,
			'controlGroupAttributes' => $controlGroupAttributes,
			'label'                  => $this->getLabel(),
			'input'                  => $this->getInput($bootstrapHelper),
			'bootstrapHelper'        => $bootstrapHelper,
			'row'                    => $this->row,
		];

		return EventbookingHelperHtml::loadCommonLayout('fieldlayout/controlgroup.php', $data);
	}

	/**
	 * Get output of the field using for sending email and display on the registration complete page
	 *
	 * @param   bool                         $tableLess
	 *
	 * @param   EventBookingHelperBootstrap  $bootstrapHelper
	 *
	 * @return string
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
		$fieldValue = $this->getDisplayValue();

		if ($tableLess)
		{
			$controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
			$controlLabelClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
			$controlsClass     = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';
			$fieldValue        = $fieldValue ?: '&nbsp;';

			return '<div class="' . $controlGroupClass . ' eb-field-value">' . '<div class="' . $controlLabelClass . '">' . $this->title . '</div>' . '<div class="' . $controlsClass . '">' .
				$fieldValue . '</div>' . '</div>';
		}

		return '<tr><td class="title_cell">' . $this->title . '</td><td class="field_cell">' . $fieldValue . "</td></tr>\r\n";
	}

	/**
	 * Method to add new class to the field
	 *
	 * @param   string  $class
	 *
	 * @return void
	 */
	public function addClass($class)
	{
		$classes = $this->getAttribute('class');
		$this->setAttribute('class', $classes ? $classes . ' ' . $class : $class);
	}

	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @return string
	 */
	public function buildAttributes()
	{
		$html = [];

		foreach ((array) $this->attributes as $key => $value)
		{
			if (is_bool($value))
			{
				$html[] = " $key ";
			}
			else
			{

				$html[] = $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8', false) . '"';
			}
		}

		if ($this->row->extra_attributes)
		{
			$html[] = $this->row->extra_attributes;
		}

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}

	/**
	 * Make current file optional
	 */
	public function makeFieldOptional()
	{
		$cssClass = $this->getAttribute('class');

		if (strpos($cssClass, 'validate[required,') !== false)
		{
			$cssClass = str_replace('validate[required,', 'validate[', $cssClass);
		}

		if (strpos($cssClass, 'validate[required') !== false)
		{
			$cssClass = str_replace('validate[required', 'validate[', $cssClass);
		}

		if (strpos($cssClass, ' validate[]') !== false)
		{
			$cssClass = str_replace(' validate[]', '', $cssClass);
		}

		if (strpos($cssClass, 'validate[]') !== false)
		{
			$cssClass = str_replace('validate[]', '', $cssClass);
		}

		if ($cssClass)
		{
			$this->setAttribute('class', $cssClass);
		}
		else
		{
			$this->removeAttribute('class');
		}

		$this->row->required = 0;
	}

	/**
	 * Get optional validation rules from a required validation rules
	 *
	 * @param   string  $validationRules
	 *
	 * @return string mixed
	 */
	public static function getOptionalValudationRules($validationRules)
	{
		if (strpos($validationRules, 'validate[required,') !== false)
		{
			$validationRules = str_replace('validate[required,', 'validate[', $validationRules);
		}

		if (strpos($validationRules, 'validate[required') !== false)
		{
			$validationRules = str_replace('validate[required', 'validate[', $validationRules);
		}

		if (strpos($validationRules, 'validate[]') !== false)
		{
			$validationRules = str_replace('validate[]', '', $validationRules);
		}

		return $validationRules;
	}

	/**
	 * Get the list options from all languages for radio, list and checkboxes field
	 *
	 * @param $fieldId
	 *
	 * @return array
	 */
	public static function getMultilingualOptions($fieldId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_fields')
			->where('id = ' . $fieldId);
		$db->setQuery($query);
		$row = $db->loadObject();

		$languages          = EventbookingHelper::getLanguages();
		$multilingualValues = [];
		$languageValues     = [];

		foreach ($languages as $language)
		{
			$sef                  = $language->sef;
			$languageValues[$sef] = explode("\r\n", $row->{'values_' . $sef});
		}

		$defaultValues = explode("\r\n", $row->values);

		for ($i = 0, $n = count($defaultValues); $i < $n; $i++)
		{
			$multilingualValues[$i]   = [];
			$multilingualValues[$i][] = $defaultValues[$i];

			foreach ($languages as $language)
			{
				$sef = $language->sef;

				if (isset($languageValues[$sef][$i]))
				{
					$multilingualValues[$i][] = $languageValues[$sef][$i];
				}
			}
		}

		return $multilingualValues;
	}

	/**
	 * Set replace data for field
	 *
	 * @param   array  $replaceData
	 */
	public function setReplaceData($replaceData)
	{
		$this->replaceData = $replaceData;
	}

	/**
	 * Get display value for custom field
	 *
	 * @return string
	 */
	public function getDisplayValue()
	{
		if (is_string($this->value) && is_array(json_decode($this->value)))
		{
			$fieldValue = implode(', ', json_decode($this->value));
		}
		else
		{
			$fieldValue = $this->value;
		}

		return $fieldValue;
	}
}
