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
 * Plugin element to render emundus_currency
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @since       3.0
 */
class PlgFabrik_Element_Emundus_Currency extends PlgFabrik_Element
{

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
        $profiler = JProfiler::getInstance('Application');
        JDEBUG ? $profiler->mark("renderListData: {$this->element->plugin}: start: {$this->element->name}") : null;

        $data = FabrikWorker::JSONtoData($data, true);
		$params = $this->getParams();

		return parent::renderListData($data, $thisRow, $opts);
	}

	/**
	 * Format the string for use in list view, email data
	 *
	 * @param   mixed $d               data
	 * @param   bool  $doNumberFormat  run numberFormat()
	 *
	 * @return string
	 */
	protected function format(&$d, $doNumberFormat = true)
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
	 * Prepares the element data for CSV export
	 *
	 * @param   string  $data      Element data
	 * @param   object  &$thisRow  All the data in the lists current row
	 *
	 * @return  string	Formatted CSV export value
	 */
	public function renderListData_csv($data, &$thisRow)
	{
		$data = $this->format($data);

		return $data;
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
		$value = parent::getValue($data, $repeatCounter, $opts);

		if (is_array($value))
		{
			return array_pop($value);
		}

		return $value;
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

		$inputMask = trim($params->get('text_input_mask', ''));

		if (!empty($inputMask))
		{
			$opts->use_input_mask = true;
			$opts->input_mask = $inputMask;
			$opts->input_mask_definitions = $params->get('text_input_mask_definitions', '{}');
			$opts->input_mask_autoclear = $params->get('text_input_mask_autoclear', '0') === '1';
		}
		else
		{
			$opts->use_input_mask = false;
			$opts->input_mask = '';
		}

		$opts->geocomplete = $params->get('autocomplete', '0') === '3';

		$config = JComponentHelper::getParams('com_fabrik');
		$apiKey = trim($config->get('google_api_key', ''));
		$opts->mapKey = empty($apiKey) ? false : $apiKey;
		$opts->language = trim(strtolower($config->get('google_api_language', '')));


		if ($this->getParams()->get('autocomplete', '0') == '2')
		{
			$autoOpts = array();
			$autoOpts['max'] = $this->getParams()->get('autocomplete_rows', '10');
			$autoOpts['storeMatchedResultsOnly'] = false;
			FabrikHelperHTML::autoComplete($id, $this->getElement()->id, $this->getFormModel()->getId(), 'field', $autoOpts);
		}

		$opts->scanQR = $params->get('scan_qrcode', '0') === '1';

		return array('FbField', $id, $opts);
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
		return $val;
	}
}
