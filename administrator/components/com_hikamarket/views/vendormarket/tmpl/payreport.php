<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<table class="adminlist pad5 table table-striped table-hover table-bordered" style="width:100%">
	<thead>
		<tr>
			<th><?php echo JText::_('ORDER_NUMBER'); ?></th>
			<th><?php echo JText::_('VENDOR_PAY_TYPE'); ?></th>
			<th><?php echo JText::_('VENDOR_NAME'); ?></th>
			<th><?php echo JText::_('HIKASHOP_TOTAL'); ?></th>
			<th><?php echo JText::_('ORDER_STATUS'); ?></th>
			<th><?php echo JText::_('PAY_VENDOR'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	foreach($this->orders as $order) {
?>
		<tr>
			<td>
				<a onclick="return window.localPage.openInvoice(this, <?php echo (int)$order->order_id; ?>);" href="<?php echo hikamarket::completeLink('shop.order&task=edit&cid[]='.$order->order_id.'&cancel_redirect='.$this->cancelUrl); ?>"><i class="fas fa-receipt"></i> <?php echo $order->order_number; ?></a>
				<a style="margin-left:1em;" href="<?php echo hikamarket::completeLink('shop.order&task=edit&cid[]='.$order->order_id.'&cancel_redirect='.$this->cancelUrl); ?>" target="_blank"><i class="fa fa-external-link-alt"></i></a>
			</td>
			<td><?php
				switch($order->order_type) {
					case 'vendorpayment':
						echo JText::_('VENDOR_PAY_PAYMENT');
						break;
					case 'sale':
						echo JText::_('VENDOR_PAY_INVOICE');
						break;
					default:
						echo $this->escape($order->order_type);
						break;
				}
			?></td>
			<td><?php
				echo $this->escape($order->vendor_name);
			?></td>
			<td><?php
				echo $this->currencyClass->format($order->order_full_price, $order->order_currency_id);
			?></td>
			<td><span id="hikamarket_payvendor_order_<?php echo $order->order_id; ?>"><?php
				echo hikamarket::orderStatus($order->order_status);
			?></span> <div class="toggle_loading"><a class="refresh" href="#refresh" onclick="return window.localPage.refreshOrder(this, <?php echo (int)$order->order_id; ?>);"></a></div></td>
			<td><?php
				if(hikamarket::toFloat($order->order_full_price) > 0.0 && $order->order_type == 'vendorpayment' && $order->order_status == $this->created_status) {
					$vendor_params = (!empty($order->vendor_params) && is_string($order->vendor_params) ) ? hikamarket::unserialize($order->vendor_params) : $order->vendor_params;
					if(!empty($vendor_params->paypal_email)) { ?>
				<span id="hikamarket_payvendor_paypal_<?php echo $order->order_id; ?>">
					<a class="hikabtn hikabtn-info" onclick="return window.localPage.payVendor(this, <?php echo (int)$order->order_id; ?>, <?php echo (int)$order->vendor_id; ?>, 'paypal');" href="#"><i class="fab fa-paypal"></i> <?php
						echo JText::_('HIKAM_PAY_PAYPAL');
					?></a>
				</span>
				<?php
					}
					?><a class="hikabtn hikabtn-info" onclick="return window.localPage.payVendor(this,<?php echo (int)$order->order_id; ?>,<?php echo (int)$order->vendor_id; ?>);" href="#"><i class="fa fa-money-bill"></i> <?php echo JText::_('HIKAM_PAY_MANUAL'); ?></a><?php
				} else {
					echo '-';
				}
			?></td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
<?php
echo $this->popup->display(
	'',
	JText::_('HIKASHOP_ORDER'),
	hikamarket::completeLink('dashboard', true),
	'hikamarket_pay_shoporder_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
echo $this->popup->display(
	'',
	JText::_('PAY_VENDOR'),
	hikamarket::completeLink('dashboard', true),
	'hikamarket_pay_payment_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
?>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.openInvoice = function(el, order_id) {
	window.hikamarket.submitFct = function(data) { window.hikamarket.closeBox(); };
	window.hikamarket.openBox('hikamarket_pay_shoporder_popup', '<?php echo hikamarket::completelink('shop.order&task=invoice&type=full&order_id=ORDERID', true, false, true); ?>'.replace('ORDERID', order_id));
	return false;
};
window.localPage.refreshOrder = function(el, order_id) {
	var span = document.getElementById('hikamarket_payvendor_order_' + order_id);
	if(!span)
		return false;
	el.parentNode.className = 'toggle_onload';
	window.Oby.xRequest('<?php echo hikamarket::completeLink('order&task=checkstatus&order_id=ORDERID&tmpl=json', false, false, true); ?>'.replace('ORDERID', order_id), {}, function(xhr) {
		span.innerHTML = xhr.responseText;
		el.parentNode.className = 'toggle_loading';
	});
	return false;
};
window.localPage.payVendor = function(el, order_id, vendor_id, mode) {
	if(mode === undefined)
		mode = 'manual';
	if(mode != 'manual' && mode != 'paypal')
		mode = 'manual';

	window.hikamarket.submitFct = function(data) {
		var el = document.getElementById('hikamarket_payvendor_order_' + order_id);
		if(el && data.result)
			el.innerHTML = data.result;
		if(mode == 'paypal') {
			var link = document.getElementById('hikamarket_payvendor_paypal_' + order_id);
			if(link)
				link.innerHTML = '<?php echo str_replace(array('\\','\''), array('\\\\','\\\''), JText::_('HIKAM_PAY_PAYPAL_PENDING')); ?>';
		}
		window.hikamarket.closeBox();
	};
	var url = '<?php echo hikamarket::completelink('vendor&task=paymanual&order_id=ORDERID&vendor_id=VENDORID&payment_method=PAYMENTMETHOD', true, false, true); ?>'.replace('ORDERID', order_id).replace('VENDORID', vendor_id).replace('PAYMENTMETHOD', mode);
	window.hikamarket.openBox('hikamarket_pay_payment_popup', url);
	return false;
};
</script>
