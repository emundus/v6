<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EventbookingViewDiscountsHtml extends RADViewList
{
	protected function prepareView()
	{
		parent::prepareView();

		$this->nullDate = Factory::getDbo()->getNullDate();
		$this->config   = EventbookingHelper::getConfig();
	}
}
