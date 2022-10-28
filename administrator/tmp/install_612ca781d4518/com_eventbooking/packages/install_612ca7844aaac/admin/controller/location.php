<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingControllerLocation extends EventbookingController
{
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

	/**
	 * Save location from ajax request
	 */
	public function save_ajax()
	{

		$post  = $this->input->post->getData();
		$model = $this->getModel();

		$json = [];

		try
		{
			/* @var EventbookingModelLocation $model */
			$model->storeLocation($post);
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
