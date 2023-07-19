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
class hikashopProductClass extends hikashopClass{
	var $tables = array('price','variant','product_related','product_related','product_category','product');
	var $pkeys = array('price_product_id','variant_product_id','product_related_id','product_id','product_id','product_id');
	var $namekeys = array('','','','');
	var $parent = 'product_parent_id';
	var $toggle = array('product_published'=>'product_id');
	var $type = '';

	function get($id, $default = null) {
		static $cachedElements = array();
		if($id == 'reset_cache') {
			$cachedElements = array();
			return true;
		}

		if((int)$id == 0)
			return true;

		if(!isset($cachedElements[$id])) {
			$cachedElements[$id] = parent::get($id);
			if($cachedElements[$id])
				$this->addAlias($cachedElements[$id]);
		}
		if(!is_object($cachedElements[$id]))
			return $cachedElements[$id];

		$copy = new stdClass();
		foreach(get_object_vars($cachedElements[$id]) as $key => $val) {
			$copy->$key = $val;
		}
		return $copy;
	}

	function saveForm() {
		$legacy = hikaInput::get()->getInt('legacy', 0);
		if(!$legacy) {
			$subtask = hikaInput::get()->getCmd('subtask', '');
			if($subtask == 'variant')
				return $this->backSaveVariantForm();
			return $this->backSaveForm();
		}

		$oldProduct = null;
		$product_id = hikashop_getCID('product_id');
		$categories = hikaInput::get()->get('category', array(), 'array');
		$app = JFactory::getApplication();
		hikashop_toInteger($categories);
		$newCategories = array();
		if(count($categories)){
			foreach($categories as $category){
				$newCategory = new stdClass();
				$newCategory->category_id = $category;
				$newCategories[] = $newCategory;
			}
		}
		if($product_id){
			$oldProduct = $this->get($product_id);
			$oldProduct->categories = $newCategories;
		}else{
			$oldProduct = new stdClass;
			$oldProduct->categories = $newCategories;
		}
		$fieldsClass = hikashop_get('class.field');
		$element = $fieldsClass->getInput('product', $oldProduct);

		$status = true;
		if(empty($element)){
			$element = $_SESSION['hikashop_product_data'];
			$status = false;
		}
		$new = empty($product_id);
		if($product_id){
			$element->product_id = $product_id;
		}

		if(isset($element->product_price_percentage)){
			$element->product_price_percentage = hikashop_toFloat($element->product_price_percentage);
		}

		$element->categories = $categories;
		if(empty($element->product_id) && !count($element->categories) && (empty($element->product_type) || $element->product_type == 'main')) {
			$id = $app->getUserState(HIKASHOP_COMPONENT.'.product.filter_id');
			if(empty($id) || !is_numeric($id)){
				$id='product';
				$categoryClass = hikashop_get('class.category');
				$categoryClass->getMainElement($id);
			}
			if(!empty($id)){
				$element->categories = array($id);
			}
		}
		$element->related = array();
		$related = hikaInput::get()->get('related', array(), 'array');
		hikashop_toInteger($related);
		if(!empty($related)){
			$related_ordering = hikaInput::get()->get('related_ordering', array(), 'array');
			hikashop_toInteger($related_ordering);
			foreach($related as $id){
				$obj = new stdClass();
				$obj->product_related_id = $id;
				$obj->product_related_ordering = $related_ordering[$id];
				$element->related[$id] = $obj;
			}
		}
		$options = hikaInput::get()->get('options', array(), 'array');
		$element->options = array();
		hikashop_toInteger($element->options);
		if(!empty($options)){
			$related_ordering = hikaInput::get()->get('options_ordering', array(), 'array');
			hikashop_toInteger($related_ordering);
			foreach($options as $id){
				$obj = new stdClass();
				$obj->product_related_id = $id;
				$obj->product_related_ordering = $related_ordering[$id];
				$element->options[$id] = $obj;
			}
		}

		$bundle = hikaInput::get()->get('bundle', array(), 'array');
		$element->$bundle = array();
		hikashop_toInteger($element->$bundle);
		if(!empty($bundle)){
			$related_ordering = hikaInput::get()->get('bundle_ordering', array(), 'array');
			hikashop_toInteger($related_ordering);
			foreach($options as $id){
				$obj = new stdClass();
				$obj->product_related_id = $id;
				$obj->product_related_ordering = $related_ordering[$id];
				$element->$bundle[$id] = $obj;
			}
		}

		$element->images = hikaInput::get()->get('image', array(), 'array');
		hikashop_toInteger($element->images);
		$element->files = hikaInput::get()->get('file', array(), 'array');
		hikashop_toInteger($element->files);

		$element->imagesorder = hikaInput::get()->get('imageorder', array(), 'array');
		hikashop_toInteger($element->imagesorder);

		$element->tags = hikaInput::get()->get('tags', array(), 'array');

		$priceData = hikaInput::get()->get('price', array(), 'array');
		$element->prices = array();
		foreach($priceData as $column => $value) {
			hikashop_secureField($column);
			if($column=='price_access'){
				if(!empty($value)){
					foreach($value as $k => $v){
						$value[$k] = preg_replace('#[^a-z0-9,]#i','',$v);
					}
				}
			}elseif($column=='price_site_id'){
				jimport('joomla.filter.filterinput');
				$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
				foreach($value as $k => $v){
					if(!is_null($safeHtmlFilter)) $value[$k] = str_replace('[unselected]','',$safeHtmlFilter->clean($v, 'string'));
				}
			}elseif($column == 'price_value') {
				$this->toFloatArray($value);
			}else{
				hikashop_toInteger($value);
			}
			foreach($value as $k => $val){
				if($column=='price_min_quantity' && $val==1){
					$val=0;
				}
				if(!isset($element->prices[$k])) $element->prices[$k] = new stdClass();
				$element->prices[$k]->$column = $val;
			}
		}

		$this->recalculateSortPrice($element);

		$element->oldCharacteristics = array();
		if(isset($element->product_type) && $element->product_type=='variant'){
			$characteristics = hikaInput::get()->get('characteristic', array(), 'array');
			hikashop_toInteger($characteristics);
			if(empty($characteristics)){
				$element->characteristics = array();
			}else{
				$this->database->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_id IN ('.implode(',',$characteristics).')');
				$element->characteristics = $this->database->loadObjectList('characteristic_id');
			}
		}else{
			$characteristics = hikaInput::get()->get('characteristic', array(), 'array');
			hikashop_toInteger($characteristics);
			if(!empty($element->product_id)){
				$this->database->setQuery('SELECT b.characteristic_id FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id ='.$element->product_id.' AND b.characteristic_parent_id=0');
				$element->oldCharacteristics = $this->database->loadColumn();
			}
			if(empty($element->oldCharacteristics)){
				$element->oldCharacteristics = array();
			}
			if(!empty($characteristics)){
				$characteristics_ordering = hikaInput::get()->get('characteristic_ordering', array(), 'array');
				hikashop_toInteger($characteristics_ordering);
				$characteristics_default = hikaInput::get()->get('characteristic_default', array(), 'array');
				hikashop_toInteger($characteristics_default);
				$this->database->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id IN ('.implode(',',$characteristics).')');
				$values = $this->database->loadObjectList();
				$element->characteristics = array();
				foreach($characteristics as $k => $id){
					$obj = new stdClass();
					$obj->characteristic_id = $id;
					$obj->ordering = $characteristics_ordering[$k];
					$obj->default_id = (int)@$characteristics_default[$k];
					$obj->values = array();
					foreach($values as $value){
						if($value->characteristic_parent_id==$id){
							$obj->values[$value->characteristic_id]=$value->characteristic_value;
						}
					}
					$element->characteristics[(int)$id] = $obj;
				}
			}
		}
		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->getTranslations($element);
		if(!empty($element->product_sale_start)){
			$element->product_sale_start=hikashop_getTime($element->product_sale_start);
		}
		if(!empty($element->product_sale_end)){
			$element->product_sale_end=hikashop_getTime($element->product_sale_end);
		}

		$element->product_max_per_order=(int)$element->product_max_per_order;

		$element->product_description = hikaInput::get()->getRaw('product_description','');
		if(!empty($element->product_id) && !empty($element->product_code)){
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code  = '.$this->database->Quote($element->product_code).' AND product_id!='.(int)$element->product_id.' LIMIT 1';
			$this->database->setQuery($query);
			if($this->database->loadResult()){
				$app->enqueueMessage(JText::_( 'DUPLICATE_PRODUCT' ), 'error');
				hikaInput::get()->set( 'fail', $element  );
				return false;
			}
		}

		$config =& hikashop_config();
		if(( empty($element->product_weight) || $element->product_weight == 0 ) && !$config->get('force_shipping',0)){
			$this->database->setQuery('SELECT shipping_id FROM '.hikashop_table('shipping').' WHERE shipping_published=1');
			if($this->database->loadResult()){
				$app->enqueueMessage(JText::_( 'SHIPPING_METHODS_WONT_DISPLAY_IF_NO_WEIGHT' ));
			}
		}

		if($config->get('alias_auto_fill',1) && empty($element->product_alias)){
			$this->addAlias($element);
			if($config->get('sef_remove_id',0)){
				$int_at_the_beginning = (int)$element->alias;
				if($int_at_the_beginning){
					$element->alias = $config->get('alias_prefix','p').$element->alias;
				}
			}
			$element->product_alias = $element->alias;
			unset($element->alias);
		}
		if(!empty($element->product_alias)){
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_alias='.$this->database->Quote($element->product_alias);
			$this->database->setQuery($query);
			$product_with_same_alias = $this->database->loadResult();
			if($product_with_same_alias && (empty($element->product_id) || $product_with_same_alias!=$element->product_id)){
				$app->enqueueMessage(JText::_( 'ELEMENT_WITH_SAME_ALIAS_ALREADY_EXISTS' ), 'error');
			}
		}

		$autoKeyMeta = $config->get('auto_keywords_and_metadescription_filling',0);
		if($autoKeyMeta){
			$seoHelper = hikashop_get('helper.seo');
			$seoHelper->autoFillKeywordMeta($element, "product");
		}

		if($status){
			$status = $this->save($element, false, true);
		}else{
			hikaInput::get()->set( 'fail', $element  );
			return $status;
		}

		if($status){
			$this->updateCategories($element,$status);
			$this->updatePrices($element,$status);
			$this->updateFiles($element,$status,'files');
			$this->updateFiles($element,$status,'images',$element->imagesorder);
			$this->updateRelated($element,$status,'related');
			$this->updateRelated($element,$status,'options');
			$this->updateRelated($element,$status,'bundle');
			$this->updateCharacteristics($element,$status);
			$translationHelper->handleTranslations('product',$status,$element, 'hikashop_', null, true);

			if($new)
				$app->triggerEvent( 'onAfterProductCreate', array( & $element ) );
			else
				$app->triggerEvent( 'onAfterProductUpdate', array( & $element ) );
		}else{
			hikaInput::get()->set( 'fail', $element  );
			if(empty($element->product_id) && empty($element->product_code) && empty($element->product_name)){
				$app->enqueueMessage(JText::_( 'SPECIFY_NAME_AND_CODE' ), 'error');
			}else{
				$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code  = '.$this->database->Quote($element->product_code).' LIMIT 1';
				$this->database->setQuery($query);
				if($this->database->loadResult()){
					$app->enqueueMessage(JText::_( 'DUPLICATE_PRODUCT' ), 'error');
				}
			}
		}
		return $status;
	}

	public function backSaveForm() {
		$this->_saveAreas('product');
		$app = JFactory::getApplication();
		if(empty($this->db))
			$this->db = JFactory::getDBO();
		$config = hikashop_config();
		$product_id = hikashop_getCID('product_id');
		$fieldsClass = hikashop_get('class.field');

		$formData = hikaInput::get()->get('data', array(), 'array');
		$formProduct = array();
		if(!empty($formData['product']))
			$formProduct = $formData['product'];

		$new = empty($product_id);
		$oldProduct = null;
		if(!$new) {
			$oldProduct = $this->get($product_id);
		} else {
			$oldProduct = new stdClass();
			$oldProduct->categories = array(0);
			if(!empty($formProduct['categories']))
				$oldProduct->categories = $formProduct['categories'];
			hikashop_toInteger($oldProduct->categories);
			if(!hikashop_acl('product/add'))
				return false;
		}

		$product = $fieldsClass->getInput('product', $oldProduct, true, 'data', false, 'all');
		$status = true;
		if(empty($product)) {
			$product = $_SESSION['hikashop_product_data'];
			$status = false;
		}

		$this->db->setQuery('SELECT field.* FROM '.hikashop_table('field').' as field WHERE field.field_table = '.$this->db->Quote('product').' ORDER BY field.`field_ordering` ASC');
		$all_fields = $this->db->loadObjectList('field_namekey');
		$edit_fields = hikashop_acl('product/edit/customfields');
		foreach($all_fields as $fieldname => $field) {
			if(!$edit_fields || empty($field->field_published) || empty($field->field_backend)) {
				unset($product->$fieldname);
			}
		}

		$product->product_id = (int)$product_id;
		if(!$new) {
			$product->product_type = $oldProduct->product_type;
			unset($product->product_parent_id);
		}

		if(!hikashop_acl('product/edit/name')) { unset($product->product_name); }
		if(!hikashop_acl('product/edit/code')) { unset($product->product_code); }
		if(!hikashop_acl('product/edit/volume')) { unset($product->product_volume); }
		if(!hikashop_acl('product/edit/published')) { unset($product->product_published); }
		if(!hikashop_acl('product/edit/manufacturer'))
			unset($product->product_manufacturer_id);
		else
			$product->product_manufacturer_id = (int) @$product->product_manufacturer_id;
		if(!hikashop_acl('product/edit/pagetitle')) { unset($product->product_page_title); }
		if(!hikashop_acl('product/edit/url')) { unset($product->product_url); }
		if(!hikashop_acl('product/edit/metadescription')) { unset($product->product_meta_description); }
		if(!hikashop_acl('product/edit/keywords')) { unset($product->product_keywords); }
		if(!hikashop_acl('product/edit/alias')) { unset($product->product_alias); }
		if(!hikashop_acl('product/edit/acl')) { unset($product->product_access); }
		if(!hikashop_acl('product/edit/msrp')) {
			unset($product->product_msrp);
		} else {
			$product->product_msrp = hikashop_toFloat($product->product_msrp);
		}
		if(!hikashop_acl('product/edit/canonical')) { unset($product->product_canonical); }
		if(!hikashop_acl('product/edit/warehouse'))
			unset($product->product_warehouse_id);
		else
			$product->product_warehouse_id = (int) @$product->product_warehouse_id;
		if(!hikashop_acl('product/edit/tax')) { unset($product->product_tax_id); }

		if(!hikashop_acl('product/edit/weight')) {
			unset($product->product_weight);
		}elseif( ( empty($product->product_weight) || $product->product_weight == 0 ) && !$config->get('force_shipping',0) ){
			$this->database->setQuery('SELECT shipping_id FROM '.hikashop_table('shipping').' WHERE shipping_published=1');
			if($this->database->loadResult()){
				$app->enqueueMessage(JText::_( 'SHIPPING_METHODS_WONT_DISPLAY_IF_NO_WEIGHT' ));
			}
		}

		if(hikashop_acl('product/edit/qtyperorder')) {
			if(isset($product->product_max_per_order))
				$product->product_max_per_order = (int)$product->product_max_per_order;
			if(isset($product->product_min_per_order))
				$product->product_min_per_order = (int)$product->product_min_per_order;
		} else {
			unset($product->product_max_per_order);
			unset($product->product_min_per_order);
		}

		unset($product->tags);
		if(hikashop_acl('product/edit/tags')) {
			$tagsHelper = hikashop_get('helper.tags');
			if(!empty($tagsHelper) && $tagsHelper->isCompatible())
				$product->tags = empty($formData['tags']) ? array() : $formData['tags'];
		}

		$removeFields = array(
			'hit', 'created', 'modified', 'last_seen_date', 'sales', 'average_score', 'total_vote', 'status',

		);
		foreach($removeFields as $rf) {
			$rf = 'product_'.$rf;
			unset($product->$rf);
		}

		if(hikashop_acl('product/edit/description')) {
			$product->product_description = trim(hikaInput::get()->getRaw('product_description', ''));

			$product->product_description_raw = hikaInput::get()->getRaw('product_description_raw', null);
			$default_description_type = $config->get('default_description_type', '');
			if(empty($product_id) && !empty($default_description_type))
				$product->product_description_type = $default_description_type;
			if(!empty($oldProduct->product_description_type))
				$product->product_description_type = $oldProduct->product_description_type;

			if($product->product_description_raw !== null && !empty($product->product_description_type) && $product->product_description_type != 'html') {
				$contentparserType = hikashop_get('type.contentparser');
				$description_types = $contentparserType->load();
				if(isset($description_types[ $product->product_description_type ])) {

					$product->product_description = $product->product_description_raw;

					JPluginHelper::importPlugin('hikashop');
					$app = JFactory::getApplication();
					$app->triggerEvent('onHkContentParse', array( &$product->product_description, $product->product_description_type ));
				} else {
					$product->product_description_type = null;
					unset($product->product_description);
				}
			}

			if((int)$config->get('safe_product_description', 0)) {
				$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
				$product->product_description = $safeHtmlFilter->clean($product->product_description, 'string');
			}
		}

		$categoryClass = hikashop_get('class.category');
		$rootCategory = 'product';
		$categoryClass->getMainElement($rootCategory);

		$product->categories = array();
		if(!empty($formProduct['categories']))
			$product->categories = $formProduct['categories'];
		hikashop_toInteger($product->categories);
		if(empty($product->product_id) && !count($product->categories) && !empty($rootCategory)) {
			$product->categories = array($rootCategory);
		}

		if(hikashop_acl('product/edit/related')) {
			$related = @$formProduct['related'];
			$product->related = array();
			if(!empty($related)) {
				$k = 0;
				foreach($related as $r) {
					$obj = new stdClass();
					$obj->product_related_id = (int)$r;
					$obj->product_related_ordering = $k++;
					$product->related[] = $obj;
				}
			}
		} else
			unset($product->related);

		if(hikashop_acl('product/edit/options')) {
			$options = @$formProduct['options'];
			$product->options = array();
			if(!empty($options)) {
				$k = 0;
				foreach($options as $r) {
					$obj = new stdClass();
					$obj->product_related_id = (int)$r;
					$obj->product_related_ordering = $k++;
					$product->options[] = $obj;
				}
			}
		} else
			unset($product->options);

		if(hikashop_acl('product/edit/bundle') && hikashop_level(1)) {
			$options = @$formProduct['bundle'];
			$product->bundle = array();
			if(!empty($options)) {
				$k = 0;
				foreach($options as $pid => $r) {
					if(empty($r))
						continue;

					$obj = new stdClass();
					$obj->product_related_id = (int)$pid;
					$obj->product_related_quantity = (int)$r;
					$obj->product_related_ordering = $k++;
					$product->bundle[] = $obj;
				}
			}
		} else
			unset($product->bundle);

		if(!empty($oldProduct) && !empty($oldProduct->product_id)) {
			$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id = ' . (int)$oldProduct->product_id;
			$this->db->setQuery($query);
			$oldProduct->prices = $this->db->loadObjectList();
		}

		$priceData = hikaInput::get()->get('price', array(), 'array');
		$product->prices = array();
		foreach($priceData as $k => $value) {
			if((int)$k == 0 && $k !== 0 && $k !== '0')
				continue;

			$price_id = (int)@$value['price_id'];
			if(!empty($oldProduct) && !empty($price_id) && !empty($oldProduct->prices)) {
				foreach($oldProduct->prices as $p) {
					if($p->price_id == $price_id) {
						$product->prices[$k] = $p;
						break;
					}
				}
			}

			if(empty($product->prices[$k]))
				$product->prices[$k] = new stdClass();
			if(isset($value['price_value']))
				$product->prices[$k]->price_value = hikashop_toFloat($value['price_value']);
			if(isset($value['price_access']))
				$product->prices[$k]->price_access = preg_replace('#[^a-z0-9,]#i', '', $value['price_access']);
			if(!empty($value['price_start_date']))
				$product->prices[$k]->price_start_date = hikashop_getTime($value['price_start_date']);
			else
				$product->prices[$k]->price_start_date = '';
			if(!empty($value['price_end_date']))
				$product->prices[$k]->price_end_date = hikashop_getTime($value['price_end_date']);
			else
				$product->prices[$k]->price_end_date = '';

			if(isset($value['price_users']))
				$product->prices[$k]->price_users = preg_replace('#[^0-9,]#i', '', $value['price_users']);

			if(isset($value['price_currency_id']))
				$product->prices[$k]->price_currency_id = (int)$value['price_currency_id'];
			if(empty($product->prices[$k]->price_currency_id))
				$product->prices[$k]->price_currency_id = $config->get('main_currency',1);

			if(isset($value['price_site_id'])){
				jimport('joomla.filter.filterinput');
				$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
				if(!is_null($safeHtmlFilter))
					$value['price_site_id'] = str_replace('[unselected]','',$safeHtmlFilter->clean($value['price_site_id'], 'string'));
				$product->prices[$k]->price_site_id = $value['price_site_id'];
			}

			if(isset($value['price_min_quantity'])) {
				$product->prices[$k]->price_min_quantity = (int)$value['price_min_quantity'];
				if($product->prices[$k]->price_min_quantity == 1)
					$product->prices[$k]->price_min_quantity = 0;
			}
			if(empty($product->prices[$k]->price_min_quantity))
				$product->prices[$k]->price_min_quantity = 0;
		}

		if(isset($product->product_price_percentage))
			$product->product_price_percentage = hikashop_toFloat($product->product_price_percentage);

		$this->recalculateSortPrice($product);

		unset($product->imagesorder);
		unset($product->images);
		if(hikashop_acl('product/edit/images')) {
			$product->images = @$formProduct['product_images'];
			hikashop_toInteger($product->images);

			$product->imagesorder = array();
			foreach($product->images as $k => $v) {
				$product->imagesorder[$v] = $k;
			}
		}
		unset($product->product_images);

		unset($product->files);
		if(hikashop_acl('product/edit/files')) {
			$product->files = @$formProduct['product_files'];
			hikashop_toInteger($product->files);

			$product->filesorder = array();
			foreach($product->files as $k => $v) {
				$product->filesorder[$v] = $k;
			}
		}
		unset($product->product_files);

		if(hikashop_acl('product/edit/saledates')) {
			if(!empty($product->product_sale_start))
				$product->product_sale_start = hikashop_getTime($product->product_sale_start);
			else
				$product->product_sale_start = 0;

			if(!empty($product->product_sale_end))
				$product->product_sale_end = hikashop_getTime($product->product_sale_end);
			else
				$product->product_sale_end = 0;
		} else {
			unset($product->product_sale_start);
			unset($product->product_sale_end);
		}

		if(!empty($product->product_code))
			$product->product_code = trim($product->product_code);
		if(!empty($product->product_id) && isset($product->product_code)){
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code  = '.$this->database->Quote($product->product_code).' AND product_id!='.(int)$product->product_id.' LIMIT 1';
			$this->database->setQuery($query);
			if($this->database->loadResult()){
				$app->enqueueMessage(JText::_( 'DUPLICATE_PRODUCT' ), 'error');
				hikaInput::get()->set( 'fail', $product  );
				return false;
			}
		}


		unset($product->characteristics);
		unset($product->characteristic);
		if(hikashop_acl('product/edit/characteristics') && !empty($formData['characteristics']) && is_array($formData['characteristics'])) {
			$characteristics = $formData['characteristics'];
			hikashop_toInteger($characteristics);

			if($new) {
				$characteristics = $this->checkProductCharacteristics($characteristics, 0, true);
				if(!empty($characteristics))
					$product->characteristics = $characteristics;
			} else
				$product->characteristics = $characteristics;
		}

		if($config->get('alias_auto_fill', 1) && empty($product->product_alias) && !empty($product->product_name)) {
			$this->addAlias($product);
			if($config->get('sef_remove_id', 0) && (int)$product->alias > 0)
				$product->alias = $config->get('alias_prefix', 'p') . $product->alias;
			$product->product_alias = $product->alias;
			unset($product->alias);
		}
		if(!empty($product->product_alias)) {
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_alias='.$this->db->Quote($product->product_alias);
			$this->db->setQuery($query);
			$product_with_same_alias = $this->db->loadResult();
			if($product_with_same_alias && (empty($product->product_id) || $product_with_same_alias!=$product->product_id)) {
				$app->enqueueMessage(JText::_('ELEMENT_WITH_SAME_ALIAS_ALREADY_EXISTS'), 'error');
				hikaInput::get()->set('fail', $product);
				return false;
			}
		}
		$autoKeyMeta = $config->get('auto_keywords_and_metadescription_filling', 0);
		if($autoKeyMeta) {
			$seoHelper = hikashop_get('helper.seo');
			$seoHelper->autoFillKeywordMeta($product, 'product');
		}


		if($status) {
			$status = $this->save($product, false, true);
		} else {
			hikaInput::get()->set('fail', $product);
			return $status;
		}

		if($status) {
			if(hikashop_acl('product/edit/category') || $new)
				$this->updateCategories($product, $status);
			if(hikashop_acl('product/edit/price'))
				$this->updatePrices($product, $status);
			if(hikashop_acl('product/edit/files'))
				$this->updateFiles($product, $status, 'files', $product->filesorder);
			if(hikashop_acl('product/edit/images'))
				$this->updateFiles($product, $status, 'images', $product->imagesorder);
			if(hikashop_acl('product/edit/related'))
				$this->updateRelated($product, $status, 'related');
			if(hikashop_acl('product/edit/options'))
				$this->updateRelated($product, $status, 'options');
			if(hikashop_acl('product/edit/bundle') && hikashop_level(1))
				$this->updateRelated($product, $status, 'bundle');

			if(hikashop_acl('product/edit/characteristics') && !empty($product->characteristics)) {
				if($new) {
					$product->product_type = 'main';
					$this->updateCharacteristics($product, $status, 0);
				} else {
					$query = 'UPDATE '. hikashop_table('variant') . ' SET ordering = CASE variant_characteristic_id';
					foreach($product->characteristics as $key => $val) {
						$query .= ' WHEN ' . (int)$val . ' THEN ' . ($key + 1);
					}
					$query .= ' ELSE ordering END WHERE variant_characteristic_id IN ('.implode(',', $product->characteristics).') AND variant_product_id = '.(int)$status;
					$this->db->setQuery($query);
					$this->db->execute();

					if(!empty($product->product_code) && !empty($oldProduct->product_code) && $product->product_code != $oldProduct->product_code) {
						if(HIKASHOP_J30)
							$product_code = "'" . $this->db->escape($oldProduct->product_code, true) . "%'";
						else
							$product_code = "'" . $this->db->getEscaped($oldProduct->product_code, true) . "%'";

						$query = 'UPDATE '.hikashop_table('product').
								' SET `product_code` = REPLACE(`product_code`,' . $this->db->Quote($oldProduct->product_code) . ',' . $this->db->Quote($product->product_code) . ')'.
								' WHERE `product_code` LIKE '.$product_code.' AND product_parent_id = '.(int)$product->product_id.' AND product_type = '.$this->db->Quote('variant');
						$this->db->setQuery($query);
						$this->db->execute();
					}
				}
			}

			if($new)
				$app->triggerEvent( 'onAfterProductCreate', array( & $product ) );
			else
				$app->triggerEvent( 'onAfterProductUpdate', array( & $product ) );

			if(hikashop_acl('product/variant') && !empty($formData['variant']))
				$this->backSaveVariantForm();
		} else {
			hikaInput::get()->set('fail', $product);
			if(empty($product->product_id) && empty($product->product_code) && empty($product->product_name)) {
				$app->enqueueMessage(JText::_('SPECIFY_NAME_AND_CODE'), 'error');
			} else {
				$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code  = '.$this->db->Quote($product->product_code) . ' AND NOT (product_id = ' . (int)(@$product->product_id) . ')';
				$this->db->setQuery($query, 0, 1);
				if($this->db->loadResult())
					$app->enqueueMessage(JText::_('DUPLICATE_PRODUCT'), 'error');
				else
					$app->enqueueMessage(JText::_('PRODUCT_SAVE_UNKNOWN_ERROR'), 'error');
			}
		}
		return $status;
	}

