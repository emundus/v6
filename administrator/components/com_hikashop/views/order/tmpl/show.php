<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
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
<div id="page-order" class="hk-row-fluid hikashop_backend_order_show">
	<div class="hkc-md-6">
			<fieldset class="hika_field adminform" id="hikashop_order_field_general"><?php
				echo $this->loadTemplate('general');
			?></fieldset>
			<fieldset class="hika_field adminform" id="hikashop_order_field_user">
<?php
echo $this->loadTemplate('user');
?>
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
