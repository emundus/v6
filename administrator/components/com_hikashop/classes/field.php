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

class hikashopFieldClass extends hikashopClass {

	var $tables = array('field');
	var $pkeys = array('field_id');
	var $namekeys = array();
	var $errors = array();
	var $prefix = '';
	var $suffix = '';
	var $excludeValue = array();
	var $toggle = array('field_required'=>'field_id','field_published'=>'field_id','field_backend'=>'field_id','field_backend_listing'=>'field_id','field_frontcomp'=>'field_id','field_core'=>'field_id');
	var $where = array();
	var $skipAddressName=false;
	var $report = true;
	var $messages = array();
	var $externalValues = null;
	var $regexs = array();

	function &getData($area, $type, $notcoreonly = false, $categories = null) {
		static $data = array();
		if(is_array($area))
			return $area;
		if(is_string($area) && $area == 'reset') {
			$data = array();
			return $area;
		}
		$key = $area.'_'.$type.'_'.$notcoreonly;
		if(!empty($categories)) {
			if(!empty($categories['originals']))
				$key .= '_' . implode('/', $categories['originals']);

			if(!empty($categories['parents']))
				$key .= '_' . implode('/', $categories['parents']);
		}
		if(!empty($categories['products'])){
			$key.='_'.implode('/',$categories['products']);
		}
		if(!empty($categories['ids'])){
			$key.='_'.implode('/',$categories['ids']);
		}
		if(!empty($categories['payment'])){
			$key.='_'.$categories['payment'];
		}
		if(!empty($categories['shipping'])){
			if(is_array($categories['shipping']))
				$key.='_'.implode('/',$categories['shipping']);
			else
				$key.='_'.$categories['shipping'];
		}
		if(isset($data[$key]))
			return $data[$key];

		$this->where = array(
			'a.`field_published` = 1'
		);
		if($area == 'backend') {
			$this->where[] = 'a.`field_backend` = 1';
		} elseif($area == 'frontcomp') {
			$this->where[] = 'a.`field_frontcomp` = 1';
		} elseif($area == 'backend_listing') {
			$this->where[] = 'a.`field_backend_listing` = 1';
		} elseif($area != 'all') {
			$clauses = explode(';', trim($area,';'));
			foreach($clauses as $clause) {
				if(empty($clause))
					continue;

				$v = '=1';
				if(strpos($clause, '=') !== false) {
					list($clause,$v) = explode('=', $clause, 2);
					$v = '=' . (int)$v;
				}
				if(substr($clause, 0, 8) == 'display:') {
					$cond = substr($clause, 8) . $v;
					$cond = $this->database->escape($cond, true);
					$this->where[] = 'a.`field_display` LIKE \'%;'.$cond.';%\'';
				} else {
					$this->where[] = 'a.' . $this->database->quoteName($clause) . $v;
				}
			}
		}

		if($notcoreonly) {
			$this->where[] = 'a.`field_core` = 0';
		}
		if($this->skipAddressName) {
			$this->where[] = 'a.field_namekey!=\'address_name\'';
		}

		if(in_array($type, array('shipping_address', 'billing_address'))) {
			$address_type = 'billing';
			if($type == 'shipping_address')
				$address_type = 'shipping';
			$type = 'address';
			$this->where[] = '(a.field_address_type='.$this->database->Quote($address_type) . ' OR a.field_address_type=\'\')';
		}
		if($type == 'variant')
			$type = 'product';
		$this->where[] = 'a.field_table='.$this->database->Quote($type);

		if(!empty($categories['ids']) && is_array($categories['ids']) && count($categories['ids'])){
			hikashop_toInteger($categories['ids']);
			$this->where[] =  'a.field_id IN ('.implode(',', $categories['ids']).')';
		}

		if(!empty($categories['shipping']) && is_array($categories['shipping']) && count($categories['shipping'])){
			hikashop_toInteger($categories['shipping']);
			$shipping_filter = array('a.field_shipping_id = ""');
			foreach($categories['shipping'] as $shipping_id) {
				$shipping_filter[] = 'a.field_shipping_id LIKE \'%,'.(int)$shipping_id.',%\'';
			}
			$this->where[] = '('.implode(' OR ', $shipping_filter).')';

		} elseif(@$categories['shipping'] === true){
			$this->where[] = 'a.field_shipping_id = ""';
		}
		if(!empty($categories['payment']) && $categories['payment'] !== true){
			$this->where[] = '( a.field_payment_id = "" OR a.field_payment_id LIKE \'%,'.(int)$categories['payment'].',%\' )';
		} elseif(@$categories['payment'] === true){
			$this->where[] = 'a.field_payment_id = ""';
		}

		$filters = '';
		if(!empty($categories) && (!empty($categories['originals']) || !empty($categories['parents']))) {
			$categories_filter = array('((field_with_sub_categories = 0 AND (field_categories = "all" OR field_categories = ""');
			if(!empty($categories['originals'])) {
				foreach($categories['originals'] as $cat) {
					$categories_filter[]='field_categories LIKE \'%,'.(int)$cat.',%\'';
				}
			}
			$filters = implode(' OR ',$categories_filter).'))';
			$categories_filter = array('OR (field_with_sub_categories = 1 AND (field_categories = "all" OR field_categories = ""');
			if(!empty($categories['parents'])) {
				foreach($categories['parents'] as $cat) {
					$categories_filter[] = 'field_categories LIKE \'%,'.(int)$cat.',%\'';
				}
			}
			$filters .= implode(' OR ',$categories_filter).')))';
		}
		if(!empty($categories['products']) && is_array($categories['products']) && count($categories['products'])){
			$products_filter = array();
			foreach($categories['products'] as $p){
				$products_filter[]='field_products LIKE \'%,'.(int)$p.',%\'';
			}
			if(empty($filters))
				$filters = '(field_products="" OR '.implode(' OR ',$products_filter).')';
			else
				$filters = '(('.$filters.' AND field_products="") OR ('.implode(' OR ',$products_filter).'))';
		}
		if(!empty($filters))
			$filters = ' AND '.$filters;

		hikashop_addACLFilters($this->where,'field_access','a');

		$query = 'SELECT * FROM '.hikashop_table('field').' as a WHERE '.implode(' AND ',$this->where).' '.$filters.' ORDER BY a.`field_ordering` ASC';
		$this->database->setQuery($query);
		$data[$key] = $this->database->loadObjectList('field_namekey');

		return $data[$key];
	}

	function getField($fieldid,$type=''){
		if(is_numeric($fieldid)){
			$element = parent::get($fieldid);
		}else{
			$this->database->setQuery('SELECT * FROM '.hikashop_table('field').' WHERE field_table='.$this->database->Quote($type).' AND field_namekey='.$this->database->Quote($fieldid));
			$element = $this->database->loadObject();
		}
		if(empty($element))
			return false;
		$fields = array($element);
		$data = null;
		$this->prepareFields($fields,$data,$fields[0]->field_type,'',true);
		return $fields[0];
	}

	function getFields($area, &$data, $type = 'user', $url = 'checkout&task=state') {
		$allCat = $this->getCategories($type, $data);
		$fields = $this->getData($area, $type, false, $allCat);

		if($type == 'item' && !empty($fields)) {
			$this->populateItemFieldValues($fields, $data);
		}

		$this->prepareFields($fields, $data, $type, $url);
		return $fields;
	}

	function populateItemFieldValues(&$fields, &$data) {
		$checkProductFields = array();
		foreach($fields as $itemKey => $itemField) {
			if(!in_array($itemField->field_type, array('radio', 'checkbox', 'singledropdown', 'multidropdown')) || !empty($itemField->field_value))
				continue;
			$checkProductFields[] = $itemKey;
		}
		if(empty($checkProductFields))
			return;

		$config = hikashop_config();
		$not_readeable_object = is_array($data) || !isset($data->product_type);
		$empty_product_field_values_means_all = (int)$config->get('empty_product_field_values_means_all', 1) || $not_readeable_object;

		$null = null;
		$productFields = $this->getData('backend', 'product');
		foreach($checkProductFields as $key) {
			$product_key = $key.'_values';
			if(!isset($productFields[$product_key]))
				continue;
			$productField = $productFields[$product_key];

			if(($not_readeable_object || empty($data->$product_key)) && $empty_product_field_values_means_all) {
				$fields[$key]->field_value = $productField->field_value;
				$fields[$key]->field_value_all = true;
			} elseif(empty($data->$product_key)) {
				unset($fields[$key]);
			} else {
				$product_data = explode(',', (string)$data->$product_key);
				$field_data = explode("\n", $productField->field_value);
				$item_data = array();
				foreach($field_data as $fd) {
					list($k,$v) = explode('::', $fd, 2);
					if(in_array($k, $product_data))
						$item_data[] = $fd;
				}
				$fields[$key]->field_value = implode("\n", $item_data);
			}
		}
		unset($productFields);
	}

	function getCategories($type, &$data) {
		$allCat = null;
		if(empty($data))
			return $allCat;

		if(in_array($type, array('product', 'item', 'contact', 'variant'))) {
			if(is_array($data) && isset($data['originals']))
				return $data;

			$id = 0;
			if(is_object($data) && !empty($data->product_id))
				$id = (int)$data->product_id;

			$ids = array();
			if(is_array($data)) {
				foreach($data as $d) {
					if(!empty($d->product_id))
						$ids[] = (int)$d->product_id;
				}
				if(!empty($ids)) {
					sort($ids);
					$id = implode(',', $ids);
				}
				foreach($data as $d) {
					if(!empty($d->product_parent_id))
						$ids[] = (int)$d->product_parent_id;
				}
			}

			static $categories = array();
			$parents = array();
			if(!isset($categories[$id]) && !is_array($data)) {
				$categories[$id]['originals'] = array();
				$categories[$id]['parents'] = array();
				$categories[$id]['products'] = array();
				if($id)
					$categories[$id]['products'][] = $id;

				$categoryClass = hikashop_get('class.category');
				$productClass = hikashop_get('class.product');
				if(!empty($data->product_id) && !isset($data->product_type)) {
					$prodData = $productClass->get($data->product_id);
					if(!empty($prodData->product_type)) {
						$data->product_type = $prodData->product_type;
						$data->product_parent_id = $prodData->product_parent_id;
					}
				}
				if(!empty($data->product_parent_id))
					$categories[$id]['products'][] = $data->product_parent_id;

				if(!empty($data->categories)) {
					foreach($data->categories as $category) {
						if(!is_object($category))
							$categories[$id]['originals'][$category] = $category;
						else
							$categories[$id]['originals'][$category->category_id] = $category->category_id;
					}
					$parents = $categoryClass->getParents($data->categories);
				} else {
					if(isset($data->product_type) && $data->product_type == 'variant' && !empty($data->product_parent_id))
						$loadedCategories = $productClass->getCategories($data->product_parent_id);
					elseif(!empty($data->product_id))
						$loadedCategories = $productClass->getCategories($data->product_id);
					if(!empty($loadedCategories)) {
						foreach($loadedCategories as $cat) {
							$categories[$id]['originals'][$cat] = $cat;
						}
						$parents = $categoryClass->getParents($loadedCategories);
					}
				}
				if(!empty($parents) && is_array($parents)) {
					foreach($parents as $parent) {
						$categories[$id]['parents'][(int)$parent->category_id] = (int)$parent->category_id;
					}
					unset($parents);
				}
			} else if(!isset($categories[$id])) {
				$c = array(
					'originals' => array(),
					'parents' => array(),
					'products' => $ids
				);
				$loadProductCategories = array();
				foreach($data as $d) {
					if(!empty($d->categories)) {
						foreach($d->categories as $category) {
							if(!is_object($category))
								$c['originals'][(int)$category] = (int)$category;
							else
								$c['originals'][(int)$category->category_id] = (int)$category->category_id;
						}
					} elseif(!empty($d->product_id)) {
						$loadProductCategories[ (int)$d->product_id ] = (int)$d->product_id;
						if(!empty($d->product_parent_id)) {
							$loadProductCategories[ (int)$d->product_parent_id ] = (int)$d->product_parent_id;
						}
					}
				}
				if(!empty($loadProductCategories)) {
					$query = 'SELECT pc.category_id '.
						' FROM '.hikashop_table('product_category').' AS pc '.
						' WHERE pc.product_id IN ('.implode(',', $loadProductCategories).');';
					$this->database->setQuery($query);
					$product_categories = $this->database->loadColumn();

					foreach($product_categories as $category) {
						$c['originals'][(int)$category] = (int)$category;
					}
				}

				$categoryClass = hikashop_get('class.category');
				$parents = $categoryClass->getParents($c['originals']);

				$c['children'] = array();
				if(!empty($parents) && is_array($parents)) {
					foreach($parents as $parent) {
						$c['parents'][(int)$parent->category_id] = (int)$parent->category_id;

						if((int)$parent->category_parent_id > 0) {
							$c['children'][(int)$parent->category_id] = array( (int)$parent->category_parent_id );
							if(isset($c['children'][(int)$parent->category_parent_id]))
								$c['children'][(int)$parent->category_id] = array_merge($c['children'][(int)$parent->category_id], $c['children'][(int)$parent->category_parent_id]);
						} else {
							$c['children'][(int)$parent->category_id] = array();
						}
					}
					unset($parents);
				}

				$categories[$id] = $c;

				foreach($data as $d) {
					if(empty($d->product_id) || empty($d->categories))
						continue;

					if(!empty($categories[(int)$d->product_id]))
						continue;

					$p_c = array(
						'originals' => array(),
						'parents' => array(),
						'products' => array((int)$d->product_id)
					);
					if(!empty($d->categories)) {
						foreach($d->categories as $category) {
							if(!is_object($category))
								$cat_id = (int)$category;
							else
								$cat_id = (int)$category->category_id;

							$p_c['originals'][$cat_id] = $cat_id;
							$p_c['parents'] = array_combine($c['children'][$cat_id], $c['children'][$cat_id]);
						}
					}
					$categories[(int)$d->product_id] = $p_c;
				}

			}

			$allCat =& $categories[$id];
		}

		if($type == 'order') {
			$allCat = array();

			$shipping_ids = null;
			if(!empty($data->cart_shipping_ids))
				$shipping_ids = $data->cart_shipping_ids;
			if(!empty($data->order_shipping_id))
				$shipping_ids = $data->order_shipping_id;
			if(!empty($shipping_ids)) {
				if(!is_array($shipping_ids))
					$shipping_ids = explode(',', $shipping_ids);
				foreach($shipping_ids as $k => $id) {
					$parts = explode('@',$id,2);
					$shipping_ids[$k] = (int)reset($parts);
				}
				$allCat['shipping'] = $shipping_ids;
			}

			if(empty($allCat['shipping'])) {
				$allCat['shipping'] = true;
			}

			if(!empty($data->cart_payment_id)) {
				$allCat['payment'] = (int)$data->cart_payment_id;
			}
			if(!empty($data->order_payment_id)) {
				$allCat['payment'] = (int)$data->order_payment_id;
			}

			if(empty($allCat['payment'])) {
				$allCat['payment'] = true;
			}

			if(!empty($data->products) && is_array($data->products) && count($data->products)) {
				$allCat['originals'] = array();
				$allCat['parents'] = array();

				$categoryClass = hikashop_get('class.category');
				$productClass = hikashop_get('class.product');

				foreach($data->products as $k => $p) {
					if(!isset($p->product_type)) {
						$prodData = $productClass->get($p->product_id);
						if(!empty($prodData->product_type)){
							$data->products[$k]->product_type = $prodData->product_type;
							$data->products[$k]->product_parent_id = $prodData->product_parent_id;
						}
					}
				}

				$ids = array();
				foreach($data->products as $p) {
					if(isset($p->product_type) && $p->product_type == 'variant') {
						$ids[] = (int)$p->product_parent_id;
					} else {
						$ids[] = (int)$p->product_id;
					}
				}

				$allCat['products'] = $ids;
				$loadedCategories = $productClass->getCategories($ids);
				if(!empty($loadedCategories)) {
					foreach($loadedCategories as $cat) {
						$allCat['originals'][$cat]=$cat;
					}
				}
				$parents = $categoryClass->getParents($loadedCategories);

				if(!empty($parents) && is_array($parents)) {
					foreach($parents as $parent) {
						$allCat['parents'][$parent->category_id] = $parent->category_id;
					}
				}
			}
			if(count($allCat)<1)
				$allCat = null;
		}

		if($type == 'category') {
			$id = null;
			if(empty($data->category_id)) {
				if(!empty($data->category_parent_id)) {
					$id = $data->category_parent_id;
				}
			} else {
				$id = $data->category_id;
			}
			if(!empty($id)) {
				static $categories2 = array();
				if(!isset($categories2[$id])) {
					$categories2[$id]['originals'][$id] = $id;
					$categoryClass = hikashop_get('class.category');
					$parents = $categoryClass->getParents($id);
					if(!empty($parents)) {
						foreach($parents as $parent) {
							$categories2[$id]['parents'][$parent->category_id] = $parent->category_id;
						}
					}
				}
				$allCat =& $categories2[$id];
			}
		}

		return $allCat;
	}

