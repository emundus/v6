<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('PRODUCT_LIST'); ?></legend>
<?php
	$editProduct = false;
	$showVendor = (hikamarket::level(1) && $this->order->order_type == 'sale' && $this->vendor->vendor_id <= 1);
	if(hikamarket::acl('order/edit/products') && $this->vendor->vendor_id <= 1) {
		$editProduct = true;
		$url = hikamarket::completeLink('product&task=selection&single=1&confirm=0&after=order|product_add&afterParams=order_id|'.$this->order->order_id, true);
?>
<div class="hikam_edit"><?php
	echo $this->popup->display(
		'<img src="'. HIKAMARKET_IMAGES .'icon-16/plus.png" alt=""/><span>'. JText::_('HIKA_EDIT') .'</span>',
		'HIKAM_ADD_ORDER_PRODUCT',
		hikamarket::completeLink('order&task=product_add&order_id='.$this->order->order_id, true),
		'hikamarket_addproduct_popup',
		750, 460, 'onclick="return window.orderMgr.addProduct(this);"', '', 'link'
	);
	echo ' ';
	echo $this->popup->display(
		'<img src="'. HIKAMARKET_IMAGES .'icon-16/product.png" alt=""/><span>'. JText::_('HIKA_EDIT') .'</span>',
		'HIKAM_ADD_ORDER_PRODUCT',
		hikamarket::completeLink('product&task=selection&single=1&confirm=0&after=order|product_add&afterParams=order_id|'.$this->order->order_id, true),
		'hikamarket_selectproduct_popup',
		750, 460, 'onclick="return window.orderMgr.selectProduct(this);"', '', 'link'
	);
?></div>
<script type="text/javascript">
<!--
window.orderMgr.addProduct = function(el) {
	window.hikamarket.submitFct = function(data) {
		var d = document, o = window.Oby;
		o.xRequest('<?php echo hikamarket::completeLink('order&task=show&subtask=products&cid='.$this->order->order_id, true); ?>', {update: 'hikamarket_order_products'});
		window.orderMgr.updateAdditionals();
		window.orderMgr.updateHistory();
		window.hikashop.closeBox();
	};
	window.hikashop.openBox(el);
	return false;
}
window.orderMgr.selectProduct = function(el) {
	window.hikamarket.submitFct = function(data) {
		var d = document, o = window.Oby;
		o.xRequest('<?php echo hikamarket::completeLink('order&task=show&subtask=products&cid='.$this->order->order_id, true); ?>', {update: 'hikamarket_order_products'});
		window.orderMgr.updateAdditionals();
		window.orderMgr.updateHistory();
		window.hikashop.closeBox();
	};
	window.hikashop.openBox(el);
	return false;
}
//-->
</script>
<?php
	}
?>
<table class="hikam_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover':'hikam_table'; ?>" id="hikamarket_order_product_listing" style="width:100%">
	<thead>
		<tr>
			<th class="hikamarket_order_item_name_title title"><?php echo JText::_('PRODUCT'); ?></th>
			<th class="hikamarket_order_item_price_title title"><?php echo JText::_('UNIT_PRICE'); ?></th>
			<th class="hikamarket_order_item_quantity_title title"><?php echo JText::_('PRODUCT_QUANTITY'); ?></th>
			<th class="hikamarket_order_item_total_price_title title"><?php echo JText::_('PRICE'); ?></th>
<?php if($showVendor){ ?>
			<th class="hikamarket_order_item_vendor_title title"><?php echo JText::_('HIKA_VENDOR'); ?></th>
<?php } ?>
<?php if($editProduct){ ?>
			<th colspan="2" class="hikamarket_order_item_remove_title title"><?php echo JText::_('ACTIONS'); ?></th>
<?php } ?>
		</tr>
	</thead>
	<tbody>
