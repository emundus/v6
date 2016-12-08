<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

use FOF30\Form\Field\Text;

defined('_JEXEC') or die;

class KeepURLParams extends Text
{
	public function getRepeatable()
	{
		switch ($this->value)
		{
			case 1:
				$key = 'ALL';
				break;

			case 2:
				$key = 'ADD';
				break;
		
			case 0:
			default:
				$key = 'OFF';
				break;
		}

		return \JText::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_' . $key);
	}
}