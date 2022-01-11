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
class hikamarketFieldClass extends hikamarketClass {

	protected $tables = array();
	protected $pkeys = array();
	protected $toggle = array();

	protected $fields = array(
		'vendor' => array(
			'struct' => array(
				'text' => 'HIKAMARKET_VENDOR',
				'table' => 'vendor',
				'id' => 'vendor_id',
				'columns' => array('vendor_params')
			),
			'display' => array(
				array(
					'name' => 'vendor_page',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_PAGE'
				),
				array(
					'name' => 'vendor_registration',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_REGISTRATION'
				),
				array(
					'name' => 'vendor_listing',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_LISTING'
				),
				array(
					'name' => 'vendor_select',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_FRONT_SELECT'
				)
			)
		),
		'shop.product' => array(
			'display' => array(
				array(
					'name' => 'vendor_product_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_PRODUCT_EDITION',
					'init' => 'backend'
				),
				array(
					'name' => 'vendor_product_listing',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_PRODUCT_LISTING'
				)
			)
		),
		'shop.category' => array(
			'display' => array(
				array(
					'name' => 'vendor_category_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_CATEGORY_EDITION',
					'init' => 'backend'
				),
				array(
					'name' => 'vendor_category_listing',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_CATEGORY_LISTING'
				)
			)
		),
		'shop.order' => array(
			'display' => array(
				array(
					'name' => 'vendor_order_show',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_SHOW',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_order_invoice',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_INVOICE',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_order_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_EDITION',
					'init' => 'backend'
				),
				array(
					'name' => 'vendor_order_listing',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_LISTING',
					'init' => 'backend_listing'
				)
			)
		),
		'shop.item' => array(
			'display' => array(
				array(
					'name' => 'vendor_order_show',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_SHOW',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_order_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_EDITION',
					'init' => 'backend'
				),
				array(
					'name' => 'vendor_order_invoice',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_INVOICE',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_order_shipping_invoice',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_SHIPPING_INVOICE',
					'init' => 'frontend'
				)
			)
		),
		'shop.entry' => array(
			'display' => array(
				array(
					'name' => 'vendor_order_show',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_SHOW',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_order_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_EDITION',
					'init' => 'backend'
				)
			)
		),
		'shop.address' => array(
			'display' => array(
				array(
					'name' => 'vendor_order_show',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_SHOW',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_order_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_ORDER_EDITION',
					'init' => 'backend'
				),
				array(
					'name' => 'vendor_user_show',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_USER_SHOW',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_user_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_USER_EDIT',
					'init' => 'backend'
				)
			)
		),
		'shop.user' => array(
			'display' => array(
				array(
					'name' => 'vendor_user_show',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_USER_SHOW',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_user_edit',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_USER_EDIT',
					'init' => 'backend'
				),
				array(
					'name' => 'vendor_registration',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_REGISTRATION',
					'init' => 'frontend'
				),
				array(
					'name' => 'vendor_user_listing',
					'title' => 'HIKAM_FIELD_DISPLAY_VENDOR_USER_LIST'
				)
			)
		)
	);

	public function tableFieldsLoad(&$values) {
		foreach($this->fields as $key => $field) {
			if(empty($field['struct']))
				continue;

			$table = new stdClass();
			$table->value = 'plg.hikamarket.' . $key;
			$table->datatype = $key;
			$table->prefix = 'hikamarket_';
			$table->text = JText::_($field['struct']['text']);
			$table->table = hikamarket::table($field['struct']['table']);
			$table->arrayColumns = $field['struct']['columns'];

			$values['hikamarket.'.$key] = $table;
		}
	}

	public function customfieldEdit(&$field, &$view) {
		if(empty($field->field_table))
			return;
		$key = 'shop.' . $field->field_table;
		if(substr($field->field_table, 0, 15) === 'plg.hikamarket.')
			$key = substr($field->field_table, 15);

		if(!isset($this->fields[$key]))
			return;

		if(!empty($field->field_display) && is_string($field->field_display)) {
			$fields_display = explode(';', trim($field->field_display, ';'));
			$field->field_display = new stdClass();
			foreach($fields_display as $f) {
				list($k,$v) = explode('=', $f, 2);
				$field->field_display->$k = $v;
			}
		}

		if(!empty($this->fields[$key]['display'])) {
			if(empty($view->displayOptions))
				$view->displayOptions = array();
			foreach($this->fields[$key]['display'] as $opt) {
				$opt['title'] = JText::_($opt['title']);
				$opt['group'] = 'HikaMarket';
				$view->displayOptions[] = $opt;
			}
		}
	}

	public function fieldFileDownload(&$found, $name, $field_table, $field_namekey, $options) {
		if(substr($field_table, 0, 15) != 'plg.hikamarket.')
			return $this->shopfieldFileDownload($found, $name, $field_table, $field_namekey, $options);
		$table = substr($field_table, 15);

		$query = '';
		foreach($this->fields as $key => $field) {
			if(empty($field['struct']))
				continue;
			if($key == $table) {
				$query = 'SELECT '.$field['id'].' FROM '.$field['table'].' WHERE '.$field_namekey . ' = '. $this->db->Quote($name);
			}
		}

		if(!empty($query)) {
			$this->db->setQuery($query);
			$result = $this->db->loadResult();
			if($result)
				$found = true;
		}
	}

