<?php
/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
/**
 * Plugin element to render currency
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @since       3.0
 */
class PlgFabrik_ElementCurrency extends PlgFabrik_Element
{

    protected array $allCurrency;
    protected array $selectedCurrencies = [];
    protected int $idSelectedCurrency = 0;


	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      Elements data
	 * @param   stdClass  &$thisRow  All the data in the lists current row
	 * @param   array     $opts      Rendering options
	 *
	 * @return  string	formatted value
	 */
	public function renderListData($data, stdClass &$thisRow, $opts = array())
	{
		return parent::renderListData($data, $thisRow, $opts);
	}

    public function preRenderElement($data, $repeatCounter = 0)
    {
        $groupModel = $this->getGroupModel();

        if (!$this->canView() && !$this->canUse())
        {
            return '';
        }
        // Used for working out if the element should behave as if it was in a new form (joined grouped) even when editing a record
        $this->inRepeatGroup        = $groupModel->canRepeat();
        $this->_inJoin              = $groupModel->isJoin();
        $opts                       = array('runplugins' => 1);
        $formatedInputValueBack     = $this->getValue($data, $repeatCounter, $opts);

        if ($this->isEditable())
        {
            return $this->render($data, $repeatCounter);
        }
        else
        {
            $htmlId = $this->getHTMLId($repeatCounter);
            return '<div class="fabrikElementReadOnly" id="' . $htmlId . '">' . $formatedInputValueBack. '</div>';
        }
    }

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To pre-populate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */
	public function render($data, $repeatCounter = 0)
	{

        $this->allCurrency          = $this->getDataCurrency();
        $formatedInputValueBack     = $this->getValue($data, $repeatCounter);
        $this->selectedCurrencies   = $this->getSelectedCurrencies();
        $valuesForSelect            = []; // formated value for the select to show
        $inputValue                 = ''; // back value for the inputValue in front

        foreach ($this->selectedCurrencies as $selectedCurrencyOne)
        {
            foreach ($this->allCurrency as $allCurrencyOne)
            {
                if ($allCurrencyOne->iso3 === $selectedCurrencyOne->iso3)
                {
                    $valuesForSelect[$allCurrencyOne->iso3] = $allCurrencyOne->symbol . ' (' . $allCurrencyOne->iso3 . ')';
                }
            }
        }

        if (is_array($formatedInputValueBack))
        {
            $inputValue = $formatedInputValueBack['rowInputValueFront'];
            $this->idSelectedCurrency = $this->getIdCurrencyFromIso3($formatedInputValueBack['selectedIso3Front']);
        }
        else
        {
            $inputValue = $this->getNumbersInputValueBack($formatedInputValueBack);
            $this->idSelectedCurrency = $this->getIdCurrencyFromIso3($this->getIso3FromFormatedInput($formatedInputValueBack));
        }

		$bits = $this->inputProperties($repeatCounter);
        $bits['valuesForSelect'] = $valuesForSelect;
        $bits['iso3SelectedCurrency'] = $this->selectedCurrencies[$this->idSelectedCurrency]->iso3; // to set options selected
        $bits['inputValue'] = $inputValue;

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->attributes = $bits;

		return $layout->render($layoutData);
	}

    private function getIso3FromFormatedInput($input)
    {
        return substr($input,-4 , -1);
    }

    private function getIdCurrencyFromIso3($iso3)
    {
        $id = 0;
        foreach ($this->selectedCurrencies as $key => $currency)
        {
            if ($currency->iso3 === $iso3)
            {
                $id = $key;
            }
        }
        return $id;
    }

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joined groups we need to know what part of the array to access
	 * @param   array  $opts           Options, 'raw' = 1/0 use raw value
	 *
	 * @return  string	value
	 */
	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
        return parent::getValue($data, $repeatCounter, $opts);
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */
	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

        $opts->allCurrency = $this->allCurrency;
        $opts->selectedCurrencies = $this->selectedCurrencies;
        $opts->idSelectedCurrency = $this->idSelectedCurrency;

