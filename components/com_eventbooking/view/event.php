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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

trait EventbookingViewEvent
{
	/**
	 * Build data use on submit event form
	 *
	 * @param   EventbookingTableEvent  $item
	 * @param   array                   $categories
	 * @param   array                   $locations
	 */
	public function buildFormData($item, $categories, $locations)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		//Locations dropdown
		$options                    = [];
		$options[]                  = HTMLHelper::_('select.option', 0, Text::_('EB_SELECT_LOCATION'), 'id', 'name');
		$options                    = array_merge($options, $locations);
		$this->lists['location_id'] = HTMLHelper::_('select.genericlist', $options, 'location_id', ' class="advancedSelect form-select" ', 'id', 'name', $item->location_id);

		if ($this->getLayout() == 'simple')
		{
			$selectCategoryValue = '';
		}
		else
		{
			$selectCategoryValue = 0;
		}

		$options = EventbookingHelperHtml::getCategoryOptions($categories, $selectCategoryValue);

		if ($item->id)
		{
			$query->clear()
				->select('category_id')
				->from('#__eb_event_categories')
				->where('event_id = ' . $item->id)
				->where('main_category = 0');
			$db->setQuery($query);
			$additionalCategories = $db->loadColumn();
		}
		else
		{
			$additionalCategories = [];
		}

