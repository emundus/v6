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
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.emundus_phonenumber
 * @since       3.0
 */
class PlgFabrik_ElementEmundus_phonenumber extends PlgFabrik_Element
{

	/**
	 * Format the string for use in list view, email data
	 *
	 * @param   mixed $d               data
	 * @param   bool  $doNumberFormat  run numberFormat()
	 *
	 * @return string
	 */
	protected function format(&$d, $doNumberFormat = true) // PAS TOUCHE
	{
		$params = $this->getParams();
		$format = $params->get('text_format_string');
		$formatBlank = $params->get('field_format_string_blank', true);

		if ($doNumberFormat)
		{
			$d = $this->numberFormat($d);
		}

		if ($format != '' && ($formatBlank || $d != ''))
		{
			$d = sprintf($format, $d);
		}

		if ($params->get('password') == '1')
		{
			$d = str_pad('', JString::strlen($d), '*');
		}

		return $d;
	}


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
        $value = $this->DBFormatToNormal($value);

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
	public function render($data, $repeatCounter = 0) // first à ce render
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$bits = $this->inputProperties($repeatCounter);
		/* $$$ rob - not sure why we are setting $data to the form's data
		 * but in table view when getting read only filter value from url filter this
		 * _form_data was not set to no readonly value was returned
		 * added little test to see if the data was actually an array before using it
		 */

		if (is_array($this->getFormModel()->data))
		{
			$data = $this->getFormModel()->data;
		}

		$value = $this->getValue($data, $repeatCounter);
        $bits['inputValue'] = $this->DBFormatToNormal($value);
        $bits['selectValue'] = substr($value, 0, 2);

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->attributes = $bits;

        $layoutData->dataSelect = $this->DBRequest();

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
            $data = $this->DBFormatToNormal($data);
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

		if (is_array($val))
		{
			foreach ($val as $k => $v)
			{
				$val[$k] = $this->_indStoreDatabaseFormat($v);
			}

			$val = implode(GROUPSPLITTER, $val);
		}
		else
		{
			$val = $this->_indStoreDatabaseFormat($val);
		}

		return $val;
	}

	/**
	 * Manipulates individual values posted form data for insertion into database
	 *
	 * @param   string  $val  This elements posted form data
	 *
	 * @return  string
	 */
	protected function _indStoreDatabaseFormat($val)
	{
		return $this->unNumberFormat($val);
	}


    public function DBRequest() // pour récup les donées de la table data_country_phone_info
    {
        $db = JFactory::getDbo();
        $query = 'SELECT dc.iso2, dc.flag FROM data_country dc
                    WHERE dc.flag IS NOT NULL';
        $db->setQuery($query);

        $db->execute();

        return $db->loadObjectList(); // on renvoit toutes les données sous forme de liste d'object (format JSON)
    }


    public function validate($data, $repeatCounter = 0)
    {
        $value = $this->DBFormatToNormal((string)$data); // +XX-YYYY format to +XXYYYY format for tests only
        $isValid = false;

        if (preg_match("/^\+\d{5,15}$/", $value))
        {
            $isValid = true;
        }
        else
        {
            if ($value[0] !== "+")
            {
                $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_BEGIN_WITH_PLUS');
            }
            else if (!(preg_match("/\+\d+$/", $value)))
            {
                $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_ONLY_NUMBERS');
            }
            else
            {
                $this->validationError = JText::_('PLG_ELEMENT_PHONE_NUMBER_SIZE_ERROR');
            }
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
        $opts->countrySelected = $params->get('default_country');

        return array('FbPhoneNumber', $id, $opts);
    }

    public function DBFormatToNormal($number)
    {
        return substr($number, 2, strlen($number));
    }

}