	protected function shopfieldFileDownload(&$found, $name, $field_table, $field_namekey, $options) {
		if(!in_array($field_table, array('order','item')))
			return;
		$vendor_id = hikamarket::loadVendor(false, false);
		if(empty($vendor_id))
			return;

		if(!hikamarket::acl('order/show/customfield') && !hikamarket::acl('order/edit/customfield'))
			return;

		$escaped_field_namekey = $this->db->quoteName($field_namekey);
		if($vendor_id > 1) {
			if($field_table == 'order') {
				$query = 'SELECT order_id FROM ' . hikamarket::table('shop.order') .
					' WHERE (order_type = ' . $this->db->Quote('subsale').') AND (order_vendor_id = ' . $vendor_id . ') AND (' . $escaped_field_namekey . ' = ' . $this->db->Quote($name) . ')';
			} else {
				$query = 'SELECT order_product_id FROM '.hikamarket::table('shop.order').' AS hk_order '.
					' LEFT JOIN '.hikashop_table('order_product').' AS hk_order_product ON hk_order.order_id = hk_order_product.order_id '.
					' WHERE (hk_order.order_type = ' . $this->db->Quote('subsale').') AND (hk_order.order_vendor_id = ' . $vendor_id . ') AND (hk_order_product.'.$escaped_field_namekey.' = '.$this->db->Quote($name).')';
			}
		} else {
			if($field_table == 'order') {
				$query = 'SELECT order_id FROM ' . hikamarket::table('shop.order').
					' WHERE (order_type = ' . $this->db->Quote('sale') . ') AND (' . $escaped_field_namekey . ' = ' . $this->db->Quote($name) . ')';
			} else {
				$query = 'SELECT order_product_id FROM '.hikamarket::table('shop.order').' AS hk_order '.
					' LEFT JOIN '.hikashop_table('order_product').' AS hk_order_product ON hk_order.order_id = hk_order_product.order_id '.
					' WHERE (hk_order.order_type = ' . $this->db->Quote('sale') . ') AND (hk_order_product.'.$escaped_field_namekey.' = '.$this->db->Quote($name).')';
			}
		}

		$this->db->setQuery($query);
		$result = $this->db->loadResult();
		if(empty($result))
			return;

		$searchVal = '\'%' . $this->db->escape(';vendor_order_show=1;', true) . '%\'';

		if($field_table == 'order') {
			$query = 'SELECT field_id FROM ' . hikamarket::table('shop.field') .
				' WHERE (field_table = ' . $this->db->Quote('order') . ') AND (field_namekey = ' . $this->db->Quote($field_namekey) . ') AND (field_display LIKE ' . $searchVal . ')';
		} else {
			$query = 'SELECT field_id FROM ' . hikamarket::table('shop.field') .
				' WHERE (field_table = ' . $this->db->Quote('item') . ') AND (field_namekey = ' . $this->db->Quote($field_namekey) . ') AND (field_display LIKE ' . $searchVal . ')';
		}
		$this->db->setQuery($query);
		$result = $this->db->loadResult();
		if(empty($result))
			return;

		$found = true;
	}

	public function getFilteredInput($type, &$oldData, $report = true, $varname = 'data', $force = false, $area = '') {
		$shopFieldClass = hikamarket::get('shop.class.field');

		$allCat = $shopFieldClass->getCategories($type, $oldData);
		$fields =& $shopFieldClass->getData($area, $type, false, $allCat);
		$data = $shopFieldClass->getInput($type, $oldData, $report, $varname, $force, $area);

		if(!$data)
			return $data;

		if($type == 'entry' && $area == 'frontcomp') {
			$ret = array();
			foreach($data as $key => $d) {
				$r = new stdClass();
				foreach($fields as $fieldname => $field) {
					if(isset($d->$fieldname))
						$r->$fieldname = $d->$fieldname;
				}
				$ret[$key] = $r;
			}
		} else {
			$ret = new stdClass();
			foreach($fields as $fieldname => $field) {
				if(isset($data->$fieldname))
					$ret->$fieldname = $data->$fieldname;
			}
			if(!empty($oldData)) {
				foreach($oldData as $key => $value) {
					if(!isset($data->$key))
						$data->$key = $value;
				}
			}
		}
		return $ret;
	}

	public function initFields() {
		$query = 'SELECT * FROM '.hikamarket::table('shop.field');
		$this->db->setQuery($query);
		$fields = $this->db->loadObjectList();

		$updateData = array();
		foreach($fields as $field) {
			if(empty($field->field_display)) {
				$table = $field->field_table;
				if(strpos($table, '.') === false) {
					$table = 'shop.' . $table;
				} else {
					$table = str_replace('plg.hikamarket.', '', $table);
				}
				if(isset($this->fields[$table])) {
					$display_data = array();
					foreach($this->fields[$table]['display'] as $display) {
						if(!empty($display['init'])) {
							if($display['init'] == 'backend') {
								$display_data[] = $display['name'].'='.$field->field_backend;
							} else {
								$display_data[] = $display['name'].'='.$field->field_frontcomp;
							}
						} else {
							$display_data[] = $display['name'].'=0';
						}
					}
					$updateData[$field->field_id] = ';'.implode(';',$display_data).';';
				}
			}
		}
		foreach($updateData as $k => $v) {
			$query = 'UPDATE '.hikamarket::table('shop.field').' SET field_display = '.$this->db->Quote($v).' WHERE field_id = '.(int)$k;
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}
}
