<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use JText;

class EmergencyOffline extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'offline', 'online'];
	}

	public function offline()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\EmergencyOffline $model */
		$model = $this->getModel();

		$status = $model->putOffline();
		$url = 'index.php?option=com_admintools';

		if ($status)
		{
			$this->setRedirect($url, JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_APPLIED'));
		}
		else
		{
			$this->setRedirect($url, JText::_('COM_ADMINTOOLS_ERR_EMERGENCYOFFLINE_NOTAPPLIED'), 'error');
		}
	}

	public function online()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\EmergencyOffline $model */
		$model  = $this->getModel();
		$status = $model->putOnline();
		$url    = 'index.php?option=com_admintools';

		if ($status)
		{
			$this->setRedirect($url, JText::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_UNAPPLIED'));
		}
		else
		{
			$this->setRedirect($url, JText::_('COM_ADMINTOOLS_ERR_EMERGENCYOFFLINE_NOTUNAPPLIED'), 'error');
		}
	}
}