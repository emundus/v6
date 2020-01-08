<?php
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$shoppingbutton = $ini_array['saveoption'];
$btnclass = $ini_array['btnclass'];

if (VmConfig::get('oncheckout_show_steps', 1)) {
	echo '<div class="checkoutStep" id="checkoutStep2">' . vmText::_('COM_VIRTUEMART_USER_FORM_CART_STEP2') . '</div>';
}

if ($this->layoutName!='default') {
$headerLevel = 1;
if($this->cart->getInCheckOut()){
	$buttonclass = 'button ';
} else {
	$buttonclass = '';
}
?>
<form method="post" id="shipmentForm" name="chooseShipmentRate" action="<?php echo JRoute::_('index.php'); ?>" class="form-validate">
	<?php
	} else {
		$headerLevel = 3;
		$buttonclass = '';
	}

	if($this->cart->virtuemart_shipmentmethod_id){
		echo '<h'.$headerLevel.' class="vm-shipment-header-selected  ttr_page_title">'.vmText::_('COM_VIRTUEMART_CART_SELECTED_SHIPMENT_SELECT').'</h'.$headerLevel.'>';
	} else {
		echo '<h'.$headerLevel.' class="vm-shipment-header-select  ttr_page_title">'.vmText::_('COM_VIRTUEMART_CART_SELECT_SHIPMENT').'</h'.$headerLevel.'>';
	}


	?>

	<?php
	if ($this->found_shipment_method ) {

		echo '<fieldset class="vm-payment-shipment-select vm-shipment-select payment_shipment_content">';
		// if only one Shipment , should be checked by default
		foreach ($this->shipments_shipment_rates as $shipment_shipment_rates) {
			if (is_array($shipment_shipment_rates)) {
				foreach ($shipment_shipment_rates as $shipment_shipment_rate) {
					echo '<div class="vm-shipment-plugin-single" style="padding:8px 0 0 10px;">'.$shipment_shipment_rate.'</div>';
				}
			}
		}
		echo '</fieldset>';
	} else {
		echo '<h'.$headerLevel.' class="payment_shipment_content">'.$this->shipment_not_found_text.'</h'.$headerLevel.'>';
	}?>

	<div class="buttonBar-right">
		<?php $dynUpdate = '';
		if( VmConfig::get('oncheckout_ajax',false)) {
		$dynUpdate=' data-dynamic-update="1" ';
		} ?>
		<button name="updatecart" class="<?php echo $buttonclass.$shoppingbutton; ?>" type="submit" <?php echo $dynUpdate ?> ><?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?></button>

		<?php   if ($this->layoutName!='default') { ?>
			<button class="<?php echo $buttonclass.$btnclass; ?>" type="reset" onClick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart&task=cancel'); ?>'" ><?php echo vmText::_('COM_VIRTUEMART_CANCEL'); ?></button>
		<?php  } ?>
	</div>

	<?php
	if ($this->layoutName!='default') {
	?> <input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="updatecart" />
	<input type="hidden" name="controller" value="cart" />
</form>
<?php
}
?>