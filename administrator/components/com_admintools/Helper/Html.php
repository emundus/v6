<?php
/**
 * @package   AdminTools
 * @copyright 2010-2018 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

use FOF30\Container\Container;
use FOF30\View\DataView\DataViewInterface;
use FOF30\View\DataView\Raw;
use JText;

defined('_JEXEC') or die;

class Html
{
	public static function localisedDate($value, $format = 'DATE_FORMAT_LC2', $localise = true, $localTZ = true)
	{
		static $tz = null;
		$container = Container::getInstance('com_admintools');

		if (is_null($tz))
		{
			$timezone = $container->platform->getUser()->getParam('timezone', $container->platform->getConfig()->get('offset', 'GMT'));
			$tz = new \DateTimeZone($timezone);
		}

		$date = $container->platform->getDate($value, 'UTC');
		$date->setTimezone($tz);

		if ($localise)
		{
			$format = \JText::_($format);
		}

		return $date->format($format, $localTZ);
	}

	public static function IpLookup($value)
	{
		$ip      = htmlspecialchars($value, ENT_COMPAT);
		$cparams = Storage::getInstance();
		$iplink  = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');

		$link = str_replace('{ip}', $ip, $iplink);

		$html = '<a href="'.$link.'" target="_blank" class="akeeba-btn--primary--small"><span class="akion-search"></span></a>&nbsp;';
		$html .= $ip;

		return $html;
	}

	public static function language($value)
	{
		static $languages;

		if (!$languages)
		{
			$db = \JFactory::getDbo();

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

		$lang = \JText::_('JALL');

		if (isset($languages[$value]))
		{
			$lang = $languages[$value]->title;
		}

		return '<span>'.$lang.'</span>';
	}

	public static function ordering(Raw $view, $orderingField, $orderingValue)
	{
		$ordering = $view->getLists()->order == $orderingField;
		$class = 'input-mini';
		$icon = 'icon-menu';

		// Default inactive ordering
		$html  = '<span class="sortable-handler inactive" >';
		$html .= '<span class="' . $icon . '"></span>';
		$html .= '</span>';

		// The modern drag'n'drop method
		if ($view->getPerms()->editstate)
		{
			$disableClassName = '';
			$disabledLabel = '';

			// DO NOT REMOVE! It will initialize Joomla libraries and javascript functions
			$hasAjaxOrderingSupport = $view->hasAjaxOrderingSupport();

			if (!$hasAjaxOrderingSupport['saveOrder'])
			{
				$disabledLabel = JText::_('JORDERINGDISABLED');
				$disableClassName = 'inactive tip-top';
			}

			$orderClass = $ordering ? 'order-enabled' : 'order-disabled';

			$html  = '<div class="' . $orderClass . '">';
			$html .= 	'<span class="sortable-handler ' . $disableClassName . '" title="' . $disabledLabel . '" rel="tooltip">';
			$html .= 		'<span class="' . $icon . '"></span>';
			$html .= 	'</span>';

			if ($ordering)
			{
				$joomla35IsBroken = version_compare(JVERSION, '3.5.0', 'ge') ? 'style="display: none"': '';

				$html .= '<input type="text" name="order[]" ' . $joomla35IsBroken . ' size="5" class="' . $class . ' text-area-order" value="' . $orderingValue . '" />';
			}

			$html .= '</div>';
		}

		return $html;
	}
}
