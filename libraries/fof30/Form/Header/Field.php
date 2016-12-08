<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Header;

use JHtml;
use JText;

defined('_JEXEC') or die;

/**
 * Generic field header, without any filters
 */
class Field extends HeaderBase
{
	/**
	 * Get the header
	 *
	 * @return  string  The header HTML
	 */
	protected function getHeader()
	{
		$sortable = ($this->element['sortable'] != 'false');

		$label = $this->getLabel();

		if ($sortable)
		{
			$view = $this->form->getView();

			return JHTML::_('grid.sort', $label, $this->name,
				$view->getLists()->order_Dir, $view->getLists()->order,
				$this->form->getModel()->task
			);
		}
		else
		{
			return JText::_($label);
		}
	}
}
