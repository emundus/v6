<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->ajax)) {
?>
<fieldset class="adminform" id="hikashop_order_field_market" style="<?php if(empty($this->data)) { echo 'display:none;'; } ?>">
<?php
}
if(!empty($this->data)) {
?>
	<legend><?php echo JText::_('HIKAMARKET_ORDERS')?></legend>
	<table style="width:100%;cell-spacing:1px;" class="adminlist table table-striped">
		<thead>
			<tr>
				<th><?php echo JText::_('ORDER_NUMBER');?></th>
				<th><?php echo JText::_('HIKA_VENDOR');?></th>
				<th><?php echo JText::_('ORDER_STATUS');?></th>
				<th><?php echo JText::_('HIKASHOP_TOTAL');?></th>
				<th><?php echo JText::_('VENDOR_TOTAL');?></th>
				<th style="width:1%"><?php echo JText::_('VENDOR_PAID');?></th>
			</tr>
		</thead>
		<tbody>
<?php
	foreach($this->data as $data) {
		$vendor_id = (int)$data->order_vendor_id;
		if(empty($vendor_id)) $vendor_id = 1;
		$paid = !empty($this->vendor_transactions[$vendor_id]) ? true : (int)$data->order_vendor_paid;
		if(!empty($this->vendor_transactions[ $vendor_id ])) {
			foreach($this->vendor_transactions[ $vendor_id ] as $transaction) {
				if(isset($transaction->order_transaction_paid) && empty($transaction->order_transaction_paid))
					$paid = false;
			}
		}
?>
			<tr>
				<td>
					<a href="<?php echo hikamarket::completeLink('shop.order&task=edit&cid='.(int)$data->order_id);?>"><?php echo $data->order_number;?></a>
					/ <?php echo !empty($data->order_invoice_number) ? $data->order_invoice_number : ' -'; ?>
				</td>
				<td><a href="<?php echo hikamarket::completeLink('vendor&task=edit&cid='.(int)$data->order_vendor_id);?>"><?php echo $this->escape($data->vendor_name); ?></a></td>
				<td><?php echo hikamarket::orderStatus($data->order_status); ?></td>
				<td><?php echo $this->currencyHelper->format($data->order_full_price, $data->order_currency_id);?></td>
				<td><?php
					echo $this->currencyHelper->format($data->order_vendor_price, $data->order_currency_id);
					if(isset($data->order_vendor_price_with_refunds) && $data->order_vendor_price_with_refunds !== null) {
						echo ' (' . $this->currencyHelper->format($data->order_vendor_price_with_refunds, $data->order_currency_id) . ')';
					}
				?></td>
				<td style="text-align:center"><?php
					if($paid)
						echo '<i class="fa fa-check"></i>';
				?></td>
			</tr>
<?php
		if(!empty($this->vendor_transactions[ $vendor_id ])) {
			foreach($this->vendor_transactions[ $vendor_id ] as $transaction) {
?>
			<tr>
				<td><em><?php echo JText::_('VENDOR_TRANSACTION'); ?></em></td>
				<td><?php echo $this->escape($data->vendor_name); ?></td>
				<td><?php echo hikamarket::orderStatus($transaction->order_transaction_status); ?></td>
				<td></td>
				<td><?php
					echo $this->currencyHelper->format($transaction->order_transaction_price, $transaction->order_transaction_currency_id);
				?></td>
				<td style="text-align:center"><?php
					if(!empty($transaction->order_transaction_paid))
						echo '<i class="fa fa-check"></i>';
				?></td>
			</tr>
<?php
			}
		}
	}
?>
		</tbody>
	</table>
<?php
	if(!empty($this->ajax)) {
?>
<script type="text/javascript">
var el = document.getElementById('hikashop_order_field_market');
if(el) el.style.display = '';
</script>
<?php
	}
}

if(empty($this->ajax)) {
?>
</fieldset>
<script type="text/javascript">
window.Oby.registerAjax('hikashop.order_update', function(params) {
	if(params.el === undefined) return;
	window.Oby.xRequest("<?php echo hikamarket::completeLink('order&task=show&cid='.$this->order_id, true, false, true); ?>", {update: 'hikashop_order_field_market'});
});
</script>
<?php
}
