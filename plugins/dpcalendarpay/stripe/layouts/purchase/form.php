<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary();

$doc = JFactory::getDocument();
$doc->addScript("https://js.stripe.com/v2/");

$doc->addScriptDeclaration("Stripe.setPublishableKey('" . $displayData['params']->get('data-pkey') . "');");
$doc->addScriptDeclaration("jQuery(function($){
					var stripeResponseHandler = function(status, response) {
						$('.control-group').removeClass('error');
						if (response.error) {
							if(response.error.code == 'incorrect_number') {
								$('#control-group-card-number').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_INCORRECT_NUMBER') . "\");
							}else if(response.error.code == 'invalid_number') {
								$('#control-group-card-number').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_INVALID_NUMBER') . "\");
							}else if(response.error.code == 'invalid_expiry_month') {
								$('#control-group-card-expiry').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_INVALID_EXP_MONTH') . "\");
							}else if(response.error.code == 'invalid_expiry_year') {
								$('#control-group-card-expiry').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_INVALID_EXP_YEAR') . "\");
							}else if(response.error.code == 'invalid_cvc') {
								$('#control-group-card-cvc').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_INVALID_CVC') . "\");
							}else if(response.error.code == 'expired_card') {
								$('#control-group-card-expiry').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_EXPIRED_CARD') . "\");
							}else if(response.error.code == 'incorrect_cvc') {
								$('#control-group-card-cvc').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_INCORRECT_CVC') . "\");
							}else if(response.error.code == 'card_declined') {
								$('#control-group-card-number').addClass('error');
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_CARD_DECLINED') . "\");
							}else if(response.error.code == 'missing') {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_MISSING') . "\");
							}else if(response.error.code == 'processing_error') {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_PROCESSING_ERROR') . "\");
							}else if(status == 401) {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_UNAUTHORIZED') . "\");
							}else if(status == 402) {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_REQUEST_FAILED') . "\");
							}else if(status == 404) {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_NOT_FOUND') . "\");
							}else if(status >= 500) {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_SERVER_ERROR') . "\");
							}else {
								$('#payment-errors').text(\"" . JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_UNKNOWN_ERROR') . "\");
							}
							$('#payment-errors').show();
							$('#payment-button').removeAttr('disabled');
						} else {
							$('#payment-errors').hide();
							var token = response.id;
							$('#token').val(token);
							$('#payment-form').submit();
						}
					};

					$('#payment-form').submit(function(e){
						var token = $('#token').val();
						if(!!token) {
							return true;
						}else{
							$('#payment-button').attr('disabled', 'disabled');
							Stripe.createToken({
								name:$('#card-holder').val(),
								number:$('#card-number').val(),
								exp_month:$('#card-expiry-month').val(),
								exp_year:$('#card-expiry-year').val(),
								cvc:$('#card-cvc').val()
							}, stripeResponseHandler);
							return false;
						}
					});
				});
			");

$booking = $displayData['booking'];
?>

<h3><?php echo JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_HEADER') ?></h3>
<div id="payment-errors" class="alert alert-error" style="display: none;"></div>

<div class="form-horizontal">
	<div class="control-group" id="control-group-card-holder">
		<label for="card-holder" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_CARDHOLDER') ?>
		</label>
		<div class="controls">
			<input type="text" name="card-holder" id="card-holder" class="input-large" value="<?php echo $booking->name ?>" />
		</div>
	</div>
	<div class="control-group" id="control-group-card-number">
		<label for="card-number" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_CC') ?>
		</label>
		<div class="controls">
			<input type="text" name="card-number" id="card-number" class="input-large" value=""/>
		</div>
	</div>
	<div class="control-group" id="control-group-card-expiry">
		<label for="card-expiry" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_EXPDATE') ?>
		</label>
		<div class="controls">
			<?php
			$options = array();
			$options[] = JHTML::_('select.option', 0, '--');
			for ($i = 1; $i <= 12; $i++)
			{
				$m = sprintf('%02u', $i);
				$options[] = JHTML::_('select.option', $m, $m);
			}

			echo JHTML::_('select.genericlist', $options, 'expiryMonth', 'class="input-small"', 'value', 'text', '', 'card-expiry-month');
			?>
			<span> / </span>
			<?php
			$year = (int) gmdate('Y');

			$options = array();
			$options[] = JHTML::_('select.option', 0, '--');
			for ($i = 0; $i <= 10; $i++)
			{
				$y = sprintf('%04u', $i + $year);
				$options[] = JHTML::_('select.option', $y, $y);
			}

			echo JHTML::_('select.genericlist', $options, 'card-expiry-year', 'class="input-small"', 'value', 'text', '', 'card-expiry-year');
			?>
		</div>
	</div>
	<div class="control-group" id="control-group-card-cvc">
		<label for="card-cvc" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_CVC') ?>
		</label>
		<div class="controls">
			<input type="text" name="card-cvc" id="card-cvc" class="input-mini" value=""/>
		</div>
	</div>
</div>

<form id="payment-form" action="<?php echo $displayData['returnUrl'] ?>" method="post" class="form form-horizontal">
	<input type="hidden" name="currency" id="currency" value="<?php echo DPCalendarHelper::getComponentParameter('currency', 'USD') ?>" />
	<input type="hidden" name="amount" id="amount" value="<?php echo $booking->price ?>" />
	<input type="hidden" name="token" id="token" />
	<div class="control-group">
		<label for="pay" class="control-label" style="width:190px; margin-right:20px;">
		</label>
		<div class="controls">
			<input type="submit" id="payment-button" class="btn" value="<?php echo JText::_('PLG_DPCALENDARPAY_STRIPE_FORM_PAYBUTTON') ?>" />
		</div>
	</div>
</form>