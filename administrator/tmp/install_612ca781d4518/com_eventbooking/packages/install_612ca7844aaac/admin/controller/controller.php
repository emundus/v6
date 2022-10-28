<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class EventbookingController extends RADControllerAdmin
{
	use RADControllerDownload;

	public function display($cachable = false, array $urlparams = [])
	{
		$document = Factory::getDocument();
		$baseUri  = Uri::base(true);

		$document->addStyleSheet($baseUri . '/components/com_eventbooking/assets/css/style.css');

		$customCssFile = JPATH_ADMINISTRATOR . '/components/com_eventbooking/assets/css/custom.css';

		if (file_exists($customCssFile) && filesize($customCssFile) > 0)
		{
			$document->addStyleSheet($baseUri . '/components/com_eventbooking/assets/css/custom.css');
		}

		parent::display($cachable, $urlparams);

		if ($this->input->getCmd('format', 'html') != 'raw')
		{
			EventbookingHelper::displayCopyRight();
		}
	}

	/**
	 * This method is implemented to help calling by typing the url on web browser to update database schema to latest version
	 */
	public function upgrade()
	{
		$this->setRedirect('index.php?option=com_eventbooking&task=update.update');
	}

	/**
	 * Check to see the installed version is up to date or not
	 *
	 * @return int 0 : error, 1 : Up to date, 2 : outof date
	 */
	public function check_update()
	{
		// Get the caching duration.
		$component     = ComponentHelper::getComponent('com_installer');
		$params        = $component->params;
		$cache_timeout = $params->get('cachetimeout', 6, 'int');
		$cache_timeout = 3600 * $cache_timeout;

		// Get the minimum stability.
		$minimum_stability = (int) $params->get('minimum_stability', JUpdater::STABILITY_STABLE);

		if (EventbookingHelper::isJoomla4())
		{
			/* @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
			$model = $this->app->bootComponent('com_joomlaupdate')->getMVCFactory()
				->createModel('Update', 'Administrator', ['ignore_request' => true]);
		}
		else
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models');

			/** @var InstallerModelUpdate $model */
			$model = JModelLegacy::getInstance('Update', 'InstallerModel');
		}

		$model->purge();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where('`type` = "package"')
			->where('`element` = "pkg_eventbooking"');
		$db->setQuery($query);
		$eid = (int) $db->loadResult();

		$result['status'] = 0;

		if ($eid)
		{
			$ret = JUpdater::getInstance()->findUpdates($eid, $cache_timeout, $minimum_stability);

			if ($ret)
			{
				$model->setState('list.start', 0);
				$model->setState('list.limit', 0);
				$model->setState('filter.extension_id', $eid);
				$updates          = $model->getItems();
				$result['status'] = 2;

				if (count($updates))
				{
					$result['message'] = Text::sprintf('EB_UPDATE_CHECKING_UPDATE_FOUND', $updates[0]->version);
				}
				else
				{
					$result['message'] = Text::sprintf('EB_UPDATE_CHECKING_UPDATE_FOUND', null);
				}
			}
			else
			{
				$result['status']  = 1;
				$result['message'] = Text::_('EB_UPDATE_CHECKING_UPTODATE');
			}
		}

		echo json_encode($result);
		$this->app->close();
	}

	/**
	 * Process download a file
	 */
	public function download_file()
	{
		$fileName = basename($this->input->getString('file_name'));
		$filePath = JPATH_ROOT . '/media/com_eventbooking/files' . '/' . $fileName;

		if (file_exists($filePath))
		{
			$this->processDownloadFile($filePath);
		}
		else
		{
			$app = $this->getApplication();
			$app->enqueueMessage(Text::_('File does not exist'), 'error');
			$app->redirect('index.php?option=com_eventbooking&view=dashboard');
		}
	}

	/**
	 * Get profile data of the registrant, return reson format using for ajax request
	 */
	public function get_profile_data()
	{
		$input   = Factory::getApplication()->input;
		$userId  = $input->getInt('user_id', 0);
		$eventId = $input->getInt('event_id');
		$data    = [];

		if ($userId && $eventId)
		{
			$rowFields = EventbookingHelperRegistration::getFormFields($eventId, 0);
			$data      = EventbookingHelperRegistration::getFormData($rowFields, $eventId, $userId);
		}

		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$user = Factory::getUser($userId);
			$name = $user->name;

			if ($name)
			{
				$pos = strpos($name, ' ');

				if ($pos !== false)
				{
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name']  = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name']  = '';
				}
			}
		}

		if ($userId && !isset($data['email']))
		{
			if (empty($user))
			{
				$user = Factory::getUser($userId);
			}

			$data['email'] = $user->email;
		}

		echo json_encode($data);

		$this->app->close();
	}
}
