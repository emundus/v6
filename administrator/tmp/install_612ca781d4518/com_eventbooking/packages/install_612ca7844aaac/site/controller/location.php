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
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

class EventbookingControllerLocation extends EventbookingController
{
	/**
	 * save location
	 */
	public function save()
	{
		$this->csrfProtection();

		if (Factory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			$post  = $this->input->post->getData();
			$model = $this->getModel();

			try
			{
				/* @var EventbookingModelLocation $model */
				$model->store($post);
				$msg = Text::_('EB_LOCATION_SAVED');
			}
			catch (Exception $e)
			{
				$msg = Text::_('EB_ERROR_SAVING_LOCATION') . ':' . $e->getMessage();
			}

			$this->setRedirect(Route::_(EventbookingHelperRoute::getViewRoute('locations', $this->input->getInt('Itemid', 0)), false), $msg);
		}
	}

	/**
	 * Save location from ajax request
	 */
	public function save_ajax()
	{
		$this->csrfProtection();

		if (Factory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			$post  = $this->input->post->getData();
			$model = $this->getModel();

			$json = [];

			try
			{
				/* @var EventbookingModelLocation $model */
				$model->store($post);
				$json['success'] = true;
				$json['id']      = $post['id'];
				$json['name']    = $post['name'];
			}
			catch (Exception $e)
			{
				$json['success'] = false;
				$json['message'] = $e->getMessage();
			}

			echo json_encode($json);

			$this->app->close();
		}
	}

	/**
	 * Delete location
	 */
	public function delete()
	{
		$this->csrfProtection();

		// Check permission
		if (!Factory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			$this->app->enqueueMessage(Text::_('EB_NO_PERMISSION'), 'error');
			$this->app->redirect(Uri::root(), 403);

			return;
		}

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);

		/* @var EventbookingModelLocation $model */
		$model = $this->getModel();
		$model->delete($cid);

		$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=locations&Itemid=' . $this->input->getInt('Itemid', 0)), Text::_('EB_LOCATION_REMOVED'));
	}

	/**
	 * Cancel location edit, redirect to location list page
	 */
	public function cancel()
	{
		$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=locations&Itemid=' . $this->input->getInt('Itemid', 0)));
	}

	/**
	 * Search for a given address using OpenStreetMap API
	 */
	public function search()
	{
		$address = $this->input->getString('query');

		/* @var EventbookingModelLocations $model */
		$model = $this->getModel('Locations', ['ignore_request' => true]);

		$response['suggestions'] = $model->searchInOpenStreetMap($address);

		echo json_encode($response);

		$this->app->close();
	}
}