	function chart($table, $field, $order_status = '', $width = 0, $height = 0) {
		static $a = false;
		if(!$a){
			$a = true;
			$doc = JFactory::getDocument();
			$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");
		}
		if($table == 'contact')
			return;
		$namekey = hikashop_secureField($field->field_namekey);
		if(empty($order_status)){
			if($table=='item') $table ='order_product';
			$this->database->setQuery('SELECT COUNT(`'.$namekey.'`) as total,`'.$namekey.'` as name FROM '.$this->fieldTable($table).' WHERE `'.$namekey.'` IS NOT NULL AND `'.$namekey.'` != \'\' GROUP BY `'.$namekey.'` ORDER BY total DESC LIMIT 20');
		}elseif($table=='entry'){
			$this->database->setQuery('SELECT COUNT(a.`'.$namekey.'`) as total,a.`'.$namekey.'` as name FROM '.$this->fieldTable($table).' AS a LEFT JOIN '.hikashop_table('order').' AS b ON a.order_id=b.order_id WHERE b.order_status='.$this->database->Quote($order_status).' AND a.`'.$namekey.'` IS NOT NULL AND a.`'.$namekey.'` != \'\' GROUP BY a.`'.$namekey.'` ORDER BY total DESC LIMIT 20');
		}
		if(empty($width)){
			$width=600;
		}
		if(empty($height)){
			$height=400;
		}
		$results = $this->database->loadObjectList();
?>
		<script type="text/javascript">
		function drawChart<?php echo $namekey; ?>() {
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn('string');
			dataTable.addColumn('number');
			dataTable.addRows(<?php echo count($results); ?>);
<?php
foreach($results as $i => $oneResult){
	$name = isset($field->field_value[$oneResult->name]) ? $this->trans(@$field->field_value[$oneResult->name]->value) : $oneResult->name; ?>
			dataTable.setValue(<?php echo $i ?>, 0, '<?php echo addslashes($name).' ('.$oneResult->total.')'; ?>');
			dataTable.setValue(<?php echo $i ?>, 1, <?php echo intval($oneResult->total); ?>);
<?php } ?>

			var vis = new google.visualization.PieChart(document.getElementById('fieldchart<?php echo $namekey;?>'));
			var options = {
				title: '<?php echo addslashes($field->field_realname);?>',
				width: <?php echo $width;?>,
				height: <?php echo $height;?>,
				is3D:true,
				legendTextStyle: {color:'#333333'}
			};
			vis.draw(dataTable, options);
		}
		google.load("visualization", "49", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart<?php echo $namekey; ?>);
		</script>

		<div class="hikachart chart" style="width:<?php echo $width;?>px;height:<?php echo $height;?>px;" id="fieldchart<?php echo $namekey;?>"></div>
<?php
	}

	function prepareFields(&$fields, &$data, $type = 'user', $url = 'checkout&task=state', $test = false) {
		if(empty($fields))
			return;

		$id = $type.'_id';
		switch($type) {
			case 'address':
			case 'billing_address':
			case 'shipping_address':
				$user_id = (int)@$data->address_user_id;
				break;
			case 'item':
				$order_id = (int)@$data->order_id;
				if($order_id > 0){
					$orderClass = hikashop_get('class.order');
					$order = $orderClass->get($order_id);
					$user_id = (int)@$order->order_user_id;
				} else {
					$user_id = 0;
				}
				$id = 'product_id';
				break;
			case 'order':
				$user_id = (int)@$data->order_user_id;
				break;
			default:
				$user_id = 0;
				break;
		}

		$guest = true;
		if($user_id > 0) {
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($user_id);
			$guest = !(bool)@$user->user_cms_id;
		}

		foreach($fields as $namekey => $field) {
			if(empty($field))
				continue;
			$fields[$namekey]->guest_mode = $guest;
			if(!empty($fields[$namekey]->field_options) && is_string($fields[$namekey]->field_options)) {
				$fields[$namekey]->field_options = hikashop_unserialize($fields[$namekey]->field_options);
			}

			$this->loadValues($fields[$namekey]);

			if(!is_array($data) && ($data == null || empty($data)))
				$data = new stdClass();
			if(is_object($data) && empty($data->$id) && !empty($namekey) && empty($data->$namekey)) {
				if(empty($fields[$namekey]->field_options['pleaseselect']) && $fields[$namekey]->field_type != 'zone') {
					$data->$namekey = $field->field_default;
				} else {
					$data->$namekey = '';
				}
			} else if(is_array($data) && !empty($namekey) && !isset($data['originals'])) {
				$v = (empty($fields[$namekey]->field_options['pleaseselect'])) ? $field->field_default : '';
				if(count($data)) {
					foreach($data as &$d) {
						if(!empty($d->$namekey) || !empty($d->$id))
							continue;
						$d->$namekey = $v;
					}
				}
			}
			if(!empty($fields[$namekey]->field_options['zone_type']) && $fields[$namekey]->field_options['zone_type'] == 'country'){
				$baseUrl = JURI::base().'index.php?option=com_hikashop&ctrl='.$url.'&tmpl=component';
				$currentUrl = strtolower(hikashop_currentUrl());
				if(substr($currentUrl, 0, 8) == 'https://') {
					$domain = substr($currentUrl, 0, strpos($currentUrl, '/', 9));
				} else {
					$domain = substr($currentUrl, 0, strpos($currentUrl, '/', 8));
				}
				if(substr($baseUrl, 0, 8) == 'https://') {
					$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', 9));
				} else {
					$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', 8));
				}
				$fields[$namekey]->field_url = $baseUrl . '&';
			}
		}
		$this->handleZone($fields, $test, $data);
	}

	function loadValues(&$field) {
		if(!empty($field->field_options['mysql_query'])) {
			if(!empty($field->field_value)) {
				if(is_string($field->field_value))
					$field->field_value = $this->explodeValues($field->field_value);
				$field->field_value_old = $field->field_value;
			}
			$this->database->setQuery($field->field_options['mysql_query']);
			$values = $this->database->loadObjectList();
			$field->field_value = array();
			if(!empty($values)) {
				foreach($values as $v) {
					if(!isset($v->value))
						continue;
					$value = $v->value;
					if(isset($v->title)) {
						$v->value = $v->title;
					}
					if(!isset($v->disabled)) {
						$v->disabled = 0;
					}
					$field->field_value[$value] = $v;
				}
			}
		} elseif(!empty($field->field_value) && is_string($field->field_value)) {
			$field->field_value = $this->explodeValues($field->field_value);
		}
	}

	function handleZone(&$fields, $test = false, $data = null) {
		$types = array();
		foreach($fields as $k => $field){
			if($field->field_type != 'zone' || empty($field->field_options['zone_type']))
				continue;

			if($field->field_options['zone_type'] != 'state') {
				$types[$field->field_options['zone_type']] = $field->field_options['zone_type'];
				continue;
			}

			if(!empty($field->field_value))
				continue;

			$allFields = $this->getData('', $field->field_table, false);

			foreach($allFields as $i => $oneField) {
				if(!empty($oneField->field_options) && is_string($oneField->field_options)) {
					$oneField->field_options = hikashop_unserialize($oneField->field_options);
				}

				if($oneField->field_type != 'zone' || empty($oneField->field_options['zone_type']) || $oneField->field_options['zone_type'] != 'country')
					continue;

				$namekey = $oneField->field_namekey;
				if(!empty($data->$namekey)) {
					$oneField->field_default = $data->$namekey;
				} else {
					$zoneClass = hikashop_get('class.zone');

					$zone = $zoneClass->get($oneField->field_default);
					$ok = true;
					if(empty($zone) || !$zone->zone_published){
						$config =& hikashop_config();
						$zone_id = explode(',',$config->get('main_tax_zone',$zone->zone_id));
						if(count($zone_id))
							$zone_id = array_shift($zone_id);
						$ok = false;
						if($zone->zone_id != $zone_id) {
							$newZone = $zoneClass->get($zone_id);
							if($newZone->zone_published) {
								$allFields[$i]->field_default = $newZone->zone_namekey;
								$oneField->field_default = $newZone->zone_namekey;
								$oneField->field_options = serialize($oneField->field_options);
								$this->save($oneField);
								$ok = true;
							}
						}
					}
					if(!$ok) {
						$app = JFactory::getApplication();
						if(empty($zone)) {
							$app->enqueueMessage('In your custom zone field "'.$oneField->field_namekey.'", you have the zone "'.$oneField->field_default. '". However, that zone does not exist. Please change your custom field accordingly.', 'error');
						} else {
							$app->enqueueMessage('In your custom zone field "'.$oneField->field_namekey.'", you have the zone "'.$oneField->field_default. '". However, that zone is unpublished. Please change your custom field accordingly.', 'error');
						}
					}
				}
				$zoneType = hikashop_get('type.country');
				$zoneType->type = 'state';
				$zoneType->published = true;
				$zoneType->country_name = $oneField->field_default;
				$zones = $zoneType->load();
				$this->setValues($zones,$fields,$k,$field);

				break;
			}
		}

		if(!empty($types)) {
			$zoneType = hikashop_get('type.country');
			$zoneType->type = $types;
			$zoneType->published = true;
			$zones = $zoneType->load();

			if(!empty($zones)) {
				foreach($fields as $k => $field) {
					$this->setValues($zones,$fields,$k,$field);
				}
			}
		}
	}

