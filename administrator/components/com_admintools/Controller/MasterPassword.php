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
		$views    = $this->input->get('views', [], 'array', 2);

		$restrictedViews = [];

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

		$this->setRedirect('index.php?option=com_admintools', Text::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_SAVED'));
	}
}
