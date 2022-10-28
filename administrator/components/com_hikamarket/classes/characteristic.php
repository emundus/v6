<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketCharacteristicClass extends hikamarketClass {

	protected $tables = array('shop.characteristic');
	protected $pkeys = array('characteristic_id');
	protected $toggle = array();

	protected $deleteToggle = array(
		'shop.characteristic' => array(
			'characteristic_id',
			'characteristic_parent_id'
		)
	);

	public function frontSaveForm() {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$characteristic_id = hikamarket::getCID('characteristic_id');
		$characteristicClass = hikamarket::get('shop.class.characteristic');
		$fieldsClass = hikamarket::get('shop.class.field');
		$vendor_id = hikamarket::loadVendor(false, false);
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		$stripTags = (int)$config->get('characteristic_strip_tags', 1);

		$formData = hikaInput::get()->get('data', array(), 'array');
		$characteristic_parent_id = hikaInput::get()->getInt('characteristic_parent_id', 0);

		$formCharacteristic = array();
		if(!empty($formData['characteristic']))
			$formCharacteristic = $formData['characteristic'];

		$new = empty($characteristic_id);
		$characteristic = new stdClass();
		$oldCharacteristic = null;

		$reorder_values = array();
		$update_values = array();
		$create_values = array();
		$current_values = array();

		$characteristic->characteristic_id = (int)$characteristic_id;
		$oldCharacteristic = $characteristicClass->get($characteristic_id);

		if(empty($characteristic_parent_id) || !empty($formCharacteristic)) {
			if(!$new && !hikamarket::acl('characteristic/edit'))
				return false;
			if($new && !hikamarket::acl('characteristic/add'))
				return false;

			if($new || $vendor_id <= 1 || (int)$oldCharacteristic->characteristic_vendor_id == $vendor_id) {
				if(hikamarket::acl('characteristic/edit/value') && isset($formCharacteristic['characteristic_value'])) {
					if($stripTags)
						$characteristic->characteristic_value = strip_tags(trim($formCharacteristic['characteristic_value']));
					else
						$characteristic->characteristic_value = $safeHtmlFilter->clean(trim($formCharacteristic['characteristic_value']), 'string');
				}

				if(hikamarket::acl('characteristic/edit/alias') && isset($formCharacteristic['characteristic_alias'])) {
					if($stripTags)
						$characteristic->characteristic_alias = strip_tags(trim($formCharacteristic['characteristic_alias']));
					else
						$characteristic->characteristic_alias = $safeHtmlFilter->clean(trim($formCharacteristic['characteristic_alias']), 'string');
				}

				if($vendor_id <= 1 && hikamarket::acl('characteristic/edit/vendor') && isset($formCharacteristic['characteristic_vendor_id']))
					$characteristic->characteristic_vendor_id = (int)$formCharacteristic['characteristic_vendor_id'];
				if($new && $vendor_id > 1)
					$characteristic->characteristic_vendor_id = (int)$vendor_id;
			}

			$value_edit = hikamarket::acl('characteristic/values/edit/value');
			$vendor_edit = ($vendor_id <= 1) && hikamarket::acl('characteristic/values/edit/vendor');
			$ordering_edit = hikamarket::acl('characteristic/values/ordering');

			if(!$new && hikamarket::acl('characteristic/values/ordering') && !empty($formData['values']) && isset($formData['values']['id']) && isset($formData['values']['ordering'])) {

				$p = array_search('{ID}', $formData['values']['id']);
				if($p !== false) {
					unset($formData['values']['id'][$p]);
					unset($formData['values']['ordering'][$p]);
				}

				hikamarket::toInteger($formData['values']['id']);
				hikamarket::toInteger($formData['values']['ordering']);
				$values = array_combine($formData['values']['id'], $formData['values']['ordering']);

				if(!empty($values)) {
					$query = 'SELECT * FROM ' . hikamarket::table('shop.characteristic') .
						' WHERE characteristic_parent_id = ' . (int)$characteristic_id . ' AND characteristic_id IN ('.implode(',', array_keys($values)).')';
					if($vendor_id > 1)
						$query .= ' AND characteristic_vendor_id = ' . (int)$vendor_id;
					$this->db->setQuery($query);
					$current_values = $this->db->loadObjectList('characteristic_id');
				} else {
					$current_values = array();
				}

				$current_ids = array_keys($current_values);
				hikamarket::toInteger($current_ids);
				$wrong_data = array_diff($formData['values']['id'], $current_ids);
				if(!empty($wrong_data)) {
					foreach($wrong_data as $wd) {
						unset($values[$wd]);
					}
				}

				foreach($values as $k => $value) {
					if(!isset($current_values[$k]))
						continue;
					if((int)$current_values[$k]->characteristic_ordering != $value)
						$reorder_values[$k] = $value;
				}
				unset($values);
			}

			if(!$new && hikamarket::acl('characteristic/values/edit') && !empty($formData['characteristic_value'])) {
				$values = $formData['characteristic_value'];

				if(empty($current_values)) {
					$query = 'SELECT * FROM ' . hikamarket::table('shop.characteristic') .
						' WHERE characteristic_parent_id = ' . (int)$characteristic_id . ' AND characteristic_id IN ('.implode(',', array_keys($values)).')';
					if($vendor_id > 1)
						$query .= ' AND characteristic_vendor_id = ' . (int)$vendor_id;
					$this->db->setQuery($query);
					$current_values = $this->db->loadObjectList('characteristic_id');
				}

				$current_ids = array_keys($current_values);
				hikamarket::toInteger($current_ids);
				$wrong_data = array_diff(array_keys($values), $current_ids);
				if(!empty($wrong_data)) {
					foreach($wrong_data as $wd) {
						unset($values[$wd]);
					}
				}

				foreach($values as $k => $value) {
					if(empty($value['value']))
						continue;

					$v = array();
					if($value_edit)
						$v[] = $safeHtmlFilter->clean(trim($value['value']), 'string');
					if($vendor_edit)
						$v[] = (int)$value['vendor'];
					$update_values[(int)$k] = $v;
				}
			}

			if($new && hikamarket::acl('characteristic/values/add') && !empty($formData['values']) && !empty($formData['values']['value'])) {
				foreach($formData['values']['value'] as $k => $v) {
					if(empty($v) || trim($v) == '')
						continue;
					$v = array($v);
					if($ordering_edit)
						$v[] = (empty($formData['values']['ordering'][$k])) ? 0 : (int)$formData['values']['ordering'][$k];
					if($vendor_edit)
						$v[] = (empty($formData['values']['vendor'][$k])) ? 0 : (int)$formData['values']['vendor'][$k];
					$create_values[] = $v;
				}
			}

		} else {
			if($new || !hikamarket::acl('characteristic/values/edit'))
				return false;
			if($vendor_id > 1 && (int)$oldCharacteristic->characteristic_vendor_id != $vendor_id)
				return false;

			$formCharacteristic = array();
			if(!empty($formData['characteristic_value'][(int)$characteristic_id]))
				$formCharacteristic = $formData['characteristic_value'][(int)$characteristic_id];
			else
				return false;

			if(hikamarket::acl('characteristic/values/edit/value')) {
				if(empty($formCharacteristic['value']) || trim($formCharacteristic['value']) == '')
					return false;
				if($stripTags)
					$characteristic->characteristic_value = strip_tags(trim($formCharacteristic['value']));
				else
					$characteristic->characteristic_value = $safeHtmlFilter->clean(trim($formCharacteristic['value']), 'string');
			}

			if($vendor_id <= 1 && hikamarket::acl('characteristic/values/edit/vendor') && isset($formCharacteristic['vendor']))
				$characteristic->characteristic_vendor_id = (int)$formCharacteristic['vendor'];
		}

		$status = $this->save($characteristic);
		if($status) {

			if(!empty($reorder_values)) {
				$data = array();
				foreach($reorder_values as $k => $v) {
					$data[] = (int)$k . ',' . (int)$v;
				}
				$query = 'INSERT INTO ' . hikamarket::table('shop.characteristic') . ' (characteristic_id, characteristic_ordering) VALUES (' . implode('),(', $data) . ') '.
						'ON DUPLICATE KEY UPDATE characteristic_ordering = VALUES(characteristic_ordering)';
				$this->db->setQuery($query);
				$this->db->execute();
				unset($data);
			}

			if(!empty($update_values)) {
				$data = array();
				foreach($update_values as $k => $v) {
					$d = (int)$k;
					if($value_edit) $d .= ',' . $this->db->Quote( array_shift($v) );
					if($vendor_edit) $d .= ',' . (int)array_shift($v);
					$data[] = $d;
				}
				$dupcolumns = array();
				$columns = array('characteristic_id');
				if($value_edit) {
					$columns[] = 'characteristic_value';
					$dupcolumns[] = 'characteristic_value = VALUES(characteristic_value)';
				}
				if($vendor_edit) {
					$columns[] = 'characteristic_vendor_id';
					$dupcolumns[] = 'characteristic_vendor_id = VALUES(characteristic_vendor_id)';
				}
				$query = 'INSERT INTO ' . hikamarket::table('shop.characteristic') . ' ('.implode(',', $columns).') VALUES (' . implode('),(', $data) . ') '.
						'ON DUPLICATE KEY UPDATE ' . implode(',', $dupcolumns);
				$this->db->setQuery($query);
				$this->db->execute();
				unset($data);
				unset($columns);
				unset($dupcolumns);
			}

			if(!empty($create_values)) {
				$data = array();
				foreach($create_values as $k => $v) {
					$d = $this->db->Quote( array_shift($v) );
					if($ordering_edit) $d .= ',' . (int)array_shift($v);
					$d .= ',' . (($vendor_edit) ? (int)array_shift($v) : (int)$vendor_id);
					$data[] = $d;
				}
				$columns = array('characteristic_parent_id', 'characteristic_alias', 'characteristic_value');
				if($ordering_edit) $columns[] = 'characteristic_ordering';
				$columns[] = 'characteristic_vendor_id';
				$query = 'INSERT IGNORE INTO ' . hikamarket::table('shop.characteristic') . ' ('.implode(',', $columns) . ') '.
						'VALUES (' . (int)$status . ',' . $this->db->Quote('') . ',' . implode('),(' . (int)$status . ',' . $this->db->Quote('') . ',', $data) . ')';
				$this->db->setQuery($query);
				$this->db->execute();
				unset($data);
				unset($columns);
			}

		} else {
			hikaInput::get()->set('fail', $characteristic);
		}
		return $status;
	}

	public function save(&$element) {
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		$do = true;
		$new = empty($element->characteristic_id);

		if($new)
			$app->triggerEvent('onBeforeCharacteristicCreate', array(&$element, &$do));
		else
			$app->triggerEvent('onBeforeCharacteristicUpdate', array(&$element, &$do));

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return false;

		if($new)
			$app->triggerEvent('onAfterCharacteristicCreate', array(&$element));
		else
			$app->triggerEvent('onAfterCharacteristicUpdate', array(&$element));

		return $status;
	}

	public function findValue($value, $characteristic_parent_id, $vendor_id = -1) {
		$ret = false;

		if($vendor_id < 0)
			$vendor_id = hikamarket::loadVendor(false);

		$query = 'SELECT count(characteristic_id) FROM ' . hikamarket::table('shop.characteristic').
				' WHERE characteristic_value LIKE ' . $this->db->Quote($value) . ' AND characteristic_parent_id = ' . (int)$characteristic_parent_id .
				' AND characteristic_vendor_id IN (0, ' . (int)$vendor_id . ')';
		$this->db->setQuery($query);
		$ret = (int)$this->db->loadResult();

		return $ret;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		$app = JFactory::getApplication();

		if(!hikamarket::isAdmin() && ((int)$value1 == 0 || !hikamarket::isVendorCharacteristic((int)$value1, (int)$value2) || !hikamarket::acl('characteristic/delete')))
			return false;

		$query = 'SELECT COUNT(v.variant_product_id) as counter '.
				' FROM ' . hikamarket::table('shop.variant') . ' AS v INNER JOIN ' . hikamarket::table('shop.product') . ' AS p ON v.variant_product_id = p.product_id '.
				' WHERE variant_characteristic_id = '.(int)$value1.' AND p.product_type = ' . $this->db->Quote('variant');
		$this->db->setQuery($query);
		$ret = (int)$this->db->loadResult();
		if($ret > 0)
			return false;

		if((int)$value2 == 0)
			return array('shop.characteristic' => array('characteristic_id', null));

		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$characteristics = null;
		if(isset($typeConfig['params']['value']) && $typeConfig['params']['value']) {
			if((int)$options['url_params']['ID'] > 0) {
				$query = 'SELECT characteristic_value, characteristic_id, characteristic_alias FROM ' . hikamarket::table('shop.characteristic').' WHERE characteristic_parent_id = ' . (int)$options['url_params']['ID'];
				if(!empty($options['vendor']))
					$query .= ' AND characteristic_vendor_id IN (0, '.(int)$options['vendor'].')';
				if(!empty($search))
					$query .= ' AND characteristic_value LIKE \'%' . hikamarket::getEscaped($search) . '%\'';
				$this->db->setQuery($query);
				$characteristics = $this->db->loadObjectList('characteristic_id');
			}

			if(!empty($value)) {

			}
		} else {

			$query = 'SELECT characteristic_value, characteristic_id, characteristic_alias FROM ' . hikamarket::table('shop.characteristic').' WHERE characteristic_parent_id = 0';
			if(!empty($options['vendor']))
				$query .= ' AND characteristic_vendor_id IN (0, '.(int)$options['vendor'].')';
			$this->db->setQuery($query);
			$characteristics = $this->db->loadObjectList('characteristic_id');

			if(!empty($value)) {

			}
		}

		if(!empty($characteristics)) {
			foreach($characteristics as $k => $v) {
				$ret[0][$k] = $v;
			}
			asort($ret[0]);
		}
		unset($characteristics);

		return $ret;
	}

}
