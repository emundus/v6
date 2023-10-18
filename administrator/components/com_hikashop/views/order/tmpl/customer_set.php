<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikashop_completeLink('order&task=customer_save') ;?>" method="post" name="hikashop_form" id="hikashop_form">
<div class="hika_confirm">
	<?php echo JText::_('HIKA_CONFIRM_USER')?><br/>
	<table class="admintable table hika_options">
		<tbody>
			<tr>
				<td class="key"><label><?php echo JText::_('HIKA_NAME'); ?></label></td>
				<td id="hikashop_order_customer_name"><?php echo $this->rows->name; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('HIKA_EMAIL'); ?></label></td>
				<td id="hikashop_order_customer_email"><?php echo $this->rows->user_email; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('ID'); ?></label></td>
				<td id="hikashop_order_customer_id"><?php echo $this->rows->user_id; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('SET_USER_ADDRESS'); ?></label></td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', 'set_user_address', 'onchange="window.orderMgr.addressSwitch(this);"', 0); ?></td>
			</tr>
			<tr class="address_selector" style="display:none;">
				<td class="key"><label><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></label></td>
				<td><?php
$values = array();
$default = 0;
$values[] = JHTML::_('select.option', 0, JText::_('NO_ADDRESS'));
foreach($this->addresses as $address) {
	if(empty($address))
		continue;
	if(!empty($address->address_type) && !in_array($address->address_type, array('both', '', 'billing')))
		continue;
	if($address->address_default)
		$default = $address->address_id;
	$addr = $this->addressClass->miniFormat($address);
	$values[] = JHTML::_('select.option', $address->address_id, $addr);
}
echo JHTML::_('select.genericlist', $values, 'billing_address', 'class="hikashop_field_dropdown"', 'value', 'text', $default);
				?></td>
			</tr>
			<tr class="address_selector" style="display:none;">
				<td class="key"><label><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></label></td>
				<td><?php
$values = array();
$default = 0;

$values[] = JHTML::_('select.option', 0, JText::_('NO_ADDRESS'));
foreach($this->addresses as $address) {
	if(empty($address))
		continue;
	if(!empty($address->address_type) && !in_array($address->address_type, array('both', '', 'shipping')))
		continue;
	if($address->address_default)
		$default = $address->address_id;
	$addr = $this->addressClass->miniFormat($address);
	$values[] = JHTML::_('select.option', $address->address_id, $addr);
}
echo JHTML::_('select.genericlist', $values, 'shipping_address', 'class="hikashop_field_dropdown"', 'value', 'text', $default);
			 ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('HISTORY'); ?></label></td>
				<td>
					<span><input onchange="window.orderMgr.orderadditional_history_changed(this);" type="checkbox" id="hikashop_history_orderadditional_store" name="data[history][store_data]" value="1"/><label for="hikashop_history_orderadditional_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
					<textarea id="hikashop_history_orderadditional_msg" name="data[history][history_data]" style="display:none;"></textarea>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="data[order][order_user_id]" value="<?php echo $this->rows->user_id; ?>"/>
	<input type="hidden" name="cid" value="<?php echo $this->order_id; ?>"/>
	<input type="hidden" name="order_id" value="<?php echo $this->order_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="customer_save" />
	<input type="hidden" name="finalstep" value="1" />
	<input type="hidden" name="single" value="1" />
	<input type="hidden" name="ctrl" value="order" />
	<input type="hidden" name="tmpl" value="component" />
	<?php echo JHTML::_('form.token'); ?>
	<div class="hika_confirm_btn">
		<button onclick="hikashop.submitform('customer_save', 'hikashop_form');" class="btn"><img src="<?php echo HIKASHOP_IMAGES ?>ok.png" style="vertical-align:middle" alt=""/> <span><?php echo Jtext::_('OK'); ?></span></button>
	</div>
</div>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.orderadditional_history_changed = function(el) {
	var fields = ['hikashop_history_orderadditional_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
window.orderMgr.addressSwitch = function(el) {
	var elements = document.querySelectorAll('.address_selector');
	var display = 'none';
	if(parseInt(el.value) == 1) {
		display = '';
	}
	for (var i = 0; i < elements.length; i++) {
		elements[i].style.display = display;
	}
}

</script>
</form>
