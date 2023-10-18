<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikamarket_section_container hikamarket_section_product_plugin_shippingprices">
	<h3 data-section-toggle="product_plugin_shippingprices" class="hikamarket_section_toggle"><?php echo JText::_('SHIPPING_PRICES'); ?></h3>
	<div id="hikamarket_section_product_plugin_shippingprices">
	<table class="adminlist table table-striped" style="cell-spacing:1px;width:100%;">
		<thead>
			<tr>
				<th class="title"><?php echo JText::_('HIKA_NAME'); ?></th>
				<th class="title" width="10px"><?php echo JText::_('MINIMUM_QUANTITY'); ?></th>
				<th class="title"><?php echo JText::_('PRICE'); ?></th>
				<th class="title"><?php echo JText::_('FEE'); ?></th>
				<th class="title"><?php echo JText::_('SHIPPING_BLOCKED'); ?></th>
				<th class="title"><?php echo JText::_('ACTIONS'); ?></th>
			</tr>
		</thead>
	<tbody>
<?php
$i = 0;
$previous_shipping_id = -1;
foreach($shippings as &$shipping) {
	$shipping->shipping_params = hikashop_unserialize($shipping->shipping_params);

	$shipping_data = $shipping->shipping_name . ' - ' . $currencyHelper->displayPrices(array($shipping), 'shipping_price', 'shipping_currency_id');
	if(isset($shipping->shipping_params->shipping_percentage) && bccomp(sprintf('%F',$shipping->shipping_params->shipping_percentage),0,3)) {
		$shipping_data .= ' +'.$shipping->shipping_params->shipping_percentage.'%';
	}

	$rest = array();
	if(!empty($shipping->shipping_params->shipping_min_volume)){ $rest[] = JText::_('SHIPPING_MIN_VOLUME').':'.$shipping->shipping_params->shipping_min_volume.$shipping->shipping_params->shipping_size_unit; }
	if(!empty($shipping->shipping_params->shipping_max_volume)){ $rest[] = JText::_('SHIPPING_MAX_VOLUME').':'.$shipping->shipping_params->shipping_max_volume.$shipping->shipping_params->shipping_size_unit; }
	if(!empty($shipping->shipping_params->shipping_min_weight)){ $rest[] = JText::_('SHIPPING_MIN_WEIGHT').':'.$shipping->shipping_params->shipping_min_weight.$shipping->shipping_params->shipping_weight_unit; }
	if(!empty($shipping->shipping_params->shipping_max_weight)){ $rest[] = JText::_('SHIPPING_MAX_WEIGHT').':'.$shipping->shipping_params->shipping_max_weight.$shipping->shipping_params->shipping_weight_unit; }

	if(isset($shipping->shipping_params->shipping_min_price) && bccomp(sprintf('%F',$shipping->shipping_params->shipping_min_price),0,5)){
		$shipping->shipping_min_price=$shipping->shipping_params->shipping_min_price;
		$rest[] = JText::_('SHIPPING_MIN_PRICE').':'.$currencyHelper->displayPrices(array($shipping),'shipping_min_price','shipping_currency_id');
	}
	if(isset($shipping->shipping_params->shipping_max_price) && bccomp(sprintf('%F',$shipping->shipping_params->shipping_max_price),0,5)){
		$shipping->shipping_max_price=$shipping->shipping_params->shipping_max_price;
		$rest[] = JText::_('SHIPPING_MAX_PRICE').':'.$currencyHelper->displayPrices(array($shipping),'shipping_max_price','shipping_currency_id');
	}
	if(!empty($shipping->shipping_params->shipping_zip_prefix)){ $rest[]=JText::_('SHIPPING_PREFIX').':'.$shipping->shipping_params->shipping_zip_prefix; }
	if(!empty($shipping->shipping_params->shipping_min_zip)){ $rest[]=JText::_('SHIPPING_MIN_ZIP').':'.$shipping->shipping_params->shipping_min_zip; }
	if(!empty($shipping->shipping_params->shipping_max_zip)){ $rest[]=JText::_('SHIPPING_MAX_ZIP').':'.$shipping->shipping_params->shipping_max_zip; }
	if(!empty($shipping->shipping_params->shipping_zip_suffix)){ $rest[]=JText::_('SHIPPING_SUFFIX').':'.$shipping->shipping_params->shipping_zip_suffix; }
	if(!empty($shipping->zone_name_english)){ $rest[]=JText::_('ZONE').':'.$shipping->zone_name_english; }
	if(!empty($rest)) {
		$shipping_data .= '<div style="margin-left:10px">'.implode('<br/>', $rest).'</div>';
	}

	if($previous_shipping_id != $shipping->shipping_id) {
		echo "\r\n".'<tr class="hikashop_shipping_price_category"><td colspan="5">'.$shipping_data.'</td><td class="hk_center">'.
			'<a href="#" title="'.JText::_('ADD').'" onclick="return hikashop_shippingprice.addLine('.$product_id.',this,'.$shipping->shipping_id.',\''.str_replace(array('"',"'"),array('&quot;','\\\''),$shipping->shipping_name).'\',\''.$shipping->currency_symbol.'\');"><i class="fa fa-plus"></i></a>'.
			'</td></tr>';
	}
	$previous_shipping_id = $shipping->shipping_id;

	if(!empty($shipping->shipping_price_value) || !empty($shipping->shipping_fee_value)) {
		if($shipping->shipping_price_min_quantity < 1)
			$shipping->shipping_price_min_quantity = 1;
		if(isset($shipping->shipping_blocked) && $shipping->shipping_blocked) {
			$blocked_checked = 'checked="checked"';
			$attribute = 'readonly="readonly"';
			$shipping->shipping_price_value = 0;
			$shipping->shipping_fee_value = 0;
		}else{
			$blocked_checked = '';
			$attribute = '';
		}
		echo '<tr><td>'.
			'<input type="hidden" name="shipping_prices['.$product_id.']['.$i.'][id]" value="'.$shipping->shipping_price_id.'"/>'.
			'<input type="hidden" name="shipping_prices['.$product_id.']['.$i.'][shipping_id]" value="'.$shipping->shipping_id.'"/>'.
			'</td><td><input style="width:auto;" type="text" name="shipping_prices['.$product_id.']['.$i.'][qty]" value="'.$shipping->shipping_price_min_quantity.'" size="3"/></td>'.
			'<td style="text-align:center"><input style="width:auto;" type="text" id="shipping_prices_value_'.$product_id.'_'.$i.'" '.$attribute.' name="shipping_prices['.$product_id.']['.$i.'][value]" value="'.$shipping->shipping_price_value.'" size="7"/> '.$shipping->currency_symbol.'</td>'.
			'<td style="text-align:center"><input style="width:auto;" type="text" id="shipping_prices_fee_'.$product_id.'_'.$i.'" '.$attribute.' name="shipping_prices['.$product_id.']['.$i.'][fee]" value="'.$shipping->shipping_fee_value.'" size="7"/> '.$shipping->currency_symbol.'</td>'.
			'<td><input type="checkbox" name="shipping_prices['.$product_id.']['.$i.'][blocked]" onchange="hikashop_shippingprice.block('.$product_id.','.$i.', this);" '.$blocked_checked.'/></td>'.
			'<td class="hk_center">'.
			'<a href="#" onclick="return hikashop_shippingprice.remLine(this);" title="'.JText::_('HIKA_DELETE').'"><i class="fas fa-trash"></i></a>'.
			'</td></tr>';
	}

	$i++;
	unset($shipping);
}
?>
		<tr id="hikashop_shipping_price_tpl_line_<?php echo $product_id; ?>" style="display:none">
			<td><input type="hidden" name="{field_id}" value="{shipping_id}"/></td>
			<td><input style="width:auto;" type="text" name="{field_qty}" value="" size="3"/></td>
			<td style="text-align:center"><input style="width:auto;" id="shipping_prices_value_<?php echo $product_id; ?>_{cpt}" type="text" name="{field_value}" value="" size="7"/> {currency}</td>
			<td style="text-align:center"><input style="width:auto;" id="shipping_prices_fee_<?php echo $product_id; ?>_{cpt}" type="text" name="{field_fee}" value="" size="7"/> {currency}</td>
			<td><input type="checkbox" name="{field_blocked}" onchange="hikashop_shippingprice.block(<?php echo $product_id; ?>,{cpt}, this);" /></td>
			<td class="hk_center"><a href="#" onclick="return hikashop_shippingprice.remLine(this);" title="<?php echo JText::_('HIKA_DELETE'); ?>"><i class="fas fa-trash"></i></a></td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="shipping_prices[init]" value=""/>
