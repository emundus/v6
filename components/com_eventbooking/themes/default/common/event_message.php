<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper    = EventbookingHelperBootstrap::getInstance();

if (!$event->is_multiple_date
	&& !$event->can_register
	&& $event->registration_type != 3
	&& $config->display_message_for_full_event
	&& !$event->waiting_list && $event->registration_start_minutes >= 0)
{
	$viewLevels         = Factory::getUser()->getAuthorisedViewLevels();
	$loginLink          = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()), false);
	$loginToRegisterMsg = str_replace('[LOGIN_LINK]', $loginLink, Text::_('EB_LOGIN_TO_REGISTER'));

	$msg = '';

	if (isset($event->cannot_register_reason))
    {
        switch ($event->cannot_register_reason)
        {
            case 'require_dependency_events':
                $msg = Text::_('EB_NEED_TO_REGISTER_FOR_ALL_DEPENDENCY_EVENTS');
               break;
            case 'require_dependency_events_one':
	            $msg = Text::_('EB_NEED_TO_REGISTER_ONE_OF_THE_DEPENDENCY_EVENTS');
                break;
	        case 'duplicate_registration_waiting_list':
		        $msg = Text::_('EB_YOU_JOINED_WAITING_LIST_ALREADY');
	        	break;
            case 'event_is_full':
	            $msg = Text::_('EB_EVENT_IS_FULL');
                break;
            case 'duplicate_registration':
	            $msg = Text::_('EB_YOU_REGISTERED_ALREADY');
                break;
            case  'event_cancelled':
                $msg = Text::_('EB_EVENT_CANCELLED_ALREADY');
                break;
        }
    }

	if (!$msg)
    {
        // Legacy, in case cannot_register_reason is not set for the event
	    if (@$event->user_registered)
	    {
		    $msg = Text::_('EB_YOU_REGISTERED_ALREADY');
	    }
        elseif ($event->event_capacity && ($event->total_registrants >= $event->event_capacity))
	    {
		    $msg = Text::_('EB_EVENT_IS_FULL');
	    }
        elseif (!in_array($event->registration_access, $viewLevels))
	    {
		    if (Factory::getUser()->id)
		    {
			    $msg = Text::_('EB_REGISTRATION_NOT_AVAILABLE_FOR_ACCOUNT');
		    }
		    else
		    {
			    $msg = $loginToRegisterMsg;
		    }
	    }
	    else
	    {
		    $msg = Text::_('EB_NO_LONGER_ACCEPT_REGISTRATION');
	    }
    }
	?>
		<div class="<?php echo $bootstrapHelper->getClassMapping('clearfix'); ?>">
			<p class="text-info eb-notice-message"><?php echo $msg; ?></p>
		</div>
	<?php
}
elseif (!empty($event->cannot_register_reason) && $event->cannot_register_reason == 'duplicate_registration_waiting_list')
{
?>
	<div class="<?php echo $bootstrapHelper->getClassMapping('clearfix'); ?>">
		<p class="text-info eb-notice-message"><?php echo Text::_('EB_YOU_JOINED_WAITING_LIST_ALREADY'); ?></p>
	</div>
<?php
}