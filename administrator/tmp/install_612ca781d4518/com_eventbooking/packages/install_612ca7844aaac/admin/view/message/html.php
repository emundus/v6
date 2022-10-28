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
use Joomla\CMS\Toolbar\ToolbarHelper;

class EventbookingViewMessageHtml extends RADViewHtml
{
	public function display()
	{
		$languages = EventbookingHelper::getLanguages();
		$message   = EventbookingHelper::getMessages();

		if (count($languages))
		{
			$translatableKeys = [
				'intro_text',
				'admin_email_subject',
				'admin_email_body',
				'user_email_subject',
				'user_email_body',
				'user_email_body_offline',
				'group_member_email_subject',
				'group_member_email_body',
				'registration_form_message',
				'registration_form_message_group',
				'number_members_form_message',
				'member_information_form_message',
				'thanks_message',
				'thanks_message_offline',
				'cancel_message',
				'registration_cancel_message_free',
				'registration_cancel_message_paid',
				'invitation_form_message',
				'invitation_email_subject',
				'invitation_email_body',
				'invitation_complete',
				'reminder_email_subject',
				'reminder_email_body',
				'registration_cancel_email_subject',
				'registration_cancel_email_body',
				'registration_approved_email_subject',
				'registration_approved_email_body',
				'waitinglist_form_message',
				'waitinglist_complete_message',
				'watinglist_confirmation_subject',
				'watinglist_confirmation_body',
				'watinglist_notification_subject',
				'watinglist_notification_body',
				'registrant_waitinglist_notification_subject',
				'registrant_waitinglist_notification_body',
			];

			foreach ($languages as $language)
			{
				$sef = $language->sef;
				foreach ($translatableKeys as $key)
				{
					if (empty($message->{$key . '_' . $sef}))
					{
						$message->{$key . '_' . $sef} = $message->{$key};
					}
				}
			}
		}

		// Extra offline payment plugin messages
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_payment_plugins')
			->where('name LIKE "os_offline_%"')
			->where('published = 1');
		$db->setQuery($query);

		$this->extraOfflinePlugins = $db->loadObjectList();
		$this->languages           = $languages;
		$this->message             = $message;

		$this->addToolbar();

		parent::display();
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('Emails & Messages'), 'generic.png');
		ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('save');
		ToolbarHelper::cancel('cancel');
	}
}
