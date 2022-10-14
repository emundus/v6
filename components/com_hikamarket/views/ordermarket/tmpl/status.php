<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order&task=save&cid='.$this->order->order_id); ?>" method="post" name="adminForm" id="adminForm">
<dl class="hikam_options">
	<dt><?php echo JText::_('ORDER_STATUS'); ?></dt>
	<dd><span class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_',$this->order->order_status)); ?>"><?php
		echo hikamarket::orderStatus($this->order->order_status);
	?></span></dd>
	<dt><?php echo JText::_('ORDER_NEW_STATUS'); ?></dt>
	<dd><?php
		echo $this->order_status->display('order[general][order_status]', $this->order->order_status, 'onchange="window.orderMgr.status_changed(this);"', false, @$this->order_status_filters);
	?></dd>
<?php
if(($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1) && hikamarket::acl('order/edit/notify')) {
?>
	<dt id="hikamarket_order_notify_lbl" style="display:none;" class="hikamarket_order_notify"><label for="order[notify]"><?php echo JText::_('NOTIFICATION'); ?></label></dt>
	<dd id="hikamarket_order_notify_val" style="display:none;" class="hikamarket_order_notify"><input type="checkbox" id="order[notify]" value="1" name="order[notify]"/><label style="display:inline-block" for="order[notify]"><?php echo JText::_('NOTIFY_CUSTOMER'); ?></label></dd>
<?php
}
?>
</dl>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.status_changed = function(el) {
	var fields = ['hikamarket_order_notify_lbl', 'hikamarket_order_notify_val'], displayValue = '';
	if(el.value == '<?php echo $this->order->order_status; ?>')
		displayValue = 'none';
	window.hikamarket.setArrayDisplay(fields, displayValue);
};
window.orderMgr.general_history_changed = function(el) {
	var fields = ['hikamarket_history_general_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikamarket.setArrayDisplay(fields, displayValue);
};
</script>
	<input type="hidden" name="closepopup" value="1"/>
	<input type="hidden" name="cid" value="<?php echo (int)$this->order->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="ctrl" value="order" />
<?php if(hikaInput::get()->getCmd('tmpl', '') != '') { ?>
	<input type="hidden" name="tmpl" value="<?php echo $this->escape(hikaInput::get()->getCmd('tmpl')); ?>" />
<?php } ?>
	<?php echo JHTML::_('form.token'); ?>
</form>
