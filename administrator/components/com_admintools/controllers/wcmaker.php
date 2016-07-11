<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * web.config Maker (for IIS) - Controller
 */
class AdmintoolsControllerWcmaker extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'wcmaker';
	}

	public function execute($task)
	{
		if (!in_array($task, array('save', 'apply')))
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	public function save()
	{
		// CSRF prevention
		$this->_csrfProtection();

		/** @var AdmintoolsModelWcmaker $model */
		$model = $this->getThisModel();

		if (is_array($this->input))
		{
			$data = $this->input;
		}
		else
		{
			$data = $this->input->getData();
		}

		$model->saveConfiguration($data);

		$this->setRedirect('index.php?option=com_admintools&view=wcmaker', JText::_('ATOOLS_LBL_WCMAKER_SAVED'));
	}

	public function apply()
	{
		/** @var AdmintoolsModelWcmaker $model */
		$model = $this->getThisModel();

		if (is_array($this->input))
		{
			$data = $this->input;
		}
		else
		{
			$data = $this->input->getData();
		}

		$model->saveConfiguration($data);
		$status = $model->writeWebConfig();

		if (!$status)
		{
			$this->setRedirect('index.php?option=com_admintools&view=wcmaker', JText::_('ATOOLS_LBL_WCMAKER_NOTAPPLIED'), 'error');
		}
		else
		{
			$this->setRedirect('index.php?option=com_admintools&view=wcmaker', JText::_('ATOOLS_LBL_WCMAKER_APPLIED'));
		}
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.security');
	}
}
