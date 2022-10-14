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
class orderMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('listing','show','invoice','mail','previewmail','status','history','showblock','request','create','payments'),
		'add' => array('add'),
		'edit' => array('edit', 'edit_additional', 'product_add', 'product_delete', 'customer_set', 'export', 'product_data'),
		'modify' => array('apply','save','customer_save','sendmail','submitblock'),
		'delete' => array(),
	);

	protected $subtasks = array(
		'customer',
		'billing_address',
		'shipping_address',
		'products',
		'additional',
		'general',
		'history'
	);

	protected $popupSubtasks = array(
		'additional',
		'products'
	);

	protected $config = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('listing');
		$this->config = hikamarket::config();
	}

	protected function checks($order_id = null) {
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('frontend_edition', 0) )
			return false;

		if($order_id !== null && !hikamarket::isVendorOrder($order_id))
			return false;

		return true;
	}

	public function listing() {
		if(!$this->checks())
			return false;
		if(!hikamarket::acl('order/listing'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_LISTING')));
		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function payments() {
		if(!$this->checks())
			return false;
		if(!hikamarket::acl('order/payments'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PAYMENTS_LISTING')));
		hikaInput::get()->set('layout', 'payments');
		return parent::display();
	}

	public function save() {
		$status = $this->store();
		$tmpl = hikaInput::get()->getVar('tmpl', '');

		if($tmpl == 'component' && hikaInput::get()->getInt('closepopup', 0)) {
			if(empty($status)) {
				return '';
			}

			$orderClass = hikamarket::get('class.order');
			if(is_int($status)) {
				$order = $orderClass->getRaw((int)$status);
			} else {
				$order = $status;
			}

			$events = $orderClass->getEvents();
			$extra_js = array();
			if(!empty($events)) {
				foreach($events as $k => $v) {
					$extra_js[] = 'window.parent.Oby.fireAjax("'.$k.'", '.json_encode($v).');';
				}
			}

			ob_end_clean();
			echo '<html><body>'.
				'<script type="text/javascript">'."\r\n".
				'window.parent.hikamarket.submitBox('.json_encode(array(
					'id' => (int)$order->order_id,
					'order_status' => $order->order_status,
					'name' => hikamarket::orderStatus($order->order_status)
				)).');'."\r\n" . implode("\r\n", $extra_js) .
				'</script>'."\r\n".
				'</body></html>';
			exit;
		}

		if($tmpl == 'component')
			return $this->show();
		return $this->listing();
	}

	public function add() {
		if(!$this->checks())
			return false;
		if( !hikamarket::acl('order/add') )
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		$app = JFactory::getApplication();

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(empty($formData['order']) || empty($formData['order']['order_user_id'])) {
			$app->redirect( hikamarket::completeLink('order&task=create') );
			return;
		}

		$orderClass = hikamarket::get('class.order');
		$order = $orderClass->saveCreateForm();

		if(!empty($order)) {
			$app->redirect( hikamarket::completeLink('order&task=show&cid=' . $order->order_id ) );
		} else {
			$app->enqueueMessage(JText::_('HIKAM_ERR_ORDER_CREATION'), 'error');
			$app->redirect( hikamarket::completeLink('order&task=create') );
		}
	}

	public function store() {
		$order_id = hikamarket::getCID('order_id');
		if(!$this->checks($order_id))
			return false;

		$orderClass = hikamarket::get('class.order');
		if( $orderClass === null )
			return false;

		$task = hikaInput::get()->getVar('subtask', null);
		if($task !== null) {
			if(!in_array($task, $this->subtasks))
				return false;
			if(!hikamarket::acl('order/edit/'.$task))
				return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

			$status = $orderClass->frontSaveFormLegacy($task);
			if($status) {
				hikaInput::get()->set('cid', $status);
				hikaInput::get()->set('fail', null);
			}
			return $status;
		}

		if(!hikamarket::acl('order/edit'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		$status = $orderClass->frontSaveForm();
		return $status;
	}

	public function show() {
		$order_id = hikamarket::getCID('order_id');
		if(!$this->checks($order_id))
			return false;

		$task = hikaInput::get()->getVar('subtask', '');
		if(!empty($task) && !in_array($task, $this->subtasks))
			return false;
		if(!hikamarket::acl('order/show'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_SHOW')));
		$vendor_id = hikamarket::loadVendor(false);

		hikaInput::get()->set('layout', 'show');
		if($vendor_id > 1 && $this->config->get('order_vendor_edition_legacy', 0))
			hikaInput::get()->set('layout', 'show_vendor');
		else if(!empty($task))
			hikaInput::get()->set('layout', 'show_'.$task);

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component') {
			ob_end_clean();
			parent::display();
			exit;
		}
		return parent::display();
	}

	public function create() {
		if(!$this->checks())
			return false;
		if(!hikamarket::acl('order/add'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_SHOW')));

		$vendor_id = hikamarket::loadVendor(false);
		if($vendor_id > 1 && !$this->config->get('vendor_edit_order', 0))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_SHOW')));

		hikaInput::get()->set('layout', 'create');
		return parent::display();
	}

	public function status() {
		$order_id = hikamarket::getCID('order_id');
		if(!$this->checks($order_id))
			return false;
		if(!hikamarket::acl('order/edit/general'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_MAIL')));

		hikaInput::get()->set('layout', 'status');
		return parent::display();
	}

	public function invoice() {
		$order_id = hikamarket::getCID('order_id');
		if(!$this->checks($order_id))
			return false;

		if(!hikamarket::acl('order/show'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_SHOW')));

		hikaInput::get()->set('layout', 'invoice');
		return parent::display();
	}

	public function export() {
		if(!$this->checks())
			return false;
		if(!hikamarket::acl('order/export'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EXPORT')));

		hikaInput::get()->set('layout', 'export_show');

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!empty($formData)) {
			if(!JSession::checkToken()) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('INVALID_TOKEN'), 'error');
			} else {
				hikaInput::get()->set('layout', 'export');
			}
		}
		return parent::display();
	}

	public function history() {
		$order_id = hikaInput::get()->getInt('order_id', 0);
		if(!$this->checks($order_id))
			return false;

		if(!hikamarket::acl('order/show/historydata'))
			return false;

		hikaInput::get()->set('layout', 'history');
		return parent::display();
	}

	public function showblock() {
		hikamarket::headerNoCache();
		$order_id = hikamarket::getCID('order_id');
		if(empty($order_id) || !$this->checks($order_id))
			return false;

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		hikaInput::get()->set('layout', 'showblock');
		if($tmpl == 'component' || $tmpl == 'ajax') {
			ob_end_clean();
			parent::display();
			exit;
		}
		return $this->display();
	}

	public function submitblock() {
		JSession::checkToken('request') || jexit('Invalid Token');
		$tmpl = hikaInput::get()->getCmd('tmpl', '');

		$order_id = hikamarket::getCID('order_id', 0);
		if(empty($order_id) || !$this->checks($order_id))
			return false;

		$orderClass = hikamarket::get('class.order');
		if( $orderClass === null )
			return false;
		$updateOrder = $orderClass->frontSaveForm();

		if($updateOrder === false) {
			ob_end_clean();
			echo '0';
			if($tmpl == 'component' || $tmpl == 'ajax')
				exit;
			return false;
		}

		$block = hikaInput::get()->getCmd('block', '');
		if(in_array($block, array('delete_product'))) {
			ob_end_clean();
			echo '1';
			exit;
		}

		if($block == 'product' && $tmpl == 'ajax') {
			$pid = hikaInput::get()->getInt('pid', 0);
			if($pid === 0 && count($updateOrder->product) == 1) {
				$p = reset($updateOrder->product);
				$pid = (int)$p->order_product_id;
				hikaInput::get()->set('pid', $pid);
			}
		}

		hikaInput::get()->set('blocksubmitted', 1);
		return $this->showblock();
	}

	public function product_data() {
		JSession::checkToken('request') || jexit('Invalid Token');

		$order_id = hikamarket::getCID('order_id');
		if(empty($order_id) || !$this->checks($order_id) || !hikamarket::acl('order/edit/products'))
			return false;

		$shopConfig = hikamarket::config(false);
		$productClass = hikamarket::get('class.product');
		$orderClass = hikamarket::get('class.order');
		$addressClass = hikamarket::get('shop.class.address');
		$zoneClass = hikamarket::get('shop.class.zone');
		$product_id = hikaInput::get()->getInt('product', 0);
		$qty = hikaInput::get()->getInt('qty', 0);

		$order_product_id = 0;

		$data = array();
		if($qty <= 0)
			$qty = 1;

		$order = $orderClass->getRaw($order_id);

		if($shopConfig->get('tax_zone_type', 'shipping') == 'billing')
			$order_address = $addressClass->get((int)$order->order_billing_address_id);
		else
			$order_address = $addressClass->get((int)$order->order_shipping_address_id);
		$address_base = !empty($order_address->address_state) ? $order_address->address_state : $order_address->address_country;
		$zone = $zoneClass->get($address_base);

		$product = $productClass->getProduct($product_id, array(
			'price' => array(
				'currency' => (int)$order->order_currency_id,
				'qty' => (int)$qty,
				'user' => (int)$order->order_user_id,
				'zone' => (int)$zone->zone_id
			)
		));

		if(empty($product)) {
			ob_end_clean();
			echo json_encode($data);
			exit;
		}

		$p = end($product->prices);
		$data = array(
			'name' => $product->product_name,
			'code' => $product->product_code,
			'tax' => isset($p->taxes[0]) ? $p->taxes[0]->tax_namekey : '',
			'price' => isset($p->price_value_with_tax) ? $p->price_value_with_tax : $p->price_value
		);

		$vendor_id = hikamarket::loadVendor(false);
		if($vendor_id > 1 && !empty($product->product_vendor_id) && (int)$product->product_vendor_id != $vendor_id) {
			ob_end_clean();
			echo '{}';
			exit;
		}

		if($vendor_id <= 1 && !empty($product->product_vendor_id)) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendorObj = $vendorClass->get((int)$product->product_vendor_id);
			if(!empty($vendorObj)) {
				$data['vendor'] = array(
					'id' => (int)$vendorObj->vendor_id,
					'name' => $vendorObj->vendor_name
				);
				$product->product_vendor_id = $vendorObj->vendor_id;
			} else
				$product->product_vendor_id = null;
		}

		if(!empty($product->product_vendor_id)) {
			$p->price_value = (float)hikamarket::toFloat($p->price_value);
			$p->price_value_with_tax = (float)hikamarket::toFloat($p->price_value_with_tax);

			$vendor_ids = array((int)$product->product_vendor_id => (int)$product->product_vendor_id);
			$products = array(
				0 => array(
					'_id' => 0,
					'id' => (int)$product->product_id,
					'vendor' => (int)$product->product_vendor_id,
					'fee' => array(),
					'qty' => (int)$qty,
					'price' => $p->price_value,
					'price_tax' => $p->price_value_with_tax - $p->price_value
				)
			);

			$config = hikamarket::config();
			if($config->get('calculate_vendor_price_with_tax', false))
				$full_price = (float)($products[0]['price'] + $products[0]['price_tax']) * (int)$products[0]['qty'];
			else
				$full_price = (float)$products[0]['price'] * (int)$products[0]['qty'];

			$feeClass = hikamarket::get('class.fee');
			$allFees = $feeClass->getProducts($products, $vendor_ids);

			$orderProduct = new stdClass();
			$orderProduct->order_product_quantity = (int)$qty;
			$orderProduct->order_product_price = isset($p->price_value_with_tax) ? $p->price_value_with_tax : $p->price_value;

			if($config->get('calculate_vendor_price_with_tax', false))
				$orderProduct->order_product_vendor_price = $p->price_value_with_tax;
			else
				$orderProduct->order_product_vendor_price = $p->price_value;

			$product_fee = $orderClass->getProductFee($orderProduct, $products[0]['fee'], $full_price, $order->order_full_price, $products[0]['qty']);

			$data['vendorprice'] = $product_fee['vendor'];
		}

		ob_end_clean();
		echo json_encode($data);
		exit;
	}

	public function request() {
		if(!$this->checks())
			return false;
		if(!hikamarket::acl('order/request'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_REQUEST')));

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!empty($formData)) {
			$app = JFactory::getApplication();

			if(!JSession::checkToken()) {
				$app->enqueueMessage(JText::_('INVALID_TOKEN'), 'error');
			} else {
				$vendor = hikamarket::loadVendor(true);

				$mailClass = hikamarket::get('class.mail');
				$infos = new stdClass;
				$infos->vendor = hikamarket::loadVendor(true);
				$infos->user = hikamarket::loadUser(true);
				$mail = $mailClass->load('vendor_payment_request', $infos);

				if(!empty($mail) && $mail->published) {
					$shopConfig = hikamarket::config(false);

					if(!empty($mail->subject))
						$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);

					$mail->from_email = $shopConfig->get('from_email');
					$mail->from_name = $shopConfig->get('from_name');

					if(!empty($infos->email))
						$mail->dst_email = $infos->email;
					else
						$mail->dst_email = $shopConfig->get('from_email');

					if(!empty($infos->name))
						$mail->dst_name = $infos->name;
					else
						$mail->dst_name = $shopConfig->get('from_name');

					if(!empty($mail->dst_email))
						$mail_sent = $mailClass->sendMail($mail);
				}

				if($mail_sent) {
					$app->enqueueMessage(JText::_('HIKAM_REQUEST_SENT'));
				} else {
					$app->enqueueMessage(JText::_('MAIL_ERROR'), 'error');
				}
			}
		}

		hikaInput::get()->set('layout', 'request');
		return parent::display();
	}

	public function mail() {
		$order_id = hikamarket::getCID('order_id');
		if(empty($order_id) || !$this->checks($order_id))
			return false;

		if(!hikamarket::acl('order/edit/mail'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_MAIL')));

		hikaInput::get()->set('layout', 'mail');
		return parent::display();
	}

	public function previewmail() {
		$order_id = hikamarket::getCID('order_id');
		if(empty($order_id) || !$this->checks($order_id))
			return false;

		if(!hikamarket::acl('order/edit/mail'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_MAIL')));

		hikamarket::cleanBuffers();
		hikaInput::get()->set('layout', 'previewmail');
		parent::display();
		exit;
	}


	public function sendmail() {
		$order_id = hikamarket::getCID('order_id');
		if(empty($order_id) || !$this->checks($order_id))
			return false;
		if(!hikamarket::acl('order/edit/mail'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_MAIL')));

		$orderClass = hikamarket::get('shop.class.order');

		$formData = hikaInput::get()->get('data', array(), 'array');
		$params = array();
		if(!empty($formData['mail']['params'])) {
			foreach($formData['mail']['params'] as $k => $v) {
				if(is_numeric($v))
					$v = (int)$v;
				if(is_string($v))
					$v = strip_tags($v);
				$params[$k] = $v;
			}
		}

		$order = $orderClass->get($order_id);
		$order->url_itemid = '';
		$orderClass->loadOrderNotification($order, 'market.user_order_notification', $params);

		$vendor = hikamarket::loadVendor(true);
		$user = hikamarket::loadUser(true);
		if($vendor->vendor_id > 1 && !empty($vendor->vendor_email)) {
			$order->mail->from_email = $vendor->vendor_email;
			$order->mail->from_name = $vendor->vendor_name;
		}

		if(!empty($formData['mail']['subject'])) {
			$subject = trim(strip_tags($formData['mail']['subject']));
			if(!empty($subject))
				$order->mail->subject = $subject;
		}

		$order->mail->dst_email = $order->customer->user_email;
		if(!empty($order->customer->name))
			$order->mail->dst_name = $order->customer->name;
		if((int)$formData['mail']['dst_email'] == 1) {
			$order->mail->dst_email = $user->user_email; // $vendor->vendor_email;
			$order->mail->dst_name = $user->name; // $vendor->vendor_name;
		}

		$mailClass = hikamarket::get('shop.class.mail');
		$mailClass->sendMail($order->mail);

		if(!$mailClass->mail_success) {
			hikaInput::get()->set('layout', 'mail');
			return parent::display();
		}

		hikamarket::headerNoCache();
		echo '<html><head><script type="text/javascript">window.parent.hikamarket.submitBox();</script></head><body></body></html>';
		exit;
	}

	private function show_products() {
		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component') {
			hikaInput::get()->set('layout', 'show_products');
			ob_end_clean();
			parent::display();
			exit;
		}
		hikaInput::get()->set('layout', 'show');
		return parent::display();
	}

	public function edit() {
		$order_id = hikamarket::getCID('order_id');
		if(!$this->checks($order_id))
			return false;

		$task = hikaInput::get()->getVar('subtask', '');
		if(!in_array($task, $this->subtasks)) {
			$tmpl = hikaInput::get()->getVar('tmpl', '');
			if($tmpl == 'component') {
				exit;
			}
			return false;
		}
		if(!hikamarket::acl('order/edit/'.$task))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		hikaInput::get()->set('layout', 'show_'.$task);

		if(!in_array($task , $this->popupSubtasks)) {
			$tmpl = hikaInput::get()->getVar('tmpl', '');
			if($tmpl == 'component') {
				ob_end_clean();
				parent::display();
				exit;
			}
		} else {
			hikaInput::get()->set('layout', 'edit_'.$task);
		}
		return parent::display();
	}

	public function customer_save() {
		if(!$this->checks())
			return false;

		if(!hikamarket::acl('order/edit/customer'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		$prderClass = hikamarket::get('class.order');
		if( $prderClass === null )
			return false;
		$status = $prderClass->frontSaveFormLegacy('customer');
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
		$order_id = hikaInput::get()->getInt('order_id', 0);
		if(!$this->checks($order_id))
			return false;

		if(!hikamarket::acl('order/edit/customer'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		hikaInput::get()->set('layout', 'customer_set');
		return parent::display();
	}

	public function product_add() {
		if(!$this->checks())
			return false;

		if(!hikamarket::acl('order/edit/products'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		$formData = hikaInput::get()->get('data', array(), 'array');
		$product_quantity = -1;
		if(isset($formData['order']) && isset($formData['order']['product']['order_product_quantity']))
			$product_quantity = (int)$formData['order']['product']['order_product_quantity'];

		if($product_quantity >= 0) {
			if(!JSession::checkToken())
				return false;

			$orderClass = hikamarket::get('class.order');
			if( $orderClass === null )
				return false;
			$status = $orderClass->saveForm('product');
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

	public function product_delete() {
		if(!$this->checks())
			return false;

		if(!hikamarket::acl('order/edit/products'))
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));

		$orderClass = hikamarket::get('class.order');
		if( $orderClass === null )
			return false;
		$status = $orderClass->frontSaveFormLegacy('product_delete');
		if($status) {
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		}

		$tmpl = hikaInput::get()->getVar('tmpl', '');
		if($tmpl == 'component')
			return $this->show_products();
		return $this->show();
	}
}
