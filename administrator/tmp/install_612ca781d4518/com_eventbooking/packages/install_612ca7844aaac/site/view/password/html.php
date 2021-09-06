<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

class EventbookingViewPasswordHtml extends RADViewHtml
{
	protected function prepareView()
	{
		parent::prepareView();

		$this->setLayout('default');
		$this->return          = $this->input->getBase64('return', '');
		$this->eventId         = $this->input->getInt('event_id', 0);
		$this->eventUrl        = Route::_(EventbookingHelperRoute::getEventRoute($this->eventId, 0, $this->Itemid), false);
		$this->bootstrapHelper = EventbookingHelperBootstrap::getInstance();
	}
}
