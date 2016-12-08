<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Model\SecurityExceptions;
use FOF30\Form\Field\Text;

class LogIp extends Text
{
	public function getRepeatable()
	{
		/** @var SecurityExceptions $item */
		$item = $this->item;
		$cparams = Storage::getInstance();
		$iplink = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');

		$link = str_replace('{ip}', $item->ip, $iplink);

		$html = '<a href="'.$link.'" target="_blank" class="btn btn-mini btn-info"><i class="icon-search icon-white"></i></a>&nbsp;';

		$token = \JFactory::getSession()->getFormToken();

		if($item->block)
		{
			$html .= '<a class="btn btn-mini btn-success" ';
			$html .= 'href="index.php?option=com_admintools&view=SecurityExceptions&task=unban&id='.$item->id.'&'.$token.'=1" ';
			$html .= 'title="'.\JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_UNBAN').'">';
			$html .= '<i class="icon-white icon-minus-sign"></i>';
			$html .= '</a>&nbsp;';
		}
		else
		{
			$html .= '<a class="btn btn-mini btn-danger" ';
			$html .= 'href="index.php?option=com_admintools&view=SecurityExceptions&task=ban&id='.$item->id.'&'.$token.'=1" ';
			$html .= 'title="'.\JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_BAN').'">';
			$html .= '<i class="icon-flag icon-white"></i>';
			$html .= '</a>&nbsp;';
		}

		$html .= htmlspecialchars($item->ip, ENT_COMPAT);

		return $html;
	}
}