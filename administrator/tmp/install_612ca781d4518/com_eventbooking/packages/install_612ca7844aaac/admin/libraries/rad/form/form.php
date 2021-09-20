<?php

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Form Class for handling custom fields
 *
 * @package        RAD
 * @subpackage     Form
 */
class RADForm
{
	/**
	 * The array hold list of custom fields
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * @var string
	 */
	protected $fieldSuffix = null;

	/**
	 * Constructor
	 *
	 * @param   array  $fields
	 */
	public function __construct($fields, $config = [])
	{
		HTMLHelper::_('script', 'jui/html5.js', ['version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9']);
		HTMLHelper::_('script', 'system/html5fallback.js', ['version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9']);

		foreach ($fields as $field)
		{
			$class         = 'RADFormField' . ucfirst($field->fieldtype);
			$overrideClass = 'RADFormFieldOverride' . ucfirst($field->fieldtype);

			if (class_exists($overrideClass))
			{
				$class = $overrideClass;
			}

			if (class_exists($class))
			{
				$this->fields[$field->name] = new $class($field, $field->default_values);
			}
			else
			{
				throw new RuntimeException('The field type ' . $field->fieldType . ' is not supported');
			}
		}
	}

	/**
	 * Get fields of form
	 *
	 * @return RADFormField[]
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Set the form fields
	 *
	 * @param   RADFormField[]  $fields
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;
	}

	/**
	 * Get the field object from name
	 *
	 * @param   string  $name
	 *
	 * @return RADFormField
	 */
	public function getField($name)
	{
		return $this->fields[$name];
	}

	/**
	 * Bind data into form fields
	 *
	 * @param   array  $data
	 * @param   bool   $useDefault
	 *
	 * @return $this
	 */
	public function bind($data, $useDefault = false)
	{
		foreach ($this->fields as $field)
		{
			if ($field->type == 'State')
			{
				$fieldName = $field->name;
				$prefix    = str_replace('state', '', $fieldName);

				if (!empty($data['country' . $prefix]))
				{
					$field->country = $data['country' . $prefix];
				}
			}

			if (isset($data[$field->name]))
			{
				$field->setValue($data[$field->name]);
			}
			else
			{
				if ($useDefault || ($field->type == 'Message'))
				{
					if ($field->type == 'Checkboxes' || ($field->type == 'List' && $field->row->multiple))
					{
						$field->setValue(explode("\r\n", $field->row->default_values));
					}
					else
					{
						$field->setValue($field->row->default_values);
					}
				}
				else
				{
					$field->setValue(null);
				}
			}
		}

		return $this;
	}

	/**
	 * Set replace data for fields on form
	 *
	 * @param   array  $replaceData
	 */
	public function setReplaceData($replaceData)
	{
		foreach ($this->fields as $field)
		{
			$field->setReplaceData($replaceData);
		}
	}

	/**
	 * Get the data of all fields on the form
	 *
	 * @return array
	 */
	public function getFormData()
	{
		$data = [];

		foreach ($this->fields as $field)
		{
			$data[$field->name] = $field->value;
		}

		return $data;
	}

	/**
	 * Add event handle to the custom fee field
	 *
	 * @param   string  $calculationFeeMethod
	 */
	public function prepareFormFields($calculationFeeMethod)
	{
		$feeFormula = '';

		foreach ($this->fields as $field)
		{
			if ($field->fee_formula)
			{
				$feeFormula .= $field->fee_formula;
			}
		}

		foreach ($this->fields as $field)
		{
			if ($field->fee_field || strpos($feeFormula, '[' . strtoupper($field->name) . ']') !== false)
			{
				$field->setFeeCalculation(true);

				switch ($field->type)
				{
					case 'List':
					case 'Text':
					case 'Number':
					case 'Countries':
						$field->setAttribute('onchange', $calculationFeeMethod);
						break;
					case 'Checkboxes':
					case 'Radio':
						$field->setAttribute('onclick', $calculationFeeMethod);
						break;
				}
			}
		}
	}

