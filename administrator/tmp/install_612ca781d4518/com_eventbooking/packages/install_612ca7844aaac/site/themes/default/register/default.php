<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

EventbookingHelperJquery::validateForm();

if (EventbookingHelper::isJoomla4())
{
    $containerClass = ' eb-container-j4';
}
else
{
	$containerClass = '';
}

/* @var  $this EventbookingViewRegisterHtml */

if ($this->waitingList)
{
	$headerText = Text::_('EB_JOIN_WAITINGLIST');

	if (strlen(strip_tags($this->message->{'waitinglist_form_message' . $this->fieldSuffix})))
	{
		$msg = $this->message->{'waitinglist_form_message' . $this->fieldSuffix};
	}
	else
	{
		$msg = $this->message->waitinglist_form_message;
	}
}
else
{
	$headerText = Text::_('EB_INDIVIDUAL_REGISTRATION');

	if ($this->fieldSuffix && strlen(strip_tags($this->event->{'registration_form_message' . $this->fieldSuffix})))
	{
		$msg = $this->event->{'registration_form_message' . $this->fieldSuffix};
	}
	elseif ($this->fieldSuffix && strlen(strip_tags($this->message->{'registration_form_message' . $this->fieldSuffix})))
	{
		$msg = $this->message->{'registration_form_message' . $this->fieldSuffix};
	}
	elseif (strlen(strip_tags($this->event->registration_form_message)))
	{
		$msg = $this->event->registration_form_message;
	}
	else
	{
		$msg = $this->message->registration_form_message;
	}

	$msg = str_replace('[AMOUNT]', EventbookingHelper::formatCurrency($this->amount, $this->config, $this->event->currency_symbol), $msg);
}

$replaces = EventbookingHelperRegistration::buildEventTags($this->event, $this->config);

foreach ($replaces as $key => $value)
{
	$key        = strtoupper($key);
	$msg        = str_replace("[$key]", $value, $msg);
	$headerText = str_replace("[$key]", $value, $headerText);
}

if ($this->config->use_https)
{
	$url = Route::_('index.php?option=com_eventbooking&task=register.process_individual_registration&Itemid=' . $this->Itemid, false, 1);
}
else
{
	$url = Route::_('index.php?option=com_eventbooking&task=register.process_individual_registration&Itemid=' . $this->Itemid, false);
}

$selectedState = '';

