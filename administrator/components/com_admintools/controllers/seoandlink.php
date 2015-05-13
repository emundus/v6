<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerSeoandlink extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'seoandlink';
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

		$model = $this->getThisModel();
		if (is_array($this->input))
		{
			$data = $this->input;
		}
		elseif ($this->input instanceof F0FInput)
		{
			$data = $this->input->getData();
		}
		else
		{
			$data = JRequest::get('POST', 2);
		}
		$model->saveConfig($data);

		$this->setRedirect('index.php?option=com_admintools&view=cpanel', JText::_('ATOOLS_LBL_SEOANDLINK_CONFIGSAVED'));
	}

	public function apply()
	{
		$this->save();
		$this->setRedirect('index.php?option=com_admintools&view=seoandlink', JText::_('ATOOLS_LBL_SEOANDLINK_CONFIGSAVED'));
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.utils');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.utils');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.utils');
	}
}
