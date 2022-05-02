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
<form action="<?php echo hikamarket::completeLink('vendor'); ?>" method="post" name="adminForm" id="adminForm">
<?php
	if(!empty($this->orders)) {
?>
<div id="hikashop_backend_tile_edition">
<div class="hk-row-fluid">

	<div class="hkc-md-2 hikashop_tile_block hkc-md-last "><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('PAY_RESULT');
		?></div>
		<dl class="hikamarket_pay_result">
			<dt><?php echo JText::_('HIKASHOP_TOTAL'); ?></dt>
			<dd><span id="hikamarket_pay_total"><?php echo $this->currencyHelper->format(0, $this->vendor->vendor_currency_id); ?></span></dd>

			<dt><?php echo JText::_('HIKA_TYPE'); ?></dt>
			<dd><span id="hikamarket_pay_type"><?php
				echo JText::_('HIKA_NONE');
			?></span></dd>
		</dl>
	</div></div>

	<div class="hkc-md-10 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('ORDERS');
		?></div>
<table class="adminlist pad5 table table-striped table-hover" style="width:100%">
	<thead>
		<tr>
			<th class="title titlebox"><input type="checkbox" name="toggle" value="" onclick="window.localPage.updateVendorTotal(this);" /></th>
			<th class="hikamarket_order_id_title title"><?php
				echo JText::_('ORDER_NUMBER');
			?></th>
			<th class="hikamarket_order_customer_title title"><?php
				echo JText::_('CUSTOMER');
			?></th>
			<th class="hikamarket_order_status_title title"><?php
				echo JText::_('ORDER_STATUS');
			?></th>
			<th class="hikamarket_order_date_title title"><?php
				echo JText::_('DATE');
			?></th>
			<th class="hikamarket_order_total_title title"><?php
				echo JText::_('HIKASHOP_TOTAL');
			?></th>
			<th class="hikamarket_order_vendor_total_title title"><?php
				echo JText::_('VENDOR_TOTAL');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
		$k = 0;
		$i = 0;
		foreach($this->orders as $order) {
?>
		<tr class="row<?php echo $k; ?>" onclick="if(window.localPage.cancelRow) {window.localPage.cancelRow = false; return false;} hikamarket.checkRow('cb<?php echo $i; ?>'); window.localPage.getVendorTotal();">
			<td align="center">
				<input type="checkbox" onchange="if(window.hkmCA&&window.hkmCA===true){return true;} window.localPage.getVendorTotal()" onclick="if(window.hkmCA&&window.hkmCA===true){return true;} this.clicked=true; this.checked=!this.checked" value="<?php echo $order->order_transaction_id;?>" name="orders[]" id="cb<?php echo $i;?>"/>
			</td>
			<td class="hikamarket_order_id_value" align="center">
				<a onclick="return window.localPage.openInvoice(this, <?php echo (int)$order->order_id; ?>);" href="<?php echo hikamarket::completeLink('shop.order&task=edit&cid[]='.$order->order_id.'&cancel_redirect='.$this->cancelUrl); ?>"><?php
					if(!empty($order->order_number)) {
						echo $order->order_number;
					} else {
						echo '<em>' . JText::_('HIKA_NONE') . '</em>';
					}
				?></a>
<?php
			if((int)$order->order_transaction_valid == 2)
				echo ' - ' . JText::_('HIKAM_ORDER_ADJUSTMENT');
?>
			</td>
			<td class="hikamarket_order_customer_value"><label for"cb<?php echo $i; ?>"><?php
				echo $order->user_email;
			?></label></td>
			<td class="hikamarket_order_status_value"><?php
				echo $order->order_transaction_status;
			?></td>
			<td class="hikamarket_order_date_value"><?php
				echo hikamarket::getDate($order->order_transaction_created, '%Y-%m-%d %H:%M');
			?></td>
			<td class="hikamarket_order_total_value"><?php
				if($order->order_full_price !== null)
					echo $this->currencyHelper->format($order->order_full_price, $order->order_currency_id);
			?></td>
			<td class="hikamarket_order_vendor_total_value"><?php
				if($this->feeMode || $order->order_transaction_price < 0 || $order->order_full_price === null)
					$convertedPrice = $this->currencyHelper->convertUniquePrice($order->order_transaction_price, $order->order_transaction_currency_id, $this->vendor->vendor_currency_id);
				else
					$convertedPrice = $this->currencyHelper->convertUniquePrice($order->order_full_price - $order->order_transaction_price, $order->order_transaction_currency_id, $this->vendor->vendor_currency_id);

				echo $this->currencyHelper->format($convertedPrice, $this->vendor->vendor_currency_id);
			?><div id="vendorTotalInCurrency<?php echo $i;?>" style="display:none;"><?php
				echo $convertedPrice;
			?></div></td>
		</tr>
<?php
			$i++;
			$k = 1 - $k;
		}
?>
	</tbody>
</table>
	</div></div>
</div>

<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.triggerVendorTotal = null;
window.localPage.getVendorTotal = function(updateCheck) {
	if(window.localPage.triggerVendorTotal != null)
		clearTimeout(window.localPage.triggerVendorTotal);
	if(updateCheck === undefined)
		updateCheck = false;

	window.localPage.triggerVendorTotal = setTimeout(function() {
		var d = document, chk = null, cpt = 0, total = 0, url = "";
		for(var i = <?php echo count($this->orders) - 1; ?>; i >= 0; i--) {
			chk = d.getElementById('cb'+i);
			if(chk && chk.checked) {
				cpt++;
				var div = d.getElementById("vendorTotalInCurrency" + i);
				if(div) {
					var v = parseFloat(div.innerHTML);
					if(!isNaN(v))
						total += v;
				}
			}
		}
		if(updateCheck) {
			if(document.adminForm.form)
				document.adminForm.form.boxchecked = cpt;
			else
				document.adminForm.boxchecked = cpt;
		}
		var urlTotal = total;
		if(urlTotal < 0)
			urlTotal = -urlTotal;
		url = "<?php echo hikamarket::completeLink('vendor&task=getPrice&currency_id='.$this->vendor->vendor_currency_id, true, false, true); ?>&value=" + urlTotal;
		window.Oby.xRequest(url, {update: "hikamarket_pay_total"}, function(xhr){
			var el = document.getElementById("hikamarket_pay_type");
			if(total == 0) {
				el.innerHTML = "<?php echo JText::_('HIKA_NONE', true); ?>";
			} else if(total > 0) {
				el.innerHTML = "<?php echo JText::_('ORDER', true); ?>";
			} else {
				el.innerHTML = "<?php echo JText::_('INVOICE', true); ?>";
			}
		});
	}, 500 );
};
window.localPage.updateVendorTotal = function(el) {
	window.hkmCA=true;
	hikashop.checkAll(el);
	window.hkmCA=false;
	setTimeout(function(){ window.localPage.getVendorTotal(); }, 10);
	return true;
};
(function(){ window.localPage.getVendorTotal(true); })();
</script>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="cid" value="<?php echo @$this->vendor->vendor_id; ?>" />
<?php
	} else {
?>
<!-- FILTERS -->
<div>
	<label for="period_start"><?php echo JText::_('FROM'); ?></label>
	<?php echo JHTML::_('calendar', hikamarket::getDate((@$this->pageInfo->filter->filter_start ? @$this->pageInfo->filter->filter_start:''), '%d %B %Y'), 'filter_start', 'period_start', '%Y-%m-%d', array('size' => '10')); ?>
	<label for="period_end"><?php echo JText::_('TO'); ?></label>
	<?php echo JHTML::_('calendar', hikamarket::getDate((@$this->pageInfo->filter->filter_end ? @$this->pageInfo->filter->filter_end:''), '%d %B %Y'), 'filter_end', 'period_end', '%Y-%m-%d', array('size' => '10')); ?>

	<button style="vertical-align:top" class="btn btn-success" onclick="this.form.submit();"><?php
		if(HIKASHOP_BACK_RESPONSIVE)
			echo '<span class="icon-filter"></span>';
		else
			echo JText::_('APPLY');
	?></button>
</div>

	<table class="adminlist pad5 table table-striped table-hover" style="width:100%">
		<thead>
			<tr>
				<th class="title titlebox"></th>
				<th class="hikamarket_pay_vendor_title title"><?php
					echo JText::_('VENDOR_NAME');
				?></th>
				<th class="hikamarket_pay_orders_title title"><?php
					echo JText::_('ORDERS_UNPAID');
				?></th>
				<th class="hikamarket_order_vendor_total_title title"><?php
					echo JText::_('VENDOR_TOTAL');
				?></th>
				<th class="title titlebox"></th>
			</tr>
		</thead>
		<tbody>
<?php
		$nb_valid_vendors = 0;
		$i = 0;
		$k = 0;
		foreach($this->vendors as $vendor) {
			if($vendor->nb_orders > 0)
				$nb_valid_vendors++;
?>
			<tr class="row<?php echo $k; ?>">
				<td><?php
					echo $this->toggleHelper->display('activate', $vendor->vendor_published);
				?></td>
				<td><?php
					echo $vendor->vendor_name;
				?></td>
				<td><?php
					echo $vendor->nb_orders;
				?></td>
				<td><?php
					echo $this->currencyHelper->format($vendor->total_vendor_price, $vendor->vendor_currency_id);
				?></td>
				<td>
					<input type="hidden" name="cid[]" value="<?php echo (int)$vendor->vendor_id; ?>" />
					<a href="#delete" onclick="return window.localPage.deleteVendorRow(this);"><img src="<?php echo HIKAMARKET_IMAGES; ?>icon-16/delete.png" alt="X"/></a>
				</td>
			</tr>
<?php
			$k = 1 - $k;
		}
?>
		</tbody>
	</table>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.deleteVendorRow = function(el) {
	if(!confirm("<?php echo JText::_('PLEASE_CONFIRM_DELETION'); ?>"))
		return false;
	window.hikashop.deleteRow(el);
	document.adminForm.submit();
	return false;
}
</script>
	<input type="hidden" name="boxchecked" value="<?php echo $nb_valid_vendors; ?>" />
<?php
	}
?>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="<?php if(empty($this->orders)) echo 'pay'; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php
echo $this->popup->display(
	'',
	JText::_('HIKASHOP_ORDER'),
	hikamarket::completeLink('dashboard', true),
	'hikamarket_pay_shoporder_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
?>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.cancelRow = false;
window.localPage.openInvoice = function(el, order_id) {
	window.localPage.cancelRow = true;
	window.hikamarket.submitFct = function(data) { window.hikamarket.closeBox(); };
	window.hikamarket.openBox('hikamarket_pay_shoporder_popup', '<?php echo hikamarket::completelink('shop.order&task=invoice&type=full&order_id=ORDERID', true, false, true); ?>'.replace('ORDERID', order_id));
	return false;
};
</script>
