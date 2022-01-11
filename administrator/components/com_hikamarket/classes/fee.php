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
class hikamarketFeeClass extends hikamarketClass {

	protected $tables = array('fee');
	protected $pkeys = array('fee_id');
	protected $toggle = array();

	public function saveForm($type, $target_id, &$formData) {

		if(!in_array($type, array('config', 'product', 'vendor'))) {
			return false;
		}

		if((int)$target_id == 0 && $type != 'config')
			return false;
		if((int)$target_id <= 1 && $type == 'vendor')
			return false;
		if($type == 'config')
			$target_id = 0;

		if(empty($formData)) {
			$query = 'DELETE FROM ' . hikamarket::table('fee') . ' WHERE (fee_type = \''.$type.'\' OR fee_type = \''.$type.'_global\') AND fee_target_id = ' . (int)$target_id;
			$this->db->setQuery($query);
			$this->db->execute();

			return true;
		}

		$fee_ids = array();

		foreach($formData as $fee) {
			if(!empty($fee['id'])) {
				$fee_ids[] = $fee['id'];
			}
		}
		$query = 'DELETE FROM ' . hikamarket::table('fee') . ' WHERE (fee_type = \''.$type.'\' OR fee_type = \''.$type.'_global\') AND fee_target_id = ' . (int)$target_id;
		if(!empty($fee_ids)) {
			$query = 'DELETE FROM ' . hikamarket::table('fee') . ' WHERE (fee_type = \''.$type.'\' OR fee_type = \''.$type.'_global\') AND fee_target_id = ' . (int)$target_id . ' AND fee_id NOT IN (' . implode(',', $fee_ids) . ')';
		}
		$this->db->setQuery($query);
		$this->db->execute();

		$data = array();
		foreach($formData as $fee) {
			if(empty($fee['currency']) || (int)$fee['currency'] == 0)
				continue;
			if(isset($fee['percent']) && $fee['percent'] == '' && isset($fee['value']) && $fee['value'] == '')
				continue;

			$fee_type = $type;
			if(!empty($fee['global']) && ($type == 'vendor' || $type == 'config'))
				$fee_type = $type . '_global';

			if(empty($fee['quantity']) || (int)$fee['quantity'] <= 0)
				$fee['quantity'] = 1;
			if(empty($fee['percent']))
				$fee['percent'] = 0;
			else
				$fee['percent'] = round((float)$fee['percent'], 2);
			if(empty($fee['fixed']))
				$fee['fixed'] = 0;
			else
				$fee['fixed'] = round((float)$fee['fixed'], 2);
			if(empty($fee['min_price']))
				$fee['min_price'] = 0;
			else
				$fee['min_price'] = round((float)$fee['min_price'], 2);
			if(empty($fee['value']))
				$fee['value'] = 0;

			if($type != 'config')
				$fee['group'] = 0;
			else
				$fee['group'] = (int)@$fee['group'];

			if(empty($fee['id'])) {
				$data[] = '\''.$fee_type.'\',' . (int)$target_id . ',' . (int)$fee['currency'] . ',' . (float)$fee['value'] . ',' . (float)$fee['percent'] . ',' . (int)$fee['quantity'] . ',' . (float)$fee['min_price'] . ',' . (float)$fee['fixed'] . ',' . (int)$fee['group'];
			} else {
				$query = 'UPDATE ' . hikamarket::table('fee') . ' SET '.
						' fee_currency_id=' . (int)$fee['currency'] . ','.
						' fee_value=' . (float)$fee['value'] . ','.
						' fee_percent=' . (float)$fee['percent'] . ','.
						' fee_min_quantity=' . (int)$fee['quantity'] . ','.
						' fee_min_price=' . (float)$fee['min_price'] . ','.
						' fee_fixed=' . (float)$fee['fixed'] . ','.
						' fee_group=' . (float)$fee['group'] . ','.
						' fee_type=\'' . $fee_type . '\'' .
						' WHERE fee_id = ' . (int)$fee['id'] . ' AND (fee_type = \''.$type.'\' OR fee_type = \''.$type.'_global\') AND fee_target_id =' . (int)$target_id;
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}
		if(!empty($data)) {
			$query = 'INSERT IGNORE INTO ' . hikamarket::table('fee') . ' (`fee_type`, `fee_target_id`, `fee_currency_id`, `fee_value`, `fee_percent`, `fee_min_quantity`, `fee_min_price`, `fee_fixed`, `fee_group`) VALUES (' . implode('),(', $data). ')';
			$this->db->setQuery($query);
			$this->db->execute();
		}

		return true;
	}

	public function delete(&$elements) {
		return false;
	}

	public function getConfig() {
		$query = 'SELECT a.* '.
				' FROM ' . hikamarket::table('fee') . ' AS a '.
				' WHERE (a.fee_type = \'config\' OR a.fee_type = \'config_global\')';
		$this->db->setQuery($query);
		$fees = $this->db->loadObjectList();
		return $fees;
	}

	public function saveConfig(&$formData) {
		return $this->saveForm('config', 0, $formData);
	}

	public function getVendor($vendor, $config = false) {
		$filter = '';
		if(is_array($vendor)) {
			if(is_object(reset($vendor))) {
				$ids = array();
				foreach($vendor as $v) {
					$ids[] = $v->vendor_id;
				}
				$filter = 'AND a.fee_target_id IN (' . implode(',', $ids) . ')';
			} else {
				$filter = 'AND a.fee_target_id IN (' . implode(',', $vendor) . ')';
			}
		} else {
			if(is_object($vendor)) {
				$filter = 'AND a.fee_target_id = ' . (int)$vendor->vendor_id;
			} else {
				$filter = 'AND a.fee_target_id = ' . (int)$vendor;
			}
		}

		$configFilter = '';
		if($config) {
			if(is_int($vendor)) {
				$query = 'SELECT vendor_id, vendor_access FROM '.hikamarket::table('vendor').' WHERE vendor_id = '.(int)$vendor;
				$this->db->setQuery($query);
				$v = $this->db->loadObject();
				$vendor_access = $v->vendor_access;
			} else if(is_object($vendor)) {
				$vendor_access = $vendor->vendor_access;
			}
			if(!empty($vendor_access)) {
				$joomlaaclType = hikamarket::get('type.joomla_acl');
				$tree_groups = $joomlaaclType->getParentList();

				if(is_array($vendor_access))
					$vendor_access = implode(',', $vendor_access);
				$vendor_access = str_replace('@0,', '', trim($vendor_access,',').',');
				$groups = array();
				if(strpos($vendor_access, '@') !== false) {
					$vendor_access = explode(',', trim($vendor_access,','));
					foreach($vendor_access as $a) {
						if(substr($a, 0, 1) != '@')
							continue;
						$i = (int)substr($a, 1);
						if(!isset($groups[$i])) {
							$groups[$i] = $i;
							foreach($tree_groups[$i]['parents'] as $c) {
								if(!empty($c))
									$groups[$c] = (int)$c;
							}
						}
					}
					$groups[0] = 0;
				}
				unset($vendor_access);
				$vendor_access = $groups;
			}

			if(empty($vendor_access))
				$configFilter = ' OR (a.fee_type = \'config\' OR a.fee_type = \'config_global\') ';
			else
				$configFilter = ' OR ((a.fee_type = \'config\' OR a.fee_type = \'config_global\') AND fee_group IN ('.implode(',', $vendor_access).')) ';
		}

		$query = 'SELECT a.* '.
				' FROM ' . hikamarket::table('fee') . ' AS a '.
				' WHERE ((a.fee_type = \'vendor\' OR a.fee_type = \'vendor_global\')'.$filter.') '.$configFilter;
		$this->db->setQuery($query);
		$fees = $this->db->loadObjectList();
		return $fees;
	}

	public function saveVendor($vendor_id, &$formData) {
		return $this->saveForm('vendor', $vendor_id, $formData);
	}

	public function getProduct($product, $vendor = false) {
		$filter = '1';
		if(is_array($product)) {
			if(is_object(reset($product))) {
				$ids = array();
				foreach($product as $p) {
					$ids[] = $p->product_id;
					if($p->product_type == 'variant' && !empty($p->product_parent_id))
						$ids[] = (int)$p->product_parent_id;
				}
				$filter = 'hkp.product_id IN (' . implode(',', $ids) . ')';
			} else {
				$filter = 'hkp.product_id IN (' . implode(',', $product) . ')';
			}
		} else {
			if(is_object($product)) {
				$filter = 'hkp.product_id = ' . (int)$product->product_id;
				if($product->product_type == 'variant' && !empty($product->product_parent_id))
					$filter = 'hkp.product_id IN (' . (int)$product->product_id . ',' . (int)$p->product_parent_id . ')';
			} else {
				$filter = 'hkp.product_id = ' . (int)$product;
			}
		}

		$vendorFilter = array();
		if($vendor) {
			if(is_array($vendor)) {
				$vendorFilter[] = 'OR ((hkm_fee.fee_type = \'vendor\' OR hkm_fee.fee_type = \'vendor_global\') AND (hkp.product_vendor_id = hkm_fee.fee_target_id OR hkm_fee.fee_target_id IN ('.implode(',',$vendor).')))';
			} else {
				$vendorFilter[] = 'OR ((hkm_fee.fee_type = \'vendor\' OR hkm_fee.fee_type = \'vendor_global\') AND (hkp.product_vendor_id = hkm_fee.fee_target_id))';
			}
			$vendorFilter[] = 'OR (hkm_fee.fee_type = \'config\')';
			$vendorFilter[] = 'OR (hkm_fee.fee_type = \'config_global\')';
		}

		$query = 'SELECT hkm_fee.*, hkp.product_id, hkp.product_parent_id '.
				' FROM '. hikamarket::table('fee') . ' AS hkm_fee '.
				' LEFT JOIN ' . hikamarket::table('shop.product') . ' AS hkp ON '.
				' (hkm_fee.fee_type = \'product\' AND (hkp.product_id = hkm_fee.fee_target_id OR (hkp.product_parent_id > 0 AND hkp.product_parent_id = hkm_fee.fee_target_id))) '. implode(' ',$vendorFilter).
				' WHERE '.$filter.
				' ORDER BY hkm_fee.fee_min_quantity ASC, hkm_fee.fee_currency_id ASC';

		$this->db->setQuery($query);
		$fees = $this->db->loadObjectList('fee_id');

		return $fees;
	}

	public function getProducts(&$products, $vendor = false) {
		$product_ids = array();
		foreach($products as $product) {
			$product_ids[] = $product['id'];
			if(!empty($product['parent']))
				$product_ids[] = $product['parent'];
		}
		$product_ids = array_unique($product_ids);
		$fees = $this->getProduct($product_ids, $vendor);
		unset($product_ids);

		$vendor_groups = array();
		foreach($fees as &$fee) {
			$fee->fee_id = (int)$fee->fee_id;
			$fee->fee_target_id = (int)$fee->fee_target_id;
			$fee->fee_currency_id = (int)$fee->fee_currency_id;
			$fee->fee_min_quantity = (int)$fee->fee_min_quantity;
			$fee->product_id = (int)$fee->product_id;
			$fee->product_parent_id = (int)$fee->product_parent_id;
			$fee->fee_value = (float)hikamarket::toFloat($fee->fee_value);
			$fee->fee_percent = (float)hikamarket::toFloat($fee->fee_percent);
			$fee->fee_min_price = (float)hikamarket::toFloat($fee->fee_min_price);
			$fee->fee_fixed = (float)hikamarket::toFloat($fee->fee_fixed);

			$fee->fee_group = (int)$fee->fee_group;
			if($fee->fee_type != 'config' && $fee->fee_type != 'config_global')
				$fee->fee_group = 0;
			else
				$vendor_groups[$fee->fee_group] = $fee->fee_group;
		}
		unset($fee);

		$vendors = null;
		if(!empty($vendor_groups) && $vendor !== false) {
			$vendor_ids = is_array($vendor) ? $vendor : array($vendor => $vendor);
			$query = 'SELECT vendor_id, vendor_access FROM '.hikamarket::table('vendor').' WHERE vendor_id IN ('.implode(',',$vendor_ids).')';
			$this->db->setQuery($query);
			$vendors = $this->db->loadObjectList('vendor_id');

			$joomlaaclType = hikamarket::get('type.joomla_acl');
			$tree_groups = $joomlaaclType->getParentList();

			foreach($vendors as &$v) {
				$v->vendor_access = str_replace('@0,', '', trim($v->vendor_access,',').',');
				$v->groups = array();
				if(strpos($v->vendor_access, '@') !== false) {
					$v->vendor_access = explode(',', trim($v->vendor_access,','));
					foreach($v->vendor_access as $a) {
						if(substr($a, 0, 1) != '@')
							continue;
						$i = (int)substr($a, 1);
						if(!isset($v->groups[$i])) {
							$v->groups[$i] = $i;
							foreach($tree_groups[$i]['parents'] as $c) {
								if(!empty($c))
									$v->groups[$c] = $c;
							}
						}
					}
				}
			}
			unset($v);
		}

		$config = hikamarket::config();
		$load_all_fees = (int)$config->get('get_best_fee', 1);

		foreach($products as &$product) {
			$public_fees = array();
			foreach($fees as $fee) {
				if($fee->fee_type == 'product' && ((int)$fee->fee_target_id == $product['id']) || ($product['parent'] > 0 && (int)$fee->fee_target_id == $product['parent'])) {
					$product['fee'][] = $fee;
				}
				if((($fee->fee_type == 'vendor' || $fee->fee_type == 'vendor_global') && (int)$fee->fee_target_id == (int)$product['vendor'])) {
					$product['fee'][] = $fee;
				}
				if($fee->fee_type == 'config' || $fee->fee_type == 'config_global') {
					if($vendors == null) {
						$product['fee'][] = $fee;
					} elseif(isset($vendors[(int)$product['vendor']])) {
						$v = $vendors[(int)$product['vendor']];
						if((empty($v->groups) && $fee->fee_group == 0) || isset($v->groups[$fee->fee_group])) {
							$product['fee'][] = $fee;
						}
					}
				}
			}
			if(empty($product['fee']))
				$product['fee'] = $public_fees;
		}

		unset($product);

		return $fees;
	}

	public function saveProduct($product_id, &$formData) {
		return $this->saveForm('product', $product_id, $formData);
	}
}
