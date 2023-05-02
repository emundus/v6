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
        $value = $this->DBFormatToE164Format($value);

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
		$bits = $this->inputProperties($repeatCounter);

		$value = $this->getValue($data, $repeatCounter);
        $bits['inputValue'] = $this->DBFormatToE164Format($value);
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

		if (is_array($val))
		{
			foreach ($val as $k => $v)
			{
				$val[$k] = $this->unNumberFormat($v);
			}

			$val = implode(GROUPSPLITTER, $val);
		}
		else
		{
			$val = $this->unNumberFormat($val);
		}

		return $val;
	}

    /**
     * Get all iso2 and flags (not null) from table data_country
     *
     * @return  array|mixed         JSON object array
     */
    public function DBRequest()
    {
        $db = JFactory::getDbo();
        $query = 'SELECT dc.iso2, dc.flag FROM data_country dc
                    WHERE dc.flag IS NOT NULL';
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
        $value = $this->DBFormatToE164Format((string)$data); // ZZ+XXYYYY to +XXYYYY format
        $isValid = false;

        if (preg_match('/^\+\d{5,15}$/', $value))
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
