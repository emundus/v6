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
class productmarketViewProductmarket extends hikamarketView {

	const ctrl = 'product';
	const name = 'HIKAMARKET_PRODUCTMARKET';
	const icon = 'thumbs-up';

	protected $triggerView = true;

	public function display($tpl = null, $params = null) {
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		$ret = true;
		if(method_exists($this, $fct))
			$ret = $this->$fct($params);
		if($ret !== false)
			parent::display($tpl);
	}

	public function shop_block($params = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'currencyClass' => 'shop.class.currency',
			'currencyType' => 'shop.type.currency',
			'popup' => 'shop.helper.popup'
		));

		$data = null;
		$product_id = 0;
		$product_type = 'main';

		if(!empty($params)) {
			$product_id = (int)$params->get('product_id');
			$product_type = $params->get('product_type');
		}

		if(hikamarket::level(1) && $product_id > 0) {
			$feeClass = hikamarket::get('class.fee');
			$data = $feeClass->getProduct($product_id);
			foreach($data as $k => $v) {
				if($v->fee_target_id != $product_id)
					unset($data[$k]);
			}
		}

		$this->assignRef('data', $data);
		$this->assignRef('product_id', $product_id);
		$this->assignRef('product_type', $product_type);
	}

	public function shop_form($params = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'nameboxType' => 'type.namebox',
			'popup' => 'shop.helper.popup'
		));

		$product_type = 'main';
		$product_id = 0;
		$product_vendor_id = 0;

		if(!empty($params)) {
			$product_id = (int)$params->get('product_id');
			$product_vendor_id = (int)$params->get('product_vendor_id');
			$product_type = $params->get('product_type');
		}

		$this->assignRef('product_id', $product_id);
		$this->assignRef('product_vendor_id', $product_vendor_id);
		$this->assignRef('product_type', $product_type);
	}

	public function waitingapproval() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.waitingapproval';
		hikamarket::setTitle(JText::_('WAITING_APPROVAL_LIST'), self::icon, self::ctrl.'&task=waitingapproval');

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'toggleHelper' => 'helper.toggle',
			'imageHelper' => 'shop.helper.image',
			'currencyHelper' => 'shop.class.currency',
			'childdisplayType' => 'shop.type.childdisplay',
			'shopCategoryType' => 'type.shop_category',
		));

		$manage = hikamarket::acl('product/edit');
		$this->assignRef('manage', $manage);

		$product_action_delete = hikamarket::acl('product/delete');
		$this->assignRef('product_action_delete', $product_action_delete);

		$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('product&task=waitingapproval')));
		$this->assignRef('cancelUrl', $cancelUrl);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$cfg = array(
			'table' => 'shop.product',
			'main_key' => 'product_id',
			'order_sql_value' => 'product.product_id'
		);

		$vendorType = hikamarket::get('type.filter_vendor');
		$this->assignRef('vendorType', $vendorType);

		$default_sort_value = trim($config->get('product_listing_default_sort_value', $cfg['order_sql_value']));
		if(empty($default_sort_value))
			$default_sort_value = $cfg['order_sql_value'];
		$default_sort_dir = trim($config->get('product_listing_default_sort_dir', 'asc'));
		if(empty($default_sort_dir) || !in_array($default_sort_dir, array('asc', 'desc')))
			$default_sort_dir = 'asc';

		$listing_filters = array(
			'vendors' => -1,
			'published' => -1,
		);

		$pageInfo = $this->getPageInfo($default_sort_value, $default_sort_dir, $listing_filters);

		$filters = array(
			'main' => 'product.product_parent_id = 0',
			'product_type' => 'product.product_type = \'waiting_approval\''
		);
		$searchMap = array(
			'product.product_name',
			'product.product_description',
			'product.product_id',
			'product.product_code'
		);
		$select = array();
		$join = '';

		if($pageInfo->filter->vendors >= 0) {
			$select['parent_product_name'] = 'parent_product.product_name as parent_product_name';
			$join = ' LEFT JOIN '.hikamarket::table('shop.product').' AS parent_product ON product.product_parent_id = parent_product.product_id AND parent_product.product_vendor_id != product.product_vendor_id AND product.product_vendor_id > 0 ';
		}
		if($pageInfo->filter->published >= 0) {
			$filters['published'] = 'product.product_published = ' . ($pageInfo->filter->published ? '1' : '0');
		}

		$fieldsClass = hikamarket::get('shop.class.field');
		$categories = array();
		$fields = $fieldsClass->getData('backend_listing', 'product', false, $categories);
		$this->assignRef('fields', $fields);
		$this->assignRef('fieldsClass', $fieldsClass);

		foreach($fields as $fieldName => $oneExtraField) {
			$searchMap[] = 'product.' . $fieldName;
		}

		if($pageInfo->filter->vendors == 0) {
			$filters['main'] .= ' OR parent_product.product_id != 0 ) AND (product.product_vendor_id IN (0, 1)';
		} elseif( $pageInfo->filter->vendors > 1) {
			$filters['main'] .= ' OR parent_product.product_id != 0 ) AND (product.product_vendor_id = '.(int)$pageInfo->filter->vendors;
		}

		$order = '';
		$this->processFilters($filters, $order, $searchMap, array('product.'));

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS product '.$join.$filters.$order;
		$db->setQuery('SELECT DISTINCT product.*' . (empty($select)?'':',') . implode(',', $select) . ' ' . $query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList('product_id');

		$products = array();
		$vendor_ids = array();
		foreach($rows as &$product) {
			$product->prices = array();
			$product->file_name = $product->product_name;
			if(!isset($products[$product->product_id])) {
				$products[$product->product_id] =& $product;
			} else if(!is_array($products[$product->product_id])) {
				$old =& $products[$product->product_id];
				unset($products[$product->product_id]);
				$products[$product->product_id] = array(&$old, &$product);
			} else {
				$products[$product->product_id][] =& $product;
			}

			if((int)$product->product_vendor_id > 0)
				$vendor_ids[ (int)$product->product_vendor_id ] = (int)$product->product_vendor_id;
		}
		unset($product);
		$this->assignRef('products', $rows);

		$this->loadPricesImages($products);

		$db->setQuery('SELECT COUNT(DISTINCT(product.product_id)) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$vendors = array();
		if(!empty($vendor_ids)) {
			$query = 'SELECT vendor_id, vendor_name FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN ('.implode(',', $vendor_ids).')';
			$db->setQuery($query);
			$vendors = $db->loadObjectList('vendor_id');
		}
		$this->assignRef('vendors', $vendors);

		$this->toolbar = array(
			'approve' => array('name' => 'custom', 'icon' => 'publish', 'alt' => JText::_('HIKAM_APPROVE'), 'task' => 'approve', 'display' => $manage),
			'decline' => array(
				'name' => 'custom', 'icon' => 'unpublish', 'alt' => JText::_('HIKAM_DECLINE'), 'task' => 'decline',
				'display' => $manage && hikamarket::level(1)
			),
			'delete' => array(
				'name' => 'deleteList',
				'display' => $manage
			),
			'|',
			array('name' => 'pophelp', 'target' => 'vendor'),
			'dashboard'
		);
		$this->getPagination();

		$this->getOrdering('a.ordering', true);
		if(!empty($this->ordering->ordering)) {
			$this->toolbar['ordering']['display'] = true;
		}

		return true;
	}

	private function loadPricesImages(&$products) {
		if(empty($products))
			return;

		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikamarket::table('shop.price').' WHERE price_product_id IN ('.implode(',', array_keys($products)).')');
		$prices = $db->loadObjectList();
		if(!empty($prices)) {
			foreach($prices as $price) {
				if(!isset($products[$price->price_product_id]) )
					continue;

				if(!is_array($products[$price->price_product_id])) {
					$products[$price->price_product_id]->prices[] = $price;
				} else {
					foreach($products[$price->price_product_id] as $p) {
						$p->prices[] = $price;
					}
				}
			}
		}
		unset($prices);

		$db->setQuery('SELECT * FROM '.hikamarket::table('shop.file').' WHERE file_ref_id IN ('.implode(',', array_keys($products)).') AND file_type=\'product\' ORDER BY file_ref_id ASC, file_ordering ASC, file_id ASC');
		$images = $db->loadObjectList();
		if(!empty($images)) {
			foreach($images as $image) {
				if(!isset($products[(int)$image->file_ref_id]))
					continue;

				if(!is_array($products[(int)$image->file_ref_id])) {
					if(isset($products[(int)$image->file_ref_id]->file_ref_id))
						continue;

					foreach(get_object_vars($image) as $key => $name) {
						$products[(int)$image->file_ref_id]->$key = $name;
					}
				} else {
					$p = reset($products[(int)$image->file_ref_id]);
					if(isset($p->file_ref_id))
						continue;

					foreach($products[(int)$image->file_ref_id] as $p) {
						foreach(get_object_vars($image) as $key => $name) {
							$p->$key = $name;
						}
					}
				}
			}
		}
	}

	public function confirm_action() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$product_ids = hikaInput::get()->get('cid', array(), 'array');
		hikamarket::toInteger($product_ids);

		$url_params = '&task='.hikaInput::get()->getCmd('task');
		foreach($product_ids as $pid) {
			$url_params .= '&cid[]=' . $pid;
		}
		$url_params .= '&redirect='.hikaInput::get()->getCmd('redirect', '');

		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.waitingapproval';
		hikamarket::setTitle(JText::_('WAITING_APPROVAL_LIST'), self::icon, self::ctrl.$url_params);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'currencyClass' => 'shop.class.currency',
			'imageHelper' => 'shop.helper.image',
			'productClass' => 'shop.class.product',
		));

		$action = hikaInput::get()->getCmd('action_to_confirm', null);
		if(empty($action) || !in_array($action, array('remove', 'approve', 'decline'))) {
			$app->enqueueMessage(JText::_('INVALID_DATA'), 'error');
			$app->redirect(hikamarket::completeLink('dashboard', false, true));
		}

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$query = 'SELECT p.*, v.vendor_name '.
				' FROM '.hikamarket::table('shop.product').' as p '.
				' LEFT JOIN '.hikamarket::table('vendor').' AS v ON p.product_vendor_id = v.vendor_id '.
				' WHERE p.product_id IN ('.implode(',', $product_ids).') '.
					' AND p.product_type = '.$db->Quote('waiting_approval');
		$db->setQuery($query);
		$products = $db->loadObjectList('product_id');

		$this->productClass->loadProductsListingData($products, array(
			'load_badges' => false,
			'load_custom_product_fields' => false,
			'load_custom_item_fields' => false,
			'price_display_type' => 'cheapest'
		));

		asort($product_ids);
		$confirmation = md5($action.'{'.implode(':',$product_ids).'}');

		$this->assignRef('action', $action);
		$this->assignRef('ids', $product_ids);
		$this->assignRef('products', $products);
		$this->assignRef('confirmation', $confirmation);

		$toolbar_icon = 'publish';
		if($action == 'remove')
			$toolbar_icon = 'remove';
		else if($action == 'decline')
			$toolbar_icon = 'unpublish';

		$this->toolbar = array(
			array('name' => 'custom', 'icon' => $toolbar_icon, 'alt' => JText::_('HIKAM_CONFIRM'), 'task' => $action),
			array('name' => 'link', 'icon' => 'back', 'url' => hikamarket::completeLink('product&task=waitingapproval'), 'alt' => JText::_('HIKA_CANCEL')),
			'|',
			'dashboard'
		);
	}
}
