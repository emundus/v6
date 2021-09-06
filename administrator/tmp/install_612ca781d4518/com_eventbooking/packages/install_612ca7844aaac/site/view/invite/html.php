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

class EventbookingViewInviteHtml extends RADViewHtml
{
	use EventbookingViewCaptcha;

	/**
	 * Display invitation form for an event
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$config = EventbookingHelper::getConfig();

		if (!$config->show_invite_friend)
		{
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		$layout = $this->getLayout();

		if ($layout == 'complete')
		{
			$this->displayInviteComplete();
		}
		else
		{
			$this->setLayout('default');

			$user        = Factory::getUser();
			$message     = EventbookingHelper::getMessages();
			$fieldSuffix = EventbookingHelper::getFieldSuffix();

			if (strlen(trim(strip_tags($message->{'invitation_form_message' . $fieldSuffix}))))
			{
				$inviteMessage = $message->{'invitation_form_message' . $fieldSuffix};
			}
			else
			{
				$inviteMessage = $message->invitation_form_message;
			}

			// Load captcha
			$this->loadCaptcha();

			$eventId = $this->input->getInt('id');
			$name    = $this->input->getString('name');

			if (empty($name))
			{
				$name = $user->get('name');
			}

			$this->event           = EventbookingHelperDatabase::getEvent($eventId);
			$this->name            = $name;
			$this->inviteMessage   = $inviteMessage;
			$this->friendNames     = $this->input->getString('friend_names');
			$this->friendEmails    = $this->input->getString('friend_emails');
			$this->mesage          = $this->input->getString('message');
			$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();

			EventbookingHelper::callOverridableHelperMethod('Html', 'antiXSS', [$this->event, ['title']]);

			parent::display();
		}
	}

	/**
	 * Display invitation complete message
	 */
	protected function displayInviteComplete()
	{
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if (strlen(trim(strip_tags($message->{'invitation_complete' . $fieldSuffix}))))
		{
			$this->message = $message->{'invitation_complete' . $fieldSuffix};
		}
		else
		{
			$this->message = $message->invitation_complete;
		}

		parent::display();
	}
}