<script type="text/javascript">
if(hikashop_shippingprice === undefined) {
var hikashop_shippingprice = {
	cpt: {
		<?php echo $product_id; ?>: <?php echo $i; ?>
	},
	addLine: function(pid,el,id,name,currency) {
		if(!this.cpt[pid])
			this.cpt[pid] = 0;
		var d = document, tplLine = d.getElementById("hikashop_shipping_price_tpl_line_"+pid),
			tableUser = tplLine.parentNode,
			htmlblocks = {
				cpt: this.cpt[pid],
				field_id: "shipping_prices["+pid+"]["+this.cpt[pid]+"][shipping_id]",
				field_qty: "shipping_prices["+pid+"]["+this.cpt[pid]+"][qty]",
				field_fee: "shipping_prices["+pid+"]["+this.cpt[pid]+"][fee]",
				field_value: "shipping_prices["+pid+"]["+this.cpt[pid]+"][value]",
				field_blocked: "shipping_prices["+pid+"]["+this.cpt[pid]+"][blocked]",
				shipping_id: id,
				name: name,
				currency: currency
			};
		if(!tplLine) return;
		var trLine = tplLine.cloneNode(true);
		trLine.id = "";
		while(el != null && el.tagName.toLowerCase() != "tr") { el = el.parentNode; }
		if(el == null || !el.nextSibling) {
			tableUser.appendChild(trLine);
		} else {
			while(el.nextSibling && el.nextSibling.tagName && el.nextSibling.tagName.toLowerCase() == "tr" && el.nextSibling.class != "hikashop_shipping_price_category") { el = el.nextSibling; }
			tableUser.insertBefore(trLine, el.nextSibling);
		}
		trLine.style.display = "";
		for (var i = tplLine.cells.length - 1; i >= 0; i--) {
			for(var k in htmlblocks) {
				if(trLine.cells[i])
					trLine.cells[i].innerHTML = trLine.cells[i].innerHTML.replace(new RegExp("{"+k+"}","g"), htmlblocks[k]);
			}
		}
		this.cpt[pid]++;
		return false;
	},
	remLine: function(el) {
		while(el != null && el.tagName.toLowerCase() != "tr") { el = el.parentNode; }
		if(!el) return;
		var table = el.parentNode;
		table.removeChild(el);
		return false;
	},
	block: function(pid,id,el) {
		var d = document,
			elValue = d.getElementById("shipping_prices_value_"+pid+"_"+id),
			elFee = d.getElementById("shipping_prices_fee_"+pid+"_"+id);
		if(!elValue || !elFee)
			return false;
		if(el.checked) {
			elValue.setAttribute("readonly", "readonly");
			elFee.setAttribute("readonly", "readonly");
		} else {
			elValue.removeAttribute("readonly", "readonly");
			elFee.removeAttribute("readonly", "readonly");
		}
		return false;
	}
};
} else {
	hikashop_shippingprice.cpt[<?php echo $product_id; ?>] = <?php echo $i; ?>;
}
</script>
	</div>
</div>
