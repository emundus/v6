<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
<!--
window.orderMgr = {
	updateAdditionnals: function(){},
	updateHistory: function(){},
	updateShipping: function(){},
	updateBilling: function(){}
};
//-->
</script>
<table class="hikam_blocks">
	<tr>
		<td style="width:50%" class="hikam_block_l">
<fieldset class="hikam_field" id="hikamarket_order_field_general">
<?php
echo $this->loadTemplate('general');
?>
</fieldset>
			<fieldset class="hikam_field">
				<legend><?php echo JText::_('CUSTOMER')?></legend>
<?php if(hikamarket::acl('order/edit/customer') && ($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1)) { ?>
				<div class="hikam_edit">
					<?php
						echo $this->popup->display(
							'<img src="'. HIKAMARKET_IMAGES .'icon-16/edit.png" alt=""/><span>'. JText::_('HIKA_EDIT') .'</span>',
							'HIKAM_SET_ORDER_CUSTOMER',
							hikamarket::completeLink('user&task=selection&single=1&confirm=0&after=order|customer_set&afterParams=order_id|'.$this->order->order_id, true),
							'hikamarket_setcustomer_popup',
							750, 460, 'onclick="return window.orderMgr.setCustomer(this);"', '', 'link'
						);
					?>
				</div>
<script type="text/javascript">
<!--
window.orderMgr.setCustomer = function(el) {
	var w = window;
	w.hikamarket.submitFct = function(data) {
		var d = document, input = null, inputs = {id:'hikamarket_order_customer_id',name:'hikamarket_order_customer_name',email:'hikamarket_order_customer_email'};
		for(var i in inputs) {
			input = d.getElementById(inputs[i]);
			if(input)
				input.innerHTML = data[i];
		}
		if(data['updates']) {
			for(var i = 0; i < data['updates'].length; i++) {
				var up = data['updates'][i];
				if(up == 'history') window.orderMgr.updateHistory();
				if(up == 'billing') window.orderMgr.updateBilling();
			}
		}
	};
	w.hikashop.openBox(el);
	return false;
}
//-->
</script>
<?php } ?>
				<dl class="hikam_options">
					<dt class="hikamarket_order_customer_name"><label><?php echo JText::_('HIKA_NAME');?></label></dt>
					<dd class="hikamarket_order_customer_name"><span id="hikamarket_order_customer_name"><?php echo @$this->order->customer->name; ?></span></dd>
				</dl>
				<dl class="hikam_options">
					<dt class="hikamarket_order_customer_email"><label><?php echo JText::_('HIKA_EMAIL');?></label></dt>
					<dd class="hikamarket_order_customer_email"><span id="hikamarket_order_customer_email"><?php echo @$this->order->customer->user_email; ?></span></dd>
				</dl>
				<dl class="hikam_options">
					<dt class="hikamarket_order_customer_id"><label><?php echo JText::_('ID');?></label></dt>
					<dd class="hikamarket_order_customer_id"><span id="hikamarket_order_customer_id"><?php echo @$this->order->customer->user_id; ?></span></dd>
				</dl>
			</fieldset>
		</td>
		<td style="width:50%;" class="hikam_block_r">
			<fieldset class="hikam_field" id="hikamarket_order_field_additional">
<?php
echo $this->loadTemplate('additional');
?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td class="hikam_block_l">
			<fieldset class="hikam_field" id="hikamarket_order_field_billing_address">
<?php
	$this->type = 'billing';
	echo $this->loadTemplate('address');
?>
			</fieldset>
		</td>
		<td class="hikam_block_r">
			<fieldset class="hikam_field" id="hikamarket_order_field_shipping_address">
<?php
	if(empty($this->order->override_shipping_address)) {
		$this->type = 'shipping';
		echo $this->loadTemplate('address');
	} else {
		echo $this->order->override_shipping_address;
	}
?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset class="hikam_field" id="hikamarket_order_products">
<?php
echo $this->loadTemplate('products');
?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan="2">
<?php
	JPluginHelper::importPlugin('hikashop');
	JPluginHelper::importPlugin('hikamarket');
	JFactory::getApplication()->triggerEvent('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_frontvendor_show'));
?>
		</td>
	</tr>
<?php if(hikamarket::acl('order/edit/history') && !empty($this->order->history)) { ?>
	<tr>
		<td colspan="2">
			<fieldset class="hikam_field" id="hikamarket_order_field_history">
<?php
echo $this->loadTemplate('history');
?>
			</fieldset>
		</td>
	</tr>
<?php }?>
</table>
