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
use Joomla\CMS\Uri\Uri;

abstract class EventbookingHelperJquery
{
	/**
	 * Method to load the colorbox into the document head
	 *
	 * If debugging mode is on an uncompressed version of colorbox is included for easier debugging.
	 *
	 * @param   string  $class
	 * @param   string  $width
	 * @param   string  $height
	 * @param   string  $iframe
	 * @param   string  $inline
	 *
	 * @return  void
	 */
	public static function colorbox($class = 'sr-iframe', $width = '80%', $height = '80%', $iframe = "true", $inline = "false", $scrolling = "true")
	{
		static $loaded = false;

		if (!$loaded)
		{
			$document = Factory::getDocument();
			$rootUri  = Uri::root(true);
			$document->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/colorbox/colorbox.min.css');
			$document->addScript($rootUri . '/media/com_eventbooking/assets/js/colorbox/jquery.colorbox.min.js');


			$activeLanguageTag   = Factory::getLanguage()->getTag();
			$allowedLanguageTags = ['ar-AA', 'bg-BG', 'ca-ES', 'cs-CZ', 'da-DK', 'de-DE', 'el-GR', 'es-ES', 'et-EE',
				'fa-IR', 'fi-FI', 'fr-FR', 'he-IL', 'hr-HR', 'hu-HU', 'it-IT', 'ja-JP', 'ko-KR', 'lv-LV', 'nb-NO', 'nl-NL',
				'pl-PL', 'pt-BR', 'ro-RO', 'ru-RU', 'sk-SK', 'sr-RS', 'sv-SE', 'tr-TR', 'uk-UA', 'zh-CN', 'zh-TW',
			];

			// English is bundled into the source therefore we don't have to load it.
			if (in_array($activeLanguageTag, $allowedLanguageTags))
			{
				$document->addScript($rootUri . '/media/com_eventbooking/assets/js/colorbox/i18n/jquery.colorbox-' . $activeLanguageTag . '.js');
			}

			$loaded = true;
		}

		if ($class == 'a.eb-modal')
		{
			$options = [
				'maxWidth'  => '80%',
				'maxHeight' => '80%',
			];
			$script  = 'Eb.jQuery(document).ready(function($){$("' . $class . '").colorbox(' . self::getJSObject($options) . ');});';
		}
		else
		{
			$options = [
				'iframe'     => $iframe,
				'fastIframe' => false,
				'inline'     => $inline,
				'width'      => $width,
				'height'     => $height,
				'scrolling'  => $scrolling,
			];
			$script  = 'Eb.jQuery(document).ready(function($){$(".' . $class . '").colorbox(' . self::getJSObject($options) . ');});';
		}

		Factory::getDocument()->addScriptDeclaration($script);
	}

	/**
	 * Convert an array to js object
	 *
	 * @param   array  $array
	 *
	 * @return string
	 */
	public static function getJSObject(array $array = [])
	{
		$object = '{';

		// Iterate over array to build objects
		foreach ((array) $array as $k => $v)
		{
			if (is_null($v))
			{
				continue;
			}

			if ($v === 'true')
			{
				$v = true;
			}

			if ($v === 'false')
			{
				$v = false;
			}

			if (is_bool($v))
			{
				$object .= ' ' . $k . ': ';
				$object .= ($v) ? 'true' : 'false';
				$object .= ',';
			}
			elseif (!is_array($v) && !is_object($v))
			{
				$object .= ' ' . $k . ': ';
				$object .= (is_numeric($v) || strpos($v, '\\') === 0) ? (is_numeric($v)) ? $v : substr($v, 1) : "'" . $v . "'";
				$object .= ',';
			}
			else
			{
				$object .= ' ' . $k . ': ' . self::getJSObject($v) . ',';
			}
		}

		if (substr($object, -1) == ',')
		{
			$object = substr($object, 0, -1);
		}

		$object .= '}';

		return $object;
	}

	/**
	 * validate form
	 */
	public static function validateForm()
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideJquery', 'validateForm'))
		{
			EventbookingHelperOverrideJquery::validateForm();

			return;
		}

		static $loaded = false;

