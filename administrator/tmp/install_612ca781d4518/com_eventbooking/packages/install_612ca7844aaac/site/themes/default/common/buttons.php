<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Layout variables
 * -----------------
 * @var   EventbookingTableEvent $item
 * @var   RADConfig              $config
 * @var   boolean                $showInviteFriend
 * @var   boolean                $canRegister
 * @var   boolean                $registrationOpen
 * @var   int                    $ssl
 * @var   int                    $Itemid
 * @var   string                 $return
 * @var   string                 $btnClass
 * @var   string                 $iconOkClass
 * @var   string                 $iconRemoveClass
 * @var   string                 $iconDownloadClass
 * @var   string                 $iconPencilClass
 */

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$btnBtnPrimary   = $bootstrapHelper->getClassMapping('btn btn-primary');

if (empty($hideRegisterButtons) && !$isMultipleDate)
{
	if ($canRegister)
	{
		$registrationUrl = trim($item->registration_handle_url);

		if ($registrationUrl)
		{
		?>
			<li>
				<a class="<?php echo $btnBtnPrimary.' eb-register-button eb-external-registration-link'; ?>" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo Text::_('EB_REGISTER');; ?></a>
			</li>
		<?php
		}
		else
		{
			if (in_array($item->registration_type, [0, 1]))
			{
			    $cssClasses = [$btnBtnPrimary, 'eb-register-button'];

			    if ($config->multiple_booking && !$item->has_multiple_ticket_types)
				{
					$url = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $item->id . '&Itemid=' . (int) $Itemid . '&pt=' . time();

					if (!$item->event_password)
					{
						$cssClasses[] = 'eb-colorbox-addcart';
					}

					$text = Text::_('EB_REGISTER');
				}
				else
				{
					$cssClasses[] = 'eb-individual-registration-button';

					$url = Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl);

					if ($item->has_multiple_ticket_types)
					{
						$text = Text::_('EB_REGISTER');
					}
					else
					{
						$text = Text::_('EB_REGISTER_INDIVIDUAL');
					}
				}
				?>
				<li>
					<a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo $url; ?>"><?php echo $text; ?></a>
				</li>
				<?php
			}

			if ($item->min_group_number > 0)
			{
				$minGroupNumber = $item->min_group_number;
			}
			else
			{
				$minGroupNumber = 2;
			}

			if ($item->event_capacity > 0 && (($item->event_capacity - $item->total_registrants) < $minGroupNumber))
			{
				$groupRegistrationAvailable = false;
			}
			else
			{
				$groupRegistrationAvailable = true;
			}

			if ($groupRegistrationAvailable && in_array($item->registration_type, [0, 2]) && !$config->multiple_booking && !$item->has_multiple_ticket_types)
			{
				$cssClasses = [$btnBtnPrimary, 'eb-register-button', 'eb-group-registration-button'];
			?>
				<li>
					<a class="<?php echo implode(' ', $cssClasses); ?>"
					   href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.group_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo Text::_('EB_REGISTER_GROUP');; ?></a>
				</li>
			<?php
			}
		}
	}
	elseif ($waitingList && $item->registration_type != 3 && !EventbookingHelperRegistration::isUserJoinedWaitingList($item->id))
	{
        if ($item->waiting_list_capacity == 0)
        {
            $numberWaitingListAvailable =  1000; // Fake number
        }
        else
        {
            $numberWaitingListAvailable = max($item->waiting_list_capacity - EventbookingHelperRegistration::countNumberWaitingList($item), 0);
        }

        if (in_array($item->registration_type, [0, 1]) && $numberWaitingListAvailable)
        {
	        $cssClasses = [$btnBtnPrimary, 'eb-register-button', 'eb-join-waiting-list-individual-button'];
	    ?>
            <li>
                <a class="<?php echo implode(' ', $cssClasses); ?>"
                   href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo Text::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST');; ?></a>
            </li>
	    <?php
        }

        if (in_array($item->registration_type, [0, 2]) && $numberWaitingListAvailable > 1 && !$config->multiple_booking)
        {
	        $cssClasses = [$btnBtnPrimary, 'eb-register-button', 'eb-join-waiting-list-group-button'];
	    ?>
            <li>
                <a class="<?php echo implode(' ', $cssClasses); ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=register.group_registration&event_id=' . $item->id . '&Itemid=' . $Itemid, false, $ssl); ?>"><?php echo Text::_('EB_REGISTER_GROUP_WAITING_LIST');; ?></a>
            </li>
	    <?php
        }
	}
}

