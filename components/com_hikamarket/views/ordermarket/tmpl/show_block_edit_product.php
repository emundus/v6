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
if(!$this->editable_order || !hikamarket::acl('order/edit/products'))
	return false;

$orderProduct = $this->product;
$pid = isset($this->pid) ? $this->pid : (int)$product->order_product_id;
$showVendor = (hikamarket::level(1) && $this->order->order_type == 'sale' && $this->vendor->vendor_id <= 1);

$colspan = 4;
if($showVendor) $colspan++;

?>
<td colspan="<?php echo $colspan; ?>" data-order-product-id="<?php $pid; ?>">

	<dl class="hikam_options">
		<dt class="hikamarket_order_product_id"><label><?php echo JText::_('PRODUCT'); ?></label></dt>
		<dd class="hikamarket_order_product_id">
<?php
	echo $this->nameboxType->display(
		'order[products]['.$pid.'][id]',
		(int)@$orderProduct->product_id,
		hikamarketNameboxType::NAMEBOX_SINGLE,
		'product',
		array(
			'default_text' => '<em>' . JText::_('HIKA_NONE') . '</em>',
			'root' => $this->rootCategory,
			'variants' => true,
			'delete' => true,
		)
	);
?>
<?php if($pid == 0) { ?>
<script type="text/javascript">
(function() {
	var n = window.oNameboxes['order_products_0_id'];
	if(!n) return;
	n.register('set', function(p){ window.orderMgr.loadProductData(<?php echo (int)$pid; ?>, p.value); });
})();
</script>
<?php } else { ?>
	<a href="#load" onclick="return window.orderMgr.loadProductData(<?php echo (int)$pid; ?>, null);"><?php echo JText::_('HIKAM_LOAD_PRODUCT_DATA'); ?></a>
<?php } ?>
		</dd>

		<dt class="hikamarket_order_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikamarket_order_product_name">
			<input type="text" name="order[products][<?php echo $pid; ?>][name]" id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_name" value="<?php echo $this->escape(@$orderProduct->order_product_name); ?>" />
		</dd>

		<dt class="hikamarket_order_product_code"><label><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
		<dd class="hikamarket_order_product_code">
			<input type="text" name="order[products][<?php echo $pid; ?>][code]" id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_code" value="<?php echo $this->escape(@$orderProduct->order_product_code); ?>" />
		</dd>

		<dt class="hikamarket_order_product_tax"><label><?php echo JText::_('VAT'); ?></label></dt>
		<dd class="hikamarket_order_product_tax"><?php
			echo $this->ratesType->display('order[products]['.$pid.'][tax_namekey]', @$orderProduct->order_product_tax_info[0]->tax_namekey, @$orderProduct->tax_rate, 'onchange="window.orderMgr.updateTaxValueFields(\'hikamarket_order_'.$this->order->order_id.'_orderproduct_'.$pid.'\');"', 'hikamarket_order_'.$this->order->order_id.'_orderproduct_'.$pid.'_tax_namekey');
		?></dd>

		<dt class="hikamarket_order_product_price"><label><?php echo JText::_('UNIT_PRICE'); ?></label></dt>
		<dd class="hikamarket_order_product_price">
			<input type="text" name="order[products][<?php echo $pid; ?>][value]" id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_value" onchange="window.orderMgr.updateTaxValueFields('hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>');" value="<?php echo @$orderProduct->order_product_price + @$orderProduct->order_product_tax; ?>"/> <?php echo $this->order->currency->currency_symbol . ' (' . $this->order->currency->currency_code . ')'; ?><br/>
			<div>
				<span id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_value_price"><?php echo round((float)hikamarket::toFloat(@$orderProduct->order_product_price)); ?></span>
				+
				<span id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_value_tax"><?php echo round((float)hikamarket::toFloat(@$orderProduct->order_product_tax)); ?></span>
			</div>
			<input type="hidden" id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_tax" name="order[products][<?php echo $pid; ?>][tax]" value="<?php echo (float)@$orderProduct->order_product_tax; ?>"/>
		</dd>

		<dt class="hikamarket_order_product_quantity"><label><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
		<dd class="hikamarket_order_product_quantity">
			<input type="text" name="order[products][<?php echo $pid; ?>][qty]" id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_qty" value="<?php
				if(empty($pid)) {
					echo 1;
				} elseif(isset($orderProduct->order_product_quantity)) {
					echo (int)$orderProduct->order_product_quantity;
				}
			?>" />
		</dd>

<?php
	if(!empty($this->fields['item'])) {
		$editCustomFields = false;
		if(hikamarket::acl('order/edit/customfields'))
			$editCustomFields = true;

		foreach($this->fields['item'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikamarket_order_product_customfield hikamarket_order_product_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikamarket_order_product_customfield hikamarket_order_product_customfield_<?php echo $fieldName; ?>"><span><?php
			if($editCustomFields) {
				echo $this->fieldsClass->display($oneExtraField, @$orderProduct->$fieldName, 'order[products]['.$pid.'][field]['.$fieldName.']',false,'',true,$this->fields['item'], $orderProduct);
			} else {
				echo $this->fieldsClass->show($oneExtraField, @$orderProduct->$fieldName);
			}
		?></span></dd>
<?php
		}
	}

	if(hikamarket::level(1) && $this->vendor->vendor_id <= 1 && hikamarket::acl('order/edit/vendor') && (!empty($orderProduct->vendor_data) || $pid == 0)) {
		$vendor_data = new stdClass();
		if(!empty($orderProduct))
			$vendor_data = is_array($orderProduct->vendor_data) ? reset($orderProduct->vendor_data) : $orderProduct->vendor_data;
?>
		<dt class="hikamarket_orderproduct_vendor"><label><?php echo JText::_('HIKA_VENDOR'); ?></label></dt>
		<dd class="hikamarket_orderproduct_vendor"><?php
			echo $this->nameboxType->display(
				'order[products]['.$pid.'][vendor_id]',
				(int) @$vendor_data->order_vendor_id,
				hikamarketNameboxType::NAMEBOX_SINGLE,
				'vendor',
				array(
					'id' => 'hikamarket_order_'.$this->order->order_id.'_orderproduct_'.$pid.'_vendor',
					'default_text' => '<em>' . JText::_('HIKA_NONE') . '</em>',
					'delete' => true,
				)
			);
		?></dd>
		<dt class="hikamarket_orderproduct_vendorprice"><label><?php echo JText::_('HIKAM_VENDOR_UNIT_PRICE'); ?></label></dt>
		<dd class="hikamarket_orderproduct_vendorprice">
			<input type="text" name="order[products][<?php echo $pid; ?>][vendor_price]" id="hikamarket_order_<?php echo $this->order->order_id; ?>_orderproduct_<?php echo $pid; ?>_vendorprice" value="<?php echo @$vendor_data->order_product_vendor_price; ?>"/>
		</dd>
<?php
	}
?>
	</dl>
	<div style="clear:both;margin-top:4px;"></div>
	<div style="float:right">
		<button onclick="return window.orderMgr.submitProduct(this, <?php echo $pid; ?>);" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
	</div>
	<button onclick="return window.orderMgr.refreshProduct(this, <?php echo $pid; ?>);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
	<div style="clear:both"></div>
</td>
