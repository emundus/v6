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
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class plgEventBookingAcym extends CMSPlugin
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
	 * @param $subject
	 * @param $config
	 */
	public function __construct(& $subject, $config)
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_acym/acym.php'))
		{
			return;
		}

		parent::__construct($subject, $config);

		Factory::getLanguage()->load('plg_eventbooking_acym', JPATH_ADMINISTRATOR);
	}

	/**
	 * Return list of custom fields in ACYMailing which will be used to map with fields in Events Booking
	 *
	 * @return array
	 */
	public function onGetNewsletterFields()
	{
	    if (!$this->app)
        {
            return [];
        }

	    $db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(['name', 'name'], ['value', 'text']))
			->from('#__acym_field')
			->where('name NOT IN ("ACYM_NAME", "ACYM_EMAIL")');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Render setting form
	 *
	 * @param   JTable  $row
	 *
	 * @return mixed
	 */
	public function onEditEvent($row)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		ob_start();

		$this->drawSettingForm($row);

		return ['title' => Text::_('PLG_EB_ACYM_LIST_SETTINGS'),
		        'form'  => ob_get_clean(),
		];
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   EventbookingTableEvent  $row
	 * @param   bool                    $isNew  true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		if (!$this->canRun($row))
		{
			return;
		}

		$params = new Registry($row->params);

		// Prevent notice/warning
		if (!isset($data['acymailing_list_ids']))
		{
			$data['acymailing_list_ids'] = [];
		}

		$params->set('acymailing_list_ids', implode(',', $data['acymailing_list_ids']));
		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Run when a membership activated
	 *
	 * @param   EventbookingTableRegistrant  $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if (!$this->app)
		{
			return;
		}

		$config = EventbookingHelper::getConfig();

		// In case subscriber doesn't want to subscribe to newsleter, stop
		if ($config->show_subscribe_newsletter_checkbox && empty($row->subscribe_newsletter))
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		// Only add subscribers to newsletter if they agree.
		if ($subscribeNewsletterField = $this->params->get('subscribe_newsletter_field'))
		{
			$query->select('name, fieldtype')
				->from('#__eb_fields')
				->where('id = ' . $db->quote((int) $subscribeNewsletterField));
			$db->setQuery($query);
			$field     = $db->loadObject();
			$fieldType = $field->fieldtype;
			$fieldName = $field->name;

			if ($fieldType == 'Checkboxes')
			{
				if (!isset($_POST[$fieldName]))
				{
					return;
				}
			}
			else
			{
				$fieldValue = strtolower($this->app->input->getString($fieldName));

				if (empty($fieldValue) || $fieldValue == 'no' || $fieldValue == '0')
				{
					return;
				}
			}
		}

		$event = Table::getInstance('EventBooking', 'Event');
		$event->load($row->event_id);
		$params  = new Registry($event->params);
		$listIds = $params->get('acymailing_list_ids', '');

		if (empty($listIds))
		{
			$listIds = $this->params->get('default_list_ids');
		}

		if ($listIds != '')
		{
			$listIds = explode(',', $listIds);

			$this->subscribeToAcyMailingLists($row, $listIds);

			if ($row->is_group_billing && $this->params->get('add_group_members_to_newsletter'))
			{
				$query->clear()
					->select('*')
					->from('#__eb_registrants')
					->where('group_id = ' . (int) $row->id);
				$db->setQuery($query);
				$groupMembers = $db->loadObjectList();

				foreach ($groupMembers as $groupMember)
				{
					$this->subscribeToAcyMailingLists($groupMember, $listIds);
				}
			}
		}
	}

	/**
	 * @param   EventbookingTableRegistrant  $row
	 * @param   array                        $listIds
	 */
	private function subscribeToAcyMailingLists($row, $listIds)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php';

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		/* @var acymuserClass $userClass */
		$userClass               = acym_get('class.user');
		$userClass->checkVisitor = false;

		if (method_exists($userClass, 'getOneByEmail'))
		{
			$subId = $userClass->getOneByEmail($row->email);
		}
		else
		{
			$subId = $userClass->getUserIdByEmail($row->email);
		}

		if (!$subId)
		{
			$myUser         = new stdClass();
			$myUser->email  = $row->email;
			$myUser->name   = trim($row->first_name . ' ' . $row->last_name);
			$myUser->cms_id = $row->user_id;

			$subId = $userClass->save($myUser);

			$config = EventbookingHelper::getConfig();

			if ($config->multiple_booking)
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($row->id, 4);
			}
            elseif ($row->is_group_billing)
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 1);
			}
			else
			{
				$rowFields = EventbookingHelperRegistration::getFormFields($row->event_id, 0);
			}

			$data = EventbookingHelperRegistration::getRegistrantData($row, $rowFields);

			foreach ($rowFields as $rowField)
			{
				if (!$rowField->newsletter_field_mapping)
				{
					continue;
				}

				// Get ID of field
				$query->clear()
					->select('id')
					->from('#__acym_field')
					->where('name = ' . $db->quote($rowField->newsletter_field_mapping));
				$db->setQuery($query);
				$fieldId = $db->loadResult();

				if (!$fieldId)
				{
					continue;
				}

				$fieldValue = isset($data[$rowField->name]) ? $data[$rowField->name] : '';

				$query->clear()
					->insert('#__acym_user_has_field')
					->columns($db->quoteName(['user_id', 'field_id', 'value']))
					->values(implode(',', $db->quote([$subId, $fieldId, $fieldValue])));

				try
				{
					$db->setQuery($query)
						->execute();
				}
				catch (Exception $e)
				{
					// Ignore the error for now
				}
			}
		}

		if (is_object($subId))
		{
			$subId = $subId->id;
		}

		$userClass->subscribe($subId, $listIds);
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php';

		if ($row->id)
		{
			$params  = new Registry($row->params);
			$listIds = explode(',', $params->get('acymailing_list_ids', ''));
		}
		else
		{
			$listIds = [];
		}

		/* @var acymlistClass $listClass */
		$listClass = acym_get('class.list');
		$allLists  = $listClass->getAllWithIdName();
		?>
        <table class="admintable adminform" style="width: 90%;">
            <tr>
                <td width="220" class="key">
					<?php echo Text::_('PLG_EB_ACYM_ASSIGN_TO_LIST_USER'); ?>
                </td>
                <td>
	                <?php echo EventbookingHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $allLists, 'acymailing_list_ids[]', 'class="form-select" multiple="multiple" size="10"', 'id', 'name', $listIds)); ?>
                </td>
                <td>
					<?php echo Text::_('PLG_EB_ACYM_ASSIGN_TO_LIST_USER_EXPLAIN'); ?>
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
	    if (!$this->app)
        {
            return false;
        }

	    if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
