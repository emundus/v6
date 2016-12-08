<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\Mixin;

use Akeeba\AdminTools\Admin\Model\ControlPanel;

defined('_JEXEC') or die;

/**
 * This trait defines the necessary properties for using the view template
 * admin:com_admintools/WebApplicationFirewall/plugin_warning
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
	public $pluginExists;

	/**
	 * Is the system plugin enabled?
	 *
	 * @var  bool
	 */
	public $pluginActive;

	protected function populateSystemPluginExists()
	{
		/** @var ControlPanel $cPanelModel */
		$cPanelModel = $this->container->factory->model('ControlPanel')->tmpInstance();

		$this->pluginExists = @file_exists(JPATH_ROOT . '/plugins/system/admintools/admintools.php');
		$this->pluginActive = ((int) $cPanelModel->getPluginID()) != 0;
	}

}