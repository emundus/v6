<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (EventbookingHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';
}
else
{
	$tabApiPrefix = 'bootstrap.';
}

if (!empty($this->editor))
{
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'event-custom-fields', Text::_('EB_EVENT_CUSTOM_FIELDS', true));

	$extra = '';

	if (file_exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
	{
		$extra = file_get_contents(JPATH_ROOT . '/components/com_eventbooking/fields.xml');
	}

	echo $this->editor->display('event_custom_fields', $extra, '100%', '550', '75', '8', false, null, null, null, ['syntax' => 'xml']);

	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}