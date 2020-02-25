<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use JText;

class UnblockIP extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'unblock'];
	}

	public function unblock()
	{
		// CSRF prevention
		$this->csrfProtection();

		$ip = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\UnblockIP $model */
		$model = $this->getModel();

		$status = $model->unblockIP($ip);

		$url = 'index.php?option=com_admintools&view=UnblockIP';

		if ($status)
		{
			$this->setRedirect($url, JText::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_OK'));
		}
		else
		{
			$this->setRedirect($url, JText::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_NOTFOUND'), 'warning');
		}
	}
}
