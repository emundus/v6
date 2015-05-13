<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerAdminpw extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'adminpw';
	}

	public function execute($task)
	{
		if (!in_array($task, array('protect', 'unprotect')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}


	public function protect()
	{
		if (!$this->checkACL('admintools.security'))
		{
			return false;
		}

		// CSRF prevention
		$this->_csrfProtection();

		$username = $this->input->getVar('username', '');
		$password = $this->input->getVar('password', '');
		$password2 = $this->input->getVar('password2', '');

		if (empty($username))
		{
			$this->setRedirect('index.php?option=com_admintools&view=adminpw', JText::_('ATOOLS_ERR_ADMINPW_NOUSERNAME'), 'error');

			return true;
		}

		if (empty($password))
		{
			$this->setRedirect('index.php?option=com_admintools&view=adminpw', JText::_('ATOOLS_ERR_ADMINPW_NOPASSWORD'), 'error');

			return true;
		}

		if ($password != $password2)
		{
			$this->setRedirect('index.php?option=com_admintools&view=adminpw', JText::_('ATOOLS_ERR_ADMINPW_PASSWORDNOMATCH'), 'error');

			return true;
		}

		$model = $this->getThisModel();

		$model->username = $username;
		$model->password = $password;

		$status = $model->protect();
		$url = 'index.php?option=com_admintools';
		if ($status)
		{
			$this->setRedirect($url, JText::_('ATOOLS_LBL_ADMINPW_APPLIED'));
		}
		else
		{
			$this->setRedirect($url, JText::_('ATOOLS_ERR_ADMINPW_NOTAPPLIED'), 'error');
		}
	}

	public function unprotect()
	{
		if (!$this->checkACL('admintools.security'))
		{
			return false;
		}

		// CSRF prevention
		$this->_csrfProtection();

		$model = $this->getThisModel();
		$status = $model->unprotect();
		$url = 'index.php?option=com_admintools';
		if ($status)
		{
			$this->setRedirect($url, JText::_('ATOOLS_LBL_ADMINPW_UNAPPLIED'));
		}
		else
		{
			$this->setRedirect($url, JText::_('ATOOLS_ERR_ADMINPW_NOTUNAPPLIED'), 'error');
		}
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}
}
