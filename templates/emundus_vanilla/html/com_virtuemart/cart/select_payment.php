<?php
defined('_JEXEC') or die('Restricted access');
$addClass="";
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$shoppingbutton = $ini_array['saveoption'];
$btnclass = $ini_array['btnclass'];

if (VmConfig::get('oncheckout_show_steps', 1)) {
	echo '<div class="checkoutStep" id="checkoutStep3">' . vmText::_('COM_VIRTUEMART_USER_FORM_CART_STEP3') . '</div>';
}

if ($this->layoutName!='default') {
	$headerLevel = 1;
	if($this->cart->getInCheckOut()){
		$buttonclass = 'vm-button-correct ';
	} else {
		$buttonclass = '';
	}
	?>
	<form method="post" id="paymentForm" name="choosePaymentRate" action="<?php echo JRoute::_('index.php'); ?>" class="form-validate <?php echo $addClass ?>">
<?php } else {
	$headerLevel = 3;
	$buttonclass = 'vm-button-correct ';
}

if($this->cart->virtuemart_paymentmethod_id){
	echo '<h'.$headerLevel.' class="vm-payment-header-selected  ttr_page_title">'.vmText::_('COM_VIRTUEMART_CART_SELECTED_PAYMENT_SELECT').'</h'.$headerLevel.'>';
} else {
	echo '<h'.$headerLevel.' class="vm-payment-header-select  ttr_page_title">'.vmText::_('COM_VIRTUEMART_CART_SELECT_PAYMENT').'</h'.$headerLevel.'>';
} ?>

<?php
if ($this->found_payment_method ) 
{
	echo '<fieldset class="vm-payment-shipment-select vm-payment-select payment_shipment_content">';
	foreach ($this->paymentplugins_payments as $paymentplugin_payments) {
		if (is_array($paymentplugin_payments)) {
			foreach ($paymentplugin_payments as $paymentplugin_payment) {
				echo '<div class="vm-payment-plugin-single" style="padding:8px 0 0 10px; " >'.$paymentplugin_payment.'</div>';
			}
		}
	}
	echo '</fieldset>';

} else {
	echo '<h1 class="payment_shipment_content">'.$this->payment_not_found_text.'</h1>';
}
?>
<div class="buttonBar-right">
		<?php
		$dynUpdate = '';
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
	?>    <input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="cart" />
	<input type="hidden" name="task" value="updatecart" />
	<input type="hidden" name="controller" value="cart" />
	</form>
<?php
}
?>