<?php
/**
 * Plugin element to render field with PHP calculated value
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.calc
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Plugin element to render field with PHP calculated value
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.calc
 * @since       3.0
 */
class PlgFabrik_ElementCalc extends PlgFabrik_Element
{
	/**
	 * This really does get just the default value (as defined in the element's settings)
	 *
	 * @param   array  $data  Form data
	 *
	 * @return mixed
	 */
	public function getDefaultValue($data = array())
	{
		if (!isset($this->default))
		{
			$w = new FabrikWorker;
			$element = $this->getElement();
			$default = $w->parseMessageForPlaceHolder($element->default, $data, true, true);
			/* calc in fabrik3.0/3.1 doesn't have eval, issues if F2.0 calc elements are migrated*/
			/*if ($element->eval == '1')
			{
				if (FabrikHelperHTML::isDebug())
				{
					$res = eval($default);
				}
				else
				{
					$res = @eval($default);
				}
				FabrikWorker::logEval($res, 'Eval exception : ' . $element->name . '::getDefaultValue() : ' . $default . ' : %s');
				$default = $res;
			}
			*/
			$this->default = $default;
		}

		return $this->default;
	}

	/**
	 * Get value
	 *
	 * @param   array  $data           Value
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  string
	 */
	private function _getV($data, $repeatCounter)
	{
		$w = new FabrikWorker;
		$groupModel = $this->getGroup();
		$name = $this->getFullName(true, false);
		$params = $this->getParams();

		// $$$ hugh - if we don't do this, we get the cached default from the previous repeat
		if ($repeatCounter > 0)
		{
			unset($this->default);
		}

		/**
		 *  $$$ hugh - don't think we want to do this here, otherwise calc gets run regardless of calc_on_save_only,
		 *  it just won't get used if 'true'
		 *  $default = $this->getDefaultValue($data, $repeatCounter);
		 */
		$default = '';
		/**
		 *  If viewing form or details view and calc set to always run then return the $default
		 *  which has had the calculation run on it.
		 */
		$task = $this->app->input->get('task', '');

		if (!$params->get('calc_on_save_only', true) || $task == 'form.process' || $task == 'process')
		{
			// $default = $this->getDefaultValue($data, $repeatCounter);
			$this->swapValuesForLabels($data);
			$this->setStoreDatabaseFormat($data, $repeatCounter);
			$default = $w->parseMessageForRepeats($params->get('calc_calculation'), $data, $this, $repeatCounter);
			$default = $w->parseMessageForPlaceHolder($default, $data, true, true);

			//  $$$ hugh - standardizing on $data but need need $d here for backward compat
			$d = $data;
			FabrikWorker::clearEval();
			$res = FabrikHelperHTML::isDebug() ? eval($default) : @eval($default);
			FabrikWorker::logEval($res, 'Eval exception : ' . $this->getElement()->name . ' (id ' . $this->getId() . ')::_getV() : ' . $default . ' : %s');

			return $res;
		}

		$rawName = $name . '_raw';

		if ($groupModel->isJoin())
		{
			$data = (array) $data;

			if (array_key_exists($name, $data))
			{
				$data[$name] = (array) $data[$name];
			}

			if ($groupModel->canRepeat())
			{
				if (array_key_exists($name, $data) && array_key_exists($repeatCounter, $data[$name]))
				{
					$default = $data[$name][$repeatCounter];
				}
				else
				{
					// erm ... ?? ... ^^
					if (array_key_exists($name, $data) && array_key_exists($repeatCounter, $data[$name]))
					{
						$default = $data[$name][$repeatCounter];
					}
				}
			}
			else
			{
				if (array_key_exists($name, $data))
				{
					$default = $data[$name];
				}
				else
				{
					if (array_key_exists($rawName, $data))
					{
						$default = $data[$rawName];
					}
				}
			}
		}
		else
		{
			// When called from getFilterArray via getROElement, $data doesn't exist
			// (i.e. when specified as a table___name=foo in a content plugin)
			if (is_array($data))
			{
				if (array_key_exists($name, $data))
				{
					$default = $data[$name];
				}
				else
				{
					if (array_key_exists($rawName, $data))
					{
						$default = $data[$rawName];
					}
				}
			}
		}

		return $default;
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
		if (!isset($this->defaults) || is_null($this->defaults))
		{
			$this->defaults = array();
		}

		if (!array_key_exists($repeatCounter, $this->defaults))
		{
			$element = $this->getElement();
			$element->default = $this->_getV($data, $repeatCounter);
			$formModel = $this->getFormModel();

			// Stops this getting called from form validation code as it messes up repeated/join group validations
			if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1)
			{
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}

			if (is_array($element->default))
			{
				$element->default = implode(',', $element->default);
			}

			$this->defaults[$repeatCounter] = $element->default;
		}

