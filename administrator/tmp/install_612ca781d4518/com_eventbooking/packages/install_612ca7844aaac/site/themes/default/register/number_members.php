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
use Joomla\CMS\Uri\Uri;

/* @var  $this EventbookingViewRegisterHtml */

if (strlen(strip_tags($this->message->{'number_members_form_message'.$this->fieldSuffix})))
{
	$msg = $this->message->{'number_members_form_message'.$this->fieldSuffix};
}
else
{
	$msg = $this->message->number_members_form_message;
}

$msg        = str_replace("[MIN_NUMBER_REGISTRANTS]", $this->minNumberRegistrants, $msg);
$msg        = str_replace("[MAX_NUMBER_REGISTRANTS]", $this->maxRegistrants, $msg);

$bootstrapHelper     = $this->bootstrapHelper;
$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass   = $bootstrapHelper->getClassMapping('control-label');
$controlsClass       = $bootstrapHelper->getClassMapping('controls');
$btnPrimaryClass     = $bootstrapHelper->getClassMapping('btn btn-primary');
$formHorizontalClass = $bootstrapHelper->getClassMapping('form form-horizontal');


Factory::getDocument()->addScriptOptions('eventId', $this->event->id)
	->addScriptOptions('Itemid', $this->Itemid)
    ->addScriptOptions('collectMemberInformation', $this->collectMemberInformation);

EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-register-group.min.js');

if (strlen($msg))
{
?>
    <div class="eb-message"><?php echo HTMLHelper::_('content.prepare', $msg); ?></div>
<?php
}
?>
<form name="eb-form-number-group-members" id="eb-form-number-group-members" autocomplete="off" class="<?php echo $formHorizontalClass; ?>">
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>" for="number_registrants">
			<?php echo  Text::_('EB_NUMBER_REGISTRANTS') ?><span class="required">*</span>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<input type="number" class="form-control input-mini validate[required,custom[number],min[<?php echo $this->minNumberRegistrants; ?>],max[<?php echo $this->maxRegistrants; ?>]"
				id="number_registrants" name="number_registrants" value="<?php echo $this->numberRegistrants;?>"
				data-errormessage-range-underflow="<?php echo Text::sprintf('EB_NUMBER_REGISTRANTS_IN_VALID', $this->minNumberRegistrants); ?>"
				data-errormessage-range-overflow="<?php echo Text::sprintf('EB_MAX_REGISTRANTS_REACH', $this->maxRegistrants);?>"
				step="1" min="<?php echo $this->minNumberRegistrants ?>" max="<?php echo $this->maxRegistrants; ?>" />
		</div>
	</div>
	<div class="form-actions">
		<input type="button" name="btn-number-members-back" id="btn-number-members-back" class="<?php echo $btnPrimaryClass; ?>" value="<?php echo Text::_('EB_BACK'); ?>" onclick="window.history.go(-1) ;" />
		<input type="button" name="btn-process-number-members" id="btn-process-number-members" class="<?php echo $btnPrimaryClass; ?>" value="<?php echo Text::_('EB_NEXT'); ?>" />
	</div>
</form>
<script type="text/javascript">
	Eb.jQuery(document).ready(function($){
	    var $btnProcessNumberMembers = $('#btn-process-number-members'),
            $formNumberGroupMembers = $("#eb-form-number-group-members");
			$formNumberGroupMembers.validationEngine();
            $btnProcessNumberMembers.click(function(){
            var formValid = $formNumberGroupMembers.validationEngine('validate');
			var ajaxUrl = '';


            if (Joomla.getOptions('storeNumberMembersUrl'))
            {
                ajaxUrl = Joomla.getOptions('storeNumberMembersUrl');
            }
            else
            {
                ajaxUrl = siteUrl + 'index.php?option=com_eventbooking&view=register&task=register.store_number_registrants&event_id=<?php echo $this->event->id; ?>&Itemid=<?php echo $this->Itemid; ?>&format=raw' + langLinkForAjax;
            }

            if (formValid)
            {
                $.ajax({
                    url: ajaxUrl,
                    dataType: 'html',
	                method: 'post',
	                data: {number_registrants: $('input[name="number_registrants"]').val()},
                    beforeSend: function() {
                        $btnProcessNumberMembers.attr('disabled', true);
                        $btnProcessNumberMembers.after('<span class="wait">&nbsp;<img src="<?php echo Uri::base(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" alt="" /></span>');
                    },
                    complete: function() {
                        $btnProcessNumberMembers.attr('disabled', false);
                        $('.wait').remove();
                    },
                    success: function(html) {
                        var $numberMembersFormContainer = $('#eb-number-group-members .eb-form-content');
                        <?php
                            if ($this->collectMemberInformation)
                            {
                            ?>
                                var $groupMembersFormContainer = $('#eb-group-members-information .eb-form-content');
                                $groupMembersFormContainer.html(html);
                                $numberMembersFormContainer.slideUp('slow');
                                $groupMembersFormContainer.slideDown('slow');
                            <?php
                            }
                            else
                            {
                            ?>
                                var $groupBillingFormContainer = $('#eb-group-billing .eb-form-content'),
                                    $email = $('#email');
                                $groupBillingFormContainer.html(html);
                                $numberMembersFormContainer.slideUp('slow');
                                $groupBillingFormContainer.slideDown('slow');

                                if ($email.val())
                                {
                                    $email.validationEngine('validate');
                                }

                                $('#return_url').val(returnUrl);
                            <?php
                            }
                        ?>
                        $('#eb-form-group-members').find(".hasTooltip").tooltip({"html": true,"container": "body"});
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        });
	});
</script>
