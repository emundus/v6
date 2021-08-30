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

JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');
JLoader::register('EventbookingHelperRoute', JPATH_ROOT . '/components/com_eventbooking/helper/route.php');

/**
 * Routing class from com_eventbooking
 *
 * @since  2.8.1
 */
class EventbookingRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_eventbooking component
	 *
	 * @param   array &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   2.8.1
	 */
	public function build(&$query)
	{
		static $additionalVars = null;

		if ($additionalVars === null)
		{
			if (file_exists(JPATH_ROOT . '/components/com_eventbooking/additional_unprocessed_vars.php'))
			{
				$additionalVars = require JPATH_ROOT . '/components/com_eventbooking/additional_unprocessed_vars.php';
			}
			else
			{
				$additionalVars = [];
			}
		}

		$segments = [];

		//Store the query string to use in the parseRouter method
		$queryArr = $query;

		//We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);

			// If the given menu item doesn't belong to our component, unset the Itemid from query array
			if ($menuItem && $menuItem->component != 'com_eventbooking')
			{
				unset($query['Itemid']);
			}
		}

		// Initialize default value for menu item query
		if ($menuItem && empty($menuItem->query['view']))
		{
			$menuItem->query['view'] = '';
		}

		if ($menuItem && empty($menuItem->query['layout']))
		{
			$menuItem->query['layout'] = 'default';
		}

		if ($menuItem && isset($query['view']) && $menuItem->query['view'] === $query['view'])
		{
			$unsetView = true;

			if (isset($query['id'], $menuItem->query['id']) && $query['id'] != $menuItem->query['id'])
			{
				$unsetView = false;
			}

			if (isset($query['id'], $menuItem->query['id']) && $query['id'] == $menuItem->query['id'])
			{
				unset($query['id'], $query['catid']);
			}

			if (isset($query['layout']) && $query['layout'] == $menuItem->query['layout'])
			{
				unset($query['layout']);
			}

			if ($unsetView)
			{
				unset($query['view']);
			}
		}

		//Dealing with the catid parameter in the link to event from category, upcoming events, calendar or full calendar page
		if ($menuItem
			&& isset($query['catid'])
			&& in_array($menuItem->query['view'], ['category', 'upcomingevents', 'calendar', 'fullcalendar'])
			&& $menuItem->query['id'] == intval($query['catid'])
		)
		{
			unset($query['catid']);
		}

		$view    = isset($query['view']) ? $query['view'] : '';
		$id      = isset($query['id']) ? (int) $query['id'] : 0;
		$catId   = isset($query['catid']) ? (int) $query['catid'] : 0;
		$eventId = isset($query['event_id']) ? (int) $query['event_id'] : 0;
		$task    = isset($query['task']) ? $query['task'] : '';
		$layout  = isset($query['layout']) ? $query['layout'] : '';

		// Language, pass from component association helper
		$language = isset($query['al']) ? $query['al'] : null;

		switch ($view)
		{
			case 'categories':
			case 'category':
				if ($id)
				{
					$segments = array_merge($segments, EventbookingHelperRoute::getCategoryPath($id, $language));
				}

				unset($query['view'], $query['id'], $query['al']);
				break;
			case 'event':
				if ($id)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($id, $language);
				}

				if ($layout == 'form')
				{
					$segments[] = 'edit';
					unset($query['layout']);
				}
				else
				{
					$config = EventbookingHelper::getConfig();

					if ($catId && $config->insert_category != 2)
					{
						$segments = array_merge(EventbookingHelperRoute::getCategoryPath($catId, $language), $segments);
					}
				}

				unset($query['view'], $query['id'], $query['al']);
				break;
			case 'location':
				if ($layout == 'form' || $layout == 'popup')
				{
					if ($id)
					{
						$segments[] = EventbookingHelperRoute::getLocationAlias($id);
						$segments[] = 'edit';
						unset($query['id']);
					}
					else
					{
						$segments[] = 'add location';
					}

					if ($layout == 'form')
					{
						unset($query['layout']);
					}
				}
				else
				{
					if (isset($query['location_id']))
					{
						$segments[] = EventbookingHelperRoute::getLocationAlias($query['location_id']);
						unset($query['location_id']);
					}
				}
				unset($query['view']);
				break;
			case 'register':
				if (in_array($layout, ['number_members', 'group_members', 'group_billing']))
				{
					if ($eventId)
					{
						$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
					}

					switch ($layout)
					{
						case 'number_members':
							$segments[] = 'number members form';
							break;
						case 'group_members':
							$segments[] = 'group members form';
							break;
						case 'group_billing':
							$segments[] = 'group billing form';
							break;
					}

					unset($query['view']);
					unset($query['layout']);
				}
				break;
			case 'map':
				if (isset($query['location_id']))
				{
					$segments[] = EventbookingHelperRoute::getLocationAlias($query['location_id']);
					unset($query['location_id']);
				}

				$segments[] = 'view map';
				unset($query['view']);
				break;
			case 'cart':
				$segments[] = 'view cart';
				unset($query['view']);
				break;
			case 'invite':
				if ($id)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($id);
				}

				$segments[] = 'invite friend';
				unset($query['view'], $query['id']);
				break;
			case 'password':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}

				$segments[] = 'password validation';
				unset($query['view'], $query['id']);
				break;
			case 'registrantlist':
				if ($id)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($id);
				}
				$segments[] = 'registrants list';
				unset($query['view'], $query['id']);
				break;
			case 'waitinglist':
				$segments[] = 'join waiting list successfull';
				unset($query['view']);
				break;
			case 'failure':
				$segments[] = 'registration failure';
				unset($query['view']);
				break;
			case 'cancel':
				$segments[] = 'registration cancel';
				unset($query['view']);
				break;
			case 'complete':
				$segments[] = 'Registration Complete';
				unset($query['view']);
				break;
			case 'registrationcancel':
				$segments[] = 'registration cancelled';
				unset($query['view']);
				break;
			case 'search':
				$segments[] = 'search result';
				unset($query['view']);
				break;
			case 'payment':
				if ($layout == 'registration')
				{
					$segments[] = 'registration payment';
				}
				elseif ($layout == 'complete')
				{
					$segments[] = 'payment-complete';
				}
				else
				{
					$segments[] = 'remainder payment';
				}

				if (isset($query['registrant_id']))
				{
					$segments[] = $query['registrant_id'];
					unset($query['registrant_id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
				unset($query['view']);
				break;
		}

		switch ($task)
		{
			case 'register.individual_registration':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'individual registration';
				unset($query['task']);
				break;
			case 'register.group_registration':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'group registration';
				unset($query['task']);
				break;
			case 'register.store_number_registrants':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'store number registrants';
				unset($query['task']);
				break;
			case 'register.validate_and_store_group_members_data':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'store group members data';
				unset($query['task']);
				break;
			case 'register.store_billing_data_and_display_group_members_form':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'store group billing data';
				unset($query['task']);
				break;
			case 'register.calculate_individual_registration_fee':
				$segments[] = 'calculate individual registration fee';
				unset($query['task']);
				break;
			case 'register.calculate_group_registration_fee':
				$segments[] = 'calculate group registration fee';
				unset($query['task']);
				break;
			case 'cart.calculate_cart_registration_fee':
				$segments[] = 'calculate cart registration fees';
				unset($query['task']);
				break;
			case 'group_billing':
				$segments[] = 'group billing';
				unset($query['task']);
				break;
			case 'event.download_ical':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'download_ical';
				unset($query['task']);
				break;
			case 'event.unpublish':
				if ($id)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($id);
				}
				$segments[] = 'Unpublish';
				unset($query['task']);
				unset($query['id']);
				break;

			case 'event.publish':
				if ($id)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($id);
				}
				$segments[] = 'Publish';
				unset($query['task']);
				unset($query['id']);
				break;
			case 'registrant.export':
				if ($eventId)
				{
					$segments[] = EventbookingHelperRoute::getEventAlias($eventId);
				}
				$segments[] = 'Export Registrants';
				unset($query['task']);
				break;
			case 'checkout':
			case 'view_checkout':
				$segments[] = 'Checkout';
				unset($query['task']);
				break;
		}

		if (isset($query['event_id']))
		{
			unset($query['event_id']);
		}

		if (isset($query['catid']))
		{
			unset($query['catid']);
		}

		$segments = array_filter($segments);

		if (count($segments))
		{
			$unProcessedVariables = [
				'option',
				'Itemid',
				'category_id',
				'search',
				'filter_city',
				'filter_state',
				'start',
				'limitstart',
				'limit',
				'print',
				'created_by',
				'format',
				'filter_from_date',
				'filter_to_date',
				'filter_duration',
				'filter_address',
				'filter_distance',
				'registration_code',
				'registrant_type',
				'al',
				'lang',
			];

			if (!in_array($view, ['location', 'map']))
			{
				$unProcessedVariables[] = 'location_id';
			}

			$unProcessedVariables = array_merge($unProcessedVariables, $additionalVars);

			foreach ($unProcessedVariables as $variable)
			{
				unset($queryArr[$variable]);
			}

			$queryString = http_build_query($queryArr);
			$segments    = array_map('Joomla\CMS\Application\ApplicationHelper::stringURLSafe', $segments);
			$route       = implode('/', $segments);
			$key         = md5($route);

			$db      = Factory::getDbo();
			$dbQuery = $db->getQuery(true)
				->select('id')
				->from('#__eb_urls')
				->where('md5_key = ' . $db->quote($key));
			$db->setQuery($dbQuery);
			$urlId = (int) $db->loadResult();

			if (!$urlId)
			{
				$dbQuery->clear()
					->insert('#__eb_urls')
					->columns($db->quoteName(['md5_key', 'query', 'route', 'view', 'record_id']))
					->values(implode(',', $db->quote([$key, $queryString, $route, $view, (int) $id])));
				$db->setQuery($dbQuery);
				$db->execute();
			}
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 * @throws  Exception
	 *
	 * @since   2.8.1
	 */
	public function parse(&$segments)
	{
		$vars = [];

		if (count($segments))
		{
			$db    = Factory::getDbo();
			$key   = md5(str_replace(':', '-', implode('/', $segments)));
			$query = $db->getQuery(true);
			$query->select('`query`')
				->from('#__eb_urls')
				->where('md5_key = ' . $db->quote($key));
			$db->setQuery($query);
			$queryString = $db->loadResult();

			if ($queryString)
			{
				parse_str(html_entity_decode($queryString), $vars);
			}
			else
			{
				$method = strtoupper(Factory::getApplication()->input->getMethod());

				if ($method == 'GET')
				{
					throw new Exception('Page not found', 404);
				}
			}

			if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
			{
				$segments = [];
			}
		}

		$item = Factory::getApplication()->getMenu()->getActive();

		if ($item)
		{
			if (!empty($vars['view']) && !empty($item->query['view']) && $vars['view'] == $item->query['view'])
			{
				foreach ($item->query as $key => $value)
				{
					if ($key != 'option' && $key != 'Itemid' && !isset($vars[$key]))
					{
						$vars[$key] = $value;
					}
				}
			}
		}

		if (isset($vars['tmpl']) && !isset($_GET['tmpl']))
		{
			unset($vars['tmpl']);
		}

		return $vars;
	}
}

/**
 * Events Booking router functions
 *
 * These functions are proxies for the new router interface
 * for old SEF extensions.
 *
 * @param   array &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 */
function EventbookingBuildRoute(&$query)
{
	$router = new EventbookingRouter();

	return $router->build($query);
}

function EventbookingParseRoute($segments)
{
	$router = new EventbookingRouter();

	return $router->parse($segments);
}
