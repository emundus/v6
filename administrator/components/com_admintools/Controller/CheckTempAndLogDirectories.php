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
use Exception;
use FOF40\Container\Container;
use FOF40\Controller\Controller;
use Joomla\CMS\Language\Text;

class CheckTempAndLogDirectories extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'check'];
	}

	public function check()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\CheckTempAndLogDirectories $model */
		$model = $this->getModel();

		$json['result'] = true;
		$json['msg']    = '';

		try
		{
			$folders        = $model->checkFolders();
			$folderMessages = [
				'<strong>' . Text::_('COM_ADMINTOOLS_LBL_CHECKTEMPANDLOGDIRECTORIES_TEMP_PATH') . '</strong>: ' . $folders['tmp'],
				'<strong>' . Text::_('COM_ADMINTOOLS_MSG_CHECKTEMPANDLOGDIRECTORIES_LOG_PATH') . '</strong>: ' . $folders['log'],
			];
			$json['msg']    = implode('<br/>', $folderMessages);
		}
		catch (Exception $e)
		{
			$json['result'] = false;
			$json['msg']    = $e->getMessage();
		}

		echo '###' . json_encode($json) . '###';

		$this->container->platform->closeApplication();
	}
}
