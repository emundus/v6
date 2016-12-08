<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\AdminPassword;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\AdminPassword;
use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * .htaccess username
	 *
	 * @var  string
	 */
	public $username;

	/**
	 * .htaccess password
	 *
	 * @var  string
	 */
	public $password;

	/**
	 * Is the backend locked?
	 *
	 * @var  string
	 */
	public $adminLocked;

	protected function onBeforeMain()
	{
		/** @var AdminPassword $model */
		$model = $this->getModel();

		$this->username     = $this->input->get('username', '', 'raw', 2);
		$this->password     = $this->input->get('password', '', 'raw', 2);
		$this->adminLocked  = $model->isLocked();
	}
}