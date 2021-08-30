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

class EventbookingViewRegistrantsHtml extends RADViewList
{
	use EventbookingViewRegistrants;

	/**
	 * Prepare the view before it is displayed
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$this->prepareViewData();

		$config = EventbookingHelper::getConfig();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__eb_payment_plugins')
			->where('published = 1');
		$db->setQuery($query);

		$this->totalPlugins     = $db->loadResult();
		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->dateFormat       = str_replace('%', '', $this->datePickerFormat);
		$this->message          = EventbookingHelper::getMessages();
	}

	/**
	 * Override addToolbar method to add custom csv export function
	 * @see RADViewList::addToolbar()
	 */
	protected function addToolbar()
	{
		parent::addToolbar();

		$config = EventbookingHelper::getConfig();

		ToolbarHelper::custom('cancel_registrations', 'cancel', 'cancel', 'EB_CANCEL_REGISTRATIONS', true);

		if ($config->activate_checkin_registrants)
		{
			ToolbarHelper::checkin('checkin_multiple_registrants');
			ToolbarHelper::unpublish('reset_check_in', Text::_('EB_CHECKOUT'), true);
		}

		// Instantiate a new JLayoutFile instance and render the batch button
		$layout = new JLayoutFile('joomla.toolbar.batch');

		$bar   = JToolbar::getInstance('toolbar');
		$dhtml = $layout->render(['title' => Text::_('EB_MASS_MAIL')]);
		$bar->appendButton('Custom', $dhtml, 'batch');

		ToolbarHelper::custom('resend_email', 'envelope', 'envelope', 'EB_RESEND_EMAIL', true);
		ToolbarHelper::custom('export', 'download', 'download', 'EB_EXPORT_REGISTRANTS', false);
		ToolbarHelper::custom('export_pdf', 'download', 'download', 'EB_EXPORT_PDF', false);

		if ($config->activate_invoice_feature)
		{
			ToolbarHelper::custom('export_invoices', 'download', 'download', 'EB_EXPORT_INVOICES', false);
		}

		if ($config->activate_certificate_feature)
		{
			ToolbarHelper::custom('download_certificates', 'download', 'download', 'EB_DOWNLOAD_CERTIFICATES', true);
			ToolbarHelper::custom('send_certificates', 'envelope', 'envelope', 'EB_SEND_CERTIFICATES', true);
		}

		if ($config->activate_waitinglist_feature)
		{
			ToolbarHelper::custom('request_payment', 'envelope', 'envelope', 'EB_REQUEST_PAYMENT', true);
		}
	}
}
