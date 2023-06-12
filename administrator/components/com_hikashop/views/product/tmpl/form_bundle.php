<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table id="hikashop_product_characteristics_table" class="adminlist table table-striped" style="width:100%">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th class="title"><?php
				echo JText::_('PRODUCT_QUANTITY');
			?></th>
			<th style="width:40px;text-align:center">
				<a href="#" onclick="return window.productMgr.newBundle();" title="<?php echo JText::_('ADD'); ?>"><i class="fa fa-plus"></i></a>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr id="hikashop_bundle_add_zone" style="display:none;">
			<td colspan="3">
<dl>
	<dt><?php echo JText::_('PRODUCT_NAME'); ?></dt>
	<dd><?php
		echo $this->nameboxType->display(
			null,
			null,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'product',
			array(
				'id' => 'hikashop_bundle_nb_add',
				'default_text' => 'PLEASE_SELECT',
				'variants' => 1,
			)
		);
	?></dd>
	<dt><?php echo JText::_('PRODUCT_QUANTITY'); ?></dt>
	<dd>
		<input type="text" size="5" style="width:70px;" id="hikashop_bundle_qty_add" name="" value="1"/>
	</dd>
</dl>
<div style="float:right">
	<button onclick="return window.productMgr.addBundle();" class="btn btn-success"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ;?></button>
</div>
<button onclick="return window.productMgr.cancelNewBundle();" class="btn btn-danger"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
<div style="clear:both"></div>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
	$k = 0;
	if(!empty($this->product->bundle)) {
		foreach($this->product->bundle as $bundle) {
			$pid = (int)$bundle->product_related_id;
?>
		<tr class="row<?php echo $k ?>">
			<td><?php
				$desc = JText::_('PRODUCT_ID') . ': ' . $pid;
				echo hikashop_hktooltip($desc, $bundle->product_name, $bundle->product_name);
			?></td>
			<td>
				<input type="text" size="5" style="width:70px;" name="data[product][bundle][<?php echo $pid; ?>]" value="<?php echo max((int)$bundle->product_related_quantity, 1); ?>"/>
			</td>
			<td style="text-align:center">
				<a href="#delete" onclick="window.hikashop.deleteRow(this); return false;"><i class="fas fa-trash"></i></a>
			</td>
		</tr>
<?php
			$k = 1 - $k;
		}
	}
?>
		<tr id="hikashop_bundle_row_template" class="row<?php echo $k ?>" style="display:none;">
			<td>{NAME}</td>
			<td style="text-align:center">
				<input type="text" size="5" style="width:70px;" name="{INPUT_NAME}" value="{VALUE}"/>
			</td>
			<td style="text-align:center">
				<a href="#delete" onclick="window.hikashop.deleteRow(this); return false;"><i class="fas fa-trash"></i></a>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
window.productMgr.newBundle = function() {
	var w = window, d = document, el = null;
	w.oNameboxes['hikashop_bundle_nb_add'].clear();
	el = d.getElementById('hikashop_bundle_qty_add');
	if(el) el.value = '1';
	el = d.getElementById('hikashop_bundle_add_zone');
	if(el) el.style.display = '';
	return false;
};
window.productMgr.cancelNewBundle = function() {
	var w = window, d = document, o = w.Oby;
	var el = d.getElementById('hikashop_bundle_add_zone');
	if(el) el.style.display = 'none';
	return false;
};
window.productMgr.addBundle = function() {
	var w = window, d = document, o = w.Oby, c = null, cv = null, ct = null,
		el = d.getElementById('hikashop_bundle_nb_add_valuehidden');
	if(el) {
		c = parseInt(el.value);
		el = d.getElementById('hikashop_bundle_nb_add_valuetext');
		if(el) ct = el.innerHTML;
	}
	el = d.getElementById('hikashop_bundle_qty_add');
	if(el) cv = parseInt(el.value);

	if(c === null || isNaN(c) || c === 0 || isNaN(cv) || cv === 0)
		return false;

	var htmlblocks = { NAME: ct, ID: c, INPUT_NAME: 'data[product][bundle][' + c + ']', VALUE: cv };
	w.hikashop.dupRow('hikashop_bundle_row_template', htmlblocks);
	w.productMgr.cancelNewBundle();
	return false;
};
</script>
