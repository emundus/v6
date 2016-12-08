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

class ScannedFileThreatScore extends Text
{
	public function getRepeatable()
	{
		/** @var ScanAlerts $item */
		$item = $this->item;

		if ($item->threat_score == 0)
		{
			$threatindex = 'none';
		}
		elseif ($item->threat_score < 10)
		{
			$threatindex = 'low';
		}
		elseif ($item->threat_score < 100)
		{
			$threatindex = 'medium';
		}
		else
		{
			$threatindex = 'high';
		}

		$html  = '<span class="admintools-scanfile-threat-'.$threatindex.'">';
		$html .=    '<span class="admintools-scanfile-pic">&nbsp;</span>';
		$html .=    $item->threat_score;
		$html .= '</span>';

		return $html;
	}
}