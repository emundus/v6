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
	EventbookingHelperJquery::colorbox('a.eb-modal');
}
else
{
	$containerClass = '';
	HTMLHelper::_('behavior.modal', 'a.eb-modal');
}

/* @var EventbookingViewRegisterHtml $this */

if ($this->config->use_https)
{
	$formUrl = Route::_('index.php?option=com_eventbooking&task=cart.process_checkout&Itemid=' . $this->Itemid, false, 1);
}
else
{
	$formUrl = Route::_('index.php?option=com_eventbooking&task=cart.process_checkout&Itemid=' . $this->Itemid, 0);
}

$selectedState = '';

/* @var EventbookingHelperBootstrap $bootstrapHelper */
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
<div id="eb-cart-registration-page" class="eb-container<?php echo $containerClass; ?>">
<h1 class="eb-page-heading"><?php echo Text::_('EB_CHECKOUT'); ?></h1>
<?php
	if (strlen(strip_tags($this->message->{'registration_form_message'.$this->fieldSuffix})))
	{
		$msg = $this->message->{'registration_form_message'.$this->fieldSuffix};
	}
	else
	{
		$msg = $this->message->registration_form_message;
	}

	if (strlen($msg))
	{
		$msg = str_replace('[EVENT_TITLE]', $this->eventTitle, $msg) ;
		$msg = str_replace('[AMOUNT]', EventbookingHelper::formatCurrency($this->amount, $this->config), $msg) ;
	?>
        <div class="eb-message"><?php echo HTMLHelper::_('content.prepare', $msg); ?></div>
	<?php
	}

	echo $this->loadTemplate('items');
