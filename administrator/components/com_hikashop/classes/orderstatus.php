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
class hikashopOrderstatusClass extends hikashopClass {
	public $tables = array('orderstatus');
	public $pkeys = array('orderstatus_id');
	public $toggle = array('orderstatus_published' => 'orderstatus_id');

	public function get($element, $default = null) {
		$ret = parent::get($element, $default);

		return $ret;
	}

	public function save(&$element) {
		$new = empty($element->orderstatus_id);

		if(empty($element->old) && !empty($element->orderstatus_id))
			$element->old = $this->get($element->orderstatus_id);
		if(isset($element->old) && $element->old === false)
			unset($element->old);

		if(!$new)
			unset($element->orderstatus_namekey);

		if($new && empty($element->orderstatus_namekey))
			return false;

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		if($new)
			$app->triggerEvent('onBeforeOrderstatusCreate', array( &$element, &$do) );
		else
			$app->triggerEvent('onBeforeOrderstatusUpdate', array( &$element, &$do) );

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return false;

		if($new)
			$app->triggerEvent('onAfterOrderstatusCreate', array( &$element ) );
		else
			$app->triggerEvent('onAfterOrderstatusUpdate', array( &$element ) );

		return $status;
	}

	public function saveForm() {
		$orderstatus_id = hikashop_getCID('orderstatus_id');
		$oldOrderstatus = null;
		if(!empty($orderstatus_id))
			$oldOrderstatus = $this->get($orderstatus_id);

		$fieldsClass = hikashop_get('class.field');
		$element = $fieldsClass->getInput('orderstatus', $oldOrderstatus);
		if(empty($element))
			return false;

		$element->orderstatus_description = hikaInput::get()->getRaw('orderstatus_description', '');
		if(!empty($oldOrderstatus) && $oldOrderstatus !== false)
			$element->old = $oldOrderstatus;

		if(empty($element->orderstatus_id) && empty($element->orderstatus_namekey)) {
			$element->orderstatus_namekey = $element->orderstatus_name;
		}
		if(!empty($element->orderstatus_namekey)) {
			$element->orderstatus_namekey = preg_replace(
				'#[^a-z0-9_]#i',
				'',
				strtolower(trim($element->orderstatus_namekey))
			);

			if(empty($element->orderstatus_namekey)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage('Please enter a namekey in English.', 'error');
				hikaInput::get()->set('fail', $element);
				return false;
			}
		}

		$status = $this->save($element);

		if($status) {

		} else {
			hikaInput::get()->set('fail', $element);
		}
		return $status;
	}

	public function getList($filters = array(), $options = array()) {
		$query = 'SELECT orderstatus.* '.
			' FROM ' .  hikashop_table('orderstatus') . ' AS orderstatus '.
			' WHERE orderstatus.orderstatus_published = 1 '.
			' ORDER BY orderstatus.orderstatus_ordering';
		$this->db->setQuery($query);
		$ret = $this->db->loadObjectList('orderstatus_namekey');

		if(!empty($options['legacy'])) {
			foreach($ret as &$r) {
				$r->category_name = $r->orderstatus_namekey;
				$r->translation = $r->orderstatus_name;
			}
		}
		return $ret;
	}

	function delete(&$elements) {
		if(!is_array($elements))
			$elements = array($elements);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeOrderstatusDelete', array(&$elements, &$do));
		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterOrderstatusDelete', array(&$elements));
			return count($elements);
		}
		return $status;
	}


	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$start = (int)@$options['start']; // TODO
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		if($limit <= 0)
			$limit = 50;

		$select = array('o.*');
		$table = array(hikashop_table('orderstatus').' AS o');
		$where = array('o.orderstatus_published = 1');

		if(!empty($search)) {
			$searchStr = "'%" . ((HIKASHOP_J30) ? $this->db->escape($search, true) : $this->db->getEscaped($search, true) ) . "%'";
			$where[] = '(o.orderstatus_name LIKE ' . $searchStr . ' OR o.orderstatus_namekey LIKE ' . $searchStr . ')';
		}

		$order = ' ORDER BY o.orderstatus_ordering ASC, o.orderstatus_name ASC';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' WHERE ' . implode(' AND ', $where).$order;
		$this->db->setQuery($query, $page, $limit);

		$orderstatuses = $this->db->loadObjectList('orderstatus_id');

		if(count($orderstatuses) < $limit)
			$fullLoad = true;

		foreach($orderstatuses as $orderstatus) {
			if(!empty($orderstatus->orderstatus_name))
				$ret[0][$orderstatus->orderstatus_namekey] = hikashop_orderStatus($orderstatus->orderstatus_name);
			else
				$ret[0][$orderstatus->orderstatus_namekey] = hikashop_orderStatus($orderstatus->orderstatus_namekey);
		}

		if(!empty($value)) {
			if($mode == hikashopNameboxType::NAMEBOX_SINGLE && isset($ret[0][$value])) {
				$ret[1][$value] = $ret[0][$value];
			} elseif($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && is_array($value)) {
				foreach($value as $v) {
					if(isset($ret[0][$v])) {
						$ret[1][$v] = $ret[0][$v];
					}
				}
			}
		}
		return $ret;
	}
}
