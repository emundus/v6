<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */


defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class plgEventBookingCB extends CMSPlugin
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
	 * @param   object  $subject
	 * @param   array   $config
	 */
	public function __construct(& $subject, $config = [])
	{
		if (!file_exists(JPATH_ROOT . '/components/com_comprofiler/comprofiler.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Method to get list of custom fields in Community builder used to map with fields in Membership Pro
	 *
	 * Method is called on custom field add / edit page from backend of Membership Pro
	 *
	 * @return mixed
	 */
	public function onGetFields()
	{
		if (!$this->app)
		{
			return [];
		}

		$db  = $this->db;
		$sql = 'SELECT name AS `value`, name AS `text` FROM #__comprofiler_fields WHERE `table`="#__comprofiler"';
		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	/**
	 * Method to get data stored in CB profile of the given user
	 *
	 * @param   int    $userId
	 * @param   array  $mappings
	 *
	 * @return array
	 */
	public function onGetProfileData($userId, $mappings)
	{
		if (!$this->app)
		{
			return [];
		}

		$synchronizer = new RADSynchronizerCommunitybuilder();

		return $synchronizer->getData($userId, $mappings);
	}

	/**
	 * Update CB profile data with information which registrant entered on registration form
	 *
	 * @param $row
	 *
	 * @return bool|void
	 */
	public function onAfterStoreRegistrant($row)
	{
		if (!$this->app)
		{
			return;
		}

		if (!$row->user_id || !$this->params->get('update_cb_data', '1'))
		{
			return;
		}


		$db  = $this->db;
		$sql = 'SELECT count(*) FROM `#__comprofiler` WHERE `user_id` = ' . $db->quote($row->user_id);
		$db->setQuery($sql);
		$count = $db->loadResult();
		$sql   = ' SHOW FIELDS FROM #__comprofiler ';
		$db->setQuery($sql);
		$fields    = $db->loadObjectList();
		$fieldList = [];

		for ($i = 0, $n = count($fields); $i < $n; $i++)
		{
			$field       = $fields[$i];
			$fieldList[] = $field->Field;
		}

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

		$fieldValues = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->field_mapping && in_array($rowField->field_mapping, $fieldList) && isset($data[$rowField->name]))
			{
				$fieldValue = $data[$rowField->name];

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValues[$rowField->field_mapping] = implode('|*|', json_decode($fieldValue));
				}
				else
				{
					$fieldValues[$rowField->field_mapping] = $fieldValue;
				}
			}
		}

		$profile = new stdClass();

		$profile->id        = $row->user_id;
		$profile->user_id   = $row->user_id;
		$profile->firstname = $row->first_name;
		$profile->lastname  = $row->last_name;

		if (!$config->use_cb_api)
		{
			$profile->confirmed      = 1;
			$profile->avatarapproved = 1;
			$profile->registeripaddr = htmlspecialchars($_SERVER['REMOTE_ADDR']);
			$profile->banned         = 0;
			$profile->acceptedterms  = 1;
		}

		foreach ($fieldValues as $fieldName => $value)
		{
			$profile->{$fieldName} = $value;
		}

		if ($count)
		{
			$db->updateObject('#__comprofiler', $profile, 'id');
		}
		else
		{
			$db->insertObject('#__comprofiler', $profile);
		}
	}
}
