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
class OrderController extends hikashopController {
	var $type = 'order';

	var $subtasks = array(
		'customer',
		'billing_address',
		'shipping_address',
		'products',
		'additional',
		'general',
		'history',
		'user',
		'guest'
	);

	var $popupSubtasks = array(
		'additional',
		'products'
	);

	function __construct($config = array()) {
		parent::__construct($config);
		$this->modify_views = array_merge($this->modify_views, array(
			'changestatus','product','product_select','product_add','product_delete','address','state',
			'mail','partner','discount','fields','changeplugin','neworder','user','form','batch',

			'product_create','customer_set','customer_save', 'add_guest'
		));
		$this->display = array_merge($this->display, array(
			'invoice','address','export','download','remove_history_data', 'findList'
		));
		$this->modify = array_merge($this->modify, array(
			'savechangestatus','saveproduct','saveproduct_delete','copy',
			'saveaddress','savemail','savechangeplugin','savediscount',
			'savepartner','savefields','saveuser','deleteentry','product_remove',
		));
	}


	function add_guest(){
		hikaInput::get()->set('layout', 'add_guest');
		return parent::display();
	}


	function form(){
		$this->neworder();
	}

	function neworder(){
		$null = new stdClass();
		$orderClass = hikashop_get('class.order');
		$orderClass->sendEmailAfterOrderCreation = false;
		if($orderClass->save($null)){
			$this->_terminate($null,1);
		}else{
			$this->listing();
		}
	}

	public function batch(){
		$params = new HikaParameter('');
		$params->set('table', 'order');
		$js = '';
		echo hikashop_getLayout('massaction', 'batch', $params, $js);
	}

	function download(){
		$file_id = hikaInput::get()->getInt('file_id');
		if(empty($file_id)){
			$field_table = hikaInput::get()->getWord('field_table');
			$field_namekey = hikaInput::get()->getString('field_namekey');
			$name = hikaInput::get()->getString('name');
			if(empty($field_table)||empty($field_namekey)||empty($name)){
				$app=JFactory::getApplication();
				$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
				return false;
			}else{
				$options = array();
				if(isset($_REQUEST['thumbnail_x']) || isset($_REQUEST['thumbnail_y'])) {
					$options = array(
						'thumbnail_x' => hikaInput::get()->getInt('thumbnail_x', 0),
						'thumbnail_y' => hikaInput::get()->getInt('thumbnail_y', 0)
					);
				}
				$fileClass = hikashop_get('class.file');
				$fileClass->downloadFieldFile(urldecode(base64_decode($name)), $field_table, urldecode(base64_decode($field_namekey)), $options);
			}

		}
		$file_pos = hikaInput::get()->getInt('file_pos',1);
		$order_id = hikaInput::get()->getInt('order_id',0);
		$fileClass = hikashop_get('class.file');
		$fileClass->download($file_id,$order_id,$file_pos);
	}

	function getUploadSetting($upload_key, $caller = '') {
		if(empty($upload_key))
			return false;
		if(strpos($upload_key, '-') === false)
			return false;

		list($field_table, $field_namekey) = explode('-', $upload_key);

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($field_namekey, $field_table);

		if(empty($field) || !in_array($field->field_type, array('ajaxfile', 'ajaximage')))
			return false;

		$map = hikaInput::get()->getString('field_map', '');
		if(empty($map))
			return false;

		$config = hikashop_config();
		$options = array(
			'upload_dir' => $config->get('uploadsecurefolder')
		);

		if(!empty($field->field_options['upload_dir']))
			$options['upload_dir'] = $field->field_options['upload_dir'];
		if(!empty($field->field_options['allowed_extensions']))
			$options['allowed_extensions'] = trim($field->field_options['allowed_extensions'], ', ');

		$type = ($field->field_type == 'ajaxfile') ? 'file' : 'image';

		return array(
			'limit' => 1,
			'type' => $type,
			'options' => $options,
			'extra' => array(
				'field_name' => $map,
				'delete' => empty($field->field_required),
				'uploader_id' => hikaInput::get()->getString('uploader_id', '')
			)
		);
	}

