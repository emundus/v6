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

class SEOAndLinkTools extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'save', 'apply'];
	}

	public function save()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\SEOAndLinkTools $model */
		$model = $this->getModel();
		
		if (is_array($this->input))
		{
			$data = $this->input;
		}
		else
		{
			$data = $this->input->getData();
		}

		$model->saveConfig($data);

		$this->setRedirect('index.php?option=com_admintools&view=ControlPanel', JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_CONFIGSAVED'));
	}

	public function apply()
	{
		$this->save();
		$this->setRedirect('index.php?option=com_admintools&view=SEOAndLinkTools', JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_CONFIGSAVED'));
	}
}