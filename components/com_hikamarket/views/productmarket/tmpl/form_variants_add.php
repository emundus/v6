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
if($this->subtask != 'duplicate') {
	$populateMode = 'add';
?>
<dl>
<?php foreach($this->characteristics as $characteristic) { ?>
	<dt><?php echo $characteristic->characteristic_value; ?></dt>
	<dd><?php
		echo $this->nameboxVariantType->display(
			'data[variant_add][' . $characteristic->characteristic_id . '][]',
			null,
			hikamarketNameboxType::NAMEBOX_MULTIPLE,
			'characteristic_value',
			array(
				'add' => true,
				'vendor' => $this->vendor->vendor_id,
				'url_params' => array('ID' => $characteristic->characteristic_id)
			)
		);
	?></dd>
<?php } ?>
</dl>
<?php
} else {
	$populateMode = 'duplicate';
?>
<div>
	<select style="width:30%" name="data[variant_duplicate][characteristic]" onchange="window.productMgr.duplicateChangeCharacteristic(this);">
<?php foreach($this->characteristics as $characteristic) { ?>
		<option value="<?php echo $characteristic->characteristic_id; ?>"><?php echo $characteristic->characteristic_value; ?></option>
<?php } ?>
	</select>
	<div style="display:inline-block;width:68%;">
<?php
	if(empty($this->productClass))
		$this->productClass = hikamarket::get('class.product');
	$c = reset($this->characteristics);
	echo $this->nameboxVariantType->display(
		'data[variant_duplicate][variants][]',
		null,
		hikamarketNameboxType::NAMEBOX_MULTIPLE,
		'characteristic_value',
		array(
			'add' => true,
			'vendor' => $this->vendor->vendor_id,
			'url_params' => array('ID' => $c->characteristic_id)
		)
	);
?>
	</div>
</div>
<script type="text/javascript">
window.productMgr.duplicateChangeCharacteristic = function(el) {
	var w = window, d = document,
		u = '<?php echo hikamarket::completeLink('characteristic&task=findList&characteristic_type=value&characteristic_parent_id={ID}', true, false, true); ?>',
		a = '<?php echo hikamarket::completeLink('characteristic&task=add&characteristic_type=value&characteristic_parent_id={ID}&tmpl=json', true, false, true); ?>';
	var n = w.oNameboxes['data_variant_duplicate_variants'];
	if(!n) return true;
	n.changeUrl(u.replace('{ID}', el.value), {add: a.replace('{ID}', el.value)});
	return true;
};
</script>
<?php } ?>
<div style="clear:both"></div>
<div style="float:right">
	<button onclick="return window.productMgr.populateVariants('<?php echo $populateMode; ?>');" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_SAVE'); ;?></button>
</div>
<button onclick="return window.productMgr.cancelPopulateVariants();" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
<div style="clear:both"></div>