	function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret) || empty($ret->name))
			return;

		if(empty($upload_key))
			return;
		if(strpos($upload_key, '-') === false)
			return;

		list($field_table, $field_namekey) = explode('-', $upload_key);

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($field_namekey, $field_table);

		if(empty($field) || ($field->field_type != 'ajaxfile' && $field->field_type != 'ajaximage'))
			return;

		$map = hikaInput::get()->getString('field_map', '');
		if(empty($map))
			return;

		if($field->field_type == 'ajaxfile')
			$ajaxFileClass = new hikashopFieldAjaxfile($fieldClass);
		else
			$ajaxFileClass = new hikashopFieldAjaximage($fieldClass);
		$ajaxFileClass->_manageUpload($field, $ret, $map, $uploadConfig, $caller);
	}

	function changestatus(){
		hikaInput::get()->set('layout', 'changestatus');
		return parent::display();
	}

	function product(){
		hikaInput::get()->set('layout', 'product');
		return parent::display();
	}
	function user(){
		hikaInput::get()->set('layout', 'user');
		hikaInput::get()->set('cart_id',hikaInput::get()->getString('cart_id','0'));
		hikaInput::get()->set('cart_type',hikaInput::get()->getString('cart_type','0'));
		return parent::display();
	}
	function product_select(){
		hikaInput::get()->set( 'layout', 'product_select'  );
		$cart_type = hikaInput::get()->getString('cart_type','cart');
		hikaInput::get()->set('cart_type',$cart_type);
		hikaInput::get()->set($cart_type.'_id', hikaInput::get()->getInt('cart_id', '0'));
		return parent::display();
	}
	function product_add($order_id = 0){
		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$classOrder = hikashop_get('class.order');
		if($order_id == 0){
			$data = $this->_cleanOrder();
			$product_ids = hikaInput::get()->get('cid', array(), 'array');
		}else{
			$data = new stdClass();
			$data->order_id = $order_id;
			$product_ids = hikaInput::get()->get('product_ids', array(), 'array');
		}

		$quantities = hikaInput::get()->get('quantity', array(), 'array');
		$rows = array();
		if(!empty($product_ids)){
			hikashop_toInteger($product_ids);
			$database	= JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$product_ids).')';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
		}
		$user_id = 0;
		$main_currency = (int)$config->get('main_currency',1);
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		if(!empty($data->order_id)){
			$orderData = $classOrder->get($data->order_id);
			$currency_id = $orderData->order_currency_id;
			$user_id = $orderData->order_user_id;
		}else{
			$currency_id = hikashop_getCurrency();
		}

		$zone_id = hikashop_getZone(null);
		$currencyClass->getPrices($rows, $product_ids, $currency_id, $main_currency, $zone_id, $discount_before_tax, $user_id);

		$element = array();
		if(!empty($rows)){
			foreach($rows as $k => $row){
				$obj = new stdClass();
				$obj->order_product_name = $row->product_name;
				$obj->order_product_code = $row->product_code;
				$obj->order_product_weight = $row->product_weight;
				$obj->order_product_weight_unit = $row->product_weight_unit;
				$obj->order_product_width = $row->product_width;
				$obj->order_product_height = $row->product_height;
				$obj->order_product_length = $row->product_length;
				$obj->order_product_dimension_unit = $row->product_dimension_unit;
				$obj->order_product_quantity = (!empty($quantities[$row->product_id]) ? $quantities[$row->product_id]:1 );
				$currencyClass->pricesSelection($row->prices,$obj->order_product_quantity);
				$obj->product_id = $row->product_id;
				$obj->order_id = (int)$data->order_id;
				if(!empty($row->prices)){
					foreach($row->prices as $price){
						$obj->order_product_price = $price->price_value;
						$obj->order_product_tax = ($price->price_value_with_tax-$price->price_value);
						$obj->order_product_tax_info = $price->taxes;
					}
				}
				$element[$k]=$obj;
			}
		}

		$result = false;
		$cart_type = hikaInput::get()->getString('cart_type','cart');
		$cart_id = hikaInput::get()->getString($cart_type.'_id','0');
		if(!empty($data->order_id)){
			$data->product = $element;
			$classOrder = hikashop_get('class.order');
			$classOrder->recalculateFullPrice($data);
			$result = $classOrder->save($data);
		}else{ //cart type
			$classCart = hikashop_get('class.cart');
			if($cart_id == '0'){
				$cart = new stdClass();
				$cart->cart_type = $cart_type;
				$cart_id = $classCart->save($cart);
			}

			hikaInput::get()->set('cart_type',$cart_type);
			hikaInput::get()->set($cart_type.'_id',$cart_id);

			$result = true;
			foreach($element as $data){
				if(!$classCart->update((int)$data->product_id, $data->order_product_quantity,1,'product',false,true,$cart_id)){
					$result=false;
				}
			}

			if($result)
				$this->_terminate($cart,'showcart');
			else
				$this->product_select();
		}
		if($result && $order_id == 0){
			$this->_terminate($data,1);
		}else{
			return true;
		}
	}
	function address(){
		hikaInput::get()->set('layout', 'address');
		return parent::display();
	}
	function invoice(){
		hikaInput::get()->set('layout', 'invoice');
		return parent::display();
	}
	function export(){
		hikaInput::get()->set('layout', 'export');
		return parent::display();
	}
	function discount(){
		hikaInput::get()->set('layout', 'discount');
		return parent::display();
	}
	function fields(){
		hikaInput::get()->set('layout', 'fields');
		return parent::display();
	}
	function savefields(){
		$this->_save(1,'fields');
	}
	function savediscount(){
		$this->_save();
	}
	function partner(){
		hikaInput::get()->set('layout', 'partner');
		return parent::display();
	}
	function savepartner(){
		$this->_save();
	}
	function saveuser(){
		$set_address = hikaInput::get()->getInt('set_address', 0);
		if($set_address) {
			$formData = hikaInput::get()->get('data', array(), 'array');
			if(isset($formData['order']['order_user_id'])) {
				$user_id = $formData['order']['order_user_id'];
				$db = JFactory::getDBO();
				if(hikaInput::get()->getString('cart_id','0') != '0'){
					$userClass = hikashop_get('class.user');
					$user = $userClass->get($user_id);
					$user_id = $user->user_cms_id;

					$query = 'UPDATE '.hikashop_table('cart').' SET user_id = '.$user_id.' WHERE cart_id = '.hikaInput::get()->getString('cart_id','0');
					$db->setQuery($query);
					$db->execute();
					hikaInput::get()->set('user_id', $user_id);
					$element = new stdClass();
					$element->user_id = $user_id;
					$element->cart_id = hikaInput::get()->getString('cart_id','0');
					$element->cart_type = hikaInput::get()->getString('cart_type','cart');
					$this->_terminate($element,'showcart');
				}else{
					$db->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_user_id = '. (int)$user_id . ' AND address_published = 1 ORDER BY address_default DESC, address_id ASC LIMIT 1');
					$address_id = $db->loadResult();
					if($address_id) {
						$formData['order']['order_billing_address_id'] = $address_id;
						hikaInput::get()->set('data', $formData);
					}
				}
			}
		}
		$this->_save();
	}
	function mail(){
		hikaInput::get()->set( 'layout', 'mail'  );
		return parent::display();
	}
	function changeplugin(){
		hikaInput::get()->set( 'layout', 'changeplugin'  );
		return parent::display();
	}
	function savechangeplugin(){
		$this->_save();
	}

	function savemail(){
		$element = $this->_cleanOrder();
		if(!empty($element->mail)){
			$mailClass = hikashop_get('class.mail');
			$mailClass->sendMail($element->mail);
			if(!$mailClass->mail_success){
				return true;
			}
		}
		$this->_terminate($element,2);
	}

	function saveproduct(){
		if(hikaInput::get()->getInt('cart_id','0') != '0'){ //Check the quantity too ?
			$cart_id = hikaInput::get()->getString('cart_id','0');
			$cart_type = hikaInput::get()->getString('cart_type','cart');
			hikaInput::get()->set('cart_id',$cart_id);
			hikaInput::get()->set('cart_type',$cart_type);
			$classCart = hikashop_get('class.cart');
			$classCart->update(hikaInput::get()->getInt('product_id','0'), 0,0,'product',true,true);
			$element = new stdClass();
			$element->cart_type = $cart_type;
			$element->cart_id = $cart_id;
			$this->_terminate($element,'showcart');
		}
		$this->_save();
	}

	function saveaddress(){
		$result = false;
		$addressClass = hikashop_get('class.address');
		$oldData = null;

		if(!empty($_REQUEST['address']['address_id'])){
			$oldData = $addressClass->get($_REQUEST['address']['address_id']);
		}

		$fieldClass = hikashop_get('class.field');
		$address = $fieldClass->getInput('address',$oldData);
		if(empty($address)){
			return false;
		}
		$element = $this->_cleanOrder();

		if(!empty($element->order_id)){
			$type = hikaInput::get()->getCmd('type');
			$result = $addressClass->save($address,$element->order_id,$type);
			if($result){
				$name = 'order_'.$type.'_address_id';
				$element->$name = $result;
				$orderClass = hikashop_get('class.order');
				$result = $orderClass->save($element);
				if($result){
					$this->_terminate($element);
				}
			}
		}
	}

	function remove_history_data(){
		$history_id = hikaInput::get()->getInt( 'history_id', 0);
		if($history_id){
			$historyClass = hikashop_get('class.history');
			$history = $historyClass->get($history_id);
			if($history){
				$newHistoryObj = new stdClass();
				$newHistoryObj->history_id = $history_id;
				$newHistoryObj->history_data = '';
				$historyClass->save($newHistoryObj);
			}
			hikaInput::get()->set( 'order_id', $history->history_order_id );
			return $this->edit();
		}else{
			return $this->listing();
		}
	}

	function product_delete(){
		hikaInput::get()->set( 'layout', 'product_delete'  );
		hikaInput::get()->set( 'cart_id', hikaInput::get()->getInt('cart_id','0')  );
		hikaInput::get()->set( 'product_id',hikaInput::get()->getInt('product_id','0')  );
		hikaInput::get()->set( 'cart_type',hikaInput::get()->getString('cart_type','cart')  );

		return parent::display();
	}

	function savechangestatus(){
		$this->_save(hikaInput::get()->getInt('edit', 0));
	}
	function _cleanOrder(){
		$element = new stdClass();
		$formData = hikaInput::get()->get('data', array(), 'array');
		$fieldClass = hikashop_get('class.field');
		$old = null; //$fieldsClass->get($formData['order']['product']['order_product_id']);

		foreach($formData['order'] as $column => $value){
			hikashop_secureField($column);
			if($column == 'product') {
				$formData['item'] = $formData['order']['product'];
				hikaInput::get()->set('data', $formData);
				$fieldClass->getInput('item',$old,false);
				$element->product = $_SESSION['hikashop_item_data'];
			} elseif(in_array($column,array('history','mail'))){
				$element->$column = new stdClass();
				foreach($value as $k => $v){
					$k = hikashop_secureField($k);
					$element->$column->$k = strip_tags($v);
				}
			}else{
				if(is_array($value)){
					$value = implode(',',$value);
				}
				$element->$column = strip_tags($value);
			}
		}
		if(!isset($element->mail))
			$element->mail = new stdClass();
		$element->mail->body = hikaInput::get()->getRaw('hikashop_mail_body', '');
		$element->mail->data = new stdClass();
		if(!empty($element->order_id))
			$element->mail->data->order_id = (int)$element->order_id;
		return $element;
	}

	function _save($type=1,$data=''){
		$element = $this->_cleanOrder();

		$result = false;
		$app = JFactory::getApplication();
		if(!empty($element->order_id)){
			$order_id = $element->order_id;
			$orderClass = hikashop_get('class.order');
			if($data == 'fields'){
				$fieldClass = hikashop_get('class.field');
				$old = $orderClass->get($element->order_id);
				$element = $fieldClass->getInput('order',$old,false);
				if($element === false) {
					$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('REQUIRED')), 'error');
				} else if(empty($element)) {
					$app->enqueueMessage(JText::_('ERROR_SAVING'), 'error');
				} else {
					$element->mail->body = hikaInput::get()->getRaw('hikashop_mail_body', '');
				}
			}

			if(!empty($element)) {
				$result = $orderClass->save($element);
			}
		}
		if($result && $orderClass->mail_success){
			$this->_terminate($element,$type);
		}

	}

	function deleteentry(){
		$entry = hikaInput::get()->getInt('entry_id',0);
		if($entry){
			$entryClass = hikashop_get('class.entry');
			$oldData = $entryClass->get($entry);
			if(!empty($oldData)){
				$entryClass->delete($entry);
				hikaInput::get()->set('cid',$oldData->order_id);
			}
		}
		$this->edit();
	}

	function _terminate(&$element,$type=1){
		$js = '';
		if($type == 2){
			$js = 'parent.hikashop.closeBox();';
		}elseif($type === 'showcart'){
			if($element != null){
				$js = 'parent.window.location.href=\''.hikashop_completeLink('cart&task=edit&cart_type='.$element->cart_type.'&cid[]='.@$element->cart_id,false,true).'\';';
			}else{
				$js = 'parent.window.location.reload();';
			}
		}elseif($type){
			$js = 'parent.window.location.href=\''.hikashop_completeLink('order&task=edit&cid[]='.@$element->order_id,false,true).'\';';
		}
		else{
			$js = 'parent.document.getElementById(\'filter_status_'.@$element->order_id.'\').value=\''.@$element->order_status.'\';parent.default_filter_status_'.@$element->order_id.'=\''.@$element->order_status.'\';if(typeof(parent.jQuery)!=\'undefined\'){parent.jQuery(parent.document.getElementById(\'filter_status_'.@$element->order_id.'\')).trigger(\'liszt:updated\');} window.parent.hikashop.closeBox();';
		}
		if(!headers_sent()){
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );
		}
		echo '<html><head><script type="text/javascript">'.$js.'</script></head><body></body></html>';
		exit;
	}

	public function copy(){
		$orders = hikaInput::get()->get('cid', array(), 'array');
		$result = true;
		if(!empty($orders)){
			$orderClass = hikashop_get('class.order');
			foreach($orders as $order){
				if(!$orderClass->copyOrder($order)){
					$result=false;
				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
		}
		return $this->listing();
	}

	public function show() {
		$task = hikaInput::get()->getVar('subtask', '');
		if(!empty($task) && !in_array($task, $this->subtasks))
			return false;

		if(empty($task))
			hikaInput::get()->set('layout', 'show');
		else
			hikaInput::get()->set('layout', 'show_'.$task);

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component') {
			ob_end_clean();
			hikashop_nocache();
			parent::display();
			exit;
		}
		return parent::display();
	}

	public function save() {
		$task = hikaInput::get()->getVar('subtask', '');
		if(!in_array($task, $this->subtasks))
			return false;

		$orderClass = hikashop_get('class.order');
		if( $orderClass === null )
			return false;
		$status = $orderClass->saveForm($task);
		if($status) {
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		}

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component') {
			if(hikaInput::get()->get('fail', null)){
				hikaInput::get()->set('task', 'edit');
				return $this->edit();
			}else{
				return $this->show();
			}
		}
		return $this->listing();
	}

	private function show_products() {
		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component') {
			hikaInput::get()->set('layout', 'show_products');
			ob_end_clean();
			hikashop_nocache();
			parent::display();
			exit;
		}
		hikaInput::get()->set('layout', 'show');
		return parent::display();
	}

	public function edit() {
		$task = hikaInput::get()->getVar('subtask', '');
		if(empty($task)) {
			$config = hikashop_config();
			if($config->get('fallback_order_edition', 0) || hikaInput::get()->getVar('fallback', 0))
				return parent::edit();

			hikaInput::get()->set('task', 'show');
			return $this->show();
		}

		if(!in_array($task, $this->subtasks)) {
			$tmpl = hikaInput::get()->getVar('tmpl', '');
			if($tmpl == 'component') {
				exit;
			}
			return false;
		}
		hikaInput::get()->set('layout', 'show_'.$task);

		if(!in_array($task , $this->popupSubtasks)) {
			$tmpl = hikaInput::get()->getVar('tmpl', '');
			if($tmpl == 'component') {
				ob_end_clean();
				$app = JFactory::getApplication();
				$messageQueue = $app->getMessageQueue();
				if(!empty($messageQueue)) {
					foreach( $messageQueue as $message) {
						hikashop_display($message['message'], $message['type']);
					}
				}
				parent::display();
				exit;
			}
		} else {
			hikaInput::get()->set('layout', 'edit_'.$task);
		}
		return parent::display();
	}

	public function customer_save() {
		$orderClass = hikashop_get('class.order');
		if( $orderClass === null )
			return false;
		$status = $orderClass->saveForm('customer');
		if($status) {
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		}

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component') {
			ob_end_clean();
			hikaInput::get()->set('layout', 'customer_set');
			return parent::display();
		}
		return $this->show();
	}

	public function customer_set() {
		hikaInput::get()->set('layout', 'customer_set');
		return parent::display();
	}

	public function product_create() {
		$formData = hikaInput::get()->get('data', array(), 'array');
		$product_quantity = -1;
		if(isset($formData['order']['product']['order_product_quantity']))
			$product_quantity = (int)$formData['order']['product']['order_product_quantity'];
		if($product_quantity >= 0) {
			$orderClass = hikashop_get('class.order');
			$status = $orderClass->saveForm('products');
			if($status) {
				hikaInput::get()->set('cid', $status);
				hikaInput::get()->set('fail', null);
			}
		} else {
			hikaInput::get()->set('layout', 'edit_products');
			return parent::display();
		}

		return $this->show_products();
	}

	public function product_remove() {
		$orderClass = hikashop_get('class.order');
		if( $orderClass === null )
			return false;
		$status = $orderClass->saveForm('product_delete');
		if($status) {
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		}

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component')
			return $this->show_products();
		return $this->show();
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');

		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['page'] = $start;

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, 'order', $options);
		echo json_encode($elements);
		exit;
	}
}
