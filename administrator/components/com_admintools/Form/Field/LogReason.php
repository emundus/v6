<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\AutoBannedAddresses;
use FOF30\Form\Field\Text;

class LogReason extends Text
{
	public function getRepeatable()
	{
		/** @var AutoBannedAddresses $item */
		$item = $this->item;

		$html = \JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($item->reason));
		
		return $html;
	}
}