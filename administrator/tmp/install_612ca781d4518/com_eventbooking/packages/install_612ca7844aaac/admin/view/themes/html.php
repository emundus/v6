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

class EventbookingViewThemesHtml extends RADViewList
{
	/**
	 * Override add toolbar method to add custom toolbar
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(Text::_('EB_THEME_MANAGEMENT'), 'generic.png');
		JToolBarHelper::publishList('publish', Text::_('EB_SET_DEFAULT'));
		JToolBarHelper::deleteList(Text::_('EB_THEME_DELETE_CONFIRM'), 'uninstall', 'Uninstall');
	}
}
