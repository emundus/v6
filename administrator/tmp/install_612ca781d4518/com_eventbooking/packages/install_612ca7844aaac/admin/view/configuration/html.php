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
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EventbookingViewConfigurationHtml extends RADViewHtml
{
	public function display()
	{
		if (!Factory::getUser()->authorise('core.admin', 'com_eventbooking'))
		{
			return JError::raiseWarning(403, Text::_('JERROR_ALERTNOAUTHOR'));
		}

		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		if ($config->multiple_booking)
		{
			// Get real value for
			$query->select('config_value')
				->from('#__eb_configs')
				->where('config_key = "collect_member_information"');
			$db->setQuery($query);
			$config->collect_member_information = $db->loadResult();
			$query->clear();
		}

		$options = [];

		if (!EventbookingHelper::isJoomla4())
		{
			$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_VERSION_2'));
			$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_VERSION_3'));
		}

		$options[] = HTMLHelper::_('select.option', 4, Text::_('EB_VERSION_4'));

		if (EventbookingHelper::isJoomla4())
		{
			$options[] = HTMLHelper::_('select.option', 5, Text::_('EB_VERSION_5'));
		}

		$options[] = HTMLHelper::_('select.option', 'uikit3', Text::_('EB_UIKIT_3'));

		// Get extra UI options
		$files = Folder::files(JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/ui', '.php');

		foreach ($files as $file)
		{
			if (in_array($file, ['abstract.php', 'bootstrap2.php', 'uikit3.php', 'bootstrap3.php', 'bootstrap4.php', 'bootstrap5.php', 'interface.php']))
			{
				continue;
			}

			$file      = str_replace('.php', '', $file);
			$options[] = HTMLHelper::_('select.option', $file, ucfirst($file));
		}

		if (EventbookingHelper::isJoomla4())
		{
			$lists['twitter_bootstrap_version'] = HTMLHelper::_('select.genericlist', $options, 'twitter_bootstrap_version', 'class="form-select"', 'value', 'text', $config->get('twitter_bootstrap_version', 5));
		}
		else
		{
			$lists['twitter_bootstrap_version'] = HTMLHelper::_('select.genericlist', $options, 'twitter_bootstrap_version', 'class="form-select"', 'value', 'text', $config->get('twitter_bootstrap_version', 2));
		}
		
		$options                       = [];
		$options[]                     = HTMLHelper::_('select.option', 0, Text::_('EB_UNTIL_END_OF_DAY'));
		$options[]                     = HTMLHelper::_('select.option', 1, Text::_('EB_UNTIL_CURRENT_TIME_GREATER'));
		$lists['show_upcoming_events'] = HTMLHelper::_('select.genericlist', $options, 'show_upcoming_events', 'class="form-select"', 'value', 'text', $config->get('show_upcoming_events', 0));


		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('SUNDAY'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('MONDAY'));

		$lists['calendar_start_date'] = HTMLHelper::_('select.genericlist', $options, 'calendar_start_date', ' class="form-select" ', 'value', 'text',
			$config->calendar_start_date);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_ORDERING'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_EVENT_DATE'));

		$lists['order_events'] = HTMLHelper::_('select.genericlist', $options, 'order_events', '  class="form-select" ', 'value', 'text',
			$config->order_events);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'asc', Text::_('EB_ASC'));
		$options[] = HTMLHelper::_('select.option', 'desc', Text::_('EB_DESC'));

		$lists['order_direction']                 = HTMLHelper::_('select.genericlist', $options, 'order_direction', 'class="form-select"', 'value', 'text', $config->order_direction);
		$lists['events_dropdown_order_direction'] = HTMLHelper::_('select.genericlist', $options, 'events_dropdown_order_direction', 'class="form-select"', 'value', 'text', $config->get('events_dropdown_order_direction', 'ASC'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('EB_FULL_PAYMENT'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('EB_DEPOSIT_PAYMENT'));

		$lists['default_payment_type'] = HTMLHelper::_('select.genericlist', $options, 'default_payment_type', 'class="form-select"', 'value', 'text', $config->get('default_payment_type', 0));

		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 'exact', Text::_('EB_EXACT_PHRASE'));
		$options[]              = HTMLHelper::_('select.option', 'any', Text::_('EB_ANY_WORDS'));
		$lists['search_events'] = HTMLHelper::_('select.genericlist', $options, 'search_events', 'class="form-select"', 'value', 'text', $config->get('search_events', ''));

		//Get list of country
		$query->clear()
			->select('name AS value, name AS text')
			->from('#__eb_countries')
			->order('name');
		$db->setQuery($query);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_DEFAULT_COUNTRY'));
		$options   = array_merge($options, $db->loadObjectList());

		$lists['country_list'] = HTMLHelper::_('select.genericlist', $options, 'default_country', 'class="chosen form-select"', 'value', 'text', $config->default_country);

		$query->clear()
			->select('name, title')
			->from('#__eb_fields')
			->where('fieldtype = "Text"')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT'), 'name', 'title');
		$options   = array_merge($options, $db->loadObjectList());

		$lists['eu_vat_number_field'] = HTMLHelper::_('select.genericlist', $options, 'eu_vat_number_field', ' class="form-select"', 'name', 'title', $config->eu_vat_number_field);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', ',', Text::_('EB_COMMA'));
		$options[] = HTMLHelper::_('select.option', ';', Text::_('EB_SEMICOLON'));

		$lists['csv_delimiter'] = HTMLHelper::_('select.genericlist', $options, 'csv_delimiter', 'class="form-select"', 'value', 'text', $config->csv_delimiter);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'csv', Text::_('EB_FILE_CSV'));
		$options[] = HTMLHelper::_('select.option', 'xls', Text::_('EB_FILE_EXCEL_2003'));
		$options[] = HTMLHelper::_('select.option', 'xlsx', Text::_('EB_FILE_EXCEL_2007'));

		$lists['export_data_format'] = HTMLHelper::_('select.genericlist', $options, 'export_data_format', 'class="form-select"', 'value', 'text', $config->get('export_data_format', 'xlsx'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_DEFAULT'));
		$options[] = HTMLHelper::_('select.option', 'simple', Text::_('EB_SIMPLE_FORM'));

		$lists['submit_event_form_layout'] = HTMLHelper::_('select.genericlist', $options, 'submit_event_form_layout', 'class="form-select"', 'value', 'text',
			$config->submit_event_form_layout);

		//Theme configuration
		$options = [];
		$themes  = Folder::files(JPATH_ROOT . '/media/com_eventbooking/assets/css/themes', '.css');
		sort($themes);

		foreach ($themes as $theme)
		{
			$theme     = substr($theme, 0, strlen($theme) - 4);
			$options[] = HTMLHelper::_('select.option', $theme, ucfirst($theme));
		}

		$lists['calendar_theme'] = HTMLHelper::_('select.genericlist', $options, 'calendar_theme', ' class="form-select" ', 'value', 'text',
			$config->calendar_theme);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_BOTTOM'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_TOP'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_BOTH'));

		$lists['register_buttons_position'] = HTMLHelper::_('select.genericlist', $options, 'register_buttons_position', 'class="form-select"', 'value', 'text', $config->get('register_buttons_position'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_POSITION'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_BEFORE_AMOUNT'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_AFTER_AMOUNT'));

		$lists['currency_position'] = HTMLHelper::_('select.genericlist', $options, 'currency_position', ' class="form-select"', 'value', 'text',
			$config->currency_position);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_SHOW_IF_LIMITED'));

		$lists['show_capacity'] = HTMLHelper::_('select.genericlist', $options, 'show_capacity', 'class="form-select"', 'value', 'text', $config->show_capacity);

		// Social sharing options
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'Facebook', Text::_('Facebook'));
		$options[] = HTMLHelper::_('select.option', 'Twitter', Text::_('Twitter'));
		$options[] = HTMLHelper::_('select.option', 'LinkedIn', Text::_('LinkedIn'));
		$options[] = HTMLHelper::_('select.option', 'Delicious', Text::_('Delicious'));
		$options[] = HTMLHelper::_('select.option', 'Digg', Text::_('Digg'));
		$options[] = HTMLHelper::_('select.option', 'Pinterest', Text::_('Pinterest'));

		$lists['social_sharing_buttons'] = HTMLHelper::_('select.genericlist', $options, 'social_sharing_buttons[]', ' class="form-select" multiple="multiple" ', 'value', 'text',
			explode(',', $config->social_sharing_buttons));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'tbl.id', Text::_('EB_ID'));
		$options[] = HTMLHelper::_('select.option', 'tbl.register_date', Text::_('EB_REGISTRATION_DATE'));

		$query->clear()
			->select('name, title')
			->from('#__eb_fields')
			->where('published = 1')
			->where('(is_core = 1 OR is_searchable = 1 )')
			->order('title');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $field)
		{
			$options[] = HTMLHelper::_('select.option', 'tbl.' . $field->name, $field->title);
		}

		$lists['public_registrants_list_order'] = HTMLHelper::_('select.genericlist', $options, 'public_registrants_list_order', 'class="chosen form-select"', 'value', 'text', $config->get('public_registrants_list_order', 'tbl.id'));


		$options[] = HTMLHelper::_('select.option', 'ev.event_date', Text::_('EB_EVENT_DATE'));
		$options[] = HTMLHelper::_('select.option', 'ev.title', Text::_('EB_EVENT_TITLE'));
		$options[] = HTMLHelper::_('select.option', 'ev.ordering', Text::_('EB_EVENT_ORDERING'));

		$lists['registrants_management_order'] = HTMLHelper::_('select.genericlist', $options, 'registrants_management_order', 'class="chosen form-select"', 'value', 'text', $config->get('registrants_management_order', 'tbl.id'));
		$lists['registration_history_order']   = HTMLHelper::_('select.genericlist', $options, 'registration_history_order', 'class="chosen form-select"', 'value', 'text', $config->get('registration_history_order', 'tbl.id'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'asc', Text::_('EB_ASC'));
		$options[] = HTMLHelper::_('select.option', 'desc', Text::_('EB_DESC'));

		$lists['public_registrants_list_order_dir'] = HTMLHelper::_('select.genericlist', $options, 'public_registrants_list_order_dir', 'class="form-select"', 'value', 'text', $config->get('public_registrants_list_order_dir', 'desc'));
		$lists['registrants_management_order_dir']  = HTMLHelper::_('select.genericlist', $options, 'registrants_management_order_dir', 'class="chosen form-select"', 'value', 'text', $config->get('registrants_management_order_dir', 'desc'));
		$lists['registration_history_order_dir']    = HTMLHelper::_('select.genericlist', $options, 'registration_history_order_dir', 'class="chosen form-select"', 'value', 'text', $config->get('registration_history_order_dir', 'desc'));


		//Default settings when creating new events
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_INDIVIDUAL_GROUP'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_INDIVIDUAL_ONLY'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_GROUP_ONLY'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_DISABLE_REGISTRATION'));

		$lists['registration_type']   = HTMLHelper::_('select.genericlist', $options, 'registration_type', ' class="form-select" ', 'value', 'text', $config->get('registration_type', 0));
		$lists['access']              = HTMLHelper::_('access.level', 'access', $config->get('access', 1), 'class="form-select"', false);
		$lists['registration_access'] = HTMLHelper::_('access.level', 'registration_access', $config->get('registration_access', 1), 'class="form-select"', false);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_UNPUBLISHED'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PUBLISHED'));

		$lists['default_event_status'] = HTMLHelper::_('select.genericlist', $options, 'default_event_status', ' class="form-select"', 'value', 'text', $config->get('default_event_status', 0));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PENDING'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PAID'));

		$lists['default_free_event_registration_status'] = HTMLHelper::_('select.genericlist', $options, 'default_free_event_registration_status', 'class="form-select"', 'value', 'text', $config->get('default_free_event_registration_status', 1));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_FORMAT'));
		$options[] = HTMLHelper::_('select.option', '%Y-%m-%d', 'Y-m-d');
		$options[] = HTMLHelper::_('select.option', '%Y/%m/%d', 'Y/m/d');
		$options[] = HTMLHelper::_('select.option', '%Y.%m.%d', 'Y.m.d');
		$options[] = HTMLHelper::_('select.option', '%m-%d-%Y', 'm-d-Y');
		$options[] = HTMLHelper::_('select.option', '%m/%d/%Y', 'm/d/Y');
		$options[] = HTMLHelper::_('select.option', '%m.%d.%Y', 'm.d.Y');
		$options[] = HTMLHelper::_('select.option', '%d-%m-%Y', 'd-m-Y');
		$options[] = HTMLHelper::_('select.option', '%d/%m/%Y', 'd/m/Y');
		$options[] = HTMLHelper::_('select.option', '%d.%m.%Y', 'd.m.Y');

		$lists['date_field_format'] = HTMLHelper::_('select.genericlist', $options, 'date_field_format', 'class="form-select"', 'value', 'text', $config->get('date_field_format', '%Y-%m-%d'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'resize', Text::_('EB_RESIZE'));
		$options[] = HTMLHelper::_('select.option', 'crop_resize', Text::_('EB_CROP_RESIZE'));

		$lists['resize_image_method'] = HTMLHelper::_('select.genericlist', $options, 'resize_image_method', 'class="form-select"', 'value', 'text', $config->get('resize_image_method', 'resize'));

		$currencies = require_once JPATH_ROOT . '/components/com_eventbooking/helper/currencies.php';

		ksort($currencies);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_CURRENCY'));

		foreach ($currencies as $code => $title)
		{
			$options[] = HTMLHelper::_('select.option', $code, $title);
		}

		$lists['currency_code'] = HTMLHelper::_('select.genericlist', $options, 'currency_code', 'class="chosen form-select"', 'value', 'text', isset($config->currency_code) ? $config->currency_code : 'USD');

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ALL_NESTED_CATEGORIES'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_ONLY_LAST_ONE'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('JNO'));

		$lists['insert_category'] = HTMLHelper::_('select.genericlist', $options, 'insert_category', ' class="form-select"', 'value', 'text',
			$config->insert_category);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PRICE_WITHOUT_TAX'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PRICE_TAX_INCLUDED'));

		$lists['setup_price'] = HTMLHelper::_('select.genericlist', $options, 'setup_price', ' class="form-select"', 'value', 'text',
			$config->get('setup_price', '0'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ENABLE'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_ONLY_TO_ADMIN'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_ONLY_TO_REGISTRANT'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_DISABLE'));

		$lists['send_emails'] = HTMLHelper::_('select.genericlist', $options, 'send_emails', ' class="form-select"', 'value', 'text',
			$config->send_emails);

		$options                             = [];
		$options[]                           = HTMLHelper::_('select.option', '', Text::_('JNO'));
		$options[]                           = HTMLHelper::_('select.option', 'first_group_member', Text::_('EB_FIRST_GROUP_MEMBER'));
		$options[]                           = HTMLHelper::_('select.option', 'last_group_member', Text::_('EB_LAST_GROUP_MEMBER'));
		$lists['auto_populate_billing_data'] = HTMLHelper::_('select.genericlist', $options, 'auto_populate_billing_data', 'class="form-select"', 'value', 'text',
			$config->auto_populate_billing_data);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'KM', Text::_('EB_KM'));
		$options[] = HTMLHelper::_('select.option', 'MILE', Text::_('EB_MILE'));

		$lists['radius_search_distance'] = HTMLHelper::_('select.genericlist', $options, 'radius_search_distance', 'class="form-select"', 'value', 'text', $config->get('radius_search_distance', 'KM'));

		$fontsPath = JPATH_ROOT . '/components/com_eventbooking/tcpdf/fonts/';
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_FONT'));
		$options[] = HTMLHelper::_('select.option', 'courier', Text::_('Courier'));
		$options[] = HTMLHelper::_('select.option', 'helvetica', Text::_('Helvetica'));
		$options[] = HTMLHelper::_('select.option', 'symbol', Text::_('Symbol'));
		$options[] = HTMLHelper::_('select.option', 'times', Text::_('Times New Roman'));
		$options[] = HTMLHelper::_('select.option', 'zapfdingbats', Text::_('Zapf Dingbats'));

		$additionalFonts = [
			'aealarabiya',
			'aefurat',
			'dejavusans',
			'dejavuserif',
			'freemono',
			'freesans',
			'freeserif',
			'hysmyeongjostdmedium',
			'kozgopromedium',
			'kozminproregular',
			'msungstdlight',
			'opensans',
			'cid0jp',
			'DroidSansFallback',
			'PFBeauSansProthin',
			'PFBeauSansPro',
			'roboto',
			'consolateelfb',
			'ubuntu',
			'tantular',
			'anonymouspro',
			'Abhayalibremedium',
			'alice',
		];

		foreach ($additionalFonts as $fontName)
		{
			if (file_exists($fontsPath . $fontName . '.php'))
			{
				$options[] = HTMLHelper::_('select.option', $fontName, ucfirst($fontName));
			}
		}

		// Support True Type Font
		$trueTypeFonts = Folder::files($fontsPath, '.ttf');

		foreach ($trueTypeFonts as $trueTypeFont)
		{
			$options[] = HTMLHelper::_('select.option', $trueTypeFont, $trueTypeFont);
		}

		$lists['pdf_font'] = HTMLHelper::_('select.genericlist', $options, 'pdf_font', ' class="form-select"', 'value', 'text', empty($config->pdf_font) ? 'times' : $config->pdf_font);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'P', Text::_('Portrait'));
		$options[] = HTMLHelper::_('select.option', 'L', Text::_('Landscape'));

		$lists['ticket_page_orientation']      = HTMLHelper::_('select.genericlist', $options, 'ticket_page_orientation', 'class="form-select"', 'value', 'text', $config->get('ticket_page_orientation', 'P'));
		$lists['certificate_page_orientation'] = HTMLHelper::_('select.genericlist', $options, 'certificate_page_orientation', 'class="form-select"', 'value', 'text', $config->get('certificate_page_orientation', 'P'));
		$lists['registrants_page_orientation'] = HTMLHelper::_('select.genericlist', $options, 'registrants_page_orientation', 'class="form-select"', 'value', 'text', $config->get('registrants_page_orientation', 'P'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'A4', Text::_('A4'));
		$options[] = HTMLHelper::_('select.option', 'A5', Text::_('A5'));
		$options[] = HTMLHelper::_('select.option', 'A6', Text::_('A6'));
		$options[] = HTMLHelper::_('select.option', 'A7', Text::_('A7'));
		$options[] = HTMLHelper::_('select.option', 'USLETTER', Text::_('USLETTER'));

		$lists['ticket_page_format']      = HTMLHelper::_('select.genericlist', $options, 'ticket_page_format', 'class="form-select"', 'value', 'text', $config->get('ticket_page_format', 'A4'));
		$lists['certificate_page_format'] = HTMLHelper::_('select.genericlist', $options, 'certificate_page_format', 'class="form-select"', 'value', 'text', $config->get('certificate_page_format', 'A4'));
		$lists['registrants_page_format'] = HTMLHelper::_('select.genericlist', $options, 'registrants_page_format', 'class="form-select"', 'value', 'text', $config->get('registrants_page_format', 'A4'));

		if (empty($config->default_ticket_layout))
		{
			$config->default_ticket_layout = $config->certificate_layout;
		}

		// Default menu item settings
		$menus     = Factory::getApplication()->getMenu('site');
		$component = ComponentHelper::getComponent('com_eventbooking');
		$items     = $menus->getItems('component_id', $component->id);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT'));

		foreach ($items as $item)
		{
			if (!empty($item->query['view']) && in_array($item->query['view'], ['calendar', 'categories', 'upcomingevents', 'category', 'archive']))
			{
				$options[] = HTMLHelper::_('select.option', $item->id, str_repeat('- ', $item->level) . $item->title);
			}
		}

		$lists['default_menu_item'] = HTMLHelper::_('select.genericlist', $options, 'default_menu_item', 'class="form-select"', 'value', 'text', $config->default_menu_item);
		$languages                  = EventbookingHelper::getLanguages();

		if (Multilanguage::isEnabled() && count($languages))
		{
			foreach ($languages as $language)
			{
				$attributes = ['component_id', 'language'];
				$values     = [$component->id, [$language->lang_code, '*']];
				$items      = $menus->getItems($attributes, $values);

				$options   = [];
				$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT'));

				foreach ($items as $item)
				{
					if (!empty($item->query['view']) && in_array($item->query['view'], ['fullcalendar', 'calendar', 'categories', 'upcomingevents', 'category', 'archive']))
					{
						$options[] = HTMLHelper::_('select.option', $item->id, str_repeat('- ', $item->level) . $item->title);
					}
				}

				$key         = 'default_menu_item_' . $language->lang_code;
				$lists[$key] = HTMLHelper::_('select.genericlist', $options, $key, 'class="form-select"', 'value', 'text', $config->{$key});
				$lists[$key] = EventbookingHelperHtml::getChoicesJsSelect($lists[$key]);
			}
		}

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'googlemap', 'Google Map');
		$options[] = HTMLHelper::_('select.option', 'openstreetmap', 'OpenStreetMap');

		$lists['map_provider'] = HTMLHelper::_('select.genericlist', $options, 'map_provider', 'class="form-select"', 'value', 'text', $config->get('map_provider', 'googlemap'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'use_tooltip', Text::_('EB_USE_TOOLTIP'));
		$options[] = HTMLHelper::_('select.option', 'under_field_label', Text::_('EB_UNDER_FIELD_LABEL'));
		$options[] = HTMLHelper::_('select.option', 'under_field_input', Text::_('EB_UNDER_FIELD_INPUT'));

		$lists['display_field_description'] = HTMLHelper::_('select.genericlist', $options, 'display_field_description', 'class="form-select"', 'value', 'text', $config->get('display_field_description', 'use_tooltip'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'name', Text::_('EB_NAME'));
		$options[] = HTMLHelper::_('select.option', 'ordering', Text::_('EB_ORDERING'));

		$lists['category_dropdown_ordering'] = HTMLHelper::_('select.genericlist', $options, 'category_dropdown_ordering', 'class="form-select"', 'value', 'text', $config->get('category_dropdown_ordering', 'name'));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_DEFAULT'));

		for ($i = 0; $i <= 9; $i++)
		{
			$options[] = HTMLHelper::_('select.option', $i, $i);
		}

		$lists['resized_png_image_quality'] = HTMLHelper::_('select.genericlist', $options, 'resized_png_image_quality', 'class="form-select"', 'value', 'text', $config->get('resized_png_image_quality', -1));

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_DEFAULT'));

		for ($i = 0; $i <= 100; $i++)
		{
			$options[] = HTMLHelper::_('select.option', $i, $i);
		}

		$lists['resized_jpeg_image_quality'] = HTMLHelper::_('select.genericlist', $options, 'resized_jpeg_image_quality', 'class="form-select chosen"', 'value', 'text', $config->get('resized_jpeg_image_quality', -1));

		// Editor plugin for code editing
		$editorPlugin = null;

		if (PluginHelper::isEnabled('editors', 'codemirror'))
		{
			$editorPlugin = 'codemirror';
		}
		elseif (PluginHelper::isEnabled('editor', 'none'))
		{
			$editorPlugin = 'none';
		}

		if ($editorPlugin)
		{
			$this->editor = Editor::getInstance($editorPlugin);
		}

		if (EventbookingHelper::isJoomla4())
		{
			$keys = [
				'country_list',
				'public_registrants_list_order',
				'registrants_management_order',
				'registration_history_order',
				'registrants_management_order_dir',
				'registration_history_order_dir',
				'currency_code',
				'resized_jpeg_image_quality',
				'default_menu_item',
				'pdf_font',
				'eu_vat_number_field',
			];

			foreach ($keys as $key)
			{
				$lists[$key] = EventbookingHelperHtml::getChoicesJsSelect($lists[$key]);
			}
		}

		$this->lists     = $lists;
		$this->config    = $config;
		$this->languages = $languages;
		$this->addToolbar();

		parent::display();
	}

	/**
	 * Override addToolbar method to use custom buttons for this view
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('EB_CONFIGURATION'), 'generic.png');
		ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('save');
		ToolbarHelper::cancel();
		ToolbarHelper::preferences('com_eventbooking');
	}
}
