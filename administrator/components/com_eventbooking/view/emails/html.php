<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2017 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EventbookingViewEmailsHtml extends RADViewList
{
	/**
	 * Method to instantiate the view.
	 *
	 * @param   array  $config  The configuration data for the view
	 *
	 * @since  1.0
	 */
	public function __construct($config = [])
	{
		$config['hide_buttons'] = ['add', 'edit', 'publish'];

		parent::__construct($config);
	}

	/**
	 * Build necessary data for the view before it is being displayed
	 *
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$this->emailTypes = [
			'new_registration_emails'          => Text::_('EB_NEW_REGISTRATION_EMAILS'),
			'reminder_emails'                  => Text::_('EB_REMINDER_EMAILS'),
			'mass_mails'                       => Text::_('EB_MASS_MAIL'),
			'registration_approved_emails'     => Text::_('EB_REGISTRATION_APPROVED_EMAILS'),
			'registration_cancel_emails'       => Text::_('EB_REGISTRATION_CANCEL_EMAILS'),
			'new_event_notification_emails'    => Text::_('EB_NEW_EVENT_NOTIFICATION_EMAILS'),
			'deposit_payment_reminder_emails'  => Text::_('EB_DEPOSIT_PAYMENT_REMINDER_EMAILS'),
			'waiting_list_emails'              => Text::_('EB_WAITING_LIST_EMAILS'),
			'event_approved_emails'            => Text::_('EB_EVENT_APPROVED_EMAILS'),
			'event_update_emails'              => Text::_('EB_EVENT_UPDATE_EMAILS'),
			'waiting_list_notification_emails' => Text::_('EB_WAITING_LIST_NOTIFICATION_EMAILS'),
			'request_payment_emails'           => Text::_('EB_REQUEST_PAYMENT_EMAILS'),
			'event_cancel_emails'              => Text::_('EB_EVENT_CANCEL_EMAILS'),
		];

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_EMAIL_TYPE'));

		foreach ($this->emailTypes as $key => $value)
		{
			$options[] = HTMLHelper::_('select.option', $key, $value);
		}

		$this->lists['filter_email_type'] = HTMLHelper::_('select.genericlist', $options, 'filter_email_type', ' onchange="submit();" ', 'value', 'text', $this->state->filter_email_type);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_SENT_TO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_ADMIN'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_REGISTRANTS'));

		$this->lists['filter_sent_to'] = HTMLHelper::_('select.genericlist', $options, 'filter_sent_to', ' onchange="submit();" ', 'value', 'text', $this->state->filter_sent_to);
	}

	/**
	 * Method to add toolbar buttons
	 */
	protected function addToolbar()
	{
		parent::addToolbar();

		ToolbarHelper::trash('delete_all', 'EB_DELETE_ALL', false);
	}
}
