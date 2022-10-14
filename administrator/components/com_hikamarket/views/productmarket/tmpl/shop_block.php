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
if(hikamarket::level(1) && $this->product_type != 'template') {
?>
<div class="hkc-xl-8 hkc-lg-12 hikashop_product_block hikashop_product_edit_vendorfees"><div>
	<div class="hikashop_product_part_title hikashop_product_edit_vendorfees_title"><?php echo JText::_('VENDOR_FEES');?></div>
	<table class="adminlist table table-striped table-hover" width="100%">
		<thead>
			<tr>
				<th class="title"><?php echo JText::_('CURRENCY');?></th>
				<th class="title" style="width:10%"><?php
					echo hikamarket::tooltip(JText::_('MINIMUM_QUANTITY'), '', '', JText::_('MIN_QTY'), '', 0);
				?></th>
				<th class="title" style="width:10%"><?php echo JText::_('HIKAM_MINIMUM_PRICE');?></th>
				<th class="title"><?php echo JText::_('FLAT_FEE');?></th>
				<th class="title"><?php echo JText::_('FIXED_FEE');?></th>
				<th class="title"><?php echo JText::_('PERCENT_FEE');?></th>
				<th class="title" style="width:8%">
					<button class="btn" style="margin:0px;" type="button" onclick="return marketAddVendorFee('<?php echo $this->product_type; ?>');">
						<img src="<?php echo HIKASHOP_IMAGES;?>add.png" alt="" style="vertical-align:middle;"><?php echo JText::_('ADD');?>
					</button>
				</th>
			</tr>
		</thead>
		<tbody id="hikamarket_vendor_fees_<?php echo $this->product_type; ?>">
<?php
$k = 0;
$cpt = 0;
if(!empty($this->data)) {
	foreach($this->data as $i => $fee) {
?>
			<tr class="row<?php echo $k;?>">
				<td class="hika_currency"><?php
					echo @$this->currencyType->display('market[product_fee]['.$this->product_type.']['.$i.'][currency]', @$fee->fee_currency_id);
				?></td>
				<td class="hika_qty">
					<input size="3" type="text" name="market[product_fee][<?php echo $this->product_type; ?>][<?php echo $i;?>][quantity]" value="<?php echo @$fee->fee_min_quantity;?>" />
				</td>
				<td class="hika_price">
					<input size="5" type="text" name="market[product_fee][<?php echo $this->product_type; ?>][<?php echo $i;?>][min_price]" value="<?php echo @$fee->fee_min_price;?>" />
				</td>
				<td class="hika_price">
					<input type="hidden" name="market[product_fee][<?php echo $this->product_type; ?>][<?php echo $i;?>][id]" value="<?php echo $fee->fee_id;?>" />
					<input size="6" type="text" name="market[product_fee][<?php echo $this->product_type; ?>][<?php echo $i;?>][value]" value="<?php echo @$fee->fee_value;?>" />
				</td>
				<td class="hika_price">
					<input size="6" type="text" name="market[product_fee][<?php echo $this->product_type; ?>][<?php echo $i;?>][fixed]" value="<?php echo @$fee->fee_fixed;?>" />
				</td>
				<td class="hika_price">
					<input size="4" type="text" name="market[product_fee][<?php echo $this->product_type; ?>][<?php echo $i;?>][percent]" value="<?php echo number_format((float)@$fee->fee_percent, 2);?>" />%
				</td>
				<td align="center">
					<a href="#" onclick="window.hikamarket.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES;?>delete.png" alt="-"/></a>
				</td>
			</tr>
<?php
		$k = 1 - $k;
		$cpt = $i;
	}
	$cpt++;
}
?>
			<tr class="row<?php echo $k;?>"  style="display:none" id="hikamarket_tpl_product_fee_<?php echo $this->product_type; ?>">
				<td class="hika_currency"><?php echo @$this->currencyType->display('{input_fee_currency}', 0);?></td>
				<td class="hika_qty"><input size="3" type="text" name="{input_fee_quantity}" value="" /></td>
				<td class="hika_price"><input size="5" type="text" name="{input_fee_min_price}" value="" /></td>
				<td class="hika_price">
					<input type="hidden" name="{input_fee_id}" value="" />
					<input size="6" type="text" name="{input_fee_value}" value="" />
				</td>
				<td class="hika_price"><input size="6" type="text" name="{input_fee_fixed}" value="" /></td>
				<td class="hika_price"><input size="4" type="text" name="{input_fee_percent}" value="" />%</td>
				<td align="center"><a href="#" onclick="window.hikamarket.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES;?>delete.png" alt="-"/></a></td>
			</tr>
		</tbody>
	</table>
<script type="text/javascript">
var hikamarket_product_fee_cpt = <?php echo $cpt;?>;
function marketAddVendorFee(type){
	var d = document,
		tbody = d.getElementById('hikamarket_vendor_fees_' + type),
		cpt = hikamarket_product_fee_cpt,
		htmlblocks = {
			input_fee_id: "market[product_fee]["+type+"]["+cpt+"][id]",
			input_fee_value: "market[product_fee]["+type+"]["+cpt+"][value]",
			input_fee_currency: "market[product_fee]["+type+"]["+cpt+"][currency]",
			input_fee_percent: "market[product_fee]["+type+"]["+cpt+"][percent]",
			input_fee_quantity: "market[product_fee]["+type+"]["+cpt+"][quantity]",
			input_fee_min_price: "market[product_fee]["+type+"]["+cpt+"][min_price]",
			input_fee_fixed: "market[product_fee]["+type+"]["+cpt+"][fixed]"
		};
	window.hikamarket.dupRow('hikamarket_tpl_product_fee_' + type, htmlblocks, "market_product_fee_" + type + "_" + cpt);
	hikamarket_product_fee_cpt++;
	return false;
}
</script>
</div></div>
<input type="hidden" name="market[form]" value="1"/>
<?php
}
