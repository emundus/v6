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
use FOF40\Controller\Controller;
use Joomla\CMS\Language\Text;

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
			$this->setRedirect($url, Text::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_OK'));
		}
		else
		{
			$this->setRedirect($url, Text::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_NOTFOUND'), 'warning');
		}
	}
}
