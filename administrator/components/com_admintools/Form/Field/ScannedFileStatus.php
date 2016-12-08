<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\ScanAlerts;
use FOF30\Form\Field\Text;

class ScannedFileStatus extends Text
{
	public function getRepeatable()
	{
		/** @var ScanAlerts $item */
		$item = $this->item;
		$extra_class= '';

		if(!$item->threat_score)
		{
			$extra_class = ' admintools-scanfile-nothreat';
		}

		if ($item->newfile)
		{
			$fstatus = 'new';
		}
		elseif ($item->suspicious)
		{
			$fstatus = 'suspicious';
		}
		else
		{
			$fstatus = 'modified';
		}

		$html = '<span class="admintools-scanfile-'.$fstatus.$extra_class.'">'.\JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $fstatus).'</span>';

		return $html;
	}
}