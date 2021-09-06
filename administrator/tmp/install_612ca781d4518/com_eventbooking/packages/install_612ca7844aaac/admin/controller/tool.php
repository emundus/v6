<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class EventbookingControllerTool extends RADController
{
	public function display_root_path()
	{
		if (Factory::getUser()->authorise('core.admin'))
		{
			echo JPATH_ROOT;
		}
		else
		{
			echo 'You do not have permission to view JPPATH_ROOT';
		}
	}

	public function delete_all_registrants()
	{
		$user = Factory::getUser();

		if (!$user->authorise('core.admin'))
		{
			throw new Exception('You do not have permission to delete all registrants');
		}

		$db = Factory::getDbo();
		$db->setQuery('TRUNCATE TABLE #__eb_field_values')
			->execute();

		$db->setQuery('TRUNCATE TABLE #__eb_registrants')
			->execute();
	}

	public function delete_orphans_registrant()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->delete('#__eb_registrants')
			->where('event_id NOT IN (SELECT id FROM #__eb_events)');
		$db->setQuery($query)
			->execute();
	}

	public function generate_formatted_invoice_number()
	{
		$config = EventbookingHelper::getConfig();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select('*')
			->from('#__eb_registrants')
			->where('invoice_number > 0');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$query->clear()
				->update('#__eb_registrants')
				->set('formatted_invoice_number = ' . $db->quote(EventbookingHelper::formatInvoiceNumber($row->invoice_number, $config, $row)))
				->where('id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}

		echo 'Successfully Generating Formatted Invoice Number';
	}

	public function download_and_set_font()
	{
		$font = $this->input->getString('font');

		$fontPackageUrl = 'https://joomdonation.com/tcpdf/fonts.zip';

		$fontFile = InstallerHelper::downloadPackage($fontPackageUrl, 'fonts.zip');

		if ($fontFile === false)
		{
			echo Text::_('The requested font could not be downloaded');

			return;
		}

		$tmpPath = $this->app->get('tmp_path');

		if (!Folder::exists($tmpPath))
		{
			$tmpPath = JPATH_ROOT . '/tmp';
		}

		$fontPackage = $tmpPath . '/fonts.zip';

		$extractDir = JPATH_ROOT . '/components/com_eventbooking/tcpdf/fonts';

		if (EventbookingHelper::isJoomla4())
		{
			$archive = new Joomla\Archive\Archive(['tmp_path' => $tmpPath]);
			$result  = $archive->extract($fontPackage, $extractDir);
		}
		else
		{
			$result = JArchive::extract($fontPackage, $extractDir);
		}

		if (!$result)
		{
			echo 'Error extract font package';

			return;
		}

		// Delete the downloaded zip file
		File::delete($fontPackage);

		if ($font)
		{
			// Now, set font to that font
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->update('#__eb_configs')
				->set('config_value = ' . $db->quote($font))
				->set('config_key = "pdf_font"');
			$db->setQuery($query)
				->execute();

			echo 'Font was successfully downloaded and set for PDF Font config option';
		}
		else
		{
			echo 'Fonts were successfully downloaded and extracted';
		}

	}

	public function remove_wrong_group_registration_records()
	{
		$cids    = [];
		$eventId = $this->input->getInt('event_id', 0);
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->select('id, number_registrants')
			->from('#__eb_registrants')
			->where('is_group_billing = 1')
			->where('(published = 1 OR payment_method LIKE "os_offline%")');

		if ($eventId > 0)
		{
			$query->where('event_id = ' . $eventId);
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__eb_registrants')
				->where('group_id = ' . $row->id);
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($row->number_registrants != $total)
			{
				$cids[] = $row->id;
			}
		}

		if (count($cids))
		{
			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel('Registrant');
			$model->delete($cids);
		}

		echo sprintf('Deleted %s registrations', count($cids));
	}

	public function remove_orphant_group_member_records()
	{
		$eventId = $this->input->getInt('event_id', 0);
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->delete('#__eb_registrants')
			->where('group_id > 0')
			->where('group_id NOT IN (SELECT id FROM #__eb_registrants WHERE is_group_billing = 1)');

		if ($eventId > 0)
		{
			$query->where('event_id = ' . $eventId);
		}

		$db->setQuery($query)
			->execute();

		echo $db->getAffectedRows();
	}

	public function find_missing_group_members_registration()
	{
		$eventId = $this->input->getInt('event_id', 0);
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->select('id, number_registrants')
			->from('#__eb_registrants')
			->where('is_group_billing = 1')
			->where('(published = 1 OR payment_method LIKE "os_offline%")');

		if ($eventId > 0)
		{
			$query->where('event_id = ' . $eventId);
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__eb_registrants')
				->where('group_id = ' . $row->id);
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($row->number_registrants != $total)
			{
				echo $row->id . '<br />';
			}
		}

		echo 'Done';
	}

	/**
	 * Reset the urls table
	 */
	public function reset_urls()
	{
		Factory::getDbo()->truncateTable('#__eb_urls');
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', Text::_('Urls have successfully reset'));
	}

	/**
	 * Setup multilingual fields
	 */
	public function setup_multilingual_fields()
	{
		EventbookingHelper::setupMultilingual();
	}

	/**
	 * Remove multilingual fields
	 */
	public function remove_multilingual()
	{
		$db = Factory::getDbo();

		$categoryTableFields = array_keys($db->getTableColumns('#__eb_categories'));
		$eventTableFields    = array_keys($db->getTableColumns('#__eb_events'));
		$fieldTableFields    = array_keys($db->getTableColumns('#__eb_fields'));
		$locationTableFields = array_keys($db->getTableColumns('#__eb_locations'));

		$suffixes = ['_fr', '_vi', '_pt', '_es-co', '_es', '_ms', '_ko', '_ja'];

		$fields = [
			'name',
			'alias',
			'page_title',
			'page_heading',
			'meta_keywords',
			'meta_description',
			'description',
		];

		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;


				if (in_array($fieldName, $categoryTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_categories` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

		$fields = [
			'title',
			'alias',
			'page_title',
			'page_heading',
			'meta_keywords',
			'meta_description',
			'price_text',
			'registration_handle_url',
			'short_description',
			'description',
			'registration_form_message',
			'registration_form_message_group',
			'admin_email_body',
			'user_email_body',
			'user_email_body_offline',
			'thanks_message',
			'thanks_message_offline',
			'registration_approved_email_body',
			'invoice_format',
			'ticket_layout',
		];


		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;

				if (in_array($fieldName, $eventTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_events` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}


		$fields = [
			'title',
			'description',
			'values',
			'default_values',
			'depend_on_options',
			'place_holder',
		];

		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;

				if (in_array($fieldName, $fieldTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_fields` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

		$fields = [
			'name',
			'alias',
			'description',
		];

		foreach ($fields as $field)
		{
			foreach ($suffixes as $suffix)
			{
				$fieldName = $field . $suffix;

				if (in_array($fieldName, $locationTableFields))
				{
					// Drop the field
					$sql = "ALTER TABLE  `#__eb_locations` DROP  `$fieldName`";
					$db->setQuery($sql)
						->execute();
				}
			}
		}

	}

	/**
	 * Add more decimal number to price related fields
	 */
	public function add_more_decimal_numbers()
	{
		$db = Factory::getDbo();

		$fieldsToChange = [
			'#__eb_events'             => ['individual_price', 'discount', 'early_bird_discount_amount', 'late_fee_amount', 'tax_rate'],
			'#__eb_event_group_prices' => ['price'],
			'#__eb_ticket_types'       => ['price'],
			'#__eb_coupons'            => ['discount'],
		];

		foreach ($fieldsToChange as $table => $fields)
		{
			$table = $db->quoteName($table);

			foreach ($fields as $field)
			{
				$field = $db->quoteName($field);
				$sql   = "ALTER TABLE  $table  CHANGE  $field $field  DECIMAL (15,8)";
				$db->setQuery($sql)
					->execute();
			}
		}

		echo 'Done';
	}

	/**
	 * Method to allow sharing language files for Events Booking
	 */
	public function share_translation()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('lang_code')
			->from('#__languages')
			->where('published = 1')
			->where('lang_code != "en-GB"')
			->order('ordering');
		$db->setQuery($query);
		$languages = $db->loadObjectList();

		if (count($languages))
		{
			$mailer   = Factory::getMailer();
			$jConfig  = Factory::getConfig();
			$mailFrom = $jConfig->get('mailfrom');
			$fromName = $jConfig->get('fromname');
			$mailer->setSender([$mailFrom, $fromName]);
			$mailer->addRecipient('tuanpn@joomdonation.com');
			$mailer->setSubject('Language Packages for Events Booking shared by ' . Uri::root());
			$mailer->setBody('Dear Tuan \n. I am happy to share my language packages for Events Booking.\n Enjoy!');
			foreach ($languages as $language)
			{
				$tag = $language->lang_code;
				if (file_exists(JPATH_ROOT . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini'))
				{
					$mailer->addAttachment(JPATH_ROOT . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini', $tag . '.com_eventbooking.ini');
				}

				if (file_exists(JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini'))
				{
					echo JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini';
					$mailer->addAttachment(JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_eventbooking.ini', 'admin.' . $tag . '.com_eventbooking.ini');
				}
			}

			require_once JPATH_COMPONENT . '/libraries/vendor/dbexporter/dumper.php';

			$tables = [$db->replacePrefix('#__eb_fields'), $db->replacePrefix('#__eb_messages')];

			try
			{

				$sqlFile = $tag . '.com_eventbooking.sql';
				$options = [
					'host'           => $jConfig->get('host'),
					'username'       => $jConfig->get('user'),
					'password'       => $jConfig->get('password'),
					'db_name'        => $jConfig->get('db'),
					'include_tables' => $tables,
				];
				$dumper  = Shuttle_Dumper::create($options);
				$dumper->dump(JPATH_ROOT . '/tmp/' . $sqlFile);

				$mailer->addAttachment(JPATH_ROOT . '/tmp/' . $sqlFile, $sqlFile);

			}
			catch (Exception $e)
			{
				//Do nothing
			}

			$mailer->Send();

			$msg = 'Thanks so much for sharing your language files to Events Booking Community';
		}
		else
		{
			$msg = 'Thanks so willing to share your language files to Events Booking Community. However, you don"t have any none English langauge file to share';
		}

		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', $msg);
	}

	/**
	 * Method to make a given field search and sortable easier
	 */
	public function make_field_search_sort_able()
	{
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$fieldId = $this->input->getInt('field_id');

		$query->select('*')
			->from('#__eb_fields')
			->where('id = ' . (int) $fieldId);
		$db->setQuery($query);
		$field = $db->loadObject();

		if (!$field)
		{
			throw new Exception('The field does not exist');
		}

		// Add new field to #__eb_registrants
		$fields = array_keys($db->getTableColumns('#__eb_registrants'));

		if (!in_array($field->name, $fields))
		{
			$sql = "ALTER TABLE  `#__eb_registrants` ADD  `$field->name` VARCHAR( 255 ) NULL;";
			$db->setQuery($sql);
			$db->execute();

			// Mark the field as searchable
			$query->clear()
				->update('#__eb_fields')
				->set('is_searchable = 1')
				->where('id = ' . (int) $fieldId);
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear()
			->select('*')
			->from('#__eb_field_values')
			->where('field_id = ' . $fieldId);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$fieldName = $db->quoteName($field->name);

		foreach ($rows as $row)
		{
			$query->clear()
				->update('#__eb_registrants')
				->set($fieldName . ' = ' . $db->quote($row->field_value))
				->where('id = ' . $row->registrant_id);
			$db->setQuery($query);
			$db->execute();
		}

		echo 'Done !';
	}

	/**
	 * Resize large event image to the given size
	 */
	public function resize_large_images()
	{
		$config = EventbookingHelper::getConfig();
		$width  = (int) $config->large_image_width ?: 800;
		$height = (int) $config->large_image_height ?: 600;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('image')
			->from('#__eb_events')
			->where('published = 1')
			->order('id DESC')
			->where('LENGTH(image) > 0');
		$db->setQuery($query);
		$images = $db->loadColumn();

		foreach ($images as $image)
		{
			$path = JPATH_ROOT . '/' . $image;

			if (!file_exists($path))
			{
				continue;
			}

			EventbookingHelper::resizeImage($path, $path, $width, $height);
		}
	}

	/**
	 * Resize large event image to the given size
	 */
	public function resize_thumb_images()
	{
		$config = EventbookingHelper::getConfig();
		$width  = (int) $config->thumb_width ?: 200;
		$height = (int) $config->thumb_height ?: 200;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('image')
			->from('#__eb_events')
			->where('published = 1')
			->order('id DESC')
			->where('LENGTH(image) > 0');
		$db->setQuery($query);
		$images = $db->loadColumn();

		foreach ($images as $image)
		{
			$path = JPATH_ROOT . '/' . $image;

			if (!file_exists($path))
			{
				continue;
			}

			$fileName  = basename($image);
			$thumbPath = JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName;

			EventbookingHelper::resizeImage($path, $thumbPath, $width, $height);
		}
	}


	/**
	 * Fix "Row size too large" issue
	 */
	public function fix_row_size()
	{
		$db = Factory::getDbo();

		$tables = [
			'#__eb_categories',
			'#__eb_events',
			'#__eb_fields',
			'#__eb_locations',
			'#__eb_registrants',
		];

		foreach ($tables as $table)
		{
			$query = "ALTER TABLE `$table` ROW_FORMAT = DYNAMIC";
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Method for finding menu linked to the extension
	 */
	public function find_menus()
	{
		$component = ComponentHelper::getComponent('com_eventbooking');
		$menus     = $this->app->getMenu('site');
		$items     = $menus->getItems('component_id', $component->id);
		?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Alias</th>
                <th>Link</th>
                <th>Menu</th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ($items as $item)
			{
				?>
                <tr>
                    <td>
						<?php echo $item->id; ?>
                    </td>
                    <td><?php echo $item->title; ?></td>
                    <td><?php echo $item->alias; ?></td>
                    <td><?php echo $item->link ?></td>
                    <td><?php echo $item->menutype; ?></td>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
		<?php
	}

	/**
	 * The second option to fix row size
	 */
	public function fix_row_size2()
	{
		$db        = Factory::getDbo();
		$languages = EventbookingHelper::getLanguages();

		if (count($languages))
		{
			$categoryTableFields = array_keys($db->getTableColumns('#__eb_categories'));
			$eventTableFields    = array_keys($db->getTableColumns('#__eb_events'));
			$fieldTableFields    = array_keys($db->getTableColumns('#__eb_fields'));
			$locationTableFields = array_keys($db->getTableColumns('#__eb_locations'));

			foreach ($languages as $language)
			{
				$prefix = $language->sef;

				$fields = [
					'name',
					'alias',
					'page_title',
					'page_heading',
					'meta_keywords',
					'meta_description',
					'description',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $categoryTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_categories` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_categories` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_categories'));
					}
				}


				$fields = [
					'title',
					'alias',
					'page_title',
					'page_heading',
					'meta_keywords',
					'meta_description',
					'price_text',
					'registration_handle_url',
					'short_description',
					'description',
					'registration_form_message',
					'registration_form_message_group',
					'user_email_body',
					'user_email_body_offline',
					'thanks_message',
					'thanks_message_offline',
					'registration_approved_email_body',
					'invoice_format',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $eventTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_events` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_events'));
					}
				}


				$fields = [
					'title',
					'description',
					'values',
					'default_values',
					'depend_on_options',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $fieldTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_fields` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_fields'));
					}
				}

				$fields = [
					'name',
					'alias',
					'description',
				];

				foreach ($fields as $field)
				{
					$fieldName = $field . '_' . $prefix;

					if (!in_array($fieldName, $locationTableFields))
					{
						$sql = "ALTER TABLE  `#__eb_locations` ADD  `$fieldName` TEXT NULL;";
					}
					else
					{
						$sql = "ALTER TABLE  `#__eb_locations` MODIFY  `$fieldName` TEXT NULL;";
					}

					$db->setQuery($sql);

					try
					{
						$db->execute();
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage(sprintf('Field %s already exist in table %s', $fieldName, '#__eb_locations'));
					}
				}
			}
		}
	}

	/**
	 * Method to create helper override
	 * https://domain.com/administrator/index.php?option=com_eventbooking&task=tool.create_helper_override&helper=registration
	 */
	public function create_helper_override()
	{
		$helper = $this->input->getCmd('helper', 'helper');

		// First, create override folder if does not exist
		$path = JPATH_ROOT . '/components/com_eventbooking/helper/override';

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$helperFile = $path . '/' . $helper . '.php';

		if (!File::exists($helper))
		{
			File::write($helperFile, '<?php');
		}

		echo sprintf('Successfully create helper override %s', $helperFile);
	}

	/**
	 * Method to create model override
	 * https://domain.com/administrator/index.php?option=com_eventbooking&task=tool.create_model_override&model=events[&app=site]
	 */

	public function create_model_override()
	{
		$app   = $this->input->getCmd('app', 'site');
		$model = $this->input->getCmd('model');

		if (!$model)
		{
			echo 'Please pass model you want to create override via model variable';

			return;
		}

		if ($app == 'site')
		{
			$path = JPATH_ROOT . '/components/com_eventbooking/model/override';
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/override';
		}

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$modelFile = $path . '/' . $model . '.php';

		if (!File::exists($modelFile))
		{
			File::write($modelFile, '<?php');
		}

		echo sprintf('Successfully create model override %s', $modelFile);
	}

	/**
	 * Method to create controller override
	 *
	 * https://domain.com/administrator/index.php?option=com_eventbooking&task=tool.create_controller_override&controller=registrant[&app=site]
	 */

	public function create_controller_override()
	{
		$app        = $this->input->getCmd('app', 'site');
		$controller = $this->input->getCmd('controller');

		if (!$controller)
		{
			echo 'Please pass controller you want to create override via controller variable';

			return;
		}

		if ($app == 'site')
		{
			$path = JPATH_ROOT . '/components/com_eventbooking/controller/override';
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/controller/override';
		}

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$controllerFile = $path . '/' . $controller . '.php';

		if (!File::exists($controllerFile))
		{
			File::write($controllerFile, '<?php');
		}

		echo sprintf('Successfully create controller override %s', $controllerFile);
	}

	/**
	 * Method to create view override
	 *
	 * https://domain.com/administrator/index.php?option=com_eventbooking&task=tool.create_view_override&view=registrant[&app=site]
	 */

	public function create_view_override()
	{
		$app  = $this->input->getCmd('app', 'site');
		$view = $this->input->getCmd('view');

		if (!$view)
		{
			echo 'Please pass view you want to create override via view variable';

			return;
		}

		if ($app == 'site')
		{
			$path = JPATH_ROOT . '/components/com_eventbooking/view/override';
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/override';
		}

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$path = $path . '/' . $view;

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$viewFile = $path . '/html.php';

		if (!File::exists($viewFile))
		{
			File::write($viewFile, '<?php');
		}

		echo sprintf('Successfully create view override %s', $viewFile);
	}

	/**
	 * Method to create layout override
	 *
	 * https://domain.com/administrator/index.php?option=com_eventbooking&task=tool.create_layout_override&view=registrant[&layout=default&app=site&template=protostar]
	 */

	public function create_layout_override()
	{
		$app = $this->input->getCmd('app', 'site');

		$template = $this->input->getCmd('template', $this->getDefaultTemplate($app));

		$view   = $this->input->getCmd('view');
		$layout = $this->input->getCmd('layout', 'default');

		if (!$template)
		{
			echo sprintf('Invalid Template %s', $template);

			return;
		}

		if (!$view)
		{
			echo 'Please pass view you want to create override via view variable';

			return;
		}

		if ($app == 'site')
		{
			$path = JPATH_ROOT . '/templates/' . $template . '/html/com_eventbooking';
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/com_eventbooking';
		}

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$path = $path . '/' . $view;

		if (!Folder::exists($path))
		{
			Folder::create($path);
		}

		$layoutFile = $path . '/' . $layout . '.php';

		if (!File::exists($layoutFile))
		{
			// Copy the original layout to template

			if ($app == 'site')
			{
				$source = JPATH_ROOT . '/components/com_eventbooking/themes/default/' . $view . '/' . $layout . '.php';
			}
			else
			{
				$source = JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/' . $view . '/tmpl/' . $layout . '.php';
			}

			if (File::exists($source))
			{
				File::copy($source, $layoutFile);
			}
			else
			{
				echo sprintf('Invalid source file %s', $source);
			}
		}

		echo sprintf('Successfully create view override %s', $layoutFile);
	}

	private function getDefaultTemplate($app)
	{
		if ($app == 'site')
		{
			$client = 0;
		}
		else
		{
			$client = 1;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id, home, template, s.params')
			->from('#__template_styles as s')
			->where('s.client_id = ' . $client)
			->where('e.enabled = 1')
			->join('LEFT', '#__extensions as e ON e.element=s.template AND e.type=' . $db->quote('template') . ' AND e.client_id=s.client_id');

		$db->setQuery($query);

		foreach ($db->loadObjectList() as $templateStyle)
		{
			if ($templateStyle->home == 1)
			{
				return $templateStyle->template;
			}
		}

		return '';
	}

	public function download_mpdf_font()
	{
		$extractDir = JPATH_ROOT . '/plugins/eventbooking/mpdf/mpdf';

		if (!Folder::exists($extractDir))
		{
			$this->app->enqueueMessage('You are not using MPDF plugin, so no need for downloading font', 'info');

			return;
		}

		$fontPackageUrl = 'https://joomdonation.com/tcpdf/ttfonts.zip';

		$fontFile = InstallerHelper::downloadPackage($fontPackageUrl, 'ttfonts.zip');

		if ($fontFile === false)
		{
			echo Text::_('The requested font could not be downloaded');

			return;
		}

		$tmpPath = $this->app->get('tmp_path');

		if (!Folder::exists($tmpPath))
		{
			$tmpPath = JPATH_ROOT . '/tmp';
		}

		$fontPackage = $tmpPath . '/ttfonts.zip';

		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$archive = new Joomla\Archive\Archive(['tmp_path' => $tmpPath]);
			$result  = $archive->extract($fontPackage, $extractDir);
		}
		else
		{
			$result = JArchive::extract($fontPackage, $extractDir);
		}

		if (!$result)
		{
			echo 'Error extract font package';

			return;
		}

		// Delete the downloaded zip file
		File::delete($fontPackage);

		$this->setRedirect('index.php?option=com_eventbooking&view=registrants', 'ttfonts for MPDF is downloaded and extracted');
	}

	public function download_mpdf_font_full()
	{
		$fontPackageUrl = 'https://joomdonation.com/tcpdf/ttfonts_full.zip';

		$fontFile = InstallerHelper::downloadPackage($fontPackageUrl, 'ttfonts.zip');

		if ($fontFile === false)
		{
			echo Text::_('The requested font could not be downloaded');

			return;
		}

		$tmpPath = $this->app->get('tmp_path');

		if (!Folder::exists($tmpPath))
		{
			$tmpPath = JPATH_ROOT . '/tmp';
		}

		$fontPackage = $tmpPath . '/ttfonts.zip';

		$extractDir = JPATH_ROOT . '/plugins/eventbooking/mpdf/mpdf';

		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$archive = new Joomla\Archive\Archive(['tmp_path' => $tmpPath]);
			$result  = $archive->extract($fontPackage, $extractDir);
		}
		else
		{
			$result = JArchive::extract($fontPackage, $extractDir);
		}

		if (!$result)
		{
			echo 'Error extract font package';

			return;
		}

		// Delete the downloaded zip file
		File::delete($fontPackage);

		$this->setRedirect('index.php?option=com_eventbooking&view=registrants', 'ttfonts for MPDF is downloaded and extracted');
	}

	/**
	 * Method to update to new countries and states database
	 *
	 */
	public function update_countries_states_database()
	{
		if (!Factory::getUser()->authorise('core.admin'))
		{
			echo 'You do not have permission to execute this task';
		}

		// We need to change data type for state_2_code and state_3_code so that it can store longer data
        $db = Factory::getDbo();

		$sql = "ALTER TABLE  `#__eb_states` CHANGE  `state_2_code` `state_2_code` char(10) DEFAULT NULL";
		$db->setQuery($sql)
			->execute();

		$sql = "ALTER TABLE  `#__eb_states` CHANGE  `state_3_code` `state_3_code` char(10) DEFAULT NULL";
		$db->setQuery($sql)
			->execute();

		EventbookingHelper::executeSqlFile(JPATH_ADMINISTRATOR.'/components/com_eventbooking/sql/countries_states.sql');

		echo 'Countries, States database successfully updated';
	}
}
