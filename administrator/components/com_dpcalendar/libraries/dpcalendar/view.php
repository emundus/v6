<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarView extends JViewLegacy
{

	protected $icon = '';

	protected $title = '';

	public function display ($tpl = null)
	{
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->init();

		// Only render the sidebar when we are not editing a form
		if (! ($this->getModel() instanceof JModelAdmin))
		{
			$this->sidebar = JHtmlSidebar::render();
			$this->filterForm = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = DPCalendarHelper::getActions();

		if (empty($this->title))
		{
			$this->title = 'COM_DPCALENDAR_MANAGER_' . strtoupper($this->getName());
		}
		if (empty($this->icon))
		{
			$this->icon = strtolower($this->getName());
		}
		JToolBarHelper::title(JText::_($this->title), $this->icon);
		$document = JFactory::getDocument();
		$document->addStyleDeclaration(
				'.icon-48-' . $this->icon . ' {background-image: url(../media/com_dpcalendar/images/admin/48-' . $this->icon .
						 '.png);background-repeat: no-repeat;}');

		if ($canDo->get('core.admin', 'com_dpcalendar'))
		{
			JToolBarHelper::preferences('com_dpcalendar');
			JToolBarHelper::divider();
		}
	}

	protected function init ()
	{
	}
}
