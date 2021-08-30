<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class EventbookingControllerUpdate extends RADController
{
	/**
	 * Update database schema when users update from old version to 1.6.4.
	 * We need to implement this function outside the installation script to avoid timeout during upgrade
	 */
	public function update()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		// Create table if not exists
		$tableSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/createifnotexists.eventbooking.sql';

		EventbookingHelper::executeSqlFile($tableSql);

		// Setup Menus
		$sqlFile = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/menus.eventbooking.sql';
		EventbookingHelper::executeSqlFile($sqlFile);

		###Setup default configuration data
		$sql = 'SELECT COUNT(*) FROM #__eb_configs';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$configSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/config.eventbooking.sql';

			EventbookingHelper::executeSqlFile($configSql);
		}

		$sql = 'SELECT COUNT(*) FROM #__eb_themes';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			// Insert default themes
			$themesSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/themes.eventbooking.sql';

			EventbookingHelper::executeSqlFile($themesSql);
		}

		$config = EventbookingHelper::getConfig();

		if ($config->map_api_key == 'AIzaSyDIq19TVV4qOX2sDBxQofrWfjeA7pebqy4')
		{
			$sql = 'UPDATE #__eb_configs SET config_value="" WHERE config_key="map_api_key"';
			$db->setQuery($sql)
				->execute();
		}

		// Publish the necessary plugin based on cb_integration config option value in older version
		if (!empty($config->cb_integration))
		{
			$plugin = '';

			switch ($config->cb_integration)
			{
				case '1':
					$plugin = 'cb';
					break;
				case '2':
					$plugin = 'jomsocial';
					break;
				case '3':
					$plugin = 'membershippro';
					break;
				case '4':
					$plugin = 'userprofile';
					break;
				case '5':
					$plugin = 'contactenhanced';
					break;
			}

			$query->update('#__extensions')
				->set('`enabled`= 1')
				->where('`element`=' . $db->quote($plugin))
				->where('`folder`="eventbooking"');
			$db->setQuery($query)
				->execute();

			$query->clear()
				->delete('#__eb_configs')
				->where('config_key = ' . $db->quote('cb_integration'));
			$db->setQuery($query)
				->execute();
		}

		//Set up default payment plugins table
		$sql = 'SELECT COUNT(*) FROM #__eb_payment_plugins';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$configSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/plugins.eventbooking.sql';

			EventbookingHelper::executeSqlFile($configSql);
		}

		$fields = array_keys($db->getTableColumns('#__eb_speakers'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_speakers` ADD  `ordering` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE `#__eb_speakers` SET `ordering` = `id`';
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eb_sponsors'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_sponsors` ADD  `ordering` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE `#__eb_sponsors` SET `ordering` = `id`';
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eb_agendas'));

		if (!in_array('ordering', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_agendas` ADD  `ordering` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE `#__eb_agendas` SET `ordering` = `id`';
			$db->setQuery($sql)
				->execute();
		}

		// Add access field for payment plugin
		$sql = 'ALTER TABLE  `#__eb_urls` CHANGE  `md5_key` `md5_key`  VARCHAR(32) DEFAULT NULL;';
		$db->setQuery($sql)
			->execute();

		$fields = array_keys($db->getTableColumns('#__eb_urls'));

		if (!in_array('view', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_urls` ADD  `view` VARCHAR( 15 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('record_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_urls` ADD  `record_id` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('route', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_urls` ADD  `route` VARCHAR( 400 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		// Add access field for payment plugin
		$fields = array_keys($db->getTableColumns('#__eb_payment_plugins'));

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_payment_plugins` ADD  `access` INT NOT NULL DEFAULT  '1';";
			$db->setQuery($sql)
				->execute();
		}

		// Update author email to tuanpn@joomdoantion.com as contact@joomdonation.com is not available anymore
		$sql = 'UPDATE #__eb_payment_plugins SET author_email="tuanpn@joomdonation.com" WHERE author_email="contact@joomdonation.com"';
		$db->setQuery($sql)
			->execute();

		// Countries and states management
		$fields = array_keys($db->getTableColumns('#__eb_countries'));

		if (!in_array('id', $fields))
		{
			//Change the name of the name of column from country_id to ID
			$sql = 'ALTER TABLE `#__eb_countries` CHANGE `country_id` `id` INT(11) NOT NULL AUTO_INCREMENT;';
			$db->setQuery($sql)
				->execute();

			//Add country ID column back for BC
			$sql = "ALTER TABLE  `#__eb_countries` ADD  `country_id` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			//Set country_id value the same with id
			$sql = 'UPDATE #__eb_countries SET country_id=id';
			$db->setQuery($sql)
				->execute();

		}

		// Countries and states management
		$fields = array_keys($db->getTableColumns('#__eb_coupons'));

		if (!in_array('max_usage_per_user', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `max_usage_per_user` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('category_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `category_id` INT(11) NOT NULL DEFAULT '-1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `user_id` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('apply_to', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `apply_to` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('enable_for', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `enable_for` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `access` INT(11) NOT NULL DEFAULT '1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('used_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_coupons` ADD  `used_amount` DECIMAL( 10, 2 ) NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eb_states'));

		if (!in_array('id', $fields))
		{
			//Change the name of the name of column from country_id to ID
			$sql = 'ALTER TABLE `#__eb_states` CHANGE `state_id` `id` INT(11) NOT NULL AUTO_INCREMENT;';
			$db->setQuery($sql)
				->execute();

			//Add state ID column back for BC
			$sql = "ALTER TABLE  `#__eb_states` ADD  `state_id` INT(11) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			//Set country_id value the same with id
			$sql = 'UPDATE #__eb_states SET state_id=id';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('published', $fields))
		{
			$db->setQuery("ALTER TABLE `#__eb_states` ADD `published` TINYINT( 4 ) NOT NULL DEFAULT '1'")
				->execute();
		}

		//Change field type of some fields
		$sql = 'ALTER TABLE  `#__eb_events` CHANGE  `short_description`  `short_description` MEDIUMTEXT  NULL DEFAULT NULL';
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_events` CHANGE  `discount`  `discount` DECIMAL( 10, 2 ) NULL DEFAULT  '0'";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_locations` CHANGE  `lat`  `lat` DECIMAL( 10, 6 ) NULL DEFAULT '0'";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_locations` CHANGE  `long`  `long` DECIMAL( 10, 6 ) NULL DEFAULT '0'";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_coupons` CHANGE  `valid_from`  `valid_from` DATETIME NULL";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_coupons` CHANGE  `valid_to`  `valid_to` DATETIME NULL";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_coupons` CHANGE `used` `used` INT( 11 ) NULL DEFAULT  '0'";
		$db->setQuery($sql)
			->execute();

		$sql = 'UPDATE #__eb_coupons SET `used` = 0 WHERE `used` IS NULL';
		$db->setQuery($sql)
			->execute();

		$sql = 'ALTER TABLE  `#__eb_fields` CHANGE  `description`  `description` MEDIUMTEXT  NULL DEFAULT NULL';
		$db->setQuery($sql)
			->execute();
		##Locations table

		$fields = array_keys($db->getTableColumns('#__eb_locations'));

		if (!in_array('user_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_locations` ADD  `user_id` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_locations` ADD  `language` VARCHAR( 50 ) NULL DEFAULT  '*';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('layout', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_locations` ADD  `layout` VARCHAR( 50 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('image', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_locations` ADD  `image` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('description', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_locations` ADD  `description` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('alias', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_locations` ADD  `alias` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();

			// Generate alias for existing locations from title
			$sql = 'SELECT id, name, alias FROM #__eb_locations';
			$db->setQuery($sql);
			$rowLocations = $db->loadObjectList();

			$generatedAlias = [];

			foreach ($rowLocations as $rowLocation)
			{
				$locationAlias = ApplicationHelper::stringURLSafe($rowLocation->name);

				if (in_array($locationAlias, $generatedAlias))
				{
					$locationAlias = $rowLocation->id . '-' . $locationAlias;
				}

				$generatedAlias[] = $locationAlias;

				$sql = 'UPDATE #__eb_locations SET alias = ' . $db->quote($locationAlias) . ' WHERE id = ' . $rowLocation->id;
				$db->setQuery($sql)
					->execute();
			}
		}

		$fields = array_keys($db->getTableColumns('#__eb_configs'));

		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_configs` ADD  `language` VARCHAR( 50 ) NULL DEFAULT  '*';";
			$db->setQuery($sql)
				->execute();
		}

		//Joomla default language
		$defaultLanguage = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		$sql             = 'SELECT COUNT(*) FROM #__eb_configs WHERE language="' . $defaultLanguage . '"';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'UPDATE #__eb_configs SET language="' . $defaultLanguage . '" WHERE language="*"';
			$db->setQuery($sql)
				->execute();
		}
		else
		{
			//Delete the old one
			$sql = 'DELETE FROM #__eb_configs WHERE language="*"';
			$db->setQuery($sql)
				->execute();
		}
		###Custom fields table
		$fields = array_keys($db->getTableColumns('#__eb_fields'));

		if (!in_array('filterable', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `filterable` TINYINT NOT NULL DEFAULT '0'";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hide_for_first_group_member', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `hide_for_first_group_member` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('not_required_for_first_group_member', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `not_required_for_first_group_member` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('newsletter_field_mapping', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `newsletter_field_mapping` VARCHAR( 100 ) NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('server_validation_rules', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `server_validation_rules` VARCHAR( 255 ) NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `language` VARCHAR( 50 ) NULL DEFAULT  '*';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('datatype_validation', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `datatype_validation` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('discountable', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `discountable` TINYINT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('extra_attributes', $fields))
		{
			if (!in_array('extra', $fields))
			{
				$sql = "ALTER TABLE  `#__eb_fields` ADD  `extra_attributes` VARCHAR( 255 ) NULL;";
				$db->setQuery($sql)
					->execute();
			}
			else
			{
				$sql = "ALTER TABLE  `#__eb_fields` CHANGE `extra` `extra_attributes` VARCHAR( 255 ) NULL;";
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `access` INT NOT NULL DEFAULT  '1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_in_list_view', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `show_in_list_view` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_public_registrants_list', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `show_on_public_registrants_list` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			$customFields = [1, 2];

			if (trim($config->registrant_list_custom_field_ids))
			{
				$customFields = explode(',', trim($config->registrant_list_custom_field_ids));
			}

			$query = $db->getQuery(true)
				->select('custom_field_ids')
				->from('#__eb_events')
				->where('LENGTH(custom_field_ids) > 0');
			$db->setQuery($query);

			try
			{
				$eventFields = $db->loadColumn();
			}
			catch (Exception $e)
			{
				$eventFields = [];
			}

			foreach ($eventFields as $eventField)
			{
				if (trim($eventField))
				{
					$customFields = array_merge($customFields, explode(',', $eventField));
				}
			}

			$customFields = array_filter(\Joomla\Utilities\ArrayHelper::toInteger($customFields));

			if (count($customFields))
			{
				$query->clear()
					->update('#__eb_fields')
					->set('show_on_public_registrants_list = 1')
					->where('id IN (' . implode(',', $customFields) . ')');
				$db->setQuery($query)
					->execute();
			}
		}

		if (!in_array('depend_on_field_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `depend_on_field_id` INT NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('depend_on_options', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `depend_on_options` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('max_length', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `max_length` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('place_holder', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD   `place_holder` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('multiple', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `multiple` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('validation_rules', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `validation_rules` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('validation_error_message', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `validation_error_message` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		// Quantity field
		if (!in_array('quantity_field', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `quantity_field` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('quantity_values', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `quantity_values` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('only_show_for_first_member', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `only_show_for_first_member` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('only_require_for_first_member', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `only_require_for_first_member` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hide_on_email', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `hide_on_email` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hide_on_export', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `hide_on_export` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('show_on_registrants', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `show_on_registrants` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('receive_confirmation_email', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `receive_confirmation_email` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		//Events table
		$fields = array_keys($db->getTableColumns('#__eb_events'));

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

		if (!in_array('waiting_list_capacity', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD `waiting_list_capacity` tinyint(3) UNSIGNED DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('enable_sms_reminder', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD `enable_sms_reminder` tinyint(3) UNSIGNED DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('from_name', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `from_name` VARCHAR( 100 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('from_email', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `from_email` VARCHAR( 100 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('send_first_reminder', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `send_first_reminder` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_events SET send_first_reminder = remind_before_x_days WHERE enable_auto_reminder = 1';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('send_second_reminder', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `send_second_reminder` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('second_reminder_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `second_reminder_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('group_member_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `group_member_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('free_event_registration_status', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `free_event_registration_status` TINYINT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('members_discount_apply_for', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `members_discount_apply_for` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('send_emails', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `send_emails` TINYINT NOT NULL DEFAULT  '-1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('page_title', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `page_title` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('page_heading', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `page_heading` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('collect_member_information', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `collect_member_information` CHAR(1) NOT NULL DEFAULT '';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('prevent_duplicate_registration', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `prevent_duplicate_registration` CHAR(1) NOT NULL DEFAULT ''";
			$db->setQuery($sql)
				->execute();
		}

		$moveEventsImages = false;

		if (!in_array('image', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `image` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();

			$moveEventsImages = true;
		}

		if (!in_array('featured', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `featured` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('has_multiple_ticket_types', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `has_multiple_ticket_types` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		// Discounts
		if (!in_array('discount_groups', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `discount_groups` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
			$discountGroups = EventbookingHelper::getConfigValue('member_discount_groups');

			if ($discountGroups)
			{
				$sql = 'UPDATE #__eb_events SET discount_groups=' . $db->quote($discountGroups);
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('discount_amounts', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `discount_amounts` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE `#__eb_events` SET `discount_amounts` = `discount` WHERE `discount` > 0';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('event_end_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `event_end_date` DATETIME NULL AFTER  `event_date` ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_start_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_start_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('publish_up', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
		}
		else
		{
			$sql = "ALTER TABLE  `#__eb_events` CHANGE  `publish_up` `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
		}

		$db->setQuery($sql)
			->execute();

		if (!in_array('publish_down', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
		}
		else
		{
			$sql = "ALTER TABLE  `#__eb_events` CHANGE  `publish_down` `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';";
		}

		$db->setQuery($sql)
			->execute();

		if (!in_array('max_end_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `max_end_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00';";
			$db->setQuery($sql)
				->execute();

			$sql = 'SELECT DISTINCT parent_id FROM #__eb_events WHERE parent_id > 0';
			$db->setQuery($sql);
			$parentIds = $db->loadColumn();
			$nullDate  = $db->getNullDate();

			foreach ($parentIds as $parentId)
			{
				$sql = 'SELECT MAX(event_date) AS max_event_date, MAX(cut_off_date) AS max_cut_off_date FROM #__eb_events WHERE published = 1 AND parent_id = ' . $parentId;
				$db->setQuery($sql);
				$maxDateInfo  = $db->loadObject();
				$maxEventDate = $maxDateInfo->max_event_date;

				if ($maxDateInfo->max_cut_off_date != $nullDate)
				{
					$oMaxEventDate  = new DateTime($maxDateInfo->max_event_date);
					$oMaxCutOffDate = new DateTime($maxDateInfo->max_cut_off_date);

					if ($oMaxCutOffDate > $oMaxEventDate)
					{
						$maxEventDate = $maxDateInfo->max_cut_off_date;
					}
				}

				$sql = 'UPDATE #__eb_events SET max_end_date = ' . $db->quote($maxEventDate) . ' WHERE id = ' . $parentId;
				$db->setQuery($sql)
					->execute();
				$db->execute();
			}
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `access` INT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('activate_tickets_pdf', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `activate_tickets_pdf` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_start_number', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_start_number` INT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_prefix', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_prefix` VARCHAR(10) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_bg_image', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_bg_image` VARCHAR(255) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_bg_top', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_bg_top` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_bg_left', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_bg_left` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_bg_width', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_bg_width` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_bg_height', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_bg_height` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_layout', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `ticket_layout` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('invoice_format', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `invoice_format` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_access` INT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('max_group_number', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `max_group_number` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('min_group_number', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `min_group_number` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('paypal_email', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `paypal_email` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_handle_url', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_handle_url` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('api_login', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `api_login` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('transaction_key', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `transaction_key` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('fixed_group_price', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `fixed_group_price` DECIMAL( 10, 2 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('paypal_email', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `paypal_email` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('attachment', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `attachment` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();

			//Need to create com_eventbooking folder under media folder
			if (!Folder::exists(JPATH_ROOT . '/media/com_eventbooking'))
			{
				Folder::create(JPATH_ROOT . '/media/com_eventbooking');
			}
		}

		if (!in_array('notification_emails', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `notification_emails` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_form_message', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_form_message` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_form_message_group', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_form_message_group` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `user_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_email_body_offline', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `user_email_body_offline` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('hits', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `hits`  INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('thanks_message', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `thanks_message` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('thanks_message_offline', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `thanks_message_offline` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		//Adding some new fields for supporting recurring events
		if (!in_array('enable_cancel_registration', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `enable_cancel_registration` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('cancel_before_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `cancel_before_date` DATETIME NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('early_bird_discount_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `early_bird_discount_type` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('early_bird_discount_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `early_bird_discount_date` DATETIME NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('early_bird_discount_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `early_bird_discount_amount` DECIMAL( 10, 2 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		// Late Fee date
		if (!in_array('late_fee_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `late_fee_type` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('late_fee_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `late_fee_date` DATETIME NULL DEFAULT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('late_fee_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `late_fee_amount` DECIMAL( 10, 2 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('parent_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `parent_id` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_additional_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `is_additional_date` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('created_by', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `created_by` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('event_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `event_type` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('recurring_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `recurring_type` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('recurring_frequency', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `recurring_frequency` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('article_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `article_id` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('weekdays', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `weekdays` VARCHAR( 50 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('monthdays', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `monthdays` VARCHAR( 50 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('recurring_end_date', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `recurring_end_date` DATETIME NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('recurring_occurrencies', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `recurring_occurrencies` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('recurring_occurrencies', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `recurring_occurrencies` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('custom_fields', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `custom_fields` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		#Support deposit payment
		if (!in_array('deposit_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `deposit_type` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('deposit_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `deposit_amount` DECIMAL( 10, 2 ) NULL DEFAULT '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_type` TINYINT NOT NULL DEFAULT  '0' AFTER  `enable_group_registration` ;";
			$db->setQuery($sql)
				->execute();
			$updateDb = true;
		}
		else
		{
			$updateDb = false;
		}

		if ($updateDb)
		{
			$sql = 'UPDATE #__eb_events SET registration_type = 1 WHERE enable_group_registration = 0';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('custom_field_ids', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `custom_field_ids` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('event_password', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `event_password` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		#Support Payment method based on event
		if (!in_array('payment_methods', $fields))
		{
			$sql = "ALTER TABLE `#__eb_events` ADD `payment_methods` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('currency_code', $fields))
		{
			$sql = "ALTER TABLE `#__eb_events` ADD `currency_code` VARCHAR( 10 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('currency_symbol', $fields))
		{
			$sql = "ALTER TABLE `#__eb_events` ADD `currency_symbol` VARCHAR( 20 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		//Thumb image for event
		if (!in_array('thumb', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `thumb` VARCHAR(60) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_approved_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD `registration_approved_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('fixed_daylight_saving_time', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `fixed_daylight_saving_time`  TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}
		/**
		 * Add support for multilingual
		 */
		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `language` VARCHAR( 50 ) NULL DEFAULT  '*';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_events SET `language`="*" ';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('meta_keywords', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `meta_keywords` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('meta_description', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `meta_description` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('reminder_email_body', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `reminder_email_body` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('enable_coupon', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `enable_coupon` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();

			if ($config->enable_coupon == 1)
			{
				$sql = 'UPDATE #__eb_events SET enable_coupon=3';
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('alias', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `alias` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql);
			$db->execute();
			$sql = 'SELECT id, parent_id, title, event_date FROM #__eb_events';
			$db->setQuery($sql);
			$rowEvents = $db->loadObjectList();

			foreach ($rowEvents as $rowEvent)
			{
				if ($rowEvent->parent_id > 0)
				{
					$alias = JApplication::stringURLSafe(
						$rowEvent->title . '-' . HTMLHelper::_('date', $rowEvent->event_date, $config->date_format, null));
				}
				else
				{
					$alias = JApplication::stringURLSafe($rowEvent->title);
				}
				//Check to see if this alias existing or not. If the alias exist, we will append id of the event at the beginning
				$sql = 'SELECT COUNT(*) FROM #__eb_events WHERE alias=' . $db->quote($alias);
				$db->setQuery($sql);
				$total = $db->loadResult();

				if ($total)
				{
					$alias = $rowEvent->id . '-' . $alias;
				}

				$sql = 'UPDATE #__eb_events SET `alias`=' . $db->quote($alias) . ' WHERE id=' . $rowEvent->id;
				$db->setQuery($sql)
					->execute();
				$db->execute();
			}
		}

		if (!in_array('tax_rate', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `tax_rate` DECIMAL( 10, 2 ) NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			//Set tax rate for the plan from configuration
			$taxRate = (float) $config->tax_rate;

			if ($taxRate > 0)
			{
				$sql = 'UPDATE #__eb_events SET tax_rate=' . $taxRate;
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('activate_waiting_list', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `activate_waiting_list` TINYINT NOT NULL DEFAULT  '2' ;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_events SET activate_waiting_list = 2';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('price_text', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `price_text` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('activate_certificate_feature', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `activate_certificate_feature` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('certificate_layout', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `certificate_layout` TEXT NULL;";
			$db->setQuery($sql)
				->execute();

			$query = $db->getQuery(true);
			$query->insert('#__eb_configs')
				->columns('config_key, config_value')
				->values('"activate_certificate_feature", 0')
				->values('"certificate_prefix", "CT"')
				->values('"certificate_number_length", 5');
			$db->setQuery($query)
				->execute();
		}

		if (!in_array('certificate_bg_image', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `certificate_bg_image` VARCHAR(255) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('certificate_bg_left', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `certificate_bg_left` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('certificate_bg_top', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `certificate_bg_top` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('certificate_bg_width', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `certificate_bg_width` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('certificate_bg_height', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `certificate_bg_height` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (empty($config->certificate_layout))
		{
			//Need to insert default data into the system
			$invoiceFormat = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/certificate_layout.html');

			if (property_exists($config, 'certificate_layout'))
			{
				$sql = 'UPDATE #__eb_configs SET config_value = ' . $db->quote($invoiceFormat) . ' WHERE config_key="certificate_layout"';
			}
			else
			{
				$sql = 'INSERT INTO #__eb_configs(config_key, config_value) VALUES ("certificate_layout", ' . $db->quote($invoiceFormat) . ')';
			}

			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('enable_terms_and_conditions', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `enable_terms_and_conditions` TINYINT NOT NULL DEFAULT  '2' ;";
			$db->setQuery($sql)
				->execute();
		}

		//The Categories table
		$fields = array_keys($db->getTableColumns('#__eb_categories'));

		if (!in_array('page_title', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `page_title` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('page_heading', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `page_heading` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('meta_keywords', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `meta_keywords` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('meta_description', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `meta_description` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('image', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `image` VARCHAR( 250 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `access` INT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('submit_event_access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `submit_event_access` INT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('color_code', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `color_code` VARCHAR( 20 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('text_color', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `text_color` VARCHAR( 20 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `language` VARCHAR( 50 ) NULL DEFAULT  '*';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('alias', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `alias` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql)
				->execute();
			$sql = 'SELECT id, name FROM #__eb_categories';
			$db->setQuery($sql);
			$rowCategories = $db->loadObjectList();

			foreach ($rowCategories as $rowCategory)
			{
				$alias = JApplication::stringURLSafe($rowCategory->name);
				$sql   = 'UPDATE #__eb_categories SET `alias`=' . $db->quote($alias) . ' WHERE id=' . $rowCategory->id;
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('level', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_categories` ADD  `level` TINYINT( 4 ) NOT NULL DEFAULT '1';";
			$db->setQuery($sql)
				->execute();

			// Update level for categories
			$query = $db->getQuery(true);
			$query->select('id, `parent`');
			$query->from('#__eb_categories');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$children = [];

			foreach ($rows as $v)
			{
				$pt   = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}

			$list = EventbookingHelper::calculateCategoriesLevel(0, [], $children, 4);

			foreach ($list as $id => $category)
			{
				$sql = "UPDATE #__eb_categories SET `level` = $category->level WHERE id = $id";
				$db->setQuery($sql)
					->execute();
			}

		}

		$fields = array_keys($db->getTableColumns('#__eb_ticket_types'));

		if (!in_array('access', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `access` INT NOT NULL DEFAULT '1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('discount_rules', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `discount_rules` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('parent_ticket_type_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `parent_ticket_type_id` INT(11) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		//Registrants table
		$fields = array_keys($db->getTableColumns('#__eb_registrants'));

		if (!in_array('tax_rate', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `tax_rate` DECIMAL( 10, 2 ) NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('refunded', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `refunded` TINYINT NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('invoice_year', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `invoice_year` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants SET `invoice_year` = YEAR(`register_date`)';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('coupon_usage_restored', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `coupon_usage_restored` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('checked_in_at', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `checked_in_at` DATETIME DEFAULT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('checked_out_at', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `checked_out_at` DATETIME DEFAULT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('subscribe_newsletter', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `subscribe_newsletter` TINYINT NOT NULL DEFAULT  '1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('agree_privacy_policy', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `agree_privacy_policy` TINYINT NOT NULL DEFAULT  '1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('coupon_usage_times', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `coupon_usage_times` INT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('auto_coupon_coupon_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `auto_coupon_coupon_id` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('deposit_payment_processing_fee', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `deposit_payment_processing_fee` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `payment_amount` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_currency', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `payment_currency` VARCHAR( 15 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('total_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `total_amount` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `discount_amount` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants  SET total_amount=`amount`';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_processing_fee', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `payment_processing_fee` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('coupon_discount_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `coupon_discount_amount` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('late_fee', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `late_fee` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('cart_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `cart_id`  INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('notified', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `notified`  TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('checked_in', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `checked_in`  TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('coupon_usage_calculated', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `coupon_usage_calculated`  TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants SET coupon_usage_calculated = 1';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('checked_in_count', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `checked_in_count`  TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants SET checked_in_count = number_registrants WHERE checked_in = 1';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('deposit_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD `deposit_amount` DECIMAL( 10, 2 ) NULL DEFAULT '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('payment_status', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `payment_status`  TINYINT NOT NULL DEFAULT  '1' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('coupon_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `coupon_id`  INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('check_coupon', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `check_coupon`  TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('tax_amount', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `tax_amount` DECIMAL( 10, 6 ) NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		$sql = "ALTER TABLE `#__eb_registrants` CHANGE `tax_amount` `tax_amount` DECIMAL(10,2) NULL DEFAULT '0.00';";
		$db->setQuery($sql)
			->execute();

		if (!in_array('registration_code', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `registration_code` VARCHAR( 15 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('params', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `params` TEXT NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `is_reminder_sent` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_second_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `is_second_reminder_sent` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_deposit_payment_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `is_deposit_payment_reminder_sent` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('process_deposit_payment', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD `process_deposit_payment` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('deposit_payment_transaction_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD `deposit_payment_transaction_id` VARCHAR( 100 ) NULL;;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('user_ip', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD `user_ip` VARCHAR( 100 ) NULL;;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('deposit_payment_method', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD `deposit_payment_method` VARCHAR( 100 ) NULL;;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_group_billing', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `is_group_billing` TINYINT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			//Update all other records
			$sql = 'SELECT DISTINCT group_id FROM #__eb_registrants WHERE group_id > 0';
			$db->setQuery($sql);
			$groupIds = $db->loadColumn();

			if (count($groupIds))
			{
				$sql = 'UPDATE #__eb_registrants SET is_group_billing=1 WHERE id IN (' . implode(',', $groupIds) . ') OR number_registrants > 1';
				$db->setQuery($sql)
					->execute();

				//Need to update the published field
				$sql = 'SELECT id, payment_method, transaction_id, published FROM #__eb_registrants WHERE id IN (' .
					implode(',', $groupIds) . ') OR number_registrants > 1';
				$db->setQuery($sql);
				$rowGroups = $db->loadObjectList();

				foreach ($rowGroups as $rowGroup)
				{
					$id            = $rowGroup->id;
					$paymentMethod = $rowGroup->payment_method;
					$transactionId = $rowGroup->transaction_id;
					$published     = $rowGroup->published;
					$sql           = "UPDATE  #__eb_registrants SET payment_method='$paymentMethod', transaction_id='$transactionId', published='$published', number_registrants=1 WHERE group_id=$id";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

		$sql = "ALTER TABLE  `#__eb_registrants` CHANGE  `group_id`  `group_id` INT( 11 ) NULL DEFAULT  '0';";
		$db->setQuery($sql)
			->execute();

		$sql = 'UPDATE #__eb_registrants SET group_id = 0 WHERE group_id IS NULL';
		$db->setQuery($sql)
			->execute();

		if (!in_array('language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `language` VARCHAR( 50 ) NULL DEFAULT  '*';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants SET `language`="*" ';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_number', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `ticket_number`  INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_code', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `ticket_code`  VARCHAR( 40 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('ticket_qrcode', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `ticket_qrcode`  VARCHAR( 40 ) NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('invoice_number', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `invoice_number` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			//Update membership Id field
			$sql = 'SELECT id FROM #__eb_registrants WHERE group_id=0 AND (published=1 OR payment_method LIKE "%os_offline%") ORDER BY id';
			$db->setQuery($sql);
			$rows = $db->loadObjectList();

			if (count($rows))
			{
				$start = 1;

				foreach ($rows as $row)
				{
					$sql = 'UPDATE #__eb_registrants SET invoice_number=' . $start . ' WHERE id=' . $row->id;
					$db->setQuery($sql)
						->execute();
					$start++;
				}
			}

			$query = $db->getQuery(true);
			$query->insert('#__eb_configs')
				->columns('config_key, config_value')
				->values('"activate_invoice_feature", 0')
				->values('"send_invoice_to_customer", 0')
				->values('"invoice_start_number", 1')
				->values('"invoice_prefix", "IV"')
				->values('"invoice_number_length", 5');
			$db->setQuery($query)
				->execute();
		}

		if (empty($config->invoice_format))
		{
			//Need to insert default data into the system
			$invoiceFormat = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/invoice_format.html');

			if (property_exists($config, 'invoice_format'))
			{
				$sql = 'UPDATE #__eb_configs SET config_value = ' . $db->quote($invoiceFormat) . ' WHERE config_key="invoice_format"';
			}
			else
			{
				$sql = 'INSERT INTO #__eb_configs(config_key, config_value) VALUES ("invoice_format", ' . $db->quote($invoiceFormat) . ')';
			}

			$db->setQuery($sql)
				->execute();
		}

		if (empty($config->invoice_format_cart))
		{
			$invoiceFormat = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/invoice_format_cart.html');

			if (property_exists($config, 'invoice_format_cart'))
			{
				$sql = 'UPDATE #__eb_configs SET config_value = ' . $db->quote($invoiceFormat) . ' WHERE config_key="invoice_format_cart"';
			}
			else
			{
				$sql = 'INSERT INTO #__eb_configs(config_key, config_value) VALUES ("invoice_format_cart", ' . $db->quote($invoiceFormat) . ')';
			}

			$db->setQuery($sql)
				->execute();
		}

		//Update to use event can be assigned to multiple categories feature
		$sql = 'SELECT COUNT(id) FROM #__eb_event_categories';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if ($total == 0)
		{
			$sql = 'INSERT INTO #__eb_event_categories(event_id, category_id)
				SELECT id, category_id FROM #__eb_events';
			$db->setQuery($sql)
				->execute();
		}
		//Field Events table
		$sql = 'SELECT COUNT(*) FROM #__eb_field_events';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'UPDATE #__eb_fields SET event_id = -1 WHERE event_id = 0';
			$db->setQuery($sql)
				->execute();
			$sql = 'INSERT INTO #__eb_field_events(field_id, event_id) SELECT id, event_id FROM #__eb_fields WHERE event_id != -1 ';
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eb_discounts'));

		if (!in_array('number_events', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_discounts` ADD  `number_events` INT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('discount_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_discounts` ADD  `discount_type` TINYINT(4) NOT NULL DEFAULT 1;";
			$db->setQuery($sql)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eb_event_categories'));

		if (!in_array('main_category', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_event_categories` ADD  `main_category` TINYINT NOT NULL DEFAULT  '0' ;";
			$db->setQuery($sql)
				->execute();
			$sql = 'SELECT * FROM #__eb_event_categories ORDER BY id DESC';
			$db->setQuery($sql);
			$rowEventCategories = $db->loadObjectList('event_id');

			foreach ($rowEventCategories as $rowEventCategory)
			{
				$sql = 'UPDATE #__eb_event_categories SET main_category=1 WHERE id=' . $rowEventCategory->id;
				$db->setQuery($sql);
				$db->execute();
			}
		}

		//Events table
		$fields = array_keys($db->getTableColumns('#__eb_events'));

		if (!in_array('main_category_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `main_category_id` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			$query = $db->getQuery(true)
				->update('#__eb_events AS a')
				->innerJoin('#__eb_event_categories as b ON (a.id = b.event_id AND b.main_category = 1)')
				->set('a.main_category_id = b.category_id');
			$db->setQuery($query)
				->execute();
		}

		$fields = array_keys($db->getTableColumns('#__eb_fields'));

		if (!in_array('pattern', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `pattern` VARCHAR( 255 ) NULL DEFAULT  NULL;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('min', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `min` INT NOT NULL DEFAULT '0'";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('max', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `max` INT NOT NULL DEFAULT '0'";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('step', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `step` INT NOT NULL DEFAULT '0'";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_searchable', $fields))
		{
			$sql = "ALTER TABLE `#__eb_fields` ADD `is_searchable` TINYINT NOT NULL DEFAULT '0' ";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_core', $fields))
		{
			$sql = "ALTER TABLE `#__eb_fields` ADD `is_core` TINYINT NOT NULL DEFAULT '0' ";
			$db->setQuery($sql)
				->execute();

			$sql = "ALTER TABLE  `#__eb_fields` ADD  `fieldtype` VARCHAR( 50 ) NULL;";
			$db->setQuery($sql)
				->execute();
			//Setup core fields
			$sql = 'UPDATE #__eb_fields SET id=id+13, ordering = ordering + 13 ORDER BY id DESC';
			$db->setQuery($sql)
				->execute();
			$sql = 'UPDATE #__eb_field_values SET field_id=field_id + 13';
			$db->setQuery($sql)
				->execute();
			$sql = 'UPDATE #__eb_field_events SET field_id=field_id + 13';
			$db->setQuery($sql)
				->execute();
			$coreFieldsSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/fields.eventbooking.sql';

			EventbookingHelper::executeSqlFile($coreFieldsSql);

			$sql = 'SELECT MAX(id) FROM #__eb_fields';
			$db->setQuery($sql);
			$maxId         = (int) $db->loadResult();
			$autoincrement = $maxId + 1;
			$sql           = 'ALTER TABLE #__eb_fields AUTO_INCREMENT=' . $autoincrement;
			$db->setQuery($sql)
				->execute();

			//Update field type , change it to something meaningful
			$typeMapping = [
				1 => 'Text',
				2 => 'Textarea',
				3 => 'List',
				5 => 'Checkboxes',
				6 => 'Radio',
				7 => 'Date',
				8 => 'Heading',
				9 => 'Message',];

			foreach ($typeMapping as $key => $value)
			{
				$sql = "UPDATE #__eb_fields SET fieldtype='$value' WHERE field_type='$key'";
				$db->setQuery($sql)
					->execute();
			}

			$sql = "UPDATE #__eb_fields SET fieldtype='List', multiple=1 WHERE field_type='4'";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_fields SET fieldtype="Countries" WHERE name="country"';
			$db->setQuery($sql)
				->execute();

			//MySql, convert data to Json
			$sql = 'SELECT id, field_value FROM #__eb_field_values WHERE field_id IN (SELECT id FROM #__eb_fields WHERE field_type=4 OR field_type=5)';
			$db->setQuery($sql);
			$rowFieldValues = $db->loadObjectList();

			foreach ($rowFieldValues as $rowFieldValue)
			{
				$fieldValue = $rowFieldValue->field_value;

				if (strpos($fieldValue, ',') !== false)
				{
					$fieldValue = explode(',', $fieldValue);
				}

				$fieldValue = json_encode($fieldValue);
				$sql        = 'UPDATE #__eb_field_values SET field_value=' . $db->quote($fieldValue) . ' WHERE id=' . $rowFieldValue->id;
				$db->setQuery($sql)
					->execute();
			}

			if ($config->display_state_dropdown)
			{
				$sql = 'UPDATE #__eb_fields SET fieldtype="State" WHERE name="state"';
				$db->setQuery($sql)
					->execute();
			}

			$sql = 'SELECT * FROM #__eb_events WHERE published =1 ORDER BY id DESC';
			$db->setQuery($sql);
			$event = $db->loadObject();

			if ($event)
			{
				$params = new Registry($event->params);
				$keys   = [
					's_lastname',
					'r_lastname',
					's_organization',
					'r_organization',
					's_address',
					'r_address',
					's_address2',
					'r_address2',
					's_city',
					'r_city',
					's_state',
					'r_state',
					's_zip',
					'r_zip',
					's_country',
					'r_country',
					's_phone',
					'r_phone',
					's_fax',
					'r_fax',
					's_comment',
					'r_comment',
					'gs_lastname',
					'gs_organization',
					'gs_address',
					'gs_address2',
					'gs_city',
					'gs_state',
					'gs_zip',
					'gs_country',
					'gs_phone',
					'gs_fax',
					'gs_email',
					'gs_comment',];

				foreach ($keys as $key)
				{
					$config->$key = $params->get($key, 0);
				}
			}

			//Process publish status of core fields
			$publishStatus = [
				'first_name'   => 1,
				'last_name'    => $config->s_lastname,
				'organization' => $config->s_organization,
				'address'      => $config->s_address,
				'address2'     => $config->s_address2,
				'city'         => $config->s_city,
				'state'        => $config->s_state,
				'zip'          => $config->s_zip,
				'country'      => $config->s_country,
				'phone'        => $config->s_phone,
				'fax'          => $config->s_fax,
				'comment'      => $config->s_comment,
				'email'        => 1,];

			foreach ($publishStatus as $key => $value)
			{
				$value = (int) $value;
				$sql   = 'UPDATE #__eb_fields SET published=' . $value . ' WHERE name=' . $db->quote($key);
				$db->setQuery($sql)
					->execute();
			}

			$requiredStatus = [
				'first_name'   => 1,
				'last_name'    => $config->r_lastname,
				'organization' => $config->r_organization,
				'address'      => $config->r_address,
				'address2'     => $config->r_address2,
				'city'         => $config->r_city,
				'state'        => $config->r_state,
				'zip'          => $config->r_zip,
				'country'      => $config->r_country,
				'phone'        => $config->r_phone,
				'fax'          => $config->r_fax,
				'comment'      => $config->r_comment,
				'email'        => 1,];

			foreach ($requiredStatus as $key => $value)
			{
				$value = (int) $value;
				$sql   = 'UPDATE #__eb_fields SET required=' . $value . ' WHERE name=' . $db->quote($key);
				$db->setQuery($sql)
					->execute();
			}

			//Now, we will need to change display settings for core fields
			$groupMemberFields = [
				'last_name'    => $config->gs_lastname,
				'organization' => $config->gs_organization,
				'address'      => $config->gs_address,
				'address2'     => $config->gs_address2,
				'city'         => $config->gs_city,
				'state'        => $config->gs_state,
				'zip'          => $config->gs_zip,
				'country'      => $config->gs_country,
				'phone'        => $config->gs_phone,
				'fax'          => $config->gs_fax,
				'comment'      => $config->gs_comment,];

			foreach ($groupMemberFields as $fieldName => $showed)
			{
				$showed = (int) $showed;

				if ($showed)
				{
					$displayIn = 0;
				}
				else
				{
					$displayIn = 3;
				}

				$sql = "UPDATE #__eb_fields SET display_in=" . $db->quote($displayIn) . ' WHERE name=' . $db->quote($fieldName);
				$db->setQuery($sql)
					->execute();
			}
		}

		if (!in_array('category_id', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `category_id` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			//Migrate fields mapping data
			$sql = 'UPDATE #__eb_fields SET category_id=0 WHERE event_id=-1';
			$db->setQuery($sql)
				->execute();

			$sql = 'SELECT id FROM #__eb_fields WHERE event_id != - 1';
			$db->setQuery($sql);
			$rowFields = $db->loadObjectList();

			foreach ($rowFields as $rowField)
			{
				//Get the event which this custom field is assigned to
				$sql = 'SELECT event_id FROM #__eb_field_events WHERE field_id=' . $rowField->id . ' ORDER BY id DESC LIMIT 1';
				$db->setQuery($sql);
				$eventId = (int) $db->loadResult();

				if ($eventId)
				{
					//Get main category
					$sql = 'SELECT category_id FROM #__eb_event_categories WHERE event_id=' . $eventId .
						' AND main_category=1';
					$db->setQuery($sql);
					$categoryId = (int) $db->loadResult();

					if ($categoryId)
					{
						$sql = 'UPDATE #__eb_fields SET category_id=' . $categoryId . ' WHERE id=' . $rowField->id;
						$db->setQuery($sql)
							->execute();
					}
					else
					{
						//This field is not assigned to any events, just unpublish it
						$sql = 'UPDATE #__eb_fields SET published=0 WHERE id=' . $rowField->id;
						$db->setQuery($sql)
							->execute();
					}
				}
				else
				{
					//This field is not assigned to any events, just unpublish it
					$sql = 'UPDATE #__eb_fields SET published=0 WHERE id=' . $rowField->id;
					$db->setQuery($sql)
						->execute();
				}
			}
		}

		$sql = "SELECT id, validation_rules FROM #__eb_fields WHERE required = 1";
		$db->setQuery($sql);
		$fields = $db->loadObjectList();

		foreach ($fields as $field)
		{
			if (empty($field->validation_rules))
			{
				$sql = 'UPDATE #__eb_fields SET validation_rules = "validate[required]" WHERE id=' . $field->id;
				$db->setQuery($sql)
					->execute();
			}
		}
		//Make sure validation is empty when required=0
		$sql = 'UPDATE #__eb_fields SET validation_rules = "" WHERE required=0 AND validation_rules="validate[required]"';
		$db->setQuery($sql)
			->execute();

		//Add show price for free event config option
		$sql = 'SELECT COUNT(id) FROM #__eb_configs WHERE config_key="show_price_for_free_event"';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'INSERT INTO #__eb_configs(config_key, config_value) VALUES("show_price_for_free_event", 1)';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SELECT COUNT(*) FROM #__eb_messages';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$row  = new RADTable('#__eb_messages', 'id', $db);
			$keys = [
				'admin_email_subject',
				'admin_email_body',
				'user_email_subject',
				'user_email_body',
				'user_email_body_offline',
				'registration_form_message',
				'registration_form_message_group',
				'number_members_form_message',
				'member_information_form_message',
				'confirmation_message',
				'thanks_message',
				'thanks_message_offline',
				'cancel_message',
				'registration_cancel_message_free',
				'registration_cancel_message_paid',
				'invitation_form_message',
				'invitation_email_subject',
				'invitation_email_body',
				'invitation_complete',
				'reminder_email_subject',
				'reminder_email_body',
				'registration_cancel_email_subject',
				'registration_cancel_email_body',
				'registration_approved_email_subject',
				'registration_approved_email_body',
				'waitinglist_form_message',
				'waitinglist_complete_message',
				'watinglist_confirmation_subject',
				'watinglist_confirmation_body',
				'watinglist_notification_subject',
				'watinglist_notification_body',];

			foreach ($keys as $key)
			{
				$row->id          = 0;
				$row->message_key = $key;
				$row->message     = $config->{$key};
				$row->store();
			}
		}

		//Update ACL field, from 1.4.1 and before to 1.4.2
		$sql = 'UPDATE #__eb_categories SET `access` = 1 WHERE `access` = 0';
		$db->setQuery($sql)
			->execute();

		$sql = 'UPDATE #__eb_events SET `access` = 1 WHERE `access` = 0';
		$db->setQuery($sql)
			->execute();

		$sql = 'UPDATE #__eb_events SET `registration_access` = 1 WHERE `registration_access` = 0';
		$db->setQuery($sql)
			->execute();

		//Update SEF setting
		$sql = 'SELECT COUNT(*) FROM #__eb_configs WHERE config_key="insert_event_id"';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = "INSERT INTO #__eb_configs(config_key, config_value) VALUES('insert_event_id', '0') ";
			$db->setQuery($sql)
				->execute();

			$sql = "INSERT INTO #__eb_configs(config_key, config_value) VALUES('insert_category', '0') ";
			$db->setQuery($sql)
				->execute();
		}

		try
		{
			// Migrate waiting list data
			$sql = 'SELECT COUNT(*) FROM #__eb_waiting_lists';
			$db->setQuery($sql);
			$total = $db->loadResult();

			if ($total)
			{
				$sql = "INSERT INTO #__eb_registrants(
				user_id, event_id, first_name, last_name, organization, address, address2, city,
		 		state, country, zip, phone, fax, email, number_registrants, register_date, notified, published
			)
		 	SELECT user_id, event_id, first_name, last_name, organization, address, address2, city,
		 	state, country, zip, phone, fax, email, number_registrants, register_date, notified, 3
		 	FROM #__eb_waiting_lists ORDER BY id
		 	";
				$db->setQuery($sql)
					->execute();
			}

			$db->truncateTable('#__eb_waiting_lists');
		}
		catch (Exception $e)
		{
			// Do-nothing
		}

		// Update old links from older version to 2.0.x
		$query = $db->getQuery(true);
		$query->update('#__menu')
			->set($db->quoteName('link') . '=' . $db->quote('index.php?option=com_eventbooking&view=locations'))
			->where($db->quoteName('link') . '=' . $db->quote('index.php?option=com_eventbooking&view=locationlist'));
		$db->setQuery($query)
			->execute();

		$query->clear()
			->update('#__menu')
			->set($db->quoteName('link') . '=' . $db->quote('index.php?option=com_eventbooking&view=location&layout=form'))
			->where($db->quoteName('link') . '=' . $db->quote('index.php?option=com_eventbooking&view=addlocation'));
		$db->setQuery($query)
			->execute();

		$sql = 'SELECT COUNT(*) FROM #__eb_field_categories';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'UPDATE #__eb_fields SET category_id = -1 WHERE category_id = 0';
			$db->setQuery($sql)
				->execute();
			$sql = 'INSERT INTO #__eb_field_categories(field_id, category_id) SELECT id, category_id FROM #__eb_field_categories WHERE category_id != -1 ';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SELECT COUNT(*) FROM #__eb_coupon_events';
		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'UPDATE #__eb_coupons SET event_id = -1 WHERE event_id = 0';
			$db->setQuery($sql)
				->execute();
			$sql = 'INSERT INTO #__eb_coupon_events(coupon_id, event_id) SELECT id, event_id FROM #__eb_coupons WHERE event_id != -1 ';
			$db->setQuery($sql)
				->execute();
		}

		// Publish necessary plugin when updating from older version to 2.2.0
		if ($config->cb_integration)
		{
			$plugin = '';

			if ($config->cb_integration == 1)
			{
				$plugin = 'cb';
			}

			if ($config->cb_integration == 2)
			{
				$plugin = 'jomsocial';
			}

			if ($config->cb_integration == 3)
			{
				$plugin = 'membershippro';
			}

			if ($config->cb_integration == 4)
			{
				$plugin = 'userprofile';
			}

			if ($config->cb_integration == 5)
			{
				$plugin = 'contactenhanced';
			}

			if ($plugin)
			{
				$query->clear();
				$query->update('#__extensions')
					->set('`enabled`= 1')
					->where('`element`=' . $db->quote($plugin))
					->where('`folder`="eventbooking"');
				$db->setQuery($query)
					->execute();
			}
		}

		// Uninstall the old plugins which is not needed from version 2.9.0
		$installer = new JInstaller();

		$plugins = [
			['eventbooking', 'cartupdate'],
			['eventbooking', 'invoice'],
			['eventbooking', 'unpublishevents'],
		];

		$query = $db->getQuery(true);
		foreach ($plugins as $plugin)
		{
			$query->clear()
				->select('extension_id')
				->from('#__extensions')
				->where($db->quoteName('folder') . ' = ' . $db->quote($plugin[0]))
				->where($db->quoteName('element') . ' = ' . $db->quote($plugin[1]));
			$db->setQuery($query);
			$id = $db->loadResult();
			if ($id)
			{
				try
				{
					$installer->uninstall('plugin', $id, 0);
				}
				catch (\Exception $e)
				{

				}
			}
		}

		// Make sure the Events Booking - System plugin always published
		$query->clear()
			->update('#__extensions')
			->set('enabled = 1')
			->where('element = "system"')
			->where('folder = "eventbooking"');
		$db->setQuery($query)
			->execute();

		$query->clear()
			->update('#__extensions')
			->set('enabled = 1')
			->where('element = "eventbooking"')
			->where('folder = "installer"');
		$db->setQuery($query)
			->execute();

		if (File::exists(JPATH_ADMINISTRATOR . '/manifests/packages/pkg_eventbooking.xml'))
		{
			// Insert update site
			$tmpInstaller = new JInstaller;
			$tmpInstaller->setPath('source', JPATH_ADMINISTRATOR . '/manifests/packages');
			$file     = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_eventbooking.xml';
			$manifest = $tmpInstaller->isManifest($file);

			if (!is_null($manifest))
			{
				$query = $db->getQuery(true)
					->select($db->quoteName('extension_id'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('name') . ' = ' . $db->quote($manifest->name))
					->where($db->quoteName('type') . ' = ' . $db->quote($manifest['type']))
					->where($db->quoteName('state') . ' != -1');
				$db->setQuery($query);

				$eid = (int) $db->loadResult();

				if ($eid && $manifest->updateservers)
				{
					// Set the manifest object and path
					$tmpInstaller->manifest = $manifest;
					$tmpInstaller->setPath('manifest', $file);

					// Load the extension plugin (if not loaded yet).
					PluginHelper::importPlugin('extension', 'joomla');

					// Fire the onExtensionAfterUpdate
					Factory::getApplication()->triggerEvent('onExtensionAfterUpdate', ['installer' => $tmpInstaller, 'eid' => $eid]);
				}
			}
		}

		// Migrate currency code from plugin param to configuration
		if (empty($config->currency_code))
		{
			$query = $db->getQuery(true);
			$query->select('name, params')
				->from('#__eb_payment_plugins')
				->where('published = 1');
			$db->setQuery($query);
			$plugins = $db->loadObjectList('name');

			if (isset($plugins['os_paypal']))
			{
				$params       = new Registry($plugins['os_paypal']->params);
				$currencyCode = $params->get('paypal_currency', 'USD');
			}
			elseif (isset($plugins['os_paypal_pro']))
			{
				$params       = new Registry($plugins['os_paypal_pro']->params);
				$currencyCode = $params->get('paypal_pro_currency', 'USD');
			}
			elseif ($plugins['os_payflowpro'])
			{
				$params       = new Registry($plugins['os_payflowpro']->params);
				$currencyCode = $params->get('payflow_currency', 'USD');
			}
			else
			{
				$currencyCode = 'USD';
			}

			$query->clear()
				->delete('#__eb_configs')
				->where('config_key = "currency_code"');
			$db->setQuery($query)
				->execute();

			$query->clear()
				->insert('#__eb_configs')
				->columns('config_key, config_value')
				->values('"currency_code", "' . $currencyCode . '"');
			$db->setQuery($query);
			$db->execute();
		}

		if (Multilanguage::isEnabled())
		{
			EventbookingHelper::callOverridableHelperMethod('Helper', 'setupMultilingual');
		}

		//Migrating permissions name, fixing bugs causes by Joomla 3.5.0
		$asset = Table::getInstance('asset');
		$asset->loadByName('com_eventbooking');

		if ($asset)
		{
			$rules        = $asset->rules;
			$rules        = str_replace('eventbooking.registrants_management', 'eventbooking.registrantsmanagement', $rules);
			$rules        = str_replace('eventbooking.view_registrants_list', 'eventbooking.viewregistrantslist', $rules);
			$asset->rules = $rules;
			$asset->store();
		}

		// Convert depend_on_options data to json instead of comma separated
		$query->clear()
			->select('*')
			->from('#__eb_fields')
			->where('depend_on_field_id > 0');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		if (Multilanguage::isEnabled())
		{
			$languages = EventbookingHelper::getLanguages();
		}

		foreach ($rowFields as $rowField)
		{
			$dependOnOptions = $rowField->depend_on_options;

			// If it is converted before, simply ignore all other fields
			if (is_string($dependOnOptions) && is_array(json_decode($dependOnOptions)))
			{
				break;
			}

			$dependOnOptions = json_encode(explode(',', $dependOnOptions));

			$query->clear()
				->update('#__eb_fields')
				->set('depend_on_options = ' . $db->quote($dependOnOptions))
				->where('id = ' . $rowField->id);

			if (!empty($languages))
			{
				foreach ($languages as $language)
				{
					$prefix          = $language->sef;
					$dependOnOptions = $rowField->{'depend_on_options_' . $prefix};
					$dependOnOptions = json_encode(explode(',', $dependOnOptions));
					$query->set('depend_on_options_' . $prefix . ' = ' . $db->quote($dependOnOptions));
				}
			}

			$db->execute();
		}

		// Insert deposit payment related messages
		$query->clear()
			->select('COUNT(*)')
			->from('#__eb_messages')
			->where('message_key = "deposit_payment_form_message"');
		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total)
		{
			$depositMessagesSql = JPATH_ADMINISTRATOR . '/components/com_eventbooking/sql/deposit.eventbooking.sql';

			EventbookingHelper::executeSqlFile($depositMessagesSql);
		}

		// Migrate speakers, sponsors data to new schema
		$query->clear()
			->select('COUNT(*)')
			->from('#__eb_event_speakers');
		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'INSERT INTO #__eb_event_speakers(event_id, speaker_id) SELECT event_id, id FROM #__eb_speakers';
			$db->setQuery($sql)
				->execute();
		}

		$query->clear()
			->select('COUNT(*)')
			->from('#__eb_event_sponsors');
		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total)
		{
			$sql = 'INSERT INTO #__eb_event_sponsors(event_id, sponsor_id) SELECT event_id, id FROM #__eb_sponsors';
			$db->setQuery($sql)
				->execute();
		}

		# Add index to improve the speed
		$sql = 'SHOW INDEX FROM #__eb_urls';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('md5_key', $fields))
		{
			$sql = 'CREATE INDEX `idx_md5_key` ON `#__eb_urls` (`md5_key`(32));';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_categories';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('parent', $fields))
		{
			$sql = 'CREATE INDEX `idx_parent` ON `#__eb_categories` (`parent`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = 'CREATE INDEX `idx_access` ON `#__eb_categories` (`access`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = 'CREATE INDEX `idx_published` ON `#__eb_categories` (`published`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('alias', $fields))
		{
			$sql = 'CREATE INDEX `idx_alias` ON `#__eb_categories` (`alias`(191));';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_events';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('category_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_category_id` ON `#__eb_events` (`category_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('location_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_location_id` ON `#__eb_events` (`location_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('parent_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_parent_id` ON `#__eb_events` (`parent_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('access', $fields))
		{
			$sql = 'CREATE INDEX `idx_access` ON `#__eb_events` (`access`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = 'CREATE INDEX `idx_published` ON `#__eb_events` (`published`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('event_date', $fields))
		{
			$sql = 'CREATE INDEX `idx_event_date` ON `#__eb_events` (`event_date`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('event_end_date', $fields))
		{
			$sql = 'CREATE INDEX `idx_event_end_date` ON `#__eb_events` (`event_end_date`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('alias', $fields))
		{
			$sql = 'CREATE INDEX `idx_alias` ON `#__eb_events` (`alias`(191));';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_registrants';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('event_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_event_id` ON `#__eb_registrants` (`event_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = 'CREATE INDEX `idx_published` ON `#__eb_registrants` (`published`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('first_name', $fields))
		{
			$sql = 'CREATE INDEX `idx_first_name` ON `#__eb_registrants` (`first_name`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('last_name', $fields))
		{
			$sql = 'CREATE INDEX `idx_last_name` ON `#__eb_registrants` (`last_name`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('email', $fields))
		{
			$sql = 'CREATE INDEX `idx_email` ON `#__eb_registrants` (`email`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('transaction_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_transaction_id` ON `#__eb_registrants` (`transaction_id`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_fields';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('access', $fields))
		{
			$sql = 'CREATE INDEX `idx_access` ON `#__eb_fields` (`access`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('published', $fields))
		{
			$sql = 'CREATE INDEX `idx_published` ON `#__eb_fields` (`published`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_field_values';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('registrant_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_registrant_id` ON `#__eb_field_values` (`registrant_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('field_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_field_id` ON `#__eb_field_values` (`field_id`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_field_events';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('field_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_field_id` ON `#__eb_field_events` (`field_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('event_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_event_id` ON `#__eb_field_events` (`event_id`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_field_categories';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('field_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_field_id` ON `#__eb_field_categories` (`field_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('category_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_category_id` ON `#__eb_field_categories` (`category_id`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_coupons';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('code', $fields))
		{
			$sql = 'CREATE INDEX `idx_code` ON `#__eb_coupons` (`code`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_coupon_events';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('event_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_event_id` ON `#__eb_coupon_events` (`event_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('coupon_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_coupon_id` ON `#__eb_coupon_events` (`coupon_id`);';
			$db->setQuery($sql)
				->execute();
		}

		$sql = 'SHOW INDEX FROM #__eb_event_categories';
		$db->setQuery($sql);
		$rows   = $db->loadObjectList();
		$fields = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row      = $rows[$i];
			$fields[] = $row->Column_name;
		}

		if (!in_array('event_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_event_id` ON `#__eb_event_categories` (`event_id`);';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('category_id', $fields))
		{
			$sql = 'CREATE INDEX `idx_category_id` ON `#__eb_event_categories` (`category_id`);';
			$db->setQuery($sql)
				->execute();
		}

		// Fix possible issue with categories data
		$sql = 'UPDATE #__eb_categories SET `parent` = 0 WHERE `parent` = `id`';
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_categories` CHANGE  `access` `access`  INT(11) NOT NULL DEFAULT '1'";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_categories` CHANGE  `submit_event_access` `submit_event_access`  INT(11) NOT NULL DEFAULT '1'";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_events` CHANGE  `access` `access`  INT(11) NOT NULL DEFAULT '1'";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_events` CHANGE  `registration_access` `registration_access`  INT(11) NOT NULL DEFAULT '1'";
		$db->setQuery($sql)
			->execute();

		// Update db schema, direct copied from update script

		//Registrants table
		$fields = array_keys($db->getTableColumns('#__eb_registrants'));

		if (!in_array('invoice_year', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `invoice_year` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants SET `invoice_year` = YEAR(`register_date`)';
			$db->setQuery($sql)
				->execute();
		}

		//Ticket Types table
		$fields = array_keys($db->getTableColumns('#__eb_ticket_types'));


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

		$fields = array_keys($db->getTableColumns('#__eb_fields'));

		if (!in_array('show_on_registration_type', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `show_on_registration_type` TINYINT NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		// Files, Folders clean up
		$deleteFiles = [
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/daylightsaving.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/controller/daylightsaving.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/controller.php',
			JPATH_ROOT . '/components/com_eventbooking/controller.php',
			JPATH_ROOT . '/components/com_eventbooking/helper/os_cart.php',
			JPATH_ROOT . '/components/com_eventbooking/helper/fields.php',
			JPATH_ROOT . '/components/com_eventbooking/helper/captcha.php',
			JPATH_ROOT . '/components/com_eventbooking/views/register/tmpl/group_member.php',
			JPATH_ROOT . '/components/com_eventbooking/views/waitinglist/tmpl/complete.php',
			JPATH_ROOT . '/components/com_eventbooking/models/waitinglist.php',
			JPATH_ROOT . '/components/com_eventbooking/ipn_logs.txt',
			JPATH_ROOT . '/modules/mod_eb_events/css/font.css',
			JPATH_ROOT . '/media/com_eventbooking/assets/css/themes/ocean.css',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/waitings.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/waiting.php',
			JPATH_ROOT . '/media/com_eventbooking/.htaccess',
			JPATH_ROOT . '/components/com_eventbooking/view/registrantlist/tmpl/default.mobile.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/categories/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/configuration/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/registrants/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/states/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/fields/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/message/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/plugins/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/countries/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/coupons/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/events/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/locations/tmpl/default.joomla3.php',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.json',
			// Layout files, removed from 3.7.0
			JPATH_ROOT . '/components/com_eventbooking/view/archive/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/archive/tmpl/table.php',
			JPATH_ROOT . '/components/com_eventbooking/view/calendar/tmpl/daily.php',
			JPATH_ROOT . '/components/com_eventbooking/view/calendar/tmpl/mini.php',
			JPATH_ROOT . '/components/com_eventbooking/view/calendar/tmpl/weekly.php',
			JPATH_ROOT . '/components/com_eventbooking/view/calendar/tmpl/table.php',
			JPATH_ROOT . '/components/com_eventbooking/view/calendar/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/cancel/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/cart/tmpl/default.mobile.php',
			JPATH_ROOT . '/components/com_eventbooking/view/cart/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/cart/tmpl/mini.mobile.php',
			JPATH_ROOT . '/components/com_eventbooking/view/cart/tmpl/mini.php',
			JPATH_ROOT . '/components/com_eventbooking/view/cart/tmpl/module.php',
			JPATH_ROOT . '/components/com_eventbooking/view/categories/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/categories/tmpl/events.php',
			JPATH_ROOT . '/components/com_eventbooking/view/category/tmpl/columns.php',
			JPATH_ROOT . '/components/com_eventbooking/view/category/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/category/tmpl/table.php',
			JPATH_ROOT . '/components/com_eventbooking/view/category/tmpl/timeline.php',
			JPATH_ROOT . '/components/com_eventbooking/view/complete/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_agendas.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_group_rates.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_location.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_plugins.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_share.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_social_buttons.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_speakers.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/default_sponsors.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form_discount_settings.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form_fields.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form_general.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form_group_rates.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form_misc.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/form_recurring_settings.php',
			JPATH_ROOT . '/components/com_eventbooking/view/event/tmpl/simple.php',
			JPATH_ROOT . '/components/com_eventbooking/view/events/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/events/tmpl/default_search_bar.bootstrap4.php',
			JPATH_ROOT . '/components/com_eventbooking/view/events/tmpl/default_search_bar.php',
			JPATH_ROOT . '/components/com_eventbooking/view/failure/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/fullcalendar/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/history/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/history/tmpl/default_search_bar.bootstrap4.php',
			JPATH_ROOT . '/components/com_eventbooking/view/history/tmpl/default_search_bar.php',
			JPATH_ROOT . '/components/com_eventbooking/view/invite/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/invite/tmpl/complete.php',
			JPATH_ROOT . '/components/com_eventbooking/view/location/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/location/tmpl/form.php',
			JPATH_ROOT . '/components/com_eventbooking/view/location/tmpl/popup.php',
			JPATH_ROOT . '/components/com_eventbooking/view/location/tmpl/table.php',
			JPATH_ROOT . '/components/com_eventbooking/view/location/tmpl/timeline.php',
			JPATH_ROOT . '/components/com_eventbooking/view/locations/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/massmail/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/password/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl/complete.php',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl/payment_amounts.php',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl/payment_javascript.php',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl/payment_methods.php',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl/registration.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/cart.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/cart_items.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/default_tickets.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/group.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/group_billing.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/group_members.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/number_members.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/register_gdpr.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/register_login.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/register_payment_amount.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/register_payment_methods.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/register_terms_and_conditions.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/register_user_registration.php',
			JPATH_ROOT . '/components/com_eventbooking/view/register/tmpl/tickets_members.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrant/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrantlist/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrants/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrants/tmpl/default_search_bar.bootstrap4.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrants/tmpl/default_search_bar.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrationcancel/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/registrationcancel/tmpl/confirmation.php',
			JPATH_ROOT . '/components/com_eventbooking/view/search/tmpl/columns.php',
			JPATH_ROOT . '/components/com_eventbooking/view/search/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/search/tmpl/table.php',
			JPATH_ROOT . '/components/com_eventbooking/view/search/tmpl/timeline.php',
			JPATH_ROOT . '/components/com_eventbooking/view/upcomingevents/tmpl/columns.php',
			JPATH_ROOT . '/components/com_eventbooking/view/upcomingevents/tmpl/default.php',
			JPATH_ROOT . '/components/com_eventbooking/view/upcomingevents/tmpl/table.php',
			JPATH_ROOT . '/components/com_eventbooking/view/upcomingevents/tmpl/timeline.php',
			JPATH_ROOT . '/components/com_eventbooking/view/waitinglist/tmpl/default.php',
		];

		$deleteFolders = [
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/PHPOffice',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/Respect',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/assets/chosen',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/models',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/views',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/daylightsaving',
			JPATH_ROOT . '/components/com_eventbooking/views/confirmation',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/waiting',
			JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/waitings',
			JPATH_ROOT . '/components/com_eventbooking/models',
			JPATH_ROOT . '/components/com_eventbooking/assets',
			JPATH_ROOT . '/components/com_eventbooking/views',
			JPATH_ROOT . '/components/com_eventbooking/view/common',
			JPATH_ROOT . '/components/com_eventbooking/view/cancel/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/failure/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/invite/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/password/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/payment/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/registrant/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/registrationcancel/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/waitinglist/tmpl',
			JPATH_ROOT . '/components/com_eventbooking/view/emailtemplates',
			JPATH_ROOT . '/components/com_eventbooking/view/users',
			JPATH_ROOT . '/modules/mod_eb_events/css/font',
		];

		foreach ($deleteFiles as $file)
		{
			if (File::exists($file))
			{
				File::delete($file);
			}
		}

		foreach ($deleteFolders as $folder)
		{
			if (Folder::exists($folder))
			{
				Folder::delete($folder);
			}
		}

		//Registrants table
		$fields = array_keys($db->getTableColumns('#__eb_events'));

		if (!in_array('hidden', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD `hidden`  TINYINT(4) NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('registration_complete_url', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `registration_complete_url` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('offline_payment_registration_complete_url', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD  `offline_payment_registration_complete_url` TEXT NULL ;";
			$db->setQuery($sql)
				->execute();
		}

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

		if (!in_array('created_language', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_events` ADD `created_language` VARCHAR(50) DEFAULT '*';";
			$db->setQuery($sql)
				->execute();
		}

		//Registrants table
		$fields = array_keys($db->getTableColumns('#__eb_registrants'));

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

		if (!in_array('invoice_year', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `invoice_year` INT NOT NULL DEFAULT  '0';";
			$db->setQuery($sql)
				->execute();

			$sql = 'UPDATE #__eb_registrants SET `invoice_year` = YEAR(`register_date`)';
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('is_offline_payment_reminder_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `is_offline_payment_reminder_sent` TINYINT NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('certificate_sent', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `certificate_sent` TINYINT NOT NULL DEFAULT '0';";
			$db->setQuery($sql)
				->execute();
		}

		//Ticket Types table
		$fields = array_keys($db->getTableColumns('#__eb_ticket_types'));

		// Older version to 3.8.4
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

		// Coupons table
		$fields = array_keys($db->getTableColumns('#__eb_coupons'));

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

		// Ticket types table
		$fields = array_keys($db->getTableColumns('#__eb_ticket_types'));

		if (!in_array('weight', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_ticket_types` ADD  `weight` INT NOT NULL DEFAULT '1';";
			$db->setQuery($sql)
				->execute();
		}

		// Custom Fields table
		$fields = array_keys($db->getTableColumns('#__eb_fields'));

		if (!in_array('populate_from_previous_registration', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD  `populate_from_previous_registration` TINYINT NOT NULL DEFAULT '1';";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('taxable', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD `taxable` tinyint(3) UNSIGNED DEFAULT 1;";
			$db->setQuery($sql)
				->execute();
		}

		if (!in_array('position', $fields))
		{
			$sql = "ALTER TABLE  `#__eb_fields` ADD `position`  tinyint(3) UNSIGNED DEFAULT 0;";
			$db->setQuery($sql)
				->execute();
		}

		// Redirect to dashboard view
		$installType = $this->input->getCmd('install_type', '');
		$app         = Factory::getApplication();

		if ($moveEventsImages)
		{
			$app->redirect('index.php?option=com_eventbooking&task=update.migrate_event_images&install_type=' . $installType);
		}
		else
		{
			if ($installType == 'install')
			{
				$msg = Text::_('The extension was successfully installed');
			}
			else
			{
				$msg = Text::_('The extension was successfully updated');
			}

			$app->enqueueMessage($msg);

			//Redirecting users to Dasboard
			$app->redirect('index.php?option=com_eventbooking&view=dashboard');
		}
	}

	/**
	 * Move events images from media folder to images folder to use media manage
	 *
	 * @throws Exception
	 */
	public function migrate_event_images()
	{
		$installType = $this->input->getCmd('install_type', '');

		if (!Folder::exists(JPATH_ROOT . '/images/com_eventbooking'))
		{
			Folder::create(JPATH_ROOT . '/images/com_eventbooking');
		}

		$db  = Factory::getDbo();
		$sql = 'SELECT thumb FROM #__eb_events WHERE thumb IS NOT NULL';
		$db->setQuery($sql);
		$thumbs = $db->loadColumn();

		if (count($thumbs))
		{
			$oldImagePath = JPATH_ROOT . '/media/com_eventbooking/images/';
			$newImagePath = JPATH_ROOT . '/images/com_eventbooking/';

			foreach ($thumbs as $thumb)
			{
				if ($thumb && file_exists($oldImagePath . $thumb))
				{
					File::copy($oldImagePath . $thumb, $newImagePath . $thumb);
				}
			}

			$sql = 'UPDATE #__eb_events SET `image` = CONCAT("images/com_eventbooking/", `thumb`) WHERE thumb IS NOT NULL';
			$db->setQuery($sql)
				->execute();
		}

		if ($installType == 'install')
		{
			$msg = Text::_('The extension was successfully installed');
		}
		else
		{
			$msg = Text::_('The extension was successfully updated');
		}

		$this->app->enqueueMessage($msg);

		//Redirecting users to Dasboard
		$this->app->redirect('index.php?option=com_eventbooking&view=dashboard');
	}
}