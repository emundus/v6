<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/* @var EventbookingViewRegisterHtml $this */

if ($this->config->use_https)
{
	$url = Route::_('index.php?option=com_eventbooking&task=register.process_group_registration&Itemid=' . $this->Itemid, false, 1);
}
else
{
	$url = Route::_('index.php?option=com_eventbooking&task=register.process_group_registration&Itemid=' . $this->Itemid, false);
}

$selectedState = '';

$bootstrapHelper     = $this->bootstrapHelper;
$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass   = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass    = $bootstrapHelper->getClassMapping('input-append');
$addOnClass          = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass   = $bootstrapHelper->getClassMapping('control-label');
$controlsClass       = $bootstrapHelper->getClassMapping('controls');
$btnPrimary          = $bootstrapHelper->getClassMapping('btn btn-primary');
$formHorizontalClass = $bootstrapHelper->getClassMapping('form form-horizontal');

$layoutData = array(
	'controlGroupClass' => $controlGroupClass,
	'controlLabelClass' => $controlLabelClass,
	'controlsClass'     => $controlsClass,
);

if (!$this->userId && ($this->config->user_registration || $this->config->show_user_login_section))
{
	$validateLoginForm = true;
	echo $this->loadCommonLayout('register/register_login.php', $layoutData);
}
else
{
	$validateLoginForm = false;
}
?>
<form method="post" name="adminForm" id="adminForm" action="<?php echo $url; ?>" autocomplete="off" class="<?php echo $formHorizontalClass; ?>" enctype="multipart/form-data">
<?php
	if (!$this->userId && $this->config->user_registration)
	{
		echo $this->loadCommonLayout('register/register_user_registration.php', $layoutData);
	}

	$fields = $this->form->getFields();

	if (isset($fields['state']))
	{
		$selectedState = $fields['state']->value;
	}

	$dateFields = array();

	foreach ($fields as $field)
	{
	    if ($field->position == 1)
        {
            continue;
        }

	    echo $field->getControlGroup($bootstrapHelper);

		if ($field->type == "Date")
		{
			$dateFields[] = $field->name;
		}
	}

	if (($this->totalAmount > 0) || $this->form->containFeeFields())
	{
	?>
		<h3 class="eb-heading"><?php echo Text::_('EB_PAYMENT_INFORMATION'); ?></h3>
	<?php
        foreach ($fields as $field)
        {
	        if ($field->position == 0)
	        {
		        continue;
	        }

	        echo $field->getControlGroup($bootstrapHelper);

	        if ($field->type == "Date")
	        {
		        $dateFields[] = $field->name;
	        }
        }

		$layoutData['currencySymbol']     = $this->event->currency_symbol ?: $this->config->currency_symbol;
		$layoutData['onCouponChange']     = 'calculateGroupRegistrationFee();';
		$layoutData['addOnClass']         = $addOnClass;
		$layoutData['inputPrependClass']  = $inputPrependClass;
		$layoutData['inputAppendClass']   = $inputAppendClass;
		$layoutData['showDiscountAmount'] = ($this->enableCoupon || $this->discountAmount > 0 || $this->bundleDiscountAmount > 0);
		$layoutData['showTaxAmount']      = ($this->event->tax_rate > 0);
		$layoutData['showGrossAmount']    = ($this->enableCoupon || $this->discountAmount > 0 || $this->bundleDiscountAmount > 0 || $this->event->tax_rate > 0 || $this->showPaymentFee);

		echo $this->loadCommonLayout('register/register_payment_amount.php', $layoutData);

		if (!$this->waitingList)
		{
			$layoutData['registrationType'] = 'group';
			echo $this->loadCommonLayout('register/register_payment_methods.php', $layoutData);
		}
	}

    if ($this->config->show_privacy_policy_checkbox || $this->config->show_subscribe_newsletter_checkbox)
    {
	    echo $this->loadCommonLayout('register/register_gdpr.php', $layoutData);
    }

    if ($articleId = $this->getTermsAndConditionsArticleId($this->event, $this->config))
	{
		$layoutData['articleId'] = $articleId;

		echo $this->loadCommonLayout('register/register_terms_and_conditions.php', $layoutData);
	}

	if ($this->showCaptcha)
	{
		if ($this->captchaPlugin == 'recaptcha_invisible')
		{
			$style = ' style="display:none;"';
		}
		else
		{
			$style = '';
		}
	?>
		<div class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>"<?php echo $style; ?>>
				<?php echo Text::_('EB_CAPTCHA'); ?><span class="required">*</span>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->captcha; ?>
			</div>
		</div>
	<?php
	}

	if ($this->waitingList)
	{
		$buttonText = Text::_('EB_PROCESS');
	}
	else
	{
		$buttonText = Text::_('EB_PROCESS_REGISTRATION');
	}
	?>
	<div class="form-actions">
		<input type="button" class="<?php echo $btnPrimary; ?>" name="btn-group-billing-back" id="btn-group-billing-back" value="<?php echo  Text::_('EB_BACK') ;?>">
		<input type="submit" class="<?php echo $btnPrimary; ?>" name="btn-process-group-billing" id="btn-process-group-billing" value="<?php echo $buttonText;?>">
		<img id="ajax-loading-animation" alt="<?php echo Text::_('EB_PROCESSING'); ?>" src="<?php echo Uri::base(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" style="display: none;"/>
	</div>
	<?php
		if (count($this->methods) == 1)
		{
		?>
			<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>" />
		<?php
		}
	?>
	<input type="hidden" name="event_id" value="<?php echo $this->event->id; ?>" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$this->showPaymentFee ; ?>" />
	<input type="hidden" id="card-nonce" name="nonce" />
    <input type="hidden" name="number_registrants" value="<?php echo $this->numberRegistrants; ?>" />
	<script type="text/javascript">
		var eb_current_page = 'group_billing';
			Eb.jQuery(document).ready(function($){
				<?php
					if (count($dateFields))
					{
						echo EventbookingHelperHtml::getCalendarSetupJs($dateFields);
					}

					if ($this->amount == 0)
					{
					?>
						$('.payment_information').css('display', 'none');
					<?php
					}

					if ($this->squareUpEnabled && !$this->waitingList)
                    {
                    ?>
                        sqPaymentForm.build();
                    <?php
                    }
				?>

				$("#adminForm").validationEngine('attach', {
					onValidationComplete: function(form, status){
						if (status == true) {
							form.on('submit', function(e) {
								e.preventDefault();
							});

							var paymentMethod;

							if($('input:radio[name^=payment_method]').length)
							{
								paymentMethod = $('input:radio[name^=payment_method]:checked').val();
							}
							else
							{
								paymentMethod = $('input[name^=payment_method]').val();
							}

                            form.find('#btn-process-group-billing').prop('disabled', true);

                            // Stripe payment method
                            if (paymentMethod.indexOf('os_stripe') == 0)
                            {
                                // Old Stripe method
                                if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible"))
                                {
                                    Stripe.card.createToken({
                                        number: $('#x_card_num').val(),
                                        cvc: $('#x_card_code').val(),
                                        exp_month: $('select[name^=exp_month]').val(),
                                        exp_year: $('select[name^=exp_year]').val(),
                                        name: $('#card_holder_name').val()
                                    }, stripeResponseHandler);

                                    return false;
                                }

                                // Stripe card element
                                if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible"))
                                {
                                    stripe.createToken(card).then(function(result) {
                                        if (result.error) {
                                            // Inform the customer that there was an error.
                                            //var errorElement = document.getElementById('card-errors');
                                            //errorElement.textContent = result.error.message;
                                            alert(result.error.message);
                                            $('#btn-process-group-billing').prop('disabled', false);
                                        } else {
                                            // Send the token to your server.
                                            stripeTokenHandler(result.token);
                                        }
                                    });

                                    return false;
                                }
                            }

							if (paymentMethod == 'os_squareup' && $('#tr_card_number').is(':visible'))
							{
								sqPaymentForm.requestCardNonce();

								return false;
							}

							return true;
						}
						return false;
					}
				});
				<?php
					if ($validateLoginForm)
					{
					?>
						$("#eb-login-form").validationEngine();
					<?php
					}

				?>
				buildStateFields('state', 'country', '<?php echo $selectedState; ?>');

                if (typeof stripe !== 'undefined' && $('#stripe-card-element').length > 0)
                {
                    var style = {
                        base: {
                            // Add your base input styles here. For example:
                            fontSize: '16px',
                            color: "#32325d",
                        }
                    };

                    // Create an instance of the card Element.
                    var card = elements.create('card', {style: style});

                    // Add an instance of the card Element into the `card-element` <div>.
                    card.mount('#stripe-card-element');
                }

				<?php
					if ($this->showCaptcha && $this->captchaPlugin == 'recaptcha')
					{
					?>
						EBInitReCaptcha2();
					<?php
					}
                    elseif ($this->showCaptcha && $this->captchaPlugin == 'recaptcha_invisible')
                    {
                    ?>
                        EBInitReCaptchaInvisible();
                    <?php
                    }
				?>
                var $btnGroupBillingBack = $('#btn-group-billing-back');
                $btnGroupBillingBack.click(function(){
                    var ajaxUrl;

                    if (Joomla.getOptions('storeGroupBillingDataUrl'))
                    {
						ajaxUrl = Joomla.getOptions('storeGroupBillingDataUrl');
                    }
                    else
                    {
                        ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&task=register.store_billing_data_and_display_group_members_form&event_id=<?php echo $this->event->id; ?>&Itemid=<?php echo $this->Itemid; ?>&format=raw' + langLinkForAjax;
                    }

					$.ajax({
						url: ajaxUrl,
						method: 'post',
                        data: $('#adminForm').serialize(),
						dataType: 'html',
						beforeSend: function() {
							$btnGroupBillingBack.attr('disabled', true);
						},
						complete: function() {
							$btnGroupBillingBack.attr('disabled', false);
						},
						success: function(html) {
							$('#eb-group-members-information .eb-form-content').html(html);
							$('#eb-group-billing .eb-form-content').slideUp('slow');
							<?php ($this->collectMemberInformation) ? $idAjax = 'eb-group-members-information' : $idAjax = 'eb-number-group-members';?>
							$('#<?php echo $idAjax; ?> .eb-form-content').slideDown('slow');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				});

				//term colorbox term
				 $(".eb-colorbox-term").colorbox({
					 href: $(this).attr('href'),
					 innerHeight: '80%',
					 innerWidth: '80%',
					 overlayClose: true,
					 iframe: true,
					 opacity: 0.3
				});
				<?php
					if ($this->collectMemberInformation)
					{
					?>
						$('html, body').animate({scrollTop:$('#eb-group-members-information').position().top}, 'slow');
					<?php
					}

                    if (EventbookingHelperRegistration::isEUVatTaxRulesEnabled())
                    {
                    ?>
                        $('#<?php echo $this->config->eu_vat_number_field; ?>').after('<span class="invalid" id="vatnumber_validate_msg" style="display: none;"><?php echo ' ' . Text::_('EB_INVALID_VATNUMBER'); ?></span></div>');

                        $("#country").on('change', function () {
                            calculateGroupRegistrationFee();
                        });

                        $('#<?php echo $this->config->eu_vat_number_field; ?>').on('change', function () {
                            calculateGroupRegistrationFee();
                        });
                    <?php

				        if (!empty($this->fees['show_vat_number_field']))
                        {
                        ?>
                            $('#field_<?php echo $this->config->eu_vat_number_field; ?>').show();
                        <?php
                        }
				        else
                        {
                        ?>
                            $('#field_<?php echo $this->config->eu_vat_number_field; ?>').hide();
                        <?php
                        }
                    }
				?>
			})
	</script>
</form>