	function handleZoneListing(&$fields,&$rows){
		if(empty($rows) || empty($fields)) return;
		$values = array();
		foreach($fields as $k => $field){
			if($field->field_type=='zone'){
				$field_namekey = $field->field_namekey;
				foreach($rows as $row){
					if(!empty($row->$field_namekey)){
						if(is_object($row->$field_namekey) && isset($row->$field_namekey->zone_namekey)) {
							$values[$row->$field_namekey->zone_namekey]=$this->database->Quote($row->$field_namekey->zone_namekey);
							continue;
						}
						if(is_string($row->$field_namekey))
							$values[$row->$field_namekey]=$this->database->Quote($row->$field_namekey);
					}
				}
			}
		}
		if(!empty($values)){
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$values).') ORDER BY zone_name_english ASC';
			$this->database->setQuery($query);
			$zones = $this->database->loadObjectList('zone_namekey');
			foreach($fields as $field){
				if($field->field_type!='zone')
					continue;
				$field_namekey = $field->field_namekey;
				foreach($rows as $k => $row){
					if(empty($row->$field_namekey))
						continue;
					foreach($zones as $zone){
						if($zone->zone_namekey!=$row->$field_namekey)
							continue;
						if(is_numeric($zone->zone_name_english)){
							$title = $zone->zone_name;
						}else{
							$title = $zone->zone_name_english;
							if($zone->zone_name_english != $zone->zone_name){
								$title.=' ('.$zone->zone_name.')';
							}
						}
						$rows[$k]->$field_namekey=$title;
						break;
					}
				}
			}
		}
	}

	function setValues(&$zones,&$fields,$k,&$field){
		foreach($zones as $zone){
			if($field->field_type=='zone' && !empty($field->field_options['zone_type']) && $field->field_options['zone_type']==$zone->zone_type){
				$title = $zone->zone_name_english;
				if($zone->zone_name_english != $zone->zone_name){
					$title.=' ('.$zone->zone_name.')';
				}
				$obj = new stdClass();
				$obj->value = $title;
				$obj->disabled = '0';
				if(empty($fields[$k]->field_value) || is_string($fields[$k]->field_value))
					$fields[$k]->field_value = array();
				$fields[$k]->field_value[$zone->zone_namekey]=$obj;
			}
		}
	}

	function getInput($type, &$oldData, $report = true, $varname = 'data', $force = false, $area = '', $ids = null) {
		$this->report = $report;
		$data = null;

		static $formDataCache = null;
		static $formDataName = null;

		if(is_string($varname) && ($force || $formDataCache === null || $formDataName != $varname)) {
			$formDataCache = hikaInput::get()->get($varname, array(), 'array');
			$formDataName = $varname;
			$formData =& $formDataCache;
		} else if(is_array($varname)) {
			$formData =& $varname;
		} else {
			$formData =& $formDataCache;
		}

		$dataType = $type;
		if(is_array($type)) {
			$dataType = $type[0];
			$type = $type[1];
		} elseif(substr($type, 0, 4) == 'plg.') {
			$this->_loadExternals();
			if(!empty($this->externalValues)) {
				foreach($this->externalValues as $name => $externalValue) {
					if($externalValue->value == $type && !empty($externalValue->datatype)) {
						$dataType = $externalValue->datatype;
						break;
					}
				}
			}
		}

		if(empty($formData[$dataType])) {
			if(is_string($varname)) {
				$formData[$dataType] = array();
			} else if(is_array($varname)) {
				unset($formData);
				$formData = array();
				$formData[$dataType] =& $varname;
			}
		}

		$app = JFactory::getApplication();
		if(empty($area))
			$area = (hikashop_isClient('administrator')) ? 'backend' : 'frontcomp';

		$allCat = $this->getCategories($type, $oldData);

		if(!empty($ids)){
			$allCat['ids'] = $ids;
		}

		$fields =& $this->getData($area, $type, false, $allCat);

		if($type == 'entry' && $area == 'frontcomp') {
			$ok = true;
			$data = array();
			foreach($formData[$dataType] as $key => $form) {
				$data[$key] = new stdClass();
				if( !$this->_checkOneInput($fields, $formData[$dataType][$key], $data[$key], $type, $oldData) ) {
					$ok = false;
				}
			}
		} else {
			if(!isset($formData[$dataType]))
				$formData[$dataType] = null;

			$data = new stdClass();
			$ok = $this->_checkOneInput($fields, $formData[$dataType], $data, $type, $oldData);
		}

		if($data != null && !empty($data) && (!is_object($data) || count(get_object_vars($data)) > 0)) {
			$_SESSION['hikashop_'.$type.'_data'] = $data;
			$_SESSION['hikashop_'.$dataType.'_data'] = $data;
		} else {
			$_SESSION['hikashop_'.$type.'_data'] = null;
			$_SESSION['hikashop_'.$dataType.'_data'] = null;
			unset($_SESSION['hikashop_'.$type.'_data']);
			unset($_SESSION['hikashop_'.$dataType.'_data']);
		}

		unset($formData);

		if(!$ok)
			return $ok;
		return $data;
	}

	public function getFilteredInput($type, &$oldData, $report = true, $varname = 'data', $force = false, $area = '', $ids = null) {
		$typeName = $type;
		if(is_array($type)) {
			$typeName = $type[1];
			if(isset($type[2]))
				$this->prefix = $type[2];
		}

		$app = JFactory::getApplication();
		if(empty($area))
			$area = (hikashop_isClient('administrator')) ? 'backend' : 'frontcomp';

		$allCat = $this->getCategories($typeName, $oldData);
		if(!empty($ids))
			$allCat['ids'] = $ids;
		$fields =& $this->getData($area, $typeName, false, $allCat);
		$data = $this->getInput($type, $oldData, $report, $varname, $force, $area, $ids);

		if(!$data)
			return $data;

		if($typeName == 'entry' && $area == 'frontcomp') {
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

	function _checkOneInput(&$fields, &$formData, &$data, $type, &$oldData) {
		if(!empty($fields)) {
			foreach($fields as $namekey => $field){
				if(!empty($fields[$namekey]->field_options) && is_string($fields[$namekey]->field_options)) {
					$fields[$namekey]->field_options = hikashop_unserialize($fields[$namekey]->field_options);
				} elseif(empty($fields[$namekey]->field_options)) {
					$fields[$namekey]->field_options = array();
				}
			}
		}

		$ok = true;
		$this->error_fields = array();
		if(empty($fields)) {
			$this->checkFields($formData, $data, $type, $fields);
			return $ok;
		}

		$fullProduct = null;

		foreach($fields as $k => $field) {
			$namekey = $field->field_namekey;

			if($field->field_type == 'customtext' || empty($field->field_published)) {
				if(isset($formData[$namekey]))
					unset($formData[$namekey]);
				continue;
			}

			if($type == 'item' && !empty($oldData->product_id) && !empty($field->field_categories_products)) {
				$product_id = (int)$oldData->product_id;
				$parent_id = (int)@$oldData->product_parent_id;
				if($parent_id == 0)
					$parent_id = -1;
				if(strpos($field->field_products, ','.$product_id.',') === false && strpos($field->field_products, ','.$parent_id.',') === false && !in_array($product_id, $field->field_categories_products) && !in_array($parent_id, $field->field_categories_products)) {
					if(isset($formData[$namekey]))
						unset($formData[$namekey]);
					continue;
				}
			}

			if(!empty($field->field_options['limit_to_parent'])) {
				$parent = $field->field_options['limit_to_parent'];
				if(!isset($field->field_options['parent_value']))
					$field->field_options['parent_value'] = array();
				if(!is_array($field->field_options['parent_value']))
					$field->field_options['parent_value'] = array($field->field_options['parent_value']);
				$skip = false;
				foreach($fields as $otherField) {
					if($otherField->field_namekey != $parent)
						continue;
					$valid = true;
					foreach($field->field_options['parent_value'] as $neededValue) {
						if(is_array($formData[$parent])){
							if(!in_array($neededValue,	$formData[$parent])) {
								$valid = false;
							}
						} elseif($neededValue != $formData[$parent]) {
							$valid = false;
						}
					}

					if(!isset($formData[$parent]) || !$valid) {
						if(isset($formData[$namekey]))
							unset($formData[$namekey]);
						$skip = true;
					}
					break;
				}

				if($skip && $field->field_required)
					continue;
			}

			$class = $this->_getFieldTypeClass($field);

			if($type == 'item' && !empty($oldData->product_id) && !empty($field->field_value_all)) {
				if(empty($fullProduct)) {
					$productClass = hikashop_get('class.product');
					$fullProduct = $productClass->get((int)$oldData->product_id);
				}
				$product_key = $k.'_values';
				$field->field_value_all = $field->field_value;
				if(isset($fullProduct->$product_key))
					$field->field_value = $fullProduct->$product_key;
			}

			$val = @$formData[$namekey];
			$class->formData = $formData;
			$class->formFields = $fields;
			if(!$class->check($fields[$k], $val, @$oldData->$namekey)) {
				$ok = false;
				$this->error_fields[] = $fields[$k];
			}
			$formData[$namekey] = $val;

			if($type == 'item' && !empty($oldData->product_id) && !empty($field->field_value_all) && $field->field_value_all !== true) {
				$field->field_value = $field->field_value_all;
				$field->field_value_all = true;
			}
		}

		unset($fullProduct);

		$this->checkFields($formData, $data, $type, $fields);
		return $ok;
	}

	public function checkFieldsData(&$fields, &$formData, &$data, $type, &$oldData) {
		$buffer = clone($data);
		$ok = $this->_checkOneInput($fields, $formData, $buffer, $type, $oldData);
		foreach($fields as $field) {
			$namekey = $field->field_namekey;
			if(isset($buffer->$namekey))
				$data->$namekey = $buffer->$namekey;
		}
		return $ok;
	}

	function checkFields(&$data, &$object, $type, &$fields) {
		$app = JFactory::getApplication();
		static $safeHtmlFilter = null;
		if(is_null($object))
			$object = new stdClass();
		if(is_null($safeHtmlFilter)) {
			jimport('joomla.filter.filterinput');
			$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		}
		$noFilter = array();
		if(!empty($fields)) {
			foreach($fields as $field) {
				if(isset($field->field_options['filtering']) && !$field->field_options['filtering']) {
					$noFilter[] = $field->field_namekey;
				}
			}
		}
		if(empty($data) || !is_array($data))
			return;

		foreach($data as $column => $value) {
			$column = trim(strtolower($column));
			if(!$this->allowed($column, $type))
				continue;

			hikashop_secureField($column);

			if($value === null)
				continue;

			if(is_array($value)){
				$arrayColumn = false;
				if(substr($type, 0, 4) == 'plg.') {
					$this->_loadExternals();
					foreach($this->externalValues as $externalValue) {
						if($externalValue->value == $type && !empty($externalValue->arrayColumns)) {
							$arrayColumn = in_array($column, $externalValue->arrayColumns);
							break;
						}
					}
				}
				if( $arrayColumn || ($type == 'user' && $column == 'user_params') || ($type == 'order' && hikashop_isClient('administrator') && in_array($column,array('history','mail','product'))) ) {
					$object->$column = new stdClass();
					foreach($value as $c => $v){
						$c = trim(strtolower($c));
						if($this->allowed($c,$type)) {
							hikashop_secureField($c);
							$object->$column->$c = in_array($c, $noFilter) ? $v : strip_tags($v);
						}
					}
				} else {
					foreach($value as $c => $v){
						if(is_array($v) || is_object($v))
							$value[$c] = '';
					}
					$value = implode(',',$value);
					$object->$column = in_array($column, $noFilter) ? $value : strip_tags($value);
				}
			} elseif(!hikashop_isClient('administrator')) {
				$value = $safeHtmlFilter->clean($value, 'string');
				$object->$column = in_array($column, $noFilter) ? $value : strip_tags($value);
			} else {
				$object->$column = in_array($column, $noFilter) ? $value : $safeHtmlFilter->clean($value, 'string');
			}
		}
	}

	function checkFieldsForJS(&$extraFields,&$requiredFields,&$validMessages,&$values){
		foreach($extraFields as $type => $oneType) {
			if(empty($oneType))
				continue;
			foreach($oneType as $k => $oneField) {
				if(!empty($oneField->field_js_added))
					continue;
				$class = $this->_getFieldTypeClass($oneField);
				$class->JSCheck($oneField, $requiredFields[$type], $validMessages[$type], $values[$type]);

				if(!empty($oneField->field_options['regex'])){
					$this->regexs[$type][$oneField->field_namekey] = str_replace(array("\\","'"),array("\\\\","\'"),$oneField->field_options['regex']);
				}
				$extraFields[$type][$k]->field_js_added = true;
			}
		}
	}

	function addJS( &$requiredFields, &$validMessages, $types = array() ) {
		static $done = false;
		$doc = JFactory::getDocument();

		if(!$done) {
			$js = "
window.hikashopFieldsJs = {
	'reqFieldsComp': {},
	'validFieldsComp': {},
	'regexFieldsComp': {},
	'regexValueFieldsComp': {}
};";
			$doc->addScriptDeclaration($js);
			$done = true;
		}

		if(empty($types))
			return;

		$js = '';
		foreach($types as $type) {
			if(!empty($requiredFields[$type])) {
				$js .= "\nwindow.hikashopFieldsJs['reqFieldsComp']['".$type."'] = ['" . implode("','", $requiredFields[$type]) . "'];".
					"\nwindow.hikashopFieldsJs['validFieldsComp']['".$type."'] = ['" . implode("','", $validMessages[$type]) . "'];";
			}

			if(!empty($this->regexs[$type])) {
				$js .= "\nwindow.hikashopFieldsJs['regexFieldsComp']['".$type."'] = ['" . implode("','", array_keys($this->regexs[$type])) . "'];".
					"\nwindow.hikashopFieldsJs['regexValueFieldsComp']['".$type."'] = ['".implode("','", $this->regexs[$type]) . "'];";
			}

			if($type == 'register') {
				$js .= "\nwindow.hikashopFieldsJs['password_different'] = '".JText::_('PASSWORDS_DO_NOT_MATCH', true)."';".
					"\nwindow.hikashopFieldsJs['valid_email'] = '".JText::_('VALID_EMAIL', true)."';";
			} elseif($type == 'address') {
				$js .= "\nwindow.hikashopFieldsJs['valid_phone'] = '".JText::_('VALID_PHONE', true)."';";
			}
		}

		if(!empty($js))
			$doc->addScriptDeclaration($js);
	}

	function jsToggle(&$fields, $data, $id = 1, $prefix = '', $options = array()) {
		$doc = JFactory::getDocument();
		$js = '';
		static $done = false;

		$return_data = !empty($options['return_data']);
		$suffix_type = !empty($options['suffix_type']) ? $options['suffix_type'] : '';

		if(!$done) {
			$js = '
function hikashopToggleFields(new_value, namekey, field_type, id, prefix) {
	if(!window.hikashop) return false;
	return window.hikashop.toggleField(new_value, namekey, field_type, id, prefix);
}';
			$done = true;
		}
		$parents = $this->getParents($fields);

		if(empty($parents)) {
			if($return_data)
				return $js;
			if(!empty($js))
				$doc->addScriptDeclaration($js);
			return false;
		}

		$first = reset($parents);
		$type = $first->type;

		if(substr($type, 0, 4) == 'plg.') {
			$this->_loadExternals();
			foreach($this->externalValues as $externalValue) {
				if($externalValue->value == $type && !empty($externalValue->datatype)) {
					$type = $externalValue->datatype;
					break;
				}
			}
		}

		$js .= '
if(!window.hikashopFieldsJs) window.hikashopFieldsJs = {};
if(!window.hikashopFieldsJs["'.$type.$suffix_type.'"]) window.hikashopFieldsJs["'.$type.$suffix_type.'"] = {};';
		foreach($parents as $namekey => $parent){
			$js .= "\nwindow.hikashopFieldsJs['".$type.$suffix_type."']['".$namekey."'] = {};";
			foreach($parent->childs as $value => $childs){
				$js .= "\nwindow.hikashopFieldsJs['".$type.$suffix_type."']['".$namekey."']['".$value."'] = {};";
				foreach($childs as $field){
					$js .= "\nwindow.hikashopFieldsJs['".$type.$suffix_type."']['".$namekey."']['".$value."']['".$field->field_namekey."'] = '".$field->field_namekey."';";
				}
			}
		}

		$js .= $this->getLoadJSForToggle($parents, $data, $id, $prefix, $options);

		if($return_data)
			return $js;

		$doc->addScriptDeclaration($js);
	}

	function getLoadJSForToggle(&$parents, &$data, $id = 1, $prefix = '', $options = array()) {
		return "\nwindow.hikashop.ready(function(){\n" .
			$this->initJSToggle($parents, $data, $id, $prefix, $options) .
			"\n});";
	}

	function initJSToggle(&$parents, &$data, $id = 1, $prefix = '', $options = array()) {
		$first = reset($parents);
		$type = $first->type;
		if(substr($type, 0, 4) == 'plg.') {
			$this->_loadExternals();
			foreach($this->externalValues as $externalValue) {
				if($externalValue->value == $type && !empty($externalValue->datatype)) {
					$type = $externalValue->datatype;
					if(!empty($externalValue->prefix))
						$id .= ',"'.$externalValue->prefix.'"';
					break;
				}
			}
		}

		$suffix_type = !empty($options['suffix_type']) ? $options['suffix_type'] : '';
		$js = '';
		foreach($parents as $namekey => $parent) {
			$js .= "\nwindow.hikashop.toggleField(null,'" . $namekey . "','" . $type . $suffix_type . "'," . $id . ",'" . $prefix . "','".@$options['type']."');";
		}
		return $js;
	}

	function getParents(&$fields){
		$parents = array();
		if(empty($fields))
			return false;

		foreach($fields as $k => $field){
			if(empty($field->field_options['limit_to_parent']))
				continue;

			$parent = $field->field_options['limit_to_parent'];

			if(!isset($parents[$parent])) {
				$obj = new stdClass();
				$obj->type = $field->field_table;
				$obj->childs = array();
				$parents[$parent] = $obj;
			}

			$parent_value = @$field->field_options['parent_value'];
			if(is_array($parent_value)) {
				foreach($parent_value as $value) {
					if(!isset($parents[$parent]->childs[$value]))
						$parents[$parent]->childs[$value] = array();
					$parents[$parent]->childs[$value][$field->field_namekey] = $field;
				}
			} else {
				if(!isset($parents[$parent]->childs[$parent_value]))
					$parents[$parent]->childs[$parent_value] = array();
				$parents[$parent]->childs[$parent_value][$field->field_namekey]=$field;
			}
		}
		return $parents;
	}

	function allowed($column, $type = 'user') {
		$restricted = array(
			'user'=>array('user_partner_price'=>1,'user_partner_paid'=>1,'user_created_ip'=>1,'user_partner_id'=>1,'user_partner_lead_fee'=>1,'user_partner_click_fee'=>1,'user_partner_percent_fee'=>1,'user_partner_flat_fee'=>1),
			'order'=>array('order_id'=>1,'order_billing_address_id'=>1,'order_shipping_address_id'=>1,'order_user_id'=>1,'order_status'=>1,'order_discount_code'=>1,'order_created'=>1,'order_ip'=>1,'order_currency_id'=>1,'order_status'=>1,'order_shipping_price'=>1,'order_discount_price'=>1,'order_shipping_id'=>1,'order_shipping_method'=>1,'order_payment_id'=>1,'order_payment_method'=>1,'order_full_price'=>1,'order_modified'=>1,'order_partner_id'=>1,'order_partner_price'=>1,'order_partner_paid'=>1,'order_type'=>1,'order_partner_currency_id'=>1)
		);
		if(substr($type, 0, 4) == 'plg.')
			$this->_loadExternals();

		if(isset($restricted[$type][$column])) {
			$app = JFactory::getApplication();
			if(!hikashop_isClient('administrator'))
				return false;
		}
		return true;
	}

	function _loadExternals() {
		if($this->externalValues !== null)
			return;

		$this->externalValues = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onTableFieldsLoad', array( &$this->externalValues ) );
		if(empty($this->externalValues))
			return;
		foreach($this->externalValues as &$externalValue) {
			if(!empty($externalValue->table) && substr($externalValue->value, 0, 4) != 'plg.')
				$externalValue->value = 'plg.' . $externalValue->value;
			unset($externalValue);
		}
	}

	function explodeValues($values){
		$allValues = explode("\n",$values);
		$returnedValues = array();

		foreach($allValues as $id => $oneVal){
			$line = explode('::',trim($oneVal));
			$var = $line[0];
			$val = !empty($line[1]) ? $line[1] : '';
			if(count($line)==2){
				$disable = '0';
			}else{
				$disable = !empty($line[2]) ? $line[2] : '0';
			}
			if(strlen($val)>0){
				$obj = new stdClass();
				$obj->value = $val;
				$obj->disabled = $disable;
				$returnedValues[$var] = $obj;
			}
		}
		return $returnedValues;
	}

	function getFieldName($field, $requiredDisplay = false, $classname = '') {
		$class = $this->_getFieldTypeClass($field);
		return $class->getFieldName($field, $requiredDisplay, $classname);
	}

	function trans($name){
		if(is_null($name))
			return '';
		$val = preg_replace('#[^a-z0-9]#i', '_', strtoupper($name));
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') && strcmp(JText::_($val), strip_tags(JText::_($val))) !== 0)
			$trans = $val;
		else
			$trans = JText::_($val);
		if($val == $trans)
			$trans = $name;
		return $trans;
	}

	function get($field_id,$default=null){
		$query = 'SELECT a.* FROM '.hikashop_table('field').' as a WHERE a.`field_id` = '.intval($field_id).' LIMIT 1';
		$this->database->setQuery($query);

		$field = $this->database->loadObject();
		if(!empty($field->field_options)) {
			$field->field_options = hikashop_unserialize($field->field_options);
		}

		if(!empty($field->field_display)) {
			$display_values = explode(';', trim($field->field_display, ';'));
			$field->field_display = array();
			foreach($display_values as $display_value) {
				if(strpos($display_value, '=') === false)
					continue;
				list($k,$v) = explode('=', $display_value, 2);
				$field->field_display[$k] = (int)$v;
			}
		}

		if(!empty($field->field_value))
			$field->field_value = $this->explodeValues($field->field_value);

		return $field;
	}

	function addValue($id, $title, $value, $disabled) {
		$field = parent::get($id);
		if(!$field)
			return false;

		if(!empty($field->field_value) && is_string($field->field_value)) {
			$field->field_value = $this->explodeValues($field->field_value);
		}

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		if(strlen($title) < 1 && strlen($value) < 1)
			return false;

		$value = (strlen($value) < 1) ? $title : $value;
		$disabled = strlen($disabled < 1) ? '0' : $disabled;
		$obj = new stdClass();
		$obj->value = $safeHtmlFilter->clean($value,'raw');
		$obj->disabled = $safeHtmlFilter->clean($disabled,'string');
		$field->field_value[$safeHtmlFilter->clean($title,'raw')] = $obj;
		$values = array();
		foreach($field->field_value as $k => $v) {
			$values[] = $k . '::' . $v->value . '::' . $v->disabled;
		}

		$field->field_value = implode("\n", $values);

		$update = new stdClass();
		$update->field_id = $field->field_id;
		$update->field_value = $field->field_value;
		return $this->save($update);
	}

	function saveForm() {
		$field = new stdClass();
		$field->field_id = hikashop_getCID('field_id');
		$field->field_products = '';
		$field->field_payment_id = '';
		$field->field_shipping_id = '';

		$formData = hikaInput::get()->get('data', array(), 'array');
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['field'] as $column => $value) {
			hikashop_secureField($column);
			if($column == 'field_default')
				continue;
			if(in_array($column, array('field_products', 'field_payment_id', 'field_shipping_id'))){
				hikashop_toInteger($value);
				$value = ','.implode(',',$value).',';
			}elseif(is_array($value))
				$value = implode(',',$value);
			$field->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}


		$fieldOptions = hikaInput::get()->get('field_options', array(), 'array');
		foreach($fieldOptions as $column => $value) {
			if($value === '')
				continue;

			if($column == 'mysql_query') {
				$value = trim($value);
			}

			if(is_array($value)) {
				foreach($value as $id => $val) {
					if(!in_array($column, array('product_value','parent_value')))
						hikashop_secureField($id);
					if($val === '')
						continue;
					$fieldOptions[$column][$id] = $safeHtmlFilter->clean($val, 'string');
				}
			} else {
				$fieldOptions[$column] = $safeHtmlFilter->clean($value, 'string');
			}
		}

		$fieldsType = hikashop_get('type.fields');
		$fieldsType->load($field->field_table);
		if(!empty($fieldsType->externalOptions) && isset($fieldsType->allValues[$field->field_type])) {
			$linkedOptions = $fieldsType->allValues[$field->field_type]['options'];
			foreach($fieldsType->externalOptions as $key => $extraOption) {
				if(in_array($key, $linkedOptions)) {
					$o = is_array($extraOption) ? $extraOption['obj'] : $extraOption->obj;
					if(is_string($o))
						$o = new $o();

					if(method_exists($o, 'save'))
						$o->save($fieldOptions);
				}
			}
		}

		if($field->field_type == "customtext") {
			$fieldOptions['customtext'] = hikaInput::get()->getRaw('fieldcustomtext','');
			if(empty($field->field_id)) {
			 	$field->field_namekey = 'customtext_'.date('z_G_i_s');
			} else {
				$oldField = $this->get($field->field_id);
				if($oldField->field_core)
					$field->field_type = $oldField->field_type;
			}
		}
		$field->field_options = $fieldOptions;

		$fields = array( &$field );
		if(isset($field->field_namekey))
			$namekey = $field->field_namekey;
		$field->field_namekey = 'field_default';

		$field_required = $field->field_required;
		$field->field_required = false;
		$data = null;
		$oldData = null;
		if($this->_checkOneInput($fields, $formData['field'], $data, '', $oldData)) {
			if(isset($formData['field']['field_default']) && is_array($formData['field']['field_default'])){
				$defaultValue = '';
				foreach($formData['field']['field_default'] as $value) {
					if(empty($defaultValue)) {
						$defaultValue .= $value;
					} else {
						$defaultValue .= ',' . $value;
					}
				}
			} else {
				$defaultValue = @$formData['field']['field_default'];
			}

			if(isset($fieldOptions['filtering']) && $fieldOptions['filtering']) {
				$field->field_default = strip_tags($defaultValue);
			} else {
				jimport('joomla.filter.filterinput');
				$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
				$field->field_default = $safeHtmlFilter->clean($defaultValue,'string');
			}
		}
		$field->field_required = $field_required;
		unset($field->field_namekey);
		if(isset($namekey))
			$field->field_namekey = $namekey;


		$field->field_options = serialize($field->field_options);

		$fieldDisplay = hikaInput::get()->get('field_display', array(), 'array');
		if(!empty($fieldDisplay)) {
			$field->field_display = ';';
			foreach($fieldDisplay as $k => $v) {
				$field->field_display .= $k . '=' . (int)$v . ';';
			}
		}

		if(in_array($field->field_table, array('product'))) {
			$field->field_backend = 1;
		}

		$fieldValues = hikaInput::get()->get('field_values', array(), 'array' );

		if(!empty($fieldValues)) {
			$field->field_value = array();
			jimport('joomla.filter.filterinput');
			$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

			foreach($fieldValues['title'] as $i => $title) {
				if(strlen($title) < 1 && strlen($fieldValues['value'][$i]) < 1)
					continue;

				$value = (strlen($fieldValues['value'][$i]) < 1) ? $title : $fieldValues['value'][$i];
				$disabled = (strlen($fieldValues['disabled'][$i]) < 1) ? '0' : $fieldValues['disabled'][$i];
				$field->field_value[] = $safeHtmlFilter->clean($title,'raw'). '::' .  $safeHtmlFilter->clean($value,'raw') . '::' .  $safeHtmlFilter->clean($disabled,'string');
			}
			$field->field_value = implode("\n", $field->field_value);
		}



		if(!preg_match('#^([a-z0-9_-]+ *= *"[\p{L}\p{N}\p{Z}\p{S}\p{M} \+\*:\-;\(\)\{\}\[\]\']+" *)* *$#i', $fieldOptions['attribute'])){
			$this->errors[] = 'Please specify a correct attribute';
			return false;
		}

		$new = empty($field->field_id);

		if($new && $field->field_type != 'customtext') {
			if(empty($field->field_namekey))
				$field->field_namekey = $field->field_realname;

			$field->field_namekey = preg_replace('#[^a-z0-9_]#i', '', strtolower($field->field_namekey));
			if(empty($field->field_namekey)) {
				$this->errors[] = 'Please specify a column name';
				return false;
			}

			if(strlen($field->field_namekey) > 50) {
				$this->errors[] = 'Please specify a shorter column name';
				return false;
			}

			if(is_numeric($field->field_namekey[0])) {
				$this->errors[] = 'The first character of the column name needs to be a letter';
				return false;
			}

			if($field->field_table != 'contact'){
				if(in_array(strtoupper($field->field_namekey),array(
					'ACCESSIBLE', 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE',
					'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE',
					'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION',
					'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CURRENT_DATE', 'CURRENT_TIME',
					'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR',
					'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT',
					'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW',
					'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'ENCLOSED', 'ESCAPED',
					'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR',
					'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GRANT', 'GROUP', 'HAVING', 'HIGH_PRIORITY',
					'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX',
					'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3',
					'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IS', 'ITERATE', 'JOIN', 'KEY', 'KEYS',
					'KILL', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME',
					'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY',
					'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH', 'MAXVALUE', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT',
					'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL',
					'NOT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMERIC', 'ON', 'OPTIMIZE', 'OPTION', 'OPTIONALLY',
					'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE',
					'RANGE', 'READ', 'READS', 'READ_WRITE', 'REAL', 'REFERENCES', 'REGEXP', 'RELEASE',
					'RENAME', 'REPEAT', 'REPLACE','REQUIRE', 'RESIGNAL', 'RESTRICT', 'RETURN', 'REVOKE',
					'RIGHT', 'RLIKE', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE',
					'SEPARATOR', 'SET', 'SHOW', 'SIGNAL', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION',
					'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT',
					'SSL', 'STARTING', 'STRAIGHT_JOIN', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT',
					'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK',
					'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP',
					'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'WHEN', 'WHERE', 'WHILE',
					'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL', 'GENERAL', 'IGNORE_SERVER_IDS',
					'MASTER_HEARTBEAT_PERIOD', 'MAXVALUE', 'RESIGNAL', 'SIGNAL', 'SLOW', 'ALIAS', 'OPTIONS',
					'RELATED', 'IMAGES', 'FILES', 'CATEGORIES', 'PRICES', 'VARIANTS', 'CHARACTERISTICS', 'TAGS',
					'ID', 'USERNAME', 'PASSWORD', 'CATEGORY', 'PRICE', 'CHARACTERISTIC', 'RELATED', 'SHIPPING',
					'PAYMENT', 'ORDER_FULL_TAX', 'ORDER_PRODUCT', 'ADDRESS', 'USER', 'JOOMLA_USERS', 'USERGROUPS',
					'PRODUCT_FILES', 'PRODUCT_IMAGES')
				)) {
					$this->errors[] = 'The column name "'.$field->field_namekey.'" is reserved. Please use another one.';
					return false;
				}

				$tables = array($field->field_table);
				if($field->field_table == 'item')
					$tables = array('cart_product', 'order_product', 'product');

				$databaseHelper = hikashop_get('helper.database');
				$databaseHelper->loadStructure();

				foreach($tables as $table_name) {
					if(isset($databaseHelper->structure['#__hikashop_'.$table_name][$field->field_namekey])) {
						$this->errors[] = 'The field "'.$field->field_namekey.'" already exists in the table "'.$table_name.'"';
						return false;
					}
				}
			}
		}

		$categories = hikaInput::get()->get('category', array(), 'array');
		hikashop_toInteger($categories);
		$cat = ',';
		foreach($categories as $category) {
			$cat .= $category . ',';
		}
		if($cat == ',')
			$cat = 'all';
		$field->field_categories = $cat;

		$field_id = $this->save($field);
		if(!$field_id)
			return false;

		if($new) {
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = 'field_id';
			$orderHelper->table = 'field';
			$orderHelper->groupMap = 'field_table';
			$orderHelper->groupVal = $field->field_table;
			$orderHelper->orderingMap = 'field_ordering';
			$orderHelper->reOrder();

			if($field->field_table == 'address') {
				$app = JFactory::getApplication();
				if(hikashop_isClient('administrator'))
					$app->enqueueMessage(JText::sprintf('PLEASE_ADD_ADDRESS_TAG', '{'.$field->field_namekey.'}'));
			}
		}
		hikaInput::get()->set('field_id', $field_id);
		return true;

	}

	public function save(&$field) {
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$new = empty($field->field_id);
		if($new)
			$app->triggerEvent( 'onBeforeFieldCreate', array( & $field, & $do) );
		else
			$app->triggerEvent( 'onBeforeFieldUpdate', array( & $field, & $do) );

		if(!$do)
			return false;

		if(isset($field->guest_mode)) {
			$guest_mode = $field->guest_mode;
			unset($field->guest_mode);
		}

		if(isset($field->field_value_old)) {
			unset($field->field_value_old);
		}

		$status = parent::save($field);
		if(empty($status))
			return $status;

		if(isset($guest_mode))
			$field->guest_mode = $guest_mode;

		if($new && $field->field_type != 'customtext') {
			$tables = array($field->field_table);
			if($field->field_table == 'item')
				$tables = array('cart_product', 'order_product');
			foreach($tables as $table_name) {
				if(in_array($table_name, array('contact')))
					continue;
				$query = 'ALTER TABLE '.$this->fieldTable($table_name).' ADD `'.$field->field_namekey.'` LONGTEXT NULL';
				$this->database->setQuery($query);
				try {
					$this->database->execute();
				}catch(Exception $e) {
				}
			}
		}

		if($new) {
			$field->field_id = $status;
			$app->triggerEvent( 'onAfterFieldCreate', array( & $field ) );
		} else
			$app->triggerEvent( 'onAfterFieldUpdate', array( & $field ) );

		return $status;
	}

	function handleBeforeDelete(&$ids, $table) {
		if(empty($ids) || !is_array($ids) || !count($ids))
			return;

		if(!isset($this->fieldsForDelete)) {
			$this->fieldsForDelete = array();
		}

		$this->fieldsForDelete[$table] = $this->getData('all', $table);

		if(empty($this->fieldsForDelete[$table])) {
			return;
		}

		$found = false;
		foreach($this->fieldsForDelete[$table] as $field) {
			$obj = $this->_getFieldTypeClass($field);
			if(get_class($obj) == 'hikashopFieldItem')
				continue;
			if(method_exists($obj, 'handleDelete')) {
				$found = true;
				break;
			}
		}

		if(!$found) {
			return;
		}

		if($table == 'item')
			return;

		if(!isset($this->elementsForDelete)) {
			$this->elementsForDelete = array();
		}

		$class = hikashop_get('class.'.$table);
		foreach($ids as $id) {
			$this->elementsForDelete[$id] = $class->get($id);
		}
	}

	function handleAfterDelete(&$ids, $table) {
		if(empty($this->fieldsForDelete[$table])) {
			return;
		}

		foreach($this->fieldsForDelete[$table] as $field) {
			$obj = $this->_getFieldTypeClass($field);
			if(get_class($obj) == 'hikashopFieldItem')
				continue;
			if(!method_exists($obj, 'handleDelete')) {
				continue;
			}
			foreach($ids as $id) {
				if(!isset($this->elementsForDelete[$id]) || empty($this->elementsForDelete[$id]))
					continue;
				$obj->handleDelete($field, $this->elementsForDelete[$id]);
			}
		}
	}

	function delete(&$elements, $keepdata = true){
		if(!is_array($elements))
			$elements = array($elements);

		foreach($elements as $key => $val) {
			$elements[$key] = hikashop_getEscaped($val);
		}

		if(empty($elements))
			return false;

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeFieldDelete', array( & $elements, & $do, & $keepdata) );

		if(!$do)
			return false;

		$this->database->setQuery('SELECT `field_namekey`,`field_id`,`field_table`,`field_type` FROM '.hikashop_table('field').'  WHERE `field_core` = 0 AND `field_id` IN ('.implode(',',$elements).')');
		$fieldsToDelete = $this->database->loadObjectList('field_id');

		if(empty($fieldsToDelete)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CORE_FIELD_DELETE_ERROR'));
			return false;
		}

		$namekeys = array();
		foreach($fieldsToDelete as $oneField) {
			if($oneField->field_type == 'customtext')
				continue;
			if($oneField->field_table=='item') {
				$namekeys['cart_product'][] = $oneField->field_namekey;
				$namekeys['order_product'][] = $oneField->field_namekey;
			} elseif($oneField->field_table != 'contact') {
				$namekeys[$oneField->field_table][] = $oneField->field_namekey;
			}
		}
		if(!$keepdata) {
			foreach($namekeys as $table => $fields) {
				$this->database->setQuery('ALTER TABLE '.$this->fieldTable($table).' DROP `'.implode('`, DROP `',$fields).'`');
				$this->database->execute();
			}
		}

		$this->database->setQuery('DELETE FROM '.hikashop_table('field').' WHERE `field_id` IN ('.implode(',',array_keys($fieldsToDelete)).')');
		$result = $this->database->execute();
		if(!$result)
			return false;


		$app->triggerEvent('onAfterFieldDelete', array( & $elements, $keepdata) );

		$affectedRows = $this->database->getAffectedRows();

		foreach($namekeys as $table => $fields) {
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = 'field_id';
			$orderHelper->table = 'field';
			$orderHelper->groupMap = 'field_table';
			$orderHelper->groupVal = $table;
			$orderHelper->orderingMap = 'field_ordering';
			$orderHelper->reOrder();
		}

		return $affectedRows;

	}

	function display(&$field, $value, $map, $inside = false, $options = '', $test = false, $allFields = null, $allValues = null, $requiredDisplay = true) {
		$field_type = $field->field_type;
		$obj = $this->_getFieldTypeClass($field);
		if(get_class($obj) == 'hikashopFieldItem')
			return 'Plugin '.$field_type.' missing or deactivated';

		if(is_string($value))
			$value = htmlspecialchars($value, ENT_COMPAT,'UTF-8');

		$html = $obj->display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);

		if($requiredDisplay && !empty($field->field_required))
			$html .=' <span class="hikashop_field_required">*</span>';
		return $html;
	}

	function show(&$field,$value,$className='') {
		$class = $this->_getFieldTypeClass($field);
		$html = $class->show($field,$value,$className);
		return $html;
	}

	function _getFieldTypeClass(&$field) {
		$field_type = $field->field_type;
		$classType = 'hikashopField'.ucfirst($field_type);
		if(substr($field->field_type,0,7) == 'joomla.') {
			$classType = 'hikashopFieldJoomla';
		}

		JPluginHelper::importPlugin('hikashop');

		if(substr($field->field_type,0,4) == 'plg.') {
			$field_type = substr($field->field_type, 4);
			$plg = hikashop_import('hikashop', $field_type);
			if(is_object($plg) && method_exists($plg, 'initFieldClass'))
				$plg->initFieldClass();

			$classType = 'hikashopField'.ucfirst($field_type);
			if(!class_exists($classType))
				$classType = 'hikashop'.ucfirst($field_type);
		} else if(!class_exists($classType))
			$classType = 'hikashop'.ucfirst($field_type);
		if(!class_exists($classType))
			$obj = new hikashopFieldItem($this);
		else
			$obj = new $classType($this);

		$app = JFactory::getApplication();
		$app->triggerEvent('onAfterFieldInit', array(&$obj, &$field));

		return $obj;
	}

	function fieldTable($table_name) {
		if(substr($table_name, 0, 4) == 'plg.') {
			$this->_loadExternals();
			$table_name = substr($table_name, 4);
			foreach($this->externalValues as $name => $externalValue) {
				if($name == $table_name) {
					if(!empty($externalValue->table))
						return $externalValue->table;
					break;
				}
			}
		}
		return hikashop_table($table_name);
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

		$table = @$options['table'];

		$select = array('f.*');
		$where = array();

		if(!empty($table)) {
			$where['field_table'] = 'f.field_table = '.$this->db->Quote($table);
		}

		if(!empty($search)) {
			$searchMap = array('f.field_id', 'f.field_realname', 'f.field_type');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$where['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$order = ' ORDER BY f.field_id DESC';

		if(count($where))
			$where = ' WHERE ' . implode(' AND ', $where);
		else
			$where = '';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . hikashop_table('field').' AS f' . $where . $order;
		$this->db->setQuery($query, $page, $limit);

		$ret[0] = $this->db->loadObjectList('field_id');

		if(count($ret[0]) < $limit)
			$fullLoad = true;

		if(!empty($value)) {
			if(is_array($value)) {
				hikashop_toInteger($value);
				$where = ' WHERE f.field_id IN ('. implode(',', $value).')';
			}else {
				$where = ' WHERE f.field_id = '. (int)$value;
			}
			$query = 'SELECT '.implode(', ', $select) . ' FROM ' . hikashop_table('field').' AS f' . $where;
			$this->db->setQuery($query, $page, $limit);
			$ret[1] = $this->db->loadObjectList('field_id');
			foreach($ret[1] as $k => $v) {
				if(!isset($ret[0][$k]))
					$ret[0][$k] = $v;
			}

		}

		$unset = array('field_options', 'field_display', 'field_default', 'field_value');
		if(!empty($ret[0])) {
			foreach($ret[0] as $k => $v) {
				foreach($unset as $u) {
					if(isset($v->$u))
						unset($ret[0][$k]->$u);
				}
			}
		}
		if(!empty($ret[1])) {
			foreach($ret[1] as $k => $v) {
				foreach($unset as $u) {
					if(isset($v->$u))
						unset($ret[1][$k]->$u);
				}
			}
		}
		return $ret;
	}
}

class hikashopFieldItem {
	public $prefix;
	public $suffix;
	public $excludeValue;
	public $report;
	public $parent;
	public $displayFor = false;

	function __construct(&$obj) {
		$this->prefix = $obj->prefix;
		$this->suffix = $obj->suffix;
		$this->excludeValue =& $obj->excludeValue;
		$this->report = @$obj->report;
		$this->parent =& $obj;
	}

	function getFieldName(&$field, $requiredDisplay = false, $classname = '') {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')) return $this->trans($field->field_realname);
		$required = '';
		$options = '';
		$for = '';
		if($requiredDisplay && !empty($field->field_required))
			$required = '<span class="hikashop_field_required_label">*</span>';
		if(!empty($classname))
			$options = ' class="'.str_replace('"','',$classname).'"';
		if($this->displayFor)
			$for = ' for="'.$this->prefix.$field->field_namekey.$this->suffix.'"';
		return '<label'.$for.$options.'>'.$this->trans($field->field_realname).$required.'</label>';
	}

	function trans($name) {
		if(is_null($name))
			return '';

		$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($name));

		$trans_not_found = true;

		if(!is_numeric($val)) {
			$trans_value = JText::_($val);

			$trans_not_found = strcmp($trans_value, $val) === 0;
		}
		if($trans_not_found) {
			$val = preg_replace('#[^A-Z_0-9]#','',strtoupper($name));
			$config = hikashop_config();
			if((empty($val) || $config->get('non_latin_translation_keys', 0)) && !empty($name)) {
				$val = 'T'.strtoupper(sha1($name));
			} elseif(is_numeric($val)) {
				$val = 'T'.$val;
			}
			$trans_value = JText::_($val);
		}

		$config = hikashop_config();
		$translate = hikashop_isClient('administrator') && !$config->get('translate_HTML_value_in_backend_for_fields', 0);
		$HTML_tags_found = strcmp($trans_value, strip_tags($trans_value)) !== 0;
		if($translate && $HTML_tags_found)
			$trans = $val;
		else
			$trans = $trans_value;

		if($val == $trans)
			$trans = $name;
		return $trans;
	}

	function show(&$field, $value) {
		return $this->trans($value);
	}

	function JSCheck(&$oneField, &$requiredFields, &$validMessages, &$values) {
		if(empty($oneField->field_required))
			return;

		$requiredFields[] = $oneField->field_namekey;
		if(!empty($oneField->field_options['errormessage'])) {
			$validMessages[] = addslashes($this->trans($oneField->field_options['errormessage']));
		} else {
			$validMessages[] = addslashes(JText::sprintf('FIELD_VALID',$this->trans($oneField->field_realname)));
		}
	}

	function check(&$field,&$value,$oldvalue) {
		if(is_string($value))
			$value = trim($value);

		if(!$field->field_required || is_array($value) || strlen($value) || ($value === null && strlen($oldvalue)))
			return true;

		if($field->field_table == 'order' && (!in_array($field->field_products, array('all', '')) || !in_array($field->field_categories, array('all', '')))){
			$cartClass = hikashop_get('class.cart');

			$cart = $cartClass->loadFullCart(true);
			$inCart = false;

			$restricted_products = array ();
			$restricted_categories = array();
			if(!in_array($field->field_products, array('all', ''))){
				$restricted_products = explode(',', $field->field_products);
				$restricted_products = array_filter($restricted_products);

				if (!isset($cart->cart_products) && isset($cart->products)) {
					$cart->cart_products = $cart->products;
				}

				foreach($cart->cart_products as $cart_product){
					if(in_array($cart_product->product_id, $restricted_products))
						$inCart = true;
				}
			}
			if(!in_array($field->field_categories, array('all', '')) && !$inCart){
				$restricted_categories = explode(',', $field->field_categories);
				$restricted_categories = array_filter($restricted_categories);

				$fieldClass = hikashop_get('class.field');
				$cart_categories = $fieldClass->getCategories('order', $cart);

				foreach($restricted_categories as $restricted_category){
					if($field->field_with_sub_categories && in_array($restricted_category, $cart_categories['parents']))
						$inCart = true;
					else if(!$field->field_with_sub_categories && in_array($restricted_category, $cart_categories['originals']))
						$inCart = true;
				}
			}

			if(!$inCart)
				return true;

		}

		if(!empty($this->report)) {
			if(!empty($field->field_options['errormessage'])){
				$message = $this->trans($field->field_options['errormessage']);
			}else{
				$message = JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname));
			}
			if($this->report === true) {
				$app = JFactory::getApplication();
				$app->enqueueMessage($message, 'error');
			} else {
				$this->parent->messages[$this->prefix.$field->field_namekey] = array($message);
			}
		}
		return false;
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) { return $value; }

	function addButton($field, $test = false) {
		if(empty($field->field_options['allow_add']))
			return '';
		if(!empty($field->field_options['mysql_query']))
			return '';

		if($test)
			return '';

		$popupHelper = hikashop_get('helper.popup');
		$html = $popupHelper->display(
			'<i class="fas fa-plus"></i> '.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("field&task=add_value&field_id=".$field->field_id,true),
			'field_'.$field->field_namekey.'_add_button',
			520, 360, 'class="hikabtn btn-primary" onclick="window.hikashop.currentFieldParent = this.parentNode.parentNode; return window.hikashop.openBox(this);"', '', 'link'
		);
		return '<div class="field_add_button">'.$html.'</div>';
	}
	function showfield($viewObj, $namekey, $row) {
		if( isset( $row->$namekey)) { return $row->$namekey; }
		return '';
	}
}