?>
<div class="clearfix"></div>
<?php
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
	<form method="post" name="adminForm" id="adminForm" action="<?php echo $formUrl; ?>" autocomplete="off" class="<?php echo $formHorizontalClass; ?>" enctype="multipart/form-data">
	<?php
		if (!$this->userId && $this->config->user_registration)
		{
			echo $this->loadCommonLayout('register/register_user_registration.php', $layoutData);
		}

		$hasMembersFeeField = false;
	    $count = 0;

		// Collect registrants information
		if ($this->config->collect_member_information_in_cart)
		{
			foreach($this->items as $item)
			{
				$rowFields    = EventbookingHelperRegistration::getFormFields($item->id, 2);
				$eventHeading = Text::sprintf('EB_EVENT_REGISTRANTS_INFORMATION', $item->title);
				$eventHeading = str_replace('[EVENT_DATE]', HTMLHelper::_('date', $item->event_date, $this->config->event_date_format, null), $eventHeading);
			?>
				<h3 class="eb-heading"><?php echo $eventHeading; ?></h3>
			<?php
				for ($i = 0 ; $i < $item->quantity; $i++)
				{
					$count++;
					$currentMemberFormFields = EventbookingHelperRegistration::getGroupMemberFields($rowFields, $i + 1);
					$form      = new RADForm($currentMemberFormFields);
					$form->setFieldSuffix($count);

					if (!isset($this->formData['country_' . $count]))
					{
						$formData['country_' . $count] = $this->config->default_country;
					}

					$form->bind($this->formData, $this->useDefault);
					$form->prepareFormFields('calculateCartRegistrationFee();');
					$form->buildFieldsDependency();
					$fields = $form->getFields();

					//We don't need to use ajax validation for email field for group members
					if (isset($fields['email']))
					{
						$emailField = $fields['email'];
						$cssClass   = $emailField->getAttribute('class');
						$cssClass   = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
					}
				?>
					<h4 class="eb-heading"><?php echo Text::sprintf('EB_MEMBER_INFORMATION', $i + 1); ?></h4>
				<?php
					/* @var RADFormField $field */
					foreach ($fields as $field)
					{
						$cssClass = $field->getAttribute('class');
						$cssClass = str_replace('equals[email]', 'equals[email_' . $count . ']', $cssClass);
						$field->setAttribute('class', $cssClass);

						if ($field->row->fee_field)
						{
							$hasMembersFeeField = true;
						}

						echo $field->getControlGroup($bootstrapHelper);
					}
				}
			}
		?>
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
		    if ($field->row->position == 1)
            {
                continue;
            }

		    echo $field->getControlGroup($bootstrapHelper);
		}

		if ($this->totalAmount > 0 || $this->form->containFeeFields() || $hasMembersFeeField)
		{
			$showPaymentInformation = true;
		?>
			<h3 class="eb-heading"><?php echo Text::_('EB_PAYMENT_INFORMATION'); ?></h3>
		<?php
        foreach ($fields as $field)
        {
            if ($field->row->position == 0)
            {
                continue;
            }

            echo $field->getControlGroup($bootstrapHelper);
        }

		$layoutData['currencySymbol']     = $this->config->currency_symbol;
		$layoutData['onCouponChange']     = 'calculateCartRegistrationFee();';
		$layoutData['addOnClass']         = $addOnClass;
		$layoutData['inputPrependClass']  = $inputPrependClass;
		$layoutData['inputAppendClass']   = $inputAppendClass;
		$layoutData['showDiscountAmount'] = ($this->enableCoupon || $this->discountAmount > 0 || $this->bunldeDiscount > 0);
		$layoutData['showTaxAmount']      = ($this->taxAmount > 0);
		$layoutData['showGrossAmount']    = ($this->enableCoupon || $this->discountAmount > 0 || $this->bunldeDiscount > 0 || $this->taxAmount > 0 || $this->showPaymentFee);

		echo $this->loadCommonLayout('register/register_payment_amount.php', $layoutData);

		$layoutData['registrationType'] = 'cart';
		echo $this->loadCommonLayout('register/register_payment_methods.php', $layoutData);
	}

	if ($this->config->show_privacy_policy_checkbox || $this->config->show_subscribe_newsletter_checkbox)
    {
	    echo $this->loadCommonLayout('register/register_gdpr.php', $layoutData);
    }

	if ($this->config->accept_term ==1 && $this->config->article_id)
	{
		$layoutData['articleId'] = $this->config->article_id;
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
	?>
	<div class="form-actions">
		<input type="button" class="<?php echo $btnPrimary; ?>" name="btnBack" value="<?php echo  Text::_('EB_BACK') ;?>" onclick="window.history.go(-1);">
		<input type="submit" class="<?php echo $btnPrimary; ?>" name="btn-submit" id="btn-submit" value="<?php echo Text::_('EB_PROCESS_REGISTRATION');?>">
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
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$this->showPaymentFee ; ?>" />
	<input type="hidden" id="card-nonce" name="nonce" />
    <?php
    if ($this->amount == 0 && !empty($showPaymentInformation))
    {
        $hidePaymentInformation = true;
    }
    else
    {
        $hidePaymentInformation = false;
    }

    EventbookingHelperPayments::writeJavascriptObjects();

    $calculateCartRegistrationFeeUrl = Route::_('index.php?option=com_eventbooking&task=cart.calculate_cart_registration_fee' . EventbookingHelper::getLangLink() . '&Itemid=' . $this->Itemid, false);

    $document = Factory::getDocument();
    $document->addScriptOptions('selectedState', $selectedState)
	    ->addScriptOptions('numberMembers', $count)
        ->addScriptOptions('hidePaymentInformation', $hidePaymentInformation)
	    ->addScriptOptions('cartUrl', Route::_(EventbookingHelperRoute::getViewRoute('cart', $this->Itemid)))
	    ->addScriptOptions('calculateCartRegistrationFeeUrl', $calculateCartRegistrationFeeUrl)
	    ->addScriptDeclaration('var eb_current_page = "cart";');

    EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-register-cart.min.js');

    if (isset($this->fees['show_vat_number_field']))
    {
	    $document->addScriptOptions('showVatNumberField', (bool) $this->fees['show_vat_number_field']);
    }

    echo HTMLHelper::_( 'form.token' );
    ?>
	</form>
</div>