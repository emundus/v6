<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h2><?php echo JText::sprintf('PAY_ORDER_X_NOW', $this->order->order_number); ?></h2>
<?php
global $Itemid;
$url_itemid = (!empty($Itemid) ? '&Itemid=' . $Itemid : '');
?>
<form action="<?php echo hikashop_completeLink('order&task=pay&order_id='.$this->order->order_id.$url_itemid); ?>" method="post">
<!-- TOTAL AMOUNT -->
<dl class="hika_options large">
	<dt><?php echo JText::_('HIKASHOP_TOTAL'); ?></dt>
	<dd><?php
		echo $this->currencyClass->format($this->order->order_full_price, $this->order->order_currency_id);
	?></dd>
</dl>
<!-- EO TOTAL AMOUNT -->
<!-- PAYMENT METHOD -->
<?php if(empty($this->new_payment_method)) { ?>

<dl class="hika_options large">
	<dt><?php echo JText::_('PAYMENT_METHOD'); ?></dt>
	<dd><?php
		echo $this->paymentPluginType->display('new_payment_method', $this->order->order_payment_method, $this->order->order_payment_id, false);
	?></dd>
</dl>
<?php } ?>
<!-- EO PAYMENT METHOD -->
<?php
	if(!empty($this->new_payment_method)) {
		if(!empty($this->paymentMethod->ask_cc)) {
			hikashop_loadJsLib('creditcard');
?>
<dl class="hika_options large">
<!-- CREDIT CARD OWNER -->
<?php if(!empty($this->paymentMethod->ask_owner)){ ?>
	<dt><label for="hikashop_credit_card_owner_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>"><?php echo JText::_('CREDIT_CARD_OWNER'); ?></label></dt>
	<dd><input type="text" autocomplete="off" style="text-align: center;" id="hikashop_credit_card_owner_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>" name="hikashop_credit_card_owner[<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>]" value="" /></dd>
<?php } ?>
<!-- EO CREDIT CARD OWNER -->
<!-- CREDIT CARD TYPE -->
<?php if(!empty($this->paymentMethod->ask_cctype)){ ?>
	<dt><label for="hikashop_credit_card_type<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>"><?php echo JText::_('CARD_TYPE'); ?></label></dt>
	<dd><?php
		$values = array();
		foreach($this->paymentMethod->ask_cctype as $k => $v){
			$values[] = JHTML::_('select.option', $k, $v);
		}
		echo JHTML::_('select.genericlist', $values, "hikashop_credit_card_type[".$this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id.']', '', 'value', 'text', $cc_type );
	?></dd>
<?php } ?>
<!-- EO CREDIT CARD TYPE -->
<!-- CREDIT CARD NUMBER -->
	<dt><label for="hikashop_credit_card_number_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>"><?php echo JText::_('CREDIT_CARD_NUMBER'); ?></label></dt>
	<dd><input type="text" autocomplete="off" name="hikashop_credit_card_number[<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>]" id="hikashop_credit_card_number_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>" value="" onchange="if(!hikashopCheckCreditCard(this.value)){ this.value='';}"/></dd>
<!-- EO CREDIT CARD NUMBER -->
<!-- CREDIT CARD DATE -->
	<dt><label for="hikashop_credit_card_month_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>"><?php echo JText::_('EXPIRATION_DATE'); ?></label></dt>
	<?php $mm = JText::_('CC_MM'); if($mm == 'CC_MM') $mm = JText::_('MM'); ?>
	<dd>
		<input style="text-align: center;" autocomplete="off" type="text" id="hikashop_credit_card_month_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>" name="hikashop_credit_card_month[<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>]" onkeyup="moveOnMax(this,'hikashop_credit_card_year_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>');" onfocus="this.value='';" maxlength="2" size="2" value="<?php echo $mm;?>" />
		/
		<input style="text-align: center;" autocomplete="off" type="text" id="hikashop_credit_card_year_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>" name="hikashop_credit_card_year[<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>]" onfocus="this.value='';" maxlength="2" size="2" value="<?php echo JText::_('YY');?>" onchange="var month = document.getElementById('hikashop_credit_card_month_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>'); if(!hikashopValidateExpDate(month.value,this.value)){this.value='';month.value='';}" />
	</dd>
<!-- EO CREDIT CARD DATE -->
<!-- CREDIT CARD CVV -->
<?php if(!empty($this->paymentMethod->ask_ccv)){ ?>
	<dt><label for="hikashop_credit_card_CCV_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>" data-original-title="<?php echo JText::_('CVC_TOOLTIP_TEXT'); ?>" data-toggle="hk-tooltip"><?php echo JText::_('CARD_VALIDATION_CODE'); ?></label></dt>
	<dd><input type="text" autocomplete="off" style="text-align: center;" id="hikashop_credit_card_CCV_<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>" name="hikashop_credit_card_CCV[<?php echo $this->paymentMethod->payment_type.'_'.$this->paymentMethod->payment_id;?>]" maxlength="4" size="4" value="" /></dd>
<?php } ?>
<!-- EO CREDIT CARD CVV -->
</dl>
<?php
	}
?>
<!-- PAYMENT METHOD CUSTOM HTML -->
<?php
	if(!empty($this->paymentMethod->custom_html)) {
?>
	<div class="hikashop_checkout_payment_custom">
<?php
		echo $this->checkoutHelper->getCustomHtml($this->paymentMethod->custom_html, 'payment[custom]');
?>
	</div>
	<input type="hidden" name="payment_custom_html" value="1"/>
<?php
	}
?>
<!-- EO PAYMENT METHOD CUSTOM HTML -->
	<input type="hidden" name="new_payment_method" value="<?php echo $this->new_payment_method; ?>"/>
<?php } ?>

<div class="hikashop_checkout_buttons">
	<div class="buttons_right">
<!-- NEXT BUTTON -->
		<button type="submit" class="btn btn-primary"><?php echo JText::_('HIKA_NEXT'); ?></button>
<!-- EO NEXT BUTTON -->
	</div>
	<div style="clear:both;"></div>
</div>

	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="pay" />
	<input type="hidden" name="order_id" value="<?php echo $this->order->order_id; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="order_token" value="<?php echo hikaInput::get()->getVar('order_token'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
