<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * EventBooking Theme controller
 *
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingControllerTheme extends EventbookingController
{
	/**
	 * Install a payment plugin
	 */
	public function install()
	{
		$themePackage = $this->input->files->get('theme_package', null, 'raw');

		/* @var EventbookingModelTheme $model */
		$model = $this->getModel();

		try
		{
			$model->install($themePackage);
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=themes', false), Text::_('EB_THEME_INSTALLED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=themes', false), Text::_('EB_THEME_INSTALL_FAILED'));
		}
	}

	/**
	 * Uninstall a payment plugin
	 */
	public function uninstall()
	{
		/* @var EventbookingModelTheme $model */
		$model = $this->getModel();
		$cid   = $this->input->get('cid', [], 'array');

		try
		{
			$model->uninstall($cid[0]);
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=themes', false), Text::_('EB_THEME_UNINSTALLED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=themes', false), Text::_('EB_THEME_UNINSTALL_FAILED'));
		}
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return void
	 */
	public function publish()
	{
		// Check for request forgeries
		$this->csrfProtection();

		/* @var EventbookingModelTheme $model */
		$model = $this->getModel();
		$cid   = $this->input->get('cid', [], 'array');
		$cid   = ArrayHelper::toInteger($cid);

		try
		{
			$model->setDefaultTheme($cid[0]);
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=themes', false), Text::_('EB_DEFAULT_THEME_CHANGED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=themes', false), Text::_('EB_ERROR_CHANGING_DEFAULT_THEME'));
		}
	}
}
