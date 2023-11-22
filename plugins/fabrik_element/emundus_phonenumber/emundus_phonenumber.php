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
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.emundus_phonenumber
 * @since       3.0
 */
class PlgFabrik_ElementEmundus_phonenumber extends PlgFabrik_Element
{

    protected array $countries;
    /**
     * Pre-render just the element (no labels etc.)
     * Was _getElement but this was ambiguous with getElement() and method is public
     *
     * @param   array $data          data
     * @param   int   $repeatCounter repeat group counter
     *
     * @return  string
     */
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
        $opts                = array('runplugins' => 1);

        $value = $this->getValue($data, $repeatCounter, $opts);

        if (is_array($value)) { // in case of non-valid information, return array
            $value = $value['country'].$value['country_code'].$value['num_tel'];
        }

        $value = $this->DBFormatToE164Format($value);
        $this->countries = $this->DBRequest(); // avoid multiple call on DB

        if ($this->isEditable())
        {
            return $this->render($data, $repeatCounter);
        }
        else
        {
            $htmlId = $this->getHTMLId($repeatCounter);
            return '<div class="fabrikElementReadOnly" id="' . $htmlId . '">' . $value . '</div>';
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
		$bits = $this->inputProperties($repeatCounter);

		$value = $this->getValue($data, $repeatCounter);

        if (is_array($value)) { // in case of non-valid information, return array
            $value = $value['country'].$value['country_code'].$value['num_tel'];
        }

        $bits['inputValue'] = $this->DBFormatToE164Format($value);
        $bits['selectValue'] = substr($value, 0, 2);

        if (is_array($value)) // validation error
        {
            $bits['inputValue'] = $value['country_code'].$value['num_tel'];
            $bits['selectValue'] = $value['country'];
        }

        $bits['mustValidate'] = $this->validator->hasValidations(); // is the element mandatory ?

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->attributes = $bits;

        $layoutData->dataSelect = $this->countries;
		$countries_to_display = [];
		$countries_to_display_param = (array)$this->getParams()->get('countries_options', []);
		foreach ($countries_to_display_param as $country) {
			if(!in_array($country->country, $countries_to_display))
			{
				$countries_to_display[] = $country->country;
			}
		}

		if(!empty($countries_to_display))
		{
			$layoutData->dataSelect = array_filter($layoutData->dataSelect, function($country) use ($countries_to_display) {
				return in_array($country->iso2, $countries_to_display);
			});
		}

		$layoutData->dataSelect = array_values($layoutData->dataSelect);

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
     * Shows the data formatted for the list view
     *
     * @param   string   $data     Elements data
     * @param   stdClass &$thisRow All the data in the lists current row
     * @param   array    $opts     Rendering options
     *
     * @return  string    formatted value
     */
    public function renderListData($data, stdClass &$thisRow, $opts = array())
    {
        if (!is_null($data))
        {
            $data = $this->DBFormatToE164Format($data);
        }
        return parent::renderListData($data, $thisRow, $opts);
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
		return $val['country'].$val['country_code'].$val['num_tel']; // ZZ concat with +XXYYYY to ZZ+XXYYYY format DB
	}

    /**
     * Get all iso2 and flags (not null) from table data_country
     *
     * @return  array|mixed         JSON object array
     */
    public function DBRequest()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT iso2,label_fr,label_en,flag,flag_img')
            ->from($db->quoteName('data_country'));
        $db->setQuery($query);

        $db->execute();
        return $db->loadObjectList();
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
        $isValid = false;
        $value = $data['country_code'].$data['num_tel'];
        $is_valid_JS = $data['is_valid'] == 'on';

        $minimalNumberlength = 5; // without counting '+', self-consider it's the minimal length, CAN BE CHANGED
        $maximalNumberlength = 15; // without counting '+', maximal phone number length e.164 format, NO CHANGE

        $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_INVALID'); // error as default value

        if ($this->validator->hasValidations()) { // phone mandatory so big validation

            if (preg_match('/^\+\d{'.$minimalNumberlength.','.$maximalNumberlength.'}$/', $value) && $is_valid_JS)
            {
                $isValid = true;
            }
            else
            {
                if ($value[0] !== "+")
                {
                    $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_BEGIN_WITH_PLUS');
                }
                else if (!(preg_match('/\+\d+$/', $value)))
                {
                    $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_ONLY_NUMBERS');
                }
                else if (strlen($value) < $minimalNumberlength + 1) // counting the '+'
                {
                    $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_SIZE_ERROR');
                }
            }
        }
        else if ($is_valid_JS && preg_match('/^\+\d*$/', $value)) // phone not mandatory but still a little validation
        {
            $isValid = true;
        }
        return $isValid;
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
        $params = $this->getParams();

        $id = $this->getHTMLId($repeatCounter);

        $opts = $this->getElementJSOptions($repeatCounter);
        $opts->default_country = $params->get('default_country');

	    $countries_to_display = [];
	    $countries_to_display_param = (array)$params->get('countries_options', []);
	    foreach ($countries_to_display_param as $country) {
		    if(!in_array($country->country, $countries_to_display))
		    {
			    $countries_to_display[] = $country->country;
		    }
	    }

	    if(!empty($countries_to_display))
	    {
		    $opts->allCountries = array_filter($this->countries, function($country) use ($countries_to_display) {
			    return in_array($country->iso2, $countries_to_display);
		    });
	    } else {
		    $opts->allCountries = $this->countries;
	    }

	    $opts->allCountries = array_values($opts->allCountries);


        return array('FbPhoneNumber', $id, $opts);
    }

    /**
     * Returns +XXYYYY format from ZZ+XXYYYY DB format data
     *
     * @param   string    $number       string DB format
     * @return  string
     */
    public function DBFormatToE164Format($number)
    {
        return substr($number, 2, strlen($number));
    }

}
