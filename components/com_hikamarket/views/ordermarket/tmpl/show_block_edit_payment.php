<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!$this->editable_order || !hikamarket::acl('order/edit/payment'))
	return;
?>
<div class="hikamarket_ajax_loading_elem"></div>
<div class="hikamarket_ajax_loading_spinner"></div>

<dl class="hikam_options">
	<dt><label><?php echo JText::_('PAYMENT_METHOD'); ?></label></dt>
	<dd><?php
		$payment_namekey = '';
		if(!empty($this->order->order_payment_method))
			$payment_namekey = $this->order->order_payment_method . '_' . $this->order->order_payment_id;
		echo $this->nameboxType->display(
			'order[payment][namekey]',
			$payment_namekey,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'payment_methods',
			array(
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
			)
		);
	?></dd>

	<dt><label><?php echo JText::_('PAYMENT_TAX'); ?></label></dt>
	<dd><?php
		echo $this->ratesType->display('order[payment][tax_namekey]', @$this->order->order_payment_tax_namekey, @$this->order->order_payment_tax_rate, 'onchange="window.orderMgr.updateTaxValueFields(\'orderpayment\');"');
	?></dd>

	<dt><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></dt>
	<dd>
		<input type="text" id="orderpayment_value" name="order[payment][value]" onchange="window.orderMgr.updateTaxValueFields('orderpayment');" value="<?php echo $this->order->order_payment_price; ?>"/> <?php echo $this->order->currency->currency_symbol . ' (' . $this->order->currency->currency_code . ')'; ?><br/>
		<div>
			<span id="orderpayment_value_price"><?php echo ($this->order->order_payment_price - $this->order->order_payment_tax); ?></span>
			+
			<span id="orderpayment_value_tax"><?php echo $this->order->order_payment_tax; ?></span>
		</div>
		<input type="hidden" id="orderpayment_tax" name="order[payment][tax]" value="<?php echo (float)$this->order->order_payment_tax; ?>"/>
	</dd>
</dl>

	<div style="clear:both;margin-top:4px;"></div>
	<div style="float:right">
		<button onclick="return window.orderMgr.submitDetails(this, 'payment');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
	</div>
	<button onclick="return window.orderMgr.showEditDetails(this, 'payment', false);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
<div style="clear:both"></div>
