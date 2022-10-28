<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><dl class="hikam_options">
	<dt class="hikamarket_product_price"><label><?php echo JText::_('PRICES'); ?></label></dt>
	<dd class="hikamarket_product_price"><?php

	$price = reset($this->product->prices);

	$form_key = empty($this->editing_variant) ? 'price' : 'variantprice';

	$pre_price = ''; $post_price = '';
	$currency = empty($price->price_currency_id) ? $this->default_currency : $this->currencies[$price->price_currency_id];
	if(is_string($currency->currency_locale))
		$currency->currency_locale = hikamarket::unserialize($currency->currency_locale);
	if($currency->currency_locale['p_cs_precedes']) {
		$pre_price .= $currency->currency_symbol;
		if($currency->currency_locale['p_sep_by_space'])
			$pre_price .= ' ';
	} else {
		if($currency->currency_locale['p_sep_by_space'])
			$post_price .= ' ';
		$post_price .= $currency->currency_symbol;
	}

	if($this->price_acls['tax'] && empty($this->product->product_tax_id)) {
		echo $pre_price;
		?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_0_price" name="<?php echo $form_key; ?>[0][price_value]" value="<?php echo @$price->price_value; ?>"/><?php
		echo $post_price;
	} else {
		if($this->price_acls['value']) {
			echo $pre_price;
			?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_0_price" name="<?php echo $form_key; ?>[0][price_value]" value="<?php echo @$price->price_value; ?>" onchange="window.productMgr.updatePriceMini(false, '<?php echo $form_key; ?>')" /><?php
			echo $post_price;
			echo '<br/>';
			if($this->price_acls['tax']) {
				echo $pre_price;
				?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_0_with_tax" name="<?php echo $form_key; ?>_with_tax_0" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePriceMini(true, '<?php echo $form_key; ?>')" /><?php
				echo $post_price;
			} else {
				echo $pre_price;
				?><span id="hikamarket_<?php echo $form_key; ?>_0_with_tax_span"><?php echo @$price->price_value_with_tax;?></span><?php
				echo $post_price;
			}
		} else {
			echo $pre_price;
?>
		<input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_0_with_tax" name="<?php echo $form_key; ?>_with_tax_0" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePriceMini(true, '<?php echo $form_key; ?>')" />
		<input type="hidden" id="hikamarket_<?php echo $form_key; ?>_0_price" name="<?php echo $form_key; ?>[0][price_value]" value="<?php echo @$price->price_value; ?>" />
<?php
			echo $post_price;
		}
	}
	?></dd>
</dl>
	<input type="hidden" name="<?php echo $form_key; ?>[0][price_id]" value="<?php echo @$price->price_id;?>" />
<script type="text/javascript">
if(!window.productMgr)
	window.productMgr = {};
if(!window.productMgr.updatePriceMini) {
window.productMgr.updatePriceMini = function(taxed, key) {
	var d = document, o = window.Oby, conversion = '', elName = 'hikamarket_'+key+'_0', destName = elName;
	if(taxed) {
		elName += '_with_tax'; destName += '_price'; conversion = 1;
	} else {
		elName += '_price'; destName += '_with_tax'; conversion = 0;
	}

	var price = d.getElementById(elName).value,
		dest = d.getElementById(destName),
		taxElem = d.getElementById('dataproductproduct_tax_id'),
		tax_id = -1, valueMode = true;
	if(!dest) {
		dest = d.getElementById(destName + '_span');
		valueMode = false;
	}
	if(taxElem)
		tax_id = taxElem.value;
<?php if(!empty($this->product->product_tax_id)) { ?>
	else
		tax_id = <?php echo (int)$this->product->product_tax_id; ?>;
<?php } ?>
	var url = '<?php echo str_replace('\'', '\\\'', hikamarket::completeLink('product&task=getprice&price={PRICE}&product_id='.$this->product->product_id.'&tax_id={TAXID}&conversion={CONVERSION}', true, false, true)); ?>';
	url = url.replace('{PRICE}', price).replace('{TAXID}', tax_id).replace('{CONVERSION}', conversion);
	o.xRequest(url, null, function(xhr, params) {
		if(valueMode)
			dest.value = xhr.responseText;
		else
			dest.innerHTML = xhr.responseText;
	});
};
}
</script>
