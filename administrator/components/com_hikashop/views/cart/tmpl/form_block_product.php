<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>			<td class="hikashop_cart_item_name_value">
				<span class="hikashop_cart_item_name"><?php
echo $this->popup->display(
	$this->product->product_name,
	strip_tags($this->product->product_name).' '.strip_tags($this->product->product_code),
	hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$this->product->product_id.'&tmpl=component'),
	'hikashop_see_product_'.$this->cart_product->cart_product_id,
	760, 480, '', '', 'link'
);
echo ' - ' . $this->product->product_code;
				?></span>
<?php
if(hikashop_level(2) && !empty($this->fields['item'])) {
?>
				<dl class="hika_options hikashop_cart_product_custom_item_fields">
<?php
	$after = array();
	foreach($this->fields['item'] as $fieldName => $field) {
		$namekey = $field->field_namekey;

		if(!$this->checkFieldForProduct($field, $this->product))
			continue;
		$html = $this->fieldClass->display($field, @$this->cart_product->$fieldName, 'data[products]['.$this->cart_product->cart_product_id.'][field]['.$fieldName.']',false,'',true);
		if($field->field_type == 'hidden') {
			$after[] = $html;
			continue;
		}
?>
		<dt class="hikashop_cart_product_customfield hikashop_cart_product_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldClass->getFieldName($field);?></dt>
		<dd class="hikashop_cart_product_customfield hikashop_cart_product_customfield_<?php echo $fieldName; ?>"><span><?php
			echo $html;
		?></span></dd>
<?php
	}
?>
				</dl>
<?php
	if(count($after)) {
		echo implode("\r\n", $after);
	}
}
?>
			</td>
<?php
if(hikashop_level(2) && !empty($this->fields['product'])) {
	foreach($this->fields['product'] as $field) {
		$namekey = $field->field_namekey;
?>
			<td><?php
		if(!empty($this->cart_product->$namekey))
			echo '<p class="hikashop_cart_product_'.$namekey.'">' . $this->fieldClass->show($field, $this->cart_product->$namekey) . '</p>';
			?></td>
<?php
	}
}
?>
			<td style="text-align: center"><?php

$tooltip_images = array(
	'ok' => '<i class="icon-publish"></i>',
	'err' => '<i class="icon-unpublish"></i>'
);
if (empty($this->product) || (!empty($this->product->product_sale_end) && $this->product->product_sale_end < time())) {
	echo hikashop_hktooltip(JText::_('HIKA_NOT_SALE_ANYMORE'), '', $tooltip_images['err']);
} elseif ($this->product->product_quantity == -1) {
	echo hikashop_hktooltip(JText::sprintf('X_ITEMS_IN_STOCK', JText::_('HIKA_UNLIMITED')), '', $tooltip_images['ok']);
} elseif (($this->product->product_quantity - $this->cart_product->cart_product_quantity) >= 0) {
	echo hikashop_hktooltip(JText::sprintf('X_ITEMS_IN_STOCK', $this->product->product_quantity), '', $tooltip_images['ok']);
} else {
	echo hikashop_hktooltip(JText::_('NOT_ENOUGH_STOCK'), '', $tooltip_images['err']);
}
if(!empty($this->ajax)) {
?>
<script type="text/javascript">
hkjQuery(function(){ hkjQuery(\'[data-toggle="hk-tooltip"]\').hktooltip({"html": true,"container": "body"}); });
</script>
<?php
}

			?></td>
			<td class="hikashop_cart_item_quantity_value order">
				<input type="text" name="data[item][<?php echo $this->cart_product->cart_product_id; ?>]" value="<?php echo $this->cart_product->cart_product_quantity;?>"/>
			</td>
			<td class="hikashop_cart_item_price_value"><?php
if(isset($this->product->prices))
	echo $this->currencyClass->format(
		isset($this->product->prices[0]->price_value_with_tax) ? $this->product->prices[0]->price_value_with_tax : @$this->product->prices[0]->price_value,
		$this->cart->cart_currency_id
	);
			?></td>
