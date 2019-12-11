<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
<!--
window.orderMgr = {
	updateAdditionnal: function(){},
	updateHistory: function(){},
	updateShipping: function(){},
	updateBilling: function(){}
};
<?php if(!empty($this->extra_data['js'])) { echo $this->extra_data['js']; } ?>
//-->
</script>
<div class="iframedoc" id="iframedoc"></div>
<div id="page-order" class="hk-row-fluid">
	<div class="hkc-md-6">
			<fieldset class="hika_field adminform" id="hikashop_order_field_general"><?php
				echo $this->loadTemplate('general');
			?></fieldset>
			<fieldset class="hika_field adminform">
				<legend><?php echo JText::_('CUSTOMER')?></legend>
				<div class="hika_edit">
					<?php
						echo $this->popup->display(
							'<i class="fas fa-pen"></i> ' . JText::_('HIKA_EDIT'),
							'HIKA_SET_ORDER_CUSTOMER',
							hikashop_completeLink('user&task=selection&single=1&confirm=0&after=order|customer_set&afterParams=order_id|'.$this->order->order_id, true),
							'hikashop_setcustomer_popup',
							750, 460, 'onclick="return window.orderMgr.setCustomer(this);" class="btn btn-primary" title="'. JText::_('HIKA_EDIT') .'"', '', 'link'
						);
					?>
				</div>
<script type="text/javascript">
<!--
window.orderMgr.setCustomer = function(el) {
	var w = window;
	w.hikashop.submitFct = function(data) {
		var d = document, input = null, inputs = {id:'hikashop_order_customer_id',name:'hikashop_order_customer_name',user_email:'hikashop_order_customer_email'};
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
				if(up == 'shipping') window.orderMgr.updateShipping();
			}
		}
		w.Oby.fireAjax('hikashop.order_update', {el: 'customer', obj: data});
	};
	w.hikashop.openBox(el);
	return false;
}
//-->
</script>
				<table class="admintable table">
					<tr class="hikashop_order_customer_name">
						<td class="key"><label><?php echo JText::_('HIKA_NAME');?></label></td>
						<td><span id="hikashop_order_customer_name"><?php echo @$this->order->customer->name; ?></span></td>
					</tr>
					<tr class="hikashop_order_customer_email">
						<td class="key"><label><?php echo JText::_('HIKA_EMAIL');?></label></td>
						<td><span id="hikashop_order_customer_email"><?php echo @$this->order->customer->user_email; ?></span></td>
					</tr>
					<tr class="hikashop_order_customer_id">
						<td class="key"><label><?php echo JText::_('ID');?></label></td>
						<td>
							<?php
							if (isset($this->order->customer->user_id))
							{
								echo '<span id="hikashop_order_customer_id"> '.@$this->order->customer->user_id.' </span>';
								echo '<a href="'.hikashop_completeLink('user&task=edit&cid[]='. $this->order->customer->user_id.'&order_id='.$this->order->order_id).'">';
								echo ' <i class="fa fa-chevron-right"></i>';
								echo '</a>';
							}
							else
							{
								echo '<span id="hikashop_order_customer_id"> </span>';
								echo '<a href="">';
							}
							?>
						</td>
					</tr>
<?php
	if(!empty($this->extra_data['user'])) {
		foreach($this->extra_data['user'] as $key => $content) {
?>					<tr class="hikashop_order_customer_<?php echo $key; ?>">
						<td class="key"><label><?php echo JText::_($content['title']); ?></label></td>
						<td><?php echo $content['data'] ?></td>
					</tr>
<?php
		}
	}
?>
				</table>
			</fieldset>
	</div>
	<div class="hkc-md-6">
			<fieldset class="hika_field adminform" id="hikashop_order_field_additional">
<?php
echo $this->loadTemplate('additional');
?>
			</fieldset>
<?php if(!empty($this->order->partner)){ ?>
		<fieldset class="hika_field adminform" id="htmlfieldset_partner">
			<legend><?php echo JText::_('PARTNER'); ?></legend>
				<div class="hika_edit"><?php
					echo $this->popup->display(
						'<i class="fas fa-pen"></i> ' . JText::_('HIKA_EDIT'),
						'HIKA_EDIT',
						hikashop_completeLink('order&task=partner&order_id='.$this->order->order_id,true),
						'hikashop_edit_partner',
						760, 480, 'class="btn btn-primary"', '', 'link'
					);
				?></div>
				<table class="admintable table">
					<tr>
						<td class="key"><?php echo JText::_('PARTNER_EMAIL'); ?></td>
						<td>
							<?php echo $this->order->partner->user_email;?>
							<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='. $this->order->partner->user_id.'&order_id='.$this->order->order_id); ?>">
								<i class="fa fa-chevron-right"></i>
							</a>
						</td>
					</tr>
<?php if(!empty($this->order->partner->name)){ ?>
					<tr>
						<td class="key"><?php echo JText::_('PARTNER_NAME'); ?></td>
						<td><?php
							echo $this->order->partner->name;
						?></td>
					</tr>
<?php } ?>
					<tr>
						<td class="key"><?php echo JText::_('PARTNER_FEE'); ?></td>
						<td><?php echo $this->currencyHelper->format($this->order->order_partner_price,$this->order->order_partner_currency_id); ?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PARTNER_PAYMENT_STATUS'); ?></td>
						<td><?php
							if(empty($this->order->order_partner_paid)) {
								echo '<span class="label label-warning">'.JText::_('NOT_PAID').'</span>';
								if(!HIKASHOP_BACK_RESPONSIVE)
									echo ' <i class="fa fa-times-circle"></i>';
							} else {
								echo '<span class="label label-success">'.JText::_('PAID').'</span>';
								if(!HIKASHOP_BACK_RESPONSIVE)
									echo ' <i class="fa fa-check"></i>';
							}
						?></td>
					</tr>
				</table>
			</fieldset>
<?php } ?>
	</div>
</div>
<div class="hk-row-fluid">
	<div class="hkc-md-6">
			<fieldset class="hika_field adminform" id="hikashop_order_field_billing_address">
<?php
	$this->type = 'billing';
	echo $this->loadTemplate('address');
?>
			</fieldset>
	</div>
	<div class="hkc-md-6">
			<fieldset class="hika_field adminform" id="hikashop_order_field_shipping_address">
<?php
	if(empty($this->order->override_shipping_address)) {
		$this->type = 'shipping';
		echo $this->loadTemplate('address');
	} else {
		echo $this->order->override_shipping_address;
	}

?>
			</fieldset>
	</div>
</div>
			<fieldset class="hika_field adminform" id="hikashop_order_products">
<?php
echo $this->loadTemplate('products');
?>
			</fieldset>
<?php
	JPluginHelper::importPlugin('hikashop');
	$app = JFactory::getApplication();
	$app->triggerEvent('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_back_show'));
?>
			<fieldset class="hika_field adminform" id="hikashop_order_field_history">
<?php
echo $this->loadTemplate('history');
?>
			</fieldset>
