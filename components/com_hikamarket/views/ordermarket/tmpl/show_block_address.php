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
$type = @$this->block_show_address;
if(!in_array($type, array('shipping', 'billing')))
	return;
if(!hikamarket::acl('order/show/'.$type.'address'))
	return;

if(empty($this->ajax)) { ?>
<div id="hikamarket_order_block_<?php echo $type; ?>address">
<?php } ?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>
<?php

if($type == 'billing' || empty($this->order->override_shipping_address)) {
	$address = ($type == 'billing') ? @$this->addresses[(int)$this->order->order_billing_address_id] : @$this->addresses[(int)$this->order->order_shipping_address_id];

	if($this->address_mode == 0) {
		echo $this->addressClass->maxiFormat($address, $this->address_fields, true);
	} else {
?>
	<dl class="hikam_options dl_glue">
<?php
		$fields = (isset($this->order->{$type.'_fields'}) ? $this->order->{$type.'_fields'} : $this->order->fields);
		foreach($fields as $field) {
			if(empty($field->field_frontcomp) && strpos($field->field_display, ';vendor_order_show=1;') === false)
				continue;

			$fieldname = $field->field_namekey;
?>
		<dt class="hikamarket_<?php echo $type; ?>order_address_<?php echo $fieldname;?>"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></dt>
		<dd class="hikamarket_<?php echo $type; ?>order_address_<?php echo $fieldname;?>"><span><?php echo $this->fieldsClass->show($field, @$address->$fieldname);?></span></dd>
<?php
		}
?>
	</dl>
<?php
	}
} else {
	echo $this->order->override_shipping_address;
}

if(!empty($this->ajax))
	return;
?>
</div>
<script type="text/javascript">
window.Oby.registerAjax('orderMgr.<?php echo $type; ?>address',function(params){
	if(params && params.src && params.src == '<?php echo $type; ?>address') return;
	window.orderMgr.refreshBlock('<?php echo $type; ?>address');
});
</script>
