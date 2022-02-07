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
$this->price_acls = array(
	'value' => $this->aclEdit('price/value'),
	'tax' => $this->aclEdit('price/tax') && !$this->shopConfig->get('floating_tax_prices', 0),
	'currency' => $this->aclEdit('price/currency') && (count($this->currencies) > 1),
	'quantity' => $this->aclEdit('price/quantity'),
	'acl' => hikashop_level(2) && $this->aclEdit('price/acl'),
	'user' => hikashop_level(2) && $this->aclEdit('price/user'),
	'date' => hikashop_level(2) && $this->aclEdit('price/date'),
);

if(!$this->price_acls['value'] && !$this->price_acls['tax'] && $this->aclEdit('price/tax') && $this->shopConfig->get('floating_tax_prices', 0))
	$this->price_acls['value'] = true;
if(!$this->price_acls['value'] && !$this->price_acls['tax'])
	return;

$show_minimal = (!$this->price_acls['currency'] && !$this->price_acls['quantity'] && !$this->price_acls['acl'] && !$this->price_acls['user']);
$form_key = empty($this->editing_variant) ? 'price' : 'variantprice';

if($show_minimal) {
	echo $this->loadTemplate('price_mini');
	return;
}

if(hikashop_level(2) && ($this->price_acls['acl'] || $this->price_acls['user'] || $this->price_acls['date'])) {
	echo $this->loadTemplate('price_advanced');
	return;
}

?>
<table class="table table-bordered table-condensed" style="width:100%">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('PRICE');
			?></th>
<?php if($this->price_acls['tax'] && $this->price_acls['value']) { ?>
			<th class="title"><?php
				echo JText::_('PRICE_WITH_TAX');
			?></th>
<?php }
	if($this->price_acls['currency']) { ?>
			<th class="title"><?php
				echo JText::_('CURRENCY');
			?></th>
<?php }
	if($this->price_acls['quantity']) { ?>
			<th class="title"><?php
				echo hikamarket::tooltip(JText::_('MINIMUM_QUANTITY'), '', '', JText::_('MIN_QTY'), '', 0);
			?></th>
<?php } ?>
			<th style="text-align:center">
				<a href="#" class="hikabtn hikabtn-success hikabtn-mini" onclick="return window.productMgr.newPrice('<?php echo $form_key; ?>');"><i class="fas fa-plus"></i></a>
			</th>
		</tr>
	</thead>
	<tbody>
<?php
$k = 0;
if(!empty($this->product->prices)) {
	foreach($this->product->prices as $i => $price) {
		if(empty($price->price_min_quantity))
			$price->price_min_quantity = 1;

		$pre_price = '';
		$post_price = '';
		if(!$this->price_acls['currency']) {
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
		}
		if(empty($price->price_currency_id))
			$price->price_currency_id = $this->default_currency;

?>		<tr class="row<?php echo $k;?>" id="hikamarket_<?php echo $form_key; ?>_<?php echo $i;?>">
			<td class="hikam_price">
				<input type="hidden" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_id]" value="<?php echo @$price->price_id;?>" />
<?php if($this->price_acls['value']) { ?>
				<?php echo $pre_price; ?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_<?php echo $i;?>_price" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_value]" value="<?php echo @$price->price_value; ?>" onchange="window.productMgr.updatePriceValue(<?php echo $i; ?>, false, '<?php echo $form_key; ?>')" /><?php echo $post_price; ?>
<?php } else { ?>
				<input size="10" type="hidden" id="hikamarket_<?php echo $form_key; ?>_<?php echo $i;?>_price" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_value]" value="<?php echo @$price->price_value; ?>"/>
				<?php echo $pre_price; ?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_<?php echo $i;?>_with_tax" name="<?php echo $form_key; ?>_with_tax_<?php echo $i;?>" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePriceValue(<?php echo $i; ?>, true, '<?php echo $form_key; ?>')"/><?php echo $post_price; ?>
<?php } ?>
			</td>
<?php if($this->price_acls['tax'] && $this->price_acls['value']) { ?>
			<td class="hikam_price">
				<?php echo $pre_price; ?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_<?php echo $i;?>_with_tax" name="<?php echo $form_key; ?>_with_tax_<?php echo $i;?>" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePriceValue(<?php echo $i; ?>, true, '<?php echo $form_key; ?>')"/><?php echo $post_price; ?>
			</td>
<?php }
	if($this->price_acls['currency']) { ?>
			<td class="hikam_currency"><?php
				echo $this->currencyType->display($form_key.'['.$i.'][price_currency_id]', @$price->price_currency_id,'class="no-chzn"');
			?></td>
<?php }
	if($this->price_acls['quantity']) { ?>
			<td class="hikam_qty">
				<input size="3" type="text" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_min_quantity]" value="<?php echo @$price->price_min_quantity; ?>" />
			</td>
