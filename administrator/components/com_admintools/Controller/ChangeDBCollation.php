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

class ChangeDBCollation extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'apply'];
	}

	public function apply()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ChangeDBCollation $model */
		$model     = $this->getModel();
		$collation = $this->input->getString('collation', 'utf8mb4_general_ci');
		$model->changeCollation($collation);

		$msg = Text::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_DONE');
		$this->setRedirect('index.php?option=com_admintools&view=ChangeDBCollation', $msg);
	}
}