		if (!$loaded)
		{
			$rootUri  = Uri::root(true);
			$document = Factory::getDocument();
			$document->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/validate/css/validationEngine.jquery.min.css');

			$languageItems = [
				'EB_VALIDATION_FIELD_REQUIRED',
				'EB_VALIDATION_CHECKBOX_REQUIRED',
				'EB_VALIDATION_BOTH_DATE_RANGE_FIELD_REQUIRED',
				'EB_VALIDATION_FIELD_MUST_EQUAL_TEST',
				'EB_VALIDATION_INVALID',
				'EB_VALIDATION_DATE_TIME_RANGE',
				'EB_VALIDATION_MINIMUM',
				'EB_CHARACTERS_REQUIRED',
				'EB_VALIDATION_MAXIMUM',
				'EB_VALIDATION_CHACTERS_ALLOWED',
				'EB_VALIDATION_GROUP_REQUIRED',
				'EB_VALIDATION_MIN',
				'EB_VALIDATION_MAX',
				'EB_VALIDATION_DATE_PRIOR_TO',
				'EB_VALIDATION_DATE_PAST',
				'EB_VALIDATION_MAXIMUM',
				'EB_VALIDATION_OPTION_ALLOW',
				'EB_VALIDATION_PLEASE_SELECT',
				'EB_VALIDATION_FIELDS_DO_NOT_MATCH',
				'EB_VALIDATION_INVALID_CREDIT_CARD_NUMBER',
				'EB_VALIDATION_INVALID_PHONE_NUMBER',
				'EB_VALIDATION_INVALID_EMAIL_ADDRESS',
				'EB_VALIDATION_NOT_A_VALID_INTEGER',
				'EB_VALIDATION_INVALID_FLOATING_DECIMAL_NUMBER',
				'EB_VALIDATION_INVALID_DATE',
				'EB_VALIDATION_INVALID_IP_ADDRESS',
				'EB_VALIDATION_INVALID_URL',
				'EB_VALIDATION_NUMBER_ONLY',
				'EB_VALIDATION_LETTERS_ONLY',
				'EB_VALIDATION_NO_SPECIAL_CHACTERS_ALLOWED',
				'EB_VALIDATION_INVALID_USERNAME',
				'EB_VALIDATION_INVALID_EMAIL',
				'EB_VALIDATION_INVALID_DATE',
				'EB_VALIDATION_EXPECTED_FORMAT',
			];

			EventbookingHelperHtml::addJSStrings($languageItems);

			$config = EventbookingHelper::getConfig();

			if ($config->multiple_booking)
			{
				$eventId = 0;
			}
			else
			{
				$eventId = Factory::getApplication()->input->getInt('event_id', 0);
			}

			$dateFormat  = $config->date_field_format ?: '%Y-%m-%d';
			$dateFormat  = str_replace('%', '', $dateFormat);
			$humanFormat = str_replace('Y', 'YYYY', $dateFormat);
			$humanFormat = str_replace('m', 'MM', $humanFormat);
			$humanFormat = str_replace('d', 'DD', $humanFormat);

			$separator          = '';
			$possibleSeparators = ['.', '-', '/'];

			foreach ($possibleSeparators as $possibleSeparator)
			{
				if (strpos($dateFormat, $possibleSeparator) !== false)
				{
					$separator = $possibleSeparator;
					break;
				}
			}

			$dateParts = explode($separator, $dateFormat);

			$yearIndex  = array_search('Y', $dateParts);
			$monthIndex = array_search('m', $dateParts);
			$dayIndex   = array_search('d', $dateParts);

			$regex = $dateFormat;
			$regex = str_replace($separator, '[\\' . $separator . ']', $regex);
			$regex = str_replace('d', '(0?[1-9]|[12][0-9]|3[01])', $regex);
			$regex = str_replace('Y', '(\d{4})', $regex);
			$regex = str_replace('m', '(0?[1-9]|1[012])', $regex);

			$document = Factory::getDocument();

			$document->addScriptDeclaration("
				var yearPartIndex = $yearIndex;
				var monthPartIndex = $monthIndex;
				var dayPartIndex = $dayIndex;
				var customDateFormat = '$dateFormat';
				var pattern = new RegExp(/^$regex$/);
			");

			$document->addScriptOptions('humanFormat', $humanFormat)
				->addScriptOptions('rootUri', $rootUri)
				->addScriptOptions('eventId', $eventId);

			if (EventbookingHelper::isJoomla4())
			{
				$files = [
					'media/com_eventbooking/assets/js/validate/js/jquery.validationEngine-lang.min.js',
					'media/com_eventbooking/assets/js/validate/js/j4.jquery.validationEngine.min.js',
				];
			}
			else
			{
				$files = [
					'media/com_eventbooking/assets/js/validate/js/jquery.validationEngine-lang.min.js',
					'media/com_eventbooking/assets/js/validate/js/jquery.validationEngine.min.js',
				];
			}

			EventbookingHelperHtml::addOverridableScript($files);
		}

		$loaded = true;
	}

	/**
	 * Equal Heights Plugin
	 * Equalize the heights of elements. Great for columns or any elements
	 * that need to be the same size (floats, etc).
	 *
	 * Version 1.0
	 * Updated 12/10/2008
	 *
	 * Copyright (c) 2008 Rob Glazebrook (cssnewbie.com)
	 *
	 * Usage: $(object).equalHeights([minHeight], [maxHeight]);
	 *
	 * Example 1: $(".cols").equalHeights(); Sets all columns to the same height.
	 * Example 2: $(".cols").equalHeights(400); Sets all cols to at least 400px tall.
	 * Example 3: $(".cols").equalHeights(100,300); Cols are at least 100 but no more
	 * than 300 pixels tall. Elements with too much content will gain a scrollbar.
	 */
	public static function equalHeights()
	{
		static $loaded = false;

		if ($loaded)
		{
			return;
		}


		$script = 'Eb.jQuery(function($) { $.fn.equalHeights = function(minHeight, maxHeight) { tallest = (minHeight) ? minHeight : 0;this.each(function() {if($(this).height() > tallest) {tallest = $(this).height();}});if((maxHeight) && tallest > maxHeight) tallest = maxHeight;return this.each(function() {$(this).height(tallest).css("overflow","auto");});}});';
		Factory::getDocument()->addScriptDeclaration($script);

		$loaded = true;
	}


	/**
	 * Load colorbox script for displaying map on popup
	 *
	 * @return void
	 */
	public static function loadColorboxForMap()
	{
		static $loaded = false;

		if ($loaded)
		{
			return;
		}

		$config = EventbookingHelper::getConfig();

		$width  = (int) $config->get('map_width', 800) ?: 800;
		$height = (int) $config->get('map_height', 600) ?: 800;

		$deviceType = EventbookingHelper::getDeviceType();

		if ($deviceType == 'mobile')
		{
			static::colorbox('eb-colorbox-map', '100%', $height . 'px', 'true', 'false');
		}
		else
		{
			static::colorbox('eb-colorbox-map', $width . 'px', $height . 'px', 'true', 'false');
		}

		$loaded = true;
	}
}
