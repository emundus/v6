<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_SITE);
JLoader::import('components.com_dpcalendar.helpers.plugin', JPATH_SITE);

class DPCalendarViewBooking extends JViewLegacy
{

	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Booking', 'DPCalendarModel');
		$this->setModel($model, true);

		$this->state = $this->get('State');
		$this->params = &$this->state->params;

		if (in_array($this->getLayout(), array(
				'pay',
				'order',
				'complete',
				'cancel'
		)))
		{
			JPluginHelper::importPlugin('dpcalendarpay');

			$this->plugin = $app->input->get('type');
			$this->item = $this->get('Item');

			if ($this->item->id == null)
			{
				JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
			$this->tickets = $this->getModel()->getTickets($this->item->id);
		}
		else
		{
			$this->item = $this->getModel()->getItem(array(
					'uid' => $app->input->get('uid')
			));

			if (! $this->item || $this->item->id == null)
			{
				$user = JFactory::getUser();
				if ($user->guest)
				{
					JFactory::getApplication()->redirect(
							JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())),
							JText::_('COM_DPCALENDAR_NOT_LOGGED_IN'), 'warning');
					return false;
				}

				JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
			$this->tickets = $this->getModel()->getTickets($this->item->id);
		}

		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument ()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();

		if (empty($this->item->id))
		{
			$head = JText::_('COM_DPCALENDAR_VIEW_FORM_EDIT_EVENT');
		}
		else
		{
			$head = JText::_('COM_DPCALENDAR_VIEW_FORM_EDIT_EVENT');
		}

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', $head);
		}

		$title = $this->params->def('page_title', $head);
		if ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		else if ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
