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
class market_order_status_notificationPreviewMaker {
	public $displaySubmitButton = false;
	public $type = 'order';

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->loadFullOrder((int)$data);

		if(empty($order->mail_status))
			$order->mail_status = hikamarket::orderStatus(@$order->order_status);
		else
			$order->mail_status = hikamarket::orderStatus($order->mail_status);

		if(isset($order->hikamarket->vendor)) {
			$order->vendor = $order->hikamarket->vendor;
		} else {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor_id = max(1, (int)$order->order_vendor_id);
			$order->vendor = $vendorClass->get($vendor_id);
		}

		$mailClass = hikamarket::get('class.mail');
		$mail = $mailClass->load('order_status_notification', $order);

		$mail->hikamarket = true;
		if(empty($mail->subject))
			$mail->subject = 'MARKET_ORDER_STATUS_NOTIFICATION_SUBJECT';
		$mail->dst_email = $order->vendor->vendor_email;
		$mail->dst_name = $order->vendor->vendor_name;

		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
		$nameboxType = hikashop_get('type.namebox');
		$html = $nameboxType->display(
			'data',
			(int)$data,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'order',
			array(
				'delete' => false,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'returnOnEmpty' => false,
			)
		);
		if(!$html){
			hikashop_display(JText::_('PLEASE_FIRST_CREATE_AN_ORDER'), 'info');
			return;
		}
		if(empty($data)) {
			echo hikashop_display(Jtext::_('PLEASE_SELECT_AN_ORDER_FOR_THE_PREVIEW'));
		}
?>
<dl class="hika_options">
	<dt><?php echo JText::_('HIKASHOP_ORDER'); ?></dt>
	<dd><?php echo $html; ?></dd>
</dl>
<script type="text/javascript">
window.Oby.ready(function() {
	var w = window;
	if(!w.oNameboxes['data'])
		return;
	w.oNameboxes['data'].register('set', function(e) {
		hikashop.submitform('preview','adminForm');
	});
});
</script>
<?php
	}
}
