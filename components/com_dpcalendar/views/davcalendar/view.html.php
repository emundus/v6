<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


class DPCalendarViewDavcalendar extends \DPCalendar\View\BaseView
{

	protected $form;

	protected $item;

	protected $return_page;

	protected $state;

	public function init()
	{
		$user = JFactory::getUser();

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		$authorised = true;
		if ($this->item != null && $this->item->id > 0)
		{
			$authorised = $this->item->principaluri == 'principals/' . $user->username;
		}

		if ($authorised !== true)
		{
			$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		if (!empty($this->item) && isset($this->item->id))
		{
			$this->form->bind($this->item);
		}

		parent::init();
	}
}
