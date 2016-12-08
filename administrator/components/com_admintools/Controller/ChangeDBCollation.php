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
		$model = $this->getModel();
		$collation = $this->input->getString('collation', 'utf8mb4_general_ci');
		$model->changeCollation($collation);

		$msg = \JText::_('COM_ADMINTOOLS_LBL_CHANGEDBCOLLATION_DONE');
		$this->setRedirect('index.php?option=com_admintools&view=ChangeDBCollation', $msg);
	}
}