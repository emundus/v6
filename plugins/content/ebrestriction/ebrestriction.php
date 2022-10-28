<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class plgContentEBRestriction extends CMSPlugin
{
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		if (file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php') && is_object($row))
		{
			// Check whether the plugin should process or not
			if (StringHelper::strpos($row->text, 'ebrestriction') === false)
			{
				return true;
			}

			// Search for this tag in the content
			$regex     = '#{ebrestriction ids="(.*?)"}(.*?){/ebrestriction}#s';
			$row->text = preg_replace_callback($regex, [&$this, 'processRestriction'], $row->text);
		}

		return true;
	}

	/**
	 * Process content restriction
	 *
	 * @param   array  $matches
	 *
	 * @return string
	 */
	private function processRestriction($matches)
	{
		$requiredEventIds = $matches[1];
		$protectedText    = $matches[2];
		$registeredEvents = $this->getRegisteredEvents();

		if ($this->isEventOwner($requiredEventIds))
		{
			return $protectedText;
		}

		if (count($registeredEvents) == 0)
		{
			return '';
		}
		elseif ($requiredEventIds == '*')
		{
			return $protectedText;
		}
		else
		{
			$requiredEventIds = array_filter(ArrayHelper::toInteger(explode(',', $requiredEventIds)));

			if (count(array_intersect($requiredEventIds, $registeredEvents)))
			{
				return $protectedText;
			}
			else
			{
				return '';
			}
		}
	}

	/**
	 *  Get list of events which the current user has registered
	 *
	 * @return array
	 */
	private function getRegisteredEvents()
	{
		$user = Factory::getUser();

		if ($user->id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('event_id')
				->from('#__eb_registrants')
				->where('published=1')
				->where('user_id=' . $user->id);
			$db->setQuery($query);

			return $db->loadColumn();
		}

		return [];
	}

	/**
	 *
	 * @param   string  $requiredEventIds
	 */
	private function isEventOwner($requiredEventIds)
	{
		$user             = Factory::getUser();
		$requiredEventIds = array_filter(ArrayHelper::toInteger(explode(',', $requiredEventIds)));

		if ($user->id && count($requiredEventIds))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('created_by')
				->from('#__eb_events')
				->where('id IN (' . implode(',', $requiredEventIds) . ')');
			$db->setQuery($query);
			$createdBys = $db->loadColumn();

			if (in_array($user->id, $createdBys))
			{
				return true;
			}
		}

		return false;
	}
}
