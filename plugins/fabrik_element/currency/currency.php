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
    protected string $rowInputValueBack;
    protected object $selectedCurrencies;
    protected int $idSelectedCurrency;


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

        for ($i = 0; $i != count($this->selectedCurrencies->iso3); $i++)
        {
            foreach ($this->allCurrency as $allCurrencyOne)
            {
                if ($allCurrencyOne->iso3 === $this->selectedCurrencies->iso3[$i])
                {
                    $valuesForSelect[$allCurrencyOne->iso3] = $allCurrencyOne->symbol . ' (' . $allCurrencyOne->iso3 . ')';
                }
            }
        }

        if (is_array($formatedInputValueBack))
        {
            $this->rowInputValueBack = $formatedInputValueBack['rowInputValueFront'];
            $this->idSelectedCurrency = $this->getIdCurrencyFromIso3($formatedInputValueBack['selectedIso3Front']);
        }
        else
        {
            $this->rowInputValueBack = $this->getNumbersInputValueBack($formatedInputValueBack);
            $this->idSelectedCurrency = $this->getIdCurrencyFromIso3($this->getIso3FromFormatedInput($formatedInputValueBack));
        }

		$bits = $this->inputProperties($repeatCounter);
        $bits['valuesForSelect'] = $valuesForSelect;
        $bits['iso3SelectedCurrency'] = $this->selectedCurrencies->iso3[$this->idSelectedCurrency]; // to set options selected

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
        for ($i = 0; $i!= count($this->selectedCurrencies->iso3); $i++)
        {
            if ($this->selectedCurrencies->iso3[$i] === $iso3)
            {
                $id = $i; // if doublons, we get the last one
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
        $opts->value = $this->rowInputValueBack;
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

        if (strlen($val) === 0) // if not value then no value in DB
        {
            return $val;
        }

        $spacePos = strrpos($val, ' ');
        $iso3 = substr($val, $spacePos+1, strlen($val));
        $number = floatval(substr($val, 0, $spacePos));

        $currencyObject = $this->getCurrencyObject($this->getDataCurrency(), $iso3);

        $decimal_separator = $this->selectedCurrencies->decimal_separator[$this->idSelectedCurrency];
        $thousands_separator = $this->selectedCurrencies->thousand_separator[$this->idSelectedCurrency] ;
        $decimalNumber = $this->selectedCurrencies->decimal_numbers[$this->idSelectedCurrency];
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
        return json_decode($this->getParams()->get('all_currencies_options'));
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
        /*
        $selectedIso3Front = $data['selectedIso3Front'];
        $rowInputValueFront = $data['rowInputValueFront'];
        */

        if (strlen($data) === 0) // empty data so no value in DB
        {
            $valid = !$this->validator->hasValidations(); // valid if no validation, not valid if validation
        }
        else
        {
            // as now its a single result
            $spacePos = strrpos($data, ' ');
            $selectedIso3Front = substr($data, $spacePos+1, strlen($data));
            $rowInputValueFront = substr($data, 0, $spacePos);

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

        for ($i = 0; $i != count($this->selectedCurrencies->iso3); $i++)
        {
            if ($this->selectedCurrencies->iso3[$i] === $selectedIso3Front)
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
            if ($rowInputValueFront < $this->selectedCurrencies->minimal_value[$this->idSelectedCurrency]
                ||
                $rowInputValueFront > $this->selectedCurrencies->maximal_value[$this->idSelectedCurrency])
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

        /* for row value
        $decimal_separator = $this->getParams()->get('decimal_separator');
        $thousands_separator = $this->getParams()->get('thousand_separator');

        return str_replace([$thousands_separator, $decimal_separator], ['', '.'], $formatedValue); // DB format number to normal
        */
    }
}
