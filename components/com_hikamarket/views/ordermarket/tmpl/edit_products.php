<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order&task=save&subtask=product&tmpl=component'); ?>" name="hikamarket_order_product_form" id="hikamarket_order_product_form" method="post" enctype="multipart/form-data">
	<dl class="hikam_options">
		<dt class="hikamarket_order_product_id"><label><?php echo JText::_('PRODUCT'); ?></label></dt>
		<dd class="hikamarket_order_product_id"><?php echo (int)@$this->orderProduct->product_id; ?> - <?php echo @$this->originalProduct->product_name; ?></dd>

		<dt class="hikamarket_order_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikamarket_order_product_name">
			<input type="text" name="data[order][product][order_product_name]" value="<?php echo $this->escape(@$this->orderProduct->order_product_name); ?>" />
		</dd>

		<dt class="hikamarket_order_product_code"><label><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
		<dd class="hikamarket_order_product_code">
			<input type="text" name="data[order][product][order_product_code]" value="<?php echo $this->escape(@$this->orderProduct->order_product_code); ?>" />
		</dd>

		<dt class="hikamarket_order_product_price"><label><?php echo JText::_('UNIT_PRICE'); ?></label></dt>
		<dd class="hikamarket_order_product_price">
			<input type="text" name="data[order][product][order_product_price]" value="<?php echo @$this->orderProduct->order_product_price; ?>" />
		</dd>

		<dt class="hikamarket_order_product_vat"><label><?php echo JText::_('VAT'); ?></label></dt>
		<dd class="hikamarket_order_product_vat">
			<input type="text" name="data[order][product][order_product_tax]" value="<?php echo @$this->orderProduct->order_product_tax; ?>" />
			<?php echo $this->ratesType->display( "data[order][product][tax_namekey]" , @$this->orderProduct->order_product_tax_info[0]->tax_namekey ); ?>
		</dd>

		<dt class="hikamarket_order_product_quantity"><label><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
		<dd class="hikamarket_order_product_quantity">
			<input type="text" name="data[order][product][order_product_quantity]" value="<?php echo @$this->orderProduct->order_product_quantity; ?>" />
		</dd>

<?php
	if(!empty($this->fields['item'])) {
		$editCustomFields = false;
			$editCustomFields = true;
		foreach($this->fields['item'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikamarket_order_product_customfield hikamarket_order_product_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikamarket_order_product_customfield hikamarket_order_product_customfield_<?php echo $fieldName; ?>"><span><?php
			if($editCustomFields) {
				echo $this->fieldsClass->display($oneExtraField, @$this->orderProduct->$fieldName, 'data[order][product]['.$fieldName.']',false,'',true);
			} else {
				echo $this->fieldsClass->show($oneExtraField, @$this->orderProduct->$fieldName);
			}
		?></span></dd>
<?php
		}
	}

	if(hikamarket::level(1) && $this->vendor->vendor_id <= 1 && hikamarket::acl('order/edit/vendor') && !empty($this->orderProduct->vendor_data)) {
?>
		<dt class="hikamarket_orderproduct_vendor"><label><?php echo JText::_('HIKA_VENDOR'); ?></label></dt>
		<dd class="hikamarket_orderproduct_vendor"><?php
			if(!empty($this->orderProduct->vendor_data->order_vendor_id)) {
				echo $this->orderProduct->vendor_data->order_vendor_id;
				echo ' - ';
				if(isset($this->orderProduct->vendor_data->vendor_name))
					echo $this->orderProduct->vendor_data->vendor_name;
				else
					echo $this->orderProduct->vendor->vendor_name;
			} else
				echo '-';
		?><input type="hidden" name="data[market][product][order_product_vendor_id]" value="<?php echo @$this->orderProduct->vendor_data->order_vendor_id; ?>"/></dd>
		<dt class="hikamarket_orderproduct_vendorprice"><label><?php echo JText::_('HIKAM_VENDOR_UNIT_PRICE'); ?></label></dt>
		<dd class="hikamarket_orderproduct_vendorprice">
			<input type="text" name="data[order][product][order_product_vendor_price]" value="<?php echo @$this->orderProduct->vendor_data->order_product_vendor_price; ?>"/>
		</dd>
<?php
	}

	if(hikamarket::acl('order/edit/history')) {
?>
		<dt class="hikamarket_orderproduct_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikamarket_orderproduct_history">
			<span><input onchange="window.orderMgr.orderproduct_history_changed(this);" type="checkbox" id="hikamarket_history_orderproduct_store" name="data[history][store_data]" value="1"/><label for="hikamarket_history_orderproduct_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikamarket_history_orderproduct_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.orderproduct_history_changed = function(el) {
	var fields = ['hikamarket_history_orderproduct_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikamarket.setArrayDisplay(fields, displayValue);
}
</script>
<?php
	}
?>
	</dl>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][product][order_product_id]" value="<?php echo @$this->orderProduct->order_product_id;?>" />
	<input type="hidden" name="data[order][product][product_id]" value="<?php echo @$this->orderProduct->product_id;?>" />
	<input type="hidden" name="data[order][product][order_id]" value="<?php echo @$this->orderProduct->order_id;?>" />
	<input type="hidden" name="data[products]" value="1" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->orderProduct->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="subtask" value="products" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
