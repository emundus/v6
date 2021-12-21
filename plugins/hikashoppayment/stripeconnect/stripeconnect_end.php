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

$js = '
window.hikashop.ready(function(){

var stripe = Stripe("'.trim($this->payment_params->publishable_key).'");
var elements = stripe.elements();
var style = {
	base: {
		color: "#32325d",
		lineHeight: "18px",
		fontFamily: \'"Helvetica Neue", Helvetica, sans-serif\',
		fontSmoothing: "antialiased",
		fontSize: "16px",
		"::placeholder": {
			color: "#aab7c4"
		}
	},
	invalid: {
		color: "#fa755a",
		iconColor: "#fa755a"
	}
};
var card = elements.create("card", {style: style});
card.mount("#stripe-card-element");

card.addEventListener("change", function(event) {
  var displayError = document.getElementById("stripe-card-errors");
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = "";
  }
});

var form = document.getElementById("stripe-payment-form");
form.addEventListener("submit", function(event) {
  event.preventDefault();
  if(form.tokenCreation) return;
  form.tokenCreation = true;
  var additionalData = '.json_encode(@$this->additional_data).';
  stripe.createPaymentMethod("card", card, additionalData).then(function(result) {
    if (result.error) {
      var errorElement = document.getElementById("stripe-card-errors");
      errorElement.textContent = result.error.message;
	  form.tokenCreation = false;
    } else {
      stripePaymentHandler(result.paymentMethod);
    }
  });
});

function stripePaymentHandler(paymentMethod) {
  var form = document.getElementById("stripe-payment-form");
  var hiddenInput = document.createElement("input");
  hiddenInput.setAttribute("type", "hidden");
  hiddenInput.setAttribute("name", "stripePaymentMethod");
  hiddenInput.setAttribute("value", paymentMethod.id);
  form.appendChild(hiddenInput);
  form.submit();
}

});
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
	<div class="form-row">
		<label for="card-element">
			<?php echo JText::_('CREDIT_OR_DEBIT_CARD'); ?>
		</label>
<?php if(!empty($this->order_total)) { ?>
		<p><?php
			echo JText::sprintf('STRIPE_PAY_X', $this->order_total);
		?></p>
<?php } ?>
		<div id="stripe-card-element">
			<!-- a Stripe Element will be inserted here. -->
			<p><?php echo JText::_('STRIPE_PLEASE_WAIT'); ?></p>
		</div>
		<!-- Used to display form errors -->
		<div id="stripe-card-errors" role="alert"></div>
	</div>
	<?php echo JHTML::_('form.token'); ?>
	<button class="StripeButton"><?php echo JText::_('STRIPE_SUBMIT_PAYMENT'); ?></button>
</form>
