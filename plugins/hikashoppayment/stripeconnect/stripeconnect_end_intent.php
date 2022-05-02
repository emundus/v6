<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.6
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

$doc = JFactory::getDocument();
$doc->addScript('https://js.stripe.com/v3/');

$plugin_js = JURI::base(true).'/media/plg_stripeconnect/stripeconnect.js?v=1-0-6';
$doc->addScript($plugin_js);

$js = '
window.hikashop.ready(function(){var init = function(){
	if(!window.stripeConnect || typeof(Stripe) == "undefined") return setTimeout(init, 200);
	var i = window.stripeConnect.init('.$this->method_id.', '.json_encode(array(
		'authData' => array('pub' => $this->publishable_key),
		'mode' => 'method',
		'additional' => $this->additional_data,
		'notify' => $this->notifyurl_js,
	)).');
	window.stripeConnect.registerFormEvent('.$this->method_id.');
}; init(); });
';
$doc->addScriptDeclaration($js);

$css = '
#stripe-payment-form {
	max-width: 450px;
	margin: 0 auto;
}
.StripeElement {
	background-color: white;
	height: 40px;
	padding: 10px 12px;
	border-radius: 4px;
	border: 1px solid transparent;
	box-shadow: 0 1px 3px 0 #e6ebf1;
	-webkit-transition: box-shadow 150ms ease;
	transition: box-shadow 150ms ease;
	box-sizing: border-box;
}
.StripeElement--focus {
	box-shadow: 0 1px 3px 0 #cfd7df;
}
.StripeElement--invalid {
	border-color: #fa755a;
}
.StripeElement--webkit-autofill {
	background-color: #fefde5 !important;
}
.StripeButton {
	border: none;
	border-radius: 4px;
	outline: none;
	text-decoration: none;
	color: #fff;
	background: #32325d;
	white-space: nowrap;
	height: 40px;
	line-height: 40px;
	padding: 0 14px;
	box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
	border-radius: 4px;
	font-size: 15px;
	font-weight: 600;
	letter-spacing: 0.025em;
	text-decoration: none;
	-webkit-transition: all 150ms ease;
	transition: all 150ms ease;
	margin-top: 12px;
}
';
$doc->addStyleDeclaration($css);

?>
<form action="<?php echo $this->notifyurl; ?>" method="post" id="stripe-payment-form">
<div id="stripe-payment-container">
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
	<div id="hk_co_p_c_STRIPEC_container_'<?php echo $this->method_id; ?>'">
		<div class="stripe-payment-form-row">
			<label for="hk_co_p_c_STRIPEC_elements_<?php echo $this->method_id; ?>" class="stripe-card-label">
				<?php echo JText::_('CREDIT_OR_DEBIT_CARD'); ?>
			</label>
<?php if(!empty($this->order_total)) { ?>
			<p><?php
				echo JText::sprintf('STRIPE_PAY_X', $this->order_total);
			?></p>
<?php } ?>
			<div id="hk_co_p_c_STRIPEC_elements_<?php echo $this->method_id; ?>">
				<p><?php echo JText::_('STRIPE_PLEASE_WAIT'); ?></p>
			</div>
			<div id="hk_co_p_c_STRIPEC_errors_<?php echo $this->method_id; ?>" role="alert"></div>
		</div>
	</div>
	<input type="hidden" name="payment_method_id" id="hk_co_p_c_STRIPEC_MET_<?php echo $this->method_id; ?>" value="" />
	<?php echo JHTML::_('form.token'); ?>
	<button class="StripeButton"><?php echo JText::_('STRIPE_SUBMIT_PAYMENT'); ?></button>
</div>
</form>
<div class="hikashop_stripeconnect_thankyou" id="hikashop_stripeconnect_thankyou" style="display:none;">
	<span id="hikashop_stripeconnect_thankyou_message" class="hikashop_stripeconnect_thankyou_message">
<?php
	echo JText::_('THANK_YOU_FOR_PURCHASE');
	if(!empty($this->payment_params->return_url)) {
		echo '<br/><a href="'.$this->payment_params->return_url.'">'.JText::_('GO_BACK_TO_SHOP').'</a>';
	}
?>
	</span>
</div>
<?php  ?>