if ($config->show_save_to_personal_calendar && $item->event_date != EB_TBC_DATE)
{
?>
	<li class="eb-save-to-calendar-buttons">
		<?php echo EventbookingHelperHtml::loadCommonLayout('common/save_calendar.php', array('item' => $item, 'Itemid' => $Itemid)); ?>
	</li>
<?php
}

if ($showInviteFriend && $config->show_invite_friend && EventbookingHelperRegistration::canInviteFriend($item))
{
?>
	<li>
		<a class="<?php echo $btnClass; ?> eb-colorbox-invite" href="<?php echo Route::_('index.php?option=com_eventbooking&view=invite&tmpl=component&id=' . $item->id . '&Itemid=' . $Itemid, false); ?>"><?php echo Text::_('EB_INVITE_FRIEND'); ?></a>
	</li>
<?php
}


$user = Factory::getUser();

if ($user->get('guest'))
{
    return;
}

if ($item->enable_cancel_registration)
{
	$registrantId = EventbookingHelperRegistration::getRegistrantId($item->id);

	if ($registrantId !== false && EventbookingHelperRegistration::canCancelRegistrationNow($item))
	{
		if (EventbookingHelperRegistration::isUserJoinedWaitingList($item->id))
		{
			$buttonLabel = 'EB_CANCEL_WAITING_LIST';
		}
		else
		{
			$buttonLabel = 'EB_CANCEL_REGISTRATION';
		}
	?>
        <li>
            <a class="<?php echo $btnClass; ?>"
               href="javascript:cancelRegistration(<?php echo $registrantId; ?>)"><?php echo Text::_($buttonLabel); ?></a>
        </li>
    <?php
	}
}

if (!$config->get('show_actions_button', '1'))
{
    return;
}

if (EventbookingHelperAcl::canEditEvent($item))
{
?>
	<li>
		<a class="<?php echo $btnClass; ?>"
		   href="<?php echo Route::_('index.php?option=com_eventbooking&view=event&layout=form&id=' . $item->id . '&Itemid=' . $Itemid . '&return=' . $return, false); ?>">
			<i class="<?php echo $iconPencilClass; ?>"></i>
			<?php echo Text::_('EB_EDIT'); ?>
		</a>
	</li>
<?php
}

if (EventbookingHelperAcl::canPublishUnpublishEvent($item))
{
	if ($item->published == 1)
	{
		$link  = Route::_('index.php?option=com_eventbooking&task=event.unpublish&id=' . $item->id . '&Itemid=' . $Itemid . '&return=' . $return, false);
		$text  = Text::_('EB_UNPUBLISH');
		$class = $iconRemoveClass;
	}
	else
	{
		$link  = Route::_('index.php?option=com_eventbooking&task=event.publish&id=' . $item->id . '&Itemid=' . $Itemid . '&return=' . $return, false);
		$text  = Text::_('EB_PUBLISH');
		$class = $iconOkClass;
	}
	?>
	<li>
		<a class="<?php echo $btnClass; ?>" href="<?php echo $link; ?>">
			<i class="<?php echo $class; ?>"></i>
			<?php echo $text; ?>
		</a>
	</li>
	<?php
}

if ($item->total_registrants && EventbookingHelperAcl::canExportEventRegistrant($item))
{
?>
	<li>
		<a class="<?php echo $btnClass; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.export&event_id=' . $item->id . '&Itemid=' . $Itemid); ?>">
			<i class="<?php echo $iconDownloadClass; ?>"></i>
			<?php echo Text::_('EB_EXPORT_REGISTRANTS'); ?>
		</a>
	</li>
<?php
}