class hikashopFieldCustomtext extends hikashopFieldItem {
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		if(empty($field->field_options))
			return '';
		if(is_string($field->field_options))
			$field->field_options = hikashop_unserialize($field->field_options);
		return $this->trans(@$field->field_options['customtext']);
	}
	function show(&$field, $value) {
		if(empty($field->field_options))
			return '';
		if(is_string($field->field_options))
			$field->field_options = hikashop_unserialize($field->field_options);
		return $this->trans(@$field->field_options['customtext']);
	}
}

class hikashopFieldText extends hikashopFieldItem {
	var $type = 'text';
	var $class = 'inputbox';
	var $displayFor = true;

	function check(&$field, &$value, $oldvalue) {
		$status = parent::check($field, $value, $oldvalue);

		if($status && !$field->field_required  && !empty($field->field_options['regex']) && !empty($field->field_value)) {
			$config = hikashop_config();
			if($config->get('check_regex_if_not_required', 0) && !preg_match('/'.str_replace('/','\/',$field->field_options['regex']).'/',$value)) {
				$this->_displayError($field);
				return false;
			}
			return true;
		}

		if (!$status || !$field->field_required || empty($field->field_options['regex']))
			return $status;

		if (preg_match('/'.str_replace('/','\/',$field->field_options['regex']).'/',$value))
			return $status;

		$status = false;
		if (empty($this->report))
			return $status;

		$this->_displayError($field);

		return $status;
	}

