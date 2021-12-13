<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
<?php
$show_url = 'order&task=show&subtask=general&cid='.$this->order->order_id;
$save_url = 'order&task=save&subtask=general&cid='.$this->order->order_id;
$update_url = 'order&task=edit&subtask=general&cid='.$this->order->order_id;
if(!isset($this->edit) || $this->edit !== true ) {
	if(hikamarket::acl('order/edit/general')) {
?>		<div class="hikam_edit"><a href="<?php echo hikamarket::completeLink($update_url, true);?>" onclick="return window.hikamarket.get(this,'hikamarket_order_field_general');"><i class="fas fa-pencil-alt"></i><span><?php echo JText::_('HIKA_EDIT'); ?></span></a></div>
<?php
	}
} else {
?>		<div class="hikam_edit">
			<a href="<?php echo hikamarket::completeLink($save_url, true);?>" onclick="return window.hikamarket.form(this,'hikamarket_order_field_general');"><i class="fas fa-check"></i><span><?php echo JText::_('HIKA_SAVE'); ?></span></a>
			<a href="<?php echo hikamarket::completeLink($show_url, true);?>" onclick="return window.hikamarket.get(this,'hikamarket_order_field_general');"><i class="far fa-times-circle"></i><span><?php echo JText::_('HIKA_CANCEL'); ?></span></a>
		</div>
<?php
}
?>
	<dl class="hikam_options">
		<dt class="hikamarket_order_number"><label><?php echo JText::_('ORDER_NUMBER'); ?></label></dt>
		<dd class="hikamarket_order_number"><span><?php echo $this->order->order_number; ?></span></dd>

		<dt class="hikamarket_order_invoicenumber"><label><?php echo JText::_('INVOICE_NUMBER'); ?></label></dt>
		<dd class="hikamarket_order_invoicenumber"><span><?php echo @$this->order->order_invoice_number; ?></span></dd>

		<dt class="hikamarket_order_status"><label for="data[order][order_status]"><?php echo JText::_('ORDER_STATUS'); ?></label></dt>
		<dd class="hikamarket_order_status"><?php
			if(!isset($this->edit) || $this->edit !== true ) {
				?><span><?php echo hikamarket::orderStatus($this->order->order_status); ?></span><?php
			} else {
				$extra = 'onchange="window.orderMgr.status_changed(this);"';
				echo $this->order_status->display('data[order][order_status]', $this->order->order_status, $extra);
			}
		?></dd>
<?php
if(isset($this->edit) && $this->edit === true && ($this->vendor->vendor_id == 0 || $this->vendor->vendor_id == 1) && hikamarket::acl('order/edit/notify')) {
?>
		<dt id="hikamarket_order_notify_lbl" style="display:none;" class="hikamarket_order_notify"><label for="data[notify]"><?php echo JText::_('NOTIFICATION'); ?></label></dt>
		<dd id="hikamarket_order_notify_val" style="display:none;" class="hikamarket_order_notify"><input type="checkbox" id="data[notify]" name="data[notify]"/><label style="display:inline-block" for="data[notify]"><?php echo JText::_('NOTIFY_CUSTOMER'); ?></label></dd>
<?php
}
?>
		<dt class="hikamarket_order_created"><label><?php echo JText::_('DATE'); ?></label></dt>
		<dd class="hikamarket_order_created"><span><?php echo hikamarket::getDate($this->order->order_created,'%Y-%m-%d %H:%M');?></span></dd>

		<dt class="hikamarket_order_id"><label><?php echo JText::_('ID'); ?></label></dt>
		<dd class="hikamarket_order_id"><span><?php echo $this->order->order_id; ?></span></dd>
<?php
if(isset($this->edit) && $this->edit === true ) {
?>
		<dt class="hikamarket_order_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikamarket_order_history">
			<span><input onchange="window.orderMgr.general_history_changed(this);" type="checkbox" id="hikamarket_history_general_store" name="data[history][store_data]" value="1"/><label for="hikamarket_history_general_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikamarket_history_general_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
<?php
}
?>
	</dl>
<?php
if(isset($this->edit) && $this->edit === true ) {
?>
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

	<input type="hidden" name="data[general]" value="1"/>
	<?php echo JHTML::_('form.token')."\r\n";
}
