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
class plgHikashoppaymentPurchaseorder extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'purchaseorder';
	var $pluginConfig = array(
		'order_status' => array('ORDER_STATUS', 'orderstatus'),
		'status_notif_email' => array('ORDER_STATUS_NOTIFICATION', 'boolean','0'),
		'information' => array('CREDITCARD_INFORMATION', 'wysiwyg'),
		'return_url' => array('RETURN_URL', 'input')
	);

	public function needCC(&$method) {
		$method->custom_html = '<span style="margin-left:10%">' . JText::_('PURCHASE_ORDER_NUMBER') .
			'<input type="text" class="hikashop_purchase_order_number inputbox required" name="hikashop_purchase_order_number" value="'.@$_SESSION['hikashop_purchase_order_number'].'"/> *</span>';
	}

	public function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$_SESSION['hikashop_purchase_order_number'] = hikaInput::get()->getVar('hikashop_purchase_order_number');

		$usable_method = parent::onPaymentSave($cart, $rates, $payment_id);

		if($usable_method && $usable_method->payment_type == 'purchaseorder' && empty($_SESSION['hikashop_purchase_order_number'])) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_ENTER_A_PURCHASE_ORDER_NUMBER'), 'error');
			return false;
		}

		return $usable_method;
	}

	public function onAfterOrderProductsListingDisplay(&$order, $type) {
		if(empty($order->order_id))
			return;

		if($order->order_payment_method != 'purchaseorder')
			return;

		$order_payment_params = $order->order_payment_params;
		if(!empty($order_payment_params) && is_string($order_payment_params))
			$order_payment_params = hikashop_unserialize($order_payment_params);
		if(isset($order_payment_params->purchase_order)) {
			echo JText::_('PURCHASE_ORDER_NUMBER') . $order_payment_params->purchase_order;
			return;
		}

		$db = JFactory::getDBO();
		$query = 'SELECT history_data FROM '.hikashop_table('history').
			' WHERE history_order_id = '.(int)$order->order_id . ' AND history_type IN (\'\', '.$db->Quote('purchase order').') AND history_data != \'\' '.
			' ORDER BY history_created ASC';
		$db->setQuery($query);
		echo $db->loadResult();
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if($order->order_payment_method != 'purchaseorder')
			return true;

		$history = new stdClass();
		$history->type = 'purchase order';
		$history->notified = 0;
		$history->data = JText::_('PURCHASE_ORDER_NUMBER') . @$_SESSION['hikashop_purchase_order_number'];

		if(empty($order->order_payment_params))
			$order->order_payment_params = new stdClass();
		$order->order_payment_params->purchase_order = @$_SESSION['hikashop_purchase_order_number'];

		$status = null;
		if(!$this->payment_params->status_notif_email)
			$status = $this->payment_params->order_status;
		$this->modifyOrder($order, $status, $history, false);
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if($order->order_status != $this->payment_params->order_status)
			$this->modifyOrder($order->order_id, $this->payment_params->order_status, (bool)@$this->payment_params->status_notif_email, false);

		$this->removeCart = true;

		$this->information = $this->payment->payment_params->information;
		if(preg_match('#^[a-z0-9_]*$#i', $this->information)) {
			$this->information = JText::_($this->information);
		}

		return $this->showPage('end');
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Purchase order';
		$element->payment_description = 'You can pay by Purchase Order.';
		$element->payment_images = '';
		$element->payment_params->information = 'We will now process your order and contact you when completed.';
		$element->payment_params->order_status = 'created';
	}
}