	function _displayError(&$field) {
		if (empty($this->report))
			return;

		if (!empty($field->field_options['errormessage'])) {
			$message = $this->trans($field->field_options['errormessage']);
		} else {
			$message = JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname));
		}

		if ($this->report === true) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($message, 'error');
		} else {
			$this->parent->messages[] = array(
				$message,
				'error'
			);
		}

	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		$size = '';
		if(!empty($field->field_options['size']))
			$size .= ' size="'.intval($field->field_options['size']).'"';
		if(!empty($field->field_options['maxlength']))
			$size .= ' maxlength="'.intval($field->field_options['maxlength']).'"';
		if(!empty($field->field_options['readonly']))
			$size .= ' readonly="readonly"';
		if(!empty($field->field_options['placeholder']))
			$size .= ' placeholder="'.JText::_($field->field_options['placeholder']).'"';
		if(!empty($field->field_options['attribute']))
			$size .= $field->field_options['attribute'];
		if(is_null($value))
			$value = '';
		$js = '';
		if(strlen(trim($value)) < 1) {
			if(!empty($field->field_default))
				$value = $field->field_default;
			elseif($inside){
				$value = $this->trans($field->field_realname);
				$jsvalue = addslashes($value);
				$this->excludeValue[$field->field_namekey] = $value;
				$js = ' onfocus="if(this.value == \''.$jsvalue.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$jsvalue.'\';"';
			}
		}

		if(!empty($field->field_required) && !empty($field->registration_page))
			$size .= ' aria-required="true" required="required"';


		if(strpos($options, 'class="') === false) {
			$options .= ' class="'.$this->class.'"';
		} else {
			$options = str_replace('class="', 'class="'.$this->class.' ', $options);
		}

		return '<input id="'.$this->prefix.@$field->field_namekey.$this->suffix.'"'.$size.$js.' '.$options.' type="'.$this->type.'" name="'.$map.'" value="'.$value.'" />';
	}

	function show(&$field, $value) {
		if(in_array($field->field_table,array('address','order','item')) && in_array($field->field_type, array('text','textarea', 'link', 'wysiwyg')))
			$html = $value;
		else
			$html = $this->trans($value);
		if(!empty($field->field_options['display_format']) && strpos($field->field_options['display_format'], '{value}') !== false)
			$html = str_replace('{value}', $html, $field->field_options['display_format']);
		return $html;
	}
}