		$this->lists['main_category_id'] = HTMLHelper::_('select.genericlist', $options, 'main_category_id', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="advancedSelect input-xlarge validate[required] form-select"',
			'list.select'        => (int) $item->main_category_id,
		]);

		array_shift($options);

		$this->lists['category_id'] = HTMLHelper::_('select.genericlist', $options, 'category_id[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="advancedSelect input-xlarge form-select"  size="5" multiple="multiple"',
			'list.select'        => $additionalCategories,
		]);

		$options                                 = [];
		$options[]                               = HTMLHelper::_('select.option', 1, Text::_('%'));
		$options[]                               = HTMLHelper::_('select.option', 2, $config->currency_symbol);
		$this->lists['discount_type']            = HTMLHelper::_('select.genericlist', $options, 'discount_type', ' class="input-medium form-select d-inline-block" ', 'value', 'text', $item->discount_type);
		$this->lists['early_bird_discount_type'] = HTMLHelper::_('select.genericlist', $options, 'early_bird_discount_type', 'class="input-medium form-select d-inline-block"', 'value', 'text', $item->early_bird_discount_type);
		$this->lists['late_fee_type']            = HTMLHelper::_('select.genericlist', $options, 'late_fee_type', 'class="input-medium form-select d-inline-block"', 'value', 'text', $item->late_fee_type);

		if ($config->activate_deposit_feature)
		{
			$this->lists['deposit_type'] = HTMLHelper::_('select.genericlist', $options, 'deposit_type', ' class="input-medium form-select" ', 'value', 'text', $item->deposit_type);
		}

		if (!$item->id)
		{
			$item->registration_type = $config->registration_type;
		}

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_INDIVIDUAL_GROUP'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_INDIVIDUAL_ONLY'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_GROUP_ONLY'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_DISABLE_REGISTRATION'));

		$this->lists['registration_type'] = HTMLHelper::_('select.genericlist', $options, 'registration_type', ' class="input-xlarge form-select" ', 'value', 'text', $item->registration_type);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_EACH_MEMBER'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_EACH_REGISTRATION'));

		$this->lists['members_discount_apply_for'] = HTMLHelper::_('select.genericlist', $options, 'members_discount_apply_for', 'class="form-select"', 'value', 'text', $item->members_discount_apply_for);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_USE_GLOBAL'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_INDIVIDUAL_ONLY'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_GROUP_ONLY'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_INDIVIDUAL_GROUP'));
		$options[] = HTMLHelper::_('select.option', 4, Text::_('EB_DISABLE'));

		$this->lists['enable_coupon'] = HTMLHelper::_('select.genericlist', $options, 'enable_coupon', ' class="form-select" ', 'value', 'text', $item->enable_coupon);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('No'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('Yes'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_USE_GLOBAL'));

		$this->lists['activate_waiting_list'] = HTMLHelper::_('select.genericlist', $options, 'activate_waiting_list', ' class="form-select" ', 'value', 'text', $item->activate_waiting_list);

		$this->lists['access']              = HTMLHelper::_('access.level', 'access', $item->access, 'class="form-select"', false);
		$this->lists['registration_access'] = HTMLHelper::_('access.level', 'registration_access', $item->registration_access, 'class="form-select"', false);

		if ($item->event_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->event_date));
			$selectedMinute = date('i', strtotime($item->event_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['event_date_hour']   = HTMLHelper::_('select.integerlist', 0, 23, 1, 'event_date_hour', ' class="input-mini form-select w-auto d-inline-block" ', $selectedHour);
		$this->lists['event_date_minute'] = HTMLHelper::_('select.integerlist', 0, 55, 5, 'event_date_minute', ' class="input-mini form-select w-auto d-inline-block" ', $selectedMinute, '%02d');

		if ($item->event_end_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->event_end_date));
			$selectedMinute = date('i', strtotime($item->event_end_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['event_end_date_hour']   = HTMLHelper::_('select.integerlist', 0, 23, 1, 'event_end_date_hour', ' class="input-mini form-select w-auto d-inline-block" ', $selectedHour);
		$this->lists['event_end_date_minute'] = HTMLHelper::_('select.integerlist', 0, 55, 5, 'event_end_date_minute', ' class="input-mini form-select w-auto d-inline-block" ', $selectedMinute, '%02d');

		// Cut off time
		if ($item->cut_off_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->cut_off_date));
			$selectedMinute = date('i', strtotime($item->cut_off_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['cut_off_hour']   = HTMLHelper::_('select.integerlist', 0, 23, 1, 'cut_off_hour', ' class="input-mini form-select w-auto d-inline-block" ', $selectedHour);
		$this->lists['cut_off_minute'] = HTMLHelper::_('select.integerlist', 0, 55, 5, 'cut_off_minute', ' class="input-mini form-select w-auto d-inline-block" ', $selectedMinute, '%02d');

		// Registration start time
		if ($item->registration_start_date != $db->getNullDate())
		{
			$selectedHour   = date('G', strtotime($item->registration_start_date));
			$selectedMinute = date('i', strtotime($item->registration_start_date));
		}
		else
		{
			$selectedHour   = 0;
			$selectedMinute = 0;
		}

		$this->lists['registration_start_hour']   = HTMLHelper::_('select.integerlist', 0, 23, 1, 'registration_start_hour', ' class="form-select input-mini form-select w-auto d-inline-block" ', $selectedHour);
		$this->lists['registration_start_minute'] = HTMLHelper::_('select.integerlist', 0, 55, 5, 'registration_start_minute', ' class="form-select input-mini form-select w-auto d-inline-block" ', $selectedMinute, '%02d');

		$nullDate = $db->getNullDate();

		//Custom field handles
		if ($config->event_custom_field)
		{
			$registry = new Registry();
			$registry->loadString($item->custom_fields);
			$data         = new stdClass();
			$data->params = $registry->toArray();
			$form         = JForm::getInstance('pmform', JPATH_ROOT . '/components/com_eventbooking/fields.xml', [], false, '//config');
			$form->bind($data);
			$this->form = $form;
		}

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_ALL_PAYMENT_METHODS'), 'id', 'title');

		$query->clear()
			->select('id, title')
			->from('#__eb_payment_plugins')
			->where('published = 1');
		$db->setQuery($query);
		$this->lists['payment_methods'] = HTMLHelper::_('select.genericlist', array_merge($options, $db->loadObjectList()), 'payment_methods[]', ' class="form-select advancedSelect" multiple="multiple" ', 'id', 'title', explode(',', $item->payment_methods));

		$currencies = require JPATH_ROOT . '/components/com_eventbooking/helper/currencies.php';
		ksort($currencies);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_CURRENCY'));

		foreach ($currencies as $code => $title)
		{
			$options[] = HTMLHelper::_('select.option', $code, $title);
		}

		$this->lists['currency_code'] = HTMLHelper::_('select.genericlist', $options, 'currency_code', 'class="form-select"', 'value', 'text', $item->currency_code);

		$this->lists['discount_groups'] = HTMLHelper::_('access.usergroup', 'discount_groups[]', explode(',', $item->discount_groups),
			' multiple="multiple" size="6" ', false);

		$this->lists['available_attachment'] = EventbookingHelper::callOverridableHelperMethod('Helper', 'attachmentList', [explode('|', $item->attachment), $config]);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_USE_GLOBAL'));

		$this->lists['enable_terms_and_conditions'] = HTMLHelper::_('select.genericlist', $options, 'enable_terms_and_conditions', ' class="form-select" ', 'value', 'text', $item->enable_terms_and_conditions);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('EB_USE_GLOBAL'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));

		$this->lists['prevent_duplicate_registration'] = HTMLHelper::_('select.genericlist', $options, 'prevent_duplicate_registration', 'class="form-select"', 'value', 'text', $item->prevent_duplicate_registration);
		$this->lists['collect_member_information']     = HTMLHelper::_('select.genericlist', $options, 'collect_member_information', 'class="form-select"', 'value', 'text', $item->collect_member_information);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_USE_GLOBAL'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_ENABLE'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_ONLY_TO_ADMIN'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_ONLY_TO_REGISTRANT'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_DISABLE'));

		$this->lists['send_emails'] = HTMLHelper::_('select.genericlist', $options, 'send_emails', 'class="form-select"', 'value', 'text',
			$item->send_emails);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PENDING'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PAID'));

		$this->lists['free_event_registration_status'] = HTMLHelper::_('select.genericlist', $options, 'free_event_registration_status', 'class="form-select"', 'value', 'text', $item->free_event_registration_status);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '1', Text::_('EB_BEFORE'));
		$options[] = HTMLHelper::_('select.option', '-1', Text::_('EB_AFTER'));

		$this->lists['send_first_reminder_time']  = HTMLHelper::_('select.genericlist', $options, 'send_first_reminder_time', 'class="input-medium form-select d-inline-block"', 'value', 'text',
			$item->send_first_reminder >= 0 ? 1 : -1);
		$this->lists['send_second_reminder_time'] = HTMLHelper::_('select.genericlist', $options, 'send_second_reminder_time', 'class="input-medium form-select d-inline-block"', 'value', 'text',
			$item->send_second_reminder >= 0 ? 1 : -1);

		$item->send_first_reminder  = abs($item->send_first_reminder);
		$item->send_second_reminder = abs($item->send_second_reminder);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'd', Text::_('EB_DAYS'));
		$options[] = HTMLHelper::_('select.option', 'h', Text::_('EB_HOURS'));

		$this->lists['first_reminder_frequency']  = HTMLHelper::_('select.genericlist', $options, 'first_reminder_frequency', 'class="form-select d-inline-block w-auto"', 'value', 'text', $item->first_reminder_frequency ?: 'd');
		$this->lists['second_reminder_frequency'] = HTMLHelper::_('select.genericlist', $options, 'second_reminder_frequency', 'class="form-select d-inline-block w-auto"', 'value', 'text', $item->second_reminder_frequency ?: 'd');

		// Recurring settings
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_NO_REPEAT'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_DAILY'));
		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_WEEKLY'));
		$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_MONTHLY_BY_DAYS'));
		$options[] = HTMLHelper::_('select.option', 4, Text::_('EB_MONTHLY_BY_WEEKDAY'));

		$this->lists['recurring_type'] = HTMLHelper::_('select.genericlist', $options, 'recurring_type', ' class="form-select input-large" ', 'value', 'text', $item->recurring_type);

		if ($item->published == 2)
		{
			$options                  = [];
			$options[]                = HTMLHelper::_('select.option', 0, Text::_('EB_UNPUBLISHED'));
			$options[]                = HTMLHelper::_('select.option', 1, Text::_('EB_PUBLISHED'));
			$options[]                = HTMLHelper::_('select.option', 2, Text::_('EB_CANCELLED'));
			$this->lists['published'] = HTMLHelper::_('select.genericlist', $options, 'published', ' class="form-select" ', 'value', 'text',
				$item->published);
		}

		#Plugin support
		PluginHelper::importPlugin('eventbooking');
		$results = Factory::getApplication()->triggerEvent('onEditEvent', [$item]);


		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->dateFormat       = str_replace('%', '', $this->datePickerFormat);
		$this->prices           = EventbookingHelperDatabase::getGroupRegistrationRates($item->id);
		$this->nullDate         = $nullDate;
		$this->config           = $config;
		$this->plugins          = $results;
	}
}