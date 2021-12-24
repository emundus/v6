<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order&task=customer_save') ;?>" method="post" name="hikamarket_form" id="hikamarket_form">
<div class="hikam_confirm">
	<?php echo JText::_('HIKA_CONFIRM_USER')?><br/>
	<table class="hikam_options">
		<tbody>
			<tr>
				<td class="key"><label><?php echo JText::_('HIKA_NAME'); ?></label></td>
				<td id="hikamarket_order_customer_name"><?php echo $this->rows->name; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('HIKA_EMAIL'); ?></label></td>
				<td id="hikamarket_order_customer_email"><?php echo $this->rows->email; ?></td>
			</tr>
			<tr>
				<td class="key"><label><?php echo JText::_('ID'); ?></label></td>
				<td id="hikamarket_order_customer_id"><?php echo $this->rows->user_id; ?></td>
			</tr>
<?php if(hikamarket::acl('order/edit/billingaddress')) { ?>
			<tr>
				<td class="key"><label><?php echo JText::_('SET_USER_ADDRESS'); ?></label></td>
				<td><?php echo JHTML::_('hikaselect.booleanlist', 'set_user_address', '', 0); ?></td>
			</tr>
<?php } ?>
<?php if(hikamarket::acl('order/edit/history')) { ?>
			<tr>
				<td class="key"><label><?php echo JText::_('HISTORY'); ?></label></td>
				<td>
					<span><input onchange="window.orderMgr.orderadditional_history_changed(this);" type="checkbox" id="hikamarket_history_orderadditional_store" name="data[history][store_data]" value="1"/><label for="hikamarket_history_orderadditional_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
					<textarea id="hikamarket_history_orderadditional_msg" name="data[history][history_data]" style="display:none;"></textarea>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<input type="hidden" name="data[order][order_user_id]" value="<?php echo $this->rows->user_id; ?>"/>
	<input type="hidden" name="cid" value="<?php echo $this->order_id; ?>"/>
	<input type="hidden" name="order_id" value="<?php echo $this->order_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="customer_save" />
	<input type="hidden" name="finalstep" value="1" />
	<input type="hidden" name="single" value="1" />
	<input type="hidden" name="ctrl" value="order" />
	<input type="hidden" name="tmpl" value="component" />
	<?php echo JHTML::_('form.token'); ?>
	<div class="hikam_confirm_btn">
		<button onclick="hikamarket.submitform('customer_save', 'hikamarket_form');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <span><?php echo Jtext::_('HIKA_OK'); ?></span></button>
	</div>
</div>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.orderadditional_history_changed = function(el) {
	var fields = ['hikamarket_history_orderadditional_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikamarket.setArrayDisplay(fields, displayValue);
}
</script>
</form>