	private function _saveAreas($type, $parent = '') {
		$config = hikashop_config();
		$configData = array('form_custom' => hikaInput::get()->getInt('config_form_custom', 1));
		$config->save($configData);

		if(!hikashop_isAllowed($config->get('acl_product_customize','all')))
			return true;

		$inputs = array('order', 'fields');
		$config = hikashop_config();
		$configData = array();
		$reset_name = $type.'_reset_custom';
		if($type == 'variant') {
			$reset_name = 'product_reset_custom';
		}
		$reset = hikaInput::get()->getInt($reset_name, 0);
		foreach($inputs as $input) {
			if($reset) {
				$configData['product_areas_'.$input] = '';
				$configData['variant_areas_'.$input] = '';
			} else {
				$areas_order = hikaInput::get()->getString($type.'_areas_'.$input, '');
				if(!empty($areas_order)) {
					if(!empty($parent)) {
						$main_order = $config->get($parent.'_areas_'.$input);
						if($main_order == $areas_order)
							continue;
					}
					$configData[$type.'_areas_'.$input] = $areas_order;
				}
			}
		}
		if(count($configData))
			$config->save($configData);
	}

	public function backSaveVariantForm() {
		$this->_saveAreas('variant', 'product');
		$app = JFactory::getApplication();
		$config = hikashop_config();
		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$product_id = hikashop_getCID('variant_id');
		$parent_product_id = hikaInput::get()->getInt('product_id', 0);
		$fieldClass = hikashop_get('class.field');

		$formData = hikaInput::get()->get('data', array(), 'array');
		$formVariant = array();
		if(!empty($formData['variant'])) {
			$formVariant = $formData['variant'];
		}
		if(!empty($formData['product'])) {
			$product_id = (int)$formVariant['product_id'];
		}

		if(!hikashop_acl('product/variant'))
			return false;

		$new = false;
		$oldProduct = null;
		$productParent = null;
		if(empty($product_id))
			$new = true;
		if(!$new) {
			$oldProduct = $this->get($product_id);

			if($oldProduct->product_type != 'variant')
				return false;
			if((int)$oldProduct->product_parent_id != $parent_product_id && $parent_product_id > 0)
				return false;

			if(empty($parent_product_id))
				$parent_product_id = (int)$oldProduct->product_parent_id;
		} else {
			if(!hikashop_acl('product/add'))
				return false;

			if(empty($parent_product_id))
				return false;

			$productParent = $this->get($parent_product_id);
			if($productParent->product_type != 'main')
				return false;


		}

		$oldProduct->categories = $this->getCategories($parent_product_id);
		$product = $fieldClass->getInput('variant', $oldProduct, true, 'data', false, 'all');
		if(empty($product))
			return false;

		$this->db->setQuery('SELECT field.* FROM '.hikashop_table('field').' as field WHERE field.field_table = '.$this->db->Quote('product').' ORDER BY field.`field_ordering` ASC');
		$all_fields = $this->db->loadObjectList('field_namekey');
		$edit_fields = hikashop_acl('product/variant/customfields');
		foreach($all_fields as $fieldname => $field) {
			if(!$edit_fields || empty($field->field_published) || empty($field->field_backend)) {
				unset($product->$fieldname);
			}
		}

		$product->product_id = $product_id;
		$product->product_type = 'variant';
		$product->product_parent_id = $parent_product_id; // TODO

		if(hikashop_acl('product/variant/characteristics')) {
			$product->characteristics = array();
			unset($product->characteristic);

			$query = 'SELECT v.*, c.* FROM '.hikashop_table('variant').' AS v '.
				' INNER JOIN '.hikashop_table('characteristic').' as c ON v.variant_characteristic_id = c.characteristic_id '.
				' WHERE variant_product_id = ' . (int)$parent_product_id;
			$this->db->setQuery($query);
			$characteristics = $this->db->loadObjectList('characteristic_id');

			$characteristic_ids = array();
			foreach($characteristics as $characteristic) {
				if((int)$characteristic->characteristic_parent_id == 0)
					$characteristic_ids[(int)$characteristic->characteristic_id] = (int)$characteristic->characteristic_id;
				else
					$characteristics[(int)$characteristic->characteristic_parent_id]->default = (int)$characteristic->characteristic_id;
			}

			if(count($characteristic_ids)){
				$query = 'SELECT c.* FROM ' . hikashop_table('characteristic') . ' AS c '.
					' WHERE c.characteristic_parent_id IN ('.implode(',', $characteristic_ids).')';
				$this->db->setQuery($query);
				$characteristics_values = $this->db->loadObjectList('characteristic_id');
			}

			foreach($characteristics as $characteristic) {
				if((int)$characteristic->characteristic_parent_id == 0) {
					$i = (int)$characteristic->characteristic_id;
					$v = (int)@$formVariant['characteristic'][$i];

					if(isset($characteristics_values[$v]) && $characteristics_values[$v]->characteristic_parent_id = $i)
						$product->characteristics[$v] = $i;
					else
						$product->characteristics[$characteristic->default] = $i;
				}
			}
		} else {
			unset($product->characteristics);
			unset($product->characteristic);
		}

		if(!hikashop_acl('product/variant/name')) { unset($product->product_name); }
		if(!hikashop_acl('product/variant/code')) { unset($product->product_code); }
		if(!hikashop_acl('product/variant/weight')) { unset($product->product_weight); }
		if(!hikashop_acl('product/variant/volume')) { unset($product->product_volume); }
		if(!hikashop_acl('product/variant/published')) { unset($product->product_published); }
		if(!hikashop_acl('product/variant/acl')) { unset($product->product_access); }

		if(hikashop_acl('product/variant/qtyperorder')) {
			if(isset($product->product_max_per_order))
				$product->product_max_per_order = (int)$product->product_max_per_order;
			if(isset($product->product_min_per_order))
				$product->product_min_per_order = (int)$product->product_min_per_order;
		} else {
			unset($product->product_max_per_order);
			unset($product->product_min_per_order);
		}

		$removeFields = array(
			'manufacturer_id', 'page_title', 'url', 'meta_description', 'keywords', 'alias', 'canonical',
			'contact', 'delay_id', 'waitlist', 'display_quantity_field',
			'status', 'hit', 'created', 'modified', 'last_seen_date', 'sales', 'layout', 'average_score', 'total_vote',
			'warehouse_id',
		);
		foreach($removeFields as $rf) {
			$rf = 'product_'.$rf;
			unset($product->$rf);
		}

		unset($product->categories);
		unset($product->related);
		unset($product->options);
		unset($product->bundle);

		if(hikashop_acl('product/variant/description')) {
			$product->product_description = trim(hikaInput::get()->getRaw('product_variant_description', ''));

			$product->product_description_raw = hikaInput::get()->getRaw('product_variant_description_raw', null);
			$default_description_type = $config->get('default_description_type', '');
			if($new && !empty($default_description_type))
				$product->product_description_type = $default_description_type;
			if($new && isset($productParent->product_description_type))
				$product->product_description_type = $productParent->product_description_type;
			if(!empty($oldProduct->product_description_type))
				$product->product_description_type = $oldProduct->product_description_type;

			if($product->product_description_raw !== null && !empty($product->product_description_type) && $product->product_description_type != 'html') {
				$contentparserType = hikashop_get('type.contentparser');
				$description_types = $contentparserType->load();
				if(isset($description_types[ $product->product_description_type ])) {

					$product->product_description = $product->product_description_raw;

					JPluginHelper::importPlugin('hikashop');
					$app = JFactory::getApplication();
					$app->triggerEvent('onHkContentParse', array( &$product->product_description, $product->product_description_type ));
				} else {
					$product->product_description_type = null;
					unset($product->product_description);
				}
			}

			if((int)$config->get('safe_product_description', 0)) {
				$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
				$product->product_description = $safeHtmlFilter->clean($product->product_description, 'string');
			}
		}

		if(hikashop_acl('product/variant/price')) {
			$acls = array(
				'value' => hikashop_acl('product/variant/price/value'),
				'tax' => hikashop_acl('product/variant/price/tax'),
				'currency' => hikashop_acl('product/variant/price/currency'),
				'quantity' => hikashop_acl('product/variant/price/quantity'),
				'acl' => hikashop_level(2) && hikashop_acl('product/variant/price/acl')
			);

			if(!empty($oldProduct)) {
				$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id = ' . (int)$oldProduct->product_id;
				$this->db->setQuery($query);
				$oldProduct->prices = $this->db->loadObjectList();
			}

			$priceData = hikaInput::get()->get('variantprice', array(), 'array');
			$product->prices = array();
			foreach($priceData as $k => $value) {
				if((int)$k == 0 && $k !== 0 && $k !== '0')
					continue;

				$price_id = (int)@$value['price_id'];
				if(!empty($oldProduct) && !empty($price_id) && !empty($oldProduct->prices)) {
					foreach($oldProduct->prices as $p) {
						if($p->price_id == $price_id) {
							$product->prices[$k] = $p;
							break;
						}
					}
				}

				if(empty($product->prices[$k]))
					$product->prices[$k] = new stdClass();

				if(($acls['value'] || $acls['tax']) && isset($value['price_value']))
					$product->prices[$k]->price_value = hikashop_toFloat($value['price_value']);
				if($acls['acl'] && isset($value['price_access']))
					$product->prices[$k]->price_access = preg_replace('#[^a-z0-9,]#i', '', $value['price_access']);
				if(!empty($value['price_start_date']))
					$product->prices[$k]->price_start_date = hikashop_getTime($value['price_start_date']);
				else
					$product->prices[$k]->price_start_date = '';
				if(!empty($value['price_end_date']))
					$product->prices[$k]->price_end_date = hikashop_getTime($value['price_end_date']);
				else
					$product->prices[$k]->price_end_date = '';
				if($acls['acl'] && isset($value['price_users']))
					$product->prices[$k]->price_users = preg_replace('#[^0-9,]#i', '', $value['price_users']);
				if($acls['currency'] && isset($value['price_currency_id']))
					$product->prices[$k]->price_currency_id = (int)$value['price_currency_id'];
				if(empty($product->prices[$k]->price_currency_id))
					$product->prices[$k]->price_currency_id = $config->get('main_currency',1);
				if($acls['quantity'] && isset($value['price_min_quantity'])) {
					$product->prices[$k]->price_min_quantity = (int)$value['price_min_quantity'];
					if($product->prices[$k]->price_min_quantity == 1)
						$product->prices[$k]->price_min_quantity = 0;
				}
				if(empty($product->prices[$k]->price_min_quantity))
					$product->prices[$k]->price_min_quantity = 0;
			}
		} else {
			unset($product->prices);
		}


		if(hikashop_acl('product/variant/images')) {
			$product->images = @$formVariant['product_images'];
			hikashop_toInteger($product->images);

			$product->imagesorder = array();
			foreach($product->images as $k => $v) {
				$product->imagesorder[$v] = $k;
			}
		} else {
			unset($product->imagesorder);
		}
		unset($product->product_images);

		if(hikashop_acl('product/variant/files')) {
			$product->files = @$formVariant['product_files'];
			hikashop_toInteger($product->files);

			$product->filesorder = array();
			foreach($product->files as $k => $v) {
				$product->filesorder[$v] = $k;
			}
		} else {
			unset($product->files);
		}
		unset($product->product_files);

		if(hikashop_acl('product/variant/saledates')) {
			if(!empty($product->product_sale_start))
				$product->product_sale_start = hikashop_getTime($product->product_sale_start);
			else
				$product->product_sale_start = 0;

			if(!empty($product->product_sale_end))
				$product->product_sale_end = hikashop_getTime($product->product_sale_end);
			else
				$product->product_sale_end = 0;
		} else {
			unset($product->product_sale_start);
			unset($product->product_sale_end);
		}

		if(!empty($product->product_code))
			$product->product_code = trim($product->product_code);
		if(!empty($product->product_id) && isset($product->product_code)){
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code  = '.$this->database->Quote($product->product_code).' AND product_id!='.(int)$product->product_id.' LIMIT 1';
			$this->database->setQuery($query);
			if($this->database->loadResult()){
				$app->enqueueMessage(JText::_( 'DUPLICATE_PRODUCT' ), 'error');
				hikaInput::get()->set( 'fail', $product  );
				return false;
			}
		}

		$status = $this->save($product, false, true);
		if($status) {
			if(hikashop_acl('product/variant/price'))
				$this->updatePrices($product, $status);
			if(hikashop_acl('product/variant/files'))
				$this->updateFiles($product, $status, 'files', $product->filesorder);
			if(hikashop_acl('product/variant/images'))
				$this->updateFiles($product, $status, 'images', $product->imagesorder);
			if(hikashop_acl('product/variant/characteristics'))
				$this->updateCharacteristics($product, $status);

			if($new)
				$app->triggerEvent( 'onAfterProductCreate', array( & $product ) );
			else
				$app->triggerEvent( 'onAfterProductUpdate', array( & $product ) );
		} else {
			hikaInput::get()->set('fail', $product);
			if(empty($product->product_id) && empty($product->product_code) && empty($product->product_name)) {
				$app->enqueueMessage(JText::_('SPECIFY_NAME_AND_CODE'), 'error');
			} else {
				$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code  = '.$this->db->Quote($product->product_code) . ' AND NOT (product_id = ' . (int)(@$product->product_id) . ')';
				$this->db->setQuery($query, 0, 1);
				if($this->db->loadResult()) {
					$app->enqueueMessage(JText::_('DUPLICATE_PRODUCT'), 'error');
				} else {
					$app->enqueueMessage(JText::_('PRODUCT_SAVE_UNKNOWN_ERROR'), 'error');
				}
			}
		}

		return $product_id;
	}

