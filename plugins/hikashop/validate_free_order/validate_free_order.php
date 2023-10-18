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
class plgHikashopValidate_free_order extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	protected function init() {
		if(isset($this->params))
			return;
		$plugin = JPluginHelper::getPlugin('hikashop', 'validate_free_order');
		$this->params = new JRegistry($plugin->params);
	}

	public function onBeforeOrderCreate(&$order, &$send_email) {
		if(empty($order) || empty($order->order_type) || $order->order_type != 'sale' || !isset($order->order_full_price))
			return;

		$this->init();
		if(!$this->params->get('send_confirmation', 1) && bccomp(sprintf('%F',$order->order_full_price), 0, 5) == 0) {
			$config = hikashop_config();
			$order->order_status = $config->get('order_confirmed_status', 'confirmed');
		}
	}

	public function onAfterOrderCreate(&$order) {
		if(empty($order) || empty($order->order_type) || $order->order_type != 'sale' || !isset($order->order_full_price))
			return;

		if(bccomp(sprintf('%F',$order->order_full_price), 0, 5) != 0)
			return;

		$this->init();
		$config = hikashop_config();


		$send_confirmation = $this->params->get('send_confirmation', 1);
		if($send_confirmation) {
			$orderObj = new stdClass();
			$orderObj->order_id = (int)$order->order_id;
			$orderObj->order_status = $order->order_status = $config->get('order_confirmed_status', 'confirmed');
			$orderObj->history = new stdClass();
			$orderObj->history->history_notified = 1;
			$orderClass = hikashop_get('class.order');
			$orderClass->save($orderObj);
		}

		$send_payment_notif = $this->params->get('send_payment_notif', 1);
		$recipients = trim($config->get('payment_notification_email', ''));
		if($send_payment_notif && !empty($recipients)) {
			$payment_status = hikashop_orderStatus($order->order_status);

			if(!empty($order->order_id)) {
				$message = str_replace('<br/>', "\r\n", JText::_('FREE_ORDER_PAYMENT_NOTIFICATION')) . ' ' .
					JText::sprintf('ORDER_STATUS_CHANGED', $payment_status) .
					"\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $order->order_number, HIKASHOP_LIVE);
				$orderClass = hikashop_get('class.order');
				$orderMail = $orderClass->loadNotification((int)$order->order_id, 'payment_notification', $message);
				if(empty($orderMail->mail->subject))
					$orderMail->mail->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', JText::_('FREE_ORDER'), $payment_status, $order->order_number);
				$orderMail->mail->dst_email = $recipients;

				$mailClass = hikashop_get('class.mail');
				$mailClass->sendMail($orderMail->mail);
			}
		}

		if($order->order_status == $config->get('order_confirmed_status', 'confirmed')) {
			$class = hikashop_get('class.cart');
			$class->cleanCartFromSession();
		}
	}
}