	/**
	 * Build the custom field dependency
	 */
	public function buildFieldsDependency()
	{
		$masterFields = [];
		$fieldsAssoc  = [];

		foreach ($this->fields as $field)
		{
			if ($field->depend_on_field_id)
			{
				$masterFields[] = $field->depend_on_field_id;
			}

			$fieldsAssoc[$field->id] = $field;
		}

		$masterFields = array_unique($masterFields);

		if (count($masterFields))
		{
			$hiddenFields = [];


			foreach ($this->fields as $field)
			{
				if (in_array($field->id, $masterFields))
				{
					$field->setFeeCalculation(true);
					$field->setMasterField(true);

					switch (strtolower($field->type))
					{
						case 'list':
							$field->setAttribute('onchange', "showHideDependFields($field->id, '$field->name', '$field->type', '$this->fieldSuffix');");
							break;
						case 'radio':
						case 'checkboxes':
							$field->setAttribute('onclick', "showHideDependFields($field->id, '$field->name', '$field->type' , '$this->fieldSuffix');");
							break;
					}
				}

				if ($field->depend_on_field_id && isset($fieldsAssoc[$field->depend_on_field_id]))
				{
					// If master field is hided, then children field will be hided, too
					if (in_array($field->depend_on_field_id, $hiddenFields))
					{
						$field->hideOnDisplay();
						$hiddenFields[] = $field->id;
					}
					else
					{
						$masterFieldValues = $fieldsAssoc[$field->depend_on_field_id]->value;

						if (is_array($masterFieldValues))
						{
							$selectedOptions = $masterFieldValues;
						}
						elseif (strpos($masterFieldValues, "\r\n"))
						{
							$selectedOptions = explode("\r\n", $masterFieldValues);
						}
						elseif (is_string($masterFieldValues) && is_array(json_decode($masterFieldValues)))
						{
							$selectedOptions = json_decode($masterFieldValues);
						}
						else
						{
							$selectedOptions = [$masterFieldValues];
						}

						$dependOnOptions = json_decode($field->depend_on_options);

						if (!count(array_intersect($selectedOptions, $dependOnOptions)))
						{
							$field->hideOnDisplay();
							$hiddenFields[] = $field->id;
						}
					}
				}
			}
		}
	}

	/**
	 * Check if the form contains fee fields or not
	 *
	 * @return boolean
	 */
	public function containFeeFields()
	{
		$containFeeFields = false;

		foreach ($this->fields as $field)
		{
			if ($field->fee_field)
			{
				$containFeeFields = true;
				break;
			}
		}

		return $containFeeFields;
	}

	/**
	 * Set Event ID for form fields, using for quantity control
	 *
	 * @param $eventId
	 */
	public function setEventId($eventId)
	{
		foreach ($this->fields as $field)
		{
			$field->setEventId($eventId);
		}
	}

