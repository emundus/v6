<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF40\Container\Container;
use FOF40\Controller\DataController;
use Joomla\CMS\Language\Text;

class ConfigureFixPermissions extends DataController
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'savedefaults', 'saveperms', 'saveapplyperms'];
	}

	public function savedefaults()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureFixPermissions $model */
		$model = $this->getModel();
		$model->setState('dirperms', $this->input->getCmd('dirperms', '0755'));
		$model->setState('fileperms', $this->input->getCmd('fileperms', '0644'));
		$model->setState('perms_show_hidden', $this->input->getInt('perms_show_hidden', 0));
		$model->saveDefaults();

		$message = Text::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFAULTSSAVED');
		$this->setRedirect('index.php?option=com_admintools&view=ConfigureFixPermissions', $message);
	}

	public function onBeforeBrowse()
	{
		$path = $this->input->get('path', '', 'raw', 2);

		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureFixPermissions $model */
		$model = $this->getModel();
		$model->setState('path', $path);
		$model->applyPath();
	}

	/**
	 * Saves the custom permissions and reloads the current view
	 */
	public function saveperms()
	{
		// CSRF prevention
		$this->csrfProtection();

		$this->save_custom_permissions();

		$message = Text::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_CUSTOMSAVED');
		$path    = $this->input->get('path', '', 'raw', 2);
		$this->setRedirect('index.php?option=com_admintools&view=ConfigureFixPermissions&path=' . urlencode($path), $message);
	}

	/**
	 * Saves the custom permissions, applies them and reloads the current view
	 */
	public function saveapplyperms()
	{
		// CSRF prevention
		$this->csrfProtection();

		$this->save_custom_permissions(true);

		$message = Text::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_CUSTOMSAVEDAPPLIED');
		$path    = $this->input->get('path', '', 'raw', 2);
		$this->setRedirect('index.php?option=com_admintools&view=ConfigureFixPermissions&path=' . urlencode($path), $message);
	}

	private function save_custom_permissions($apply = false)
	{
		$path = $this->input->get('path', '', 'raw', 2);

		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureFixPermissions $model */
		$model = $this->getModel();
		$model->setState('path', $path);
		$model->applyPath();

		$folders = $this->input->get('folders', [], 'array', 2);
		$model->setState('folders', $folders);
		$files = $this->input->get('files', [], 'array', 2);
		$model->setState('files', $files);

		$model->savePermissions($apply);
	}
}
