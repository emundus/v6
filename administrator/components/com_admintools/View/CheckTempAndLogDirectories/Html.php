<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\CheckTempAndLogDirectories;

defined('_JEXEC') or die;

use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class Html extends BaseView
{
	protected function onBeforeMain()
	{
		if (version_compare(JVERSION, '3.999.999', 'lt'))
		{
			HTMLHelper::_('behavior.modal');
		}

		$this->addJavascriptFile('admin://components/com_admintools/media/js/CheckTempAndLogDirectories.min.js');

		Text::script('COM_ADMINTOOLS_LBL_CHECKTEMPANDLOGDIRECTORIES_CHECKCOMPLETED', true);
	}

}
