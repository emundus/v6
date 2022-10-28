<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('calendar', '', 'id', 'name');
HTMLHelper::_('bootstrap.tooltip');

EventbookingHelperJquery::validateForm();

if (EventbookingHelper::isJoomla4())
{
	$containerClass = ' eb-container-j4';
}
else
{
	$containerClass = '';
}

if ($this->config->accept_term ==1 && !$this->config->fix_term_and_condition_popup)
{
	EventbookingHelperJquery::colorbox();
}

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
	$headerText = Text::_('EB_GROUP_REGISTRATION');

	if ($this->fieldSuffix && strlen(strip_tags($this->event->{'registration_form_message_group' . $this->fieldSuffix})))
	{
		$msg = $this->event->{'registration_form_message_group' . $this->fieldSuffix};
	}
	elseif ($this->fieldSuffix && strlen(strip_tags($this->message->{'registration_form_message_group' . $this->fieldSuffix})))
	{
		$msg = $this->message->{'registration_form_message_group' . $this->fieldSuffix};
	}
	elseif (strlen(strip_tags($this->event->registration_form_message_group)))
	{
		$msg = $this->event->registration_form_message_group;
	}
	else
	{
		$msg = $this->message->registration_form_message_group;
	}
}

$replaces = EventbookingHelperRegistration::buildEventTags($this->event, $this->config);

foreach ($replaces as $key => $value)
{
    $key        = strtoupper($key);
    $msg        = str_replace("[$key]", $value, $msg);
    $headerText = str_replace("[$key]", $value, $headerText);
}
?>
<div id="eb-group-registration-form" class="eb-container<?php echo $containerClass; ?><?php echo $this->waitingList ? ' eb-waitinglist-group-registration-form' : '';?>">
	<h1 class="eb-page-title"><?php echo $headerText; ?></h1>
	<?php
	if (strlen($msg))
	{
	?>
		<div class="eb-message"><?php echo HTMLHelper::_('content.prepare', $msg); ; ?></div>
	<?php
	}

	if (!$this->bypassNumberMembersStep)
	{
	?>
		<div id="eb-number-group-members">
			<div class="eb-form-heading">
				<?php echo Text::_('EB_NUMBER_MEMBERS'); ?>
			</div>
			<div class="eb-form-content">

			</div>
		</div>
	<?php
	}

	if ($this->collectMemberInformation)
	{
	?>
		<div id="eb-group-members-information">
			<div class="eb-form-heading">
				<?php echo Text::_('EB_MEMBERS_INFORMATION'); ?>
			</div>
			<div class="eb-form-content"></div>
		</div>
	<?php
	}

	if($this->showBillingStep)
	{
	?>
		<div id="eb-group-billing">
			<div class="eb-form-heading">
				<?php echo Text::_('EB_BILLING_INFORMATION'); ?>
			</div>
			<div class="eb-form-content">

			</div>
		</div>
	<?php
	}

	$langLink                         = EventbookingHelper::getLangLink();
	$numberMembersUrl = Route::_('index.php?option=com_eventbooking&view=register&layout=number_members&event_id=' . $this->event->id . '&format=raw' . $langLink . '&Itemid=' . $this->Itemid, false);
	$groupMembersUrl = Route::_('index.php?option=com_eventbooking&view=register&layout=group_members&event_id=' . $this->event->id . '&format=raw' . $langLink . '&Itemid=' . $this->Itemid, false);
	$groupBillingUrl = Route::_('index.php?option=com_eventbooking&view=register&layout=group_billing&event_id=' . $this->event->id . '&format=raw' . $langLink . '&Itemid=' . $this->Itemid, false);
	$storeNumberMembersUrl = Route::_('index.php?option=com_eventbooking&task=register.store_number_registrants&event_id=' . $this->event->id . '&format=raw' . $langLink . '&Itemid=' . $this->Itemid, false);
	$storeGroupMembersDataUrl = Route::_('index.php?option=com_eventbooking&task=register.validate_and_store_group_members_data&event_id=' . $this->event->id . '&format=raw' . $langLink . '&Itemid=' . $this->Itemid, false);
	$storeGroupBillingDataUrl = Route::_('index.php?option=com_eventbooking&task=register.store_billing_data_and_display_group_members_form&event_id=' . $this->event->id . '&format=raw' . $langLink . '&Itemid=' . $this->Itemid, false);
	$calculateGroupRegistrationFeeUrl = Route::_('index.php?option=com_eventbooking&task=register.calculate_group_registration_fee' . $langLink . '&Itemid=' . $this->Itemid, false);

	Factory::getDocument()->addScriptOptions('defaultStep', $this->defaultStep)
		->addScriptOptions('returnUrl', base64_encode(Uri::getInstance()->toString() . '#group_billing'))
		->addScriptOptions('eventId', $this->event->id)
		->addScriptOptions('Itemid', $this->Itemid)
		->addScriptOptions('numberMembersUrl', $numberMembersUrl)
		->addScriptOptions('groupMembersUrl', $groupMembersUrl)
		->addScriptOptions('groupBillingUrl', $groupBillingUrl)
		->addScriptOptions('storeNumberMembersUrl', $storeNumberMembersUrl)
		->addScriptOptions('storeGroupMembersDataUrl', $storeGroupMembersDataUrl)
		->addScriptOptions('storeGroupBillingDataUrl', $storeGroupBillingDataUrl)
		->addScriptOptions('calculateGroupRegistrationFeeUrl', $calculateGroupRegistrationFeeUrl)
		->addScriptDeclaration('var returnUrl = "' . base64_encode(Uri::getInstance()->toString() . '#group_billing') . '";');

	EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-register-group.min.js');
	?>
</div>