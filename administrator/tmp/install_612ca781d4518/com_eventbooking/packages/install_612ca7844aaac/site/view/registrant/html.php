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

class EventbookingViewRegistrantHtml extends RADViewHtml
{
	use EventbookingViewRegistrant;

	protected function prepareView()
	{
		parent::prepareView();

		$rootUri  = Uri::root(true);
		$document = Factory::getDocument();
		$user     = Factory::getUser();

		$item       = $this->model->getData();
		$this->item = $item;

		// Add scripts
		EventbookingHelper::addLangLinkForAjax();
		$document->addScriptDeclaration('var siteUrl="' . EventbookingHelper::getSiteUrl() . '";');

		EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/assets/js/paymentmethods.min.js', ['version' => EventbookingHelper::getInstalledVersion()]);

		$customJSFile = JPATH_ROOT . '/media/com_eventbooking/assets/js/custom.js';

		if (file_exists($customJSFile) && filesize($customJSFile) > 0)
		{
			$document->addScript($rootUri . '/media/com_eventbooking/assets/js/custom.js');
		}

		$disableEdit = false;

		if ($item->id
			&& $item->registrant_edit_close_date != Factory::getDbo()->getNullDate()
			&& $item->edit_close_minutes > 0 &&
			!$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			// Not allowed to edit the record after edit closing data reached
			$disableEdit = true;

			$this->item->disableEdit = true;

			// Hide the save button
			$this->hideButtons[] = 'registrant.save';
		}

		$this->prepareViewData();

		if ($this->event->has_multiple_ticket_types)
		{
			$this->canChangeTicketsQuantity = $this->userType == 'registrants_manager';
		}

		$this->canChangeStatus = $this->userType == 'registrants_manager';

		if ($disableEdit)
		{
			$this->canChangeStatus          = false;
			$this->canChangeFeeFields       = false;
			$this->canChangeTicketsQuantity = false;
		}

		$this->return      = $this->input->get->getBase64('return');
		$this->disableEdit = $disableEdit;

		$this->addToolbar();

		$this->setLayout('default');
	}

	/**
	 * Build Form Toolbar
	 */
	protected function addToolbar()
	{
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR . '/includes/toolbar.php');

		if (!in_array('registrant.save', $this->hideButtons))
		{
			ToolbarHelper::save('registrant.save', 'JTOOLBAR_SAVE');
		}

		if ($this->item->id
			&& $this->item->published != 2
			&& !in_array('registrant.cancel', $this->hideButtons)
			&& EventbookingHelperAcl::canCancelRegistration($this->item->event_id)
		)
		{
			ToolbarHelper::custom('registrant.cancel', 'delete', 'delete', Text::_('EB_CANCEL_REGISTRATION'), false);
		}

		if (!in_array('registrant.cancel_edit', $this->hideButtons))
		{
			ToolbarHelper::cancel('registrant.cancel_edit', 'JTOOLBAR_CLOSE');
		}

		if (EventbookingHelperRegistration::canRefundRegistrant($this->item)
			&& !in_array('registrant.refund', $this->hideButtons))
		{
			ToolbarHelper::custom('registrant.refund', 'delete', 'delete', Text::_('EB_REFUND'), false);
		}
	}
}
