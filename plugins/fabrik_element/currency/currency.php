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
        $this->inRepeatGroup = $groupModel->canRepeat();
        $this->_inJoin       = $groupModel->isJoin();
        $this->allCurrency   = $this->getDataCurrency();
        $formatedInputValueBack    = $this->getValue($data, $repeatCounter);


        $this->selectedCurrencies = $this->getSelectedCurrencies();

        $this->rowInputValueBack = is_array($formatedInputValueBack)
            ? $formatedInputValueBack['rowInputValueFront']
            : $this->getNumbersInputValueBack($formatedInputValueBack);

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
		$params = $this->getParams();
		$element = $this->getElement();
		$bits = $this->inputProperties($repeatCounter);

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->attributes = $bits;

		return $layout->render($layoutData);
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

        $number = floatval($val['rowInputValueFront']);
        $iso3 = $val['selectedIso3Front'];
        $currencyObject = $this->getCurrencyObject($this->getDataCurrency(), $iso3);

        $decimal_separator = $this->getParams()->get('decimal_separator');
        $thousands_separator = $this->getParams()->get('thousand_separator');
        $decimalNumber = $this->getParams()->get('decimal_numbers');
        // $regex = $this->getParams()->get('regex'); // pas sur de réussir

        $numberFormated = number_format($number, $decimalNumber, $decimal_separator, $thousands_separator);
        $currencyFormated = $this->formatSymbol($currencyObject->symbol). ' ('. $iso3. ')';

		return $numberFormated . ' ' . $currencyFormated;
	}

    private function formatSymbol($symbol)
    {
        $formatedSymbol = utf8_encode($symbol);

        //TODO remake the formatSymbol to match every symbol
        // currently not working for all symbols
        /*if (substr($formatedSymbol, -2) === '')
        {
            $formatedSymbol = '\''.$formatedSymbol.'\'';
        }
        */

        return $formatedSymbol;
    }

    private function getSymbol($string) // will be use to show data to export + details + folders
    {
        $from = strpos($string, ' ')+1;
        $to = strrpos($string, ' ');

        return substr($string,$from, $to-$from);
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

    public function getIso3($input = null)
    {
        return $this->getSelectedCurrencies()->iso3[0];
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

        $selectedIso3Front = $data['selectedIso3Front'];
        $rowInputValueFront = $data['rowInputValueFront'];

        $valueBack = $this->getValue($data, $repeatCounter);
        $valid = $this->currencyFormatValidation($selectedIso3Front, $this->getIso3($valueBack));

        if ($valid)
        {
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

    private function currencyFormatValidation($selectedIso3Front, $selectedIso3Back)
    {
        $valid = true;

        if (!($selectedIso3Front === $selectedIso3Back)) // not the same currency
        {
            $this->validationError = JText::_('PLG_ELEMENT_CURRENCY_CURRENCY_ERROR');
            $valid = false;
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
            if ($rowInputValueFront < $this->getParams()->get('minimal_value')
                ||
                $rowInputValueFront > $this->getParams()->get('maximal_value'))
            {
                $this->validationError = JText::_('PLG_ELEMENT_CURRENCY_NOT_IN_INTERVALS');
                $valid = false;
            }
        }

        return $valid;
    }

    private function getNumbersInputValueBack($formatedInputValueBack)
    {
        $to  = strpos($formatedInputValueBack, ' ');
        return substr($formatedInputValueBack,0 , $to); // get only numbers from DB format

        /* for row value
        $decimal_separator = $this->getParams()->get('decimal_separator');
        $thousands_separator = $this->getParams()->get('thousand_separator');

        return str_replace([$thousands_separator, $decimal_separator], ['', '.'], $formatedValue); // DB format number to normal
        */
    }
}
