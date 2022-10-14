<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketModulesClass extends hikamarketClass {

	protected $tables = array('joomla.modules');
	protected $pkeys = array('id');
	protected $toggle = array('published' => 'id');

	public function get($id, $default = '') {
		$obj = parent::get($id);
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		if(is_null($obj))
			$obj = new stdClass();

		if(!empty($obj->id))
			$obj->hikamarket_params = $config->get('params_' . $obj->id, null);

		if(empty($obj->hikamarket_params))
			$obj->hikamarket_params = $shopConfig->get('default_params', null);

		$this->loadParams($obj);
		return $obj;
	}

	protected function loadParams(&$result) {
		if(empty($result->params))
			return;

		$registry = new JRegistry;
		if(!HIKASHOP_J30)
			$registry->loadJSON($result->params);
		else
			$registry->loadString($result->params);
		$result->params = $registry->toArray();
	}

	public function saveForm($id = null) {
		$module = new stdClass();
		$formData = hikaInput::get()->get('module', array(), 'array');
		if(!empty($formData)) {
			foreach($formData as $column => $value) {
				hikamarket::secureField($column);
				if(is_array($value)) {
					$module->$column = array();
					foreach($value as $k2 => $v2) {
						hikamarket::secureField($k2);
						$module->{$column}[$k2] = strip_tags($v2);
					}
				} else {
					$module->$column = strip_tags($value);
				}
			}
		}

		$element = array();
		$formData = hikaInput::get()->get('config', array(), 'array');
		if(isset($module->id) && empty($id))
			$params_name = 'params_'.(int)$module->id;
		else
			$params_name = 'params_'.(int)$id;

		if(!empty($formData[$params_name])) {
			foreach($formData[$params_name] as $column => $value) {
				hikamarket::secureField($column);
				$element[$column] = strip_tags($value);
			}
		}

		$formData = hikaInput::get()->get('moduleconfig', array(), 'array');

		if(!empty($formData[$params_name])) {
			foreach($formData[$params_name] as $column => $value){
				hikamarket::secureField($column);
				$module->$column = strip_tags($value);
			}
		}
		$module->hikamarket_params =& $element;

		$result = $this->save($module);
		return $result;
	}

	public function save(&$element) {
		if(!empty($element->params) && is_array($element->params)) {
			$handler = JRegistryFormat::getInstance('JSON');
			$element->params = $handler->objectToString($element->params);
		}
		$element->id = parent::save($element);

		if($element->id && !empty($element->hikamarket_params)){
			$config =& hikamarket::config();
			$update_config = new stdClass();
			$params_name = 'params_'.$element->id;
			$update_config->$params_name = $element->hikamarket_params;
			if($config->save($update_config)) {
				$config->set($params_name, $element->hikamarket_params);
			}
		}
		return $element->id;
	}

	public function delete(&$elements) {
		$result = parent::delete($elements);
		if(!$result)
			return false;

		if(!is_array($elements))
			$elements = array($elements);

		if(!empty($elements)) {
			$ids = array();
			foreach($elements as $id) {
				$ids[] = $this->db->Quote('params_' . (int)$id);
			}
			$query = 'DELETE FROM '.hikamarket::table('config').' WHERE config_namekey IN (' . implode(',', $ids) . ');';
			$this->db->setQuery($query);
			$result = $this->db->execute();
		}
		return $result;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$sqlFilters = array();
		if(!empty($options['filters'])) {
			foreach($options['filters'] as $filter) {
			}
		}

		$module_types = array(
			'mod_hikamarket',
			'mod_hikashop',
		);
		$sqlTypes = array();
		foreach($module_types as $type) {
			$sqlTypes[] = 'module = ' . $this->db->Quote($type);
			if(!HIKASHOP_J30)
				$sqlTypes[] = 'module LIKE \''.$this->db->getEscaped($type.'_', true).'%\'';
			else
				$sqlTypes[] = 'module LIKE \''.$this->db->escape($type.'_', true).'%\'';
		}
		$sqlFilters[] = '('.implode(' OR ', $sqlTypes).')';

		if(!empty($search)) {
			$searchMap = array('title', 'id');
			$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$sqlFilters[] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$sqlSort = 'id';

		$max = 15;
		$start = (int)@$options['page'];

		$query = 'SELECT id, title, module '.
			' FROM ' . hikamarket::table('joomla.modules') .
			' WHERE ('.implode(') AND (', $sqlFilters).') '.
			' ORDER BY '.$sqlSort;
		$this->db->setQuery($query, $start, $max+1);
		$modules = $this->db->loadObjectList('id');
		if(count($modules) > $max) {
			$fullLoad = false;
			array_pop($modules);
		}

		if(!empty($value) && !is_array($value) && (int)$value > 0) {
			$value = (int)$value;
			if(isset($modules[$value])) {
				$ret[1] = $modules[$value];
			} else {
				$query = 'SELECT id, title, module '.
					' FROM ' . hikamarket::table('joomla.modules') .
					' WHERE id = ' . $value;
				$this->db->setQuery($query);
				$ret[1] = $this->db->loadObject();
			}
		} else if(!empty($value) && is_array($value)) {
			hikamarket::toInteger($value);
			$query = 'SELECT id, title, module '.
				' FROM ' . hikamarket::table('joomla.modules') .
				' WHERE id IN (' . implode(',', $value) . ')';
			$this->db->setQuery($query);
			$module_values = $this->db->loadObjectList('id');
			$ret[1] = array();
			foreach($value as $v) {
				if(isset($module_values[$v]))
					$ret[1][$v] = $module_values[$v];
			}
			unset($module_values);
		}

		if(!empty($modules))
			$ret[0] = $modules;

		return $ret;
	}
}
