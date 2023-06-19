<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopModulesClass extends hikashopClass{
	var $pkeys=array('id');
	var $toggle = array('published'=>'id');
	function getTable(){
		return hikashop_table('modules',false);
	}

	function get($id,$default=''){
		$obj = parent::get($id);
		$config =& hikashop_config();
		if(is_null($obj)){
			$obj= new stdClass();
		}
		if(!empty($obj->id)){
			$obj->hikashop_params = $config->get('params_'.$obj->id,null);
		}
		if(empty($obj->hikashop_params)){
			$obj->hikashop_params = $config->get('default_params',null);
		}
		$this->loadParams($obj);
		return $obj;
	}

	function loadParams(&$result){
		if(!empty($result->params) && is_string($result->params)){
			$registry = new JRegistry;
			if(!HIKASHOP_J30)
				$registry->loadJSON($result->params);
			else
				$registry->loadString($result->params);
			$result->params = $registry->toArray();
		}
	}

	function saveForm($id=null){
		$module = new stdClass();
		$formData = hikaInput::get()->get('module', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		if(!empty($formData)){
			foreach($formData as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					$module->$column=array();
					foreach($value as $k2 => $v2){
						hikashop_secureField($k2);
						$module->{$column}[$k2] = $safeHtmlFilter->clean(strip_tags($v2), 'string');
					}
				}else{
					$module->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
				}
			}
		}

		$element = array();
		$formData = hikaInput::get()->get('config', array(), 'array');
		if (isset($module->id) && empty($id))
			$params_name = 'params_'.(int)$module->id;
		else
			$params_name = 'params_'.(int)$id;

		if(!empty($formData[$params_name])){
			foreach($formData[$params_name] as $column => $value){
				hikashop_secureField($column);
				$element[$column] = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
			if(empty($element['selectparentlisting'])){
				$categoryClass = hikashop_get('class.category');
				$mainProductCategory = 'product';
				$categoryClass->getMainElement($mainProductCategory);
				$element['selectparentlisting']=$mainProductCategory;
			}
		}

		$formData = hikaInput::get()->get('moduleconfig', array(), 'array');

		if(!empty($formData[$params_name])){
			foreach($formData[$params_name] as $column => $value){
				hikashop_secureField($column);
				$module->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}
		$module->hikashop_params =& $element;
		$result = $this->save($module);
		return $result;
	}

	function save(&$element){

		if(!empty($element->params)&&is_array($element->params)){
			$handler = JRegistryFormat::getInstance('JSON');
			$element->params = $handler->objectToString($element->params);
		}
		$element->id = parent::save($element);

		if($element->id && !empty($element->hikashop_params)){
			$configClass =& hikashop_config();
			$config=new stdClass();
			$params_name = 'params_'.$element->id;
			$config->$params_name = $element->hikashop_params;
			if($configClass->save($config)){
				$configClass->set($params_name,$element->hikashop_params);
			}


			if(!HIKASHOP_J30) return $element->id;

			$plugin = JPluginHelper::getPlugin('system', 'cache');
			$params = new JRegistry(@$plugin->params);

			$options = array(
				'defaultgroup'	=> 'page',
				'browsercache'	=> $params->get('browsercache', false),
				'caching'		=> false,
			);

			$cache		= JCache::getInstance('page', $options);
			$cache->clean();
		}
		return $element->id;
	}

	function delete(&$elements){
		$result = parent::delete($elements);
		if($result){
			if(!is_array($elements)){
				$elements=array($elements);
			}
			if(!empty($elements)){
				$ids = array();
				foreach($elements as $id){
					$ids[]=$this->database->Quote('params_'.(int)$id);
				}
				$query = 'DELETE FROM '.hikashop_table('config').' WHERE config_namekey IN ('.implode(',',$ids).');';
				$this->database->setQuery($query);
				return $this->database->execute();
			}
		}
		return $result;
	}

	function restrictedModule($params) {
		$display = $params->get('display_on_product_page', false);
		if($display === false)
			return true;

		$option = hikaInput::get()->getVar('option', ''); // com_hikashop
		$ctrl = hikaInput::get()->getVar('ctrl', ''); // product, category, checkout
		$task = hikaInput::get()->getVar('task', ''); // show, listing, compare, ...

		if($option != 'com_hikashop')
			return true;

		if(!in_array($ctrl, array('product', 'category', 'checkout')))
			return true;

		if(in_array($ctrl, array('product', 'category'))) {
			if(!in_array($task, array('show', 'listing', 'compare', 'waitlist', 'contact')))
				return true;

			if($task == 'show' && $ctrl == 'product' && (int)$params->get('display_on_product_page', 1) == 0)
				return false;
			if($task == 'listing' && $ctrl == 'product' && (int)$params->get('display_on_product_listing_page', 1) == 0)
				return false;

			if($task == 'compare' && $ctrl == 'product' && (int)$params->get('display_on_product_compare_page', 1) == 0)
				return false;
			if($task == 'listing' && $ctrl == 'category' && (int)$params->get('display_on_category_listing_page', 1) == 0)
				return false;
			if($task == 'contact' && $ctrl == 'product' && (int)$params->get('display_on_contact_page', 1) == 0)
				return false;
			if($task == 'waitlist' && $ctrl == 'product' && (int)$params->get('display_on_waitlist_page', 1) == 0)
				return false;
		}
		if($ctrl == 'checkout' && (int)$params->get('display_on_checkout_page', 1) == 0) {
			return false;
		}

		return true;
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
			' FROM ' . hikashop_table('modules', false) .
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
					' FROM ' . hikashop_table('modules', false) .
					' WHERE id = ' . $value;
				$this->db->setQuery($query);
				$ret[1] = $this->db->loadObject();
			}
		} else if(!empty($value) && is_array($value)) {
			hikashop_toInteger($value);
			$query = 'SELECT id, title, module '.
				' FROM ' . hikashop_table('modules', false) .
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