	public function checkProductCharacteristics($characteristics, $vendor_id = 0, $complete_return = false) {
		$query = 'SELECT c.characteristic_id, c.characteristic_value, c.characteristic_parent_id '.
			' FROM ' . hikashop_table('characteristic') . ' AS c '.
			' WHERE c.characteristic_id IN (' . implode(',', $characteristics) . ')';
		if(!empty($vendor_id))
			$query .= ' AND c.characteristic_vendor_id IN (0, '.(int)$vendor_id.')';
		$this->db->setQuery($query);
		$characteristics = $this->db->loadObjectList('characteristic_id');

		foreach($characteristics as $k => $c) {
			$c->characteristic_parent_id = (int)$c->characteristic_parent_id;
			if($c->characteristic_parent_id == 0)
				continue;

			if(isset($characteristics[$c->characteristic_parent_id]) && empty($characteristics[$c->characteristic_parent_id]->checked))
				$characteristics[$c->characteristic_parent_id]->checked = $k;
			else
				unset($characteristics[$k]);
		}

		foreach($characteristics as $k => $c) {
			if($c->characteristic_parent_id > 0)
				continue;
			if(empty($c->checked))
				unset($characteristics[$k]);
		}

		if(empty($characteristics))
			return false;

		if(!$complete_return)
			return array_keys($characteristics);

		$ret = array();
		$i = 1;
		foreach($characteristics as $c) {
			if($c->characteristic_parent_id > 0)
				continue;

			$e = new stdClass();
			$e->characteristic_id = (int)$c->characteristic_id;
			$e->ordering = $i++;
			$e->default_id = $c->checked;
			$e->values = array();

			$ret[ $e->characteristic_id ] = $e;
		}

		return $ret;
	}

	function getCategories($product_id){
		if(empty($product_id) || (is_array($product_id) && !count($product_id))) return false;
		static $categoriesArray = array();
		if(is_array($product_id)){
			$products = array();
			foreach($product_id as $p){
				if(is_numeric($p)){
					$products[] = (int)$p;
				}elseif(!empty($p->product_id)){
					$products[] = (int)$p->product_id;
				}
			}
		}else{
			$products = array((int)$product_id);
		}
		$products = implode(',',$products);
		if(!isset($categoriesArray[$products])){
			$query='SELECT category_id FROM '.hikashop_table('product_category').' WHERE product_id IN ('.$products.') ORDER BY ordering ASC';
			$this->database->setQuery($query);
			$categoriesArray[$products]=$this->database->loadColumn();
		}
		return $categoriesArray[$products];
	}

	function getRawProducts($ids, $load_parents = false) {
		if(empty($ids) || !is_array($ids))
			return false;
		hikashop_toInteger($ids);
		$this->database->setQuery('SELECT * FROM #__hikashop_product WHERE product_id IN ('.implode(',',$ids).');');
		$products = $this->database->loadObjectList('product_id');
		if($load_parents && count($products)) {
			$parent_ids = array();
			foreach($products as $product) {
				if(!empty($product->product_parent_id))
					$parent_ids[] = (int)$product->product_parent_id;
			}
			if(count($parent_ids)) {
				$this->database->setQuery('SELECT * FROM #__hikashop_product WHERE product_id IN ('.implode(',',$parent_ids).');');
				$parents = $this->database->loadObjectList('product_id');
				if(!empty($parents)) {
					foreach($parents as $id => $parent) {
						$products[$id] = $parent;
					}
				}
			}
		}
		return $products;
	}

