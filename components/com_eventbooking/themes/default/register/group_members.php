<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/* @var  $this EventbookingViewRegisterHtml */

$bootstrapHelper     = $this->bootstrapHelper;
$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass   = $bootstrapHelper->getClassMapping('control-label');
$controlsClass       = $bootstrapHelper->getClassMapping('controls');
$btnPrimaryClass     = $bootstrapHelper->getClassMapping('btn btn-primary');
$formHorizontalClass = $bootstrapHelper->getClassMapping('form form-horizontal');

$memberFields = array();

foreach ($this->rowFields as $rowField)
{
	$memberFields[] = $rowField->name;
}

$memberFields = json_encode($memberFields);
?>
<form name="eb-form-group-members" id="eb-form-group-members" action="<?php echo Route::_('index.php?option=com_eventbooking&Itemid='.$this->Itemid); ?>" autocomplete="off" class="<?php echo $formHorizontalClass; ?>" method="post">
<?php
$dateFields = array();

for ($i = 1 ; $i <= $this->numberRegistrants; $i++)
{
	$headerText = Text::_('EB_MEMBER_REGISTRATION') ;
	$headerText = str_replace('[ATTENDER_NUMBER]', $i, $headerText);

	if ($this->config->allow_populate_group_member_data)
	{
	?>
		<div class="<?php echo $controlGroupClass; ?> clearfix">
			<h3 class="eb-heading">
				<?php echo $headerText ?>
			</h3>
			<?php
			if ($i > 1)
			{
				$options = array();
				$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_POPULATE_DATA_FROM'));

				for ($j = 1 ; $j < $i ; $j++)
				{
					$options[] = HTMLHelper::_('select.option', $j, Text::sprintf('EB_MEMBER_NUMBER', $j));
				}

				echo HTMLHelper::_('select.genericlist', $options, 'member_number_' . $i, 'id="member_number_' . $i . '" class="input-large eb-member-number-select" onchange="populateMemberFormData(' . $i . ', this.value)"', 'value', 'text', 0);
			}
			?>
		</div>
	<?php
	}
	else
	{
	?>
		<h3 class="eb-heading">
			<?php echo $headerText ?>
		</h3>
	<?php
	}

	$currentMemberFields = EventbookingHelperRegistration::getGroupMemberFields($this->rowFields, $i);

	$form = new RADForm($currentMemberFields);
	$form->setFieldSuffix($i);

	if (!isset($this->membersData['country_' . $i]))
	{
		$this->membersData['country_' . $i] = $this->defaultCountry;
	}

	$form->bind($this->membersData, $this->useDefaultValueForFields);

	$form->buildFieldsDependency();

	if (!$this->waitingList)
	{
		$form->setEventId($this->event->id);
	}

	$fields = $form->getFields();

	//We don't need to use ajax validation for email field for group members
	if (isset($fields['email']))
	{
		/* @var RADFormField $emailField */
		$emailField = $fields['email'];
		$cssClass = $emailField->getAttribute('class');
		$cssClass = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
		$emailField->setAttribute('class', $cssClass);
	}

	foreach ($fields as $field)
	{
		$cssClass = $field->getAttribute('class');
		$cssClass = str_replace('equals[email]', 'equals[email_' . $i . ']', $cssClass);
		$field->setAttribute('class', $cssClass);

		echo $field->getControlGroup($bootstrapHelper);

		if ($field->type == 'Date')
		{
			$dateFields[] = $field->name;
		}
	}
}

$layoutData = array(
	'controlGroupClass' => $controlGroupClass,
	'controlLabelClass' => $controlLabelClass,
	'controlsClass'     => $controlsClass,
);

if (!$this->showBillingStep
    && ($this->config->show_privacy_policy_checkbox || $this->config->show_subscribe_newsletter_checkbox))
{
	echo $this->loadCommonLayout('register/register_gdpr.php', $layoutData);
}

if (!$this->showBillingStep && $articleId = $this->getTermsAndConditionsArticleId($this->event, $this->config))
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
	<div class="<?php echo $controlGroupClass; ?>"<?php echo $style; ?>>
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_CAPTCHA'); ?><span class="required">*</span>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $this->captcha; ?>
		</div>
	</div>
