<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\Mixin;

use Akeeba\AdminTools\Admin\Model\ControlPanel;
use AtsystemUtilRescueurl;

defined('_JEXEC') || die;

/**
 * This trait defines the necessary properties for using the view template
 * admin:com_admintools/ControlPanel/plugin_warning
 *
 * Include this trait and in your onBefore<TaskName> method call
 * $this->populateSystemPluginExists();
 */
trait SystemPluginExists
{
	/**
	 * Does the system plugin exist?
	 *
	 * @var  bool
	 */
	public $pluginExists = false;

	/**
	 * Is the system plugin enabled?
	 *
	 * @var  bool
	 */
	public $pluginActive = false;

	/**
	 * Is the plugin currently loaded?
	 *
	 * @var  bool
	 */
	public $pluginLoaded = false;

	/**
	 * Is main.php renamed to something else?
	 *
	 * @var  bool
	 */
	public $isMainPhpDisabled = false;

	/**
	 * What is the plugin's main.php file currently renamed to?
	 *
	 * @var  string
	 */
	public $mainPhpRenamedTo = false;

	/**
	 * Is Rescue Mode activated?
	 *
	 * @var  bool
	 */
	public $isRescueMode = false;

	protected function populateSystemPluginExists()
	{
		/** @var ControlPanel $cPanelModel */
		$cPanelModel = $this->container->factory->model('ControlPanel')->tmpInstance();

		// Does the plugin exist in the filesystem?
		$this->pluginExists = @file_exists(JPATH_ROOT . '/plugins/system/admintools/admintools.php');

		if (!$this->pluginExists)
		{
			return;
		}

		// Is the plugin enabled in the database?
		$this->pluginActive = (int) $cPanelModel->getPluginID() != 0;

		if (!$this->pluginActive)
		{
			return;
		}

		// Is Rescue Mode enabled?
		$this->isRescueMode = class_exists('AtsystemUtilRescueurl', true) ? AtsystemUtilRescueurl::isRescueMode() : false;

		if ($this->isRescueMode)
		{
			return;
		}

		// Is the plugin currently loaded
		$this->pluginLoaded = $cPanelModel->isPluginLoaded();

		if ($this->pluginLoaded)
		{
			return;
		}

		// Is main.php renamed?
		$this->isMainPhpDisabled = $cPanelModel->isMainPhpDisabled();

		if (!$this->isMainPhpDisabled)
		{
			return;
		}

		// What is main.php renamed to?
		$this->mainPhpRenamedTo = $cPanelModel->getRenamedMainPhp();
	}

}