class hikashopFieldLink extends hikashopFieldText {
	var $displayFor = false;
	function check(&$field,&$value,$oldvalue) { 
		if(is_string($value)) {
			$value = explode(':', $value, 2);
			if(!empty($value[1]))
				$value[1] = trim($value[1], ':');
		}
		if(is_array($value)) {
			$return = '';
			if(!empty($value[0]))
				$value[0] = '"'.$value[0].'"';
			if(!empty($value[0]) && !empty($value[1]))
				$return = $value[0].':'.$value[1];
			$value = $return;
		}

		return parent::check($field, $value, $oldvalue);
	}
	function show(&$field,$value) {
		$target = '';
		if(isset($field->field_options['target_blank']) && $field->field_options['target_blank'] == '1')
			$target = ' target="_blank"';
		$link = $value;
		$text = $value;
		if(preg_match('#^"?(.*)"?:(.*)$#iU', $value, $m)) {
			$link = $m[2];
			$text = trim($m[1], '"');
		}
		return '<a'.$target.' href="' .$this->trans($link).'">'.$this->trans($text).'</a>';
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		$namekey = $field->field_namekey;

		if(strlen(trim($value)) < 1 && !empty($field->field_default))
			$value = $field->field_default;
		$link = $value;
		$text = $value;
		if(preg_match('#^"?(.*)"?:(.*)$#iU', $value, $m)) {
			$link = $m[2];
			$text = trim(str_replace('&quot;', '"',$m[1]), '"');
		}else{
			@list($text, $link) = explode(':',$value,2);
		}

		$js = 'document.getElementById(\''.$this->prefix.$namekey.$this->suffix.'\').value = \'\' + document.getElementById(\''.$this->prefix.$namekey.'_text'.$this->suffix.'\').value + \':\' + document.getElementById(\''.$this->prefix.$namekey.'_link'.$this->suffix.'\').value;';
		if(strpos($options, 'onchange="') === false) {
			$options .= ' onchange="'.$js.'"';
		} else {
			$options = str_replace('onchange="', 'onchange="'.$js.' ', $options);
		}
		$field->field_namekey .= '_link';
		$html = '<div class="hikashop_field_link"><label class="hikashop_field_link_label" for="'.$field->field_namekey.'">'.JText::_('URL').'</label>'.parent::display($field, $link, $field->field_namekey, $inside, $options, $test, $allFields, $allValues).'</div>';
		$field->field_namekey = $namekey . '_text';
		$html .= '<div class="hikashop_field_link_text"><label class="hikashop_field_link_text_label" for="'.$field->field_namekey.'">'.JText::_('FIELD_TEXT').'</label>'.parent::display($field, $text, $field->field_namekey, $inside, $options, $test, $allFields, $allValues).'</div>';
		return $html . '<input type="hidden" id="'.$this->prefix.$namekey.$this->suffix.'" name="'.$map.'" value="'.$value.'" />';
	}
}

class hikashopFieldFile extends hikashopFieldText {
	var $type = 'file';
	var $class = 'inputbox hikashop_custom_file_upload_field';

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		$html = '';
		if(!empty($value)) {
			$html .= $this->show($field,$value,'hikashop_custom_file_upload_link');
		}
		$map = str_replace('.', '_', $field->field_table) . '_' . $field->field_namekey;
		$html.= parent::display($field, $value, $map, $inside, $options, $test, $allFields, $allValues);
		$html.= '<span class="hikashop_custom_file_upload_message">' . JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')).'</span>';
		return $html;
	}

	function JSCheck(&$oneField, &$requiredFields, &$validMessages, &$values) {
		$namekey = $oneField->field_namekey;
		if(empty($values->$namekey))
			return parent::JSCheck($oneField, $requiredFields, $validMessages, $values);
		return true;
	}

	function show(&$field,$value,$class='hikashop_custom_file_link') {
		switch($class){
			case 'admin_email':
				return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).'">'.$value.'</a>';
			case 'user_email':
				if(@$field->guest_mode)
					return $value;
				$app = JFactory::getApplication();
				if(!hikashop_isClient('administrator'))
					return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
				return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).'">'.$value.'</a>';
			default:
				break;
		}
		return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
	}

	function check(&$field, &$value, $oldvalue) {
		$fileClass = hikashop_get('class.file');
		$map = str_replace('.', '_', $field->field_table) . '_' . $field->field_namekey;

		if(empty($field->field_options['file_type']))
			$field->field_options['file_type'] = 'file';

		$file = $fileClass->saveFile($map, $field->field_options['file_type'], $this->allowedFiles(), $field);

		if(!empty($file)) {
			$value = $file;
		} else if(!empty($oldvalue)) {
			$value = $oldvalue;
		} else {
			$value = '';
		}

		return parent::check($field, $value, $oldvalue);
	}

	function allowedFiles() {
		$config =& hikashop_config();
		return $config->get('allowedfiles');
	}
}

class hikashopFieldImage extends hikashopFieldFile {
	function show(&$field, $value, $class='hikashop_custom_image_link') {
		if(in_array($class,array('admin_email', 'user_email')))
			return parent::show($field, $value, $class);

		if(empty($class))
			$class = 'hikashop_custom_image_link';
		return '<img class="'.$class.'" src="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'" alt="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" />';
	}

	function allowedFiles() {
		$config =& hikashop_config();
		return $config->get('allowedimages');
	}
}

class hikashopFieldAjaxfile extends hikashopFieldItem {
	var $layoutName = 'upload';
	var $mode = 'file';
	var $viewName = 'file_entry';
	var $defaultText = 'HIKA_PRODUCT_FILES_EMPTY_UPLOAD';

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$uploaderType = hikashop_get('type.uploader');

		$id = $this->prefix.@$field->field_namekey.$this->suffix;
		if(!empty($field->field_options['multiple']))
			$map .= '[]';
		$options = array(
			'upload' => true,
			'tooltip' => true,
			'gallery' => false,
			'text' => JText::_($this->defaultText),
			'uploader' => array('order', $field->field_table.'-'.$field->field_namekey),
			'ajax' => true,
			'vars' => array(
				'field_map' => $map,
				'uploader_id' => $id
			)
		);

		$value = trim($value, '|');

		if(!empty($value)) {
			if(empty($field->field_options['multiple'])) {
				$content = $this->_displayOne($value, $map, $id, $field);
			} else {
				$files = explode('|', $value);
				$content = '';
				foreach($files as $file) {
					if(empty($file))
						continue;
					$content .= $this->_displayOne($file, $map, $id, $field);
				}
			}
		} else {
			$content = '<input type="hidden" name="'.$map.'" value=""/>';
			$options['empty'] = true;
		}

		$function = 'display';
		if($this->mode == 'image')
			$function .= 'Image';
		else
			$function .= 'File';

		if(!empty($field->field_options['multiple']))
			$function .= 'Multiple';
		else
			$function .= 'Single';

		return $uploaderType->$function($id, $content, $options);
	}

	function _displayOne($value, $map, $id, &$field) {
		$params = new stdClass();
		$params->file_name = $value;
		$params->file_path = $value;
		$params->field_name = $map.'[name]';
		$params->file_size = 0;
		$params->delete = empty($field->field_required);
		$params->uploader_id = $id;

		if(!empty($value)) {

			$fileClass = hikashop_get('class.file');
			$path = $fileClass->getPath('file', '', $field);
			$v = '';
			if(JFile::exists($path . $value)) {
				$v = md5_file($path . $value);
				$params->file_size = filesize($path . $value);

				$params->working_path = $path;

				$n = $map.'[sec]';

				$params->extra_fields = array(
					$n => $v
				);
			}
		}

		$params->origin_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)));

		if($this->mode == 'image' && !empty($value)) {
			$thumbnail_x = 100;
			$thumbnail_y = 100;
			$thumbnails_params = '&thumbnail_x='.$thumbnail_x.'&thumbnail_y='.$thumbnail_y;

			$params->thumbnail_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).$thumbnails_params);
		}
		$js = '';
		$content = hikashop_getLayout($this->layoutName, $this->viewName, $params, $js);
		return $content;
	}

	function show(&$field, $value, $class = 'hikashop_custom_file_link') {
		if(!is_array($field->field_options)) {
			$field->field_options = hikashop_unserialize($field->field_options);
		}
		if(empty($field->field_options['multiple'])) {
			if(empty($value))
				return;
			return '<p class="hikashop_custom_file_area">'.$this->_showOne($field, $value, $class).'</p>';
		}

		$html = '';
		if(!empty($value)) {
			$value = trim($value, '|');
			$files = explode('|', $value);
			$html = array();
			foreach($files as $file) {
				if(empty($file))
					continue;
				$html[] = '<p class="hikashop_custom_file_area">'.$this->_showOne($field, $file, $class).'</p>';
			}
			$html = implode('', $html);
		}
		return $html;
	}

	function _showOne(&$field, $value, $class = 'hikashop_custom_file_link') {
		$download_link = 'order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value));
		if($class == 'admin_email')
			return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl='.$download_link.'">'.$value.'</a>';

		if($class == 'user_email') {
			if(@$field->guest_mode)
				return $value;

			$app = JFactory::getApplication();
			if(!hikashop_isClient('administrator'))
				return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink($download_link).'">'.$value.'</a>';
			return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl='.$download_link.'">'.$value.'</a>';
		}

		hikashop_loadJslib('opload');
		if($this->mode == 'image') {
			$thumbnail_x = 100;
			$thumbnail_y = 100;
			if(!empty($field->field_options['thumbnail_x'])) {
				$thumbnail_x = $field->field_options['thumbnail_x'];
			}
			if(!empty($field->field_options['thumbnail_y'])) {
				$thumbnail_y = $field->field_options['thumbnail_y'];
			}
			$thumbnail_link = hikashop_completeLink($download_link.'&thumbnail_x='.$thumbnail_x.'&thumbnail_y='.$thumbnail_y);
			$main_link = hikashop_completeLink($download_link.'&thumbnail_x=0&thumbnail_y=0');
			return '<a target="_blank" class="'.$class.'" href="'.$main_link.'"><img class="'.$class.'" src="'.$thumbnail_link.'" alt="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" /></a>';
		}
		return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink($download_link).'">'.$value.'</a>';
	}

	function check(&$field,&$value, $oldvalue) {
		if(!empty($value) && !is_array($value))
			return false;
		if(is_array($value)) {
			if(!empty($field->field_options['multiple'])) {
				$tmp_array = array();
				foreach($value as $k => $element) {
					if(empty($element)) {
						continue;
					}
					$tmp_array[$k] = $element;
				}
				$value = $tmp_array;

				$newvalue = array();
				$error = false;
				$sec_start = count($value)/2;
				$mode = null;
				foreach($value as $k => $element) {
					if(empty($element['name']))
						continue;

					if(is_null($mode)) {
						if(!empty($value[$k+1]['sec']))
							$mode = 'no_sec_start';
						elseif(!empty($value[$sec_start+$k]['sec']))
							$mode = 'sec_start';
					}
					if($mode == 'sec_start') {
						if(!empty($value[$sec_start+$k]['sec']))
							$sec = $value[$sec_start+$k]['sec'];
						elseif(!empty($value[$k+1]['sec']))
							$sec = $value[$k+1]['sec'];
						else
							continue;
					} else {
						if(!empty($value[$k+1]['sec']))
							$sec = $value[$k+1]['sec'];
						elseif(!empty($value[$sec_start+$k]['sec']))
							$sec = $value[$sec_start+$k]['sec'];
						else
							continue;

					}
					$file = array('name' => $element['name'], 'sec' => $sec);
					$ok = $this->_checkOneFile($file, '', $field);
					if($ok) {
						$newvalue[] = $file;
					} else {
						$error = true;
					}
				}
				if($error) {
					$value = $oldvalue;
					return false;
				} else {
					if(is_string($oldvalue))
						$oldfiles = explode('|',$oldvalue);
					else
						$oldfiles = $oldvalue;
					foreach($oldfiles as $oldfile) {
						if(!in_array($oldfile, $newvalue)) {
							$this->_handleDelete($field, $oldfile);
						}
					}

					$value = implode('|', $newvalue);
				}
			} else {
				$return = $this->_checkOneFile($value, $oldvalue, $field);
				if(!$return)
					return $return;
			}
		} else if($value != $oldvalue) {
			$this->_handleDelete($field, $oldvalue);
			$value = $oldvalue;
			return false;
		}
		return parent::check($field,$value,$oldvalue);
	}

	function _checkOneFile(&$value, $oldValue, $field) {
		$fileClass = hikashop_get('class.file');
		$path = $fileClass->getPath('file', '', $field);
		$hash = '';

		if(!empty($value['name']) && file_exists($path . $value['name']))
			$hash = md5_file($path . $value['name']);
		if(!empty($value['name']) && (empty($value['sec']) || $hash != $value['sec'])) {
			$value = $oldValue;
			return false;
		}
		if(!empty($value['name'])) {
			$value = $value['name'];

			if(!empty($field->field_options['max_filesize']) && filesize($path.$value) > $field->field_options['max_filesize']) {
				$this->reportError($field, JText::sprintf('UPLOADED_FILE_IS_BIGGER_THAN_LIMIT', $value, hikashop_human_readable_bytes(filesize($path.$value)), hikashop_human_readable_bytes($field->field_options['max_filesize'])));
				$value = $oldValue;
				return false;
			}

			if($this->mode == 'image' && (!empty($field->field_options['max_width']) || !empty($field->field_options['max_height']))) {
				$imageHelper = hikashop_get('helper.image');
				$imageHelper->uploadFolder = $path;
				$imageHelper->autoRotate($value);
				$imageHelper->resizeImage($value,'field', array(
					'image_x' => $field->field_options['max_width'],
					'image_y' => $field->field_options['max_height'],
					'watermark' => false,
				));
			}
		} else {
			$value = '';
		}
		return true;
	}

	function reportError(&$field, $message) {
		if(!empty($this->report)) {
			if($this->report === true) {
				$app = JFactory::getApplication();
				$app->enqueueMessage($message, 'error');
			} else {
				$this->parent->messages[$this->prefix.$field->field_namekey] = array($message);
			}
		}
	}

	function _manageUpload($field, &$ret, $map, $uploadConfig, $caller) {
		if(empty($map) || empty($field))
			return;

		$fileClass = hikashop_get('class.file');
		$path = $fileClass->getPath('file', '', $field);

		$ret->params->file_name = $ret->params->file_path;
		$ret->params->field_name = $map.'[name]';
		if(!empty($ret->params->file_path)) {
			$v = md5_file($path . $ret->params->file_path);
			$ret->params->file_size = filesize($path . $ret->params->file_path);

			$n = $map.'[sec]';
			$ret->params->extra_fields = array(
				$n => $v
			);
		}

		$ret->params->origin_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($ret->params->file_path)));

		if($this->mode == 'image') {
			$thumbnail_x = 100;
			$thumbnail_y = 100;
			$thumbnails_params = '&thumbnail_x='.$thumbnail_x.'&thumbnail_y='.$thumbnail_y;
			$ret->params->thumbnail_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($ret->params->file_path)).$thumbnails_params);
		}
	}

	private function _handleDelete(&$field, $value) {
		if(!is_array($field->field_options)) {
			$field->field_options = hikashop_unserialize($field->field_options);
		}

		if(empty($field->field_options['delete_files']))
			return;

		$fileClass = hikashop_get('class.file');
		$path = $fileClass->getPath('file', '', $field);

		if(!is_array($value)) {
			$value = trim($value, '|');
			$files = explode('|', $value);
		} else {
			$files = $value;
		}

		jimport('joomla.filesystem.folder');
		$thumbnail_folders = JFolder::folders($uploadPath);
		if(JFolder::exists($uploadPath.'thumbnails')) {
			$other_thumbnail_folders = JFolder::folders($uploadPath.'thumbnails');
			foreach($other_thumbnail_folders as $other_thumbnail_folder) {
				$thumbnail_folders[] = 'thumbnails'.DS.$other_thumbnail_folder;
			}
		}

		foreach($files as $file) {
			if(JFile::exists($path . $file)) {
				JFile::delete( $path . $file);
				foreach($thumbnail_folders as $thumbnail_folder) {
					if($thumbnail_folder != 'thumbnail' && substr($thumbnail_folder, 0, 9) != 'thumbnail' && substr($thumbnail_folder, 0, 11) != ('thumbnails'.DS))
						continue;
					if(JFile::exists($path.$thumbnail_folder.DS.$file)) {
						JFile::delete( $path .$thumbnail_folder.DS. $file );
					}
				}
			}
		}
	}

	public function handleDelete(&$field, &$element) {
		$namekey = $field->field_namekey;
		if(empty($element->$namekey))
			return;

		return $this->_handleDelete($field, $element->$namekey);
	}
}