<?php
foreach($this->order->products as $k => $product) {
	$td_class = '';
	if(!empty($product->order_product_option_parent_id))
		$td_class = ' hikamarket_order_item_option';
?>
		<tr>
			<td class="hikamarket_order_item_name_value<?php echo $td_class; ?>">
<?php
if(!empty($product->product_id)) {
?>
				<a onclick="return window.orderMgr.showProduct(this);" href="<?php echo hikamarket::completeLink('shop.product&task=show&cid='.$product->product_id, true); ?>"><?php
					echo $product->order_product_name;
				?></a>
<?php
	} else {
		echo $product->order_product_name;
	}
?>
				<br/><?php
	echo $product->order_product_code;
	if(hikashop_level(2) && !empty($this->fields['item'])) {
?>				<p class="hikamarket_order_product_custom_item_fields">
<?php
		foreach($this->fields['item'] as $field) {
			$namekey = $field->field_namekey;
			if(empty($product->$namekey) && !strlen($product->$namekey))
				continue;
			echo '<p class="hikamarket_order_item_'.$namekey.'">' .
				$this->fieldsClass->trans($field->field_realname) . ': ' . $this->fieldsClass->show($field,$product->$namekey) .
				'</p>';
		}
?>
				</p>
<?php
	}
?>
			</td>
			<td class="hikamarket_order_item_price_value"><?php
				echo $this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id);
				if(bccomp($product->order_product_tax,0,5)) {
					echo '<br/>'.JText::sprintf('PLUS_X_OF_VAT', $this->currencyHelper->format($product->order_product_tax, $this->order->order_currency_id));
				}
			?></td>
			<td class="hikamarket_order_item_quantity_value"><?php echo $product->order_product_quantity;?></td>
			<td class="hikamarket_order_item_total_price_value"><?php echo $this->currencyHelper->format($product->order_product_total_price, $this->order->order_currency_id);?></td>
<?php if($showVendor) { ?>
			<td class="hikamarket_order_item_vendor_value"><?php
				if(!empty($product->vendor_data) && (int)$product->vendor_data->vendor_id > 1) {
					echo $product->vendor_data->vendor_name.'<br/>'.
						$this->currencyHelper->format($product->vendor_data->order_product_vendor_price, $this->order->order_currency_id);
				} else
					echo '-';
			?></td>
<?php } ?>
<?php if($editProduct){ ?>
			<td class="hikamarket_order_item_edit_value" style="text-align:center">
				<a onclick="return window.orderMgr.setProduct(this);" href="<?php
					echo hikamarket::completeLink('order&task=edit&subtask=products&order_id='.$this->order->order_id.'&order_product_id='.$product->order_product_id, true);
				?>"><img src="<?php echo HIKAMARKET_IMAGES?>icon-16/edit.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>"/></a>
			</td>
			<td class="hikamarket_order_item_remove_value" style="text-align:center">
				<a onclick="return window.orderMgr.delProduct(this, <?php echo $product->order_product_id; ?>);" href="<?php echo hikamarket::completeLink('order&task=product_delete&order_id='.$this->order->order_id.'&order_product_id='.$product->order_product_id); ?>"><img src="<?php echo HIKAMARKET_IMAGES?>icon-16/delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"/></a>
			</td>
<?php } ?>
		</tr>
<?php
}
?>
	</tbody>
</table>
<?php
echo $this->popup->display(
	'',
	'HIKAM_SHOW_ORDER_PRODUCT',
	hikamarket::completeLink('shop.product&task=show&cid=0', true),
	'hikamarket_showproduct_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
?>
<script type="text/javascript">
<!--
window.orderMgr.showProduct = function(el) {
	window.hikamarket.submitFct = function(data) { window.hikashop.closeBox(); };
	window.hikashop.openBox('hikamarket_showproduct_popup', el.getAttribute('href'));
	return false;
}
</script>
<?php
if($editProduct) {
	echo $this->popup->display(
		'',
		'HIKAM_MODIFY_ORDER_PRODUCT',
		hikamarket::completeLink('order&task=edit&subtask=products&order_id='.$this->order->order_id.'&order_product_id=0', true),
		'hikamarket_editproduct_popup',
		550, 350, 'style="display:none;"', '', 'link'
	);
?>
<script type="text/javascript">
<!--
window.orderMgr.setProduct = function(el) {
	window.hikamarket.submitFct = function(data) {
		var w = window, o = w.Oby;
		w.hikashop.closeBox();
		o.xRequest('<?php echo hikamarket::completeLink('order&task=show&subtask=products&cid='.$this->order->order_id, true); ?>', {update: 'hikamarket_order_products'}, function() {
			window.orderMgr.updateAdditionals();
			window.orderMgr.updateHistory();
		});
	};
	window.hikashop.openBox('hikamarket_editproduct_popup', el.getAttribute('href'));
	return false;
}
window.orderMgr.delProduct = function(el, id) {
	if(confirm("<?php echo JText::_('HIKAM_CONFIRM_DELETE_ORDER_PRODUCT'); ?>")) {
		var w = window, o = w.Oby;
		el.parentNode.innerHTML = '<img src="<?php echo HIKAMARKET_IMAGES?>icon-16/loading.gif" alt="loading..."/>';
		o.xRequest('<?php echo hikamarket::completeLink('order&task=product_delete&order_id='.$this->order->order_id.'&order_product_id=HKMPRODID', true, false, true); ?>'.replace('HKMPRODID',id), {update: 'hikamarket_order_products'}, function() {
			window.orderMgr.updateAdditionals();
			window.orderMgr.updateHistory();
		});
	}
	return false;
}
//-->
</script>
<?php
}
