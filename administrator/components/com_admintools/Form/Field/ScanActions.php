<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\Scans;
use FOF30\Form\Field\Text;
use JText;

class ScanActions extends Text
{
	public function getRepeatable()
	{
		/** @var Scans $item */
		$item = $this->item;

		if($item->files_modified + $item->files_new + $item->files_suspicious)
		{
			$html  = '<a class="btn btn-mini" href="index.php?option=com_admintools&view=ScanAlerts&scan_id='.$item->id.'">';
			$html .= JText::_('COM_ADMINTOOLS_LBL_SCAN_ACTIONS_VIEW').'</a>';

			return $html;
		}

		return JText::_('COM_ADMINTOOLS_LBL_SCAN_ACTIONS_NOREPORT');
	}
}