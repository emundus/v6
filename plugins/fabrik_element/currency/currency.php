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

use Joomla\CMS\Helper\MediaHelper;
use Joomla\Utilities\ArrayHelper;

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
    protected string $inputValueBack;
    protected string $selectedIso3Back;


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
        $this->inputValueBack    = $this->getValue($data, $repeatCounter);
        $this->selectedIso3Back  = $this->getIso3($this->inputValueBack);


        if ($this->isEditable())
        {
            return $this->render($data, $repeatCounter);
        }
        else
        {
            $htmlId = $this->getHTMLId($repeatCounter);
            return '<div class="fabrikElementReadOnly" id="' . $htmlId . '">' . $this->inputValueBack . '</div>';
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
        $opts->value = $this->inputValueBack;
        $opts->selectedIso3 = $this->selectedIso3Back;
        $opts->min_value = $this->getParams()->get('minimal_value');
        $opts->max_value = $this->getParams()->get('maximal_value');

        $opts->thousand_separator = $this->getParams()->get('thousand_separator');
        $opts->decimal_separator = $this->getParams()->get('decimal_separator');


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
        // $regex = $this->getParams()->get('regex'); // pas sure de réussir

        $numberFormated = number_format($number, $decimalNumber, $decimal_separator, $thousands_separator);
        $currencyFormated = $currencyObject->symbol . ' ('. $iso3. ')';

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

    public function getIso3($input = null)
    {
        return $input !== ''
            ? substr($input,-4 , -1)
            : $this->getParams()->get('default_currency');
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

        $valueBack = $this->getValue([], $repeatCounter);
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
            // error cause not only numbers
            $valid = false;
        }
        else
        {
            if ($rowInputValueFront < $this->getParams()->get('minimal_value')
                ||
                $rowInputValueFront > $this->getParams()->get('maximal_value'))
            {
                // error cause not in intervals
                $valid = false;
            }
        }

        return $valid;
    }
}
