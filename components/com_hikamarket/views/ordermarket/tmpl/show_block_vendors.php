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
if(empty($this->ajax)) {
	$display = '';
	if(empty($this->order->hikamarket->children))
		$display = 'display:none;';
?>
<!-- VENDORS -->
<div id="hikamarket_order_block_vendors" style="<?php echo $display; ?>">
<?php
}
?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

	<h3 style="display:inline-block"><?php echo JText::_('HIKAM_VENDOR_ORDERS')?></h3>

	<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover table-bordered':'hikam_table'; ?>" id="hikamarket_order_subsales" style="width:100%">
		<thead>
			<tr>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('ORDER_NUMBER');?></th>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('HIKA_VENDOR');?></th>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('ORDER_STATUS');?></th>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('HIKASHOP_TOTAL');?></th>
				<th class="hikamarket_order_item_name_title title"><?php echo JText::_('VENDOR_TOTAL');?></th>
			</tr>
		</thead>
		<tbody id="hikamarket_order_product_listing_content">
<?php
if(!empty($this->order->hikamarket->children)) {
	foreach($this->order->hikamarket->children as $subOrder) {
?>
			<tr>
				<td>
<?php
		if($subOrder->order_type == 'subsale') {
?>
					<a href="<?php echo hikamarket::completeLink('order&task=show&cid='.(int)$subOrder->order_id);?>"><?php echo $subOrder->order_number; ?></a>
<?php
		} else {
			echo '<em>'.JText::_('HIKAM_ORDER_ADJUSTMENT').'</em>';
		}
?>
				</td>
				<td><?php
					echo $this->escape($subOrder->vendor_name);
				?></td>
				<td><?php echo hikamarket::orderStatus($subOrder->order_status); ?></td>
				<td><?php
					echo $this->currencyHelper->format($subOrder->order_full_price, $subOrder->order_currency_id);
				?></td>
				<td><?php
					echo $this->currencyHelper->format($subOrder->order_vendor_price, $subOrder->order_currency_id);
					if(isset($subOrder->order_vendor_price_with_refunds) && $subOrder->order_vendor_price_with_refunds !== null) {
						echo ' (' . $this->currencyHelper->format($subOrder->order_vendor_price_with_refunds, $subOrder->order_currency_id) . ')';
					}
					if(!empty($subOrder->order_vendor_paid))
						echo ' ' . hikamarket::tooltip(JText::_('HIKAM_ORDER_IS_PAID'), '', '', '<img src="'.HIKAMARKET_IMAGES.'icon-16/save2.png" style="vertical-align:top;" alt="('.JText::_('PAID').')" />', '', 0);
				?></td>
			</tr>
<?php
	}
}
?>
		</tbody>
	</table>
<?php if(empty($this->ajax)) { ?>
</div>
<script type="text/javascript">
window.Oby.registerAjax('orderMgr.details',function(params){ window.orderMgr.refreshBlock('vendors'); });
</script>
<?php } elseif(!empty($this->order->hikamarket->children)) { ?>
<script type="text/javascript">
(function(){
	var el = document.getElementById('hikamarket_order_block_vendors');
	if(el) el.style.display = '';
})();
</script>
<?php }
