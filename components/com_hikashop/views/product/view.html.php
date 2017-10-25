<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class ProductViewProduct extends HikaShopView {
	var $type = 'main';
	var $ctrl = 'product';
	var $nameListing = 'PRODUCTS';
	var $nameForm = 'PRODUCTS';
	var $icon = 'product';
	var $module = false;
	var $triggerView = true;

	public function display($tpl = null, $params = array()) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params =& $params;

		$this->handleAddToCartPopup($function);

		if(method_exists($this, $function))
			$this->$function();
		parent::display($tpl);
	}

	public function getJS() {
		static $done = false;
		if($done)
			return '';
		$done = true;

		$config = hikashop_config();
		if( !$config->get('add_to_cart_legacy', true) )
			return '';

		$cartHelper = hikashop_get('helper.cart');
		$js = $cartHelper->getJS($this->init());
		if(!empty($js))
			$this->addToCartJs = $js;

		$js = 'window.hikashop.ready(function(){ SqueezeBox.fromElement("hikashop_notice_box_trigger_link",{parse: "rel"}); });';
		return $js;
	}

	public function filter() {
	}

	public function listing() {
		$app = JFactory::getApplication();
		$database = JFactory::getDBO();
		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$module = hikashop_get('helper.module');
		$module->initialize($this);

		$this->paramBase .= '_' . $this->params->get('main_div_name');

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = '';
		$this->assignRef('pageInfo', $pageInfo);

		$filters = array('b.product_published = 1');
		$category_selected = '';
		$select = '';
		$is_synchronized = false;
		$table = 'b';
		$defaultParams = $config->get('default_params');

		if(empty($defaultParams['add_to_wishlist']))
			$defaultParams['add_to_wishlist'] = 0;

		$params = array(
			'price_display_type' => 'inherit',
			'random' => '-1',
			'limit' => '',
			'product_synchronize' => 4,
			'div_item_layout_type' => 'inherit',
			'columns' => '',
			'margin' => '',
			'text_center' => '-1',
			'border_visible' => '-1',
			'link_to_product_page' => '-1',
			'show_price' => '-1',
			'add_to_cart' => '-1',
			'add_to_wishlist' => '-1',
			'layout_type' => 'inherit',
			'display_badges' => '-1',
			'show_discount' => '3',
			'show_quantity_field' => '-1',
			'display_custom_item_fields' => '-1'
		);

		$data = $this->params->get('data',new stdClass());
		$moduleData = $this->params->get('hikashopmodule');

		if(isset($data->hk_product) && is_object($data->hk_product)) {
			if(!empty($data->hk_product->category))
				$this->params->set('selectparentlisting', (int)$data->hk_product->category);
		}
		elseif(isset($moduleData) && is_object($moduleData)) {
			foreach($moduleData as $k => $v) {
				$this->params->set($k, $v);
			}
		}
		else {
			if(HIKASHOP_J30)
				$this->params->set('content_synchronize', 1);
			$this->params->set('recently_viewed', 0);
		}

		foreach($params as $k => $v) {
			if($this->params->get($k, $v) == $v)
				$this->params->set($k, @$defaultParams[$k]);
		}

		if($this->params->get('product_order', 'inherit') == 'inherit') {
			if(!isset($defaultParams['product_order']) || $defaultParams['product_order'] == '' || $defaultParams['product_order'] == 'inherit')
				$defaultParams['product_order'] = 'ordering';
			$this->params->set('product_order', $defaultParams['product_order']);
		}
		if($this->params->get('order_dir', 'inherit') == 'inherit' || $this->params->get('order_dir', 'inherit') == '') {
			$this->params->set('order_dir', @$defaultParams['order_dir']);
			if($this->params->get('order_dir', 'inherit') == 'inherit' || $this->params->get('order_dir', 'inherit') == '')
				$this->params->set('order_dir', 'ASC');
		}
		if($this->params->get('show_quantity_field', 0) == 1)
			$this->params->set('show_quantity_field', 1);
		if((int)$this->params->get('limit') == 0)
			$this->params->set('limit', 1);

		if((int)$this->params->get('infinite_scroll', 0) == 1 && (int)$this->params->get('random', 0) == 0 && (int)$this->params->get('enable_carousel', 0) == 0) {
			$tmpl = hikaInput::get()->getCmd('tmpl', '');
			if(in_array($tmpl, array('ajax', 'raw'))) {
				$this->tmpl_ajax = true;
			}
		}

		if($config->get('enable_status_vote') == 'nothing')
			$this->params->set('show_vote_product', 0);

		if($this->params->get('product_order', 'ordering') == 'ordering')
			$table = 'a';

		$this->loadRef(array(
			'fieldsClass' => 'class.field',
			'quantityDisplayType' => 'type.quantitydisplay',
			'badgeClass' => 'class.badge',
			'currencyClass' => 'class.currency',
			'toggleHelper' => 'helper.toggle',
			'imageHelper' => 'helper.image',
		));

		$this->currencyHelper = $this->currencyClass;
		$this->toggleClass = $this->toggleHelper;
		$this->image = $this->imageHelper;
		$this->classbadge = $this->badgeClass;

		if(!empty($this->module)) {
			$pageInfo->search = '';
			$force_recently_viewed = false;

			$pageInfo->filter->order->dir = $this->params->get('order_dir','ASC');
			$pageInfo->filter->order->value = $table.'.'.$this->params->get('product_order','ordering');

			$synchro = $this->params->get('content_synchronize');
			if($this->params->get('recently_viewed','-1')=='-1'){
				$this->params->set('recently_viewed',@$defaultParams['recently_viewed']);
			}
			$recently_viewed = (int)$this->params->get('recently_viewed',0);
			if($synchro) {
				if(hikaInput::get()->getString('option','') == HIKASHOP_COMPONENT && hikaInput::get()->getString('ctrl', 'category') == 'product' && hikaInput::get()->getString('task', 'listing') == 'show') {
					$product_synchronize = (int)$this->params->get('product_synchronize',0);
					if($product_synchronize) {
						$product_id = hikashop_getCID('product_id');
						if(!empty($product_id)) {
							$pageInfo->filter->cid = $this->params->get('selectparentlisting');
							if($product_synchronize == 2) {
								$filters[] = 'a.product_related_type=\'related\'';
								$filters[] = 'a.product_id='.$product_id;
								$select = 'SELECT DISTINCT b.*';
								$b = hikashop_table('product_related').' AS a LEFT JOIN ';
								$a = hikashop_table('product').' AS b';
								$on = ' ON a.product_related_id=b.product_id';
								if($this->params->get('product_order') == 'ordering')
									$pageInfo->filter->order->value = 'a.product_related_ordering';
							}elseif($product_synchronize == 3) {
								$query = "SELECT product_manufacturer_id FROM ".hikashop_table('product').' WHERE product_id='.$product_id.' OR product_parent_id='.$product_id;
								$database->setQuery($query);
								$filters[] = 'b.product_manufacturer_id ='.(int)$database->loadResult();
								$filters[] = 'b.product_id!='.$product_id;
								$filters[] = 'b.product_parent_id!='.$product_id;
								$select = 'SELECT DISTINCT b.*';
								$b = '';
								$on = '';
								$a = hikashop_table('product').' AS b';
								$pageInfo->filter->order->value = '';
							}elseif($product_synchronize == 4) {
								$filters[] = 'b.product_parent_id='.$product_id;
								$select = 'SELECT DISTINCT b.*';
								$b = '';
								$on = '';
								$a = hikashop_table('product').' AS b';
								$this->type = 'variant';
							} else {
								$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
								$pathway = hikaInput::get()->getInt($pathway_sef_name,0);
								$filters[] = 'b.product_id!='.$product_id;
								$filters[] = 'b.product_parent_id!='.$product_id;
								if(empty($pathway)) {
									$query = "SELECT a.category_id FROM ".hikashop_table('product_category').' AS a INNER JOIN '.hikashop_table('category').' AS b ON a.category_id=b.category_id WHERE b.category_published=1 AND a.product_id='.$product_id.' ORDER BY a.product_category_id ASC';
									$database->setQuery($query);
									if(!HIKASHOP_J25){
										$pageInfo->filter->cid = $database->loadResultArray();
									} else {
										$pageInfo->filter->cid = $database->loadColumn();
									}
								} else {
									$pageInfo->filter->cid = array($pathway);
								}
							}
						}
					}
				} elseif(hikaInput::get()->getString('option','') == HIKASHOP_COMPONENT && in_array(hikaInput::get()->getString('ctrl', 'category'),array('category','product')) && hikaInput::get()->getString('task', 'listing') == 'listing') {
					$pageInfo->filter->cid = hikaInput::get()->getInt("cid",$this->params->get('selectparentlisting'));
					$is_synchronized = true;
				} else {
					$pageInfo->filter->cid = $this->params->get('selectparentlisting');
				}
			}elseif($recently_viewed){
				$force_recently_viewed = true;
			}else{
				$pageInfo->filter->cid = $this->params->get('selectparentlisting');
			}

			if(!empty($pageInfo->filter->cid) && !is_array($pageInfo->filter->cid)){
				$category_selected = '_'.$pageInfo->filter->cid;
				$this->paramBase.=$category_selected;
			}
			$pageInfo->filter->price_display_type = $this->params->get('price_display_type');
			if(hikaInput::get()->getVar('hikashop_front_end_main',0)){
				$oldValue = $app->getUserState($this->paramBase.'.list_limit');
				if(empty($oldValue)){
					$oldValue = $this->params->get('limit');
				}
				if($config->get('redirect_post',0)){
					if(isset($_REQUEST['limit_'.$this->params->get('main_div_name').$category_selected])){
						$pageInfo->limit->value = hikaInput::get()->getInt('limit_'.$this->params->get('main_div_name').$category_selected);
					}elseif(isset($_REQUEST['limit']) && (empty($this->module) || hikaInput::get()->getVar('hikashop_front_end_main',0))){
						$pageInfo->limit->value = hikaInput::get()->getInt('limit');
					}else{
						$pageInfo->limit->value = $this->params->get('limit');
					}
					hikaInput::get()->set('limit_'.$this->params->get('main_div_name').$category_selected,$pageInfo->limit->value);
					hikaInput::get()->set('limit',$pageInfo->limit->value);
				}else{
					$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit_'.$this->params->get('main_div_name').$category_selected, $this->params->get('limit'), 'int' );
				}
				if($oldValue!=$pageInfo->limit->value){
					hikaInput::get()->set('limitstart_'.$this->params->get('main_div_name').$category_selected,0);
					hikaInput::get()->set('limitstart',0);
				}
			}else{
				$pageInfo->limit->value = $this->params->get('limit');
				$pageInfo->limit->start = 0;
			}
			if($pageInfo->limit->value < 0)
				$pageInfo->limit->value = 1;
			if($force_recently_viewed){
				$i = $pageInfo->limit->value;
				if(!empty($_SESSION['hikashop_viewed_products'])){
					$viewed_products_ids = $_SESSION['hikashop_viewed_products'];
					if(hikaInput::get()->getString('option','')==HIKASHOP_COMPONENT && hikaInput::get()->getString('ctrl','category')=='product'){
						$product_id = hikashop_getCID('product_id');
						if(isset($viewed_products_ids[$product_id])){
							unset($viewed_products_ids[$product_id]);
						}
					}
					$ids_for_the_query = array();
					for($i=$pageInfo->limit->value;$i>0 && count($viewed_products_ids);$i--){
						$ids_for_the_query[]=array_shift($viewed_products_ids);
					}
					if(count($ids_for_the_query)){
						$filters[]='b.product_id IN ('.implode(',',$ids_for_the_query).')';
					}else{
						$filters[]='b.product_id=0';
					}
				}else{
					$filters[]='b.product_id=0';
				}
				$select='SELECT DISTINCT b.*';
				$b = '';
				$on = '';
				$a = hikashop_table('product').' AS b';
				if($this->params->get('product_order')=='ordering'){
					$pageInfo->filter->order->value = 'b.product_id';
				}
			}
		} else {
			$doc = JFactory::getDocument();
			$pageInfo->filter->cid = hikaInput::get()->getInt("cid", $this->params->get('selectparentlisting'));
			if($config->get('show_feed_link', 1) == 1) {
				if($config->get('hikarss_format') != 'none') {
					$doc_title = $config->get('hikarss_name','');
					if(empty($doc_title)) {
						$categoryClass = hikashop_get('class.category');
						$catData = $categoryClass->get($pageInfo->filter->cid);
						if($catData) $doc_title = $catData->category_name;
					}
					if($config->get('hikarss_format') != 'both') {
						$link	= '&format=feed&limitstart=';
						$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
						$doc->addHeadLink(JRoute::_($link.'&type='.$config->get('hikarss_format')), 'alternate', 'rel', $attribs);
					} else {
						$link = '&format=feed&limitstart=';
						$attribs = array('type' => 'application/rss+xml', 'title' => $doc_title.' RSS 2.0');
						$doc->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
						$attribs = array('type' => 'application/atom+xml', 'title' => $doc_title.' Atom 1.0');
						$doc->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
					}
				}
			}

			$category_selected = '_'.$pageInfo->filter->cid;
			$this->paramBase .= $category_selected;
			if(empty($pageInfo->filter->order->value) && !is_numeric($this->params->get('product_order','ordering')))
				$pageInfo->filter->order->value = $table.'.'.$this->params->get('product_order','ordering');
			$pageInfo->filter->order->dir = $this->params->get('order_dir','ASC');

			$oldValue = $app->getUserState($this->paramBase.'.list_limit');
			if(empty($oldValue))
				$oldValue = $this->params->get('limit');
			if($config->get('redirect_post',0)){
				if(isset($_REQUEST['limit_'.$this->params->get('main_div_name').$category_selected])){
					$pageInfo->limit->value = hikaInput::get()->getInt('limit_'.$this->params->get('main_div_name').$category_selected);
				}elseif(isset($_REQUEST['limit']) && (empty($this->module) || hikaInput::get()->getVar('hikashop_front_end_main',0))){
					$pageInfo->limit->value = hikaInput::get()->getInt('limit');
				}else{
					$pageInfo->limit->value = $this->params->get('limit');
				}
				hikaInput::get()->set('limit_'.$this->params->get('main_div_name').$category_selected,$pageInfo->limit->value);
				hikaInput::get()->set('limit',$pageInfo->limit->value);
				$app->setUserState($this->paramBase.'.list_limit',$pageInfo->limit->value);
			}else{
				$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit_'.$this->params->get('main_div_name').$category_selected, $this->params->get('limit'), 'int' );
			}
			if($oldValue!=$pageInfo->limit->value){
				hikaInput::get()->set('limitstart_'.$this->params->get('main_div_name').$category_selected,0);
				hikaInput::get()->set('limitstart',0);
			}

			$pageInfo->filter->price_display_type = $app->getUserStateFromRequest( $this->paramBase.'.price_display_type', 'price_display_type_'.$this->params->get('main_div_name').$category_selected, $this->params->get('price_display_type'), 'word' );
		}

		$this->assignRef('category_selected',$category_selected);

		$pageInfo->currency_id = hikashop_getCurrency();
		$pageInfo->zone_id = hikashop_getZone(null);
		$this->params->set('show_price_weight', (int)$config->get('show_price_weight', 0));

		if(hikashop_level(2))
			$this->params->set('show_compare', (int)$config->get('show_compare', 0));
		else
			$this->params->set('show_compare', 0);

		if(!empty($pageInfo->filter->cid)) {
			if(is_array($pageInfo->filter->cid)){
				JArrayHelper::toInteger($pageInfo->filter->cid);
			}
			$acl_filters = array();
			hikashop_addACLFilters($acl_filters,'category_access');
			if(!empty($acl_filters)){
				if(!is_array($pageInfo->filter->cid)){
					$pageInfo->filter->cid = array($database->Quote($pageInfo->filter->cid));
				}
				$acl_filters[]='category_id IN ('.implode(',',$pageInfo->filter->cid).')';
				$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$acl_filters);
				$database->setQuery($query);
				if(!HIKASHOP_J25){
					$pageInfo->filter->cid = $database->loadResultArray();
				} else {
					$pageInfo->filter->cid = $database->loadColumn();
				}
			}
		}

		if(empty($pageInfo->filter->cid)){
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND category_parent_id=0 LIMIT 1';
			$database->setQuery($query);
			$pageInfo->filter->cid = $database->loadResult();
		}
		$searchMap = array('b.product_name','b.product_description','b.product_id','b.product_code');

		$filters[]='b.product_type = '.$database->Quote($this->type);
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		if(!is_array($pageInfo->filter->cid))
			$pageInfo->filter->cid = array( (int)$pageInfo->filter->cid );

		$this->assignRef('pageInfo',$pageInfo);

		if(hikashop_level(2))
			$this->filter();

		$categoryClass = hikashop_get('class.category');
		$element = $categoryClass->get(reset($pageInfo->filter->cid),true);
		$this->assignRef('element', $element);

		if(empty($select)) {
			$parentCategories = implode(',',$pageInfo->filter->cid);
			$catName = 'a.category_id';
			$type = 'product';

			if(!empty($element->category_type) && $element->category_type == 'manufacturer') {
				if($pageInfo->filter->order->value=='a.ordering' || $pageInfo->filter->order->value=='b.ordering'){
					$pageInfo->filter->order->value='b.product_name';
				}
				$type = 'manufacturer';
				$catName = 'b.product_manufacturer_id';
				$b = '';
				$a = hikashop_table('product').' AS b';
				$on = '';
				$select='SELECT DISTINCT b.*';
			} else {
				if($pageInfo->filter->order->value == 'b.ordering') {
					$pageInfo->filter->order->value = 'a.ordering';
				}
				$b = hikashop_table('product_category').' AS a LEFT JOIN ';
				$a = hikashop_table('product').' AS b';
				$on = ' ON a.product_id=b.product_id';
				$select='SELECT DISTINCT b.*';
			}
			if((int)$this->params->get('filter_type', 2) == 2) {
				$defaultParams = $config->get('default_params');
				$this->params->set('filter_type',$defaultParams['filter_type']);
			}

			if(!$this->params->get('filter_type')) {
				if(!empty($parentCategories) && $parentCategories != '0')
					$filters[] = $catName.' IN ('.$parentCategories.')';
			} else {
				$categoryClass->parentObject =& $this;
				$categoryClass->type = $type;

				$children = $categoryClass->getChildren($pageInfo->filter->cid,true,array(),'',0,0);
				$filter = $catName.' IN (';
				foreach($children as $child){
					$filter .= $child->category_id.',';
				}
				$filters[]=$filter.$parentCategories.')';
			}
		}

		$cart_related_only = (int)$this->params->get('related_products_from_cart', 0);
		if($cart_related_only) {
			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->getFullCart();
			$ids = '0';
			if(!empty($cart->products) && count($cart->products)){
				$mainIds = array();
				foreach($cart->products as $product) {
					$mainIds[] = (empty($product->product_parent_id) || (int)$product->product_parent_id == 0) ? (int)$product->product_id : (int)$product->product_parent_id;
				}
				if(count($mainIds)){
					$queryDiscount = 'SELECT DISTINCT product_related_id FROM #__hikashop_product_related WHERE product_related_type=\'related\' AND product_id IN ('.implode(',',$mainIds).')';
					$database->setQuery($queryDiscount);
					if(!HIKASHOP_J25)
						$related_products = $database->loadResultArray();
					else
						$related_products = $database->loadColumn();
					if(count($related_products)){
						$ids = implode(',',$related_products);
					}
				}
			}
			$filters[] = 'b.product_id IN ('.$ids.')';

		}

		$discounted_only = (int)$this->params->get('discounted_only', 0);
		if($discounted_only) {
			$discount_filter = array(
				'discount.discount_type = ' . $database->Quote('discount'),
				'discount.discount_published = 1',
				'(discount.discount_quota = 0 OR discount.discount_quota > discount.discount_used_times)',
				'discount.discount_start < '.time(),
				'(discount.discount_end = 0 OR discount.discount_end > '.time().')',
			);

			hikashop_addACLFilters($discount_filter, 'discount_access', 'discount', 2, false);

			$queryDiscount = 'SELECT MAX(discount_product_id) as max_product_id, MAX(discount_category_id) as max_category_id, MAX(discount_category_childs) as max_category_children, MAX(discount_zone_id) as max_zone_id '.
							' FROM '.hikashop_table('discount').' as discount  WHERE ('.implode(') AND (',$discount_filter).')';
			$database->setQuery($queryDiscount);
			$discounts = $database->loadObject();

			$join_discount_links = array('discount.discount_product_id = \'\' AND discount.discount_category_id = \'\'');

			if(!empty($discounts->max_product_id)) {
				$join_discount_links[] = 'discount.discount_product_id = b.product_id';
				$join_discount_links[] = 'discount.discount_product_id != \'\' AND discount.discount_product_id LIKE CONCAT(\'%,\', b.product_id, \',%\')';
			}

			if(!empty($discounts->max_category_id)) {
				$on .=  ' LEFT JOIN '.hikashop_table('product_category').' AS discount_cat ON discount_cat.product_id = b.product_id ';

				$join_discount_links[] = 'discount.discount_product_id = \'\' AND discount.discount_category_id = discount_cat.category_id';
				$join_discount_links[] = 'discount.discount_product_id = \'\' AND discount.discount_category_id != \'\' AND discount.discount_category_id LIKE CONCAT(\'%,\', discount_cat.category_id, \',%\')';

				if(!empty($discounts->max_category_children)) {
					$on .= ' LEFT JOIN '.hikashop_table('category').' AS child_category '.
								' ON child_category.category_id = discount_cat.category_id '.
							' LEFT JOIN '.hikashop_table('category').' AS parent_category '.
								' ON (parent_category.category_left <= child_category.category_left AND parent_category.category_right >= child_category.category_right)';

					$join_discount_links[] = 'discount.discount_product_id = \'\' AND discount.discount_category_id != \'\' AND discount.discount_category_childs = 1 AND discount.discount_category_id LIKE CONCAT(\'%,\', parent_category.category_id, \',%\')';
				}
			}

			if(!empty($discounts->max_product_id) || !empty($discounts->max_category_id) || !empty($discounts->max_category_children) || !empty($discounts->max_zone_id)) {
				$on .= ' LEFT JOIN '.hikashop_table('discount').' AS discount '.
					' ON (('. implode(') OR (', $join_discount_links) . '))';

				$filters[] = 'discount.discount_type = ' . $database->Quote('discount');
				$filters[] = 'discount.discount_published = 1';
				$filters[] = '(discount.discount_quota = 0 OR discount.discount_quota > discount.discount_used_times)';
				$filters[] = 'discount.discount_start < '.time().'';
				$filters[] = '(discount.discount_end = 0 OR discount.discount_end > '.time().')';

				hikashop_addACLFilters($filters, 'discount_access', 'discount', 2, false);

				if(!empty($discounts->max_zone_id)) {
					$zones = array();
					$zone_id = hikashop_getZone(null);
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getZoneParents($zone_id);

					if(!empty($zones)) {
						foreach($zones as &$zone) {
							$zone = hikashop_getEscaped($zone, true);
						}
						unset($zone);

						$filters[] = '(discount.discount_zone_id = \'\' OR discount.discount_zone_id LIKE \'%,' .implode(',%\' OR discount.discount_zone_id LIKE \'%,', $zones) . ',%\')';
					} else {
						$filters[] = 'discount.discount_zone_id = \'\'';
					}
				}
			}
		}

		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if($this->params->get('add_to_cart','-1')=='-1'){
			$defaultParams = $config->get('default_params');
			$this->params->set('add_to_cart',$defaultParams['add_to_cart']);
		}

		if($this->params->get('add_to_cart')) {
			$cartHelper = hikashop_get('helper.cart');
			$this->assignRef('cart', $cartHelper);
			$catalogue = (int)$config->get('catalogue',0);
			$this->params->set('catalogue', $catalogue);
			$cartHelper->cartCount(true);
			$cartHelper->getJS($this->init());
		}

		if($this->params->get('show_out_of_stock','-1')=='-1'){
			$this->params->set('show_out_of_stock',@$config->get('show_out_of_stock'));
		}
		if($this->params->get('show_out_of_stock') != '1'){
			$filters[]='b.product_quantity!=0';
		}

		hikashop_addACLFilters($filters,'product_access','b');

		if($this->params->get('random')){
			$order = ' ORDER BY RAND()';
		}
		$select2='';
		if(hikashop_level(2) && hikaInput::get()->getVar('hikashop_front_end_main',0) && (hikaInput::get()->getVar('task','listing') != 'show' || hikaInput::get()->getVar('force_using_filters', 0) === 1)) {
			foreach($this->filters as $uniqueFitler){
				$this->filterClass->addFilter($uniqueFitler, $filters,$select,$select2, $a, $b, $on, $order, $this, $this->params->get('main_div_name'));
			}
		}

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeProductListingLoad', array( & $filters, & $order, & $this, & $select, & $select2, & $a, & $b, & $on) );

		$translationFilter = '';
		if(isset($filters['translation'])) {
			$translationFilter = ' OR '.$filters['translation'].' ';
			unset($filters['translation']);
		}

		if(preg_match('#(.*)(a|b)\.(product_name|product_code) ?(ASC|DESC)(.*)#i',$order,$match)) {
			$translationHelper = hikashop_get('helper.translation');
			if($translationHelper->isMulti()) {
				$trans_table = 'jf_content';
				if($translationHelper->falang) {
					$trans_table = 'falang_content';
				}
				$language = JFactory::getLanguage();
				$language_id = (int)$translationHelper->getId($language->getTag());
				$select2 .= ', CONCAT_WS(\'\', trans_table.value, '.$match[2].'.'.$match[3].') AS translation_for_orderby';
				$on .= ' LEFT JOIN #__'.$trans_table.' AS trans_table ON trans_table.reference_table=\'hikashop_product\' AND trans_table.published=1 AND trans_table.language_id='.$language_id.' AND trans_table.reference_field=\''.$match[3].'\' AND '.$match[2].'.product_id=trans_table.reference_id';
				$order = $match[1].'translation_for_orderby '. $match[4].$match[5];
			}
		}

		if(empty($filters))
			$filters = array('1=1');

		$query = $select2.' FROM '.$b.$a.$on.' WHERE ('.implode(') AND (',$filters).')'.$translationFilter.$order;

		if(hikashop_level(2) && hikaInput::get()->getVar('hikashop_front_end_main',0) && hikaInput::get()->getVar('task','listing')!='show'){
			$config->set('hikashopListingQuery', $query);
			$this->assignRef('listingQuery', $query);
		}

		if(!isset($pageInfo->limit->start)){
			if($config->get('redirect_post', 0)) {
				if(isset($_REQUEST['limitstart_'.$this->params->get('main_div_name').$category_selected])) {
					$pageInfo->limit->start = hikaInput::get()->getInt('limitstart_'.$this->params->get('main_div_name').$category_selected);
				} elseif(isset($_REQUEST['limitstart']) && (empty($this->module) || hikaInput::get()->getVar('hikashop_front_end_main',0))) {
					$pageInfo->limit->start = hikaInput::get()->getInt('limitstart');
				} else {
					$pageInfo->limit->start = 0;
				}
			} elseif(!empty($this->tmpl_ajax)) {
				$pageInfo->limit->start = hikaInput::get()->getInt('limitstart', 0);
			} else {
				if(hikaInput::get()->getInt('limitstart', -1) >= 0) {
					$app->setUserState($this->paramBase.'.limitstart', hikaInput::get()->getInt('limitstart'));
				}
				$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart_'.$this->params->get('main_div_name') . $category_selected, 0, 'int');
			}
		}

		$this->checkBackButtonRedirect($this->params->get('main_div_name').$category_selected);

		$database->setQuery($select.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();

		$productClass = hikashop_get('class.product');
		$options = array(
			'currency_id'=> $pageInfo->currency_id,
			'zone_id' => $pageInfo->zone_id,
			'load_badges' => $this->params->get('display_badges', 1),
			'load_custom_item_fields' => $this->params->get('display_custom_item_fields', 0),
			'price_display_type' => $pageInfo->filter->price_display_type
		);
		if($this->params->get('filter_type') == 3) {
			if(!empty($element->category_type) && $element->category_type == 'manufacturer')
				$options['group_by_category'] = 0;
			else
				$options['group_by_category'] = $pageInfo->filter->cid;
		}
		$productClass->loadProductsListingData($rows, $options);

		if($this->params->get('filter_type') == 3) {
			$this->assignRef('categories', $productClass->sortedCategories);
		}
		$this->assignRef('productFields', $productClass->productFields);

		if(!empty($productClass->itemFields)) {
			$null = array();
			$this->fieldsClass->addJS($null, $null, $null);

			foreach($rows as &$row) {
				if(empty($row->itemFields))
					continue;
				$this->fieldsClass->jsToggle($row->itemFields, $row, 0);

				$extraFields = array('item' => &$row->itemFields);
				$requiredFields = array();
				$validMessages = array();
				$values = array('item' => $row);
				$this->fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
				$this->fieldsClass->addJS($requiredFields, $validMessages, array('item'));
			}
			unset($row);
		}

		$database->setQuery('SELECT COUNT( DISTINCT b.product_id )' . $query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$this->assignRef('rows', $rows);

		global $Itemid;
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		if(empty($menu)) {
			if(!empty($Itemid)) {
				$menus->setActive($Itemid);
				$menu = $menus->getItem($Itemid);
			}
		}

		$url_itemid = '';
		if(!empty($Itemid)) {
			$url_itemid = '&Itemid='.(int)$Itemid;
		}

		if(isset($data->hk_product))
			$this->modules = '';

		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$pagination->hikaSuffix = '_'.$this->params->get('main_div_name').$category_selected;
		$this->assignRef('pagination', $pagination);

		if(empty($this->module)) {
			$fields = $this->fieldsClass->getFields('frontcomp', $element, 'category', 'checkout&task=state');
			$this->assignRef('fields', $fields);

			$title = $this->params->get('page_title');
			if(empty($title)) {
				$title = $this->params->get('title');
			}
			$use_module = $this->params->get('use_module_name');
			if(empty($use_module) && !empty($element->category_name)) {
				$title = $element->category_name;
			}

			if(!empty($element->category_page_title)) {
				$page_title = $element->category_page_title;
			} else {
				$page_title = $title;
			}

			hikashop_setPageTitle($page_title);
			$this->params->set('page_title', $title);

			if(empty($element->category_keywords))
				$element->category_keywords =  $menu->params->get('menu-meta_keywords', '');
			if(empty($element->category_meta_description))
				$element->category_meta_description =  $menu->params->get('menu-meta_description', '');

			$doc = JFactory::getDocument();
			if(!empty($element->category_keywords)) {
				$doc->setMetadata('keywords', $element->category_keywords);
			}
			if(!empty($element->category_meta_description)) {
				$doc->setMetadata('description', $element->category_meta_description);
			}

			if(!$this->params->get('random')) {
				$this->params->set('show_limit', 1);
			}

			$pathway_sef_name = $config->get('pathway_sef_name', 'category_pathway');
			if(empty($menu)) {
				$categoryClass = hikashop_get('class.category');
				$pathway = $app->getPathway();
				$category_pathway = '&'.$pathway_sef_name.'='.hikaInput::get()->getVar('menu_main_category');
				$categories = $categoryClass->getParents(reset($pageInfo->filter->cid));
				if(!empty($categories)) {
					array_shift($categories);
					foreach($categories as $category) {
						$categoryClass->addAlias($category);
						$pathway->addItem($category->category_name, hikashop_completeLink('category&task=listing&cid='.(int)$category->category_id.'&name='.$category->alias));
					}
				}
			} else {
				$category_pathway = '&'.$pathway_sef_name.'='.reset($pageInfo->filter->cid);
			}
		} else {
			$main = hikaInput::get()->getVar('hikashop_front_end_main', 0);
			if($main) {
				if(!empty($product_id)) {
					$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
					$related_sef_name = $config->get('related_sef_name','related_product');
					$category_pathway = '&'.$pathway_sef_name.'='.hikaInput::get()->getInt($pathway_sef_name,0).'&'.$related_sef_name.'='.$product_id;
				}
				if(!$this->params->get('random')) {
					$this->params->set('show_limit', 1);
				}
			}

			$module_item_id = $this->params->get('itemid');
			if(!empty($module_item_id)) {
				$url_itemid = '&Itemid='.(int)$module_item_id;
			}

			if(empty($category_pathway) && !empty($menu) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false) && !hikaInput::get()->getInt('no_cid',0)){
				$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
				$category_pathway = '&'.$pathway_sef_name.'='.reset($pageInfo->filter->cid);
			}
		}

		$this->assignRef('itemid', $url_itemid);

		if($config->get('simplified_breadcrumbs', 1))
			$category_pathway = '';
		$this->assignRef('category_pathway', $category_pathway);

		$url = $this->init(true);
		$this->assignRef('redirect_url',$url);
	}

	public function checkBackButtonRedirect($key) {
		$parameters = $this->getParameters($key);
		$config = hikashop_config();
		if ($config->get('redirect_post', 0) && $_SERVER['REQUEST_METHOD'] === 'POST' && count($parameters) && (empty($this->module) || hikaInput::get()->getVar('hikashop_front_end_main',0))) {
			$url = $this->addParametersToUrl(hikashop_currentURL(),$parameters);
			$app = JFactory::getApplication();
			$app->redirect($url);
		}
	}

	public function getParameters($key) {
		$parameters = array();
		$parameterNames = array(
			'limit'.'_'.$key => 'limit',
			'limitstart'.'_'.$key => 'limitstart'
		);
		if(hikashop_level(2) && hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task','listing') != 'show') {
			foreach($this->filters as $uniqueFitler){
				$parameterNames['filter_'.$uniqueFitler->filter_namekey]='filter_'.$uniqueFitler->filter_namekey;
				if($uniqueFitler->filter_type=='cursor'){
					$parameterNames['filter_'.$uniqueFitler->filter_namekey.'_values']='filter_'.$uniqueFitler->filter_namekey.'_values';
				}
				if(hikaInput::get()->getVar('reseted')==1){
					$_POST['filter_'.$uniqueFitler->filter_namekey] = '';
				}
			}
		}
		foreach($parameterNames as $key => $name) {
			if(isset($_POST[$key])) {
				if(is_array($_POST[$key])) {
					$_POST[$key] = implode('::',$_POST[$key]);
				}
				$parameters[$name] = $_POST[$key];
			}
		}
		return $parameters;
	}

	public function addParametersToUrl($url, $parameters){
		foreach($parameters as $k => $v){
			if($v == ' ') $v = '';
			if(strpos($url,$k.'-')!==false || strpos($url,$k.'=')!==false){
				if(!preg_match_all('#(\?|\&|\/)'.$k.'(\-|\=)(.*?)(?=(\&|\?|.html|\/))#i',$url,$matches)){
					preg_match_all('#(\?|\&|\/)'.$k.'(\-|\=)(.*)#i',$url,$matches);
				}
				if(!empty($matches) && count($matches)){
					$done = false;
					foreach($matches[0] as $i => $match){
						if(!in_array($k,array('limit','limistart')) || is_numeric($matches[3][$i])){
							$url = str_replace($matches[0][$i],$matches[1][$i].$k.$matches[2][$i].$v,$url);
							$done = true;
							break;
						}
					}
					if(!$done){
						$start = '?';
						if(strpos($url,'?')!==false){
							$start = '&';
						}
						$url.=$start.$k.'='.$v;
					}
				}
			}else{
				$start = '?';
				if(strpos($url,'?')!==false){
					$start = '&';
				}
				$url.=$start.$k.'='.$v;
			}
		}
		return $url;
	}

	public function show() {
		$app = JFactory::getApplication();
		$product_id = (int)hikashop_getCID('product_id');

		$config =& hikashop_config();
		$this->assignRef('config', $config);

		global $Itemid;
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		if(empty($menu) && !empty($Itemid)) {
			$menus->setActive($Itemid);
			$menu = $menus->getItem($Itemid);
		}


		if(empty($product_id) && is_object($menu)) {
			jimport('joomla.html.parameter');
			$category_params = new HikaParameter($menu->params);

			$product_id = $category_params->get('product_id');
			if(is_array($product_id))
				$product_id = (int)$product_id[0];
			else
				$product_id = (int)$product_id;
			hikaInput::get()->set('product_id', $product_id);
		}
		if(empty($product_id))
			return;

		$filters = array(
			'product.product_id = ' . $product_id
		);
		hikashop_addACLFilters($filters,'product_access', 'product');
		$query = 'SELECT product.*, product_category.product_category_id, product_category.category_id, product_category.ordering '.
			' FROM '.hikashop_table('product').' AS product '.
			' LEFT JOIN '.hikashop_table('product_category').' AS product_category ON product.product_id = product_category.product_id '.
			' WHERE ('.implode(') AND (',$filters).')';
		$database = JFactory::getDBO();
		$database->setQuery($query, 0, 1);
		$element = $database->loadObject();
		if(empty($element))
			return;

		$moduleHelper = hikashop_get('helper.module');
		$this->modules = $moduleHelper->setModuleData($config->get('product_show_modules', ''));

		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$productClass = hikashop_get('class.product');

		$main_currency = (int)$config->get('main_currency',1);
		$discount_before_tax = (int)$config->get('discount_before_tax',0);

		$default_params = $config->get('default_params');
		$empty = '';
		jimport('joomla.html.parameter');
		$params = new HikaParameter($empty);
		foreach($default_params as $k => $param) {
			$params->set($k,$param);
		}

		$params->set('main_currency', $main_currency);
		$params->set('discount_before_tax', $discount_before_tax);
		$params->set('catalogue', (int)$config->get('catalogue',0));
		$params->set('show_price_weight', (int)$config->get('show_price_weight',0));
		$params->set('price_with_tax', $config->get('price_with_tax'));
		$params->set('characteristic_display', $config->get('characteristic_display', 'table'));
		$params->set('characteristic_display_text', $config->get('characteristic_display_text', 1));
		$params->set('show_quantity_field', $config->get('show_quantity_field', 1));
		$params->set('add_to_wishlist',@$default_params['add_to_wishlist']);

		if((int)$params->get('show_vote_product', -1) == -1) {
			$params->set('show_vote_product', (int)$config->get('show_vote_product'));
		}

		if($config->get('enable_status_vote') == 'nothing')
			$params->set('show_vote_product', 0);

		$this->assignRef('params', $params);

		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cart', $cartHelper);

		$currency_id = hikashop_getCurrency();
		$zone_id = hikashop_getZone(null);

		$null = null;
		$currencies = $currencyClass->getCurrencies($currency_id, $null);
		$currency = $currencies[$currency_id];
		unset($currencies);
		$this->assignRef('currency', $currency);

		$this->selected_variant_id = 0;

		if($element->product_type == 'variant') {
			$this->selected_variant_id = $product_id;

			$filters = array(
				'product.product_id = ' . (int)$element->product_parent_id
			);
			hikashop_addACLFilters($filters, 'product_access', 'product');
			$query = 'SELECT product.*, product_category.* '.
				' FROM '.hikashop_table('product').' AS product '.
				' LEFT JOIN '.hikashop_table('product_category').' AS product_category ON product.product_id = product_category.product_id '.
				' WHERE ('.implode(') AND (',$filters). ') '.
				' ORDER BY product_category_id ASC';
			$database->setQuery($query, 0, 1);
			$element = $database->loadObject();

			if(empty($element))
				return;

			$product_id = $element->product_id;
			hikaInput::get()->set('product_id', $product_id);
		}

		if(!isset($_SESSION['hikashop_viewed_products']))
			$arr = array();
		else
			$arr = array_reverse($_SESSION['hikashop_viewed_products'], true);
		$arr[$product_id] = $product_id;
		$_SESSION['hikashop_viewed_products'] = array_reverse($arr, true);

		$productClass->addAlias($element);
		if(!$element->product_published)
			return;

		if($productClass->hit($product_id))
			$element->product_hit++;

		$filters = array(
			'pr.product_id = ' . $product_id,
			'pr.product_related_type = ' . $database->Quote('options'),
			'p.product_published = 1',
			'(p.product_sale_start = \'\' OR p.product_sale_start <= '.time().')',
			'(p.product_sale_end = \'\' OR p.product_sale_end > '.time().')'
		);
		hikashop_addACLFilters($filters, 'product_access', 'p');
		$query = 'SELECT p.* '.
			' FROM '.hikashop_table('product_related').' AS pr '.
			' LEFT JOIN '.hikashop_table('product').' AS p ON pr.product_related_id = p.product_id '.
			' WHERE ('.implode(') AND (', $filters).') '.
			' ORDER BY pr.product_related_ordering ASC, pr.product_related_id ASC';
		$database->setQuery($query);
		$element->options = $database->loadObjectList('product_id');

		$ids = array($product_id);
		if(!empty($element->options))
			$ids = array_merge($ids, array_keys($element->options));

		$filters = array(
			'product_parent_id IN ('.implode(',',$ids).')',
			'product_published = 1'
		);
		hikashop_addACLFilters($filters,'product_access');
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE ('.implode(') AND (', $filters).')';
		$database->setQuery($query);
		$variants = $database->loadObjectList();
		if(!empty($variants)) {
			foreach($variants as $variant) {
				$ids[] = (int)$variant->product_id;

				if($variant->product_parent_id == $product_id)
					$element->variants[$variant->product_id] = $variant;

				if(!empty($element->options)) {
					foreach($element->options as $k => $optionElement) {
						if($variant->product_parent_id == $optionElement->product_id) {
							$element->options[$k]->variants[$variant->product_id] = $variant;
							break;
						}
					}
				}
			}
		}

		$sort = $config->get('characteristics_values_sorting');
		if($sort == 'old') {
			$order = 'characteristic_id ASC';
		} elseif($sort == 'alias') {
			$order = 'characteristic_alias ASC';
		} elseif($sort == 'ordering') {
			$order = 'characteristic_ordering ASC';
		} else {
			$order = 'characteristic_value ASC';
		}

		$query = 'SELECT v.*, c.* '.
			' FROM '.hikashop_table('variant').' AS v '.
			' INNER JOIN '.hikashop_table('characteristic').' AS c ON v.variant_characteristic_id = c.characteristic_id '.
			' WHERE v.variant_product_id IN ('.implode(',', $ids).') '.
			' ORDER BY v.ordering ASC, c.'.$order;
		$database->setQuery($query);
		$characteristics = $database->loadObjectList();

		if(!empty($characteristics)) {
			$mainCharacteristics = array();
			foreach($characteristics as $characteristic) {
				if($product_id == $characteristic->variant_product_id) {
					$mainCharacteristics[$product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id] = $characteristic;
				}
				if(!empty($element->options)) {
					foreach($element->options as $k => $optionElement) {
						if($optionElement->product_id == $characteristic->variant_product_id) {
							$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id] = $characteristic;
						}
					}
				}
			}

			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterProductCharacteristicsLoad', array( &$element, &$mainCharacteristics, &$characteristics ));

			if(!empty($element->variants)) {
				$this->addCharacteristics($element, $mainCharacteristics, $characteristics);
				$this->orderVariants($element);
			}
			unset($element->characteristics[0]);

			if(!empty($element->options)) {
				foreach($element->options as $k => $optionElement) {
					if(empty($optionElement->variants))
						continue;

					$this->addCharacteristics($element->options[$k], $mainCharacteristics, $characteristics);
					if(count(@$mainCharacteristics[$optionElement->product_id][0]) > 0)
						$this->orderVariants($element->options[$k]);
				}
			}
		}

		$query = 'SELECT * FROM '.hikashop_table('file').
			' WHERE file_ref_id IN ('.implode(',', $ids).') AND file_type IN (\'product\',\'file\') '.
			' ORDER BY file_ordering ASC, file_id ASC';
		$database->setQuery($query);
		$product_files = $database->loadObjectList();
		if(!empty($product_files)) {
			$productClass->addFiles($element,$product_files);
		}

		$currencyClass->getPrices($element, $ids, $currency_id, $main_currency, $zone_id, $discount_before_tax);

		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('frontcomp', $element, 'product', 'checkout&task=state');
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);
		if(hikashop_level(2)) {
			$itemFields = $fieldsClass->getFields('frontcomp', $element, 'item', 'checkout&task=state');

			foreach ($itemFields as $fieldName => $oneExtraField) {
				if(empty($element->$fieldName)) {
					$element->$fieldName = $oneExtraField->field_default;
				}
			}
			$null = array();
			$fieldsClass->addJS($null, $null, $null);
			$fieldsClass->jsToggle($itemFields, $element, 0);
			$this->assignRef('itemFields', $itemFields);
			$extraFields = array('item'=> &$itemFields);
			$requiredFields = array();
			$validMessages = array();
			$values = array('item'=> $element);
			$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
			$fieldsClass->addJS($requiredFields, $validMessages, array('item'));
		}

		$this->checkVariants($element);
		if(!empty($element->options)) {
			foreach($element->options as $k => $optionElement) {
				$this->checkVariants($element->options[$k]);
			}
		}

		$this->setDefault($element);
		if(!empty($element->options)) {
			foreach($element->options as $k => $optionElement) {
				$this->setDefault($element->options[$k]);
			}
		}

		if(!empty($element->variants)) {
			foreach($element->variants as $k => $variant) {
				$variant->main =& $element->main;
			}
		}

		$this->assignRef('element', $element);

		$product_name = $this->element->product_name;
		$product_page_title = $this->element->product_page_title;
		$product_description = $element->product_meta_description;
		$product_keywords = $element->product_keywords;

		if(!empty($this->element->main)) {
			$product_name = $this->element->main->product_name;
			if(!empty($this->element->main->product_page_title))
				$product_page_title = $this->element->main->product_page_title;
			if(!empty($this->element->main->product_meta_description))
				$product_description = $this->element->main->product_meta_description;
			if(!empty($this->element->main->product_keywords))
				$product_keywords = $this->element->main->product_keywords;
		}

		if(empty($product_keywords) && is_object($menu))
			$product_keywords =  $menu->params->get('menu-meta_keywords', '');
		if(empty($product_description) && is_object($menu))
			$product_description =  $menu->params->get('menu-meta_description', '');

		$doc = JFactory::getDocument();
		if(!empty($product_keywords))
			$doc->setMetadata('keywords', $product_keywords);
		if(!empty($product_description))
			$doc->setMetadata('description', $product_description);

		if(empty($product_page_title)){
			$product_page_title = $product_name;
		}
		hikashop_setPageTitle($product_page_title);

		$parent = 0;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);

		if(empty($menu) || !(strpos($menu->link,'option='.HIKASHOP_COMPONENT) !== false && strpos($menu->link,'view=product') !== false && strpos($menu->link,'layout=show') !== false)) {
			$pathway = $app->getPathway();
			$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
			$category_pathway = hikaInput::get()->getInt($pathway_sef_name,0);

			if($category_pathway) {
				$categoryClass = hikashop_get('class.category');

				if(!empty($menu->id)) {
					$menuClass = hikashop_get('class.menus');
					$menuData = $menuClass->get($menu->id);
					if(@$menuData->hikashop_params['content_type'] == 'manufacturer') {
						$new_id = 'manufacturer';
						$categoryClass->getMainElement($new_id);
						$menuData->hikashop_params['selectparentlisting'] = $new_id;
					}
					if(!empty($menuData->hikashop_params['selectparentlisting'])) {
						$parent = $menuData->hikashop_params['selectparentlisting'];
					}
				}

				$categories = $categoryClass->getParents($category_pathway, $parent);
				array_shift($categories);
				foreach($categories as $category) {
					$categoryClass->addAlias($category);
					$pathway->addItem($category->category_name, hikashop_completeLink('category&task=listing&cid='.(int)$category->category_id.'&name='.$category->alias.$url_itemid));
				}
				unset($categories);
			}
			$related_sef_name = $config->get('related_sef_name','related_product');
			$related = hikaInput::get()->getInt($related_sef_name,0);
			if($config->get('simplified_breadcrumbs',1) || !$category_pathway) {
				$category_pathway = '';
			} else {
				$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
				$category_pathway = '&'.$pathway_sef_name.'='.$category_pathway;
			}

			if(!empty($related)) {
				$prod = $productClass->get($related);
				if(!empty($prod)) {
					$productClass->addAlias($prod);
					$pathway->addItem($prod->product_name, hikashop_completeLink('product&task=show&cid='.(int)$prod->product_id.'&name='.$prod->alias.$category_pathway.$url_itemid));
				}
			}
			$pathway->addItem($product_name, hikashop_completeLink('product&task=show&cid='.(int)$element->product_id.'&name='.$element->alias.$category_pathway.$url_itemid));
		}

		$classbadge = hikashop_get('class.badge');
		$this->assignRef('classbadge',$classbadge);
		$classbadge->loadBadges($element);
		if(!empty($element->variants)){
			foreach($element->variants as $k => $variant){
				$classbadge->loadBadges($element->variants[$k]);
			}
		}

		$links = new stdClass();
		$links->previous = '';
		$links->next = '';

		if($config->get('show_other_product_shortcut')) {
			$filters = array(
				'published' => 'b.product_published = 1',
				'type' => 'b.product_type = \'main\''
			);

			$pathway_sef_name = $config->get('pathway_sef_name', 'category_pathway');
			$category_id = hikaInput::get()->getInt($pathway_sef_name, 0);
			if(empty($category_id) && is_object($menu) && !empty($menu->id)) {
				$menuClass = hikashop_get('class.menus');
				$menuData = $menuClass->get($menu->id);
				if(!empty($menuData->hikashop_params['selectparentlisting'])) {
					if($menuData->hikashop_params['filter_type'] == 2)
						$menuData->hikashop_params['filter_type'] = $config->get('filter_type');

					if(!$menuData->hikashop_params['filter_type']) {
						$type = 'product';
						$catName = 'a.category_id';
						$categoryClass = hikashop_get('class.category');
						$category = $categoryClass->get($menuData->hikashop_params['selectparentlisting'], true);
						if(!empty($category->category_type) && $category->category_type == 'manufacturer') {
							$type = 'manufacturer';
							$catName = 'b.product_manufacturer_id';
						}

						$categoryClass->parentObject =& $this;
						$categoryClass->type = $type;
						$children = $categoryClass->getChildren($menuData->hikashop_params['selectparentlisting'], true, array(), '', 0, 0);

						$filters['category'] = $catName.' IN (';
						foreach($children as $child) {
							$filters['category'] .= $child->category_id.',';
						}
						$filters['category'] .= (int)$menuData->hikashop_params['selectparentlisting'].')';
					}
				}
			} else {
				$categoryClass = hikashop_get('class.category');
				$category = $categoryClass->get($category_id, true);
				if(!empty($category->category_type) && $category->category_type == 'manufacturer') {
					$filters['category'] = 'b.product_manufacturer_id = '.(int)$category_id;
				}
			}


			if(empty($category_id)) {
				$query = 'SELECT a.category_id FROM '.hikashop_table('product_category').' AS a WHERE a.product_id='.(int)$product_id.' ORDER BY a.product_category_id ASC';
				$database->setQuery($query, 0, 1);
				$category_id = $database->loadResult();
				$filters['category'] = 'a.category_id = '.(int)$category_id;
			}
			if(empty($filters['category']))
				$filters['category'] = 'a.category_id = '.(int)$category_id;

			hikashop_addACLFilters($filters,'product_access', 'b');
			if($this->params->get('show_out_of_stock','-1') == '-1')
				$this->params->set('show_out_of_stock', $config->get('show_out_of_stock', '1'));

			if($this->params->get('show_out_of_stock') != '1')
				$filters['quantity'] = 'b.product_quantity != 0';

			$query = 'SELECT DISTINCT a.product_id '.
				' FROM '.hikashop_table('product_category').' AS a '.
				' LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id '.
				' WHERE ('.implode(') AND (',$filters).') '.
				' GROUP BY a.product_id ORDER BY a.ordering ASC';
			$database->setQuery($query);
			if(!HIKASHOP_J25) {
				$articles = $database->loadResultArray();
			} else {
				$articles = $database->loadColumn();
			}

			if(!empty($articles)) {
				$pathway_sef_name = $config->get('pathway_sef_name', 'category_pathway');
				foreach($articles as $k => $article){
					if($article != $element->product_id && $article != $element->product_parent_id)
						continue;

					$links->path = JURI::root() . 'media/com_hikashop/images/icons/';
					$pathway = '';
					if(isset($category_pathway))
						$pathway = $pathway_sef_name.'='.$category_pathway;

					if($k != 0) {
						$p = $k - 1;
						$id_previous = $articles[$p];
						$elt = $productClass->get($id_previous);
						$productClass->addAlias($elt);
						$links->previous = hikashop_completeLink('product&task=show&cid='.(int)$id_previous.'&name='.$elt->alias.'&'.$pathway.$url_itemid);
						$links->previous_product = $elt;
					}
					$n = $k;
					while(isset($articles[$n]) && ($articles[$n] == $element->product_id || $articles[$n] == $element->product_parent_id)) {
						$n++;
					}

					if(isset($articles[$n])) {
						$id_next = $articles[$n];
						$elt = $productClass->get($id_next);
						$productClass->addAlias($elt);
						$links->next = hikashop_completeLink('product&task=show&cid='.(int)$id_next.'&name='.$elt->alias.'&'.$pathway.$url_itemid);
						$links->next_product = $elt;
					}
					break;
				}
				if($config->get('circle_around_for_shortcuts', 1)){
					if(empty($links->next) && !empty($links->previous)){
						$id_next = reset($articles);
						$elt = $productClass->get($id_next);
						$productClass->addAlias($elt);
						$links->next = hikashop_completeLink('product&task=show&cid='.(int)$id_next.'&name='.$elt->alias.'&'.$pathway.$url_itemid);
						$links->next_product = $elt;
					}
					if(empty($links->previous) && !empty($links->next)){
						$id_previous = end($articles);
						$elt = $productClass->get($id_previous);
						$productClass->addAlias($elt);
						$links->previous = hikashop_completeLink('product&task=show&cid='.(int)$id_previous.'&name='.$elt->alias.'&'.$pathway.$url_itemid);
						$links->previous_product = $elt;
					}
				}
			}
		}
		$this->assignRef('links',$links);

		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('image', $imageHelper);
		$characteristic = hikashop_get('type.characteristic');
		$this->assignRef('characteristic', $characteristic);

		$query = 'SELECT b.* '.
			' FROM '.hikashop_table('product_category').' AS a '.
			' LEFT JOIN '.hikashop_table('category').' AS b ON a.category_id = b.category_id '.
			' WHERE a.product_id = '.(($element->product_type == 'variant' && !empty($element->product_parent_id)) ? (int)$element->product_parent_id : (int)$element->product_id).
			' ORDER BY a.product_category_id ASC';
		$database->setQuery($query);
		$categories = $database->loadObjectList('category_id');
		$this->assignRef('categories',$categories);

		$productlayout = $element->product_layout;
		$productDisplayType = hikashop_get('type.productdisplay');
		$quantityDisplayType = hikashop_get('type.quantitydisplay');
		if(!empty($element->main->product_layout)){
			$productlayout=$element->main->product_layout;
		}
		if(!$productDisplayType->check( $productlayout, $app->getTemplate())) {
			$productlayout = '';
		}
		$categoryQuantityLayout = '';
		if(!empty($categories) ) {
			foreach($categories as $category) {
				if(empty($productlayout) && !empty($category->category_layout) && $productDisplayType->check($category->category_layout, $app->getTemplate())) {
					$productlayout = $category->category_layout;
				}
				if(empty($categoryQuantityLayout) && !empty($category->category_quantity_layout) && $quantityDisplayType->check($category->category_quantity_layout, $app->getTemplate())) {
					$categoryQuantityLayout = $category->category_quantity_layout;
				}

				if(!empty($productlayout) && !empty($categoryQuantityLayout))
					break;
			}
		}
		if(empty($productlayout) && $productDisplayType->check($config->get('product_display'), $app->getTemplate())) {
			$productlayout = $config->get('product_display');
		}
		if(empty($productlayout)) {
			$productlayout = 'show_default';
		}
		$this->assignRef('productlayout',$productlayout);

		$url = $this->init();
		$cartHelper->getJS($url);
		$this->assignRef('redirect_url', $url);

		if($element->product_parent_id > 0 && isset($element->main_product_quantity_layout))
			$element->product_quantity_layout = $element->main_product_quantity_layout;
		$qLayout = $config->get('product_quantity_display', 'show_default');
		if(!empty($element->product_quantity_layout) && $element->product_quantity_layout != 'inherit') {
			$qLayout = $element->product_quantity_layout;
		} elseif(!empty($categoryQuantityLayout) && $categoryQuantityLayout != 'inherit') {
			$qLayout = $categoryQuantityLayout;
		}
		$this->assignRef('quantityLayout', $qLayout);

		$canonical = false;
		if(!empty($element->main->product_canonical)) {
			$canonical = $element->main->product_canonical;
		} elseif(!empty($element->product_canonical)) {
			$canonical = $element->product_canonical;
		}

		$force_canonical = (int)$config->get('force_canonical_urls', 1);
		if(empty($canonical) && !empty($force_canonical)) {
			$newObj = new stdClass();
			$newObj->product_id = $element->product_id;
			if(!empty($element->main->product_id))
				$newObj->product_id = $element->main->product_id;
			$newObj->product_canonical = str_replace(HIKASHOP_LIVE, '', hikashop_currentURL());
			if($force_canonical == 2)
				$productClass->save($newObj);
			$canonical = $newObj->product_canonical;
		}
		$this->assignRef('canonical', $canonical);


		if(!empty($this->element->main->characteristics)) {
			$display = array('images'=>false, 'variant_name'=>false, 'product_description'=>false, 'prices'=>false);
			$main_images = '';
			if(!empty($this->element->main->images)) {
				foreach($this->element->main->images as $image) {
					$main_images .= '|' . $image->file_path;
				}
			}
			$main_prices = '';
			if(!empty($this->element->main->prices)) {
				foreach($this->element->main->prices as $price) {
					$main_prices .= '|' . $price->price_value . '_' . $price->price_currency_id;
				}
			}
			foreach($this->element->variants as $variant) {
				foreach($display as $k => $v) {
					if(isset($variant->$k) && !is_array($variant->$k) && !empty($variant->$k))
						$display[$k] = true;
				}
				$variant_images = '';
				if(!empty($this->element->main->images) && !empty($variant->images)) {
					foreach($variant->images as $image) {
						$variant_images .= '|' . $image->file_path;
					}
				}
				if($variant_images != $main_images)
					$display['images'] = true;
				$variant_prices = '';
				if(!empty($variant->prices)) {
					foreach($variant->prices as $price) {
						$variant_prices .= '|' . $price->price_value . '_' . $price->price_currency_id;
					}
				}
				if($variant_prices!=$main_prices)
					$display['prices'] = true;
			}
			$this->assignRef('displayVariants', $display);
		}
	}

	public function compare() {
		if(!hikashop_level(2)) { return; }
		$app = JFactory::getApplication();
		$cids = hikaInput::get()->get('cid', array(), 'array');

		$config =& hikashop_config();
		$this->assignRef('config',$config);
		global $Itemid;
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		if(empty($menu)){
			if(!empty($Itemid)){
				$menus->setActive($Itemid);
				$menu	= $menus->getItem($Itemid);
			}
		}
		if(empty($cids)){
			if (is_object( $menu )) {
				jimport('joomla.html.parameter');
				$category_params = new HikaParameter( $menu->params );
				$cids = $category_params->get('product_id');
				if(!is_array($cids))
					$cids = array($cids);
				foreach($cids as $k => $cid){
					if($k > 7)
						unset($cids[$k]);
				}
			}
		}

		if(empty($cids)){
			return;
		}

		$c = array();
		foreach($cids as $cid) {
			if( strpos($cid,',')!==false) {
				$c = array_merge($c,explode(',',$cid));
			} else {
				$c[] = (int)$cid;
			}
		}
		$cids = $c;
		JArrayHelper::toInteger($cids);

		$empty = '';
		$default_params = $config->get('default_params');
		jimport('joomla.html.parameter');
		$params = new HikaParameter($empty);
		foreach($default_params as $k => $param){
			$params->set($k,$param);
		}
		$main_currency = (int)$config->get('main_currency',1);
		$params->set('main_currency',$main_currency);
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		$params->set('discount_before_tax',$discount_before_tax);
		$params->set('show_compare',(int)$config->get('show_compare',0));
		$compare_limit = (int)$config->get('compare_limit',5);
		$params->set('compare_limit',$compare_limit);
		$compare_inc_lastseen = (int)$config->get('compare_inc_lastseen',0);
		$params->set('compare_inc_lastseen',$compare_inc_lastseen);
		$params->set('compare_show_name_separator',(int)$config->get('compare_show_name_separator',1));
		$params->set('catalogue',(int)$config->get('catalogue',0));
		$params->set('add_to_cart', (int)1);
		$params->set('add_to_wishlist', $config->get('compare_to_wishlist', 1) && $config->get('enable_wishlist', 1));
		$params->set('show_price_weight',(int)$config->get('show_price_weight',0));
		$params->set('characteristic_display',$config->get('characteristic_display','table'));
		$params->set('characteristic_display_text',$config->get('characteristic_display_text',1));
		$params->set('show_quantity_field',$config->get('show_quantity_field',1));
		$this->assignRef('params',$params);

		if( count($cids) > $compare_limit ) {
			$cids = array_slice($cids, 0, $compare_limit );
		}

		$filters=array('a.product_id IN ('.implode(',',$cids).')');
		hikashop_addACLFilters($filters,'product_access','a');
		$query = 'SELECT DISTINCT a.product_id, a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters).' ORDER BY b.ordering ASC, a.product_id ASC';
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$elements = $database->loadObjectList();
		if(empty($elements)){
			return;
		}

		$this->modules = $config->get('product_show_modules','');
		$module = hikashop_get('helper.module');
		$this->modules = $module->setModuleData($this->modules);

		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		$currency_id = hikashop_getCurrency();

		$zone_id = hikashop_getZone(null);
		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cart', $cartHelper);
		$this->selected_variant_id = 0;

		$productClass = hikashop_get('class.product');

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$classbadge = hikashop_get('class.badge');
		$this->assignRef('classbadge', $classbadge);

		$unset=array();
		$done = array();
		foreach($elements as $k => $element) {
			$product_id = $element->product_id;
			if(isset($done[$product_id])){
				$unset[]=$k;
				continue;
			}else{
				$done[$product_id]=$product_id;
			}
			if( $element->product_type == 'variant' ) {
				$filters = array('a.product_id='.$element->product_parent_id);
				hikashop_addACLFilters($filters,'product_access','a');
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
				$database->setQuery($query);
				$elements[$k] = $database->loadObject();
				if(empty($elements[$k])){
					return;
				}
				$k = array_search($product_id,$cids);
				if( $k !== false ) {
					$cids[$k] = (int)$element->product_id;
				}
			}
			$productClass->addAlias($elements[$k]);
			if(!$elements[$k]->product_published){
				return;
			}

			if( $compare_inc_lastseen ) {
				$prod = new stdClass();
				$prod->product_id = $product_id;
				$prod->product_hit = $element->product_hit+1;
				$prod->product_last_seen_date = time();
				$productClass->save($prod,true);
			}
		}
		if(!empty($unset)){
			foreach($unset as $u){
				unset($elements[$u]);
			}
		}
		$defaultParams = $config->get('default_params');
		$options = array(
			'currency_id'=> $currency_id,
			'zone_id' => $zone_id,
			'load_badges' => true,
			'load_custom_item_fields' => false,
			'load_custom_product_fields' => 'display:compare=1',
			'price_display_type' => @$defaultParams['price_display_type']
		);
		$productClass->loadProductsListingData($elements, $options);

		$fields = array( 0 => $productClass->productFields );

		$this->assignRef('elements',$elements);
		$image = hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$this->assignRef('fields',$fields);


		$url = $this->init();
		$cartHelper->getJS($url);
		$this->assignRef('redirect_url', $url);
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)){
			$url_itemid = '&Itemid=' . $Itemid;
		}
		$this->assignRef('itemid', $url_itemid);
		$category_pathway = '';
		$this->assignRef('category_pathway', $category_pathway);
		$this->config->set('show_quantity_field', 0);

	}

	public function orderVariants(&$element){
		if(empty($element->variants))
			return;

		$optionsVariants = array();
		$config =& hikashop_config();
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
		foreach($element->variants as $k2 => $variant) {
			$key = '';
			foreach($variant->characteristics as $char) {
				if(in_array($sort,array('old','ordering'))) {
					$key .= sprintf('%04d', $char->$order).'+';
				} else {
					$key .= $char->$order.'+';
				}
			}
			$key .= $variant->product_id;
			$optionsVariants[$key] = &$element->variants[$k2];
		}
		ksort($optionsVariants);
		$element->variants = $optionsVariants;
	}

	public function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics) {
		$element->characteristics = @$mainCharacteristics[$element->product_id][0];
		if(!empty($element->characteristics) && is_array($element->characteristics)) {
			foreach($element->characteristics as $k => $characteristic) {
				if(!empty($mainCharacteristics[$element->product_id][$k])) {
					$element->characteristics[$k]->default = end($mainCharacteristics[$element->product_id][$k]);
				} else {
					$app = JFactory::getApplication();
					$app->enqueueMessage('The default value of one of the characteristics of that product isn\'t available as a variant. Please check the characteristics and variants of that product');
				}
			}
		}

		if(empty($element->variants))
			return;

		foreach($characteristics as $characteristic) {
			foreach($element->variants as $k => $variant) {
				if($variant->product_id != $characteristic->variant_product_id)
					continue;

				$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id] = $characteristic;
				$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id] = $characteristic;
				if($this->selected_variant_id && $variant->product_id==$this->selected_variant_id)
					$element->characteristics[$characteristic->characteristic_parent_id]->default = $characteristic;
			}
		}

		if(isset($_REQUEST['hikashop_product_characteristic'])) {
			if(is_array($_REQUEST['hikashop_product_characteristic'])) {
				JArrayHelper::toInteger($_REQUEST['hikashop_product_characteristic']);
				$chars = $_REQUEST['hikashop_product_characteristic'];
			} else {
				$chars = hikaInput::get()->getCmd('hikashop_product_characteristic','');
				$chars = explode('_',$chars);
			}
			if(!empty($chars)) {
				foreach($element->variants as $k => $variant) {
					$chars = array();
					foreach($variant->characteristics as $val) {
						$i = 0;
						$ordering = @$element->characteristics[$val->characteristic_parent_id]->ordering;
						while(isset($chars[$ordering])&& $i < 30) {
							$i++;
							$ordering++;
						}
						$chars[$ordering] = $val;
					}
					ksort($chars);
					$element->variants[$k]->characteristics=$chars;
					$variant->characteristics=$chars;

					$choosed = true;
					foreach($variant->characteristics as $characteristic) {
						$ok = false;
						foreach($chars as $k => $char) {
							if(!empty($char)) {
								if($characteristic->characteristic_id == $char) {
									$ok = true;
									break;
								}
							}
						}
						if(!$ok){
							$choosed=false;
						}else{
							$element->characteristics[$characteristic->characteristic_parent_id]->default = $characteristic;
						}
					}
					if($choosed)
						break;
				}
			}
		}
		foreach($element->variants as $k => $variant) {
			$temp = array();
			foreach($element->characteristics as $k2 => $characteristic2) {
				if(!empty($variant->characteristics)) {
					foreach($variant->characteristics as $k3 => $characteristic3) {
						if($k2 == $k3) {
							$temp[$k3] = $characteristic3;
							break;
						}
					}
				}
			}
			$element->variants[$k]->characteristics = $temp;
		}
	}

	public function setDefault(&$element) {
		if(empty($element->characteristics) || empty($element->variants))
			return;

		$match = false;
		if(!isset($element->main) || is_null($element->main))
			$element->main = new stdClass();

		foreach($element->variants as $k => $variant) {
			$default = true;
			foreach($element->characteristics as $characteristic) {
				$found = false;
				foreach($variant->characteristics as $k => $characteristic2) {
					if(!empty($characteristic->default->characteristic_id) && $characteristic2->characteristic_id == $characteristic->default->characteristic_id) {
						$found = true;
						break;
					}
				}
				if(!$found) {
					$default = false;
					break;
				}
			}
			if($default) {
				foreach(get_object_vars($variant) as $field => $value) {
					if(isset($element->$field))
						$element->main->$field = $element->$field;
					else
						$element->main->$field = '';
					if(!in_array($field, array('product_keywords','product_meta_description','product_page_title','product_canonical','product_alias','product_url')))
						$element->$field = $value;
				}
				$match = true;
				break;
			}
		}
		if(!$match) {
			$variant = reset($element->variants);
			foreach(get_object_vars($variant) as $field => $value) {
				$element->main->$field = @$element->$field;
				$element->$field = $value;
			}
		}
	}

	public function checkVariants(&$element) {
		if(empty($element->characteristics))
			return;

		$mapping = array();
		foreach($element->characteristics as $characteristic) {
			$tempmapping = array();
			if(!empty($characteristic->values) && !empty($characteristic->characteristic_id)) {
				foreach($characteristic->values as $k => $value) {
					if(empty($mapping)) {
						$tempmapping[] = array($characteristic->characteristic_id => $k);
					} else {
						foreach($mapping as $val) {
							$val[$characteristic->characteristic_id] = $k;
							$tempmapping[] = $val;
						}
					}
				}
			}
			$mapping = $tempmapping;
		}

		if(empty($element->variants))
			$element->variants = array();

		$productClass = hikashop_get('class.product');

		foreach($mapping as $map) {
			$found = false;
			foreach($element->variants as $k2 => $variant) {
				$ok = true;
				foreach($map as $k => $id) {
					if(empty($variant->characteristics[$k]->characteristic_id) || $variant->characteristics[$k]->characteristic_id != $id) {
						$ok = false;
						break;
					}
				}
				if($ok) {
					$found = true;
					$productClass->checkVariant($element->variants[$k2], $element, $map);
					break;
				}
			}

			if(!$found) {
				$new = new stdClass;
				$new->product_published = 0;
				$new->product_quantity = 0;
				$productClass->checkVariant($new, $element, $map);
				$element->variants[$new->map] = $new;
			}
		}
	}

	public function _getCheckoutURL(){
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		return hikashop_completeLink('checkout'.$url_itemid,false,true);
	}

	public function init($cart=false){
		$config =& hikashop_config();
		$url = $config->get('redirect_url_after_add_cart','stay_if_cart');
		switch($url){
			case 'checkout':
				$url = $this->_getCheckoutURL();
				break;
			case 'stay_if_cart':
				$url='';
				if(!$cart){
					$url = $this->_getCheckoutURL();
					break;
				}
			case 'ask_user':
			case 'stay':
				$url='';
			case '':
			default:
				if(empty($url)){
					$url = hikashop_currentURL('return_url');
				}
				break;
		}

		return urlencode($url);
	}

	public function cart() {
		hikashop_nocache();

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$moduleHelper = hikashop_get('helper.module');
		$moduleHelper->initialize($this, false);

		$from_module = 0;
		if(isset($this->params) && is_object($this->params))
			$from_module = (int)$this->params->get('from_module', 0);

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component' && $from_module == 0) {

			$this->ajax = true;

			$module_id = hikaInput::get()->getInt('module_id', 0);

			$module = JModuleHelper::getModule('hikashop_cart', false);
			if(empty($module) || $module->id != $module_id) {
				$req = $moduleHelper->setModuleData($module_id);
				$req = reset($req);
				if(is_object($req) && (int)$req->id == $module_id && ($req->module == 'mod_hikashop_cart' || $req->module == 'mod_hikashop_wishlist'))
					$module = $req;
			}
			$params = new HikaParameter(@$module->params);

			$module_options = array();

			if(!empty($module))
				$module_options = $config->get('params_'.$module->id);
			if(empty($module_options))
				$module_options = $config->get('default_params');

			if($module->module == 'mod_hikashop_wishlist')
				$data = $params->get('hikashopwishlistmodule', '');
			else
				$data = $params->get('hikashopcartmodule', '');
			if(!empty($data) && is_object($data)) {
				foreach($data as $k => $v) {
					$module_options[$k] = $v;
				}
			}

			foreach($module_options as $key => $optionElement) {
				$params->set($key, $optionElement);
			}
			if(!empty($module)) {
				foreach(get_object_vars($module) as $k => $v) {
					if(!is_object($v))
						$params->set($k, $v);
				}
			}
			$params->set('from_module', $module->id);
			$params->set('from', 'module');

			$url = hikaInput::get()->getVar('return_url','');
			if(empty($url)) {
				$url = urldecode(hikaInput::get()->getVar('url', ''));
			} else {
				$url = base64_decode(urldecode($url));
			}

			$url = str_replace(array('&popup=1','?popup=1'), '', $url);

			if(hikashop_disallowUrlRedirect($url))
				$url = '';

			if(empty($url)) {
				global $Itemid;
				$url = 'checkout';
				if(!empty($Itemid))
					$url .= '&Itemid=' . $Itemid;
				$url = hikashop_completeLink($url, false, true);
			}
			$params->set('return_url', $url);
			hikaInput::get()->set('return_url', $url);

			$this->params = $params;
		}

		$app = JFactory::getApplication();
		$this->assignRef('app', $app);

		$database = JFactory::getDBO();

		$default_params = $config->get('default_params');
		$this->assignRef('default_params', $default_params);

		$this->loadRef(array(
			'popup' => 'helper.popup',
		));

		$cartClass = hikashop_get('class.cart');
		$module_type = hikaInput::get()->getCmd('module_type', 'cart');
		if(!in_array($module_type, array('cart', 'wishlist')))
			$module_type = 'cart';

		$cart_type = $this->params->get('cart_type', $module_type);
		$this->assignRef('cart_type', $cart_type);

		if(!in_array($cart_type, array('cart', 'wishlist')))
			$cart_type = 'cart';

		$cart_id = $cartClass->getCurrentCartId($cart_type);

		$fullcart = new stdClass();
		if(!empty($cart_id))
			$fullcart = $cartClass->getFullCart($cart_id);
		$this->assignRef('element', $fullcart);

		$rows = null;
		if(!empty($fullcart) && isset($fullcart->products))
			$rows =& $fullcart->products;

		if(!empty($rows)) {
			$this->loadRef(array(
				'currencyClass' => 'class.currency',
				'productClass' => 'class.product',
				'imageHelper' => 'helper.image',
			));

			$this->currencyHelper = $this->currencyClass;
			$this->image = $this->imageHelper;

			foreach($rows as $k => $row) {
				if($cart_type != 'wishlist' && $row->cart_product_quantity == 0) {
					$rows[$k]->hide = 1;
				} else if($cart_type == 'wishlist' && $row->product_type == 'variant' && !empty($row->cart_product_parent_id) && isset($rows[$row->cart_product_parent_id])) {
					$rows[$row->cart_product_parent_id]->hide = 1;
				}
				$this->productClass->addAlias($rows[$k]);
			}
		}
		$this->assignRef('rows', $rows);

		if($this->params->get('show_shipping') || $this->params->get('show_coupon')) {
			$total =& $fullcart->full_total;
		} else {
			$total =& $fullcart->total;
		}
		$this->assignRef('total', $total);

		$shipping_price = null;
		if(!empty($fullcart->shipping)) {
			foreach($fullcart->shipping as $shipping) {
				if(!isset($shipping->shipping_price) && isset($shipping->shipping_price_with_tax) ) {
					$shipping->shipping_price = $shipping->shipping_price_with_tax;
				}
				if(!isset($shipping->shipping_price))
					continue;

				if($shipping_price === null)
					$shipping_price = 0.0;
				if(!$this->params->get('price_with_tax') || !isset($shipping->shipping_price_with_tax)) {
					$shipping_price += $shipping->shipping_price;
				} else {
					$shipping_price += $shipping->shipping_price_with_tax;
				}
			}
		}
		$this->assignRef('shipping_price', $shipping_price);

		$cartHelper = hikashop_get('helper.cart');
		$this->assignRef('cartHelper', $cartHelper);
		$this->cart = $this->cartHelper;

		$cartHelper->cartCount(true);

		$url = $this->init(true);
		$this->params->set('url', $url);

		ob_start();
		$cartHelper->getJS($url,false);
		$notice_html = ob_get_clean();
		$this->assignRef('notice_html', $notice_html);

		if(hikashop_level(2) && !empty($rows)) {
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);

			$itemFields = $fieldsClass->getFields('display:product_cart=1', $rows, 'item', 'checkout&task=state');
			$this->assignRef('itemFields', $itemFields);

			foreach($itemFields as $itemField) {
				$namekey = $itemField->field_namekey;
				foreach($rows as $k => $p) {
					if(isset($fullcart->cart_products[$k]->$namekey))
						$rows[$k]->$namekey = $fullcart->cart_products[$k]->$namekey;
				}
			}
		}

		$extraData = new stdClass();
		$this->assignRef('extraData', $extraData);

		$this->legacyCartInit();
	}

	public function legacyCartInit() {
		global $Itemid;
		$this->url_itemid = '';
		$menuClass = hikashop_get('class.menus');
		if(!empty($Itemid)) {
			$current_id = $menuClass->loadAMenuItemId('', '', $Itemid);
			if($current_id)
				$this->url_itemid = '&Itemid='.$Itemid;
		}
		if(empty($this->url_itemid)) {
			$random_id = $menuClass->loadAMenuItemId('', '');
			if($random_id)
				$this->url_itemid = '&Itemid='.$random_id;
		}

		$this->url_checkout = $menuClass->getCheckoutURL();

		$this->cart_itemid = $this->url_itemid;

		if($this->cart_type == 'wishlist') {
			$set = $this->params->get('cart_itemid', false);
			if(!$set)
				$set = $menuClass->loadAMenuItemId('cart', 'showcart', $this->params->get('cart_itemid', 0));
			if(!$set)
				$set = $menuClass->loadAMenuItemId('cart', 'showcart');
			if(!$set)
				$set = $menuClass->loadAMenuItemId('','');
			if($set)
				$this->cart_itemid = '&Itemid=' . $set;
		}

		if($this->params->get('from', 'no') == 'no')
			$this->params->set('from', hikaInput::get()->getString('from', 'display'));
	}

	public function contact() {
		$user = hikashop_loadUser(true);
		$this->assignRef('element',$user);

		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		$product_id = (int)hikashop_getCID('product_id');
		$config =& hikashop_config();
		$this->assignRef('config',$config);

		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper',$imageHelper);

		$element = null;
		if(!empty($product_id)) {
			$filters=array('a.product_id='.$product_id);
			hikashop_addACLFilters($filters,'product_access','a');
			$query = 'SELECT a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
			$database = JFactory::getDBO();
			$database->setQuery($query);
			$element = $database->loadObject();
			if(!empty($element)){
				if($element->product_type=='variant') {
					$this->selected_variant_id = $product_id;
					$filters=array('a.product_id='.$element->product_parent_id);
					hikashop_addACLFilters($filters,'product_access','a');
					$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
					$database->setQuery($query);
					$element = $database->loadObject();
					if(empty($element)){
						return;
					}
					$product_id = $element->product_id;
				}
				$productClass = hikashop_get('class.product');
				$productClass->addAlias($element);
				if(!$element->product_published) {
					return;
				}

				$query = 'SELECT file_id, file_name, file_description, file_path FROM ' . hikashop_table('file') . ' AS file WHERE file.file_type = \'product\' AND file_ref_id = '.(int)$product_id.' ORDER BY file_ordering ASC';
				$database->setQuery($query);
				$element->images = $database->loadObjectList();

				global $Itemid;
				$url_itemid='';
				if(!empty($Itemid)){
					$url_itemid='&Itemid='.$Itemid;
				}
				$product_url = hikashop_contentLink('product&task=show&cid='.(int)$element->product_id.'&name='.$element->alias.$url_itemid,$element);
				$this->assignRef('product_url',$product_url);
			}
		}

		if(hikashop_level(1)){
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);
			$contactFields = $fieldsClass->getFields('frontcomp',$element,'contact','checkout&task=state');
			$null=array();
			$fieldsClass->addJS($null,$null,$null);
			$fieldsClass->jsToggle($contactFields,$element,0);
			$extraFields = array('contact'=>&$contactFields);
			$requiredFields = array();
			$validMessages = array();
			$values = array('contact'=>$element);
			$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
			$fieldsClass->addJS($requiredFields,$validMessages,array('contact'));
			$this->assignRef('contactFields',$contactFields);
		}

		$this->assignRef('product',$element);

		$js = "
