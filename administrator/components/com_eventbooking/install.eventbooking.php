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
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;

class com_eventbookingInstallerScript
{
	/**
	 * Language files
	 *
	 * @var array
	 */
	public static $languageFiles = ['en-GB.com_eventbooking.ini'];

	/**
	 * The original version, use for update process
	 *
	 * @var string
	 */
	protected $installedVersion = '1.0.0';

	/**
	 * Method to run before installing the component
	 */
	public function preflight($type, $parent)
	{
		// Get and store current installed version
		$this->getInstalledVersion();

		// If this is new install, we don't have to do anything
		if (strtolower($type) == 'install')
		{
			return true;
		}

		$this->deleteFilesFolders();

		//Backup the old language files
		foreach (self::$languageFiles as $languageFile)
		{
			if (JFile::exists(JPATH_ROOT . '/language/en-GB/' . $languageFile))
			{
				JFile::copy(JPATH_ROOT . '/language/en-GB/' . $languageFile, JPATH_ROOT . '/language/en-GB/bak.' . $languageFile);
			}
		}

		//Backup even custom fields
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_eventbooking/fields.xml', JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml');
		}
	}


	/**
	 * Method to run after installing the component
	 */
	public function postflight($type, $parent)
	{
		//Restore the modified language files + event custom fields file
		$this->restoreFiles();

		// Create needed files and folders
		$this->createFilesFolders();

		if (strtolower($type) == 'update')
		{
			// Create new tables if not exist
			$this->createTablesIfNotExist();

			// Synchronize db schema to latest version
			$this->synchronizeDBSchema();
		}

		if (Multilanguage::isEnabled())
		{
			JLoader::register('EventbookingHelperOverrideHelper', JPATH_ROOT . '/components/com_eventbooking/helper/override/helper.php');
			EventbookingHelper::callOverridableHelperMethod('Helper', 'setupMultilingual');
		}

		// Insert additional default data
		$this->insertAdditionalDefaultData();

		// Enable required plugins
		$this->enableRequiredPlugin();
	}

	/**
	 * Restore the files which were changed during installation process
	 *
	 */
	private function restoreFiles()
	{
		//Restore the modified language strings by merging to language files
		$registry = new Registry();

		foreach (self::$languageFiles as $languageFile)
		{
			$backupFile  = JPATH_ROOT . '/language/en-GB/bak.' . $languageFile;
			$currentFile = JPATH_ROOT . '/language/en-GB/' . $languageFile;

			if (JFile::exists($currentFile) && JFile::exists($backupFile))
			{
				$registry->loadFile($currentFile, 'INI');
				$currentItems = $registry->toArray();
				$registry->loadFile($backupFile, 'INI');
				$backupItems = $registry->toArray();
				$items       = array_merge($currentItems, $backupItems);

				foreach ($items as $key => $value)
				{
					$registry->set($key, addcslashes($value, '"'));
				}

				JFile::write($currentFile, $registry->toString('INI'));

				//Delete the backup file
				JFile::delete($backupFile);
			}
		}

		//Restore the renamed files
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml', JPATH_ROOT . '/components/com_eventbooking/fields.xml');
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml');
		}
	}

	/**
	 * Create necessary files and folders
	 */
	private function createFilesFolders()
	{
		// Create custom css file if it does not exist
		$customCss = JPATH_ROOT . '/media/com_eventbooking/assets/css/custom.css';

		if (!file_exists($customCss))
		{
			$fp = fopen($customCss, 'w');
			fclose($fp);
			@chmod($customCss, 0644);
		}
		else
		{
			@chmod($customCss, 0644);
		}

		$foldersToCreate = [];

		if (version_compare($this->installedVersion, '3.10.5', '<'))
		{
			$foldersToCreate = [
				JPATH_ROOT . '/images/com_eventbooking',
				JPATH_ROOT . '/images/com_eventbooking/categories',
				JPATH_ROOT . '/images/com_eventbooking/categories/thumb',
				JPATH_ROOT . '/images/com_eventbooking/galleries/thumbs',
				JPATH_ROOT . '/images/com_eventbooking/speakers',
				JPATH_ROOT . '/images/com_eventbooking/speakers/thumbs',
				JPATH_ROOT . '/images/com_eventbooking/sponsors',
				JPATH_ROOT . '/images/com_eventbooking/speakers/thumbs',
				JPATH_ROOT . '/images/com_eventbooking/sponsors',
				JPATH_ROOT . '/images/com_eventbooking/sponsors/thumbs',
				JPATH_ROOT . '/images/com_eventbooking/galleries',
				JPATH_ROOT . '/images/com_eventbooking/galleries/thumbs',
			];
		}

		foreach ($foldersToCreate as $folder)
		{
			if (!JFolder::exists($folder))
			{
				JFolder::create($folder);
			}
		}
	}

	/**
	 *  Delete files/folders which were using on old version but not needed on new version anymore
	 */
	private function deleteFilesFolders()
	{
		$deleteFiles   = [];
		$deleteFolders = [];

		if (version_compare($this->installedVersion, '3.8.3', '<'))
		{
			$deleteFiles = [
				// CSS files
				JPATH_ROOT . '/components/com_eventbooking/assets/css/default.css',
				JPATH_ROOT . '/components/com_eventbooking/assets/css/fire.css',
				JPATH_ROOT . '/components/com_eventbooking/assets/css/leaf.css',
				JPATH_ROOT . '/components/com_eventbooking/assets/css/ocean.css',
				JPATH_ROOT . '/components/com_eventbooking/assets/css/sky.css',
				JPATH_ROOT . '/components/com_eventbooking/assets/css/tree.css',
			];

			$deleteFolders = [
				JPATH_ROOT . '/components/com_eventbooking/views',
				JPATH_ROOT . '/components/com_eventbooking/view/common',
				JPATH_ROOT . '/components/com_eventbooking/emailtemplates',
				JPATH_ROOT . '/administrator/components/com_eventbooking/controller',
			];
		}

		if (version_compare($this->installedVersion, '3.11.0', '<'))
		{
			$deleteFiles[] = JPATH_ROOT . '/administrator/components/com_eventbooking/elements/ebcurrency';
		}

		// If there are more files need to be deleted on new versions, it will need to be added to $deleteFiles and $deleteFolders array

		foreach ($deleteFiles as $file)
		{
			if (JFile::exists($file))
			{
				JFile::delete($file);
			}
		}

		foreach ($deleteFolders as $folder)
		{
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
	}

	/**
	 * Create new tables if not exist during update
	 */
	private function createTablesIfNotExist()
	{
		JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

		$tableSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/createifnotexists.eventbooking.sql';

		EventbookingHelper::executeSqlFile($tableSql);
	}

	/**
	 * Synchronize db schema with latest version
	 */
	private function synchronizeDBSchema()
	{
		$db = Factory::getDbo();

		// Change row format for some tables
		if (version_compare($this->installedVersion, '3.13.0', '<='))
		{
			$tables = [
				'#__eb_categories',
				'#__eb_events',
				'#__eb_fields',
				'#__eb_locations',
				'#__eb_registrants',
			];

			try
			{
				foreach ($tables as $table)
				{
					$query = "ALTER TABLE `$table` ROW_FORMAT = DYNAMIC";
					$db->setQuery($query)
						->execute();
				}
			}
			catch (Exception $e)
			{

			}
		}

		//Registrants table
		$fields = array_keys($db->getTableColumns('#__eb_events'));

		if (version_compare($this->installedVersion, '3.8.5', '<='))
		{
			if (!in_array('registration_complete_url', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_complete_url` TEXT NULL ;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.9.0', '<='))
		{
			if (!in_array('offline_payment_registration_complete_url', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD  `offline_payment_registration_complete_url` TEXT NULL ;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.9.1', '<='))
		{
			if (!in_array('registrant_edit_close_date', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD  `registrant_edit_close_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('admin_email_body', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD  `admin_email_body` TEXT;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.10.0', '<='))
		{
			if (!in_array('hidden', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `hidden` TINYINT(4) NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.10.6', '<='))
		{
			if (!in_array('created_language', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `created_language` VARCHAR(50) DEFAULT '*';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.12.3', '<='))
		{
			if (!in_array('group_member_email_body', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `group_member_email_body` TEXT NULL;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.13.0', '<='))
		{
			if (!in_array('enable_sms_reminder', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `enable_sms_reminder` tinyint(3) UNSIGNED DEFAULT 0;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.14.0', '<='))
		{
			if (!in_array('waiting_list_capacity', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `waiting_list_capacity` INT UNSIGNED DEFAULT 0;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.16.2', '<='))
		{
			if (!in_array('first_reminder_frequency', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `first_reminder_frequency` CHAR(1) DEFAULT 'd';";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('second_reminder_frequency', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_events` ADD `second_reminder_frequency` CHAR(1) DEFAULT 'd';";
				$db->setQuery($sql)
					->execute();
			}
		}

		//Registrants table
		$fields = array_keys($db->getTableColumns('#__eb_registrants'));

		// Older version to 3.8.4
		if (version_compare($this->installedVersion, '3.8.3', '<='))
		{
			if (!in_array('invoice_year', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `invoice_year` INT NOT NULL DEFAULT  '0';";
				$db->setQuery($sql)
					->execute();

				$sql = 'UPDATE #__eb_registrants SET `invoice_year` = YEAR(`register_date`)';
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.8.5', '<='))
		{
			if (!in_array('is_offline_payment_reminder_sent', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `is_offline_payment_reminder_sent` TINYINT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.10.1', '<='))
		{
			if (!in_array('certificate_sent', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `certificate_sent` TINYINT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.11.3', '<='))
		{
			if (!in_array('refunded', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `refunded` TINYINT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.12.0', '<='))
		{
			if (!in_array('tax_rate', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `tax_rate` DECIMAL( 10, 2 ) NULL DEFAULT  '0';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.12.3', '<='))
		{
			if (!in_array('formatted_invoice_number', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `formatted_invoice_number` VARCHAR( 255 ) NULL;";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('first_sms_reminder_sent', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `first_sms_reminder_sent` TINYINT(4) NOT NULL DEFAULT 0;";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('second_sms_reminder_sent', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_registrants` ADD  `second_sms_reminder_sent` TINYINT(4) NOT NULL DEFAULT 0;";
				$db->setQuery($sql)
					->execute();
			}
		}


		//Ticket Types table
		$fields = array_keys($db->getTableColumns('#__eb_ticket_types'));

		// Older version to 3.8.4
		if (version_compare($this->installedVersion, '3.8.3', '<='))
		{
			if (!in_array('publish_up', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('publish_down', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('ordering', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `ordering` INT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();

				$sql = 'UPDATE `#__eb_ticket_types` SET `ordering` = `id`';
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.12.3', '<='))
		{
			if (!in_array('discount_rules', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `discount_rules` TEXT NULL;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.15.2', '<='))
		{
			if (!in_array('access', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `access` INT NOT NULL DEFAULT '1';";
				$db->setQuery($sql)
					->execute();
			}
		}

		$fields = array_keys($db->getTableColumns('#__eb_discounts'));

		if (version_compare($this->installedVersion, '3.12.3', '<='))
		{
			if (!in_array('number_events', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_discounts` ADD  `number_events` INT NOT NULL DEFAULT  '0' ;";
				$db->setQuery($sql)
					->execute();
			}
		}

		// Coupons table
		$fields = array_keys($db->getTableColumns('#__eb_coupons'));

		if (version_compare($this->installedVersion, '3.8.5', '<='))
		{
			if (!in_array('min_number_registrants', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_coupons` ADD  `min_number_registrants` INT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('max_number_registrants', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_coupons` ADD  `max_number_registrants` INT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('note', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_coupons` ADD  `note` VARCHAR( 50 ) NULL DEFAULT  NULL;";
				$db->setQuery($sql)
					->execute();
			}
		}

		// Fields table
		$fields = array_keys($db->getTableColumns('#__eb_fields'));

		if (version_compare($this->installedVersion, '3.9.0', '<='))
		{
			if (!in_array('show_on_registration_type', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_fields` ADD  `show_on_registration_type` TINYINT NOT NULL DEFAULT '0';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.10.3', '<='))
		{
			if (!in_array('populate_from_previous_registration', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_fields` ADD  `populate_from_previous_registration` TINYINT NOT NULL DEFAULT '1';";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (version_compare($this->installedVersion, '3.10.4', '<='))
		{
			if (!in_array('taxable', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_fields` ADD `taxable`  tinyint(3) UNSIGNED DEFAULT 1;";
				$db->setQuery($sql)
					->execute();
			}

			if (!in_array('position', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_fields` ADD `position` tinyint(3) UNSIGNED DEFAULT 0;";
				$db->setQuery($sql)
					->execute();
			}
		}

		// Ticket types table
		$fields = array_keys($db->getTableColumns('#__eb_ticket_types'));

		if (version_compare($this->installedVersion, '3.10.1', '<='))
		{
			if (!in_array('weight', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `weight` INT NOT NULL DEFAULT '1';";
				$db->setQuery($sql)
					->execute();
			}
		}

		// Change default value for datetime fields
		if (version_compare($this->installedVersion, '3.15.0', '<='))
		{
			$fieldsToChange = [
				'#__eb_coupons'      => ['valid_from', 'valid_to'],
				'#__eb_discounts'    => ['from_date', 'to_date'],
				'#__eb_emails'       => ['sent_at'],
				'#__eb_events'       => ['event_date', 'event_end_date', 'cut_off_date', 'early_bird_discount_date', 'cancel_before_date', 'recurring_end_date', 'registration_start_date', 'max_end_date', 'late_fee_date'],
				'#__eb_registrants'  => ['register_date', 'payment_date', 'checked_in_at', 'checked_out_at'],
				'#__eb_ticket_types' => ['publish_up', 'publish_down'],
			];

			try
			{
				$query    = $db->getQuery(true);
				$nullDate = $db->getNullDate();

				foreach ($fieldsToChange as $table => $fields)
				{
					foreach ($fields as $field)
					{
						$sql = "ALTER TABLE $table CHANGE $field $field datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
						$db->setQuery($sql)
							->execute();

						$query->clear()
							->update($table)
							->set($field . '=' . $db->quote($nullDate))
							->where($field . ' IS NULL');
						$db->setQuery($query)
							->execute();
					}
				}
			}
			catch (Exception $e)
			{

			}
		}

		if (version_compare($this->installedVersion, '3.15.1', '<='))
		{
			$sql = "ALTER TABLE  `#__eb_events` MODIFY  `thumb` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		// Ticket types table
		$fields = array_keys($db->getTableColumns('#__eb_urls'));

		if (version_compare($this->installedVersion, '3.16.2', '<='))
		{
			if (!in_array('route', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_urls` ADD  `route` VARCHAR( 400 ) NULL DEFAULT  NULL;";
				$db->setQuery($sql)
					->execute();
			}
		}

		// Discounts table
		$fields = array_keys($db->getTableColumns('#__eb_discounts'));

		if (version_compare($this->installedVersion, '3.16.2', '<='))
		{
			if (!in_array('discount_type', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_discounts` ADD  `discount_type` TINYINT(4) NOT NULL DEFAULT 1;";
				$db->setQuery($sql)
					->execute();
			}
		}
	}


	/**
	 * Insert additional default data on upgrade
	 */
	private function insertAdditionalDefaultData()
	{
		JLoader::register('RADConfig', JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/config/config.php');
		JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

		$db = Factory::getDbo();

		// Setup menus
		$sqlFile = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/menus.eventbooking.sql';
		EventbookingHelper::executeSqlFile($sqlFile);

		// Custom admin menus
		$sqlFile = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/custommenus.eventbooking.sql';

		if (file_exists($sqlFile))
		{
			EventbookingHelper::executeSqlFile($sqlFile);
		}

		$message             = EventbookingHelper::getMessages();
		$possibleNewMessages = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/updates/messages.php';
		$query               = $db->getQuery(true);

		foreach ($possibleNewMessages as $key => $value)
		{
			if (!isset($message->{$key}))
			{
				$query->clear()
					->insert('#__eb_messages')
					->columns($db->quoteName(['id', 'message_key', 'message']))
					->values(implode(',', $db->quote([0, $key, $value])));
				$db->setQuery($query)
					->execute();
			}
		}

		$config          = EventbookingHelper::getConfig();
		$possibleConfigs = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/updates/configs.php';
		$query           = $db->getQuery(true);

		foreach ($possibleConfigs as $key => $value)
		{
			if (!isset($config->{$key}))
			{
				$query->clear()
					->insert('#__eb_configs')
					->columns($db->quoteName(['id', 'config_key', 'config_value']))
					->values(implode(',', $db->quote([0, $key, $value])));
				$db->setQuery($query)
					->execute();
			}
		}

		// Countries, States database
		$query->clear()
			->select('COUNT(*)')
			->from('#__eb_countries');
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			EventbookingHelper::executeSqlFile(JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/countries_states.sql');
		}
	}

	/**
	 * Enable Events Booking - System plugin
	 */
	private function enableRequiredPlugin()
	{
		$db = Factory::getDbo();

		// Events Booking - System Plugin
		$query = $db->getQuery(true)
			->update('#__extensions')
			->set('enabled = 1')
			->where('element = "system"')
			->where('folder = "eventbooking"');
		$db->setQuery($query)
			->execute();


		// Installer - Events Booking plugin
		$query->clear()
			->update('#__extensions')
			->set('enabled = 1')
			->where('element = "eventbooking"')
			->where('folder = "installer"');
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Get installed version of the component
	 *
	 * @return void
	 */
	private function getInstalledVersion()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('manifest_cache')
			->from('#__extensions')
			->where($db->quoteName('element') . ' = "com_eventbooking"')
			->where($db->quoteName('type') . ' = "component"');
		$db->setQuery($query);
		$manifestCache = $db->loadResult();

		if ($manifestCache)
		{
			$manifest               = json_decode($manifestCache);
			$this->installedVersion = $manifest->version;
		}
	}
}