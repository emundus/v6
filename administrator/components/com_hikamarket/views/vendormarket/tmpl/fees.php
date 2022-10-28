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
if($this->config->get('market_mode', 'fee') == 'commission') {
?>
<div style="float:left">
	<h3><?php echo JText::_('HIKAM_MODE_COMMISSION');?></h3>
</div>
<?php
}
?>
<table class="adminlist table table-striped table-hover" width="100%">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('CURRENCY');?></th>
			<th class="title" style="width:10%"><?php echo JText::_('MINIMUM_QUANTITY');?></th>
			<th class="title" style="width:10%"><?php echo JText::_('HIKAM_MINIMUM_PRICE');?></th>
			<th class="title"><?php echo JText::_('FLAT_FEE');?></th>
			<th class="title"><?php echo JText::_('FIXED_FEE');?></th>
			<th class="title"><?php echo JText::_('PERCENT_FEE');?></th>
			<th class="title" style="width:5%"><?php echo JText::_('GLOBAL_FEE');?></th>
<?php if(!empty($this->fees_show_groups)) { ?>
			<th class="title"><?php echo JText::_('GROUP_FEE');?></th>
<?php } ?>
			<th class="title" style="width:4%">
				<button class="hikabtn hikabtn-primary hikabtn-mini" onclick="return marketAddVendorFee();" type="button" style="margin:0px;"><i class="fas fa-plus"></i> <?php echo JText::_('ADD'); ?></button>
			</th>
		</tr>
	</thead>
	<tbody id="hikamarket_vendor_fees">
<?php
$k = 0;
$cpt = 0;
$formRoot = 'data';
if(!empty($this->formRoot))
	$formRoot = $this->formRoot;
if(!empty($this->fees)) {
	foreach($this->fees as $i => $fee) {
		$global = (substr($fee->fee_type, -7) == '_global');
?>
		<tr class="row<?php echo $k;?>">
			<td><?php
				echo @$this->currencyType->display($formRoot.'[vendor_fee]['.$i.'][currency]', @$fee->fee_currency_id);
			?></td>
			<td>
				<input style="width:auto;" size="3" type="text" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][quantity]" value="<?php echo @$fee->fee_min_quantity;?>" />
			</td>
			<td>
				<input style="width:auto;" size="5" type="text" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][min_price]" value="<?php echo @$fee->fee_min_price;?>" />
			</td>
			<td>
				<input type="hidden" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][id]" value="<?php echo $fee->fee_id;?>" />
				<input style="width:auto;" size="6" type="text" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][value]" value="<?php echo @$fee->fee_value;?>" />
			</td>
			<td>
				<input style="width:auto;" size="6" type="text" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][fixed]" value="<?php echo @$fee->fee_fixed;?>" />
			</td>
			<td>
				<input style="width:auto;" size="4" type="text" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][percent]" value="<?php echo number_format((float)@$fee->fee_percent, 2);?>" />%
			</td>
			<td>
				<input type="checkbox" name="<?php echo $formRoot; ?>[vendor_fee][<?php echo $i;?>][global]" value="1" <?php echo $global ? 'checked="checked" ':''; ?>/>
			</td>
<?php if(!empty($this->fees_show_groups) && !empty($this->joomlaAclType)) { ?>
			<td><?php
				echo $this->joomlaAclType->displayList($formRoot.'[vendor_fee]['.$i.'][group]', @$fee->fee_group);
			?></td>
<?php } ?>
			<td style="text-align:center">
				<a href="#" onclick="hikamarket.deleteRow(this); return false;"><i class="far fa-trash-alt"></i></a>
			</td>
		</tr>
<?php
		$k = 1 - $k;
		$cpt = $i;
	}
	$cpt++;
}
?>
		<tr class="row<?php echo $k;?>"  style="display:none" id="hikamarket_tpl_vendor_fee">
			<td><?php echo @$this->currencyType->display('{input_fee_currency}', 0);?></td>
			<td><input style="width:auto;" size="3" type="text" name="{input_fee_quantity}" value="" /></td>
			<td><input style="width:auto;" size="5" type="text" name="{input_fee_min_price}" value="" /></td>
			<td>
				<input type="hidden" name="{input_fee_id}" value="" />
				<input style="width:auto;" size="6" type="text" name="{input_fee_value}" value="" />
			</td>
			<td><input style="width:auto;" size="6" type="text" name="{input_fee_fixed}" value="" /></td>
			<td><input style="width:auto;" size="4" type="text" name="{input_fee_percent}" value="" />%</td>
			<td><input type="checkbox" name="{input_fee_global}" value="1" /></td>
<?php if(!empty($this->fees_show_groups) && !empty($this->joomlaAclType)) { ?>
			<td><?php
				echo $this->joomlaAclType->displayList('{input_fee_group}', 0);
			?></td>
<?php } ?>
			<td style="text-align:center"><a href="#" onclick="hikamarket.deleteRow(this); return false;"><i class="far fa-trash-alt"></i></a></td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
var hikamarket_product_fee_cpt = <?php echo $cpt;?>;
function marketAddVendorFee(){
	var d = document,
		tbody = d.getElementById('hikamarket_vendor_fees'),
		cpt = hikamarket_product_fee_cpt,
		htmlblocks = {
			input_fee_id: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][id]",
			input_fee_currency: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][currency]",
			input_fee_quantity: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][quantity]",
			input_fee_min_price: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][min_price]",
			input_fee_value: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][value]",
			input_fee_fixed: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][fixed]",
			input_fee_percent: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][percent]",
			input_fee_global: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][global]",
			input_fee_group: "<?php echo $formRoot; ?>[vendor_fee]["+cpt+"][group]"
		};
	hikamarket.dupRow('hikamarket_tpl_vendor_fee', htmlblocks, "market_vendor_fee_" + cpt);
	hikamarket_product_fee_cpt++;
	return false;
}
</script>