	/**
	 * Calculate total fee generated by all fields on the form
	 *
	 * @param   array  $replaces
	 *
	 * @return float total fee
	 */
	public function calculateFee(&$replaces = [])
	{
		if (!isset($replaces['NUMBER_REGISTRANTS']))
		{
			$replaces['NUMBER_REGISTRANTS'] = 1;
		}

		if (!isset($replaces['INDIVIDUAL_PRICE']))
		{
			$replaces['INDIVIDUAL_PRICE'] = 1;
		}

		$fee                 = 0;
		$noneDiscountableFee = 0;
		$noneTaxableFee      = 0;

		$this->buildFieldsDependency();
		$fieldsFee = $this->calculateFieldsFee();

		foreach ($this->fields as $field)
		{
			if ($field->hideOnDisplay)
			{
				continue;
			}

			if (!$field->row->fee_field)
			{
				continue;
			}

			if (!$field->row->fee_formula && in_array(strtolower($field->type), ['text', 'number']))
			{
				$field->row->fee_formula = '[FIELD_VALUE]';
			}

			if ($field->row->fee_formula)
			{

				$formula = $field->row->fee_formula;
				$formula = str_replace('[FIELD_VALUE]', floatval($field->value), $formula);

				foreach ($fieldsFee as $fieldName => $fieldFee)
				{
					$fieldName = strtoupper($fieldName);
					$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
				}

				foreach ($replaces as $fieldName => $fieldFee)
				{
					$fieldName = strtoupper($fieldName);
					$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
				}

				$feeValue = 0;

				if ($formula)
				{
					@eval('$feeValue = ' . $formula . ';');

					if (!$field->row->discountable)
					{
						$noneDiscountableFee += $feeValue;
					}

					if (!$field->row->taxable)
					{
						$noneTaxableFee += $feeValue;
					}

					$fee += $feeValue;

					//Use the code below if eval is disabled on server
					//$fee += self::calculateFormula($formula);
				}
			}
			else
			{
				$feeValues = explode("\r\n", $field->row->fee_values);
				$values    = explode("\r\n", $field->row->values);

				if (is_array($field->value))
				{
					$fieldValues = $field->value;
				}
				elseif ($field->value)
				{
					$fieldValues   = [];
					$fieldValues[] = $field->value;
				}
				else
				{
					$fieldValues = [];
				}

				$values      = array_map('trim', $values);
				$fieldValues = array_map('trim', $fieldValues);

				$feeValue = 0;

				for ($j = 0, $m = count($fieldValues); $j < $m; $j++)
				{
					$fieldValue      = $fieldValues[$j];
					$fieldValueIndex = array_search($fieldValue, $values);

					if ($fieldValueIndex !== false && isset($feeValues[$fieldValueIndex]))
					{
						$fieldValueFee = $feeValues[$fieldValueIndex];

						if (strpos($fieldValueFee, '[') !== false)
						{
							$formula = $fieldValueFee;

							foreach ($fieldsFee as $fieldName => $fieldFee)
							{
								$fieldName = strtoupper($fieldName);
								$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
							}

							foreach ($replaces as $fieldName => $fieldFee)
							{
								$fieldName = strtoupper($fieldName);
								$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
							}

							@eval('$fieldValueFee = ' . $formula . ';');

							$feeValue += $fieldValueFee;
						}
						else
						{
							$feeValue += $fieldValueFee;
						}
					}
				}

				$fee += $feeValue;

				if (!$field->row->discountable)
				{
					$noneDiscountableFee += $feeValue;
				}

				if (!$field->row->taxable)
				{
					$noneTaxableFee += $feeValue;
				}
			}
		}

		$replaces['none_discountable_fee'] = $noneDiscountableFee;
		$replaces['none_taxable_fee']      = $noneTaxableFee;

		return $fee;
	}

	/**
	 * Set validator language
	 *
	 * @param   string  $lang
	 */
	public function setValidatorLanguage($lang)
	{
		$lang = strtolower($lang);

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/valitron/lang/' . $lang . '.php'))
		{
			$this->lang = $lang;

			return;
		}

		$parts = explode('-', $lang);

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/valitron/lang/' . $parts[0] . '.php'))
		{
			$this->lang = $parts[0];

			return;
		}


		// Use default language if the passed language does not exist
		$this->lang = 'en';
	}