<?php } ?>
			<td style="text-align:center">
				<a href="#" onclick="window.hikamarket.deleteRow(this); return false;"><i class="fas fa-trash-alt"></i></a>
			</td>
		</tr>
<?php
		$k = 1 - $k;
	}
}
?>		<tr class="row<?php echo $k;?>" id="hikamarket_<?php echo $form_key; ?>_tpl" style="display:none;">
			<td class="hikam_price">
				<input type="hidden" name="<?php echo $form_key; ?>[{id}][price_id]" value="" />
<?php if($this->price_acls['value']) { ?>
				<?php echo $pre_price; ?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_{id}_price" name="<?php echo $form_key; ?>[{id}][price_value]" value="" onchange="window.productMgr.updatePriceValue({id}, false, '<?php echo $form_key; ?>')" /><?php echo $post_price; ?>
<?php } else { ?>
				<input size="10" type="hidden" id="hikamarket_<?php echo $form_key; ?>_{id}_price" name="<?php echo $form_key; ?>[{id}][price_value]" value=""/>
				<?php echo $pre_price; ?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_{id}_with_tax" value="" onchange="window.productMgr.updatePriceValue({id}, true, '<?php echo $form_key; ?>')"/><?php echo $post_price; ?>
<?php } ?>
			</td>
<?php if($this->price_acls['tax'] && $this->price_acls['value']) { ?>
			<td class="hikam_price">
				<?php echo $pre_price; ?><input size="10" type="text" id="hikamarket_<?php echo $form_key; ?>_{id}_with_tax" value="" onchange="window.productMgr.updatePriceValue({id}, true, '<?php echo $form_key; ?>')"/><?php echo $post_price; ?>
			</td>
<?php }
	if($this->price_acls['currency']) { ?>
			<td class="hikam_currency"><?php echo $this->currencyType->display($form_key.'[{id}][price_currency_id]', $this->main_currency_id, 'class="no-chzn"'); ?></td>
<?php }
	if($this->price_acls['quantity']) { ?>
			<td class="hikam_qty"><input size="3" type="text" name="<?php echo $form_key; ?>[{id}][price_min_quantity]" value="" /></td>
<?php } ?>
			<td style="text-align:center">
				<a href="#" onclick="hikamarket.deleteRow(this); return false;"><i class="fas fa-trash-alt"></i></a>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
if(!window.productMgr)
	window.productMgr = {};
if(!window.productMgr.updatePriceValue) {
window.productMgr.updatePriceValue = function(id, taxed, key) {
<?php if($this->price_acls['tax'] || !$this->price_acls['value']) { ?>
	var d = document, o = window.Oby, conversion = '', elName = 'hikamarket_'+key+'_'+id, destName = elName;
	if(taxed) {
		elName += '_with_tax'; destName += '_price'; conversion = 1;
	} else {
		elName += '_price'; destName += '_with_tax'; conversion = 0;
	}

	var price = d.getElementById(elName).value,
		dest = d.getElementById(destName),
		taxElem = d.getElementById('dataproductproduct_tax_id'),
		tax_id = -1;
	if(taxElem)
		tax_id = taxElem.value;
<?php if(!empty($this->product->product_tax_id)) { ?>
	else
		tax_id = <?php echo (int)$this->product->product_tax_id; ?>;
<?php } ?>
	var url = '<?php echo str_replace('\'', '\\\'', hikamarket::completeLink('product&task=getprice&price={PRICE}&product_id='.$this->product->product_id.'&tax_id={TAXID}&conversion={CONVERSION}', true, false, true)); ?>';
	url = url.replace('{PRICE}', price).replace('{TAXID}', tax_id).replace('{CONVERSION}', conversion);
	o.xRequest(url, null, function(xhr, params) {
		dest.value = xhr.responseText;
	});
<?php } ?>
};
}

if(!window.productMgr.newPrice) {
window.productMgr.newPrice = function(key) {
	var t = window.hikamarket,
		cpt = window.productMgr.cpt[key],
		htmlBlocks = {id: cpt};
	t.dupRow('hikamarket_'+key+'_tpl', htmlBlocks, 'hikamarket_'+key+'_'+cpt);
	window.productMgr.cpt[key]++;
	return false;
};
}
if(!window.productMgr.cpt)
	window.productMgr.cpt = {};
window.productMgr.cpt['<?php echo $form_key; ?>'] = <?php echo count(@$this->product->prices); ?>;
window.hikashop.ready(function(){ hikamarket.noChzn(); });
</script>
