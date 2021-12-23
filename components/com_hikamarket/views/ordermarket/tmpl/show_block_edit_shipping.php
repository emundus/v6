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
if(!$this->editable_order || !hikamarket::acl('order/edit/shipping'))
	return;
?>
<div class="hikamarket_ajax_loading_elem"></div>
<div class="hikamarket_ajax_loading_spinner"></div>
<?php
if(empty($this->order->warehouses) || count($this->order->warehouses) == 1) {
?>
<dl class="hikam_options">
	<dt><label><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></label></dt>
	<dd><?php
		$shipping_namekey = '';
		if(!empty($this->order->order_shipping_method))
			$shipping_namekey = $this->order->order_shipping_method . '_' . $this->order->order_shipping_id;
		echo $this->nameboxType->display(
			'order[shipping][namekey]',
			$shipping_namekey,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'shipping_methods',
			array(
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
			)
		);
	?></dd>

	<dt><label><?php echo JText::_('SHIPPING_TAX'); ?></label></dt>
	<dd><?php
		echo $this->ratesType->display('order[shipping][tax_namekey]', @$this->order->order_shipping_tax_namekey, @$this->order->order_shipping_tax_rate, 'onchange="window.orderMgr.updateTaxValueFields(\'ordershipping\');"');
	?></dd>

	<dt><label><?php echo JText::_('HIKASHOP_SHIPPING'); ?></label></dt>
	<dd>
		<input type="text" id="ordershipping_value" name="order[shipping][value]" onchange="window.orderMgr.updateTaxValueFields('ordershipping');" value="<?php echo $this->order->order_shipping_price; ?>"/> <?php echo $this->order->currency->currency_symbol . ' (' . $this->order->currency->currency_code . ')'; ?><br/>
		<div>
			<span id="ordershipping_value_price"><?php echo ($this->order->order_shipping_price - $this->order->order_shipping_tax); ?></span>
			+
			<span id="ordershipping_value_tax"><?php echo $this->order->order_shipping_tax; ?></span>
		</div>
		<input type="hidden" id="ordershipping_tax" name="order[shipping][tax]" value="<?php echo (float)$this->order->order_shipping_tax; ?>"/>
	</dd>
</dl>
<?php
} else {
	foreach($this->order->warehouses as $key => $warehouse) {
		$price = $this->order->order_shipping_params->prices[$key];
		list($shipping_id, $key) = explode('@', $key, 2);

		$tax_namekey = array_keys($price->taxes);
		$tax_namekey = reset($tax_namekey);
		if(empty($tax_namekey))
			$tax_namekey = @$this->order->order_shipping_tax_namekey;

		$tax_rate = @$this->order->order_shipping_tax_rate;
		if(!empty($price->tax) && ($price->price_with_tax - $price->tax) != 0)
			$tax_rate = $price->tax / ($price->price_with_tax - $price->tax);
?>
<div class="order_shipping_warehouse">
	<h4><?php
		if(!empty($warehouse->name))
			echo $warehouse->name;
		else
			echo $warehouse->warehouse_name;
	?></h4>
	<dl class="hikam_options">
		<dt><label><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></label></dt>
		<dd><?php
			$shipping_namekey = '';
			if(isset($this->order->shippings[(int)$shipping_id]))
				$shipping_namekey = $this->order->shippings[(int)$shipping_id]->shipping_type . '_' . $shipping_id;
			echo $this->nameboxType->display(
				'order[shippings]['.$key.'][namekey]',
				$shipping_namekey,
				hikamarketNameboxType::NAMEBOX_SINGLE,
				'shipping_methods',
				array(
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
				)
			);
		?></dd>

		<dt><label><?php echo JText::_('SHIPPING_TAX'); ?></label></dt>
		<dd><?php
			echo $this->ratesType->display('order[shippings]['.$key.'][tax_namekey]', $tax_namekey, $tax_rate, 'onchange="window.orderMgr.updateTaxValueFields(\'ordershippings'.$key.'\');"');
		?></dd>

		<dt><label><?php echo JText::_('HIKASHOP_SHIPPING'); ?></label></dt>
		<dd>
			<input type="text" id="ordershippings<?php echo $key; ?>_value" name="order[shippings][<?php echo $key; ?>][value]" onchange="window.orderMgr.updateTaxValueFields('ordershippings<?php echo $key; ?>');" value="<?php echo $price->price_with_tax; ?>"/> <?php echo $this->order->currency->currency_symbol . ' (' . $this->order->currency->currency_code . ')'; ?><br/>
			<div>
				<span id="ordershippings<?php echo $key; ?>_value_price"><?php echo ($price->price_with_tax - $price->tax); ?></span>
				+
				<span id="ordershippings<?php echo $key; ?>_value_tax"><?php echo $price->tax; ?></span>
			</div>
			<input type="hidden" id="ordershippings<?php echo $key; ?>_tax" name="order[shippings][<?php echo $key; ?>][tax]" value="<?php echo (float)$price->tax; ?>"/>
		</dd>
	</dl>
</div>
<?php
	}
}
?>
	<div style="clear:both;margin-top:4px;"></div>
	<div style="float:right">
		<button onclick="return window.orderMgr.submitDetails(this, 'shipping');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
	</div>
	<button onclick="return window.orderMgr.showEditDetails(this, 'shipping', false);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
<div style="clear:both"></div>
