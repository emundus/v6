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
if($this->payment_method == 'paypal') {
	hikaInput::get()->set('noform', 1);
?>
	<form id="hikashop_paypal_form" name="hikashop_paypal_form" action="<?php echo $this->return_url;?>" method="POST" target="_blank" onsubmit="window.localPage.afterSubmit();">
<?php
	foreach($this->vars as $name => $value ) {
		echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
	}
?>
		<div><?php
			echo JText::_('PAYPAL_PAYMENT_OPEN_NEW_WINDOW');
		?></div>
		<div id="hikashop_paypal_end_image" class="hikashop_paypal_end_image">
			<input id="hikashop_paypal_button" type="submit" class="hikabtn hikabtn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
	</form>
	<script type="text/javascript">
	<!--
	if(!window.localPage)
		window.localPage = {};
	window.localPage.afterSubmit = function() {
		setTimeout(function() {
			window.parent.hikamarket.submitBox({result:"pending..."});
		}, 2000);
	}
	</script>
<?php
	return;
}

?>
<fieldset>
	<div class="toolbar" id="toolbar">
		<div style="float:right">
			<button class="hikabtn hikabtn-success" style="margin:5px;" type="button" onclick="submitbutton('paymanual');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
		</div>
		<button class="hikabtn hikabtn-danger" style="margin:5px;" type="button" onclick="if(window.parent) { window.parent.hikamarket.closeBox(); } return false;"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ?></button>
	</div>
</fieldset>
<form id="adminForm" name="adminForm" action="<?php echo hikamarket::completeLink('vendor&task=paymanual'); ?>" method="POST">
	<dl class="hikam_options">
		<dt><?php
			echo JText::_('ORDER_NUMBER');
		?></dt>
		<dd><?php
			echo $this->escape($this->order->order_number);
		?></dd>
		<dt><?php
			echo JText::_('HIKASHOP_TOTAL');
		?></dt>
		<dd><?php
			echo $this->currencyClass->format($this->order->order_full_price, $this->order->order_currency_id);
		?></dd>
		<dt><?php
			echo JText::_('HIKA_VENDOR');
		?></dt>
		<dd><?php
			echo $this->escape($this->vendor->vendor_name);
		?></dd>
		<dt><?php
			echo JText::_('ORDER_STATUS');
		?></dt>
		<dd><?php
			echo $this->escape($this->order->order_status);
		?></dd>
		<dt><?php
			echo JText::_('NEW_ORDER_STATUS');
		?></dt>
		<dd><?php
			echo $this->escape($this->confirmed_status);
		?></dd>
		<dt><?php
			echo JText::_('NOTIFY_VENDOR');
		?></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[notify]', '', 0);
		?></dd>
	</dl>
	<input type="hidden" name="payment_method" value="manual" />
	<input type="hidden" name="vendor_id" value="<?php echo (int)$this->vendor->vendor_id; ?>" />
	<input type="hidden" name="order_id" value="<?php echo (int)$this->order->order_id; ?>" />
	<input type="hidden" name="data[validation]" value="1" />

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="paymanual" />
	<input type="hidden" name="ctrl" value="vendor" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
