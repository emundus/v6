<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('calendar', '', 'id', 'name');

$count           = 0;
$config          = EventbookingHelper::getConfig();
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

foreach ($ticketTypes as $item)
{
    if (empty($item->quantity))
    {
        continue;
    }

	$eventHeading = Text::sprintf('EB_TICKET_MEMBERS_INFORMATION', Text::_($item->title));
	?>
    <h3 class="eb-heading"><?php echo $eventHeading; ?></h3>
	<?php

    if (isset($formData['use_field_default_value']))
    {
        $useDefault = $formData['use_field_default_value'];
    }
    else
    {
        $useDefault = true;
    }

	$rowFields = EventbookingHelperRegistration::getFormFields($eventId, 2);

    for ($i = 0; $i < $item->quantity; $i++)
	{
		$count++;
		$currentMemberFields = EventbookingHelperRegistration::getGroupMemberFields($rowFields, $i + 1);
		$form      = new RADForm($currentMemberFields);
		$form->setFieldSuffix($count);

		if (!isset($formData['country_' . $count]))
		{
			$formData['country_' . $count] = $config->default_country;
		}

        $form->bind($formData, $useDefault);
		$form->prepareFormFields('calculateIndividualRegistrationFee();');
		$form->buildFieldsDependency();
		$fields = $form->getFields();

		//We don't need to use ajax validation for email field for group members
		if (isset($fields['email']))
		{
			$emailField = $fields['email'];
			$cssClass   = $emailField->getAttribute('class');
			$cssClass   = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
			$emailField->setAttribute('class', $cssClass);
		}
		?>
        <h4 class="eb-heading"><?php echo Text::sprintf('EB_MEMBER_INFORMATION', $i + 1); ?></h4>
		<?php

		/* @var RADFormField $field */
		foreach ($fields as $field)
		{
			echo $field->getControlGroup($bootstrapHelper);
		}
	}
}
?>
<script type="text/javascript">
    Eb.jQuery(document).ready(function($){
        <?php
            for ($i = 1; $i <= $count; $i++)
            {
            ?>
                buildStateFields('state_<?php echo $i; ?>', 'country_<?php echo $i; ?>', '');
            <?php
            }
        ?>

        calendarElements = document.querySelectorAll("#tickets_members_information .field-calendar");
        for (i = 0; i < calendarElements.length; i++) {
            JoomlaCalendar.init(calendarElements[i]);
        }
    });
</script>