		return array('FbCurrency', $id, $opts);
	}

	/**
	 * Manipulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   This elements posted form data
	 * @param   array  $data  Posted form data
	 *
	 * @return  mixed
	 */
	public function storeDatabaseFormat($val, $data)
	{
        if (strlen($val['rowInputValueFront']) === 0) // if not value then no value in DB
        {
            return $val['rowInputValueFront'];
        }

        $iso3 = $val['selectedIso3Front'];
        $number = floatval($val['rowInputValueFront']);
        $currencyObject = $this->getCurrencyObject($this->getDataCurrency(), $iso3);

        $decimal_separator = $this->selectedCurrencies[$this->idSelectedCurrency]->decimal_separator;

        $thousands_separator = $this->selectedCurrencies[$this->idSelectedCurrency]->thousand_separator;

        $decimalNumber = $this->selectedCurrencies[$this->idSelectedCurrency]->decimal_numbers;

        // $regex = $this->getParams()->get('regex'); // pas sur de réussir

        $numberFormated = number_format($number, $decimalNumber, $decimal_separator, $thousands_separator);
        $currencyFormated = $currencyObject->symbol. ' ('. $iso3. ')';

		return $numberFormated . ' ' . $currencyFormated;
	}

    public function getDataCurrency()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('name, iso3, symbol')
            ->from($db->quoteName('data_currency'));
        $db->setQuery($query);

        $db->execute();
        return $db->loadObjectList();
    }

    private function getSelectedCurrencies()
    {
        $listCurrency = [];
        foreach ($this->getParams()->get('all_currencies_options') as $element)
        {
            $listCurrency[] = $element;

        }
        return $listCurrency;
    }

    private function getCurrencyObject($listCurrency, $iso3)
    {
        $currencyObject = null;

        foreach ($listCurrency as $key => $value) {
            if ($value->iso3 === $iso3)
            {
                $currencyObject = $value;
            }
        }
        return $currencyObject;
    }

    /**
     * Internal element validation
     *
     * @param   array $data          Form data
     * @param   int   $repeatCounter Repeat group counter
     *
     * @return  bool
     */
    public function validate($data, $repeatCounter = 0)
    {
        $valid = true;
        $this->selectedCurrencies = $this->getSelectedCurrencies();
        $selectedIso3Front = $data['selectedIso3Front'];
        $rowInputValueFront = $data['rowInputValueFront'];

        if (strlen($rowInputValueFront) === 0) // empty data so no value in DB
        {
            $this->validationError = JText::_('PLG_ELEMENT_CURRENCY_NOT_IN_INTERVALS');
            $valid = !$this->validator->hasValidations(); // valid if no validation, not valid if validation
        }
        else
        {
            $valid = $this->currencyFormatValidation($selectedIso3Front);
        }

        if ($valid)
        {
            $this->idSelectedCurrency = $this->getIdCurrencyFromIso3($selectedIso3Front); // valid so we can get his id
            if ($this->validator->hasValidations()) // element mandatory
            {
                $valid = $this->isValueCorrect(floatval($rowInputValueFront));
            }
            else // element not mandatory
            {
                $valid = strlen($rowInputValueFront) !== 0 ? $this->isValueCorrect(intval($rowInputValueFront)) : $valid; // test if not empty
            }
        }
        return $valid;
    }

    private function currencyFormatValidation($selectedIso3Front)
    {
        $this->validationError = JText::_('PLG_ELEMENT_CURRENCY_CURRENCY_ERROR');
        $valid = false;

        foreach ($this->selectedCurrencies as $currency)
        {
            if ($currency->iso3 === $selectedIso3Front)
            {
                $valid = true;
            }
        }

        return $valid;
    }

    private function isValueCorrect($rowInputValueFront)
    {
        $valid = true;

        if (!preg_match('/\d+$/', $rowInputValueFront))
        {
            $this->validationError = JText::_('PLG_ELEMENT_CURRENCY_ONLY_NUMBER');
            $valid = false;
        }
        else
        {
            if ($rowInputValueFront < $this->selectedCurrencies[$this->idSelectedCurrency]->minimal_value
                ||
                $rowInputValueFront > $this->selectedCurrencies[$this->idSelectedCurrency]->maximal_value)
            {
                $this->validationError = JText::_('PLG_ELEMENT_CURRENCY_NOT_IN_INTERVALS');
                $valid = false;
            }
        }

        return $valid;
    }

    private function getNumbersInputValueBack($formatedInputValueBack)
    {
        $string = $formatedInputValueBack;

        for ($i = 0; $i!= 2; $i++)
        {
            $to = strrpos($formatedInputValueBack, ' ');
            $string = substr($formatedInputValueBack, 0, $to);
        }

        return $string;
    }
}
