<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use FOF30\Form\Field\Text;

class RequestPartForWAFBlacklist extends Text
{
	public function getRepeatable()
	{
		if (!$this->value)
		{
			$this->value = \JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL');
		}

		return parent::getRepeatable();
	}
}