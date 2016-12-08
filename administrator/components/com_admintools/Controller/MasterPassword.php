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

class MasterPassword extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'save'];
	}

	public function save()
	{
		// CSRF prevention
		$this->csrfProtection();

		$masterpw = $this->input->get('masterpw', '', 'raw', 2);
		$views    = $this->input->get('views', array(), 'array', 2);

		$restrictedViews = array();

		foreach ($views as $view => $locked)
		{
			if ($locked == 1)
			{
				$restrictedViews[] = $view;
			}
		}

		/** @var \Akeeba\AdminTools\Admin\Model\MasterPassword $model */
		$model = $this->getModel();
		$model->saveSettings($masterpw, $restrictedViews);

		$this->setRedirect('index.php?option=com_admintools', JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_SAVED'));
	}
}