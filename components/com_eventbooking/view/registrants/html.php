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
use Joomla\CMS\Uri\Uri;

class EventbookingViewRegistrantsHtml extends RADViewList
{
	use EventbookingViewRegistrants;

	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->params = $this->getParams(['registrants']);

		$this->hideButtons = $this->params->get('hide_buttons', []);
	}

	/**
	 * Prepare view data for displaying
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$user = Factory::getUser();

		if (!$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$app = Factory::getApplication();

			if ($user->get('guest'))
			{
				$this->requestLogin();
			}
			else
			{
				$app->enqueueMessage(Text::_('NOT_AUTHORIZED'), 'error');
				$app->redirect(Uri::root(), 403);
			}
		}

		$this->prepareViewData();
		$this->coreFields = EventbookingHelperRegistration::getPublishedCoreFields();

		$this->findAndSetActiveMenuItem();

		$this->addToolbar();
	}

	/**
	 * Override addToolbar method to add custom csv export function
	 * @see RADViewList::addToolbar()
	 */
	protected function addToolbar()
	{
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR . '/includes/toolbar.php');

		if (!EventbookingHelperAcl::canDeleteRegistrant())
		{
			$this->hideButtons[] = 'delete';
		}

		parent::addToolbar();

		$config = EventbookingHelper::getConfig();

		if (!in_array('cancel_registrations', $this->hideButtons))
		{
			ToolbarHelper::custom('cancel_registrations', 'cancel', 'cancel', 'EB_CANCEL_REGISTRATIONS', true);
		}

		if ($config->activate_checkin_registrants)
		{
			if (!in_array('checkin_multiple_registrants', $this->hideButtons))
			{
				ToolbarHelper::checkin('checkin_multiple_registrants');
			}

			if (!in_array('check_out', $this->hideButtons))
			{
				ToolbarHelper::unpublish('check_out', Text::_('EB_CHECKOUT'), true);
			}
		}

		if (!in_array('batch_mail', $this->hideButtons))
		{
			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$bar   = JToolbar::getInstance('toolbar');
			$dhtml = $layout->render(['title' => Text::_('EB_MASS_MAIL')]);
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if (!in_array('resend_email', $this->hideButtons))
		{
			ToolbarHelper::custom('resend_email', 'envelope', 'envelope', 'EB_RESEND_EMAIL', true);
		}

		if (!in_array('export', $this->hideButtons))
		{
			ToolbarHelper::custom('export', 'download', 'download', 'EB_EXPORT_REGISTRANTS', false);
		}

		if (!in_array('export_pdf', $this->hideButtons))
		{
			ToolbarHelper::custom('export_pdf', 'download', 'download', 'EB_EXPORT_PDF', false);
		}

		if ($config->activate_certificate_feature)
		{
			if (!in_array('download_certificates', $this->hideButtons))
			{
				ToolbarHelper::custom('download_certificates', 'download', 'download', 'EB_DOWNLOAD_CERTIFICATES', true);
			}

			if (!in_array('send_certificates', $this->hideButtons))
			{
				ToolbarHelper::custom('send_certificates', 'envelope', 'envelope', 'EB_SEND_CERTIFICATES', true);
			}
		}

		if ($config->activate_waitinglist_feature && !in_array('request_payment', $this->hideButtons))
		{
			ToolbarHelper::custom('request_payment', 'envelope', 'envelope', 'EB_REQUEST_PAYMENT', true);
		}
	}
}