class hikashopFieldAjaximage extends hikashopFieldAjaxfile {
	var $layoutName = 'upload';
	var $mode = 'image';
	var $viewName = 'image_entry';
	var $defaultText = 'HIKA_DEFAULT_IMAGE_EMPTY_UPLOAD';
}
class hikashopFieldHidden extends hikashopFieldText {
	var $type = 'hidden';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		if($test) {
			$this->type = 'text';
		}
		return parent::display($field, $value, $map, $inside, $options, $test, $allFields, $allValues);
	}
}

class hikashopFieldCoupon extends hikashopFieldText {
	function check(&$field,&$value,$oldvalue){
		$status = parent::check($field,$value,$oldvalue);
		if(!$status)
			return false;
		if($field->field_required && empty($value))
			return true;

		$zone_id = hikashop_getZone('shipping');
		$discountClass = hikashop_get('class.discount');
		$zoneClass = hikashop_get('class.zone');
		$zones = $zoneClass->getZoneParents($zone_id);
		$total = new stdClass();
		$price = new stdClass();
		$price->price_value_with_tax = 0;
		$price->price_value = 0;
		$price->price_currency_id = hikashop_getCurrency();
		$total->prices = array($price);
		if(empty($field->coupon))
			$field->coupon = array();

		$products = array();
		$field->coupon[$value] = $discountClass->loadAndCheck($value,$total,$zones,$products,true);

		if(empty($field->coupon[$value])) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(hikaInput::get()->getVar('coupon_error_message'),'notice');
			$status = false;
		}

		static $validCoupons = array();
		if(!isset($validCoupons[$value])) {
			$validCoupons[$value] = 1;
		} else {
			$validCoupons[$value]++;
		}

		if(!empty($field->coupon[$value]->discount_quota) && $field->coupon[$value]->discount_quota > 0){
			$left = ($field->coupon[$value]->discount_quota - $field->coupon[$value]->discount_used_times);
			if($left < $validCoupons[$value]) {
				if($left > 0){
					$app = JFactory::getApplication();
					$app->enqueueMessage('You cannot use the coupon '.$value.' more than '.$left.' times !');
				}
				$status = false;
			}
		}
		return $status;
	}
}

class hikashopFieldWysiwyg extends hikashopFieldTextarea {
	var $displayFor = true;
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		$editorHelper = hikashop_get('helper.editor');
		$editorHelper->name = $map;
		$editorHelper->content = $value;
		$editorHelper->id = $this->prefix.@$field->field_namekey.$this->suffix;
		$editorHelper->width = '100%';
		$editorHelper->cols = empty($field->field_options['cols']) ? 50 : intval($field->field_options['cols']);
		$editorHelper->rows = empty($field->field_options['rows']) ? 10 : intval($field->field_options['rows']);

		$ret = $editorHelper->display().
			'<div style="clear:both"></div>'.
			'<script type="text/javascript">'."\r\n".
			'if(window.Oby) window.Oby.registerAjax("syncWysiwygEditors", function(){ '.$editorHelper->jsCode().' });'."\r\n".
			'</script>';

		return $ret;
	}

	function show(&$field,$value) {
		$html = JHTML::_('content.prepare', $this->trans($value));
		if(!empty($field->field_options['display_format']) && strpos($field->field_options['display_format'], '{value}') !== false)
			$html = str_replace('{value}', $html, $field->field_options['display_format']);
		return $html;
	}
}

class hikashopFieldTextarea extends hikashopFieldItem {
	var $displayFor = true;
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$js = '';
		$html = '';
		if($inside && strlen($value) < 1){
			$value = addslashes($this->trans($field->field_realname));
			$this->excludeValue[$field->field_namekey] = $value;
			$js = 'onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		if(!empty($field->field_options['maxlength'])){
			static $done = false;
			if(!$done){
				$jsFunc='
				function hikashopTextCounter(textarea, counterID, maxLen) {
					cnt = document.getElementById(counterID);
					if (textarea.value.length > maxLen){
						textarea.value = textarea.value.substring(0,maxLen);
					}
					cnt.innerHTML = maxLen - textarea.value.length;
				}';
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration($jsFunc);
				$html.= '<span class="hikashop_remaining_characters">'.JText::sprintf('X_CHARACTERS_REMAINING',$this->prefix.@$field->field_namekey.$this->suffix.'_count',(int)$field->field_options['maxlength']).'</span>';
			}
			$js .= ' onKeyUp="hikashopTextCounter(this,\''.$this->prefix.@$field->field_namekey.$this->suffix.'_count'.'\','.(int)$field->field_options['maxlength'].');" onBlur="hikashopTextCounter(this,\''.$this->prefix.@$field->field_namekey.$this->suffix.'_count'.'\','.(int)$field->field_options['maxlength'].');" ';
		}

		$cols = empty($field->field_options['cols']) ? '' : 'cols="'.intval($field->field_options['cols']).'"';
		$rows = empty($field->field_options['rows']) ? '' : 'rows="'.intval($field->field_options['rows']).'"';
		$options .= empty($field->field_options['readonly']) ? '' : ' readonly="readonly"';
		$options .= empty($field->field_options['placeholder']) ? '' : ' placeholder="'.JText::_($field->field_options['placeholder']).'"';
		$options .= empty($field->field_options['attribute']) ? '' : $field->field_options['attribute'];
		if(strpos($options, 'class="') === false) {
			$options .= ' class="inputbox"';
		} else {
			$options = str_replace('class="', 'class="inputbox ', $options);
		}
		return '<textarea id="'.$this->prefix.@$field->field_namekey.$this->suffix.'" name="'.$map.'" '.$cols.' '.$rows.' '.$js.' '.$options.'>'.$value.'</textarea>'.$html;
	}

	function check(&$field, &$value, $oldvalue) {
		$status = parent::check($field, $value, $oldvalue);

		if (!$status || !$field->field_required || empty($field->field_options['regex']))
			return $status;

		if (preg_match('/'.str_replace('/','\/',$field->field_options['regex']).'/',$value))
			return $status;

		$status = false;
		if (empty($this->report))
			return $status;

		if (!empty($field->field_options['errormessage'])) {
			$message = $this->trans($field->field_options['errormessage']);
		} else {
			$message = JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname));
		}

		if ($this->report === true) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($message, 'error');
		} else {
			$this->parent->messages[] = array(
				$message,
				'error'
			);
		}

		return $status;
	}

	function show(&$field,$value){
		$html = nl2br(parent::show($field,$value));
		if(!empty($field->field_options['display_format']) && strpos($field->field_options['display_format'], '{value}') !== false)
			$html = str_replace('{value}', $html, $field->field_options['display_format']);
		return $html;
	}
}

class hikashopFieldDropdown extends hikashopFieldItem {
	var $type = '';
	var $displayFor = true;
	function show(&$field,$value){
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) $value = $field->field_value[$value]->value;

		$html = parent::show($field,$value);
		if(!empty($field->field_options['display_format']) && strpos($field->field_options['display_format'], '{value}') !== false)
			$html = str_replace('{value}', $html, $field->field_options['display_format']);
		return $html;
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$string = '';
		if(!empty($field->field_value) && !is_array($field->field_value)) {
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(empty($field->field_value) || !count($field->field_value)) {
			if(is_array($value))
				$value = reset($value);
			return '<input type="hidden" name="'.$map.'" value="'.$value.'" />';
		}
		if($this->type == "multiple"){
			$string.= '<input type="hidden" name="'.$map.'" value=" " />';
			$map.='[]';
			$arg = 'multiple="multiple"';
			if(!empty($field->field_options['size'])) $arg .= ' size="'.intval($field->field_options['size']).'"';
		}else{
			$arg = 'size="1"';
			if(is_string($value)&& empty($value) && !empty($field->field_value) && (!isset($field->field_default) || !isset($field->field_value[$field->field_default]))){
				$found = false;
				$first = false;
				foreach($field->field_value as $oneValue => $title){
					if($first===false){
						$first=$oneValue;
					}
					if($oneValue==$value){
						$found = true;
						break;
					}
				}
				if(!$found){
					$value = $first;
				}
			}
		}
		if(strpos($options, 'class="') === false) {
			$options .= ' class="hikashop_field_dropdown"';
		} else {
			$options = str_replace(array('class="form-control', 'class="'), array('class="form-select','class="hikashop_field_dropdown '), $options);
		}
		$options .= empty($field->field_options['attribute']) ? '' : ' '.$field->field_options['attribute'];
		$string .= '<select id="'.$this->prefix.$field->field_namekey.$this->suffix.'" name="'.$map.'" '.$arg.$options.'>';
		if(!empty($field->field_value)) {

			$app = JFactory::getApplication();
			$admin = hikashop_isClient('administrator');

			$values = array();
			foreach($field->field_value as $oneValue => $title) {
				$oneValue = htmlentities((string)$oneValue, ENT_COMPAT, 'UTF-8');
				$values[$oneValue] = $title;
			}
			if(is_array($value)) {
				$tmp = array();
				foreach($value as $k => $v) {
					$k = htmlentities((string)$k, ENT_COMPAT, 'UTF-8');
					$tmp[$k] = $v;
				}
				$value = $tmp;
				$keys = array_keys($values);
				$isValue = array_intersect($value, $keys);
				$isValue = !empty($isValue);
			} else {
				$value = htmlentities((string)$value, ENT_COMPAT, 'UTF-8');
				$isValue = !empty($value) && isset($values[$value]);
			}
			$selected = '';
			if(!empty($field->field_default))
				$field->field_default = htmlentities((string)$field->field_default, ENT_COMPAT, 'UTF-8');
			$config = hikashop_config();
			foreach($values as $oneValue => $title) {
				if(isset($field->field_default) && !$isValue) {
					if(array_key_exists($field->field_default, $values)) {
						$defaultValueEqualToCurrentValue = (is_numeric($field->field_default) && is_numeric($oneValue) && $oneValue == $field->field_default) || (is_string($field->field_default) && $oneValue === $field->field_default);
						if($defaultValueEqualToCurrentValue){
							$selected = ($defaultValueEqualToCurrentValue || is_array($field->field_default) && in_array($oneValue,$field->field_default)) ? 'selected="selected" ' : '';
						}else{
							$selected = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';
						}
					}
				} else {
					$selected = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';
					if(empty($selected) || $config->get('allow_disabled_selected_values_in_fields', 0))
						$selected .= ((is_numeric($value) && is_numeric($oneValue) && $oneValue == $value) || (is_string($value) && $oneValue === $value) || is_array($value) && in_array($oneValue,$value)) ? 'selected="selected" ' : '';
				}
				$id = $this->prefix.$field->field_namekey.$this->suffix.'_'.$oneValue;
				$string .= '<option value="'.$oneValue.'" id="'.$id.'" '.$selected.'>'.$this->trans($title->value).'</option>';
			}
		}

		$string .= '</select>';


		$string .= $this->addButton($field, $test);

		return $string;
	}

	function check(&$field, &$value, $oldvalue) {
		if(is_string($value))
			$value = trim($value);

		if(!$field->field_required || is_array($value) || strlen($value) || ($value === null && strlen($oldvalue)))
			return true;

		if($field->field_required && empty($field->field_value) && empty($value))
			return true;


		return parent::check($field, $value, $oldvalue);
	}
}

class hikashopFieldSingledropdown extends hikashopFieldDropdown {
	var $type = 'single';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
}

class hikashopFieldZone extends hikashopFieldSingledropdown {
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		if(is_string($field->field_options))
			$field->field_options = hikashop_unserialize($field->field_options);

		if($field->field_options['zone_type'] != 'country' || empty($field->field_options['pleaseselect'])) {
			$app = JFactory::getApplication();
			$currentZoneId = (int)$app->getUserState(HIKASHOP_COMPONENT.'.geoloc_zone_id', 0);
			if(!empty($currentZoneId) && !hikashop_isClient('administrator')) {
				$zoneClass = hikashop_get('class.zone');
				$currentZone = $zoneClass->getZoneParents($currentZoneId);
				foreach($currentZone as $currentZoneInfos){
					if(preg_match('/country/',$currentZoneInfos)){
						$defaultCountry = $currentZoneInfos;
					}
				}
			}
		}

