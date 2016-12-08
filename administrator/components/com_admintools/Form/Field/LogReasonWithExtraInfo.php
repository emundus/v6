<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\SecurityExceptions;
use FOF30\Form\Field\Text;

class LogReasonWithExtraInfo extends Text
{
	public function getRepeatable()
	{
		/** @var SecurityExceptions $item */
		$item = $this->item;

		$html = \JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($item->reason));

		if ($item->extradata)
		{
			if (stristr($item->extradata, '|') === false)
			{
				$item->extradata .= '|';
			}

			list($moreinfo, $techurl) = explode('|', $item->extradata);

			$html .= '&nbsp;'.\JHtml::_('tooltip', strip_tags(htmlspecialchars($moreinfo, ENT_COMPAT, 'UTF-8')), '', 'tooltip.png', '', $techurl);
		}

		return $html;
	}
}