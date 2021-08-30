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

class EventbookingControllerPlugin extends EventbookingController
{
	/**
	 * Install a payment plugin
	 */
	public function install()
	{
		$plugin = $this->input->files->get('plugin_package', null, 'raw');
		$model  = $this->getModel();

		try
		{
			$model->install($plugin);
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=plugins', false), Text::_('EB_PLUGIN_INSTALLED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=plugins', false), Text::_('EB_PLUGIN_INSTALL_FAILED'));
		}
	}

	/**
	 * Uninstall a payment plugin
	 */
	public function uninstall()
	{
		$model = $this->getModel();
		$cid   = $this->input->get('cid', [], 'array');
		$model->uninstall($cid[0]);
		$this->setRedirect(Route::_('index.php?option=com_eventbooking&view=plugins', false), Text::_('EB_PLUGIN_UNINSTALLED'));
	}
}