	function getProducts($ids, $mode = 'id') {
		if(is_numeric($ids))
			$ids = array($ids);

		$where = '';
		if(empty($ids)) {
			$this->database->setQuery('SELECT product_id FROM '.hikashop_table('product').' ORDER BY product_id ASC');
			$ids = $this->database->loadColumn();
		} else {
			hikashop_toInteger($ids,0);
		}

		if(count($ids) < 1)
			return false;

		$query = 'SELECT * FROM '.hikashop_table('product_related').' AS a WHERE a.product_id IN ('.implode(',',$ids).') ORDER BY a.product_related_ordering';
		$this->database->setQuery($query);
		$related = $this->database->loadObjectList();
		foreach($related as $rel) {
			if($mode!='import' && $rel->product_related_type == 'options' && !in_array($rel->product_related_id, $ids))
				$ids[] = $rel->product_related_id;
		}

		$where = ' WHERE product_id IN ('.implode(',',$ids).') OR product_parent_id IN ('.implode(',',$ids).')';
		$query = 'SELECT * FROM '.hikashop_table('product').$where.' ORDER BY product_parent_id ASC, product_id ASC';
		$this->database->setQuery($query);
		$all_products = $this->database->loadObjectList('product_id');
		if(empty($all_products)) return false;

		$all_ids = array_keys($all_products);

		$products = array();
		$variants = array();

		$ids = array();
		foreach($all_products as $key => $product){
			$all_products[$key]->prices=array();
			$all_products[$key]->files=array();
			$all_products[$key]->images=array();
			$all_products[$key]->variant_links=array();
			$all_products[$key]->translations=array();
			if($product->product_type=='main'){
				$all_products[$key]->categories=array();
				$all_products[$key]->categories_ordering=array();
				$all_products[$key]->related=array();
				$all_products[$key]->options=array();
				$all_products[$key]->bundle=array();
				$all_products[$key]->variants=array();
				$products[$product->product_id]=&$all_products[$key];
				$ids[] = $product->product_id;
			}else{
				foreach($all_products as $key2 => $main){
					if($main->product_type != 'main') continue;
					if($main->product_id == $product->product_parent_id){
						$all_products[$key2]->variants[$product->product_id]=&$all_products[$key];
					}
				}
				$variants[$product->product_id]=&$all_products[$key];
			}
		}

		foreach($related as $rel){
			$type = $rel->product_related_type;
			$all_products[$rel->product_id]->{$type}[] = $rel->product_related_id;
			if($type == 'bundle')
				$all_products[$rel->product_id]->bundle_quantity[] = $rel->product_related_quantity;
		}

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper->isMulti(true) && $translationHelper->falang){
			$trans_table = 'falang_content';
			$query = 'SELECT * FROM '.hikashop_table($trans_table,false).' WHERE reference_id IN ('.implode(',',$all_ids).')  AND reference_table=\'hikashop_product\' ORDER BY reference_id ASC';
			$this->database->setQuery($query);
			$translations = $this->database->loadObjectList();
			if(!empty($translations)){
				foreach($translations as $translation){
					$all_products[$translation->reference_id]->translations[]=$translation;
				}
			}
		}
		if(!empty($ids)){
			$query = 'SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).') ORDER BY ordering ASC';
			$this->database->setQuery($query);
			$categories = $this->database->loadObjectList();
			if(!empty($categories)){
				foreach($categories as $category){
					$all_products[$category->product_id]->categories[]=$category->category_id;
					$all_products[$category->product_id]->categories_ordering[]=$category->ordering;
				}
			}
		}

		$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$all_ids).')';
		$this->database->setQuery($query);
		$prices = $this->database->loadObjectList();
		if(!empty($prices)){
			foreach($prices as $price){
				$all_products[$price->price_product_id]->prices[]=$price;
			}
		}
		$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$all_ids).') AND file_type IN (\'product\',\'file\') ORDER BY file_ordering ASC, file_id ASC';
		$this->database->setQuery($query);
		$files = $this->database->loadObjectList();
		if(!empty($files)){
			foreach($files as $file){
				if($file->file_type=='file'){
					$type='files';
				}else{
					$type='images';
				}
				$all_products[$file->file_ref_id]->{$type}[]=$file;
			}
		}
		$query = 'SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$all_ids).') ORDER BY ordering ASC';
		$this->database->setQuery($query);
		$variants = $this->database->loadObjectList();
		if(!empty($variants)){
			foreach($variants as $variant){
				$all_products[$variant->variant_product_id]->variant_links[]=$variant->variant_characteristic_id;
			}
		}
		$this->products =& $products;
		$this->all_products =& $all_products;
		$this->variants =& $variants;
		return true;
	}

	public function getProduct($product_id) {
		if((int)$product_id <= 0)
			return false;

		$product = $this->get($product_id);
		if(empty($product))
			return $product;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$sql_product = '= '.(int)$product_id;
		$product->images = array();

		if($product->product_type == 'variant' && (int)$product->product_parent_id > 0) {
			$product->parent = $this->get($product->product_parent_id);
			if(!empty($product->parent)) {
				$product->parent->images = array();
				$sql_product = 'IN ('.(int)$product_id.','.(int)$product->product_parent_id.')';
			}
		}

		$query = 'SELECT * FROM '.hikashop_table('file') .
			' WHERE file_ref_id '.$sql_product.' AND file_type = \'product\'' . // file_type IN (\'product\',\'file\')
			' ORDER BY file_ordering ASC, file_id ASC';
		$this->db->setQuery($query);
		$files = $this->db->loadObjectList();
		foreach($files as $file) {
			if(!empty($product->parent) && (int)$file->file_ref_id == (int)$product->product_parent_id) {
				$product->parent->images[] = $file;
				continue;
			}
			$product->images[] = $file;
		}
		unset($files);

		return $product;
	}

	public function addCharacteristic($product_id, $characteristic_id, $characteristic_value_id, $vendor_id = 0) {
		if((int)$product_id <= 0 || (int)$characteristic_id <= 0 || (int)$characteristic_value_id <= 0)
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$product_characteristics = $this->getProductCharacteristics($product_id);

		if(in_array((int)$characteristic_id, $product_characteristics))
			return false;

		$new_characteristics = array_merge($product_characteristics, array((int)$characteristic_id));

		$query = 'SELECT c.characteristic_id, c.characteristic_value, c.characteristic_parent_id FROM ' . hikashop_table('characteristic') . ' AS c '.
			' WHERE (c.characteristic_parent_id = '.$characteristic_id;
		if(!empty($vendor_id))
			$query .= ' AND c.characteristic_vendor_id IN (0, '.(int)$vendor_id.')';
		$query .= ')';
		if(!empty($product_characteristics))
			$query .= ' OR (c.characteristic_parent_id IN (' . implode(',', $product_characteristics) . '))';
		$this->db->setQuery($query);
		$characteristic_values = $this->db->loadObjectList('characteristic_id');

		if($characteristic_value_id != $characteristic_id && (!isset($characteristic_values[ (int)$characteristic_value_id ]) || (int)$characteristic_values[ (int)$characteristic_value_id ]->characteristic_parent_id != (int)$characteristic_id))
			return false;

		$query = 'SELECT c.characteristic_id, c.characteristic_parent_id FROM ' . hikashop_table('characteristic') . ' AS c '.
			' INNER JOIN ' . hikashop_table('variant') . ' AS v ON v.variant_characteristic_id = c.characteristic_id '.
			' WHERE c.characteristic_parent_id > 0 AND v.variant_product_id = ' . (int)$product_id;
		$this->db->setQuery($query);
		$default_values = $this->db->loadObjectList('characteristic_parent_id');

		if(empty($default_values))
			$default_values = array();

		$e = new stdClass();
		$e->characteristic_id = (int)$characteristic_value_id;
		$e->characteristic_parent_id = (int)$characteristic_id;
		$default_values[ (int)$characteristic_id ] = $e;

		$query = 'SELECT product_code FROM ' . hikashop_table('product') . ' WHERE product_id = ' . (int)$product_id;
		$this->db->setQuery($query);
		$product = $this->db->loadObject();

		$elem = new stdClass();
		$elem->product_type = 'main';
		$elem->product_id = $product_id;
		$elem->product_code = $product->product_code;
		$elem->oldCharacteristics = $product_characteristics;
		$elem->characteristics = array();
		$i = 1;
		foreach($new_characteristics as $c) {
			$e = new stdClass();
			$e->characteristic_id = (int)$c;
			$e->ordering = $i++;
			$e->default_id = $default_values[ (int)$c ]->characteristic_id;
			$e->values = array();

			$elem->characteristics[ (int)$c ] = $e;
		}

		foreach($characteristic_values as $k => $v) {
			if(!isset($elem->characteristics[ (int)$v->characteristic_parent_id ]))
				continue;
			$elem->characteristics[ (int)$v->characteristic_parent_id ]->values[ (int)$k ] = $v->characteristic_value;
		}

		$ret = $this->updateCharacteristics($elem, (int)$product_id, 0);

		if(!$ret)
			return false;
		return ($i - 1);
	}

	public function populateVariant($product_id, $characteristic_data) {
		if((int)$product_id <= 0)
			return false;

		if(empty($characteristic_data['variant_add']))
			return false;

		$product_characteristics = $this->getProductCharacteristics($product_id);

		foreach($characteristic_data['variant_add'] as $k => $v) {
			if(!in_array($k, $product_characteristics))
				return false;
		}

		if(count($characteristic_data['variant_add']) != count($product_characteristics))
			return false;

		$query = 'SELECT product_code FROM ' . hikashop_table('product') . ' WHERE product_id = ' . (int)$product_id;
		$this->db->setQuery($query);
		$product = $this->db->loadObject();

		$elem = new stdClass();
		$elem->product_type = 'main';
		$elem->product_id = $product_id;
		$elem->product_code = $product->product_code;
		$elem->oldCharacteristics = $product_characteristics;
		$elem->characteristics = array();
		$i = 1;
		foreach($characteristic_data['variant_add'] as $k => $v) {
			$e = new stdClass();
			$e->characteristic_id = (int)$k;
			$e->default_id = null;
			$e->ordering = null;
			$e->values = array_combine($v, $v);

			$elem->characteristics[ (int)$k ] = $e;
		}

		return $this->updateCharacteristics($elem, (int)$product_id, 2);
	}

	public function duplicateVariant($product_id, $cid, $data) {
		if((int)$product_id <= 0)
			return false;

		if(empty($cid) || empty($data['variant_duplicate']) || empty($data['variant_duplicate']['characteristic']) || empty($data['variant_duplicate']['variants']))
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$product_characteristics = $this->getProductCharacteristics($product_id);

		$characteristic_id = (int)$data['variant_duplicate']['characteristic'];

		if(!in_array((int)$characteristic_id, $product_characteristics))
			return false;

		if(!in_array($characteristic_id, $product_characteristics))
			return false;

		$query = 'SELECT product_code FROM ' . hikashop_table('product') . ' WHERE product_id = ' . (int)$product_id;
		$this->db->setQuery($query);
		$product = $this->db->loadObject();

		$elem = new stdClass();
		$elem->product_type = 'main';
		$elem->product_id = $product_id;
		$elem->product_code = $product->product_code;
		$elem->oldCharacteristics = $product_characteristics;
		$elem->duplicateVariants = $cid;
		$elem->characteristics = array();
		$i = 1;

		$e = new stdClass();
		$e->characteristic_id = (int)$characteristic_id;
		$e->default_id = null;
		$e->ordering = null;
		hikashop_toInteger($data['variant_duplicate']['variants']);
		$e->values = array_combine($data['variant_duplicate']['variants'], $data['variant_duplicate']['variants']);

		$elem->characteristics[ $characteristic_id ] = $e;

		return $this->updateCharacteristics($elem, (int)$product_id, 2);
	}

	public function removeCharacteristic($product_id, $characteristic_id) {
		if((int)$product_id <= 0 || (int)$characteristic_id <= 0)
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$product_characteristics = $this->getProductCharacteristics($product_id);

		if(!in_array((int)$characteristic_id, $product_characteristics))
			return false;

		$query = 'SELECT c.characteristic_id, c.characteristic_value, c.characteristic_parent_id FROM ' . hikashop_table('characteristic') . ' AS c '.
			' WHERE c.characteristic_parent_id IN (' . implode(',', $product_characteristics) . ')';
		$this->db->setQuery($query);
		$characteristic_values = $this->db->loadObjectList('characteristic_id');

		$query = 'SELECT c.characteristic_id, c.characteristic_parent_id FROM ' . hikashop_table('characteristic') . ' AS c '.
			' INNER JOIN ' . hikashop_table('variant') . ' AS v ON v.variant_characteristic_id = c.characteristic_id '.
			' WHERE c.characteristic_parent_id > 0 AND v.variant_product_id = ' . (int)$product_id;
		$this->db->setQuery($query);
		$default_values = $this->db->loadObjectList('characteristic_parent_id');

		if(empty($default_values))
			$default_values = array();

		$query = 'SELECT product_code FROM ' . hikashop_table('product') . ' WHERE product_id = ' . (int)$product_id;
		$this->db->setQuery($query);
		$product = $this->db->loadObject();

		$elem = new stdClass();
		$elem->product_type = 'main';
		$elem->product_id = $product_id;
		$elem->product_code = $product->product_code;
		$elem->oldCharacteristics = $product_characteristics;
		$elem->characteristics = array();
		$i = 1;
		foreach($product_characteristics as $c) {
			if($c == (int)$characteristic_id)
				continue;

			$e = new stdClass();
			$e->characteristic_id = (int)$c;
			$e->ordering = $i++;
			$e->default_id = $default_values[ (int)$c ]->characteristic_id;
			$e->values = array();

			$elem->characteristics[ (int)$c ] = $e;
		}
		foreach($characteristic_values as $k => $v) {
			if(!isset($elem->characteristics[ (int)$v->characteristic_parent_id ]))
				continue;
			$elem->characteristics[ (int)$v->characteristic_parent_id ]->values[ (int)$k ] = $v->characteristic_value;
		}

		$ret = $this->updateCharacteristics($elem, (int)$product_id, 1);

		if(!$ret)
			return false;
		return ($i - 1);
	}

	public function deleteVariants($product_id, $variant_ids) {
		if((int)$product_id <= 0)
			return false;
		if(empty($variant_ids))
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		hikashop_toInteger($variant_ids);
		$query = 'SELECT p.product_id FROM ' . hikashop_table('product') . ' AS p '.
				' WHERE p.product_type = ' . $this->db->Quote('variant') . ' AND p.product_parent_id = ' . (int)$product_id.
				' AND p.product_id IN (' . implode(',', $variant_ids) . ')';
		$this->db->setQuery($query);
		$ids = $this->db->loadColumn();

		if(empty($ids))
			return false;

		hikashop_toInteger($ids);
		return $this->delete($ids);
	}

	private function getProductCharacteristics($product_id) {
		if((int)$product_id <= 0)
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$query = 'SELECT c.characteristic_id FROM ' . hikashop_table('variant') . ' AS v '.
			' INNER JOIN ' . hikashop_table('characteristic') . ' AS c ON v.variant_characteristic_id = c.characteristic_id '.
			' WHERE c.characteristic_parent_id = 0 AND v.variant_product_id = ' . (int)$product_id.' '.
			' ORDER BY v.ordering ASC';
		$this->db->setQuery($query);
		$ret = $this->db->loadColumn();

		if(empty($ret))
			$ret = array();
		else
			hikashop_toInteger($ret);
		return $ret;
	}

	public function setDefaultVariant($product_id, $variant_id) {
		if(!hikashop_acl('product/variant') || (int)$variant_id <= 0)
			return false;

		$app = JFactory::getApplication();
		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$variant = $this->get((int)$variant_id);
		if((int)$variant->product_parent_id != $product_id)
			return false;

		$query = 'SELECT variant.*, characteristic.* FROM '.hikashop_table('variant').' as variant '.
				' LEFT JOIN '.hikashop_table('characteristic').' AS characteristic ON variant.variant_characteristic_id = characteristic.characteristic_id '.
				' WHERE variant.variant_product_id = '.(int)$product_id;
		$this->db->setQuery($query);
		$original_data = $this->db->loadObjectList('characteristic_id');

		if(empty($original_data))
			return true;

		$query = 'SELECT variant.*, characteristic.* FROM '.hikashop_table('variant').' as variant '.
				' LEFT JOIN '.hikashop_table('characteristic').' AS characteristic ON variant.variant_characteristic_id = characteristic.characteristic_id '.
				' WHERE variant.variant_product_id = '.(int)$variant_id;
		$this->db->setQuery($query);
		$variant_data = $this->db->loadObjectList();

		$values = array();
		foreach($variant_data as $v) {
			$values[ (int)$v->characteristic_parent_id ] = (int)$v->characteristic_parent_id;
			$values[ (int)$v->characteristic_id ] = (int)$v->characteristic_id;
		}
		unset($values[0]);
		unset($variant_data);

		$query = 'DELETE FROM '.hikashop_table('variant').' WHERE variant_product_id = '.(int)$product_id;
		$this->db->setQuery($query);
		$this->db->execute();

		$query = 'INSERT INTO '.hikashop_table('variant').' (`variant_characteristic_id`,`variant_product_id`,`ordering`) VALUES ';
		foreach($values as $k => $value) {
			$ordering = '0';
			if(isset($original_data[$k]))
				$ordering = $original_data[$k]->ordering;
			$values[$k] = '('.$k.','.$product_id.','.$ordering.')';
		}
		unset($original_data);

		$this->db->setQuery($query . implode(',', $values) );
		$this->db->execute();

		unset($values);
		unset($query);

		return true;
	}

	public function publishVariant($variant_id) {
		if(!hikashop_acl('product/variant'))
			return false;

		if(empty($this->db))
			$this->db = JFactory::getDBO();

		$variant = $this->get((int)$variant_id);
		if(!isset($variant->product_published))
			return false;

		if($variant->product_published){
			$query = 'UPDATE '.hikashop_table('product').' SET product_published = 0 WHERE product_id = '.(int)$variant_id;
		}else{
			$query = 'UPDATE '.hikashop_table('product').' SET product_published = 1 WHERE product_id = '.(int)$variant_id;
		}
		$this->db->setQuery($query);
		$success = $this->db->execute();
		return $success;
	}

	function toFloatArray(&$array, $default = null) {
		if(is_array($array)) {
			foreach($array as $i => $v) {
				$array[$i] = hikashop_toFloat($v);
			}
		} else if ($default === null) {
			$array = array();
		} elseif (is_array($default)) {
			$this->toFloatArray($default, null);
			$array = $default;
		} else {
			$array = array( (float) $default );
		}
	}

	function addAlias(&$element, $language_code = null){
		if(empty($element->product_alias)) {
			$element->alias = strip_tags(preg_replace('#<span class="hikashop_product_variant_subname">.*</span>#isU','',$element->product_name));
		} else {
			$element->alias = $element->product_alias;
		}

		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->translateAlias($element, 'product', $language_code);

		$config = JFactory::getConfig();
		if(!$config->get('unicodeslugs')){
			$lang = JFactory::getLanguage();
			$element->alias = str_replace(array(',', "'", '"'), array('-', '-', '-'), $lang->transliterate($element->alias));
		}
		$app = JFactory::getApplication();
		if(method_exists($app,'stringURLSafe')){
			$element->alias = $app->stringURLSafe($element->alias);
		}elseif(method_exists('JFilterOutput','stringURLUnicodeSlug')){
			$element->alias = JFilterOutput::stringURLUnicodeSlug($element->alias);
		}else{
			$element->alias = JFilterOutput::stringURLSafe($element->alias);
		}
		if(is_numeric(substr($element->alias,0,1)) && hikashop_isClient('site')) {
			$config = hikashop_config();
			if($config->get('sef_remove_id',0)) {
				$element->alias = 'p'.$element->alias;
				$save = new stdClass();
				$save->product_id = $element->product_id;
				$save->product_alias = $element->alias;
				parent::save($save);
			}
		}
	}

	public function loadProductVariants(&$element, $options = array()) {

		$user_id = 0;
		if(!empty($options['user_id'])){
			$user_id = (int)$options['user_id'];
		}
		$selected_variant_id = 0;
		if(!empty($options['selected_variant_id'])){
			$selected_variant_id = (int)$options['selected_variant_id'];
		}

		$product_id = (int)$element->product_id;
		$ids = array($product_id);

		$filters = array(
			'product_parent_id IN ('.implode(',',$ids).')',
			'product_published = 1'
		);
		hikashop_addACLFilters($filters,'product_access');
		$database = Jfactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE ('.implode(') AND (', $filters).')';
		$database->setQuery($query);
		$variants = $database->loadObjectList();
		if(!empty($variants)) {
			foreach($variants as $key => $variant) {
				$ids[] = (int)$variant->product_id;

				if(!empty($variant->product_name))
					$variants[$key]->product_name = hikashop_translate($variant->product_name);
				if(!empty($variant->product_description))
					$variants[$key]->product_description = hikashop_translate($variant->product_description);

				if($variant->product_parent_id == $product_id)
					$element->variants[$variant->product_id] = $variant;
			}
		}

		$config = hikashop_config();
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
			}

			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onAfterProductCharacteristicsLoad', array( &$element, &$mainCharacteristics, &$characteristics ));

			if(!empty($element->variants)) {
				$this->addCharacteristics($element, $mainCharacteristics, $characteristics, array('selected_variant_id'=>$selected_variant_id));
				$this->orderVariants($element);
			}
			unset($element->characteristics[0]);
		}

		$query = 'SELECT * FROM '.hikashop_table('file').
			' WHERE file_ref_id IN ('.implode(',', $ids).') AND file_type IN (\'product\',\'file\') '.
			' ORDER BY file_ordering ASC, file_id ASC';
		$database->setQuery($query);
		$product_files = $database->loadObjectList();
		if(!empty($product_files)) {
			$this->generateDownloadLinks($product_files, $ids);
			$this->addFiles($element,$product_files);
		}

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->getProductPrices($element, $ids, array('user_id' => $user_id));

		$this->checkVariants($element);

		$this->setDefault($element);

		if(!empty($element->variants)) {
			foreach($element->variants as $k => $variant) {
				$variant->main =& $element->main;
			}
		}
	}


	public function generateDownloadLinks(&$files, &$ids) {
		global $Itemid;
		$url_itemid = (!empty($Itemid) ? '&Itemid='.$Itemid : '');
		$orders = null;
		$main_download_time_limit = 0;
		$main_download_number_limit = 0;
		$config = hikashop_config();

		$display_paid_downloads = $config->get('display_downloads_on_product_page', 0);
		$db = JFactory::getDBO();

		if($display_paid_downloads) {
			$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
			$order_status_for_download = explode(',', $order_status_for_download);

			if(count($order_status_for_download)) {

				$user = hikashop_loadUser(true);
				if(!empty($user->user_cms_id)) {

					$main_download_time_limit = $config->get('download_time_limit', 0);

					$main_download_number_limit = $config->get('download_number_limit', 0);

					$statuses = array();
					foreach($order_status_for_download as $status) {
						$statuses[] = $db->Quote($status);
					}

					$query = 'SELECT * FROM #__hikashop_order_product as op '.
					'LEFT JOIN #__hikashop_order AS o ON op.order_id = o.order_id '.
					'LEFT JOIN #__hikashop_product AS p ON op.product_id = p.product_id '.
					'WHERE o.order_user_id = ' . $user->user_id . ' '.
					'AND op.product_id IN (' . implode(',', $ids) . ') '.
					'AND o.order_status IN (' . implode(',', $statuses) . ')';
					$db->setQuery($query);
					$orders = $db->loadObjectList('order_id');
				}
			}
		}
		foreach($files as $k => $file) {
			if($file->file_type != 'file')
					continue;

			if(!empty($file->file_free_download)) {
				$files[$k]->download_link = hikashop_completeLink('product&task=download&file_id=' . $file->file_id.$url_itemid);
				continue;
			}


			if(!$display_paid_downloads)
				continue;

			if(empty($orders))
				continue;
			$files[$k]->orders = array();
			foreach($orders as $order) {
				if($file->file_ref_id != $order->product_id && $file->file_ref_id != $order->product_parent_id)
					continue;
				$files[$k]->orders[] = $order;
			}
			if(!count($files[$k]->orders))
				continue;

			if(!empty($file->file_limit) && (int)$file->file_limit != 0) {
				$download_number_limit = $file->file_limit;
				if($download_number_limit < 0)
					$download_number_limit = 0;
			} else {
				$download_number_limit = $main_download_number_limit;
			}

			if(!empty($file->file_time_limit))
				$download_time_limit = $file->file_time_limit;
			else
				$download_time_limit = $main_download_time_limit;

			$downloads = array();
			if(!empty($download_number_limit) && count($orders)) {
				$db->setQuery('SELECT * FROM #__hikashop_download WHERE order_id IN ('.implode(',', array_keys($orders)).') AND file_id = '.(int)$file->file_id);
				$downloads = $db->loadObjectList('order_id');
			}

			foreach($files[$k]->orders as $order) {
				if(!empty($download_time_limit) && ($download_time_limit + (!empty($order->order_invoice_created) ? $order->order_invoice_created : $order->order_created)) < time()) {
					continue;
				}
				if(!empty($download_number_limit) && $download_number_limit <= (int)@$downloads[$order->order_id]->download_number) {
					continue;
				}
				$file_pos = '';
				if(!empty($file->file_pos)) {
					$file_pos = '&file_pos='.$file->file_pos;
				}
				$files[$k]->download_link = hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$order->order_id.$file_pos.$url_itemid);
				break;
			}
		}
	}


	public function loadProductOptions(&$element, $options = array()) {
		if(!hikashop_level(1))
			return false;

		$user_id = 0;
		if(!empty($options['user_id'])){
			$user_id = (int)$options['user_id'];
		}

		$product_id = $element->product_id;

		$database = JFactory::getDBO();
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
		$options = $database->loadObjectList('product_id');

		if(!empty($options) && count($options)) {

			foreach($options as $k => $optionElement) {
				if(!empty($optionElement->product_name))
					$options[$k]->product_name = hikashop_translate($optionElement->product_name);
				if(!empty($optionElement->product_description))
					$options[$k]->product_description = hikashop_translate($optionElement->product_description);
			}

			$ids = array_keys($options);
			$filters = array(
				'product_parent_id IN ('.implode(',',$ids).')',
				'product_published = 1'
			);
			hikashop_addACLFilters($filters,'product_access');
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE ('.implode(') AND (', $filters).')';
			$database->setQuery($query);
			$variants = $database->loadObjectList();
			if(!empty($variants)) {
				foreach($variants as $key => $variant) {
					$ids[] = (int)$variant->product_id;

					if(!empty($variant->product_name))
						$variants[$key]->product_name = hikashop_translate($variant->product_name);
					if(!empty($variant->product_description))
						$variants[$key]->product_description = hikashop_translate($variant->product_description);

					if(!empty($options)) {
						foreach($options as $k => $optionElement) {
							if($variant->product_parent_id == $optionElement->product_id) {
								$options[$k]->variants[$variant->product_id] = $variant;
								break;
							}
						}
					}
				}
			}
			$config = hikashop_config();
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
					if(!empty($options)) {
						foreach($options as $k => $optionElement) {
							if($optionElement->product_id == $characteristic->variant_product_id) {
								$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id] = $characteristic;
							}
						}
					}
				}

				JPluginHelper::importPlugin('hikashop');
				$app = JFactory::getApplication();
				$app->triggerEvent('onAfterProductCharacteristicsLoad', array( &$element, &$mainCharacteristics, &$characteristics ));

				foreach($options as $k => $optionElement) {
					if(empty($optionElement->variants))
						continue;

					$this->addCharacteristics($options[$k], $mainCharacteristics, $characteristics);
					if(count(@$mainCharacteristics[$optionElement->product_id][0]) > 0)
						$this->orderVariants($options[$k]);
				}
			}
			$element->options =& $options;

			$currencyClass = hikashop_get('class.currency');
			$currencyClass->getProductPrices($element, $ids, array('user_id' => $user_id));

			foreach($options as $k => $optionElement) {
				$this->checkVariants($options[$k]);
				$this->setDefault($options[$k]);
			}
		}

		return $options;
	}

	public function getAllValuesMatches($characteristics, $variants, $mainProduct = null) {
		$allMatches = array();
		$length = count($characteristics);
		if($length == 1)
			return false;

		$config = hikashop_config();
		$waitlist = $config->get('product_waitlist', 0);
		if($waitlist == 1 && !empty($mainProduct))
			$waitlist = $mainProduct->product_waitlist;
		$wishlist = $config->get('enable_wishlist', 1);
		foreach($variants as $k => $variant) {
			$isNeeded = $variant->product_published && $variant->product_id != $variant->product_parent_id && ($variant->product_quantity != 0 || $waitlist || $wishlist);
			if(!$isNeeded) {
				continue;
			}
			$chars = array();
			foreach($variant->characteristics as $variantCharacteristic) {
				$chars[] = $variantCharacteristic->characteristic_id;
			}
			$allMatches[] = $chars;
		}
		return $allMatches;

	}

	public function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics, $options=array()) {
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

		$selected_variant_id = 0;
		if(!empty($options['selected_variant_id']))
			$selected_variant_id = $options['selected_variant_id'];

		foreach($characteristics as $characteristic) {
			foreach($element->variants as $k => $variant) {
				if($variant->product_id != $characteristic->variant_product_id)
					continue;

				$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id] = $characteristic;
				$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id] = $characteristic;
				if($selected_variant_id && $variant->product_id==$selected_variant_id)
					$element->characteristics[$characteristic->characteristic_parent_id]->default = $characteristic;
			}
		}

		if(isset($_REQUEST['hikashop_product_characteristic'])) {
			if(is_array($_REQUEST['hikashop_product_characteristic'])) {
				hikashop_toInteger($_REQUEST['hikashop_product_characteristic']);
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
			if(!empty($element->characteristics)) {
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
			if($sort == 'price'){
				$key .= sprintf('%020.5f', $variant->product_sort_price);
			} else {
				foreach($variant->characteristics as $char) {
					if(in_array($sort,array('old','ordering'))) {
						$key .= sprintf('%04d', $char->$order).'+';
					} else {
						$key .= $char->$order.'+';
					}
				}
			}
			$key .= $variant->product_id;
			$optionsVariants[$key] = &$element->variants[$k2];
		}
		ksort($optionsVariants);
		$element->variants = $optionsVariants;
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
					$this->checkVariant($element->variants[$k2], $element, $map);
					break;
				}
			}

			if(!$found) {
				$new = new stdClass;
				$new->product_published = 0;
				$new->product_quantity = 0;
				$this->checkVariant($new, $element, $map, true);
				$element->variants[$new->map] = $new;
			}
		}
	}

	public function loadProductsListingData(&$rows, $options = array()){

		if(empty($rows))
			return true;

		if(!isset($options['currency_id']))
			$options['currency_id'] = hikashop_getCurrency();
		if(!isset($options['zone_id']))
			$options['zone_id'] = hikashop_getZone(null);
		if(!isset($options['load_badges']))
			$options['load_badges'] = false;
		if(!isset($options['load_custom_product_fields']))
			$options['load_custom_product_fields'] = 'display:front_listing=1';
		if(!isset($options['load_custom_item_fields']))
			$options['load_custom_item_fields'] = true;
		if(!isset($options['group_by_category']))
			$options['group_by_category'] = false;
		if(!isset($options['price_display_type']))
			$options['price_display_type'] = 'inherit';
		if(!isset($options['bundle_qty']))
			$options['bundle_qty'] = true;

		$ids = array();
		foreach($rows as $key => $row) {
			if(!is_null($row->product_id)) {
				$ids[] = (int)$row->product_id;
				$this->addAlias($rows[$key]);
			}
			$rows[$key]->product_name = hikashop_translate($row->product_name);
			$rows[$key]->product_description = hikashop_translate($row->product_description);
		}
		if(empty($ids))
			$ids = array(0);

		$q = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type IN (\'product\',\'file\') ORDER BY file_ref_id ASC, file_ordering ASC, file_id ASC';
		$this->database->setQuery($q);
		$product_files = $this->database->loadObjectList();
		if(!empty($product_files)){
			foreach($rows as $k => $row) {
				$this->addFiles($rows[$k],$product_files);
				if(!empty($rows[$k]->images)) {
					foreach($rows[$k]->images as $image) {
						if(!isset($rows[$k]->file_ref_id)) {
							foreach(get_object_vars($image) as $key => $name) {
								$rows[$k]->$key = $name;
							}
						}
					}
				}
				if(empty($rows[$k]->file_name)) {
					$rows[$k]->file_name = $rows[$k]->product_name;
				}
				if(empty($rows[$k]->file_description)) {
					$rows[$k]->file_description = $rows[$k]->product_name;
				}
			}
		}

		$q = 'SELECT variant_product_id FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',', $ids).')';
		$this->database->setQuery($q);
		$variants = $this->database->loadObjectList();
		if(!empty($variants)) {
			foreach($rows as &$product) {
				foreach($variants as $variant) {
					if($product->product_id == $variant->variant_product_id) {
						$product->has_options = true;
						break;
					}
				}
			}
			unset($product);
		}
		unset($variants);

		$q = 'SELECT product_id FROM '.hikashop_table('product_related').' WHERE product_related_type = '.$this->database->quote('options').' AND product_id IN ('.implode(',', $ids).')';
		$this->database->setQuery($q);
		$optionsData = $this->database->loadObjectList();
		if(!empty($optionsData)) {
			foreach($rows as $k => &$product) {
				foreach($optionsData as $option) {
					if($product->product_id == $option->product_id) {
						$product->has_options = true;
						break;
					}
				}
			}
			unset($product);
		}
		unset($optionsData);

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->getListingPrices($rows, $options['zone_id'], $options['currency_id'], $options['price_display_type']);

		if($options['group_by_category'] !== false) {
			$all_categories = array();

			$q = 'SELECT product_category.product_id, category.* '.
				' FROM ' . hikashop_table('product_category').' as product_category '.
				' INNER JOIN '.hikashop_table('category').' AS category ON product_category.category_id = category.category_id '.
				' INNER JOIN '.hikashop_table('category').' AS main_category ON (category.category_left >= main_category.category_left AND category.category_right <= main_category.category_right AND category.category_depth >= main_category.category_depth) '.
				' WHERE product_category.product_id IN ('.implode(',',$ids).')';
			if($options['group_by_category'])
				$q .= ' AND main_category.category_id IN ('.implode(',', $options['group_by_category']).')';
			$this->database->setQuery($q);
			$product_categories = $this->database->loadObjectList();

			$categories = array();
			foreach($product_categories as $product_category) {
				$product_category->category_name = hikashop_translate($product_category->category_name);
				if(empty($categories[$product_category->category_id])) {
					$categories[$product_category->category_id] = array(
						'category' => $product_category,
						'products' => array()
					);
				}
				$categories[$product_category->category_id]['products'][] = $product_category->product_id;
			}
			$this->sortedCategories = array();
			$this->_sortCategories($categories, $this->sortedCategories);
		}


		$q = 'SELECT c.*, pc.* '.
			' FROM '.hikashop_table('category').' AS c '.
			' LEFT JOIN '.hikashop_table('product_category').' AS pc ON c.category_id = pc.category_id '.
			' WHERE pc.product_id IN ('.implode(',', $ids).');';
		$this->database->setQuery($q);
		$categories = $this->database->loadObjectList();
		if(!empty($categories)) {
			foreach($rows as &$row) {
				$row->categories = array();
				foreach($categories as $category) {
					if($row->product_id > 0 && $row->product_id == $category->product_id) {
						$row->categories[(int)$category->category_id] = $category;
					}
				}
			}
			unset($row);
		}

		if($options['load_badges']) {
			$badgeClass = hikashop_get('class.badge');
			foreach($rows as $k => $row) {
				$badgeClass->loadBadges($rows[$k]);
			}
		}
		if(!hikashop_level(1))
			return true;

		if($options['load_custom_product_fields']) {
			$fieldsClass = hikashop_get('class.field');
			$this->productFields = $fieldsClass->getFields($options['load_custom_product_fields'], $rows, 'product', 'checkout&task=state');
		}

		if($options['bundle_qty']) {
			$pids = array();
			foreach($rows as $row) {
				if($row->product_quantity >= 0)
					continue;
				$pids[] = (int)$row->product_id;
			}
			if(!empty($pids)) {
				$query = 'SELECT pr.product_id, MIN( p.product_quantity / pr.product_related_quantity) as qty '.
						' FROM '.hikashop_table('product_related').' AS pr '.
						' INNER JOIN '.hikashop_table('product').' AS p ON pr.product_related_id = p.product_id '.
						' WHERE pr.product_id IN ('.implode(',', $pids).') AND pr.product_related_type = '.$this->database->quote('bundle').
							' AND p.product_quantity >= 0 GROUP BY pr.product_id';
				$this->database->setQuery($query);
				$relations = $this->database->loadObjectList('product_id');

				foreach($rows as $k => $row) {
					if($row->product_quantity>=0)
						continue;
					if(!isset($relations[(int)$row->product_id]))
						continue;
					$rows[$k]->product_quantity = floor($relations[(int)$row->product_id]->qty);
				}
			}
		}
		if(!hikashop_level(2))
			return true;

		if(!$options['load_custom_item_fields'])
			return true;

		$this->loadCustomItemFieldsForProductsListing($rows, $options);
		return true;
	}

	function loadCustomItemFieldsForProductsListing(&$rows, $options = array()) {
		$fieldsClass = hikashop_get('class.field');

		$this->itemFields = $fieldsClass->getFields('frontcomp', $rows, 'item', 'checkout&task=state');
		if(empty($this->itemFields))
			return true;

		$cats = $fieldsClass->getCategories('item', $rows);

		$item_keys = array('field_categories', 'field_products');
		foreach($this->itemFields as &$itemField) {
			foreach($item_keys as $k) {
				if(is_string($itemField->$k) && strpos($itemField->$k, ',') !== false) {
					$itemField->$k = explode(',', trim($itemField->$k, ','));
					hikashop_toInteger($itemField->$k);
				} elseif(!is_array($itemField->$k) && !empty($itemField->$k) && is_numeric($itemField->$k)) {
					$itemField->$k = array( (int)$itemField->$k );
				} elseif(empty($itemField->$k)) {
					$itemField->$k = array();
				}
			}

			$item_cats = array();
			if(!empty($itemField->field_with_sub_categories) && is_array($itemField->field_categories)) {
				foreach($itemField->field_categories as $c) {
					$item_cats[] = $c;
					foreach($cats['children'] as $k => $v) {
						if(in_array($c, $v))
							$item_cats[] = $k;
					}
				}
				array_unique($item_cats);
			}

			$isListingField = strpos($itemField->field_display, ';front_product_listing=1;') !== false;

			if(!$isListingField && empty($itemField->field_required))
				continue;

			$product_restriction = !empty($itemField->field_products) && count($itemField->field_products);
			$category_restriction = !empty($itemField->field_categories) && $itemField->field_categories != 'all';

			foreach($rows as &$row) {
				if(!isset($row->itemFields))
					$row->itemFields = array();

				if($product_restriction && in_array((int)$row->product_id, $itemField->field_products)) {
					$row->itemFields[$itemField->field_namekey] =& $itemField;
					if(!$isListingField)
						$row->has_options = true;
					$row->has_required_item_field = true;
					continue;
				}

				if($category_restriction) {
					$prod_cats = array_keys($row->categories);

					if(empty($item_cats)) {
						$tmp = array_intersect($itemField->field_categories, $prod_cats);
					} else {
						$tmp = array_intersect($item_cats, $prod_cats);
					}

					if(!empty($tmp)){
						$row->itemFields[$itemField->field_namekey] =& $itemField;
						if(!$isListingField)
							$row->has_options = true;
						$row->has_required_item_field = true;

						continue;
					}
				}

				if(!$product_restriction && !$category_restriction) {
					$row->itemFields[$itemField->field_namekey] =& $itemField;
					if(!$isListingField)
						$row->has_options = true;
					$row->has_required_item_field = true;
				}
			}
			unset($row);
		}
		unset($itemField);
		unset($prod_cats);
	}


	private function _sortCategories(&$in, &$out, $curr = null) {
		if(empty($in))
			return;

		if($curr === null) {
			$min_level = -1;
			foreach($in as $i) {
				if((int)$i['category']->category_depth < $min_level || $min_level == -1)
					$min_level = (int)$i['category']->category_depth;
			}
			$parents = array();
			foreach($in as $k => $i) {
				if($i['category']->category_depth == $min_level
				|| ($i['category']->category_depth > $min_level && !array_key_exists($i['category']->category_parent_id, $in) )
				) {
					$parents[$i['category']->category_parent_id] = $i['category']->category_parent_id;
				}
			}
			if(count($parents)){
				$this->database->setQuery('SELECT category_id,category_ordering FROM #__hikashop_category WHERE category_id IN ('.implode(',',$parents).')');
				$p = $this->database->loadObjectList('category_id');
			}
			$o = array();
			foreach($in as $k => $i) {
				if($i['category']->category_depth == $min_level) {
					$id = sprintf('%05d-%05d-%05d-%05d',  $p[$i['category']->category_parent_id]->category_ordering, $i['category']->category_parent_id, $i['category']->category_ordering, $i['category']->category_id);
					$o[ $id ] = $k;
				}
				if($i['category']->category_depth > $min_level && !array_key_exists($i['category']->category_parent_id, $in)) {
					$id = sprintf('%05d-%05d-%05d-%05d-%05d-%05d',  reset($p)->category_ordering, reset($p)->category_id, $p[$i['category']->category_parent_id]->category_ordering, $i['category']->category_parent_id, $i['category']->category_ordering, $i['category']->category_id);
					$o[ $id ] = $k;
				}
			}
			ksort($o);
			foreach($o as $k) {
				if(!isset($in[$k]))
					continue;
				$cur = $in[$k];
				$out[] = $cur;
				unset($in[$k]);
				$this->_sortCategories($in, $out, $cur['category']);
			}
		} else {
			$o = array();
			foreach($in as $k => $i) {
				if($i['category']->category_left > $curr->category_left && $i['category']->category_right < $curr->category_right) {
					$id = sprintf('%05d-%05d-%05d', $i['category']->category_depth, $i['category']->category_ordering, $i['category']->category_id);
					$o[ $id ] = $k;
				}
			}
			ksort($o);
			foreach($o as $k) {
				if(!isset($in[$k]))
					continue;
				$cur = $in[$k];
				$out[] = $cur;
				unset($in[$k]);
				$this->_sortCategories($in, $out, $cur['category']);
			}
		}
	}

	function save(&$element, $stats = false, $noAfterTrigger = false) {
		if(!$stats)
			$element->product_modified = time();

		$new = false;
		if(empty($element->product_id)) {
			if(strlen(@$element->product_quantity) == 0) {
				$element->product_quantity = -1;
			}
			$element->product_created = @$element->product_modified;
			$new = true;
		} else {
			$element->old = $this->get($element->product_id);
		}

		if(empty($element->product_id) && empty($element->product_type)) {
			if(!isset($element->product_parent_id) || empty($element->product_parent_id)) {
				$element->product_type = 'main';
			} else {
				$element->product_type = 'variant';
			}
		}
		if(isset($element->product_quantity) && !is_numeric($element->product_quantity))
			$element->product_quantity = -1;

		if(empty($element->product_id) && empty($element->product_code)) {
			if(!empty($element->product_name)) {
				$search = explode(',', 'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u');
				$replace = explode(',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u');
				$test = str_replace($search, $replace, $element->product_name);
				$test = preg_replace('#[^a-z0-9_-]#i', '', $test);
				if(empty($test)) {
					$query = 'SELECT MAX(`product_id`) FROM '.hikashop_table('product');
					$this->database->setQuery($query);
					$last_pid = $this->database->loadResult();
					$last_pid++;
					$element->product_code = 'product_'.$last_pid;
				} else {
					$test = str_replace($search, $replace, $element->product_name);
					$element->product_code = preg_replace('#[^a-z0-9_-]#i', '_', $test);
				}
			} elseif($element->product_type == 'variant' && !empty($element->product_parent_id) && !empty($element->characteristics)) {
				$parent = $this->get($element->product_parent_id);
				$element->product_code = $parent->product_code . '_' . implode('_', array_keys($element->characteristics));
			} else {
				return false;
			}
		}

		if(!empty($element->product_canonical)) {
			if(strpos($element->product_canonical, 'http://') !== false || strpos($element->product_canonical, 'https://') !== false) {
				if (strpos($element->product_canonical, HIKASHOP_LIVE)!==false) {
					$element->product_canonical = str_replace(HIKASHOP_LIVE,'',$element->product_canonical);
				} else {
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::sprintf('CANONICAL_URL_NOT_FROM_CURRENT_WEBSITE', $element->product_canonical, HIKASHOP_LIVE),'warning');
				}
			}
		}

		$this->recalculateSortPrice($element);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;

		if($new)
			$app->triggerEvent('onBeforeProductCreate', array( & $element, & $do) );
		else
			$app->triggerEvent('onBeforeProductUpdate', array( & $element, & $do) );

		if(!$do)
			return false;

		$tags = null;
		if(isset($element->tags)) {
			$tags = $element->tags;
			unset($element->tags);
		}

		$status = parent::save($element);

		if(!$status)
			return $status;

		$this->get('reset_cache');
		$element->product_id = $status;

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper->isMulti()) {
			$fieldsClass = hikashop_get('class.field');
			$columns = array('product_name', 'product_description', 'product_page_title', 'product_url', 'product_meta_description', 'product_keywords', 'product_alias', 'product_canonical');
			$fields = $fieldsClass->getFields('backend', $element, 'product', 'field&task=state');
			foreach($fields as $key => $field) {
				if(@$field->field_options['translatable'] == 1)
					$columns[] = $key;
			}
			$translationHelper->checkTranslations($element, $columns);
		}

		if(!$noAfterTrigger) {
			if($new)
				$app->triggerEvent( 'onAfterProductCreate', array( & $element ) );
			else
				$app->triggerEvent( 'onAfterProductUpdate', array( & $element ) );
		}

		if($tags === null && !empty($element->old)) {
			$tagsHelper = hikashop_get('helper.tags');
			if($tagsHelper->isCompatible()) {
				$tags = $tagsHelper->loadTags('product', $element->old);
				if(!empty($tags) && count($tags)) {
					$array = array();
					foreach($tags as $tag) {
						$array[] = $tag->tag_id;
					}
					$tags = $array;
				}
			}
		}

		$type = null;
		if(isset($element->product_type))
			$type = $element->product_type;
		elseif(!empty($element->old->product_type))
			$type = $element->old->product_type;

		if($tags !== null && $type!='variant') {
			$tagsHelper = hikashop_get('helper.tags');
			$fullElement = $element;
			if(!empty($element->old)) {
				foreach($element->old as $k => $v) {
					if(!isset($fullElement->$k))
						$fullElement->$k = $v;
				}
			}
			$tagsHelper->saveUCM('product', $fullElement, $tags);
		}

		return $status;
	}

	function saveRaw(&$element, $stats = false) {
		if(!$stats)
			$element->product_modified = time();

		$new = false;
		if(empty($element->product_id)) {
			$element->product_created = @$element->product_modified;
			$new = true;
		} else {
			$element->old = $this->get($element->product_id);
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;

		if($new)
			$app->triggerEvent('onBeforeProductCreate', array( & $element, & $do) );
		else
			$app->triggerEvent('onBeforeProductUpdate', array( & $element, & $do) );

		if(!$do)
			return false;
		$status = parent::save($element);

		if(!$status)
			return $status;

		$this->get('reset_cache');
		$element->product_id = $status;

		if($new)
			$app->triggerEvent( 'onAfterProductCreate', array( & $element ) );
		else
			$app->triggerEvent( 'onAfterProductUpdate', array( & $element ) );

		return $status;
	}

	function recalculateSortPrice(&$element) {
		if(!isset($element->prices))
			return false;

		$old_value = hikashop_toFloat( isset($element->product_sort_price) ? $element->product_sort_price : @$element->old->product_sort_price );

		if(empty($element->prices) || !count($element->prices)) {
			$element->product_sort_price = 0;

			if(isset($element->product_type) && $element->product_type != 'main' || empty($element->characteristics)) {
				return ($old_value != $element->product_sort_price);
			}

			$variant = $this->getMainVariant($element->product_id);
			if(!$variant) {
				return ($old_value != $element->product_sort_price);
			}

			$element->product_sort_price = $variant->product_sort_price;

			return ($old_value != $element->product_sort_price);
		}

		$this->selectSortPrice($element);

		if(!isset($element->product_type) || $element->product_type != 'variant' || empty($element->product_parent_id)) {
			return ($old_value != $element->product_sort_price);
		}

		$variant = $this->getMainVariant($element->product_parent_id);
		if(!empty($variant) && $variant->product_id != $element->product_id) {
			return ($old_value != $element->product_sort_price);
		}

		if($old_value == $element->product_sort_price)
			return false;

		$mainProduct = new stdClass();
		$mainProduct->product_id = (int)$element->product_parent_id;
		$mainProduct->product_sort_price = $element->product_sort_price;

		$this->save($mainProduct);

		return true;
	}

	function getMainVariant($id) {
		if(empty($id))
			return;

		$query = 'SELECT characteristic_id FROM #__hikashop_variant AS v '.
			' LEFT JOIN #__hikashop_characteristic AS c ON v.variant_characteristic_id = c.characteristic_id '.
			' WHERE c.characteristic_parent_id!=0 AND v.variant_product_id = '.(int)$id;
		$this->database->setQuery($query);
		$values_ids = $this->database->loadColumn();

		if(empty($values_ids))
			return;

		$leftjoins = array();
		foreach($values_ids as $k => $value_id) {
			$leftjoins[] = 'JOIN #__hikashop_variant AS v'.$k.' ON p.product_id = v'.$k.'.variant_product_id AND v'.$k.'.variant_characteristic_id = '.(int)$value_id;
		}

		$query = 'SELECT p.* FROM #__hikashop_product AS p '.implode(' ', $leftjoins).' WHERE p.product_parent_id = '.(int)$id;
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}

	function selectSortPrice(&$element) {
		$config = hikashop_config();
		$mainCurrency = (int) $config->get('main_currency', 1);
		$withTax = (bool) $config->get('price_with_tax', 1);

		$pricesArray = array();
		foreach($element->prices as $price) {
			if($price->price_currency_id == $mainCurrency)
				$pricesArray[] = $price;
		}
		if(empty($pricesArray)) {
			$currencyClass = hikashop_get('class.currency');
			foreach($element->prices as $price) {
				$convertedPrice = clone($price);
				$convertedPrice->price_value = $currencyClass->convertUniquePrice($convertedPrice->price_value, $convertedPrice->price_currency_id, $mainCurrency);
				$pricesArray[] = $convertedPrice;
			}
		}

		$publicPrices = array();
		foreach($pricesArray as $price) {
			if(!empty($price->price_users))
					continue;
			if(empty($price->price_access) || $price->price_access == 'all')
				$publicPrices[] = $price;
		}

		if(empty($publicPrices))
			$publicPrices = $pricesArray;

		$select_method = $config->get('sort_price_select_mode', 'unit');

		foreach($publicPrices as $price) {
			if((empty($select_method) || $select_method == 'auto') && $price->price_min_quantity <= 1) {
				$selectedPrice = $price;
				continue;
			}
			if($select_method == 'cheapest' && (empty($selectedPrice) || $selectedPrice->price_value > $price->price_value)) {
				$selectedPrice = $price;
				continue;
			}
			if($select_method == 'expensive' && (empty($selectedPrice) || $selectedPrice->price_value < $price->price_value)) {
				$selectedPrice = $price;
				continue;
			}
		}
		if(empty($selectedPrice))
			$selectedPrice = reset($publicPrices);

		$element->product_sort_price = hikashop_toFloat($selectedPrice->price_value);
	}

	function updatePrices($element,$status) {
		$filters=array('price_product_id='.$status);
		if(count($element->prices)){
			$ids = array();
			foreach($element->prices as $price){
				if(!empty($price->price_id) && !empty($price->price_value))
					$ids[] = $price->price_id;
			}
			if(!empty($ids)){
				$filters[]= 'price_id NOT IN ('.implode(',',$ids).')';
			}
		}
		$query = 'DELETE FROM '.hikashop_table('price').' WHERE '.implode(' AND ',$filters);
		$this->database->setQuery($query);
		$this->database->execute();

		if(count($element->prices)){
			$insert = array();
			foreach($element->prices as $price){
				if((int)$price->price_currency_id == 0)
					$price->price_currency_id = hikashop_getCurrency();
				if(empty($price->price_value) && $price->price_value !== '0.00000') continue;
				if(empty($price->price_id))	$price->price_id = 'NULL';
				$line = '('.(int)$price->price_currency_id.','.$status.','.(int)$price->price_min_quantity.','.(float)$price->price_value.','.$price->price_id.','.$this->database->Quote(@$price->price_site_id);
				if(hikashop_level(2)){
					if(empty($price->price_access))
						$price->price_access = 'all';

					if(empty($price->price_users))
						$price->price_users = '';
					if(empty($price->price_start_date))
						$price->price_start_date = 0;
					if(empty($price->price_end_date))
						$price->price_end_date = 0;

					$line.=','.$this->database->Quote($price->price_access).','.$this->database->Quote($price->price_users).','.(int)$price->price_start_date.','.(int)$price->price_end_date;
				}
				$insert[]=$line.')';
			}
			if(!empty($insert)){
				$select = 'price_currency_id,price_product_id,price_min_quantity,price_value,price_id,price_site_id';
				if(hikashop_level(2)){
					$select.=',price_access,price_users,price_start_date,price_end_date';
				}
				$query = 'REPLACE '.hikashop_table('price').' ('.$select.') VALUES '.implode(',',$insert).';';
				$this->database->setQuery($query);
				$this->database->execute();
			}
		}
	}

	function updateCharacteristics($element, $product_id = 0, $auto_variants = null) {
		$product_id = (int)$product_id;
		if($product_id == 0) {
			$product_id = (int)@$element->product_id;
			if($product_id == 0)
				return false;
		}

		if($element->product_type == 'variant') {

			$c = $element->characteristics;
			unset($c['']);

			$query = 'DELETE FROM ' . hikashop_table('variant') . ' WHERE variant_product_id = ' . $product_id;
			if(!empty($c))
				$query .= ' AND variant_characteristic_id NOT IN (' . implode(',', array_keys($c)) . ')';

			unset($c);

			$this->database->setQuery($query);
			$this->database->execute();

			if(!empty($element->characteristics)) {
				$insert = array();
				foreach(array_keys($element->characteristics) as $c) {
					if(is_numeric($c) && (int)$c > 0)
						$insert[] = (int)$c . ',' . (int)$product_id . ',0';
				}
				if(empty($insert))
					return false;

				$query = 'INSERT IGNORE INTO ' . hikashop_table('variant').
						' (variant_characteristic_id, variant_product_id, ordering)'.
						' VALUES (' . implode('),(', $insert) . ');';
				$this->database->setQuery($query);
				$this->database->execute();

				unset($insert);
			}
			return true;
		}

		if($element->product_type == 'main') {

			if(!empty($element->product_code) && !empty($element->old->product_code) && $element->product_code != $element->old->product_code) {
				if(HIKASHOP_J30)
					$product_code = "'" . $this->database->escape($element->old->product_code, true) . "%'";
				else
					$product_code = "'" . $this->database->getEscaped($element->old->product_code, true) . "%'";

				$query = 'UPDATE '.hikashop_table('product').
						' SET `product_code` = REPLACE(`product_code`,' . $this->database->Quote($element->old->product_code) . ',' . $this->database->Quote($element->product_code) . ')'.
						' WHERE `product_code` LIKE '.$product_code.' AND product_parent_id = '.(int)$element->product_id.' AND product_type = '.$this->database->Quote('variant');
				$this->database->setQuery($query);
				$this->database->execute();
			}

			$config = hikashop_config();
			if($auto_variants === null)
				$auto_variants = $config->get('auto_variants', 1);

			$characteristic_ids = array();
			$default_ids = array();
			$characteristics = array();
			$ordering_max = 0;
			if(!empty($element->characteristics)) {
				foreach($element->characteristics as $c) {
					$characteristic_ids[] = (int)$c->characteristic_id;
					$default_ids[] = (int)$c->default_id;
					$characteristics[ (int)$c->characteristic_id ] = $c;
					$ordering_max = max($ordering_max, $c->ordering);
				}

				foreach($element->characteristics as $c) {
					if($c->ordering <= 0)
						$c->ordering = ++$ordering_max;
				}
			}

			if(!empty($element->oldCharacteristics)) {
				hikashop_toInteger($element->oldCharacteristics);
			} else {
				if(!isset($element->oldCharacteristics)) {
					$query = 'SELECT c.characteristic_id FROM '.hikashop_table('variant').' AS v '.
						' LEFT JOIN '.hikashop_table('characteristic').' AS c ON v.variant_characteristic_id = c.characteristic_id '.
						' WHERE v.variant_product_id = '.(int)$product_id.' AND c.characteristic_parent_id = 0';
					$this->database->setQuery($query);
					$element->oldCharacteristics = $this->database->loadColumn();
				}
				if(empty($element->oldCharacteristics))
					$element->oldCharacteristics = array();
			}

			$addition = array_diff($characteristic_ids, $element->oldCharacteristics);
			$deletion = array_diff($element->oldCharacteristics, $characteristic_ids);

			$query = 'SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id = ' . (int)$product_id;
			$this->database->setQuery($query);
			$current_data = $this->database->loadObjectList();

			$removed = array();
			$ordering = array();
			$defaults = array();
			if(!empty($default_ids))
				$defaults = array_combine($default_ids, $default_ids);
			if(!empty($current_data)) {
				foreach($current_data as $c) {
					$i = (int)$c->variant_characteristic_id;
					if(isset($characteristics[$i])) {
						if($c->ordering != $characteristics[$i]->ordering)
							$ordering[$i] = $characteristics[$i]->ordering;
					} else if(isset($defaults[$i])) {
						unset($defaults[$i]);
					} else {
						$removed[] = $i;
					}
				}
			}

			if($auto_variants == 2) {
				$defaults = array(); $addition = array(); $deletion = array();
				$removed = array(); $ordering = array();
			}

			if(!empty($defaults) && isset($defaults[0]) && $defaults[0] === 0)
				unset($defaults[0]);
			if(!empty($defaults)) {
				$query = 'INSERT IGNORE INTO ' . hikashop_table('variant') .
					' (variant_characteristic_id, variant_product_id, ordering) VALUES ('.implode(','.(int)$product_id.',0),(', $defaults).','.(int)$product_id.',0)';
				$this->database->setQuery($query);
				$this->database->execute();
			}

			if(!empty($addition)) {
				$d = array();
				foreach($addition as $k) {
					$d[] = (int)$k . ',' . (int)$product_id . ',' . (int)$characteristics[(int)$k]->ordering;
				}
				$query = 'INSERT IGNORE INTO ' . hikashop_table('variant') .
					' (variant_characteristic_id, variant_product_id, ordering) VALUES ('.implode('),(', $d).')';
				$this->database->setQuery($query);
				$this->database->execute();
			}

			if(!empty($removed) || !empty($ordering)) {
				$ids = array_merge($removed, array_keys($ordering));
				$query = 'DELETE FROM ' . hikashop_table('variant') . ' WHERE '.
					' variant_product_id = ' . (int)$product_id.
					' AND variant_characteristic_id IN ('.implode(',', $ids).')';
				$this->database->setQuery($query);
				$this->database->execute();
			}

			if(!empty($ordering)) {
				$d = array();
				foreach($ordering as $k => $v) {
					$d[] = (int)$k . ',' . (int)$product_id . ',' . (int)$v;
				}
				$query = 'INSERT INTO ' . hikashop_table('variant') .
					' (variant_characteristic_id, variant_product_id, ordering) VALUES ('.implode('),(', $d).')';
				unset($d);
				$this->database->setQuery($query);
				$this->database->execute();
			}

			if(empty($addition) && empty($deletion) && $auto_variants != 2)
				return true;

			if($auto_variants == 0) {
				if(!empty($addition)) {
					foreach($addition as $a) {
						$query = 'INSERT IGNORE INTO ' . hikashop_table('variant') . ' (variant_characteristic_id, variant_product_id, ordering) ' .
							' SELECT ' . (int)$characteristics[(int)$a]->default_id . ' AS variant_characteristic_id, p.product_id, 0 AS ordering FROM ' . hikashop_table('product') . ' AS p '.
							' WHERE p.product_parent_id = ' . (int)$product_id . ' AND p.product_type = ' . $this->database->Quote('variant');
						$this->database->setQuery($query);
						$this->database->execute();

						if(HIKASHOP_J30)
							$product_code = "'" . $this->database->escape($element->product_code, true) . "%'";
						else
							$product_code = "'" . $this->database->getEscaped($element->product_code, true) . "%'";
						$query = 'UPDATE ' . hikashop_table('product') . ' AS p ' .
							' SET p.product_code = CONCAT(p.product_code, \'_'.(int)$characteristics[(int)$a]->default_id.'\') '.
							' WHERE p.product_parent_id = ' . (int)$product_id . ' AND p.product_type = ' . $this->database->Quote('variant') . ' AND p.product_code LIKE '.$product_code;
						$this->database->setQuery($query);
						$this->database->execute();
					}
				}

				if(!empty($deletion)) {
					$query = 'DELETE v.* FROM ' . hikashop_table('variant') . ' AS v '.
						' INNER JOIN ' . hikashop_table('characteristic') . ' AS c ON v.variant_characteristic_id = c.characteristic_id '.
						' INNER JOIN ' . hikashop_table('product') . ' AS p ON v.variant_product_id = p.product_id '.
						' WHERE p.product_parent_id = ' . (int)$product_id .
							' AND p.product_type = ' . $this->database->Quote('variant').
							' AND c.characteristic_parent_id IN (' . implode(',', $deletion). ')';
					$this->database->setQuery($query);
					$this->database->execute();
				}
			}
			else {
				JPluginHelper::importPlugin('hikashop');
				$app = JFactory::getApplication();

				$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id = ' . (int)$product_id . ' AND product_type = ' . $this->database->Quote('variant');
				$this->database->setQuery($query);
				$variant_ids = $this->database->loadColumn();

				$variants = array();
				if(!empty($variant_ids)) {
					hikashop_toInteger($variant_ids);
					if(version_compare(PHP_VERSION, '5.2.0', '>='))
						$variants = array_fill_keys($variant_ids, array());
					else
						$variants = array_combine($variant_ids, array_fill(0, count($variant_ids), array()));

					$query = 'SELECT v.variant_characteristic_id as `characteristic_id`, v.variant_product_id as `product_id`, c.characteristic_parent_id as `characteristic_parent` '.
							' FROM '.hikashop_table('variant').' as v LEFT JOIN '.hikashop_table('characteristic').' AS c ON v.variant_characteristic_id = c.characteristic_id '.
							' WHERE variant_product_id IN ('.implode(',', $variant_ids).')';
					$this->database->setQuery($query);
					$variant_data = $this->database->loadObjectList();
					foreach($variant_data as $d) {
						$variants[ (int)$d->product_id ][ (int)$d->characteristic_parent ] = (int)$d->characteristic_id;
					}
				}

				if(!empty($deletion)) {
					$k_list = array();
					$d_list = array();

					if(!empty($element->characteristics)) {
						foreach($variants as $pid => $variant) {
							$key = array();
							foreach($variant as $k => $v) {
								if(!in_array($k, $deletion)) $key[] = $v;
							}
							sort($key);
							$key = implode(';', $key);
							if(!isset($k_list[$key]) && !isset($d_list[$key])) {
								$k_list[$key] = $pid;
							} else {
								if(isset($k_list[$key])) {
									$d_list[$key] = array($k_list[$key]);
									unset($k_list[$key]);
								}
								$d_list[$key][] = $pid;
							}
						}
					}

					if(!empty($d_list) || empty($element->characteristics)) {
						$old_default = array_diff($removed, $deletion);

						$delete = array();
						foreach($d_list as $k => $products) {
							$r = array();
							foreach($products as $p) {
								$product = $variants[$p];
								$r[$p] = count( array_intersect($product, $old_default) );
							}
							arsort($r);
							$r = array_keys($r);
							$keep = array_shift($r);
							$k_list[$k] = $keep;
							$delete = array_merge($delete, $r);
						}

						if(empty($element->characteristics))
							$delete = array_keys($variants);

						if(!empty($delete)) {
							$app->triggerEvent('onBeforeVariantsDelete', array($product_id, $delete, $element));

							$query = 'DELETE p, v, pr, f '.
								' FROM '.hikashop_table('product').' AS p '.
									' INNER JOIN '.hikashop_table('variant').' AS v ON p.product_id = v.variant_product_id '.
									' LEFT JOIN '.hikashop_table('price').' AS pr ON p.product_id = pr.price_product_id '.
									' LEFT JOIN '.hikashop_table('file').' AS f ON (p.product_id = f.file_ref_id AND (f.file_type = '.$this->database->Quote('file').' OR f.file_type = '.$this->database->Quote('product').'))'.
								' WHERE p.product_id IN ('.implode(',', $delete).') AND p.product_type = ' . $this->database->Quote('variant');
							$this->database->setQuery($query);
							$this->database->execute();

							$translationHelper = hikashop_get('helper.translation');
							$translationHelper->deleteTranslations('product', $delete);

							$app->triggerEvent('onAfterVariantsDelete', array($product_id, $delete, $element));

							if(!empty($addition)) {
								foreach($delete as $d) {
									unset($variants[$d]);
								}
								foreach($variants as &$v) {
									foreach($deletion as $d) {
										unset($v[$d]);
									}
								}
								unset($v);
							}
						}
					}

					$query = 'DELETE v.* FROM ' . hikashop_table('variant') . ' AS v '.
						' INNER JOIN ' . hikashop_table('characteristic') . ' AS c ON v.variant_characteristic_id = c.characteristic_id '.
						' INNER JOIN ' . hikashop_table('product') . ' AS p ON v.variant_product_id = p.product_id '.
						' WHERE p.product_parent_id = ' . (int)$product_id .
							' AND p.product_type = ' . $this->database->Quote('variant').
							' AND c.characteristic_parent_id IN (' . implode(',', $deletion) . ')';
					$this->database->setQuery($query);
					$this->database->execute();

					if(!empty($k_list)) {
						$old_default = array_diff($removed, $deletion);
						$data = 'CONCAT(`product_code`,\'_\')';
						foreach($old_default as $default) {
							$data = 'REPLACE('.$data.',\'_'.$default.'_\',\'_\')';
						}
						$query = 'UPDATE '.hikashop_table('product').
							' SET `product_code` = TRIM(TRAILING \'_\' FROM '.$data.')'.
							' WHERE `product_id` IN ('.implode(',', $k_list).')';
						$this->database->setQuery($query);
						$this->database->execute();
					}
				}

				if(!empty($addition) || $auto_variants == 2) {
					$values = array();
					$values_defaults = array();
					foreach($addition as $a) {
						$values[$a] = $element->characteristics[$a]->values;
						$i = (int)$characteristics[(int)$a]->default_id;

						if(!empty($variants)) {
							$values_defaults[] = $i;
							unset($values[$a][ $i ]);
							if(empty($values[$a]))
								unset($values[$a]);
						}

						$query = 'INSERT IGNORE INTO ' . hikashop_table('variant') . ' (variant_characteristic_id, variant_product_id, ordering) ' .
							' SELECT ' . (int)$characteristics[(int)$a]->default_id . ' AS variant_characteristic_id, p.product_id, 0 AS ordering FROM ' . hikashop_table('product') . ' AS p '.
							' WHERE p.product_parent_id = ' . (int)$product_id . ' AND p.product_type = ' . $this->database->Quote('variant');
						$this->database->setQuery($query);
						$this->database->execute();
					}
					ksort($values);

					if($auto_variants == 2) {
						if(!empty($element->duplicateVariants)) {

							$v_list = array();
							foreach($variants as $pid => $variant) {
								ksort($variant);
								$key = implode('_', $variant);
								$v_list[$key] = $pid;
							}

							$ids = $element->duplicateVariants;
							hikashop_toInteger($ids);
							$ids = array_combine($ids, $ids);

							$c_id = array_keys($element->characteristics);
							$c_id = reset($c_id);
							$d_list = array();
							$r_list = array();
							foreach($variants as $pid => $variant) {
								if(!isset($ids[$pid])) {
									unset($variants[$pid]);
									continue;
								}

								$v = $variant[$c_id];
								$r_list[$v] = $v;

								ksort($variant);
								foreach($element->characteristics[$c_id]->values as $k => $v) {
									$variant[$c_id] = $v;
									$key = implode('_', $variant);
									$d_list[$key] = $pid;
								}
							}


							$having = null;
							$k_list = array_intersect($v_list, $d_list);
							if(!empty($k_list)) {
								$having = array();
								foreach($k_list as $k => $v) {
									$having[] = $this->database->Quote($element->product_code . '_' . $k);
								}
								$having = ' HAVING c_product_code NOT IN (' . implode(', ', $having) . ')';
							}
						}
						else {
							$having = array();
							sort($characteristic_ids);
							foreach($variants as $k => $v) {
								$f = true;
								foreach($characteristic_ids as $a) {
									if(!isset($v[$a]) || !in_array($v[$a], $element->characteristics[$a]->values)) {
										$f = false;
										break;
									}
								}
								if($f) {
									ksort($v);
									$p = $element->product_code . '_' . implode('_', $v);
									$having[] = $this->database->Quote($p);
								}
							}
							if(!empty($having)) {
								$having = ' HAVING c_product_code NOT IN (' . implode(', ', $having) . ')';
							} else {
								$having = null;
							}
							unset($variants);
							$variants = array();
						}

						foreach($characteristic_ids as $a) {
							$values[$a] = $element->characteristics[$a]->values;
						}
						ksort($values);
					}

					$p_code = $this->database->Quote($element->product_code . '_');
					$t = time();
					$concat = array();
					$tables = array();
					$filters = array();
					foreach($values as $k => $v) {
						$concat[] = 'c'.$k.'.characteristic_id';
						$tables[] = hikashop_table('characteristic') . ' AS c'.$k;
						if(empty($v))
							$v = array(0 => 0);
						$filters[] = 'c'.$k.'.characteristic_id IN (\''.implode('\',\'', array_keys($v)).'\')';
					}

					if(!empty($variants)) {
						$duplicate_ids = array_keys($variants);

						$fields = $this->database->getTableColumns(hikashop_table('product'));

						unset($fields['product_id']);
						unset($fields['product_code']);
						unset($fields['product_parent_id']);
						unset($fields['product_created']);
						unset($fields['product_modified']);

						unset($fields['product_hit']);
						unset($fields['product_last_seen_date']);

						$fields = array_keys($fields);

						$p_code = 'p.product_code, \'_\'';
						if(!empty($r_list)) {
							$p_code = 'CONCAT(`product_code`,\'_\')';
							foreach($r_list as $r) {
								$p_code = 'REPLACE('.$p_code.',\'_'.$r.'_\',\'_\')';
							}
						}

						if(!empty($concat) && count($concat))
							$p_code = 'CONCAT('.$p_code.', '.implode(',\'_\',', $concat).')';


						$query = 'INSERT IGNORE INTO ' . hikashop_table('product') . ' (product_code, product_parent_id, product_created, product_modified, product_hit, product_last_seen_date, '.implode(', ', $fields).') '.
							' SELECT '.$p_code.' AS c_product_code, p.product_id, '.$t.', '.$t.', 0, 0, '.implode(', ', $fields).
							' FROM ' . hikashop_table('product') . ' AS p, '. implode(', ', $tables).
							' WHERE p.product_id IN ('.implode(',', $duplicate_ids).')';
						if(!empty($filters) && count($filters))
							$query .= ' AND ('.implode(') AND (', $filters) . ')';
						if(!empty($having))
							$query .= $having;
						$this->database->setQuery($query);
						$this->database->execute();

						if(!empty($values_defaults)) {
							$query = 'UPDATE ' . hikashop_table('product') . ' SET product_code = CONCAT(product_code, \'_'.implode('_', $values_defaults).'\') WHERE product_id IN ('.implode(',', $duplicate_ids).')';
							$this->database->setQuery($query);
							$this->database->execute();
						}

						$query = 'INSERT IGNORE INTO ' . hikashop_table('price') . ' (price_product_id, price_currency_id, price_value, price_min_quantity, price_access, price_users, price_site_id) '.
							' SELECT p.product_id, pr.price_currency_id, pr.price_value, pr.price_min_quantity, pr.price_access, pr.price_users, pr.price_site_id '.
							' FROM ' . hikashop_table('product') . ' AS p '.
							' INNER JOIN ' . hikashop_table('price') . ' AS pr ON p.product_parent_id = pr.price_product_id '.
							' WHERE p.product_parent_id IN ('.implode(',', $duplicate_ids).')';
						$this->database->setQuery($query);
						$this->database->execute();

						$query = 'INSERT IGNORE INTO ' . hikashop_table('file') . ' (file_ref_id, file_name, file_description, file_path, file_type, file_free_download, file_ordering, file_limit) '.
							' SELECT p.product_id, f.file_name, f.file_description, f.file_path, f.file_type, f.file_free_download, f.file_ordering, f.file_limit '.
							' FROM ' . hikashop_table('product') . ' AS p '.
							' INNER JOIN ' . hikashop_table('file') . ' AS f ON (p.product_parent_id = f.file_ref_id AND f.file_type IN (\'file\',\'product\')) '.
							' WHERE p.product_parent_id IN ('.implode(',', $duplicate_ids).')';
						$this->database->setQuery($query);
						$this->database->execute();

						$query = 'INSERT IGNORE INTO ' . hikashop_table('shipping_price') . ' (shipping_price_ref_id, shipping_id, shipping_price_ref_type, shipping_price_min_quantity, shipping_price_value, shipping_fee_value) '.
							' SELECT p.product_id, s.shipping_id, s.shipping_price_ref_type, s.shipping_price_min_quantity, s.shipping_price_value, s.shipping_fee_value '.
							' FROM ' . hikashop_table('product') . ' AS p '.
							' INNER JOIN ' . hikashop_table('shipping_price') . ' AS s ON (p.product_parent_id = s.shipping_price_ref_id AND shipping_price_ref_type = \'product\')'.
							' WHERE p.product_parent_id IN ('.implode(',', $duplicate_ids).')';
						$this->database->setQuery($query);
						$this->database->execute();

						$app->triggerEvent('onAfterVariantsDuplicate', array($product_id, $duplicate_ids, $element));

						$query = 'SELECT p.product_id, p.product_parent_id, p.product_code FROM ' . hikashop_table('product') . ' AS p WHERE p.product_parent_id IN ('.implode(',', $duplicate_ids).') AND p.product_created = ' . $t;
						$this->database->setQuery($query);
						$new_variants = $this->database->loadObjectList('product_id');
					}
					else {
						$config =& hikashop_config();
						$symbols = explode(',',$config->get('weight_symbols', 'kg,g'));
						$weight_unit = $symbols[0];
						$symbols = explode(',', $config->get('volume_symbols', 'm,cm'));
						$volume_unit = $symbols[0];

						if(!empty($concat) && count($concat))
							$p_code = 'CONCAT('.$p_code.', '.implode(',\'_\',', $concat).')';
						$query = 'INSERT IGNORE INTO '.hikashop_table('product').' (product_code, product_type, product_parent_id, product_published, product_modified, product_created, product_group_after_purchase, product_weight_unit, product_dimension_unit) '.
							' SELECT '.$p_code.' as c_product_code, '. $this->database->Quote('variant') .','. (int)$product_id . ','.(int)$config->get('variant_default_publish',1).',' . $t . ',' . $t . ',' . $this->database->Quote(@$element->product_group_after_purchase).','.$this->database->Quote($weight_unit).','.$this->database->Quote($volume_unit).
							' FROM ' . implode(', ', $tables);
						if(!empty($filters) && count($filters))
							$query .= ' WHERE ('.implode(') AND (', $filters) . ')';
						if(!empty($having))
							$query .= $having;
						$this->database->setQuery($query);
						$this->database->execute();

						$query = 'SELECT p.product_id, p.product_parent_id, p.product_code FROM ' . hikashop_table('product') . ' AS p WHERE p.product_parent_id = ' . (int)$product_id . ' AND p.product_created = ' . $t;
						$this->database->setQuery($query);
						$new_variants = $this->database->loadObjectList('product_id');
					}

					$data = array();
					$count_values = count($values);
					$value_characteristic = array();
					$new_variants_ids = array_keys($new_variants);

					foreach($addition as $a) {
						foreach($element->characteristics[$a]->values as $k => $v) {
							$value_characteristic[ (int)$k ] = (int)$a;
						}
					}

					if($auto_variants == 2) {
						foreach($characteristic_ids as $a) {
							foreach($element->characteristics[$a]->values as $k => $v) {
								$value_characteristic[ (int)$k ] = (int)$a;
							}
						}
					}

					foreach($new_variants as $v) {
						if((int)$v->product_parent_id != (int)$product_id) {
							foreach($variants[(int)$v->product_parent_id] as $k => $variant) {
								if($auto_variants == 2 && in_array($k, $characteristic_ids))
									continue;
								$data[] = (int)$variant . ',' . (int)$v->product_id;
							}
						}
						$codes = explode('_', $v->product_code);
						$codes = array_slice($codes, -$count_values);
						foreach($codes as $code) {
							if(isset($value_characteristic[ (int)$code ]))
								$data[] = (int)$code . ',' . (int)$v->product_id;
						}
					}
					unset($value_characteristic);
					unset($count_values);
					unset($new_variants);

					while(!empty($data)) {
						$sql_data = array_slice($data, 0, 250);
						$data = array_slice($data, 250);
						$query = 'INSERT IGNORE INTO ' . hikashop_table('variant') . ' (variant_characteristic_id, variant_product_id, ordering) '.
							' VALUES ('.implode(',0), (', $sql_data).',0)';
						$this->database->setQuery($query);
						$this->database->execute();
						unset($sql_data);
					}

					if(!empty($variants)) {
						if(empty($duplicate_ids))
							$duplicate_ids = array_keys($variants);
						$query = 'UPDATE ' . hikashop_table('product') . ' SET product_parent_id = ' . (int)$product_id . ' WHERE product_parent_id IN ('.implode(',', $duplicate_ids).')';
						$this->database->setQuery($query);
						$this->database->execute();
					}

					$app->triggerEvent('onAfterVariantsCreation', array($product_id, $new_variants_ids, $element));
				}
			}
		}
		return true;
	}

	function updateRelated($element, $status, $type = 'related') {
		if($element->product_type == 'variant')
			return true;

		if($type == 'bundle' && !hikashop_level(1))
			return true;

		$quotedType = $this->database->Quote($type);

		$filter = '';
		$config = hikashop_config();
		$both_ways = $config->get('product_association_in_both_ways', 0);
		if($both_ways) {
			$query = 'SELECT product_related_id FROM '.hikashop_table('product_related').
				' WHERE product_related_type = '.$quotedType.' AND product_id = '.(int)$status . $filter;
			$this->database->setQuery($query);
			$this->database->execute();
			$products = $products = $this->database->loadObjectList();
		}

		$query = 'DELETE FROM '.hikashop_table('product_related').
			' WHERE product_related_type = '.$quotedType.' AND product_id = ' . (int)$status . $filter;
		$this->database->setQuery($query);
		$this->database->execute();

		if(count($element->$type)) {
			$insert = array();
			foreach($element->$type as $new) {
				if(!isset($new->product_related_quantity) || $type != 'bundle')
					$new->product_related_quantity = 0;
				$insert[] = '(' . (int)$new->product_related_id . ',' . (int)$status . ',' . $quotedType . ',' . (int)$new->product_related_ordering . ',' . (int)$new->product_related_quantity . ')';
			}
			if($both_ways && $type == 'related') {
				foreach($element->$type as $new) {
					$insert[] = '(' . (int)$status . ',' . (int)$new->product_related_id . ',' . $quotedType . ',' . (int)$new->product_related_ordering . ',' . (int)$new->product_related_quantity . ')';
					foreach($products as $product) {
						if($product->product_related_id == $new->product_related_id) {
							$product->still_related = true;
						}
					}
				}
			}
			$query = 'INSERT IGNORE INTO ' . hikashop_table('product_related') .
				' (product_related_id, product_id, product_related_type, product_related_ordering, product_related_quantity) ' .
				' VALUES ' . implode(',', $insert) . ';';
			$this->database->setQuery($query);
			$this->database->execute();
		}

		if($both_ways && $type == 'related') {
			$ids = array();
			foreach($products as $product) {
				if(!isset($product->still_related) || $product->still_related != true) {
					$ids[] = (int)$product->product_related_id;
				}
			}

			if(count($ids)) {
				$query = 'DELETE FROM '.hikashop_table('product_related').
					' WHERE product_related_type = '.$quotedType.' AND product_id IN ('.implode(',', $ids).') AND product_related_id = '.(int)$status;
				$this->database->setQuery($query);
				$this->database->execute();
			}
		}
	}

	function updateCategories(&$element, $status) {
		if($element->product_type=='variant')
			return false;

		if((empty($element->categories) || !count($element->categories) ) && $element->product_type == 'main') {
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' AND category_parent_id=0 LIMIT 1';
			$this->database->setQuery($query);
			$root = $this->database->loadResult();
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_parent_id='.(int)$root.' AND category_type=\'product\' LIMIT 1';
			$this->database->setQuery($query);
			$root = $this->database->loadResult();
			$element->categories = array($root);
		}

		if(empty($element->categories))
			return false;

		$this->database->setQuery('SELECT * FROM '.hikashop_table('product_category').' WHERE product_id='.$status);
		$olds = $this->database->loadObjectList('category_id');

		$keep = array_intersect($element->categories, array_keys($olds));
		$delete = array_diff(array_keys($olds), $keep);
		$news = array_diff($element->categories, $keep);

		$this->database->setQuery('DELETE FROM '.hikashop_table('product_category').' WHERE product_id='.$status);
		$this->database->execute();

		$insert = array();
		foreach($element->categories as $entry){
			$insert[]='('.(int)$entry.','.(int)$status.','.(int)@$olds[$entry]->ordering.')';
		}
		$query = 'INSERT IGNORE INTO '.hikashop_table('product_category').' (category_id,product_id,ordering) VALUES '.implode(',',$insert).';';
		$this->database->setQuery($query);
		$this->database->execute();

		$reorders = array_merge($news, $delete);
		if(!empty($reorders)) {
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = 'product_category_id';
			$orderHelper->table = 'product_category';
			$orderHelper->groupMap = 'category_id';
			$orderHelper->orderingMap = 'ordering';
			foreach($reorders as $reorder){
				$orderHelper->groupVal = $reorder;
				$orderHelper->reOrder();
			}
		}

		return (!empty($news) || !empty($delete));
	}

	function updateFiles(&$element,$status,$type='images',$orders=null){
		$filter='';
		if(count($element->$type)){
			$filter = 'AND file_id NOT IN ('.implode(',',$element->$type).')';
		}
		$file_type = 'product';
		if($type == 'files'){
			$file_type = 'file';
		}
		$main = ' FROM '.hikashop_table('file').' WHERE file_ref_id = '.$status.' AND file_type=\''.$file_type.'\' AND SUBSTRING(file_path,1,1) != \'@\' '.$filter;
		$this->database->setQuery('SELECT file_path '.$main);
		$toBeRemovedFiles = $this->database->loadColumn();
		if(!empty($toBeRemovedFiles)){
			$config = hikashop_config();
			if(!$config->get('keep_category_product_images', 0)) {
				$fileClass = hikashop_get('class.file');
				$uploadPath = $fileClass->getPath($file_type);
				$oldFiles = array();
				foreach($toBeRemovedFiles as $old){
					$oldFiles[] = $this->database->Quote($old);
				}

				$filter = '';
				if(!empty($element->$type) && count($element->$type))
					$filter = ' OR file_id IN ('.implode(',',$element->$type).')';
				$query = 'SELECT file_path FROM '.hikashop_table('file').' WHERE file_path IN ('.implode(',',$oldFiles).') AND (file_ref_id != '.$status.$filter.')';
				$this->database->setQuery($query);
				$keepFiles = $this->database->loadColumn();
				foreach($toBeRemovedFiles as $old){
					if((empty($keepFiles) || !in_array($old,$keepFiles)) && JFile::exists( $uploadPath . $old)){
						JFile::delete( $uploadPath . $old );
						jimport('joomla.filesystem.folder');
						$thumbnail_folders = JFolder::folders($uploadPath);
						if(JFolder::exists($uploadPath.'thumbnails'.DS)) {
							$other_thumbnail_folders = JFolder::folders($uploadPath.'thumbnails');
							foreach($other_thumbnail_folders as $other_thumbnail_folder) {
								$thumbnail_folders[] = 'thumbnails'.DS.$other_thumbnail_folder;
							}
						}
						foreach($thumbnail_folders as $thumbnail_folder){
							if($thumbnail_folder != 'thumbnail' && substr($thumbnail_folder, 0, 9) != 'thumbnail' && substr($thumbnail_folder, 0, 11) != ('thumbnails'.DS))
								continue;
							if(!in_array($file_type,array('file','watermark')) && JFile::exists(  $uploadPath .$thumbnail_folder.DS. $old)){
								JFile::delete( $uploadPath .$thumbnail_folder.DS. $old );
							}
						}
					}
				}
			}
			$this->database->setQuery('DELETE'.$main);
			$this->database->execute();
		}
		if(!empty($orders) && is_array($element->$type) && count($element->$type)) {
			$this->database->setQuery('SELECT file_id, file_ordering FROM '.hikashop_table('file').' WHERE file_id IN ('.implode(',',$element->$type).')');
			$oldOrders = $this->database->loadObjectList();
			if(!empty($oldOrders)) {
				foreach($oldOrders as $oldOrder) {
					if(isset($orders[$oldOrder->file_id]) && $orders[$oldOrder->file_id] != $oldOrder->file_ordering) {
						$this->database->setQuery('UPDATE '.hikashop_table('file').' SET file_ordering = '.(int)$orders[$oldOrder->file_id].' WHERE file_id = '.$oldOrder->file_id);
						$this->database->execute();
					}
				}
			}
		}
		if(count($element->$type)){
			$query = 'UPDATE '.hikashop_table('file').' SET file_ref_id='.$status.' WHERE file_id IN ('.implode(',',$element->$type).') AND file_ref_id=0';
			$this->database->setQuery($query);
			$this->database->execute();
		}
	}

	function delete(&$elements, $ignoreFile = false) {
		if(!is_array($elements))
			$elements = array($elements);
		hikashop_toInteger($elements);

		if(!empty($elements)) {
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_type=\'variant\' AND product_parent_id IN ('.implode(',',$elements).')';
			$this->database->setQuery($query);
			$elements = array_merge($elements, $this->database->loadColumn());
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeProductDelete', array(&$elements, &$do));
		if(!$do)
			return false;

		$fieldClass = hikashop_get('class.field');
		$fieldClass->handleBeforeDelete($elements, 'product');

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterProductDelete', array(&$elements));

			$tagsHelper = hikashop_get('helper.tags');
			$tagsHelper->deleteUCM('product', $elements);

			$fileClass = hikashop_get('class.file');
			$fileClass->deleteFiles('product', $elements, $ignoreFile);
			$fileClass->deleteFiles('file', $elements, $ignoreFile);

			$fieldClass->handleAfterDelete($elements, 'product');

			$translationHelper = hikashop_get('helper.translation');
			$translationHelper->deleteTranslations('product', $elements);
			return count($elements);
		}
		return $status;
	}

	public function trash(&$elements) {
		if(!is_array($elements))
			$elements = array($elements);
		hikashop_toInteger($elements);
		if(empty($elements))
			return false;

		$query = 'SELECT product_id FROM '.hikashop_table('product').
			' WHERE product_type IN ('.$this->database->Quote('main').','.$this->database->Quote('variant').') '.
				' AND product_id IN ('.implode(',', $elements).')';
		$this->database->setQuery($query);
		$product_ids = $this->database->loadColumn();

		hikashop_toInteger($product_ids);

		$diff = array_diff($elements, $product_ids);
		if(!empty($diff)) {
			$ret = $this->delete($diff);
			if($ret === false)
				return false;
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeProductTrash', array(&$product_ids, &$do));
		if(!$do || empty($product_ids))
			return false;

		$query = 'UPDATE '.hikashop_table('product').' SET product_type = ' . $this->database->Quote('trash').
			' WHERE product_id IN ('.implode(',', $product_ids).')';
		$this->database->setQuery($query);
		$this->database->execute();

		$app->triggerEvent('onAfterProductTrash', array(&$product_ids));

		return count($product_ids);
	}

	public function untrash(&$elements) {
		if(!is_array($elements))
			$elements = array($elements);
		hikashop_toInteger($elements);
		if(empty($elements))
			return false;

		$query = 'SELECT product_id FROM '.hikashop_table('product').
			' WHERE product_type = '.$this->database->Quote('trash').' '.
				' AND product_id IN ('.implode(',', $elements).')';
		$this->database->setQuery($query);
		$product_ids = $this->database->loadColumn();

		hikashop_toInteger($product_ids);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeProductUntrash', array(&$product_ids, &$do));
		if(!$do)
			return false;

		$query = 'UPDATE '.hikashop_table('product').' SET product_type = ' . $this->database->Quote('main').
			' WHERE product_id IN ('.implode(',', $product_ids).') AND product_parent_id = 0';
		$this->database->setQuery($query);
		$this->database->execute();

		$query = 'UPDATE '.hikashop_table('product').' SET product_type = ' . $this->database->Quote('variant').
			' WHERE product_id IN ('.implode(',', $product_ids).') AND product_parent_id > 0';
		$this->database->setQuery($query);
		$this->database->execute();

		$app->triggerEvent('onAfterProductUntrash', array(&$product_ids));

		return count($product_ids);
	}

	function addFiles(&$element, &$files) {
		if(!empty($element->variants)) {
			foreach($element->variants as $k => $variant) {
				$this->addFiles($element->variants[$k], $files);
			}
		}
		if(!empty($element->options)) {
			foreach($element->options as $k => $optionElement) {
				$this->addFiles($element->options[$k], $files);
			}
		}
		foreach($files as $file) {
			if($file->file_ref_id != $element->product_id)
				continue;

			if($file->file_type == 'file') {
				$element->files[] = $file;
			} else {
				if(empty($file->file_name)) {
					$file->file_name = $element->product_name;
				}
				if(empty($file->file_description)) {
					$file->file_description = $element->product_name;
				}
				$element->images[] = $file;
			}

		}
	}

	function checkVariant(&$variant,&$element,$map=array(),$force=false){
		if(!empty($variant->variant_checked)) return true;
		$checkfields = array(
			'product_name','product_description','prices','images','discount','product_url',
			'product_weight','product_weight_unit','product_keywords','product_meta_description',
			'product_dimension_unit','product_width','product_length','product_height','files',
			'product_contact','product_max_per_order','product_min_per_order','product_sale_start',
			'product_sale_end','product_manufacturer_id','file_path','file_name','file_description',
			'product_warehouse_id','product_selection_method'
		);
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('frontcomp',$element,'product','checkout&task=state');
		foreach($fields as $field){
			$checkfields[]=$field->field_namekey;
		}
		if(empty($variant->product_id)) {
			$variant->product_id = $element->product_id;
			$variant->map = implode('_', $map);
			$variant->product_parent_id = $element->product_id;
			$variant->product_quantity = 0;
			$variant->product_code = '';
			$variant->product_published = -1;
			$variant->product_type = 'variant';
			$variant->product_sale_start = 0;
			$variant->product_sale_end = 0;
			$variant->product_created = 0;
			$variant->characteristics = array();
			foreach($map as $k => $id) {
				$variant->characteristics[$id] = $element->characteristics[$k]->values[$id];
			}
		} else if(empty($variant->characteristics)) {
			$variant->characteristics = array();
		}

		if(isset($variant->product_weight) && $variant->product_weight == 0) {
			$variant->product_weight_unit = $element->product_weight_unit;
		}
		if(isset($variant->product_length) && isset($variant->product_height) && isset($variant->product_width) && $variant->product_length==0 && $variant->product_height==0 && $variant->product_width==0){
			$variant->product_dimension_unit = $element->product_dimension_unit;
		}

		$variant->main_product_name = @$element->product_name;
		$variant->main_product_quantity_layout = @$element->product_quantity_layout;
		$variant->product_canonical = @$element->product_canonical;
		$variant->product_alias = @$element->product_alias;
		$variant->characteristics_text = '';
		$variant->variant_name = @$variant->product_name;

		$config =& hikashop_config();
		$perfs = (int)$config->get('variant_increase_perf', 1);
		$separator = JText::_('HIKA_VARIANTS_MIDDLE_SEPARATOR');
		if($separator == 'HIKA_VARIANTS_MIDDLE_SEPARATOR')
			$separator = ' ';
		$product_price_percentage = (float) @$variant->product_price_percentage;
		foreach($checkfields as $field) {
			if($field == 'images') {
				$image_mode = $config->get('variant_images_behavior', 'replace_main_product_images');
				if(empty($variant->$field) && !empty($element->$field)) {
					$variant->$field = hikashop_copy($element->$field);
				} elseif($image_mode != 'replace_main_product_images' && !empty($element->$field)) {
					if($image_mode == 'display_along_main_product_images')
						$variant->$field = array_merge(hikashop_copy($element->$field), $variant->$field);
					else
						$variant->$field = array_merge($variant->$field, hikashop_copy($element->$field));
				}
				continue;
			}
			if(!empty($variant->$field) && $field != 'product_name' && (!is_numeric($variant->$field) || bccomp(sprintf('%F',$variant->$field),0,5)))
				continue;

			if(isset($element->$field) && (is_array($element->$field) && count($element->$field) || is_object($element->$field))) {
				switch($field) {
					case 'prices':
						$variant->$field = hikashop_copy($element->$field);

						if(!empty($variant->cart_product_total_variants_quantity)) {
							$variant->cart_product_total_quantity = $variant->cart_product_total_variants_quantity;
						}
						if($product_price_percentage <= 0)
							break;

						foreach($variant->$field as $k => $v) {
							foreach(get_object_vars($v) as $key => $value) {
								if(in_array($key, array('taxes_without_discount', 'taxes', 'taxes_orig'))) {
									foreach($value as $taxKey => $tax) {
										$variant->prices[$k]->taxes[$taxKey]->tax_amount = @$tax->tax_amount * $product_price_percentage / 100;
									}
								} elseif(is_numeric($value) && !in_array($key,array('price_currency_id','price_orig_currency_id','price_min_quantity','price_access', 'price_users'))) {
									$variant->prices[$k]->$key = $value * $product_price_percentage / 100;
								}
							}
						}
						break;
					default:
						$variant->$field = hikashop_copy($element->$field);
						break;
				}
			} else if($field == 'product_name') {
				if(!empty($variant->characteristics)) {
					foreach($variant->characteristics as $val) {
						$char_value = $val->characteristic_value;
						if(strpos($char_value, '<') === false)
							$char_value = hikashop_translate($char_value);
						$variant->characteristics_text .= $separator . $char_value;
					}
				}
			} else if(!$perfs || $force) {
				$variant->$field = @$element->$field;
			}
		}
		$variant->characteristics_text = ltrim($variant->characteristics_text, $separator);
		if(empty($variant->product_name))
			$variant->product_name = $variant->main_product_name;

		if(!empty($variant->main_product_name) && $config->get('append_characteristic_values_to_product_name', 1)) {
			$separator = JText::_('HIKA_VARIANT_SEPARATOR');
			if($separator == 'HIKA_VARIANT_SEPARATOR')
				$separator = ': ';
			if(!empty($variant->variant_name))
				$variant->characteristics_text = $variant->variant_name;
			$variant->product_name = $variant->main_product_name.'<span class="hikashop_product_variant_subname">'.$separator.$variant->characteristics_text.'</span>';
		}
		if(empty($variant->product_published))
			$variant->product_quantity = 0;
		$variant->variant_checked = true;

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onAfterVariantChecked', array( &$variant, &$element));
	}

	function generateVariantData(&$element){
		$config =& hikashop_config();
		$perfs = $config->get('variant_increase_perf',1);
		if($perfs && !empty($element->main)){
			$required_fields = array();

			foreach (get_object_vars($element->main) as $name=>$value) {
				if(is_array($name) || is_object($name))
					continue;

				$required = false;

				foreach ($element->variants as $variant) {
					if(!empty($variant->$name) && (!is_numeric($variant->$name) || $variant->$name>0)){
						$required = true;
						break;
					}
				}
				if(!$required)
					continue;

				foreach($element->variants as $k=>$variant) {
					if(!empty($variant->$name) && (!is_numeric($variant->$name) || $variant->$name != 0.0))
						continue;

					if(($name == 'product_quantity' || $name == 'product_published') && $variant->$name == 0)
						continue;

					$element->variants[$k]->$name = $element->main->$name;
				}
			}
		}

		if(!isset($element->main->images)) {
			if(!isset($element->main))
				$element->main = new stdClass();
			$element->main->images = null;
		}
	}

	public function hit($product) {
		if(is_object($product))
			$product_id = $product->product_id;
		else
			$product_id = (int)$product;

		if(empty($product))
			return false;

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$do = true;

		$app->triggerEvent('onBeforeProductHit', array($product_id, &$do) );
		if(!$do)
			return false;

		$db = JFactory::getDBO();
		$query = 'UPDATE '.hikashop_table('product').' SET product_hit = product_hit + 1, product_last_seen_date = '.(int)time().' WHERE product_id = '.$product_id;
		$db->setQuery($query);
		$ret = $db->execute();

		$app->triggerEvent('onAfterProductHit', array($product_id) );

		return $ret;
	}

	public function &getNameboxData(&$typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$app = JFactory::getApplication();

		$fullLoad = false;
		$tree_mode = (!isset($typeConfig['mode']) || $typeConfig['mode'] == 'tree');

		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$depth = (int)@$options['depth'];
		$start = (int)@$options['start'];
		$limit = (int)@$options['limit'];
		$unfold = (int)@$options['unfold'];
		$load_variants = (int)@$options['variants'];
		$is_root = false;
		$set_no_selection = empty($typeConfig['options']['onlyNode']);

		if(empty($start) && !empty($options['root'])) {
			$start = (int)$options['root'];
			$is_root = true;
		}

		if($load_variants) {
			$typeConfig['options']['tree_url'] .= '&variants='.$load_variants;
			$typeConfig['url'] .= '&variants='.$load_variants;
		}

		if($depth <= 0)
			$depth = 1;
		if($limit <= 0 && $tree_mode)
			$limit = 200;
		if($limit <= 0 && !$tree_mode)
			$limit = 20;
		if(!$tree_mode)
			$limit++;

		if(!empty($search)) {
			$searchStr = "'%" . ((HIKASHOP_J30) ? $this->db->escape($search, true) : $this->db->getEscaped($search, true) ) . "%'";
		}

		if($tree_mode) {
			if(empty($search)) {
				$query = 'SELECT c.*, 0 as `base_depth`' .
					' FROM ' . hikashop_table('category') . ' AS c ' .
					' WHERE c.category_type IN (\'product\',\'manufacturer\',\'vendor\',\'root\') AND c.category_depth >= 0 AND c.category_depth <= ' . $depth .
					' ORDER BY c.category_left ASC, c.category_name ASC';

				if($start > 0) {
					$query = 'SELECT c.*, basecat.category_depth as `base_depth`' .
						' FROM ' . hikashop_table('category') . ' AS c ' .
						' INNER JOIN ' . hikashop_table('category') . ' AS basecat ON c.category_left >= basecat.category_left AND c.category_right <= basecat.category_right'.
						' WHERE basecat.category_id = ' . $start . ' AND c.category_type IN (\'product\',\'manufacturer\',\'vendor\',\'root\') AND c.category_depth >= basecat.category_depth AND c.category_depth <= (basecat.category_depth + ' . $depth . ')'.
						' ORDER BY c.category_left ASC, c.category_name ASC';
				}
			} else {
				$query = 'SELECT c.*, 0 as `base_depth` '.
					' FROM ' . hikashop_table('category') . ' AS c ' .
					(($start > 0) ? ' INNER JOIN ' . hikashop_table('category') . ' AS b ON a.category_left >= b.category_left AND a.category_right <= b.category_right' : '') .
					' WHERE c.category_type IN (\'product\',\'manufacturer\',\'vendor\',\'root\') AND (( c.category_name LIKE ' . $searchStr .
					(($start > 0) ?  'AND b.category_id = ' . $start . ') OR ( c.category_id = ' . $start : '') . '))' .
					' ORDER BY c.category_left ASC, c.category_name ASC';
			}

			$this->db->setQuery($query);
			$category_elements = $this->db->loadObjectList('category_id');
			$categories = array();
			$base_depth = 0;
			$lookup_categories = array($start => $start);
		}

		if(!empty($category_elements) && empty($search)) {
			$base_depth = (int)@$category_elements[$start]->category_depth + $depth;

			foreach($category_elements as $k => $v) {
				if($k == $start && !$is_root)
					continue;

				$o = new stdClass();
				$o->status = 3;
				$o->name = hikashop_translate($v->category_name);
				$o->value = $k;
				$o->data = array();
				if($set_no_selection)
					$o->noselection = 1;

				if($depth > 1 && $v->category_depth < $base_depth) {
					$lookup_categories[$k] = $k;
					$o->status = $unfold ? 2: 1;
				}

				if(empty($v->category_parent_id) || $k == $start) {
					$o->status = 5;
					$o->icon = 'world';
					$ret[0][] =& $o;
				} else if((int)$v->category_parent_id == 1 || (int)$v->category_parent_id == $start || !isset($categories[(int)$v->category_parent_id])) {
					$ret[0][] =& $o;
				} else {
					$categories[(int)$v->category_parent_id]->data[] =& $o;
				}
				$categories[$k] =& $o;
				unset($o);
			}
		}

		$product_elements = array();
		if(!empty($lookup_categories) && empty($search)) {
			$query = 'SELECT p.*, c.category_id FROM ' . hikashop_table('product') . ' AS p '.
				' INNER JOIN ' . hikashop_table('product_category') . ' AS pc ON p.product_id = pc.product_id '.
				' INNER JOIN ' . hikashop_table('category') . ' AS c ON c.category_id = pc.category_id '.
				' WHERE pc.category_id IN (' . implode(',', $lookup_categories) . ') AND p.product_type != \'trash\'' .
				' ORDER BY c.category_left ASC, c.category_name ASC, p.product_name ASC';
			$this->db->setQuery($query, 0, $limit);
			$product_elements = $this->db->loadObjectList();

		} else if(!empty($search)) {

			$columns = array('product_code', 'product_name');

			$joins = array(
				'INNER JOIN ' . hikashop_table('product_category') . ' AS pc ON p.product_id = pc.product_id',
				'INNER JOIN ' . hikashop_table('category') . ' AS c ON c.category_id = pc.category_id',
			);
			if($load_variants) {
				$searchMap[] = 'v.product_code';
				$searchMap[] = 'v.product_name';
				$joins[] = 'LEFT JOIN ' . hikashop_table('product') . ' AS v ON p.product_id = v.product_parent_id';
			}

			if(hikashop_level(1)) {
				$cat = null;
				$fieldsClass = hikashop_get('class.field');
				$fields = $fieldsClass->getData('all', 'product', false, $cat);

				if(!empty($fields)) {
					foreach($fields as $field) {
						if($field->field_type == "customtext")
							continue;
						$columns[] = $field->field_namekey;
					}
				}
			}
			$searchMap = array();
			foreach($columns as $column) {
				$searchMap[] = 'p.'.$column;
				if($load_variants) {
					$searchMap[] = 'v.'.$column;
				}
			}
			$search_filter = '('.implode(" LIKE $searchStr OR ",$searchMap)." LIKE $searchStr".')';

			$query = 'SELECT p.*, c.category_id, c.category_right, c.category_left FROM ' . hikashop_table('product') . ' AS p '.
				implode(' ', $joins).
				' WHERE '.$search_filter.' AND p.product_type != \'trash\''.
				' ORDER BY p.product_name ASC';
			$this->db->setQuery($query, 0, $limit);
			$product_elements = $this->db->loadObjectList();

			$lookup_categories = array();
			foreach($category_elements as $c) {
				if(empty($lookup_categories[ (int)$c->category_id ]))
					$lookup_categories[ (int)$c->category_id ] = (int)$c->category_left . ' AND c.category_right >= ' . (int)$c->category_right;
			}
			foreach($product_elements as $p) {
				if(empty($lookup_categories[ (int)$p->category_id ]))
					$lookup_categories[ (int)$p->category_id ] = (int)$p->category_left . ' AND c.category_right >= ' . (int)$p->category_right;
				if(isset($category_elements[ (int)$p->category_id ]))
					$category_elements[ (int)$p->category_id ]->isproduct = true;
			}

			$base = '';
			if($start > 0)
				$base = '(c.category_left >= ' . (int)$category_elements[$start]->category_left . ' AND c.category_right <= ' . (int)$category_elements[$start]->category_right . ') AND ';

			if(!empty($lookup_categories))
				$base .=  '((c.category_left <= '.implode(') OR (c.category_left <= ', $lookup_categories) . '))';

			$category_tree = null;
			if(!empty($base)) {
				$query = 'SELECT c.* ' .
				' FROM ' . hikashop_table('category') . ' AS c ' .
				' WHERE ' . $base;
				$this->db->setQuery($query);
				$category_tree = $this->db->loadObjectList('category_id');
			}


			if(!empty($category_tree)) {
				foreach($category_tree as $k => $v) {
					if($k == $start && !$is_root)
						continue;

					$o = new stdClass();
					$o->status = 2;
					$o->name = hikashop_translate($v->category_name);
					$o->value = $k;
					$o->data = array();
					if($set_no_selection)
						$o->noselection = 1;

					if(isset($category_elements[$k]) && empty($category_elements[$k]->isproduct))
						$o->status = 3;

					if(empty($v->category_parent_id) || $k == $start) {
						$o->status = 5;
						$o->icon = 'world';
						$ret[0][] =& $o;
					} else if((int)$v->category_parent_id == 1 || (int)$v->category_parent_id == $start || !isset($categories[(int)$v->category_parent_id])) {
						$ret[0][] =& $o;
					} else {
						$categories[(int)$v->category_parent_id]->data[] =& $o;
					}
					$categories[$k] =& $o;
					unset($o);
				}
			}
		} else {
			$product_types = array();
			$product_type = array('main');
			if(!empty($typeConfig['params']['product_type']))
				$product_type = $typeConfig['params']['product_type'];
			if(!empty($options['product_type']))
				$product_type = $options['product_type'];
			if(is_string($product_type))
				$product_type = explode(',', $product_type);

			foreach($product_type as &$type) {
				$type = trim($type);
				$product_types[] = $this->db->Quote($type);
			}
			unset($type);

			$query = 'SELECT p.* FROM ' . hikashop_table('product') . ' AS p '.
				' WHERE p.product_type IN (' . implode(',', $product_types) . ' ) AND p.product_type != \'trash\''.
				' ORDER BY p.product_name ASC';
			$this->db->setQuery($query, 0, $limit);
			$product_elements = $this->db->loadObjectList('product_id');
		}

		foreach($product_elements as $k => $p) {
			if(!empty($p->product_name))
				$product_elements[$k]->product_name = hikashop_translate($p->product_name);
			if(!preg_match('!!u', $product_elements[$k]->product_name))
				$product_elements[$k]->product_name = htmlentities(utf8_encode($product_elements[$k]->product_name), ENT_QUOTES, "UTF-8");
			else
				$product_elements[$k]->product_name = htmlentities($product_elements[$k]->product_name, ENT_QUOTES, "UTF-8");
		}

		if(!empty($product_elements) && $tree_mode) {
			$displayFormat_tags = null;
			if(!preg_match_all('#{([-_a-zA-Z0-9]+)}#U', $displayFormat, $displayFormat_tags))
				$displayFormat_tags = null;

			$product_ids = array();
			if($load_variants) {
				foreach($product_elements as $p) {
					$product_ids[(int)$p->product_id] = array();
				}
				$query = 'SELECT p.* FROM ' . hikashop_table('product') . ' AS p '.
						' WHERE p.product_type = ' . $this->db->Quote('variant') . ' AND p.product_parent_id IN ('.implode(',', array_keys($product_ids)).')'.
						' ORDER BY p.product_parent_id ASC, p.product_name ASC';
				$this->db->setQuery($query);
				$variants = $this->db->loadObjectList('product_id');
				foreach($variants as $k => $v) {
					$product_ids[ (int)$v->product_parent_id ][] = (int)$v->product_id;
					if(!empty($v->product_name))
						$variants[$k]->product_name = hikashop_translate($v->product_name);
					$variants[$k]->product_name = htmlentities( ((!preg_match('!!u', $variants[$k]->product_name)) ? utf8_encode($variants[$k]->product_name) : $variants[$k]->product_name), ENT_QUOTES, "UTF-8");
				}

				$characteristics = $this->getCharacteristics(array_keys($product_ids), array_keys($variants));
				$characteristic_separator = JText::_('HIKA_VARIANT_SEPARATOR');
				if($characteristic_separator == 'HIKA_VARIANT_SEPARATOR')
					$characteristic_separator = ': ';
				$variant_separator = JText::_('HIKA_VARIANTS_MIDDLE_SEPARATOR');
				if($variant_separator == 'HIKA_VARIANTS_MIDDLE_SEPARATOR')
					$variant_separator = ' ';
			}

			$done = array();

			foreach($product_elements as $p) {
				$key = $p->category_id . '_'.$p->product_id;
				if(isset($done[$key]))
					continue;
				$done[$key] = $key;

				$o = new stdClass();
				$o->status = 0;
				$o->value = (int)$p->product_id;

				$product_name = $p->product_name;

				if(!empty($displayFormat) && !empty($displayFormat_tags)) {
					if($p->product_quantity == -1)
						$p->product_quantity = JText::_('UNLIMITED');
					$p->product_name = $product_name;
					$o->name = $displayFormat;

					foreach($displayFormat_tags[1] as $key) {
						$o->name = str_replace('{'.$key.'}', $p->$key, $o->name);
					}
				}
				if(empty($o->name)) {
					$o->name = $product_name;
					if(empty($o->name))
						$o->name = '['.$p->product_id.']';
				}

				if(!empty($product_ids[ (int)$p->product_id ])) {
					$o->status = 1;
					$o->icon = 'node';
					$o->data = array();

					if($load_variants == 2) {
						$o2 = new stdClass();
						$o2->status = 0;
						$o2->value = (int)$p->product_id;
						$o2->name = $o->name . ' ('.JText::_('HIKA_ALL_VARIANTS').')';
						$o->data[] =& $o2;
						unset($o2);
					}

					foreach($product_ids[ (int)$p->product_id ] as $id) {
						$o2 = new stdClass();
						$o2->status = 0;

						$o2->value = (int)$id;
						$v = $variants[$id];

						$variant_name = $v->product_name;
						if(empty($variant_name)) {
							$variant_name = $product_name . $characteristic_separator;
							foreach($characteristics[ (int)$p->product_id ] as $k => $c) {
								if($k > 0)
									$variant_name .= $variant_separator;
								$variant_name .= $characteristics[ (int)$v->product_id ][ (int)$c->characteristic_id ]->characteristic_value;
							}
						}
						if(!empty($displayFormat) && !empty($displayFormat_tags)) {
							if($v->product_quantity == -1)
								$v->product_quantity = JText::_('UNLIMITED');
							$v->product_name = $variant_name;
							$o2->name = $displayFormat;

							foreach($displayFormat_tags[1] as $key) {
								$o2->name = str_replace('{'.$key.'}', $v->$key, $o2->name);
							}
						}
						if(empty($o2->name)) {
							$o2->name = !empty($variant_name) ? $variant_name : $product_name;
							if(empty($o2->name))
								$o2->name = '['.$v->product_id.']';
						}

						$o->data[] =& $o2;

						unset($o2);
					}
				}

				if($p->category_id != $start && isset($categories[(int)$p->category_id]))
					$categories[(int)$p->category_id]->data[] =& $o;
				else
					$ret[0][] =& $o;
				unset($o);
			}
		} else if(!empty($product_elements)) {
			if(count($product_elements) < $limit) {
				$fullLoad = true;
			} else {
				array_pop($product_elements);
			}
			$ret[0] = $product_elements;
		}

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			if(is_object(reset($value))) {
				$values = array();
				foreach($value as $v) {
					$values[] = (int)$v->product_id;
				}
				$value = $values;
			}

			$filter = array();
			foreach($value as $v) {
				$filter[] = (int)$v;
			}
			$query = 'SELECT p.* '.
					' FROM ' . hikashop_table('product') . ' AS p ' .
					' WHERE p.product_id IN ('.implode(',', $filter).') AND p.product_type != \'trash\'';
			$this->db->setQuery($query);
			$products_data = $this->db->loadObjectList('product_id');
			foreach($products_data as $k => $p) {
				if(!empty($p->product_name))
					$products_data[$k]->product_name = hikashop_translate($p->product_name);
				$products_data[$k]->product_name = htmlentities( ((!preg_match('!!u', $products_data[$k]->product_name)) ? utf8_encode($products_data[$k]->product_name) : $products_data[$k]->product_name), ENT_QUOTES, "UTF-8");
			}

			$products = array();
			foreach($filter as $pid){
				if(isset($products_data[$pid]))
					$products[$pid] =& $products_data[$pid];
			}


			$variant_data = array(
				'product' => array(),
				'variant' => array()
			);
			if(!empty($products)) {
				foreach($products as $p) {
					if($p->product_type != 'variant' || !empty($p->product_name))
						continue;
					$variant_data['product'][ (int)$p->product_parent_id ] = (int)$p->product_parent_id;
					$variant_data['variant'][ (int)$p->product_id ] = (int)$p->product_id;
				}
			}

			if(!empty($variant_data['product'])) {
				$query = 'SELECT p.product_id, p.product_name FROM ' . hikashop_table('product') . ' AS p WHERE p.product_id IN (' . implode(',', $variant_data['product']) . ') AND p.product_type != \'trash\';';
				$this->db->setQuery($query);
				$parents = $this->db->loadObjectList('product_id');
				foreach($parents as $k => $p) {
					if(!empty($p->product_name))
						$parents[$k]->product_name = hikashop_translate($p->product_name);
					$parents[$k]->product_name = htmlentities( ((!preg_match('!!u', $parents[$k]->product_name)) ? utf8_encode($parents[$k]->product_name) : $parents[$k]->product_name), ENT_QUOTES, "UTF-8");
				}

				$characteristics = $this->getCharacteristics($variant_data['product'], $variant_data['variant']);

				$characteristic_separator = JText::_('HIKA_VARIANT_SEPARATOR');
				if($characteristic_separator == 'HIKA_VARIANT_SEPARATOR')
					$characteristic_separator = ': ';
				$variant_separator = JText::_('HIKA_VARIANTS_MIDDLE_SEPARATOR');
				if($variant_separator == 'HIKA_VARIANTS_MIDDLE_SEPARATOR')
					$variant_separator = ' ';

				foreach($products as &$p) {
					if($p->product_type != 'variant' || !empty($p->product_name))
						continue;

					$parent = isset($parents[ $p->product_parent_id ]) ? $parents[ $p->product_parent_id ] : $p;
					$product_name = $parent->product_name;

					$variant_name = $product_name . $characteristic_separator;
					foreach($characteristics[ (int)$p->product_parent_id ] as $k => $c) {
						if($k > 0)
							$variant_name .= $variant_separator;
						$variant_name .= $characteristics[ (int)$p->product_id ][ (int)$c->characteristic_id ]->characteristic_value;
					}
					$p->product_name_orig = $p->product_name;
					$p->product_name = $variant_name;
				}
				unset($p);
			}

			if(!empty($products)) {
				$ret[1] = array();
				foreach($value as $v) {
					$ret[1][(int)$v] = $products[(int)$v];
				}
			}

			if($mode == hikashopNameboxType::NAMEBOX_SINGLE)
				$ret[1] = reset($ret[1]);
		}

		return $ret;
	}

	protected function getCharacteristics($products, $variants) {
		if(!is_array($products))
			$products = array($products);
		if(!is_array($variants))
			$variants = array($variants);

		$ids = array_merge($products, $variants);

		$query = 'SELECT v.variant_product_id as product_id, c.* '.
				' FROM ' . hikashop_table('characteristic') . ' AS c ' .
				' INNER JOIN ' . hikashop_table('variant') . ' AS v ON v.variant_characteristic_id = c.characteristic_id ' .
				' WHERE v.variant_product_id IN ('.implode(',', $ids).') '.
				' ORDER BY v.ordering ASC, c.characteristic_ordering ASC';
		$this->db->setQuery($query);
		$characteristic_data = $this->db->loadObjectList();

		$characteristics = array();
		foreach($characteristic_data as $k => $v) {
			$p = (int)$v->product_id;
			if(!isset($characteristics[ $p ]))
				$characteristics[ $p ] = array();
			if(in_array($p, $products)) { // isset($product_ids[ $p ])) {
				if((int)$v->characteristic_parent_id > 0)
					continue;
				$characteristics[ $p ][] = $v;
			} else {
				$characteristics[ $p ][ (int)$v->characteristic_parent_id ] = $v;
			}
		}
		unset($characteristic_data);

		return $characteristics;
	}
}
