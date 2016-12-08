<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use FOF30\Form\Field\Text;

class RequestFieldForExceptionsFromWAF extends Text
{
	public function getRepeatable()
	{
		if(!$this->value)
		{
			if ($this->name == 'option')
			{
				$this->value = \JText::_('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION_ALL');
			}
			elseif ($this->name == 'view')
			{
				$this->value = \JText::_('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW_ALL');
			}
			else
			{
				$this->value = \JText::_('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY_ALL');
			}

		}

		return parent::getRepeatable();
	}
}