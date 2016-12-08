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

class ScannedFilePath extends Text
{
	public function getRepeatable()
	{
		/** @var ScanAlerts $item */
		$item = $this->item;

		if (strlen($item->path) > 100)
		{
			$truncatedPath = true;
			$path          = htmlspecialchars(substr($item->path, -100));
			$alt           = 'title="' . htmlspecialchars($item->path) . '"';
		}
		else
		{
			$truncatedPath = false;
			$path          = htmlspecialchars($item->path);
			$alt           = '';
		}

		$html  = $truncatedPath ? "&hellip;" : '';
		$html .= '<a href="index.php?option=com_admintools&view=ScanAlerts&task=edit&id='.$item->admintools_scanalert_id.'" '.$alt.'>';
		$html .= $path;
		$html .= '</a>';

		return $html;
	}
}