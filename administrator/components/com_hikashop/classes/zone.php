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
class hikashopZoneClass extends hikashopClass{

	var $tables = array('zone_link','zone_link','zone');
	var $pkeys = array('','','zone_id');
	var $namekeys = array('zone_parent_namekey','zone_child_namekey','zone_namekey');
	var $deleteToggle = array('zone_link'=>array('zone_parent_namekey','zone_child_namekey'));
	var $toggle = array('zone_published'=>'zone_id');

	function saveForm() {
		$zone = new stdClass();
		$zone->zone_id = hikashop_getCID('zone_id');
		$formData = hikaInput::get()->get('data', array(), 'array');
		$status = false;
		if(!empty($formData['zone'])){
			jimport('joomla.filter.filterinput');
			$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
			foreach($formData['zone'] as $column => $value){
				hikashop_secureField($column);
				$zone->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}

			$status = $this->save($zone);

			if(!$status){
				hikaInput::get()->set( 'fail', $zone  );
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_( 'DUPLICATE_ZONE' ), 'error');
			}
		}
		return $status;
	}

	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeZoneDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterZoneDelete', array(&$elements));
		}
		return $status;
	}

	public function save(&$element) {
		if(!empty($element->zone_id)) {
			$element->old = $this->get($element->zone_id);
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if(empty($element->zone_id)) {
			$app->triggerEvent('onBeforeZoneCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeZoneUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if(empty($element->zone_id)) {
			$app->triggerEvent('onAfterZoneCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterZoneUpdate', array( &$element ));
		}
		return $status;
	}

	function getZones($ids, $columns = '*', $key = 'zone_id', $returnArrayWithOneColumn = false) {
		if(is_numeric($ids))
			$ids = array($ids);
		if(!is_array($ids))
			return array();
		if($key == 'zone_id') {
			hikashop_toInteger($ids);
		} else {
			$key = preg_replace('#[^a-z_]#','',$key);
			foreach($ids as $k => $id) {
				if(is_object($id)) {
					$ids[$k] = $this->database->Quote(@$id->$key);
					continue;
				}
				$ids[$k] = $this->database->Quote($id);
			}
		}

		$columns = explode(',', $columns);
		foreach($columns as &$column) {
			$column = trim($column);
			if($column == '*')
				continue;
			$column = $this->database->quoteName($column);
		}
		unset($column);

		$query = 'SELECT '.implode(',', $columns).' FROM #__hikashop_zone WHERE '.hikashop_secureField($key).' IN ('.implode(',',$ids).')';
		$this->database->setQuery($query);

		if($returnArrayWithOneColumn)
			return $this->database->loadColumn();
		return $this->database->loadObjectList();
	}

	function getZoneParents($zone_id, $already = array()) {
		static $level = 0;
		if(!count($already)) $level = 0;
		if($level>10) return array();
		$level++;

		if(!is_array($zone_id)){
			if(is_numeric($zone_id)){
				$zone = $this->get($zone_id);
				if($zone){
					$zone_id = $zone->zone_namekey;
				}
			}
			$zone_id = array($zone_id);
		}
		$quoted = array();
		foreach($zone_id as $zone){
			$quoted[]=$this->database->Quote($zone);
		}
		$query = 'SELECT a.zone_parent_namekey FROM '.hikashop_table('zone_link').' AS a WHERE a.zone_child_namekey IN ('.implode(',',$quoted).');';
		$this->database->setQuery($query);
		$parents = $this->database->loadColumn();
		$results = array();

		foreach($zone_id as $z){
			$results[$z]=$z;
		}

		if(!empty($parents)){
			$getParents = array();
			foreach($parents as $p){
				if(!isset($already[$p])){
					$getParents[]=$p;
				}
				$results[$p]=$p;
			}
			if(!empty($getParents)){
				$grandparents = $this->getZoneParents($getParents,$results);
				foreach($grandparents as $gp){
					$results[$gp]=$gp;
				}
			}
		}

		return $results;
	}

	function getZoneCurrency($zone_id){
		$zone = $this->get($zone_id);
		$already = array($zone->zone_namekey);
		$childs = array($zone->zone_namekey);
		if(empty($zone->zone_currency_id)){
			while(!empty($childs)){
				$quoted = array();
				foreach($childs as $z){
					$quoted[]=$this->database->Quote($z);
				}
				$query = 'SELECT b.* FROM '.hikashop_table('zone_link').' AS a LEFT JOIN '.hikashop_table('zone').' AS b ON a.zone_parent_namekey=b.zone_namekey WHERE a.zone_child_namekey IN ('.implode(',',$quoted).');';
				$this->database->setQuery($query);
				$parents = $this->database->loadObjectList();
				$childs = array();
				if(!empty($parents)){
					foreach($parents as $parent){
						if(in_array($parent->zone_namekey,$already)) continue;
						if(!empty($parent->zone_currency_id)){
							return (int)$parent->zone_currency_id;
						}
						$childs[]=$parent->zone_namekey;
						$already[]=$parent->zone_namekey;
					}
				}
			}
		}
		return (int)$zone->zone_currency_id;
	}

	function getOrderZones(&$order, $force = null) {
		$fieldClass = hikashop_get('class.field');
		$fields = $fieldClass->getData('frontcomp', 'address');

		$field = 'address_country';
		if(isset($fields['address_state']) && $fields['address_state']->field_type == 'zone' && !empty($order->shipping_address) && !empty($order->shipping_address->address_state) && (!is_array($order->shipping_address->address_state) || count($order->shipping_address->address_state) > 1 || !empty($order->shipping_address->address_state[0]))) {
			$field = 'address_state';
		}

		if(in_array($force, array('billing_address','shipping_address'))) {
			$type = $force;
		} else {
			$type = 'shipping_address';
			if(empty($order->shipping_address) && !empty($order->billing_address)) {
				$type = 'billing_address';
			}
		}

		if($type == 'shipping_address' && !empty($order->order_shipping_id)) {
			$shippingClass = hikashop_get('class.shipping');
			$shipping = $shippingClass->get($order->order_shipping_id);
			if(!empty($shipping) && !empty($shipping->shipping_params) && !empty($shipping->shipping_params->override_tax_zone)) {
				$zoneClass = hikashop_get('class.zone');
				$zone = $zoneClass->get($shipping->shipping_params->override_tax_zone);
				if(!empty($zone)) {
					return array($zone->zone_namekey);
				}
			}
		}

		if(empty($order->$type) || empty($order->$type->$field)) {
			$zone = hikashop_getZone('shipping', array('object' => $order));
			$zones = $this->getZoneParents($zone);
		} else {
			$zones =& $order->$type->$field;
			$field_namekey = $field . '_orig';
			if(isset($order->$type->$field_namekey)) {
				$zones = $order->$type->$field_namekey;
			}
			if(!is_array($zones)) {
				$zones = array($zones);
			}
		}
		return $zones;
	}

	function getNamekey($element) {
		return $element->zone_type.'_'.preg_replace('#[^a-z_]#i', '', $element->zone_name_english).'_'.rand();
	}

	function getChilds($zone_namekey) {
		return $this->getChildren($zone_namekey);
	}

	function getChildren($zone_namekey) {
		if(is_numeric($zone_namekey)) {
			$zone = $this->get($zone_namekey);
			if(empty($zone))
				return array();
			$zone_namekey = $zone->zone_namekey;
		}
		$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS b LEFT JOIN '.hikashop_table('zone').' AS a ON b.zone_child_namekey=a.zone_namekey WHERE b.zone_parent_namekey  = '.$this->db->Quote($zone_namekey).' ORDER BY a.zone_id';
		$this->db->setQuery($query);
		return  $this->db->loadObjectList();
	}

	function addChilds($mainNamekey, $childNamekeys) {
		return $this->addChildren($mainNamekey, $childNamekeys);
	}

	function addChildren($mainNamekey, $childNamekeys) {
		if(empty($mainNamekey)) return null;
		if(empty($childNamekeys)) return null;
		$NamekeysString = '';
		if(is_numeric($mainNamekey)){
			foreach($childNamekeys as $childNamekey){
				$NamekeysString .= $this->database->Quote($childNamekey).',';
			}
			$NamekeysString .= $this->database->Quote($mainNamekey).',';
			$query = 'SELECT zone_id,zone_namekey FROM '.hikashop_table('zone').' WHERE zone_id  IN ('.rtrim($NamekeysString,',').')';
			$this->database->setQuery($query);
			$zones =  $this->database->loadObjectList('zone_id');
			$newChildNamekeys = array();
			foreach($childNamekeys as $childNamekey){
				$newNamekey = $zones[$childNamekey]->zone_namekey;
				$NamekeysString .= $this->database->Quote($newNamekey).',';
				$newChildNamekeys[] = $newNamekey;
			}
			$mainNamekey = $zones[$mainNamekey]->zone_namekey;
			$childNamekeys = $newChildNamekeys;
		}else{
			foreach($childNamekeys as $childNamekey){
				$NamekeysString .= $this->database->Quote($childNamekey).',';
			}
		}

		$query = 'SELECT zone_child_namekey FROM '.hikashop_table('zone_link').' WHERE zone_parent_namekey  = '.$this->database->Quote($mainNamekey).' AND zone_child_namekey IN ('.rtrim($NamekeysString,',').') LIMIT 1';
		$this->database->setQuery($query);
		$alreadyChild =  $this->database->loadColumn();
		$toInsertNamekeys = array();
		foreach($childNamekeys as $childNamekey){
			if(!in_array($childNamekey,$alreadyChild))$toInsertNamekeys[]=$childNamekey;
		}
		if(empty($toInsertNamekeys)) return null;
		$query = 'INSERT IGNORE INTO '.hikashop_table('zone_link').' (zone_parent_namekey,zone_child_namekey) VALUES ';
		foreach($toInsertNamekeys as $childNamekey){
			$query.='('.$this->database->Quote($mainNamekey).','.$this->database->Quote($childNamekey).'),';
		}
		$this->database->setQuery(rtrim($query,',').';');
		$this->database->execute();
		return $toInsertNamekeys;
	}

	public function getStateDropdownContent($namekey, $field_namekey = '', $field_id = '', $field_type = '') {
		if(empty($namekey))
			return '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';

		if(empty($field_namekey))
			$field_namekey = 'address_state';
		if(empty($field_id))
			$field_id = 'address_state';
		if(empty($field_type))
			$field_type = 'address';

		$query = 'SELECT * FROM '.hikashop_table('field').' WHERE field_namekey = '.$this->db->Quote($field_namekey);
		$this->db->setQuery($query, 0, 1);
		$field = $this->db->loadObject();

		$countryType = hikashop_get('type.country');
		return $countryType->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type, '', $field->field_options);
	}

	public function &getNameboxData(&$typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$db = JFactory::getDBO();
		$zones = null;
		$fullLoad = false;

		$type = 'namekey';
		if(!empty($options['type'])) {
			$type = $options['type'];
			$typeConfig['url'] .= '&return_zonetype=' . urlencode($type);
			$typeConfig['options']['tree_url'] .= '&return_zonetype=' . urlencode($type);
		}
		$column = 'zone_'.$type;
		$parent_column = 'zone_parent_'.$type;

		if(!empty($search)) {
			$limit = 40;
			$searchStr = "'%" . ((HIKASHOP_J30) ? $db->escape($search, true) : $db->getEscaped($search, true) ) . "%'";

			$query = 'SELECT z.*, zp.zone_namekey as zone_parent_namekey, zp.zone_id as zone_parent_id, zp.zone_name_english as zone_parent_name_english, zp.zone_type as zone_parent_type '.
				' FROM ' . hikashop_table('zone') . ' AS z '.
				' LEFT JOIN ' . hikashop_table('zone_link') . ' AS zl ON z.zone_namekey = zl.zone_child_namekey '.
				' LEFT JOIN ' . hikashop_table('zone') . ' AS zp ON zp.zone_namekey = zl.zone_parent_namekey '.
				' WHERE z.zone_published = 1 AND (zp.zone_published IS NULL OR zp.zone_published = 1) AND z.zone_name_english LIKE '.$searchStr.
				' ORDER BY zp.zone_name_english, z.zone_name_english';

			$db->setQuery($query, 0, $limit);
			$zones = $db->loadObjectList('zone_namekey');

			$containers = array();
			foreach($zones as $element) {
				if(!empty($element->zone_parent_namekey) && !isset($containers[ $element->zone_parent_namekey ])) {
					$obj = new stdClass();
					$obj->status = 2;
					$obj->name = $element->zone_parent_name_english;
					$obj->value = $element->$parent_column;
					$obj->data = array();

					$ret[0][] =& $obj;
					$containers[ $element->zone_parent_namekey ] =& $obj;
					unset($obj);
				}

				$obj = new stdClass();
				if($element->zone_type == 'state')
					$obj->status = 0;
				else
					$obj->status = 4;
				$obj->name = $element->zone_name_english;
				$obj->value = $element->$column;
				if(!empty($element->zone_parent_namekey)) {
					$containers[ $element->zone_parent_namekey ]->data[] =& $obj;
				} else {
					$ret[0][] =& $obj;
				}
				unset($obj);
			}

			return $ret;
		}

		if(empty($options['zone_key'])) {

			if(!empty($options['zone_types'])){
				$zone_types = $options['zone_types'];
			}else{
				$zone_types = array('country' => 'COUNTRY', 'ship' => 'SHIPPING');
			}

			$zone_types_db = array();
			foreach($zone_types as $k => $v) {
				$zone_types_db[] = $db->Quote($k);
				$o = new stdClass();
				$o->status = 1;
				$o->name = JText::_($v);
				$o->value = 0;
				$o->data = array();
				$o->noselection = 1;
				$ret[0][] =& $o;
				$zone_types[$k] =& $o;
				unset($o);
			}
			$zone_types_db = implode(',', $zone_types_db);

			$query = 'SELECT zone_id, zone_namekey, zone_name, zone_name_english, zone_code_2, zone_code_3, zone_type '.
					' FROM ' . hikashop_table('zone').
					' WHERE zone_published = 1 AND zone_type IN ('.$zone_types_db.')'.
					' ORDER BY zone_name_english';
		} else {

			$zone_key = $options['zone_key'];

			if($type == 'namekey') {
				$query = 'SELECT z.* '.
					' FROM '.hikashop_table('zone').' as z '.
					' INNER JOIN ' . hikashop_table('zone_link') . ' as zl ON z.zone_namekey = zl.zone_child_namekey '.
					' WHERE z.zone_published = 1 AND zl.zone_parent_namekey = ' . $db->Quote($zone_key).
					' ORDER BY z.zone_name_english';
			} else {
				$query = 'SELECT z.* '.
					' FROM '.hikashop_table('zone').' as z '.
					' INNER JOIN ' . hikashop_table('zone_link') . ' as zl ON z.zone_namekey = zl.zone_child_namekey '.
					' LEFT JOIN ' . hikashop_table('zone') . ' AS zp ON zp.zone_namekey = zl.zone_parent_namekey '.
					' WHERE z.zone_published = 1 AND zp.zone_id = ' . $db->Quote($zone_key).
					' ORDER BY z.zone_name_english';
			}
		}

		$db->setQuery($query);
		$zones = $db->loadObjectList('zone_id');

		if(!empty($zones)) {
			foreach($zones as $zone) {
				$o = new stdClass();
				$o->name = $zone->zone_name_english;
				$o->value = $zone->$column;
				if(isset($zone_types) && isset($zone_types[ $zone->zone_type ])) {
					$o->status = 3;
					$zone_types[ $zone->zone_type ]->data[] =& $o;
				} else {
					$o->status = 0;
					$ret[0][] =& $o;
				}
				unset($o);
			}
		}
		unset($zones);

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			$search = array();
			foreach($value as $v) {
				$search[] = $db->Quote($v);
			}
			$query = 'SELECT zone_id, zone_namekey, zone_name, zone_name_english, zone_code_2, zone_code_3, zone_type '.
					' FROM ' . hikashop_table('zone').
					' WHERE zone_published = 1 AND '.$column.' IN ('.implode(',', $search).')';
			$db->setQuery($query);
			$zones = $db->loadObjectList('zone_id');
			if(!empty($zones)) {
				foreach($zones as $zone) {
					$zone->name = $zone->zone_name_english;
					$ret[1][$zone->$column] = $zone;
				}
			}
			unset($zones);

			if($mode == hikashopNameboxType::NAMEBOX_SINGLE)
				$ret[1] = reset($ret[1]);
		}

		return $ret;
	}
}
