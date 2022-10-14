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

class vendorMarketController extends hikamarketController {

	protected $type = 'vendor';
	protected $toggle = array('vendor_published' => 'vendor_id');
	protected $rights = array(
		'display' => array('display', 'show', 'cancel', 'listing', 'admin', 'products', 'invoices', 'pay', 'paymanual', 'geninvoice', 'dogeninvoice', 'selection', 'useselection', 'getprice', 'searchfields', 'getvalues', 'reports'),
		'add' => array('add'),
		'edit' => array('edit','toggle','publish','unpublish'),
		'modify' => array('save','apply','dopay'),
		'delete' => array('remove')
	);

	public function __construct($config = array())	{
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}

	public function store() {
		return parent::adminStore();
	}

	public function remove() {
		$confirm = hikaInput::get()->getVar('confirm', '');

		$cid = hikaInput::get()->post->get('cid', array(), 'array');
		hikamarket::toInteger($cid);

		if(!empty($confirm)) {
			sort($cid);
			$check = md5(implode(';', $cid));
			if($confirm != $check || in_array(1, $cid)) {
				$confirm = null;
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('INCORRECT_DATA'));
			}
		}

		if(empty($confirm)) {
			hikaInput::get()->set('layout', 'delete');
			return parent::display();
		}
		return parent::adminRemove();
	}

	public function cancel(){
		$this->setRedirect( hikamarket::completeLink('vendor',false,true) );
	}

	public function admin() {
		hikaInput::get()->set('layout', 'admin');
		return parent::display();
	}

	public function products() {
		hikaInput::get()->set('layout', 'products');
		return parent::display();
	}

	public function pay() {
		$config = hikamarket::config();
		$vendor_id = hikamarket::getCID('vendor_id');
		$vendor_ids = hikaInput::get()->get('cid', array(), 'array');

		if(!empty($vendor_ids) && count($vendor_ids) > 1) {
			$vendor_id = $vendor_ids;
			hikamarket::toInteger($vendor_id);
		}

		if(!empty($vendor_id))
			hikaInput::get()->set('layout', 'pay');

		if(is_array($vendor_id) && hikaInput::get()->getInt('report', 0) != 0) {
			hikaInput::get()->set('layout', 'payreport');
			return parent::display();
		}

		$orders = hikaInput::get()->get('orders', array(), 'array');
		if(!empty($orders) && !is_array($vendor_id)) {
			JSession::checkToken('request') || die('Invalid Token');

			$vendorClass = hikamarket::get('class.vendor');
			$status = $vendorClass->pay($vendor_id, $orders);

			if($status) {
				$app = JFactory::getApplication();
				$app->redirect(hikamarket::completeLink('shop.order&task=edit&cid[]='.$status, false, true));
			}
		}
		return parent::display();
		return false;
	}

	public function dopay() {
		JSession::checkToken('request') || die('Invalid Token');

		$app = JFactory::getApplication();
		$vendor_id = hikamarket::getCID();
		$vendor_ids = hikaInput::get()->get('cid', array(), 'array');
		hikamarket::toInteger($vendor_ids);

		hikaInput::get()->set('layout', 'listing');
		if(empty($vendor_id))
			return parent::display();

		if(count($vendor_ids) > 1) {
			$filter_start = hikaInput::get()->getVar('filter_start', null);
			$filter_end = hikaInput::get()->getVar('filter_end', null);
			$session_filter_start = $app->getUserState(HIKAMARKET_COMPONENT.'.vendormarket.pay.filter_start', null);
			$session_filter_end = $app->getUserState(HIKAMARKET_COMPONENT.'.vendormarket.pay.filter_end', null);

			if($filter_start != $session_filter_start || $filter_end != $session_filter_end) {
				$app->enqueueMessage(JText::_('HIKAM_THE_FILTER_HAS_CHANGED'));
				hikaInput::get()->set('layout', 'pay');
				return parent::display();
			}

			$filters = array(
				'start' => $filter_start,
				'end' => $filter_end
			);

			$vendorClass = hikamarket::get('class.vendor');
			$status = $vendorClass->pay($vendor_ids, null, $filters);

			if(!empty($status)) {
				if(!is_array($status)) {
					$app->redirect(hikamarket::completeLink('shop.order&task=edit&cid[]=' . (int)$status, false, true));
				}

				$vendor_errors = array();
				foreach($status as $k => $v) {
					if($v !== false)
						continue;
					unset($status[$k]);
					$vendor_errors[] = (int)$k;
				}
				if(!empty($vendor_errors)) {
					$query = 'SELECT vendor_name FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN ('.implode(',', $vendor_errors).')';
					$db = JFactory::getDBO();
					$vendors = $db->loadColumn();
					$app->enqueueMessage(JText::sprint('CANNOT_PAY_VENDORS', implode(', ', $vendors)), 'error');
				}
				if(count($status) == 1)
					$status[] = 0;
				$app->redirect(hikamarket::completeLink('vendor&task=pay&report=1&cid[]=' . implode('&cid[]=', $status), false, true));
			}

			hikaInput::get()->set('layout', 'pay');
			$app->enqueueMessage(JText::_('INVALID_DATA'), 'error');
		} else {
			$orders = hikaInput::get()->get('orders', array(), 'array');
			if(!empty($orders)) {
				JSession::checkToken('request') || die('Invalid Token');

				$vendorClass = hikamarket::get('class.vendor');
				$status = $vendorClass->pay($vendor_id, $orders);

				if(!is_array($status))
					$status = array($status);
				if(count($status) == 1)
					$status[] = 0;
				$app->redirect(hikamarket::completeLink('vendor&task=pay&report=1&cid[]=' . implode('&cid[]=', $status), false, true));
			}
		}
		return parent::display();
		return false;
	}

	public function geninvoice() {
		return self::pay();
	}

	public function dogeninvoice() {
		return self::dopay();
	}

	public function paymanual() {
		$vendor_id = hikamarket::getCID('vendor_id');
		$order_id = hikaInput::get()->getInt('order_id', 0);
		$payment_method = hikaInput::get()->getString('payment_method', 'manual');

		if(empty($order_id) || empty($vendor_id)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('INVALID_DATA'), 'error');
			return false;
		}

		$vendorClass = hikamarket::get('class.vendor');
		$vendor = $vendorClass->get($vendor_id);
		if(empty($vendor)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('INVALID_DATA'), 'error');
			return false;
		}

		if($payment_method == 'paypal' && empty($vendor->vendor_params->paypal_email)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('HIKAM_ERR_PAYPAL_EMAIL_EMPTY'), 'error');
			return false;
		}

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!empty($formData) && $payment_method == 'manual' && !empty($formData['validation'])) {
			$config = hikamarket::config();
			$shopConfig = hikamarket::config(false);
			$confirmed_status = $config->get('vendorpayment_confirmed_status', '');
			if(empty($confirmed_status))
				$confirmed_status = $shopConfig->get('order_confirmed_status', 'confirmed');

			$update_order = new stdClass();
			$update_order->order_id = (int)$order_id;
			$update_order->order_status = $confirmed_status;
			$update_order->history = new stdClass();
			$update_order->history->history_reason = JText::_('MANUAL_VALIDATION');
			$update_order->history->history_notified = false;

			if(!empty($formData['notify']))
				$update_order->history->history_notified = true;

			$orderClass = hikamarket::get('shop.class.order');
			$status = $orderClass->save($update_order);

			$data = array(
				'result' => ($status ? $confirmed_status : 'error')
			);

			echo '<html><body>'.
				'<script type="text/javascript">'."\r\n".
				'window.parent.hikamarket.submitBox('.json_encode($data).');'."\r\n".
				'</script>'."\r\n".
				'</body></html>';
			exit;
		}

		hikaInput::get()->set('layout', 'paymanual');
		return parent::display();
		return false;
	}

	public function selection() {
		hikaInput::get()->set('layout', 'selection');
		return parent::display();
	}

	public function useselection() {
		hikaInput::get()->set('layout', 'useselection');
		return parent::display();
	}

	public function searchfields() {
		hikaInput::get()->set('layout', 'searchfields');
		return parent::display();
	}

	public function reports() {
		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if($tmpl == 'ajax') {
			return $this->reportsAjax();
		}

		hikaInput::get()->set('layout', 'reports');
		return parent::display();
	}

	protected function reportsAjax() {
		$vendor_id = hikamarket::getCID('vendor_id', 0);
		$statName = hikaInput::get()->getCmd('chart', '');
		$statValue = hikaInput::get()->getString('value', '');
		if(empty($vendor_id) || empty($statName) || empty($statValue)) {
			echo '{}';
			exit;
		}

		$statisticsClass = hikamarket::get('class.statistics');
		$ret = $statisticsClass->getAjaxData($vendor_id, $statName, $statValue);

		if($ret === false) {
			echo '{}';
			exit;
		}
		echo $ret;
		exit;
	}

	public function getUploadSetting($upload_key, $caller = '') {
		$vendor_id = hikaInput::get()->getInt('vendor_id');
		$vendorClass = hikamarket::get('class.vendor');
		$vendor = $vendorClass->get($vendor_id);
		if(empty($upload_key) || (empty($vendor) && !empty($vendor_id)))
			return false;

		$upload_value = null;
		$upload_keys = array(
			'vendor_image' => array(
				'type' => 'image',
				'field' => 'data[vendor][vendor_image]'
			)
		);

		if(empty($upload_keys[$upload_key]))
			return false;
		$upload_value = $upload_keys[$upload_key];

		return array(
			'limit' => 1,
			'type' => $upload_value['type'],
			'options' => array(),
			'extra' => array(
				'vendor_id' => $vendor_id,
				'field_name' => $upload_value['field']
			)
		);
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret) || empty($ret->name) || empty($uploadConfig['extra']['vendor_id']))
			return;

		$vendorClass = hikamarket::get('class.vendor');
		$vendor = new stdClass();
		$vendor->vendor_id = (int)$uploadConfig['extra']['vendor_id'];
		$vendor->vendor_image = $ret->name;
		$vendorClass->save($vendor);
	}

	public function getPrice() {
		$currency_id = hikaInput::get()->getInt('currency_id', 0);
		$price_id = hikaInput::get()->getFloat('value', 0);
		$currencyClass = hikamarket::get('shop.class.currency');
		echo $currencyClass->format($price_id, $currency_id);
		exit;
	}

	public function getValues() {
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');
		$search = hikaInput::get()->getVar('search', null);
		$start = hikaInput::get()->getInt('start', 0);

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'displayFormat' => $displayFormat
		);
		if($start > 0)
			$options['page'] = $start;
		$ret = $nameboxType->getValues($search, 'vendor', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
}