/* @var EventbookingHelperBootstrap $bootstrapHelper*/
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
?>
<div id="eb-individual-registration-page" class="eb-container<?php echo $containerClass; ?><?php echo $this->waitingList ? ' eb-waitinglist-individual-registration-form' : '';?>">
	<h1 class="eb-page-heading"><?php echo $headerText; ?></h1>
	<?php
	if (strlen($msg))
	{
	?>
		<div class="eb-message"><?php echo HTMLHelper::_('content.prepare', $msg); ?></div>
	<?php
	}

	if (!empty($this->ticketTypes))
	{
		echo $this->loadTemplate('tickets');
	}

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

		if ($this->collectMembersInformation)
        {
	    ?>
            <div class="clearfix" id="tickets_members_information">
                <?php
                    if (isset($this->ticketsMembers))
                    {
                        echo $this->ticketsMembers;
                    }
                ?>
            </div>
            <h3 class="eb-heading"><?php echo Text::_('EB_BILLING_INFORMATION'); ?></h3>
	    <?php
        }

		$fields = $this->form->getFields();

		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}

		foreach ($fields as $field)
		{
		    if ($field->position == 1)
            {
                continue;
            }

		    echo $field->getControlGroup($bootstrapHelper);
		}

	    if ($this->totalAmount > 0 || (!empty($this->ticketTypes) && EventbookingHelperRegistration::showPriceColumnForTicketType($this->event->id)) || $this->form->containFeeFields())
		{
			$showPaymentInformation = true;
			$showTaxAmount          = ($this->event->tax_rate > 0);

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
        }

        $hasTicketDiscountRules = false;

        if (!empty($this->ticketTypes))
        {
            foreach ($this->ticketTypes as $ticketType)
            {
                if (trim($ticketType->discount_rules))
                {
                    $hasTicketDiscountRules = true;
                    break;
                }
            }
        }

        $nullDate = Factory::getDbo()->getNullDate();
        $showDiscountAmount = $this->enableCoupon
                || $hasTicketDiscountRules
                || $this->discountAmount > 0
                || $this->discountRate > 0
                || $this->bundleDiscountAmount > 0
                || ($this->event->early_bird_discount_date && ($this->event->early_bird_discount_date != $nullDate) && $this->event->date_diff >= 0);

        $hasLateFee = ($this->event->late_fee_date != $nullDate)
            && $this->event->late_fee_date_diff >= 0
            && $this->event->late_fee_amount > 0;

        $showTaxAmount = $this->event->tax_rate > 0;

        $layoutData['currencySymbol']     = $this->event->currency_symbol ?: $this->config->currency_symbol;
        $layoutData['onCouponChange']     = 'calculateIndividualRegistrationFee();';
        $layoutData['addOnClass']         = $addOnClass;
        $layoutData['inputPrependClass']  = $inputPrependClass;
        $layoutData['inputAppendClass']   = $inputAppendClass;
        $layoutData['showDiscountAmount'] = $showDiscountAmount;
        $layoutData['showTaxAmount']      = $showTaxAmount;
        $layoutData['showGrossAmount']    = ($showDiscountAmount || $showTaxAmount || $hasLateFee || $this->showPaymentFee);

		echo $this->loadCommonLayout('register/register_payment_amount.php', $layoutData);

		if (!$this->waitingList)
		{
			$layoutData['registrationType'] = 'individual';
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
		<div class="<?php echo $controlGroupClass;  ?>">
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
			<input type="button" class="<?php echo $btnPrimary; ?>" name="btnBack" value="<?php echo  Text::_('EB_BACK') ;?>" onclick="window.history.go(-1);" />
			<input type="submit" class="<?php echo $btnPrimary; ?>" name="btn-submit" id="btn-submit" value="<?php echo $buttonText;?>" />
			<img id="ajax-loading-animation" alt="<?php echo Text::_('EB_PROCESSING'); ?>" src="<?php echo Uri::base(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" style="display: none;"/>
		</div>
	<?php
	if (count($this->methods) == 1)
	{
	?>
		<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>" />
	<?php
	}

	if (!empty($this->ticketTypes))
    {
        $hasTicketTypes = true;
    }
	else
    {
        $hasTicketTypes = false;
    }

	if ($this->amount == 0 && !empty($showPaymentInformation))
    {
        $hidePaymentInformation = true;
    }
	else
    {
        $hidePaymentInformation = false;
    }

	EventbookingHelperPayments::writeJavascriptObjects();

	$calculateIndividualRegistrationFeeUrl = Route::_('index.php?option=com_eventbooking&task=register.calculate_individual_registration_fee' . EventbookingHelper::getLangLink() . '&Itemid=' . $this->Itemid, false);

	Factory::getDocument()->addScriptDeclaration('var eb_current_page = "default";')
            ->addScriptOptions('hidePaymentInformation', $hidePaymentInformation)
            ->addScriptOptions('hasTicketTypes', $hasTicketTypes)
            ->addScriptOptions('selectedState', $selectedState)
			->addScriptOptions('calculateIndividualRegistrationFeeUrl', $calculateIndividualRegistrationFeeUrl);

	EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-register-default.min.js');

	if (isset($this->fees['show_vat_number_field']))
	{
		Factory::getDocument()->addScriptOptions('showVatNumberField', (bool) $this->fees['show_vat_number_field']);
	}
	?>
	<input type="hidden" id="ticket_type_values" name="ticket_type_values" value="" />
	<input type="hidden" name="event_id" id="event_id" value="<?php echo $this->event->id ; ?>" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$this->showPaymentFee ; ?>" />
	<input type="hidden" id="card-nonce" name="nonce" />
    <?php echo HTMLHelper::_( 'form.token' ); ?>
	</form>
</div>
