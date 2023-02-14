<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('_JEXEC') || die;

use DateTimeZone;
use FOF40\Container\Container;
use FOF40\View\DataView\Raw;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class Html
{
	public static function localisedDate($value, $format = 'DATE_FORMAT_LC2', $localise = true, $localTZ = true)
	{
		static $tz = null;
		$container = Container::getInstance('com_admintools');

		if (is_null($tz))
		{
			$timezone = $container->platform->getUser()->getParam('timezone', $container->platform->getConfig()->get('offset', 'GMT'));
			$tz       = new DateTimeZone($timezone);
		}

		$date = $container->platform->getDate($value, 'UTC');
		$date->setTimezone($tz);

		if ($localise)
		{
			$format = Text::_($format);
		}

		return $date->format($format, $localTZ);
	}

	public static function IpLookup($value)
	{
		$ip      = htmlspecialchars($value, ENT_COMPAT);
		$cparams = Storage::getInstance();
		$iplink  = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');

		$link = str_replace('{ip}', $ip, $iplink);

		$html = '<a href="' . $link . '" target="_blank" class="akeeba-btn--primary--small"><span class="akion-search"></span></a>&nbsp;';
		$html .= $ip;

		return $html;
	}

	public static function language($value)
	{
		static $languages;

		if (!$languages)
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__languages'));

			$languages = $db->setQuery($query)->loadObjectList('lang_code');
		}

		// Unknown value
		if ($value != '*' && !isset($languages[$value]))
		{
			return '';
		}

		$lang = Text::_('JALL');

		if (isset($languages[$value]))
		{
			$lang = $languages[$value]->title;
		}

		return '<span>' . $lang . '</span>';
	}
}