	/**
	 * Validate form data
	 *
	 * @param   string  $prefix
	 *
	 * @return array
	 */
	public function validate($prefix = '')
	{
		$errors          = [];
		$validationRules = [];
		$labels          = [];
		$data            = [];
		$fields          = $this->getFields();

		foreach ($fields as $fieldName => $field)
		{
			if ($fieldName != $field->name)
			{
				$fields[$field->name] = $field;
				unset($fields[$fieldName]);
			}
		}

		reset($fields);

		/* @var RADFormField $field */
		foreach ($fields as $field)
		{
			if ($field->hideOnDisplay)
			{
				continue;
			}

			// Ignore State, Heading, Message validation since these field types don't need to have data
			$fieldType = strtolower($field->type);

			if (in_array($fieldType, ['state', 'heading', 'message']))
			{
				continue;
			}

			$data[$field->name]   = $field->value;
			$labels[$field->name] = $field->title;

			$fieldRules = [];

			// Required rule
			if ($field->required)
			{
				$fieldRules[] = 'required';
			}

			// Custom rules
			if ($field->row->server_validation_rules)
			{
				$rules = explode('|', $field->row->server_validation_rules);

				foreach ($rules as $rule)
				{
					$parts    = explode(':', $rule);
					$ruleName = $parts[0];

					if (count($parts) > 1)
					{
						$params = explode(',', $parts[1]);
						$params = array_map('trim', $params);

						// The
						if (in_array($ruleName, ['in', 'notIn']))
						{
							$fieldRules[] = [$ruleName, $params];
						}
						else
						{
							$fieldRules[] = array_merge([$ruleName], $params);
						}
					}
					else
					{
						$fieldRules[] = $ruleName;
					}
				}
			}

			if (count($fieldRules))
			{
				$validationRules[$field->name] = $fieldRules;
			}
		}


		// Load custom validators if exist
		if (file_exists(JPATH_ROOT . '/components/com_eventbooking/helper/validator.php'))
		{
			require_once JPATH_ROOT . '/components/com_eventbooking/helper/validator.php';
		}

		// Create validator object
		$v = new Valitron\Validator($data);
		$v->mapFieldsRules($validationRules);
		$v->labels($labels);

		// Set validation language
		if (empty($this->lang))
		{
			$this->setValidatorLanguage(Factory::getLanguage()->getTag());
		}

		$v::lang($this->lang);

		// Perform validation and return error message
		if (!$v->validate())
		{
			foreach ($v->errors() as $fieldName => $errorMessages)
			{
				$field = $fields[$fieldName];

				// If the field has a custom error message, use it
				if (!empty($field->row->validation_error_message))
				{
					$errors[$fieldName] = str_ireplace('[FIELD_NAME]', $field->title, $field->row->validation_error_message);
				}
				else
				{
					$errors[$fieldName] = Text::sprintf('EB_FIELD_IS_INVALID', $field->title);
				}
			}
		}

		return $errors;
	}

