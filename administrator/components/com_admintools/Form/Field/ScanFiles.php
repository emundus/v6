<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use FOF30\Form\Field\Text;

class ScanFiles extends Text
{
	public function getRepeatable()
	{
		$class = 'noalert';
		
		if($this->value)
		{
			$class = 'alert';
		}
		
		return '<span class="admintools-files-'.$class.'">'.$this->value.'</span>';
	}
}