<?php
}
?>
	<div class="form-actions">
		<?php
			if (!$this->bypassNumberMembersStep)
			{
			?>
				<input type="button" id="btn-group-members-back" name="btn-group-members-back" class="<?php echo $btnPrimaryClass; ?>" value="<?php echo Text::_('EB_BACK'); ?>"/>
			<?php
			}
		?>
		<input type="<?php echo $this->showBillingStep ? "button" : "submit";?>" id="btn-process-group-members" name="btn-process-group-members" class="<?php echo $btnPrimaryClass; ?>" value="<?php echo $this->showBillingStep ? Text::_('EB_NEXT'): Text::_('EB_PROCESS_REGISTRATION'); ?>" />
	</div>
	<input type="hidden" name="task" value="<?php echo $this->showBillingStep ? 'register.validate_and_store_group_members_data' : 'register.store_group_members_data'; ?>" />
	<input type="hidden" name="event_id" value="<?php echo $this->eventId; ?>" />
    <input type="hidden" name="number_registrants" value="<?php echo $this->numberRegistrants; ?>" />
	<script type="text/javascript">
		var memberFields = <?php echo $memberFields ?>;
		Eb.jQuery(document).ready(function($){
			<?php
				if ($this->config->allow_populate_group_member_data)
				{
				?>
					populateMemberFormData = (function (currentMemberNumber, fromMemberNumber) {
						if (fromMemberNumber != 0)
						{
							var arrayLength = memberFields.length, selecteds = [], value = '';

							for (var i = 0; i < arrayLength; i++)
							{
								if ($('input[name="' + memberFields[i] + '_' + currentMemberNumber + '[]"]').length)
								{
									//This is a checkbox or multiple select
									selecteds = $('input[name="' + memberFields[i] + '_' + fromMemberNumber + '[]"]:checked').map(function(){return $(this).val();}).get();
									$('input[name="' + memberFields[i] + '_' + currentMemberNumber + '[]"]').val(selecteds);
								}
								else if ($('input[type="radio"][name="' + memberFields[i] + '_' + currentMemberNumber + '"]').length)
								{
									value = $('input[name="' + memberFields[i] + '_' + fromMemberNumber + '"]:checked').val();
									$('input[name="' + memberFields[i] + '_' + currentMemberNumber + '"][value="' + value + '"]').attr('checked', 'checked');
								}
								else
								{
									value = $('#' + memberFields[i] + '_' + fromMemberNumber).val();
									$('#' + memberFields[i] + '_' + currentMemberNumber).val(value);
								}
							}
						}
					})
				<?php
				}

				if (count($dateFields))
				{
					echo EventbookingHelperHtml::getCalendarSetupJs($dateFields);
				}
			?>

            var $formGroupMembers = $("#eb-form-group-members"),
                $btnGroupMembersBack = $('#btn-group-members-back');
            $formGroupMembers.validationEngine();
			<?php
				for($i = 1; $i <= $this->numberRegistrants; $i++)
				{
				?>
					buildStateFields('state_<?php echo $i; ?>', 'country_<?php echo $i; ?>', '');
				<?php
				}

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

                if ($this->showBillingStep)
				{
				?>
                    var $btnProcessGroupMembers = $('#btn-process-group-members');
					$btnProcessGroupMembers.click(function(){
						var formValid = $formGroupMembers.validationEngine('validate');
						if (formValid)
						{
                            var ajaxUrl = '';

                            if (Joomla.getOptions('storeGroupMembersDataUrl'))
						    {
						        ajaxUrl = Joomla.getOptions('storeGroupMembersDataUrl');
						    }
						    else
						    {
								ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&task=register.validate_and_store_group_members_data&event_id=<?php echo $this->event->id; ?>&Itemid=<?php echo $this->Itemid; ?>&format=raw' + langLinkForAjax;
						    }
							$.ajax({
								url: ajaxUrl,
								method: 'post',
								data: $formGroupMembers.serialize(),
                                dataType: 'json',
								beforeSend: function() {
									$btnProcessGroupMembers.attr('disabled', true);
									$btnProcessGroupMembers.after('<span class="wait">&nbsp;<img src="<?php echo Uri::base(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" alt="" /></span>');
									$('.eb-field-validation-error').remove();
								},
								complete: function() {
									$btnProcessGroupMembers.attr('disabled', false);
									$('.wait').remove();
								},
								success: function(json) {
								    var $groupBillingFormContainer = $('#eb-group-billing .eb-form-content'), $email = $('#email');

								    if (json.status == 'OK')
                                    {
                                        $('ul.eb-validation_errors').remove();
                                        $groupBillingFormContainer.html(json.html);
                                        $('#eb-group-members-information .eb-form-content').slideUp('slow');
                                        $groupBillingFormContainer.slideDown('slow');

                                        if ($email.val())
                                        {
                                            $email.validationEngine('validate');
                                        }

                                        $('#return_url').val(returnUrl);

                                        $('#adminForm').find(".hasTooltip").tooltip({"html": true,"container": "body"});
                                    }
                                    else
                                    {
                                        for (var field in json.errors)
                                        {
                                            value = json.errors[field];
                                            $( '<div class="eb-field-validation-error required"> '+ value+'</div>').insertAfter( '#'+field );

                                        }

                                        $('#eb-group-members-information .eb-form-content').prepend(json.html)
                                    }
								},
								error: function(xhr, ajaxOptions, thrownError) {
									alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
								}
							});
						}
					});
				<?php
				}
			?>

            $btnGroupMembersBack.click(function(){
                var ajaxUrl;

                if (Joomla.getOptions('numberMembersUrl'))
                {
                    ajaxUrl = Joomla.getOptions('numberMembersUrl');
                }
                else
                {
                    ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&view=register&layout=number_members&event_id=<?php echo $this->event->id; ?>&Itemid=<?php echo $this->Itemid; ?>&format=raw' + langLinkForAjax;
                }

				$.ajax({
					url: ajaxUrl,
					method: 'post',
					dataType: 'html',
					beforeSend: function() {
						$btnGroupMembersBack.attr('disabled', true);
					},
					complete: function() {
						$btnGroupMembersBack.attr('disabled', false);
					},
					success: function(html) {
					    var $numberGroupMembersFormContainer = $('#eb-number-group-members .eb-form-content');
						$numberGroupMembersFormContainer.html(html);
						$('#eb-group-members-information .eb-form-content').slideUp('slow');
						$numberGroupMembersFormContainer.slideDown('slow');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		});
	</script>
</form>
