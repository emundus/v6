<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class EventbookingViewPluginHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$registry = new Registry();
		$registry->loadString($this->item->params);
		$data         = new stdClass();
		$data->params = $registry->toArray();
		$form         = JForm::getInstance('pmform', JPATH_ROOT . '/components/com_eventbooking/payments/' . $this->item->name . '.xml', [], false, '//config');
		$form->bind($data);
		$this->form = $form;
	}

	/**
	 * Build custom toolbar
	 *
	 * @see RADViewItem::addToolbar()
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(Text::_('EB_PLUGIN') . ': <small><small>[edit]</small></small>');
		JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel');
	}
}
