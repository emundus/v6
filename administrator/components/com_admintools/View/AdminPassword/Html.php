<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\AdminPassword;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Model\AdminPassword;
use FOF40\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * .htaccess username
	 *
	 * @var   string
	 */
	public $username;

	/**
	 * .htaccess password
	 *
	 * @var   string
	 */
	public $password;

	/**
	 * Should I reset custom error pages?
	 *
	 * @var   bool
	 *
	 * @since 5.3.4
	 */
	public $resetErrorPages;

	/**
	 * Is the backend locked?
	 *
	 * @var  string
	 */
	public $adminLocked;

	/**
	 * Protection mode
	 *
	 * @var   string
	 *
	 * @since 6.0.7
	 */
	public $mode;

	protected function onBeforeMain()
	{
		/** @var AdminPassword $model */
		$model = $this->getModel();

		$this->username        = $this->input->get('username', '', 'raw', 2);
		$this->password        = $this->input->get('password', '', 'raw', 2);
		$this->resetErrorPages = $this->input->get('resetErrorPages', 1, 'int');
		$this->mode            = $this->input->get('mode', 'everything', 'cmd');
		$this->adminLocked     = $model->isLocked();
	}
}
