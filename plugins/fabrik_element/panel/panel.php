<?php
/**
 * Plugin element to render plain text/HTML
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.display
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Plugin element to render plain text/HTML
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.display
 * @since       3.0
 */

class PlgFabrik_ElementPanel extends PlgFabrik_Element
{
	/**
	 * Db table field type
	 *
	 * @var  string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * Does the element's data get recorded in the db
	 *
	 * @var bool
	 */
	protected $recordInDatabase = false;

	/**
	 * Set/get if element should record its data in the database
	 *
	 * @deprecated - not used
	 *
	 * @return bool
	 */

	public function setIsRecordedInDatabase()
	{
		$this->recordInDatabase = false;
	}

	/**
	 * Get the element's HTML label
	 *
	 * @param   int     $repeatCounter  Group repeat counter
	 * @param   string  $tmpl           Form template
	 *
	 * @return  string  label
	 */

	public function getLabel($repeatCounter = 0, $tmpl = '')
	{
		$params = $this->getParams();
		$element = $this->getElement();

		if (!$params->get('display_showlabel', true))
		{
			$element->label = $this->getValue(array());
			$element->label_raw = $element->label;
		}

		return parent::getLabel($repeatCounter, $tmpl);
	}

	/**
	 * Get the element's raw label (used for details view, not wrapped in <label> tags
	 *
	 * @return  string  Label
	 */
	protected function getRawLabel()
	{
		if (!$this->getParams()->get('display_showlabel', true))
		{
			return $this->getValue(array());;
		}

		return parent::getRawLabel();
	}

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

        unset($this->default);
		$value = $this->getValue(ArrayHelper::fromObject($thisRow));

		return parent::renderListData($value, $thisRow, $opts);
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
		$layout = $this->getLayout('form');
		$displayData = new stdClass;
		$displayData->id = $this->getHTMLId($repeatCounter);
		$displayData->name = $this->getHTMLName($repeatCounter);
		$displayData->type = $params->get('type', 0);
		$displayData->accordion = $params->get('accordion', '');
		$displayData->title = $params->get('title', '');
		$displayData->iconType = $params->get('panel_icon_type', '');

		switch ($displayData->type) {
			case 1:
				$displayData->backgroundColor = '#ebeefa';
				$displayData->iconColor = '#525b85';
				$displayData->icon = 'info';
				break;
			case 2:
				$displayData->backgroundColor = '#fff6de';
				$displayData->iconColor = '#b38405';
				$displayData->icon = 'warning';
				break;
			case 3:
				$displayData->backgroundColor = '#fae9e9';
				$displayData->iconColor = '#a60e15';
				$displayData->icon = 'error';
				break;
			case 4:
				$displayData->backgroundColor = 'transparent';
				$displayData->iconColor = 'transparent';
				$displayData->icon = '';
				break;
			default:
				$displayData->backgroundColor = $params->get('panel_background', '#ebeefa');
				$displayData->iconColor = $params->get('panel_icon_color', '#525b85');
				$displayData->icon = $params->get('panel_icon', 'info');
		}


		$displayData->value = $this->getValue($data, $repeatCounter);

		return $layout->render($displayData);
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
		return null;
	}

	/**
	 * Helper method to get the default value used in getValue()
	 * Unlike other elements where readonly effects what is displayed, the display element is always
	 * read only, so get the default value.
	 *
	 * @param   array  $data  Form data
	 * @param   array  $opts  Options
	 *
	 * @since  3.0.7
	 *
	 * @return  mixed	value
	 */

	protected function getDefaultOnACL($data, $opts)
	{
		return FArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joined groups we need to know what part of the array to access
	 * @param   array  $opts           Options
	 *
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$value = $this->getDefaultOnACL($data, $opts);

		if ($value === '')
		{
			// Query string for joined data
			$value = FArrayHelper::getValue($data, $value);
		}

		$formModel = $this->getFormModel();

		// Stops this getting called from form validation code as it messes up repeated/join group validations

		if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1)
		{
			FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
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
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

		return array('FbPanel', $id, $opts);
	}
}
