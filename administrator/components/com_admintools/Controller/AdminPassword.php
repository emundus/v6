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
use Akeeba\AdminTools\Admin\Controller\Mixin\SendTroubleshootingEmail;
use Exception;
use FOF40\Container\Container;
use FOF40\Controller\Controller;
use Joomla\CMS\Language\Text;

class AdminPassword extends Controller
{
	use PredefinedTaskList, CustomACL, SendTroubleshootingEmail;

	/**
	 * AdminPassword constructor.
	 *
	 * @param   Container  $container  Component container
	 * @param   array      $config     Controller configuration overrides
	 */
	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'protect', 'unprotect'];
	}

	/**
	 * Enabled administrator directory password protection
	 *
	 * @throws Exception
	 */
	public function protect()
	{
		// CSRF prevention
		$this->csrfProtection();

		$username        = $this->input->get('username', '', 'raw', 2);
		$password        = $this->input->get('password', '', 'raw', 2);
		$password2       = $this->input->get('password2', '', 'raw', 2);
		$resetErrorPages = $this->input->get('resetErrorPages', 1, 'int');
		$mode            = $this->input->get('mode', 'everything', 'cmd');

		if (!in_array($mode, ['joomla', 'php', 'everything']))
		{
			$mode = 'everything';
		}

		if (empty($username))
		{
			$this->setRedirect('index.php?option=com_admintools&view=AdminPassword', Text::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOUSERNAME'), 'error');

			return;
		}

		if (empty($password))
		{
			$this->setRedirect('index.php?option=com_admintools&view=AdminPassword', Text::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOPASSWORD'), 'error');

			return;
		}

		if ($password != $password2)
		{
			$this->setRedirect('index.php?option=com_admintools&view=AdminPassword', Text::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_PASSWORDNOMATCH'), 'error');

			return;
		}

		$this->sendTroubelshootingEmail($this->getName());

		/** @var \Akeeba\AdminTools\Admin\Model\AdminPassword $model */
		$model = $this->getModel();

		$model->username        = $username;
		$model->password        = $password;
		$model->resetErrorPages = $resetErrorPages;
		$model->mode            = $mode;

		$status = $model->protect();
		$url    = 'index.php?option=com_admintools';

		if ($status)
		{
			$this->setRedirect($url, Text::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_APPLIED'));

			return;
		}

		$this->setRedirect($url, Text::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOTAPPLIED'), 'error');
	}

	public function unprotect()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\AdminPassword $model */
		$model  = $this->getModel();
		$status = $model->unprotect();
		$url    = 'index.php?option=com_admintools';

		if ($status)
		{
			$this->setRedirect($url, Text::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNAPPLIED'));

			return;
		}

		$this->setRedirect($url, Text::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOTUNAPPLIED'), 'error');
	}
}
