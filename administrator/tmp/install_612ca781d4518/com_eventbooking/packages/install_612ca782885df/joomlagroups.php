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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class plgEventbookingJoomlagroups extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object    $subject
	 * @param   Registry  $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		Factory::getLanguage()->load('plg_eventbooking_joomlagroups', JPATH_ADMINISTRATOR);
	}

	/**
	 * Render settings form
	 *
	 * @param $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);

		return ['title' => Text::_('PLG_EVENTBOOKING_JOOMLA_GROUPS_SETTINGS'),
		        'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   Boolean                 $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		if (!isset($data['joomla_group_ids']))
		{
			$data['joomla_group_ids'] = [];
		}

		$params = new Registry($row->params);
		$params->set('joomla_group_ids', implode(',', $data['joomla_group_ids']));
		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * This method is run after registration record is stored into database
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if ($row->user_id
			&& $this->params->get('assign_offline_pending_registrants', '0')
			&& strpos($row->payment_method, 'os_offline') !== false)
		{
			$this->assignToUserGroups($row);
		}
	}

	/**
	 * Add registrants to selected Joomla groups when payment for registration completed
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterPaymentSuccess($row)
	{
		if ($row->user_id
			&& (strpos($row->payment_method, 'os_offline') === false || !$this->params->get('assign_offline_pending_registrants', '0')))
		{
			$this->assignToUserGroups($row);
		}
	}

	/**
	 * Add registrants to selected Joomla groups which is configured in registered event
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	private function assignToUserGroups($row)
	{
		$user          = Factory::getUser($row->user_id);
		$currentGroups = $user->get('groups');
		$event         = Table::getInstance('EventBooking', 'Event');
		$eventIds      = [$row->event_id];
		$config        = EventbookingHelper::getConfig();

		if ($config->multiple_booking)
		{
			// Get all events which users register for in this cart registration
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('event_id')
				->from('#__eb_registrants')
				->where('cart_id=' . $row->id);
			$db->setQuery($query);
			$eventIds = array_unique(array_merge($eventIds, $db->loadColumn()));
		}

		// Calculate the groups which registrant should be assigned to
		foreach ($eventIds as $eventId)
		{
			$event->load($eventId);
			$params   = new Registry($event->params);
			$groupIds = $params->get('joomla_group_ids');

			if (!$groupIds)
			{
				$groupIds = implode(',', $this->params->get('default_user_groups', []));
			}

			if ($groupIds)
			{
				$groups        = explode(',', $groupIds);
				$currentGroups = array_unique(array_merge($currentGroups, $groups));
			}
		}

		$user->set('groups', $currentGroups);
		$user->save(true);
	}

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$params           = new Registry($row->params);
		$joomla_group_ids = explode(',', $params->get('joomla_group_ids', ''));
		?>
        <table class="admintable adminform" style="width: 90%;">
            <tr>
                <td width="220" class="key">
					<?php echo Text::_('PLG_EVENTBOOKING_JOOMLA_ASSIGN_TO_JOOMLA_GROUPS'); ?>
                </td>
                <td>
					<?php echo EventbookingHelperHtml::getChoicesJsSelect(HTMLHelper::_('access.usergroup', 'joomla_group_ids[]', $joomla_group_ids, ' multiple="multiple" size="6" ', false)); ?>
                </td>
                <td>
					<?php echo Text::_('PLG_EVENTBOOKING_JOOMLA_ASSIGN_TO_JOOMLA_GROUPS_EXPLAIN'); ?>
                </td>
            </tr>
        </table>
		<?php
	}

	/**
	 * Method to check to see whether the plugin should run
	 *
	 * @param   EventbookingTableEvent  $row
	 *
	 * @return bool
	 */
	private function canRun($row)
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
