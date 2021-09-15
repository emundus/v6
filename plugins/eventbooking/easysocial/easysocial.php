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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgEventbookingEasySocial extends CMSPlugin
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
	 * Constructor
	 *
	 * @param   object &$subject   The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(& $subject, $config)
	{
		if (!file_exists(JPATH_ROOT . '/components/com_easysocial/easysocial.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Method to get list of custom fields in Jomsocial used to map with fields in Membership Pro
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

		Factory::getLanguage()->load('com_easysocial', JPATH_ADMINISTRATOR);

		$db  = $this->db;
		$sql = 'SELECT unique_key AS `value`, title AS `text` FROM #__social_fields WHERE state=1 AND title != ""';
		$db->setQuery($sql);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$row->text = Text::_($row->text);
		}

		return $rows;
	}

	/**
	 * Method to get data stored in Jomsocial profile of the given user
	 *
	 * @param   int    $userId
	 * @param   array  $mappings
	 *
	 * @return array
	 */
	public function onGetProfileData($userId, $mappings = [])
	{
		if (!$this->app)
		{
			return [];
		}

		$synchronizer = new RADSynchronizerEasysocial();

		return $synchronizer->getData($userId, $mappings);
	}

	/**
	 * Method to create Jomsocial account for subscriber and assign him to selected Jomsocial groups when subscription is active
	 *
	 * @param $row
	 *
	 * @return bool
	 */
	/*public function onAfterStoreRegistrant($row)
	{
		if (!$this->canRun)
		{
			return;
		}

		if ($row->user_id)
		{
			$db  = $this->db;
			$sql = 'SELECT COUNT(*) FROM #__social_users WHERE user_id=' . $row->user_id;
			$db->setQuery($sql);
			$count = $db->loadResult();
			if (!$count)
			{
				$sql = 'INSERT INTO #__social_users(user_id) VALUES(' . $row->user_id . ')';
				$db->setQuery($sql);
				$db->execute();
			}

			$sql = 'SELECT id, title FROM #__social_fields WHERE state=1 AND title != ""';
			$db->setQuery($sql);
			$rowFields = $db->loadObjectList();
			$fieldList = array();
			foreach ($rowFields as $rowField)
			{
				$fieldList[$rowField->fieldcode] = $rowField->id;
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

			$fieldValues = array();
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
			
			if (count($fieldValues))
			{
				foreach ($fieldValues as $fieldCode => $fieldValue)
				{
					if (isset($fieldList[$fieldCode]))
					{
						$fieldId = $fieldList[$fieldCode];
						if ($fieldId)
						{
							$fieldValue = $db->quote($fieldValue);
							$sql        = "INSERT INTO #__social_fields_data(uid, field_id, `data`) VALUES($row->user_id, $fieldId, $fieldValue)";
							$db->setQuery($sql);
							$db->execute();
						}
					}
				}
			}

		}

		return true;
	}*/
}
