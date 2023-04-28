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
        $this->getValue($data, $repeatCounter, $opts);

        if ($this->isEditable())
        {
            return $this->render($data, $repeatCounter);
        }
        else
        {
            $htmlId = $this->getHTMLId($repeatCounter);

            // $$$ rob even when not in ajax mode the element update() method may be called in which case we need the span
            // $$$ rob changed from span wrapper to div wrapper as element's content may contain divs which give html error

            // Placeholder to be updated by ajax code
            // @TODO the entity decode causes problems on RO with tooltips
            $v = $this->getROElement($data, $repeatCounter);
            $v = html_entity_decode($v);
            $v = $this->DBFormatToNormal($v);
            //$v = $v == '' ? '&nbsp;' : $v;

            return '<div class="fabrikElementReadOnly" id="' . $htmlId . '">' . $v . '</div>';
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
        $this->BDRequest();
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


		if (!$this->getFormModel()->failedValidation())
		{
			if ($this->isEditable())
			{
				$value = $this->numberFormat($value);
			}
		}

		if (!$this->isEditable())
		{
			if ($params->get('render_as_qrcode', '0') === '1')
			{
				// @TODO - skip this is new form
				if (!empty($value))
				{
					$value = $this->qrCodeLink($data);
				}
			}
			else
			{
				$this->_guessLinkType($value, $data);
				$value = $this->format($value, false);
				$value = $this->getReadOnlyOutput($value, $value);
			}

			return ($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}
		else
		{
			if ($params->get('autocomplete', '0') === '3')
			{
				$bits['class'] .= ' fabrikGeocomplete';
				$bits['autocomplete'] = 'off';
			}
		}

		/* stop "'s from breaking the content out of the field.
		 * $$$ rob below now seemed to set text in field from "test's" to "test&#039;s" when failed validation
		 * so add false flag to ensure its encoded once only
		 * $$$ hugh - the 'double encode' arg was only added in 5.2.3, so this is blowing some sites up
		 */
		if (version_compare(phpversion(), '5.2.3', '<'))
		{
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}

		$bits['class'] .= ' ' . $params->get('text_format');

		if ($params->get('speech', 0))
		{
			$bits['x-webkit-speech'] = 'x-webkit-speech';
		}

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->scanQR = $params->get('scan_qrcode', '0') === '1';
		$layoutData->attributes = $bits;

        $layoutData->dataSelect = $this->BDRequest();

		$layoutData->sizeClass = $params->get('bootstrap_class', '');

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
	 * Format guess link type
	 *
	 * @param   string  &$value         Original field value
	 * @param   array   $data           Record data
	 *
	 * @return  void
	 */
	protected function _guessLinkType(&$value, $data) // PAS TOUCHE
	{
		$params = $this->getParams();

		if ($params->get('guess_linktype') == '1')
		{
			$w = new FabrikWorker;
			$opts = $this->linkOpts();
			$title = $params->get('link_title', '');
			$attrs = $params->get('link_attributes', '');

			if (!empty($attrs))
			{
				$attrs = $w->parseMessageForPlaceHolder($attrs);
				$attrs = explode(' ', $attrs);

				foreach ($attrs as $attr)
				{
					list($k, $v) = explode('=', $attr);
					$opts[$k] = trim($v, '"');
				}
			}
			else
			{
				$attrs = array();
			}

			if ((new MediaHelper)->isImage($value))
			{
				$alt = empty($title) ? '' : 'alt="' . strip_tags($w->parseMessageForPlaceHolder($title, $data)) . '"';
				$value = '<img src="' . $value . '" ' . $alt . ' ' . implode(' ', $attrs) . ' />';
			}
			else
			{
				if (FabrikWorker::isEmail($value) || JString::stristr($value, 'http'))
				{
				}
				elseif (JString::stristr($value, 'www.'))
				{
					$value = 'http://' . $value;
				}

				if ($title !== '')
				{
					$opts['title'] = strip_tags($w->parseMessageForPlaceHolder($title, $data));
				}

				$label = FArrayHelper::getValue($opts, 'title', '') !== '' ? $opts['title'] : $value;

				$value = FabrikHelperHTML::a($value, $label, $opts);
			}
		}
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


    public function BDRequest() // pour récup les donées de la table data_country_phone_info
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
        return str_replace('-', '', $number);
    }

}
