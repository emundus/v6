<?php
defined ('_JEXEC') or die('Restricted access');

JHtml::_ ('behavior.formvalidation');

$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$btnclass = $ini_array['btnclass'];
$checkoutbutton = $ini_array['checkoutbutton'];
$registerationbutton = $ini_array['registerationbutton'];
$shoppingbtnclass = $ini_array['shoppingbtnclass'];

?>

<div id="cart-view" class="cart-view">
<?php if (VmConfig::get ('oncheckout_show_steps', 1) ){
		if($this->checkout_task == 'checkout') {
			echo '<div class="checkoutStep" id="checkoutStep1">' . vmText::_ ('COM_VIRTUEMART_USER_FORM_CART_STEP1') . '</div>';
		} else { //if($this->checkout_task == 'confirm') {
			echo '<div class="checkoutStep" id="checkoutStep4">' . vmText::_ ('COM_VIRTUEMART_USER_FORM_CART_STEP4') . '</div>';
		}
	}  ?>
	<div class="vm-cart-header-container">
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 vm-cart-header">
			<h1 class="ttr_page_title"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TITLE'); ?></h1>
			<div class="payments-signin-button" ></div>
		</div>		
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 vm-continue-shopping">
			<?php // Continue Shopping Button
			if (!empty($this->continue_link_html)) {
				echo '<a class="continue_link '. $shoppingbtnclass.' " href="' . $this->continue_link . '">' . vmText::_ ('COM_VIRTUEMART_CONTINUE_SHOPPING') . '</a>';
			} ?>
		</div>
		<div class="clear"></div>
	</div>

	<?php
	$uri = vmURI::getCleanUrl();
	$uri = str_replace('&tmpl=component','',$uri);
	echo shopFunctionsF::getLoginForm ($this->cart, FALSE,$uri);

	// This displays the form to change the current shopper
	if ($this->allowChangeShopper){
		echo $this->loadTemplate ('shopperform');
	}


	$taskRoute = '';
	?><form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=cart' . $taskRoute, $this->useXHTML, $this->useSSL); ?>">
		<?php
		if(VmConfig::get('multixcart')=='byselection'){
			if (!class_exists('ShopFunctions')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
			echo shopFunctions::renderVendorFullVendorList($this->cart->vendorId);
			?><input type="submit" name="updatecart" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" class="button <?php echo $btnclass; ?>"  style="margin-left: 10px;"/><?php
		}
		echo $this->loadTemplate ('address');
		// This displays the pricelist MUST be done with tables, because it is also used for the emails
		echo $this->loadTemplate ('pricelist');

		if (!empty($this->checkoutAdvertise)) {
			?> <div id="checkout-advertise-box"> <?php
			foreach ($this->checkoutAdvertise as $checkoutAdvertise) {
				?>
				<div class="checkout-advertise">
					<?php echo $checkoutAdvertise; ?>
				</div>
			<?php
			}
			?></div><?php
		}

		echo $this->loadTemplate ('cartfields');

		?> <div class="checkout-button-top"> <?php
			echo $this->checkout_link_html;
			//echo  '<button type="submit" id="checkoutFormSubmit" name="checkout" value="1" class="'.$checkoutbutton.'" data-dynamic-update="1" ><span> Check Out Now </span> </button>';
			?>
			</div>

		<?php // Continue and Checkout Button END ?>
		<input type='hidden' name='order_language' value='<?php echo $this->order_language; ?>'/>
		<input type='hidden' name='task' value='updatecart'/>
		<input type='hidden' name='option' value='com_virtuemart'/>
		<input type='hidden' name='view' value='cart'/>
	</form>


<?php

if(VmConfig::get('oncheckout_ajax',false)){
	vmJsApi::addJScript('updDynamicListeners',"
if (typeof Virtuemart.containerSelector === 'undefined') Virtuemart.containerSelector = '#cart-view';
if (typeof Virtuemart.container === 'undefined') Virtuemart.container = jQuery(Virtuemart.containerSelector);

jQuery(document).ready(function() {
	if (Virtuemart.container)
		Virtuemart.updDynFormListeners();
}); ");
}


vmJsApi::addJScript('vm.checkoutFormSubmit',"
Virtuemart.bCheckoutButton = function(e) {
	e.preventDefault();
	jQuery(this).vm2front('startVmLoading');
	jQuery(this).attr('disabled', 'true');
	jQuery(this).removeClass( 'vm-button-correct' );
	jQuery(this).addClass( 'vm-button' );
	jQuery(this).fadeIn( 400 );
	var name = jQuery(this).attr('name');
	var div = '<input name=\"'+name+'\" value=\"1\" type=\"hidden\">';

	jQuery('#checkoutForm').append(div);
	//Virtuemart.updForm();
	jQuery('#checkoutForm').submit();
}
jQuery(document).ready(function($) {
	jQuery(this).vm2front('stopVmLoading');
	var el = jQuery('#checkoutFormSubmit');
	el.addClass('$checkoutbutton').removeClass('vm-button-correct');
	el.unbind('click dblclick');
	el.on('click dblclick',Virtuemart.bCheckoutButton);
});
	");

if( !VmConfig::get('oncheckout_ajax',false)) {
	vmJsApi::addJScript('vm.STisBT',"
		jQuery(document).ready(function($) {

			if ( $('#STsameAsBTjs').is(':checked') ) {
				$('#output-shipto-display').hide();
			} else {
				$('#output-shipto-display').show();
			}
			$('#STsameAsBTjs').click(function(event) {
				if($(this).is(':checked')){
					$('#STsameAsBT').val('1') ;
					$('#output-shipto-display').hide();
				} else {
					$('#STsameAsBT').val('0') ;
					$('#output-shipto-display').show();
				}
				var form = jQuery('#checkoutFormSubmit');
				form.submit();
			});
		});
	");
}

$this->addCheckRequiredJs();
echo vmJsApi::writeJS();
?>
</div>