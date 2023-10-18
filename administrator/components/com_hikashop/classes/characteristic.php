<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopCharacteristicClass extends hikashopClass{

	var $tables = array('characteristic','characteristic');
	var $pkeys = array('characteristic_parent_id','characteristic_id');
	var $deleteToggle = array('variant'=>array('variant_characteristic_id','variant_product_id'));

	public function saveForm() {
		$element = new stdClass();
		$element->characteristic_id = hikashop_getCID('characteristic_id');
		$formData = hikaInput::get()->get('data', array(), 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['characteristic'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean($value, 'string');
		}

		$element->values = hikaInput::get()->get('characteristic', array(), 'array' );
		hikashop_toInteger($element->values);
		$element->values_ordering = hikaInput::get()->get('characteristic_ordering', array(), 'array' );
		hikashop_toInteger($element->values);
		hikashop_toInteger($element->values_ordering);

		$status = $this->save($element);

		if(!$status){
			hikaInput::get()->set( 'fail', $element  );
		}elseif(@$element->characteristic_parent_id==0){
			$this->updateValues($element,$status);
		}
		return $status;
	}

	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCharacteristicDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterCharacteristicDelete', array(&$elements));
		}
		return $status;
	}

	public function save(&$element){
		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->getTranslations($element);
		$new = empty($element->characteristic_id);
		if(!$new)
			$element->old = $this->get($element->characteristic_id);
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if($new) {
			$app->triggerEvent('onBeforeCharacteristicCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeCharacteristicUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($new) {
			$app->triggerEvent('onAfterCharacteristicCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterCharacteristicUpdate', array( &$element ));
		}
		if($status){
			if($translationHelper->isMulti()) {
				$columns = array('characteristic_value');
				$translationHelper->checkTranslations($element, $columns);
			}
			$translationHelper->handleTranslations('characteristic',$status,$element);
		}
		return $status;
	}

	public function updateValues(&$element,$status){
		$filter='';
		if(count($element->values)){
			$filter = ' AND characteristic_id NOT IN ('.implode(',',$element->values).')';
		}
		$query = 'DELETE FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = '.$status.$filter;
		$this->database->setQuery($query);
		$this->database->execute();

		if(count($element->values)){
			$query = 'UPDATE '.hikashop_table('characteristic').' SET characteristic_parent_id='.$status.' WHERE characteristic_id IN ('.implode(',',$element->values).') AND characteristic_parent_id<1';
			$this->database->setQuery($query);
			$this->database->execute();
		}
		if(count($element->values_ordering)){
			foreach($element->values_ordering as $key => $value){
				if(!$value) continue;
				$this->database->setQuery('UPDATE '.hikashop_table('characteristic').' SET characteristic_ordering='.(int)$value.' WHERE characteristic_id='.(int)$element->values[$key]);
				$this->database->execute();
			}
		}
	}

	public function loadConversionTables(&$obj){
		$obj->characteristics = array();
		$obj->characteristicsConversionTable = array();
		$query = 'SELECT * FROM '.hikashop_table('characteristic'). ' ORDER BY characteristic_parent_id ASC, characteristic_ordering ASC';
		$this->database->setQuery($query);
		$obj->characteristics = $this->database->loadObjectList('characteristic_id');
		$app = JFactory::getApplication();
		$translationHelper = hikashop_get('helper.translation');
		if(!hikashop_isClient('administrator') && $translationHelper->isMulti(true) && class_exists('JFalangDatabase')){
			$this->database->setQuery($query);
			$obj->characteristics = array_merge($obj->characteristics,$this->database->loadObjectList('characteristic_id','stdClass',false));
		}elseif(!hikashop_isClient('administrator') && $translationHelper->isMulti(true) && (class_exists('JFDatabase')||class_exists('JDatabaseMySQLx'))){
			$this->database->setQuery($query);
			$obj->characteristics = array_merge($obj->characteristics,$this->database->loadObjectList('characteristic_id','stdClass',false));
		}
		if(!empty($obj->characteristics)){
			foreach($obj->characteristics as $characteristic){
				$key = '';
				$key_alias = '';
				if(!empty($characteristic->characteristic_parent_id) && !empty($obj->characteristics[$characteristic->characteristic_parent_id])){
					if(function_exists('mb_strtolower')){
						$key = mb_strtolower(trim($obj->characteristics[$characteristic->characteristic_parent_id]->characteristic_value)).'_';
						$key_alias = mb_strtolower(trim($obj->characteristics[$characteristic->characteristic_parent_id]->characteristic_alias)).'_';
					}else{
						$key = strtolower(trim($obj->characteristics[$characteristic->characteristic_parent_id]->characteristic_value)).'_';
						$key_alias = strtolower(trim($obj->characteristics[$characteristic->characteristic_parent_id]->characteristic_alias)).'_';
					}
				}
				if(function_exists('mb_strtolower')){
					$key2 = mb_strtolower(trim($characteristic->characteristic_value,'" '));
				}else{
					$key2 = strtolower(trim($characteristic->characteristic_value,'" '));
				}
				$key .= $key2;
				$key_alias .= $key2;
				if(!empty($characteristic->characteristic_alias)){
					if(function_exists('mb_strtolower')){
						$alias = mb_strtolower(trim($characteristic->characteristic_alias,'" '));
					}else{
						$alias = strtolower(trim($characteristic->characteristic_alias,'" '));
					}
					$obj->characteristicsConversionTable[$alias]=$characteristic->characteristic_id;
				}
				$obj->characteristicsConversionTable[$key_alias]=$characteristic->characteristic_id;
				$obj->characteristicsConversionTable[$key]=$characteristic->characteristic_id;
				$obj->characteristicsConversionTable[$key2]=$characteristic->characteristic_id;
			}
		}
	}

	public function findValue($value, $characteristic_parent_id, $vendor_id = 0) {
		$ret = false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$query = 'SELECT count(characteristic_id) FROM ' . hikashop_table('characteristic').
				' WHERE characteristic_value LIKE ' . $this->db->Quote($value) . ' AND characteristic_parent_id = ' . (int)$characteristic_parent_id;
		if($vendor_id > 0)
			$query .= ' AND characteristic_vendor_id IN (0, ' . (int)$vendor_id . ')';

		$this->db->setQuery($query);
		$ret = (int)$this->db->loadResult();

		return $ret;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$characteristics = null;
		if(isset($typeConfig['params']['value']) && $typeConfig['params']['value']) {
			if((int)$options['url_params']['ID'] > 0) {
				$query = 'SELECT characteristic_value, characteristic_alias, characteristic_id, characteristic_ordering FROM ' . hikashop_table('characteristic').' WHERE characteristic_parent_id = ' . (int)$options['url_params']['ID'];
				if(!empty($options['vendor']))
					$query .= ' AND characteristic_vendor_id IN (0, '.(int)$options['vendor'].')';
				if(!empty($search))
					$query .= ' AND (characteristic_value LIKE \'%' . hikashop_getEscaped($search) . '%\' OR characteristic_alias LIKE \'%' . hikashop_getEscaped($search) . '%\')';
				$this->database->setQuery($query);
				$characteristics = $this->database->loadObjectList('characteristic_id');
			}

			if(!empty($value)) {

			}
		} else {

			$query = 'SELECT characteristic_value, characteristic_alias, characteristic_id, characteristic_ordering FROM ' . hikashop_table('characteristic').' WHERE characteristic_parent_id = 0';
			if(!empty($options['vendor']))
				$query .= ' AND characteristic_vendor_id IN (0, '.(int)$options['vendor'].')';
			$this->database->setQuery($query);
			$characteristics = $this->database->loadObjectList('characteristic_id');

			if(!empty($value)) {

			}
		}

		if(!empty($characteristics)) {
			$this->orderValues($characteristics);
			foreach($characteristics as $v) {
				$v->name = hikashop_translate($v->characteristic_value);
				if( !empty($v->characteristic_alias) && $v->characteristic_value != $v->characteristic_alias ) {
					$v->name .= ' ('.$v->characteristic_alias.')';
				}
				$ret[0][$v->characteristic_id] = $v;
			}
		} else {
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onNameboxCharacteristicsLoad', array( $typeConfig, &$fullLoad, $mode, $value, $search, $options, &$ret ));
		}
		unset($characteristics);

		return $ret;
	}

	public function orderValues(&$characteristics){
		if(empty($characteristics))
			return;

		$sortedChars = array();
		$config = hikashop_config();
		$sort = $config->get('characteristics_values_sorting');
		if($sort == 'old') {
			$order = 'characteristic_id';
		}elseif($sort == 'alias') {
			$order = 'characteristic_alias';
		}elseif($sort == 'ordering') {
			$order = 'characteristic_ordering';
		}else{
			$order = 'characteristic_value';
		}
		foreach($characteristics as $k => $char) {
			$key = '';

			if(in_array($sort,array('old','ordering'))) {
				$key = sprintf('%04d', $char->$order);
			} else {
				$key = $char->$order;
			}

			$key .= '+'.$char->characteristic_id;
			$sortedChars[$key] =& $characteristics[$k];
		}
		ksort($sortedChars);
		$characteristics = $sortedChars;
	}
}
