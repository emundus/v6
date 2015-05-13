<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerFixpermsconfig extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'Fixpermsconfig';
	}

	public function execute($task)
	{
		if (!in_array($task, array('savedefaults', 'saveperms', 'saveapplyperms')))
		{
			$task = 'browse';
		}
		$this->getThisModel()->setState('task', $task);
		parent::execute($task);
	}

	public function savedefaults()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$model = $this->getThisModel();
		$model->setState('dirperms', $this->input->getCmd('dirperms', '0755'));
		$model->setState('fileperms', $this->input->getCmd('fileperms', '0644'));
		$model->saveDefaults();

		$message = JText::_('ATOOLS_LBL_FIXPERMSCONFIG_DEFAULTSSAVED');
		$this->setRedirect('index.php?option=com_admintools&view=fixpermsconfig', $message);
	}

	public function onBeforeBrowse()
	{
		$path = $this->input->get('path', '', 'none', 2);

		$model = $this->getThisModel();
		$model->setState('path', $path);
		$model->applyPath();

		return $this->checkACL('admintools.maintenance');
	}

	/**
	 * Saves the custom permissions and reloads the current view
	 */
	public function saveperms()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$this->save_custom_permissions();

		$message = JText::_('ATOOLS_LBL_FIXPERMSCONFIG_CUSTOMSAVED');
		$path = $this->input->get('path', '', 'none', 2);
		$this->setRedirect('index.php?option=com_admintools&view=fixpermsconfig&path=' . urlencode($path), $message);
	}

	/**
	 * Saves the custom permissions, applies them and reloads the current view
	 */
	public function saveapplyperms()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$this->save_custom_permissions(true);

		$message = JText::_('ATOOLS_LBL_FIXPERMSCONFIG_CUSTOMSAVEDAPPLIED');
		$path = $this->input->get('path', '', 'none', 2);
		$this->setRedirect('index.php?option=com_admintools&view=fixpermsconfig&path=' . urlencode($path), $message);
	}

	private function save_custom_permissions($apply = false)
	{
		$path = $this->input->get('path', '', 'none', 2);

		$model = $this->getThisModel();
		$model->setState('path', $path);
		$model->applyPath();

		$folders = $this->input->get('folders', array(), 'array', 2);
		$model->setState('folders', $folders);
		$files = $this->input->get('files', array(), 'array', 2);
		$model->setState('files', $files);

		$model->savePermissions($apply);
	}

	protected function onBeforeSavedefaults()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeSaveperms()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeSaveapplyperms()
	{
		return $this->checkACL('admintools.maintenance');
	}
}
