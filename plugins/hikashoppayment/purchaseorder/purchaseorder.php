<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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
		'information' => array('CREDITCARD_INFORMATION', 'big-textarea'),
		'return_url' => array('RETURN_URL', 'input')
	);

	public function needCC(&$method) {
		$method->custom_html = '<span style="margin-left:10%">' . JText::_('PURCHASE_ORDER_NUMBER') .
			'<input type="text" class="hikashop_purchase_order_number inputbox required" name="hikashop_purchase_order_number" value="'.@$_SESSION['hikashop_purchase_order_number'].'"/> *</span>';
	}

	public function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$_SESSION['hikashop_purchase_order_number'] = JRequest::getVar('hikashop_purchase_order_number');

		$usable_method = parent::onPaymentSave($cart, $rates, $payment_id);

		if($usable_method && $usable_method->payment_type == 'purchaseorder' && empty($_SESSION['hikashop_purchase_order_number'])) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_ENTER_A_PURCHASE_ORDER_NUMBER'));
			return false;
		}

		return $usable_method;
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

		$this->modifyOrder($order, $this->payment_params->order_status, $history, false);
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		$method =& $methods[$method_id];
		$this->removeCart = true;

		$this->information = $method->payment_params->information;
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