		return $this->defaults[$repeatCounter];
	}

	/**
	 * run on formModel::setFormData()
	 * Appends the calculation to the form's data when the form is submitted
	 *
	 * @param   int  $c  Repeat group counter
	 *
	 * @return void
	 */

	public function preProcess($c)
	{
		$form = $this->getFormModel();
		$data = unserialize(serialize($form->formData));

		/**
		 * get the key name in dot format for updateFormData method
		 * $$$ hugh - added $rawKey stuff, otherwise when we did "$key . '_raw'" in the updateFormData
		 * below on repeat data, it ended up in the wrong format, like join.XX.table___element.0_raw
		 */
		$key = $this->getFullName(true, false);
		$rawKey = $key . '_raw';
		$this->swapValuesForLabels($data);
		$res = $this->_getV($data, $c);

		// Create arrays for calc values as needed
		if (is_array($data[$key]))
		{
			$data[$key][$c] = $res;
		}
		elseif (!isset($data[$key]) && $c == 0)
		{
			$data[$key] = $res;
		}
		else
		{
			$group = $this->getGroup();

			if ($group->canRepeat())
			{
				if (!isset($data[$key]))
				{
					$data[$key] = array();
				}
				else
				{
					$data[$key] = array($data[$key]);
				}

				$data[$key][$c] = $res;
			}
			else
			{
				$data[$key] = $res;
			}
		}

		$form->updateFormData($key, $data[$key]);
		$form->updateFormData($rawKey, $data[$key]);
	}

	/**
	 * Allows the element to pre-process a rows data before and join merging of rows
	 * occurs. Used in calc element to do calcs on actual row rather than merged row
	 *
	 * @param   string  $data  Elements data for the current row
	 * @param   object  $row   Current row's data
	 *
	 * @since	3.0.5
	 *
	 * @return  string	Formatted value
	 */
	public function preFormatFormJoins($data, $row)
	{
		$params = $this->getParams();
		$element_data = $data;

		if ($params->get('calc_on_save_only', 0))
		{
			$element_data = $this->getFormattedValue($element_data);

			return parent::preFormatFormJoins($element_data, $row);
		}
		else
		{
			$element = $this->getElement();
			$cal = $params->get('calc_calculation', '');
			$listModel = $this->getlistModel();
			$formModel = $this->getFormModel();
			$data = ArrayHelper::fromObject($row);
			$data['rowid'] = $data['__pk_val'];
			$data['fabrik'] = $formModel->getId();

			//  $$$ Paul - Because this is run on List rows before repeat-group merges, repeat group placeholders are OK.
			//  $$$ hugh - standardizing on $data but need need $d here for backward compat
			$d = $data;
			$w = new FabrikWorker;
			$cal = $w->parseMessageForPlaceHolder($cal, $data, true, true);
			FabrikWorker::clearEval();

			if (FabrikHelperHTML::isDebug())
			{
				$res = eval($cal);
			}
			else
			{
				$res = @eval($cal);
			}

			FabrikWorker::logEval($res, 'Eval exception : ' . $element->name . ' (id ' . $this->getId() . ')::preFormatFormJoins() : ' . $cal . ' : %s');

			$res = $this->getFormattedValue($res);

			// $$$ hugh - need to set _raw, might be needed if (say) calc is being used as 'use_as_row_class'
			// See comments in formatData() in table model, we might could move this to a renderRawListData() method.
			$raw_name = $this->getFullName(true, false) . '_raw';
			$row->$raw_name = str_replace(GROUPSPLITTER, ',', $res);

			return parent::preFormatFormJoins($res, $row);
		}
	}

	/**
	 * Prepares the element data for CSV export
	 *
	 * @param   string  $data      Element data
	 * @param   object  &$thisRow  All the data in the lists current row
	 *
	 * @return  string	Formatted value
	 */
	public function renderListData_csv($data, &$thisRow)
	{
		$val = $this->renderListData($data, $thisRow);
		$col = $this->getFullName(true, false);
		$raw = $col . '_raw';
		$thisRow->$raw = $val;

		return $val;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to pre-populate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */
	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$data = $this->getFormModel()->data;
		$value = $this->getFormattedValue($this->getValue($data, $repeatCounter));

		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$str = array();

		if ($this->canView())
		{
			if (!$this->isEditable())
			{
				$value = $this->replaceWithIcons($value);
				$str[] = $value;
			}
			else
			{
				$layout = $this->getLayout('form');
				$layoutData = new stdClass;
				$layoutData->id = $id;
				$layoutData->name = $name;
				$layoutData->height = $element->height;
				$layoutData->value = $value;
				$layoutData->cols = $element->width;
				$layoutData->rows = $element->height;
				$str[] = $layout->render($layoutData);
			}
		}
		else
		{
			// Make a hidden field instead
			$str[] = '<input type="hidden" class="fabrikinput" name="' . $name . '" id="' . $id . '" value="' . $value . '" />';
		}

		if (in_array($this->app->input->get('format', 'html'), array('html', 'partial')))
		{
			$opts  = array('alt' => FText::_('PLG_ELEMENT_CALC_LOADING'), 'style' => 'display:none;padding-left:10px;', 'class' => 'loader');
			$str[] = FabrikHelperHTML::image('ajax-loader.gif', 'form', @$this->tmpl, $opts);
		}

		return implode("\n", $str);
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
		$params = $this->getParams();
		$calc = $params->get('calc_calculation');
		$obs = array();
		$opts->ajax = $params->get('calc_ajax', 0) == 0 ? false : true;

		if ($opts->ajax)
		{
			if ($params->get('calc_ajax_observe_all', '0') === '0')
			{
				$obs = preg_replace('#\s#', '', $params->get('calc_ajax_observe'));
				$obs = explode(',', $obs);

				if (preg_match_all("/{[^}\s]+}/i", $calc, $matches) !== 0)
				{
					$matches = $matches[0];
					$obs     = array_merge($obs, $matches);
				}

				foreach ($obs as $key => &$m)
				{

					if (empty($m))
					{
						unset($obs[$key]);
						continue;
					}

					$m = str_replace(array('{', '}'), '', $m);

					// $$$ hugh - we need to knock any _raw off, so JS can match actual element ID
					$m = preg_replace('#_raw$#', '', $m);
				}
			}
			else
			{
				$formModel = $this->getFormModel();
				$groups    = $formModel->getGroupsHiarachy();

				foreach ($groups as $groupModel)
				{
					$elementModels = $groupModel->getPublishedElements();

					foreach ($elementModels as $elementModel)
					{
						if ($elementModel->getElement()->plugin !== 'calc')
						{
							$obs[] = $elementModel->getFullName(true, false);
						}
					}
				}
			}
		}

		$opts->observe = array_values(array_unique($obs));
		$opts->calcOnLoad = (bool) $params->get('calc_on_load', false);
		$opts->id = $this->id;

		return array('FbCalc', $id, $opts);
	}

	/**
	 * Perform calculation from ajax request
	 *
	 * @return  void
	 */

	public function onAjax_calc()
	{
		$input = $this->app->input;
		$this->setId($input->getInt('element_id'));
		$this->loadMeForAjax();
		$params        = $this->getParams();
		$w             = new FabrikWorker;
		$filter        = JFilterInput::getInstance();
		$d             = $filter->clean($_REQUEST, 'array');
		$formModel     = $this->getFormModel();
		$repeatCounter = $this->app->input->get('repeatCounter', '0');
		$formModel->addEncrytedVarsToArray($d);
		$this->getFormModel()->data = $d;
		$this->swapValuesForLabels($d);
		$calc = $params->get('calc_calculation');
		$this->setStoreDatabaseFormat($d);

		// $$$ hugh - trying to standardize on $data so scripts know where data is
		$data = $d;
        $calc = $w->parseMessageForRepeats($calc, $data, $this, $repeatCounter);
        $calc = $w->parseMessageForPlaceHolder($calc, $d);
		$c    = FabrikHelperHTML::isDebug() ? eval($calc) : @eval($calc);
		$c    = preg_replace('#(\/\*.*?\*\/)#', '', $c);
		$c    = $this->getFormattedValue($c);

		echo $c;
	}

	/**
	 * Get sum query
	 *
	 * @param   FabrikFEModelList  &$listModel  List model
	 * @param   array              $labels      Label
	 *
	 * @return string
	 */
	protected function getSumQuery(&$listModel, $labels = array())
	{
		$fields = $listModel->getDBFields($this->getTableName(), 'Field');
		$name = $this->getElement()->name;
		$field = FArrayHelper::getValue($fields, $name, false);

		if ($field !== false && $field->Type == 'time')
		{
			$db = $listModel->getDb();
			$label = count($labels) == 0 ? "'calc' AS label" : 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
			$name = $this->getFullName(false, false);
			$table = $listModel->getTable();
			$joinSQL = $listModel->buildQueryJoin();
			$whereSQL = $listModel->buildQueryWhere();

			return "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC($name))) AS value, $label FROM " . $db->qn($table->db_table_name)
				. " $joinSQL $whereSQL";
		}
		else
		{
			return parent::getSumQuery($listModel, $labels);
		}
	}

	/**
	 * Build the query for the avg calculation
	 *
	 * @param   FabrikFEModelList  &$listModel  list model
	 * @param   array              $labels      Labels
	 *
	 * @return  string	sql statement
	 */
	protected function getAvgQuery(&$listModel, $labels = array())
	{
		$fields = $listModel->getDBFields($this->getTableName(), 'Field');
		$name = $this->getElement()->name;
		$field = FArrayHelper::getValue($fields, $name, false);

		if ($field !== false && $field->Type == 'time')
		{
			$db = $listModel->getDb();
			$label = count($labels) == 0 ? "'calc' AS label" : 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
			$name = $this->getFullName(false, false);
			$table = $listModel->getTable();
			$joinSQL = $listModel->buildQueryJoin();
			$whereSQL = $listModel->buildQueryWhere();

			return "SELECT SEC_TO_TIME(AVG(TIME_TO_SEC($name))) AS value, $label FROM " . $db->qn($table->db_table_name)
				. " $joinSQL $whereSQL";
		}
		else
		{
			return parent::getAvgQuery($listModel, $labels);
		}
	}

	/**
	 * Get a query for our median query
	 *
	 * @param   FabrikFEModelList  &$listModel  List
	 * @param   array              $labels      Label
	 *
	 * @return string
	 */
	protected function getMedianQuery(&$listModel, $labels = array())
	{
		$fields = $listModel->getDBFields($this->getTableName(), 'Field');
		$name = $this->getElement()->name;
		$field = FArrayHelper::getValue($fields, $name, false);

		if ($field !== false && $field->Type == 'time')
		{
			$db = $listModel->getDb();
			$label = count($labels) == 0 ? "'calc' AS label" : 'CONCAT(' . implode(', " & " , ', $labels) . ')  AS label';
			$name = $this->getFullName(false, false);
			$table = $listModel->getTable();
			$joinSQL = $listModel->buildQueryJoin();
			$whereSQL = $listModel->buildQueryWhere();

			return "SELECT SEC_TO_TIME(TIME_TO_SEC($name)) AS value, $label FROM " . $db->qn($table->db_table_name)
				. " $joinSQL $whereSQL";
		}
		else
		{
			return parent::getMedianQuery($listModel, $labels);
		}
	}

	/**
	 * Get the sprintf format string
	 *
	 * @since 3.0.4
	 *
	 * @return string
	 */
	public function getFormatString()
	{
		$params = $this->getParams();

		return $params->get('calc_format_string');
	}

	/**
	 * Get the formatted value
	 *
	 * @param  $value
	 *
	 * @since 3.5
	 */
	public function getFormattedValue($value)
	{
		$format = $this->getFormatString();

		if (!empty($format))
		{
			$value = sprintf($format, $value);
		}

		return $value;
	}

	/**
	 * Get JS code for ini element list js
	 * Overwritten in plugin classes
	 *
	 * @return string
	 */

	public function elementListJavascript()
	{
		$params = $this->getParams();
		$id = $this->getHTMLId();
		$list = $this->getlistModel()->getTable();
		$opts = new stdClass;
		$opts->listid = $list->id;
		$opts->listRef = 'list_' . $this->getlistModel()->getRenderContext();
		$opts->formid = $this->getFormModel()->getId();
		$opts->elid = $this->getElement()->id;
		$opts->doListUpdate = $params->get('calc_on_save_only', '1') == '0' && $params->get('calc_ajax', '0') == '1';
		$opts = json_encode($opts);

		return "new FbCalcList('$id', $opts);\n";
	}

	/**
	 * Update list data
	 *
	 * @return  void
	 */

	public function onAjax_listUpdate()
	{
		$input = $this->app->input;
		$listId = $input->getInt('listid');
		$elId = $input->getInt('element_id');
		$this->setId($elId);
		$this->loadMeForAjax();

		/** @var FabrikFEModelList $listModel */
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($listId);
		$data = $listModel->getData();
		$return = new stdClass;
		/**
		 * $$$ hugh ... no, we never need to store in this context.  The 'calc_on_save_only' param simply dictates
		 * whether we re-calc when displaying the element, or just use the stored value.  So if calc_on_save_only is
		 * set, then when displaying in lists, we don't execute the calc, we just used the stored value fro the database.
		 * And that logic is handled in _getV(), so we don't need to do the $store stuff.
		 */
		$listRef = 'list_' . $listModel->getRenderContext() . '_row_';

		foreach ($data as $group)
		{
			foreach ($group as $row)
			{
				$key = $listRef . $row->__pk_val;
				$row->rowid = $row->__pk_val;

				$return->$key = $this->getFormattedValue($this->_getV(ArrayHelper::fromObject($row), 0));
			}
		}

		echo json_encode($return);
	}

	/**
	 * Turn form value into email formatted value
	 * $$$ hugh - I added this as for reasons I don't understand, something to do with
	 * how the value gets calc'ed during preProcess, sometimes the calc is "right" when
	 * it's submitted to the database, but wrong during form email plugin processing.  So
	 * I gave up trying to work out why, and now just re-calc it during getEmailData()
	 *
	 * @param   mixed  $value          Element value
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  Group repeat counter
	 *
	 * @return  string  email formatted value
	 */
	protected function getIndEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		$params = $this->getParams();

		if (!$params->get('calc_on_save_only', true))
		{
			$value = $this->_getV($data, $repeatCounter);
		}

		$value = $this->getFormattedValue($value);

		return $value;
	}

	/**
	 * Get database field description
	 * For calc, as we have no idea what they will be storing, needs to be TEXT.
	 *
	 * @return  string  db field type
	 */
	public function getFieldDescription()
	{
		if ($this->encryptMe())
		{
			return 'BLOB';
		}

		return 'TEXT';
	}

    /**
     * Is the element consider to be empty for purposes of rendering on the form,
     * i.e. for assigning classes, etc.  Can be overridden by individual elements.
     *
     * @param   array $data          Data to test against
     * @param   int   $repeatCounter Repeat group #
     *
     * @return  bool
     */
    public function dataConsideredEmpty($data, $repeatCounter)
    {
        $parts = explode("\n", $data);

        // see if all it contains is the "\n" and loader gif added in render ...
        if (count($parts) === 2)
        {
            if (empty($parts[0]) && strstr($parts[1], 'loader'))
            {
                return true;
            }
        }

        return false;
    }
}