function checkFields(){
	var send = true;
	var name = document.getElementById('hikashop_contact_name');
	if(name != null){
		if(name.value == ''){
			name.className = name.className.replace('invalid','') + ' invalid';
			send = false;
		}else{
			name.className=name.className.replace('invalid','');
		}
	}
	var email = document.getElementById('hikashop_contact_email');
	if(email != null){
		if(email.value == ''){
			email.className = email.className.replace('invalid','') + ' invalid';
			send = false;
		}else{
			email.value = email.value.replace(/ /g,\"\");
			var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,14})+$/i;
			if(!email || !filter.test(email.value)){
				email.className = email.className.replace('invalid','') + ' invalid';
				return false;
			}else{
				email.className=email.className.replace('invalid','');
			}
		}
	}
	var altbody = document.getElementById('hikashop_contact_altbody');
	if(altbody != null){
		if(altbody.value == ''){
			altbody.className = altbody.className.replace('invalid','') + ' invalid';
			send = false;
		}else{
			altbody.className=altbody.className.replace('invalid','');
		}
	}
	if(!hikashopCheckChangeForm('contact','hikashop_contact_form')){
		send = false;
	}
	if(send == true){
		document.getElementById('toolbar').innerHTML='<img src=\"".HIKASHOP_IMAGES."spinner.gif\"/>';
		window.hikashop.submitform('send_email', 'hikashop_contact_form');
	}
}
window.hikashop.ready(function(){
	var name = document.getElementById('hikashop_contact_name');
	if(name != null){
		name.onclick=function(){
			name.className=name.className.replace('invalid','');
		}
	}
	var email = document.getElementById('hikashop_contact_email');
	if(email != null){
		email.onclick=function(){
			email.className=email.className.replace('invalid','');
		}
	}
	var altbody = document.getElementById('hikashop_contact_altbody');
	if(altbody != null){
		altbody.onclick=function(){
			altbody.className=altbody.className.replace('invalid','');
		}
	}
});
		";
		$doc->addScriptDeclaration($js);
	}

	public function status() {
		$app = JFactory::getApplication();

		$shipping_method = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
		$this->assignRef('shipping_method', $shipping_method);
		$shipping_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');
		$this->assignRef('shipping_id', $shipping_id);
		$shipping_data = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_data');
		$this->assignRef('shipping_data', $shipping_data);
		$payment_method = $app->getUserState(HIKASHOP_COMPONENT.'.payment_method');
		$this->assignRef('payment_method', $payment_method);
		$payment_id = $app->getUserState(HIKASHOP_COMPONENT.'.payment_id');
		$this->assignRef('payment_id', $payment_id);
		$payment_data = $app->getUserState(HIKASHOP_COMPONENT.'.payment_data');
		$this->assignRef('payment_data', $payment_data);

		$address = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_address');
	}

	public function waitlist() {
		$user = hikashop_loadUser(true);
		$this->assignRef('element',$user);

		$app = JFactory::getApplication();
		$product_id = (int)hikashop_getCID('product_id');
		$config =& hikashop_config();
		$this->assignRef('config',$config);

		$filters=array('a.product_id='.$product_id);
		hikashop_addACLFilters($filters,'product_access','a');
		$query = 'SELECT a.*,b.product_category_id, b.category_id, b.ordering FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$element = $database->loadObject();
		if(empty($element)){
			return;
		}
		if($element->product_type=='variant'){
			$this->selected_variant_id = $product_id;
			$filters=array('a.product_id='.$element->product_parent_id);
			hikashop_addACLFilters($filters,'product_access','a');
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('product_category').' AS b ON a.product_id = b.product_id WHERE '.implode(' AND ',$filters). ' LIMIT 1';
			$database->setQuery($query);
			$main = $database->loadObject();
			if(empty($main)){
				return;
			}
			$main->variants =array($element);
			$element = $main;
			$product_id = $element->product_id;

			$ids = array((int)$element->variants[0]->product_id, (int)$product_id);
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id IN ('.implode(',',$ids).') ORDER BY a.ordering ASC, b.characteristic_value ASC';
			$database->setQuery($query);
			$characteristics = $database->loadObjectList();
			if(!empty($characteristics)){

				$mainCharacteristics = array();
				foreach($characteristics as $characteristic){
					if($product_id==$characteristic->variant_product_id){
						$mainCharacteristics[$product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
					}
				}
				$cartClass = hikashop_get('class.cart');
				$cartClass->addCharacteristics($element, $mainCharacteristics, $characteristics);

				$productClass = hikashop_get('class.product');
				$productClass->checkVariant($element->variants[0], $element);

				$element=$element->variants[0];

			}

		}
		$productClass = hikashop_get('class.product');
		$productClass->addAlias($element);
		if(!$element->product_published){
			return;
		}
		$this->assignRef('product',$element);

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$product_url = hikashop_contentLink('product&task=show&cid='.(int)$element->product_id.'&name='.$element->alias.$url_itemid,$element);
		$this->assignRef('product_url',$product_url);
	}

	public function pagination_display($type, $divName, $id, $currentId, $position, $products) {
		if($position == 'top' || $position == 'bottom') {
			if($type == 'numbers') {
				echo '<a id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'" style="cursor:pointer; text-decoration:none">'.($id+1).'</a>';
			} elseif($type == 'rounds') {
				echo '<span class="hikashop_slide_dot_basic'.($currentId<$products ? ' hikashop_slide_dot_selected' : '').'" id="slide_number_'.$divName.'_'.$id.'"></span>';
			} elseif($type == 'thumbnails'){
				echo '<span class="'.($currentId<$products ? ' hikashop_pagination_images_selected' : 'hikashop_pagination_images').'" id="slide_number_'.$divName.'_'.$id.'">';
			} elseif($type == 'names') {
				echo '<span id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'">';
			}
			return;
		}

		if($type == 'numbers') {
			echo '<a id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'" style="cursor:pointer; text-decoration:none">'.($id+1).'</a><br/>';
		} elseif($type == 'rounds') {
			echo '<span class="hikashop_slide_dot_basic'.($currentId<$products ? ' hikashop_slide_dot_selected' : '').'" id="slide_number_'.$divName.'_'.$id.'"></span><br/>';
		} elseif($type == 'thumbnails') {
			echo '<span class="'.($currentId<$products ? ' hikashop_pagination_images_selected' : 'hikashop_pagination_images').'" id="slide_number_'.$divName.'_'.$id.'">';
		} elseif($type == 'names') {
			echo '<span id="slide_number_'.$divName.'_'.$id.'" class="hikashop_slide_numbers '.($currentId<$products ? ' hikashop_slide_pagination_selected' : '').'">';
		}
	}

	public function scaleImage($x, $y, $cx, $cy) {
		if(empty($cx)){
			$cx = ($x*$cy)/$y;
		}
		if(empty($cy)){
			$cy = ($y*$cx)/$x;
		}
		return array($cx,$cy);
	}

	public function checkSize(&$width, &$height, &$row) {
		$imageHelper = hikashop_get('helper.image');
		$imageHelper->checkSize($width,$height,$row);
	}

	public function add_to_cart_listing() {
		if($this->params->get('display_custom_item_fields','-1')=='-1'){
			$config =& hikashop_config();
			$default_params = $config->get('default_params');
			$this->params->set('display_custom_item_fields',@$default_params['display_custom_item_fields']);
		}
		$obj=$_SESSION['hikashop_product'];
		$this->row =& $obj;
		$this->params->set('js',1);
		$config =& hikashop_config();
		$this->assignRef('config',$config);

		$cartHelper=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$cartHelper->cartCount(true);
		$url = $this->init(true);
		$this->params->set('url', $url);
		$this->addToCartJs = $cartHelper->getJS($url);
		$this->assignRef('redirect_url', $url);
		$this->category_pathway = '';

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$this->assignRef('itemid',$url_itemid);
		$fieldsClass = hikashop_get('class.field');

		$js = $this->getJS();

		$modal = JHTML::script('system/modal.js',false,true,true);
		$body = JResponse::getBody();
		$alternate_body = false;
		if(empty($body)){
			$app = JFactory::getApplication();
			$body = $app->getBody();
			$alternate_body = true;
		}

		if(!strpos($body,'function hikashopModifyQuantity(') && !empty($this->addToCartJs)){
			$body=str_replace('</head>','<script type="text/javascript">'.$this->addToCartJs.'</script></head>',$body);
			JResponse::setBody($body);
		}

		if(!strpos($body,'/media/com_hikashop/js/hikashop.js')){
			$body=str_replace('</head>','<script src="'.HIKASHOP_LIVE.'media/com_hikashop/js/hikashop.js" type="text/javascript"></script></head>',$body);
			if($alternate_body){
				$app->setBody($body);
			}else{
				JResponse::setBody($body);
			}
		}

		if(hikaInput::get()->getInt('popup') && empty($_COOKIE['popup'])){
			if(!empty($js)){
				$body=str_replace('</head>','<script type="text/javascript">'.$js.'</script></head>',$body);
				if($alternate_body){
					$app->setBody($body);
				}else{
					JResponse::setBody($body);
				}
			}
		}

		if(!strpos($body, $modal)){
			$conf = JFactory::getConfig();
			if(HIKASHOP_J30){
				$debug = $conf->get('debug');
			} else {
				$debug = $conf->getValue('config.debug');
			}

			$mootools = JHtml::script('system/mootools-core.js', false, true, true, false, $debug);
			if(!strpos($body,$mootools))$body=str_replace('</head>','<script src="'.$mootools.'" type="text/javascript"></script></head>',$body);
			$mootoolsmore = JHtml::script('system/mootools-more.js', false, true, true, false, $debug);
			if(!strpos($body,$mootoolsmore))$body=str_replace('</head>','<script src="'.$mootoolsmore.'" type="text/javascript"></script></head>',$body);
			$core = JHtml::script('system/core.js', false, true,true);
			if(!strpos($body,$core))$body=str_replace('</head>','<script src="'.$core.'" type="text/javascript"></script></head>',$body);
			if(!HIKASHOP_J16){
				$modalcss = JHtml::stylesheet('system/modal.css', '',array());
			}else{
				$modalcss = JHtml::stylesheet('system/modal.css', array(), true,true);
			}

			if(!strpos($body,$modal)) $body=str_replace('</head>','<script src="'.$modal.'" type="text/javascript"></script></head>',$body);
			if(!strpos($body,$modalcss)) $body=str_replace('</head>','<link rel="stylesheet" href="'.$modalcss.'" type="text/css" /></head>',$body);
			if(!strpos($body,"SqueezeBox.assign($$('a.modal'), {")){
				$js = "
window.hikashop.ready( function() {
	SqueezeBox.initialize();
	SqueezeBox.assign($$('a.modal'),{ parse: 'rel' });
});
";
				$body=str_replace('</head>','<script type="text/javascript">'.$js.'</script></head>',$body);
			}
			JResponse::setBody($body);
		}

		$this->assignRef('fieldsClass',$fieldsClass);
	}

	public function listing_price() {
		$obj = $_SESSION['hikashop_product'];
		$this->row =& $obj;

		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	public function getProductQuantityLayout(&$row) {
		if(empty($this->cartHelper)) {
			$cartHelper = hikashop_get('helper.cart');
			$this->assignRef('cartHelper', $cartHelper);
		}
		return $this->cartHelper->getProductQuantityLayout($row);
	}

	public function getQuantityCounter() {
		if(empty($this->cartHelper)) {
			$cartHelper = hikashop_get('helper.cart');
			$this->assignRef('cartHelper', $cartHelper);
		}
		return $this->cartHelper->getQuantityCounter($this);
	}

	protected function handleAddToCartPopup($function) {
		if(in_array($function, array('cart', 'add_to_cart_listing', 'listing_price')))
			return;
		if(hikaInput::get()->getInt('popup', 0) == 0 || !empty($_COOKIE['popup']) || hikaInput::get()->getVar('tmpl') == 'component')
			return;

		$app = JFactory::getApplication();
		if(!$app->getUserState( HIKASHOP_COMPONENT.'.popup', 0))
			return;

		$js = $this->getJS();
		$app->setUserState( HIKASHOP_COMPONENT.'.popup', 0);
		if(!empty($js)) {
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration("\r\n<!--\r\n".trim($js)."\r\n//-->\r\n");
		}
	}
}
