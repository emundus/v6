<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('CUSTOMER')?></legend>
<div class="hika_edit">
<?php
if(hikashop_level(1)) {
	echo $this->popup->display(
		'<i class="fas fa-plus"></i> ' . JText::_('NEW_GUEST_USER'),
		'NEW_GUEST_USER',
		hikashop_completeLink('order&task=add_guest&order_id='.$this->order->order_id, true),
		'hikashop_setguestcustomer_popup',
		750, 460, 'onclick="return window.orderMgr.setCustomer(this);" style="margin-right:10px" class="btn btn-primary" title="'. JText::_('NEW_GUEST_USER') .'"', '', 'link'
	);
}
?>
<?php
echo $this->popup->display(
	'<i class="fas fa-pen"></i> ' . JText::_('SELECT_ANOTHER_USER'),
	'HIKA_SET_ORDER_CUSTOMER',
	hikashop_completeLink('user&task=selection&single=1&confirm=0&after=order|customer_set&afterParams=order_id|'.$this->order->order_id, true),
	'hikashop_setcustomer_popup',
	750, 460, 'onclick="return window.orderMgr.setCustomer(this);" class="btn btn-primary" title="'. JText::_('SELECT_ANOTHER_USER') .'"', '', 'link'
);
?>
</div>
<script type="text/javascript">
<!--
window.orderMgr.setCustomer = function(el) {
	var w = window;
	w.hikashop.submitFct = function(data) {
		var w = window, o = w.Oby;
		w.hikashop.closeBox();
		window.orderMgr.updateUser();
		window.orderMgr.updateBilling();
		window.orderMgr.updateShipping();
		window.orderMgr.updateHistory();
		o.fireAjax('hikashop.order_update', {el: 'customer', obj: data});
	};
	w.hikashop.openBox(el);
	return false;
}
window.orderMgr.updateUser = function() {
	window.hikashop.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask=user&cid='.$this->order->order_id, true, true, true); ?>', {update: 'hikashop_order_field_user'});
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
?>	<tr class="hikashop_order_customer_<?php echo $key; ?>">
		<td class="key"><label><?php echo JText::_($content['title']); ?></label></td>
		<td><?php echo $content['data'] ?></td>
	</tr>
<?php
	}
}
?>
</table>