	/**
	 * Store custom fields data for a registration record
	 *
	 * @param   int    $registrantId
	 * @param   array  $data
	 * @param   bool   $excludeFeeFields
	 *
	 * @return bool
	 */
	public function storeData($registrantId, $data, $excludeFeeFields = false)
	{
		$rowFieldValue = Table::getInstance('EventBooking', 'Fieldvalue');
		$config        = EventbookingHelper::getConfig();
		$dateFormat    = $config->date_field_format ? $config->date_field_format : '%Y-%m-%d';
		$dateFormat    = str_replace('%', '', $dateFormat);
		$fieldIds      = [0];
		$fileFieldIds  = [0];

		foreach ($this->fields as $field)
		{
			$fieldType = strtolower($field->type);

			if ($fieldType == 'file')
			{
				$fileFieldIds[] = $field->id;
			}
			elseif (!$excludeFeeFields || !$field->fee_field)
			{
				$fieldIds[] = $field->id;
			}
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__eb_field_values')->where('registrant_id=' . (int) $registrantId)
			->where('field_id IN (' . implode(',', $fieldIds) . ')');
		$db->setQuery($query);
		$db->execute();

		foreach ($this->fields as $field)
		{
			$fieldType = strtolower($field->type);

			if ($field->row->is_core
				|| $field->hideOnDisplay
				|| $fieldType == 'heading'
				|| $fieldType == 'message')
			{
				continue;
			}

			// Don't update fee field if not needed
			if ($excludeFeeFields && $field->fee_field)
			{
				continue;
			}

			if ($fieldType == 'date')
			{
				$fieldValue = $data[$field->name];

				if ($fieldValue)
				{
					// Try to convert the format
					try
					{
						$date = DateTime::createFromFormat($dateFormat, $fieldValue);

						if ($date)
						{
							$fieldValue = $date->format('Y-m-d');
						}
						else
						{
							$fieldValue = '';
						}
					}
					catch (Exception $e)
					{
						$fieldValue = '';
					}

					$data[$field->name] = $fieldValue;
				}
			}

			$fieldValue = isset($data[$field->name]) ? $data[$field->name] : '';

			if ($fieldValue != '')
			{
				if (in_array($field->id, $fileFieldIds))
				{
					$query->clear()
						->delete('#__eb_field_values')
						->where('registrant_id=' . (int) $registrantId)
						->where('field_id = ' . $field->id);
					$db->setQuery($query);
					$db->execute();
				}

				$rowFieldValue->id            = 0;
				$rowFieldValue->field_id      = $field->row->id;
				$rowFieldValue->registrant_id = $registrantId;

				if (is_array($fieldValue))
				{
					$rowFieldValue->field_value = json_encode($fieldValue);
				}
				else
				{
					$rowFieldValue->field_value = $fieldValue;
				}

				$rowFieldValue->store();
			}
		}

		return true;
	}

	/**
	 * Set the suffix for the form fields which will change the name of it
	 *
	 * @param   string  $suffix
	 */
	public function setFieldSuffix($suffix)
	{
		$this->fieldSuffix = $suffix;

		foreach ($this->fields as $field)
		{
			$field->setFieldSuffix($suffix);
		}
	}

	/**
	 * Remove the suffix for the form fields which will change the name of it
	 */
	public function removeFieldSuffix()
	{
		foreach ($this->fields as $field)
		{
			$field->removeFieldSuffix();
		}
	}

	/**
	 * Calculate the fee associated with each field to use in fee formula
	 *
	 * @return array
	 */
	private function calculateFieldsFee()
	{
		$fieldsFee     = [];
		$feeFieldTypes = ['text', 'number', 'radio', 'list', 'checkboxes', 'hidden'];

		foreach ($this->fields as $fieldName => $field)
		{
			if ($field->hideOnDisplay)
			{
				$fieldsFee[$fieldName] = 0;
				continue;
			}

			$fieldsFee[$fieldName] = 0;
			$fieldType             = strtolower($field->type);

			if (in_array($fieldType, $feeFieldTypes))
			{
				if (in_array($fieldType, ['text', 'number', 'hidden']))
				{
					$fieldsFee[$fieldName] = floatval($field->value);
				}
				elseif ($fieldType == 'checkboxes' || ($fieldType == 'list' && $field->row->multiple))
				{
					$feeValues = explode("\r\n", $field->row->fee_values);
					$values    = explode("\r\n", $field->row->values);
					$feeAmount = 0;

					if (is_array($field->value))
					{
						$selectedOptions = $field->value;
					}
					elseif (strpos($field->value, "\r\n"))
					{
						$selectedOptions = explode("\r\n", $field->value);
					}
					elseif (is_string($field->value) && is_array(json_decode($field->value)))
					{
						$selectedOptions = json_decode($field->value);
					}
					else
					{
						$selectedOptions = [$field->value];
					}

					if (is_array($selectedOptions))
					{
						foreach ($selectedOptions as $selectedOption)
						{
							$index = array_search($selectedOption, $values);

							if ($index !== false)
							{
								if (isset($feeValues[$index]))
								{
									$feeAmount += floatval($feeValues[$index]);
								}
							}
						}
					}

					$fieldsFee[$fieldName] = $feeAmount;
				}
				else
				{
					$feeValues  = explode("\r\n", $field->row->fee_values);
					$values     = explode("\r\n", $field->row->values);
					$values     = array_map('trim', $values);
					$valueIndex = array_search(trim($field->value), $values);

					if ($valueIndex !== false && isset($feeValues[$valueIndex]))
					{
						$fieldsFee[$fieldName] = floatval($feeValues[$valueIndex]);
					}
				}
			}
		}

		return $fieldsFee;
	}

	/**
	 * Helper function to calculate fee when eval function is disabled by the hosting
	 *
	 * @param   string  $formula
	 *
	 * @return number
	 */
	public static function calculateFormula($formula)
	{
		$formula = trim($formula);     // trim white spaces
		$formula = preg_replace('/[^0-9\+-\*\/\(\) ]/', '', $formula);    // remove any non-numbers chars; exception for math operators
		$compute = create_function("", "return (" . $formula . ");");

		return 0 + $compute();
	}
}
