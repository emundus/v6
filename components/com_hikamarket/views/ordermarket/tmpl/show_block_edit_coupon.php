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
if(!$this->editable_order || !hikamarket::acl('order/edit/coupon'))
	return;
?>
<div class="hikamarket_ajax_loading_elem"></div>
<div class="hikamarket_ajax_loading_spinner"></div>

<dl class="hikam_options">
	<dt><label><?php echo JText::_('COUPON_CODE'); ?></label></dt>
	<dd><input type="text" name="order[coupon][code]" value="<?php echo $this->escape(@$this->order->order_discount_code); ?>"/></dd>

	<dt><label><?php echo JText::_('COUPON_TAX'); ?></label></dt>
	<dd><?php
		echo $this->ratesType->display('order[coupon][tax_namekey]', @$this->order->order_discount_tax_namekey, @$this->order->order_discount_tax_rate, 'onchange="window.orderMgr.updateTaxValueFields(\'ordercoupon\');"');
	?></dd>

	<dt><label><?php echo JText::_('COUPON_VALUE'); ?></label></dt>
	<dd>
		<input type="text" id="ordercoupon_value" name="order[coupon][value]" onchange="window.orderMgr.updateTaxValueFields('ordercoupon');" value="<?php echo $this->order->order_discount_price; ?>"/> <?php echo $this->order->currency->currency_symbol . ' (' . $this->order->currency->currency_code . ')'; ?><br/>
		<div>
			<span id="ordercoupon_value_price"><?php echo ($this->order->order_discount_price - $this->order->order_discount_tax); ?></span>
			+
			<span id="ordercoupon_value_tax"><?php echo $this->order->order_discount_tax; ?></span>
		</div>
		<input type="hidden" id="ordercoupon_tax" name="order[coupon][tax]" value="<?php echo (float)$this->order->order_discount_tax; ?>"/>
	</dd>
</dl>

	<div style="clear:both;margin-top:4px;"></div>
	<div style="float:right">
		<button onclick="return window.orderMgr.submitDetails(this, 'coupon');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
	</div>
	<button onclick="return window.orderMgr.showEditDetails(this, 'coupon', false);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
<div style="clear:both"></div>