		if($field->field_options['zone_type'] == 'country'){
			if(isset($defaultCountry)){
				$field->field_default = $defaultCountry;
			}

			if(!empty($field->field_options['pleaseselect'])){
				$PleaseSelect = new stdClass();
				$PleaseSelect->value = JText::_('PLEASE_SELECT_SOMETHING');
				$PleaseSelect->disabled = 0;
				$field->field_value = array_merge(array('' => $PleaseSelect), $field->field_value);
				$field->field_default = '';
			}

			$stateNamekey = str_replace('country','state',$field->field_namekey);
			$id = 0;
			if(!empty($allFields)) {
				foreach($allFields as &$f) {
					if($f->field_type == 'zone' && !empty($f->field_options['zone_type']) && $f->field_options['zone_type'] == 'state') {
						$stateNamekey = $f->field_namekey;
						$id = $f->field_id;
						break;
					}
				}
			}
			$stateId = str_replace(
				array('[',']',$field->field_namekey),
				array('_','',$stateNamekey),
				$map
			);
			$form_name = str_replace(array('data[',']['.$field->field_namekey.']'), '', $map);

			$changeJs = 'window.hikashop.changeState(this,\''.$stateId.'\',\''.$field->field_url.'field_type='.$form_name.'&field_id='.$stateId.'&field_namekey='.$stateNamekey.'&namekey=\'+this.value+\'&state_field_id=\'+'.(int)$id.');';
			if(!empty($options) && stripos($options,'onchange="')!==false){
				$options = preg_replace('#onchange="#i','onchange="'.$changeJs,$options);
			}else{
				$options = ' onchange="'.$changeJs.'"';
			}
			if($allFields == null || $allValues == null) {
				$doc = JFactory::getDocument();
				$lang = JFactory::getLanguage();
				$locale = strtolower(substr($lang->get('tag'),0,2));
				$js = 'window.hikashop.ready( function() {
	var el = document.getElementById(\''.$this->prefix.$field->field_namekey.$this->suffix.'\');
	window.hikashop.changeState(el,\''.$stateId.'\',\''.$field->field_url.'lang='.$locale.'&field_type='.$form_name.'&field_id='.$stateId.'&field_namekey='.$stateNamekey.'&namekey=\'+el.value+\'&state_field_id=\'+'.(int)$id.');
});';
				$doc->addScriptDeclaration($js);
			}
		} elseif($field->field_options['zone_type'] == 'state') {
			$stateId = str_replace(array('[',']'),array('_',''),$map);

			$dropdown = '';

			if(empty($field->field_options['pleaseselect']) && empty($value))
				$value = $field->field_default;

			if($allFields != null) {
				$country = null;
				if(isset($defaultCountry)){
					$country = $defaultCountry;
				}
				foreach($allFields as $f) {
					if($f->field_type == 'zone' && !empty($f->field_options['zone_type']) && $f->field_options['zone_type'] == 'country') {
						$key = $f->field_namekey;
						if(!empty($allValues->$key)) {
							$country = $allValues->$key;
						} elseif(empty($defaultCountry)) {
							$country = $f->field_default;
						}
						break;
					}
				}

				if(empty($country)) {
					$country_id = 13;
					if($field->field_id == 13)
						$country_id = 14;
					$address_country_field = $this->parent->get($country_id);
					if(!empty($address_country_field) && $address_country_field->field_type=='zone' && !empty($address_country_field->field_options['zone_type']) && $address_country_field->field_options['zone_type']=='country' && !empty($address_country_field->field_default)) {
						$country = $address_country_field->field_default;
					}
				}
				if(!empty($country)) {
					$countryType = hikashop_get('type.country');
					$countryType->field = $field;
					$dropdown = $countryType->displayStateDropDown($country, $stateId, $map, '', $value, $field->field_options);
				} else {
					$dropdown = '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
				}
			}

			return '<span id="'.$stateId.'_container">'.$dropdown.'</span>'.
				'<input type="hidden" id="'.$stateId.'_default_value" name="'.$stateId.'_default_value" value="'.$value.'"/>';
		}
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}

	function check(&$field,&$value,$oldvalue){
		if(is_string($value))
			$value = trim($value);
		$error_msg = '';

		if(!$field->field_required || is_array($value) || strlen($value) || ($value === null && strlen($oldvalue))) {
			if($field->field_type == 'zone' && $field->field_options['zone_type'] == 'state') {
				$country_field = 'address_country';
				if(!empty($this->formFields)) {
					foreach($this->formFields as $f) {
						if($f->field_type == 'zone' && !empty($f->field_options['zone_type']) && $f->field_options['zone_type'] == 'country') {
							$country_field = $f->field_namekey;
						}
					}
				}
				if(isset($this->formData[$country_field])) {
					$countryType = hikashop_get('type.country');
					$countryType->type = 'state';
					$countryType->published = true;
					$countryType->country_name = $this->formData[$country_field];
					$states = $countryType->load();
					if(!empty($field->field_options['pleaseselect'])) {
						$states[''] = '';
					}
					if(empty($states)) {
						$value == 'no_state_found';
					}elseif(!isset($states[$value])) {
						$error_msg = JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname));
					}
				}
			}

			if($value == 'no_state_found')
				$value = '';
		} else {
			$error_msg = JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname));
		}

		if(!empty($error_msg)) {
			if(!empty($this->report)) {
				if($this->report === true) {
					$app = JFactory::getApplication();
					$app->enqueueMessage($error_msg);
				} else {
					$this->parent->messages[] = $error_msg;
				}
			}
			return false;
		}
		return true;
	}
}

class hikashopFieldMultipledropdown extends hikashopFieldDropdown{
	var $type = 'multiple';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$value = explode(',',(string)$value);
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
	function show(&$field,$value){
		if(!is_array($value)){
			$value = explode(',',(string)$value);
		}
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		$results = array();
		foreach($value as $val){
			if(isset($field->field_value[$val])) $val = $field->field_value[$val]->value;
			$results[]= parent::show($field,$val);
		}
		$config = hikashop_config();
		return implode($config->get('fields_multiple_values_separator',', '),$results);
	}
}

class hikashopFieldRadioCheck extends hikashopFieldItem {
	var $radioType = 'checkbox';
	function show(&$field,$value) {
		if(!empty($field->field_value)){
			if(!is_array($field->field_value))
				$field->field_value = $this->parent->explodeValues($field->field_value);
			$values = array();
			foreach($field->field_value as $k => $v) {
				$oneValue = preg_replace("/&#?[a-z0-9]{2,8};/i","", htmlentities($k, ENT_COMPAT, 'UTF-8'));
				$values[$oneValue] = $v;
			}
			if(isset($values[$value])) $value = $values[$value]->value;
		}
		return parent::show($field,$value);
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$type = $this->radioType;
		$string = '';
		if($inside) $string = $this->trans($field->field_realname).' ';
		if($type == 'checkbox'){
			$string .= '<input type="hidden" name="'.$map.'" value=" "/>';
			$map .= '[]';
		}
		if(empty($field->field_value))
			return $string;

		if(is_null($value) || is_array($value) && count($value) == 1 && empty($value[0])){
			$value = $field->field_default;
			if($type == 'checkbox' && !is_array($value))
				$value = explode(',', (string)$value);
		}


		if(is_array($value)) {
			foreach($value as &$v) {
				$v = (string)htmlentities($v, ENT_COMPAT, 'UTF-8');
			}
			unset($v);
		}

		$app = JFactory::getApplication();
		$admin = hikashop_isClient('administrator');
		$use_bootstrap = $admin ? HIKASHOP_BACK_RESPONSIVE : HIKASHOP_RESPONSIVE;
		$class = 'hk'.$type;
		if(!empty($field->field_options['inline']))
			$class .= '-inline';

		if(strpos($options, 'class="') === false) {
			$options .= ' class="hkform-control"';
		} else {
			$options = str_replace(array('class="form-control'), array('class="hkform-control'), $options);
		}

		foreach($field->field_value as $oneValue => $title){
			$checked = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';

			$oneValue = (string)$oneValue;
			$oneValue = preg_replace("/&#?[a-z0-9]{2,8};/i","", htmlentities($oneValue, ENT_COMPAT, 'UTF-8'));
			$checked .= ((is_string($value) && $oneValue === $value) || is_array($value) && in_array($oneValue,$value)) ? 'checked="checked" ' : '';
			$id = $this->prefix.$field->field_namekey.$this->suffix.'_'.$oneValue;
			$options .= empty($field->field_options['attribute']) ? '' : $field->field_options['attribute'];

			if(!$use_bootstrap) {
				$string .= '<div class="'.$class.'"><label for="'.$id.'"><input type="'.$type.'" name="'.$map.'" value="'.$oneValue.'" id="'.$id.'" '.$checked.' '.$options.' /><span>'.$this->trans($title->value).'</span></label></div>';
			} else {
				$string .= '<label class="'.$class.'"><input type="'.$type.'" name="'.$map.'" value="'.$oneValue.'" id="'.$id.'" '.$checked.' '.$options.' /> <span>'.$this->trans($title->value).'</span></label>';
			}
		}

		$string .= $this->addButton($field, $test);
		return $string;
	}
}

class hikashopFieldRadio extends hikashopFieldRadioCheck {
	var $radioType = 'radio';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
}

class hikashopFieldBoolean extends hikashopFieldItem {
	function show(&$field, $value) {
		$value = JText::_( $value ? 'JYES' : 'JNO' );
		return parent::show($field, $value);
	}
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		if(is_null($value))
			$value = $field->field_default;
		$radioType = hikashop_get('type.radio');
		return $radioType->booleanlist($map, $options, !!$value);
	}
}

class hikashopFieldCheckbox extends hikashopFieldRadioCheck {
	var $radioType = 'checkbox';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		if(!is_array($value)){
			$value = explode(',',(string)$value);
		}
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
	function show(&$field,$value){
		if(!is_array($value)){
			$value = explode(',',(string)$value);
		}
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		$results = array();
		foreach($value as $val){
			$results[] = parent::show($field,$val);
		}
		$config = hikashop_config();
		return implode($config->get('fields_multiple_values_separator',', '),$results);
	}
}

class hikashopFieldDate extends hikashopFieldItem {
	var $displayFor = true;
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		if(empty($field->field_options['format'])) $field->field_options['format'] = "%Y-%m-%d";
		$format = $field->field_options['format'];
		$size = $options . empty($field->field_options['size']) ? '' : ' size="'.$field->field_options['size'].'"';

		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		elseif(!HIKASHOP_J40)
			JHTML::_('behavior.framework');
		$processing='';
		$message='';
		$check = 'false';
		if(HIKASHOP_J30 && !empty($field->field_options['allow'])){
			switch($field->field_options['allow']){
				case 'future':
					$check = 'today>selectedDate';
					$message = JText::_('SELECT_DATE_IN_FUTURE',true);
					$format .= '",'. "\r\n" . 'disableFunc: function(date) { var today=new Date(); today.setHours(0);today.setMinutes(0);today.setSeconds(0);today.setMilliseconds(0); if(date < today) { return true; } return false; }, //';
					break;
				case 'past':
					$check = 'today<selectedDate';
					$message = JText::_('SELECT_DATE_IN_PAST',true);
					$format .= '",'. "\r\n" . 'disableFunc: function(date) { var today=new Date(); today.setHours(23);today.setMinutes(59);today.setSeconds(59);today.setMilliseconds(99); if(date > today) { return true; } return false; }, //';
					break;
			}
		}

		if(!empty($check)) {
			$conversion = '';
			if($field->field_options['format'] != "%Y-%m-%d") {
				$seps = preg_replace('#[a-z0-9%]#iU','',$field->field_options['format']);
				$seps = str_replace(array('.','-'),array('\.','\-'),$seps);
				$mConv = false; $yP = -1; $mP = -1; $dP = -1; $i = 0;
				foreach(preg_split('#['.$seps.']#', $field->field_options['format']) as $d) {
					switch($d) {
						case '%y':
						case '%Y':
							if($yP<0) $yP = $i;
							break;
						case '%b':
						case '%B':
							$mConv = true;
						case '%m':
							if($mP<0) $mP = $i;
							break;
						case '%d':
						case '%e':
							if($dP<0) $dP = $i;
							break;
					}
					$i++;
				}
				$conversion .= '
				var reg = new RegExp("['.$seps.']+", "g");
				var elems = d.split(reg);
				';

				if($mConv) {
					$conversion .= 'for(var j=0;j<12;++j){if(Calendar._MN[j].substr(0,elems['.$mP.'].length).toLowerCase()==elems['.$mP.'].toLowerCase()){elems['.$mP.']=(j+1);break;}};
				';
				}

				$conversion .= 'd = elems['.$yP.'] + "-" + elems['.$mP.'] + "-" + elems['.$dP.'];
				';
			}
			$js = 'function '.$this->prefix.$field->field_namekey.$this->suffix.'_checkDate(nohide)
			{
				var selObj = document.getElementById(\''.$this->prefix.$field->field_namekey.$this->suffix.'\');
				if( typeof('.$this->prefix.$field->field_namekey.$this->suffix.'_preCheckDate) == "function" ) {
					try {
						if(!'.$this->prefix.$field->field_namekey.$this->suffix.'_preCheckDate(selObj))
							return false;
					} catch(ex) {}
				}
				if(selObj.value==\'\'){
					return true;
				}
				var d = selObj.value;'.$conversion.'
				var timestamp=Date.parse(d);
				var today=new Date();
				today.setHours(0);today.setMinutes(0);today.setSeconds(0);today.setMilliseconds(0);
				if(isNaN(timestamp)!=false){
					selObj.value=\'\';
					alert(\''.JText::_('INCORRECT_DATE_FORMAT',true).'\');
					return false;
				}
				var selectedDate = new Date(timestamp);
				selectedDate.setHours(0);selectedDate.setMinutes(0);selectedDate.setSeconds(0);selectedDate.setMilliseconds(0);

				'.$processing.'
				if('.$check.'){
					selObj.value=\'\';
					alert(\''.$message.'\');
				}else{
					if(!nohide) this.hide();
				}
				if( typeof('.$this->prefix.$field->field_namekey.$this->suffix.'_postCheckDate) == "function" ) {
					try{ '.$this->prefix.$field->field_namekey.$this->suffix.'_postCheckDate(selObj, selectedDate); } catch(ex){}
				}
			}';
			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);
			$size .= ' onChange="'.$this->prefix.$field->field_namekey.$this->suffix.'_checkDate(1);"';
		}

		if(!empty($value) && $field->field_options['format'] != "%Y-%m-%d") {
			$seps = preg_replace('#[a-z0-9%]#iU','',$field->field_options['format']);
			$seps = str_replace(array('.','-'),array('\.','\-'),$seps);
			$mConv = false; $yP = -1; $mP = -1; $dP = -1; $i = 0;
			foreach(preg_split('#['.$seps.']#', $field->field_options['format']) as $d) {
				switch($d) {
					case '%y':
					case '%Y':
						if($yP<0) $yP = $i;
						break;
					case '%b':
					case '%B':
						$mConv = true;
					case '%m':
						if($mP<0) $mP = $i;
						break;
					case '%d':
					case '%e':
						if($dP<0) $dP = $i;
						break;
				}
				$i++;
			}
			$elems = preg_split('#['.$seps.']#', $value);
			$value = @$elems[$yP] . '-' . @$elems[$mP] . '-' . @$elems[$dP];
			$app = Jfactory::getApplication();
			if(hikashop_isClient('administrator')) {
				$app->enqueueMessage('Since Joomla 2.5.24 it is not possible anymore to change the format of dates. If you need a different format, please use the advanced datepicker type of custom field.');
			}
			$format = "%Y-%m-%d";
			$field->field_options['format'] = $format;
		}
		if(!empty($value)) {
			try{
				JHTML::_('date', $value, null, null);
			}catch(Exception $e) {
				$value = '';
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onFieldDateDisplay', array($field->field_namekey, $field, &$value, &$map, &$format, &$size));

		return JHTML::_('calendar', $value, $map,$this->prefix.$field->field_namekey.$this->suffix,$format,$size);
	}

	function showfield($viewObj, $namekey, $row) {
		if( isset( $row->$namekey)) {
			$date_format = !empty( $this->field_options['format']) ? $this->field_options['format'] : '%Y-%m-%d %H:%M:%S';
			return hikashop_getDate( $row->$namekey, $date_format);
		}
		return '';
	}
}
