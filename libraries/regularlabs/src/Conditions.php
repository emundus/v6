<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use RegularLabs\Library\CacheNew as Cache;

jimport('joomla.filesystem.file');

/**
 * Class Conditions
 * @package RegularLabs\Library
 */
class Conditions
{
	static $installed_extensions = null;
	static $params               = null;

	public static function getConditionsFromParams(&$params)
	{
		$cache = new Cache([__METHOD__, $params]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		self::renameParamKeys($params);

		$types = [];

		foreach (self::getTypes() as $id => $type)
		{
			if (empty($params->conditions[$id]))
			{
				continue;
			}

			$types[$type] = (object) [
				'include_type' => $params->conditions[$id],
				'selection'    => [],
				'params'       => (object) [],
			];

			if (isset($params->conditions[$id . '_selection']))
			{
				$types[$type]->selection = self::getSelection($params->conditions[$id . '_selection'], $type);
			}

			self::addParams($types[$type], $type, $id, $params);
		}

		return $cache->set($types);
	}

	private static function renameParamKeys(&$params)
	{
		$params->conditions = $params->conditions ?? [];

		foreach ($params as $key => $value)
		{
			if (strpos($key, 'condition_') === false && strpos($key, 'assignto_') === false)
			{
				continue;
			}

			$new_key                      = substr($key, strpos($key, '_') + 1);
			$params->conditions[$new_key] = $value;

			unset($params->{$key});
		}
	}

	private static function getTypes($only_types = [])
	{
		$types = [
			'menuitems'             => 'Menu',
			'homepage'              => 'Homepage',
			'date'                  => 'Date.Date',
			'seasons'               => 'Date.Season',
			'months'                => 'Date.Month',
			'days'                  => 'Date.Day',
			'time'                  => 'Date.Time',
			'accesslevels'          => 'User.Accesslevel',
			'usergrouplevels'       => 'User.Grouplevel',
			'users'                 => 'User.User',
			'languages'             => 'Language',
			'ips'                   => 'Ip',
			'geocontinents'         => 'Geo.Continent',
			'geocountries'          => 'Geo.Country',
			'georegions'            => 'Geo.Region',
			'geopostalcodes'        => 'Geo.Postalcode',
			'templates'             => 'Template',
			'urls'                  => 'Url',
			'devices'               => 'Agent.Device',
			'os'                    => 'Agent.Os',
			'browsers'              => 'Agent.Browser',
			'components'            => 'Component',
			'tags'                  => 'Tag',
			'contentpagetypes'      => 'Content.Pagetype',
			'cats'                  => 'Content.Category',
			'articles'              => 'Content.Article',
			'easyblogpagetypes'     => 'Easyblog.Pagetype',
			'easyblogcats'          => 'Easyblog.Category',
			'easyblogtags'          => 'Easyblog.Tag',
			'easyblogitems'         => 'Easyblog.Item',
			'flexicontentpagetypes' => 'Flexicontent.Pagetype',
			'flexicontenttags'      => 'Flexicontent.Tag',
			'flexicontenttypes'     => 'Flexicontent.Type',
			'form2contentprojects'  => 'Form2content.Project',
			'k2pagetypes'           => 'K2.Pagetype',
			'k2cats'                => 'K2.Category',
			'k2tags'                => 'K2.Tag',
			'k2items'               => 'K2.Item',
			'zoopagetypes'          => 'Zoo.Pagetype',
			'zoocats'               => 'Zoo.Category',
			'zooitems'              => 'Zoo.Item',
			'akeebasubspagetypes'   => 'Akeebasubs.Pagetype',
			'akeebasubslevels'      => 'Akeebasubs.Level',
			'hikashoppagetypes'     => 'Hikashop.Pagetype',
			'hikashopcats'          => 'Hikashop.Category',
			'hikashopproducts'      => 'Hikashop.Product',
			'mijoshoppagetypes'     => 'Mijoshop.Pagetype',
			'mijoshopcats'          => 'Mijoshop.Category',
			'mijoshopproducts'      => 'Mijoshop.Product',
			'redshoppagetypes'      => 'Redshop.Pagetype',
			'redshopcats'           => 'Redshop.Category',
			'redshopproducts'       => 'Redshop.Product',
			'virtuemartpagetypes'   => 'Virtuemart.Pagetype',
			'virtuemartcats'        => 'Virtuemart.Category',
			'virtuemartproducts'    => 'Virtuemart.Product',
			'cookieconfirm'         => 'Cookieconfirm',
			'php'                   => 'Php',
		];

		if (empty($only_types))
		{
			return $types;
		}

		return array_intersect_key($types, array_flip($only_types));
	}

	private static function getSelection($selection, $type = '')
	{
		if (in_array($type, self::getNotArrayTextAreaTypes()))
		{
			return $selection;
		}

		$delimiter = in_array($type, self::getTextAreaTypes()) ? "\n" : ',';

		return self::makeArray($selection, $delimiter);
	}

	private static function addParams(&$object, $type, $id, &$params)
	{
		$extra_params = [];
		$array_params = [];
		$includes     = [];

		switch ($type)
		{
			case 'Menu':
				$extra_params = ['inc_children', 'inc_noitemid'];
				break;

			case 'Date.Date':
				$extra_params = ['publish_up', 'publish_down', 'recurring', 'ignore_time_zone'];
				break;

			case 'Date.Season':
				$extra_params = ['hemisphere'];
				break;

			case 'Date.Time':
				$extra_params = ['publish_up', 'publish_down'];
				break;

			case 'User.Grouplevel':
				$extra_params = ['inc_children'];
				break;

			case 'Url':
				if (is_array($object->selection))
				{
					$object->selection = implode("\n", $object->selection);
				}
				if (isset($params->conditions['urls_selection_sef']))
				{
					$object->selection .= "\n" . $params->conditions['urls_selection_sef'];
				}
				$object->selection             = trim(str_replace("\r", '', $object->selection));
				$object->selection             = explode("\n", $object->selection);
				$object->params->casesensitive = $params->conditions['urls_casesensitive'] ?? false;
				$object->params->regex         = $params->conditions['urls_regex'] ?? false;
				break;

			case 'Agent.Browser':
				if ( ! empty($params->conditions['mobile_selection']))
				{
					$object->selection = array_merge(self::makeArray($object->selection), self::makeArray($params->conditions['mobile_selection']));
				}
				if ( ! empty($params->conditions['searchbots_selection']))
				{
					$object->selection = array_merge($object->selection, self::makeArray($params->conditions['searchbots_selection']));
				}
				break;

			case 'Tag':
				$extra_params = ['inc_children'];
				break;

			case 'Content.Category':
				$extra_params = ['inc_children'];
				$includes     = ['cats' => 'categories', 'arts' => 'articles', 'others'];
				break;

			case 'Easyblog.Category':
			case 'K2.Category':
			case 'Hikashop.Category':
			case 'Mijoshop.Category':
			case 'Redshop.Category':
			case 'Virtuemart.Category':
				$extra_params = ['inc_children'];
				$includes     = ['cats' => 'categories', 'items'];
				break;

			case 'Zoo.Category':
				$extra_params = ['inc_children'];
				$includes     = ['apps', 'cats' => 'categories', 'items'];
				break;

			case 'Easyblog.Tag':
			case 'Flexicontent.Tag':
			case 'K2.Tag':
				$includes = ['tags', 'items'];
				break;

			case 'Content.Article':
				$extra_params = [
					'featured',
					'content_keywords', 'keywords' => 'meta_keywords',
					'authors',
					'date', 'date_comparison', 'date_type', 'date_date', 'date_from', 'date_to',
					'fields',
				];
				break;

			case 'K2.Item':
				$extra_params = ['content_keywords', 'meta_keywords', 'authors'];
				break;

			case 'Easyblog.Item':
				$extra_params = ['content_keywords', 'authors'];
				break;

			case 'Zoo.Item':
				$extra_params = ['authors'];
				break;

			default:
				break;
		}

		if (in_array($type, self::getMatchAllTypes()))
		{
			$extra_params[] = 'match_all';

			if (count($object->selection) == 1 && strpos($object->selection[0], '+') !== false)
			{
				$object->selection = ArrayHelper::toArray($object->selection[0], '+');
				$params->match_all = true;
			}
		}

		if (empty($extra_params) && empty($array_params) && empty($includes))
		{
			return;
		}

		self::addParamsByType($object, $id, $params, $extra_params, $array_params, $includes);
	}

	private static function getNotArrayTextAreaTypes()
	{
		return [
			'Php',
		];
	}

	private static function getTextAreaTypes()
	{
		return [
			'Ip',
			'Url',
			'Php',
		];
	}

	private static function makeArray($array = '', $delimiter = ',', $trim = true)
	{
		if (empty($array))
		{
			return [];
		}

		$cache = new Cache([__METHOD__, $array, $delimiter, $trim]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$array = self::mixedDataToArray($array, $delimiter);

		if (empty($array))
		{
			return $array;
		}

		if ( ! $trim)
		{
			return $array;
		}

		foreach ($array as $k => $v)
		{
			if ( ! is_string($v))
			{
				continue;
			}

			$array[$k] = trim($v);
		}

		return $cache->set($array);
	}

	public static function getMatchAllTypes()
	{
		return [
			'User.Grouplevel',
			'Tag',
		];
	}

	private static function addParamsByType(&$object, $id, $params, $extra_params = [], $array_params = [], $includes = [])
	{
		foreach ($extra_params as $key => $param)
		{
			$key                      = is_numeric($key) ? $param : $key;
			$object->params->{$param} = self::getTypeParamValue($id, $params, $key);
		}

		foreach ($array_params as $key => $param)
		{
			$key                      = is_numeric($key) ? $param : $key;
			$object->params->{$param} = self::getTypeParamValue($id, $params, $key, true);
		}

		if (empty($includes))
		{
			return;
		}

		$incs = self::getTypeParamValue($id, $params, 'inc', true);

		if (empty($incs) && ! empty($params->conditions[$id]) && ! isset($params->conditions[$id . '_inc']))
		{
			$incs = ['inc_items', 'inc_arts', 'inc_cats', 'inc_others', 'x'];
		}

		foreach ($includes as $key => $param)
		{
			$key                               = is_numeric($key) ? $param : $key;
			$object->params->{'inc_' . $param} = in_array('inc_' . $key, $incs) ? 1 : 0;
		}

		unset($object->params->inc);
	}

	private static function mixedDataToArray($array = '', $delimiter = ',')
	{
		if ( ! is_array($array))
		{
			return explode($delimiter, $array);
		}

		if (empty($array))
		{
			return $array;
		}

		if (isset($array[0]) && is_array($array[0]))
		{
			return $array[0];
		}

		if (count($array) === 1 && strpos($array[0], $delimiter) !== false)
		{
			return explode($delimiter, $array[0]);
		}

		return $array;
	}

	private static function getTypeParamValue($id, $params, $key, $is_array = false)
	{
		if (isset($params->conditions) && isset($params->conditions[$id . '_' . $key]))
		{
			return $params->conditions[$id . '_' . $key];
		}

		if (isset($params->{'assignto_' . $id . '_' . $key}))
		{
			return $params->{'assignto_' . $id . '_' . $key};
		}

		if (isset($params->{$key}))
		{
			return $params->{$key};
		}

		if ($is_array)
		{
			return [];
		}

		return '';
	}

	public static function getConditionsFromTagAttributes(&$attributes, $only_types = [])
	{
		$conditions = [];

		PluginTag::replaceKeyAliases($attributes, self::getTypeAliases(), true);
		$types = self::getTypes($only_types);

		if (empty($types))
		{
			return $conditions;
		}

		$type_params = [];

		foreach ($attributes as $type_param => $value)
		{
			if (strpos($type_param, '_') === false)
			{
				continue;
			}

			[$type, $param] = explode('_', $type_param, 2);

			$condition_type = self::getType($type, $only_types);

			if ( ! $condition_type)
			{
				continue;
			}

			$type_params[$type_param] = $value;
			unset($attributes->{$type_param});
		}

		foreach ($attributes as $type => $value)
		{
			if (empty($value))
			{
				continue;
			}

			$condition_type = self::getType($type, $only_types);

			if ( ! $condition_type)
			{
				continue;
			}

			$value = html_entity_decode($value);

			$params             = self::getDefaultParamsByType($condition_type, $type);
			$params->conditions = $type_params;

			$reverse = false;

			$selection = self::getSelectionFromTagAttribute($condition_type, $value, $params, $reverse);

			$condition = (object) [
				'include_type' => $reverse ? 2 : 1,
				'selection'    => $selection,
				'params'       => (object) [],
			];

			self::addParams($condition, $condition_type, $type, $params);

			$conditions[$condition_type] = $condition;
		}

		return $conditions;
	}

	private static function getTypeAliases()
	{
		return [
			'matching_method'  => ['method'],
			'menuitems'        => ['menu'],
			'homepage'         => ['home'],
			'date'             => ['daterange'],
			'seasons'          => [''],
			'months'           => [''],
			'days'             => [''],
			'time'             => [''],
			'accesslevels'     => ['access'],
			'usergrouplevels'  => ['usergroups', 'groups'],
			'users'            => [''],
			'languages'        => ['langs'],
			'ips'              => ['ipaddress', 'ipaddresses'],
			'geocontinents'    => ['continents'],
			'geocountries'     => ['countries'],
			'georegions'       => ['regions'],
			'geopostalcodes'   => ['postalcodes', 'postcodes'],
			'templates'        => [''],
			'urls'             => [''],
			'devices'          => [''],
			'os'               => [''],
			'browsers'         => [''],
			'components'       => [''],
			'tags'             => [''],
			'contentpagetypes' => ['pagetypes'],
			'cats'             => ['categories', 'category'],
			'articles'         => [''],
			'php'              => [''],
		];
	}

	private static function getType(&$type, $only_types = [])
	{
		$types = self::getTypes($only_types);

		if (isset($types[$type]))
		{
			return $types[$type];
		}

		// Make it plural
		$type = rtrim($type, 's') . 's';

		if (isset($types[$type]))
		{
			return $types[$type];
		}

		// Replace incorrect plural endings
		$type = str_replace('ys', 'ies', $type);

		if (isset($types[$type]))
		{
			return $types[$type];
		}

		return false;
	}

	private static function getDefaultParamsByType($condition_type, $type)
	{
		switch ($condition_type)
		{
			case 'Content.Category':
				return (object) [
					'assignto_' . $type . '_inc' => [
						'inc_cats',
						'inc_arts',
					],
				];

			case 'Easyblog.Category':
			case 'K2.Category':
			case 'Zoo.Category':
			case 'Hikashop.Category':
			case 'Mijoshop.Category':
			case 'Redshop.Category':
			case 'Virtuemart.Category':
				return (object) [
					'assignto_' . $type . '_inc' => [
						'inc_cats',
						'inc_items',
					],
				];

			default:
				return (object) [];
		}
	}

	private static function getSelectionFromTagAttribute($type, $value, &$params, &$reverse)
	{
		if ($type == 'Date.Date')
		{
			$value = str_replace('from', '', $value);
			$dates = explode(' - ', str_replace('to', ' - ', $value));

			$params->ignore_time_zone = true;

			if ( ! empty($dates[0]))
			{
				$params->publish_up = date('Y-m-d H:i:s', strtotime($dates[0]));
			}

			if ( ! empty($dates[1]))
			{
				$params->publish_down = date('Y-m-d H:i:s', strtotime($dates[1]));
			}

			return [];
		}

		if ($type == 'Date.Time')
		{
			$value = str_replace('from', '', $value);
			$dates = explode(' - ', str_replace('to', ' - ', $value));

			$params->publish_up   = $dates[0];
			$params->publish_down = $dates[1] ?? $dates[0];

			return [];
		}

		if (in_array($type, self::getTextAreaTypes()))
		{
			$value = Html::convertWysiwygToPlainText($value);
		}

		if (strpos($value, '!NOT!') === 0)
		{
			$reverse = true;
			$value   = substr($value, 5);
		}

		if ( ! in_array($type, self::getNotArrayTextAreaTypes()))
		{
			$value = str_replace('[[:COMMA:]]', ',', str_replace(',', '[[:SPLIT:]]', str_replace('\\,', '[[:COMMA:]]', $value)));
			$value = explode('[[:SPLIT:]]', $value);
		}

		return $value;
	}

	public static function hasConditions($conditions)
	{
		if (empty($conditions))
		{
			return false;
		}

		foreach (self::getTypes() as $type)
		{
			if (isset($conditions[$type]) && isset($conditions[$type]->include_type) && $conditions[$type]->include_type)
			{
				return true;
			}
		}

		return false;
	}

	public static function pass($conditions, $matching_method = 'all', $article = null, $module = null)
	{
		if (empty($conditions))
		{
			return true;
		}

		$article_id      = $article->id ?? '';
		$module_id       = $module->id ?? '';
		$matching_method = in_array($matching_method, ['any', 'or']) ? 'any' : 'all';

		$cache = new Cache([__METHOD__, $article_id, $module_id, $matching_method, $conditions]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$pass = (bool) ($matching_method == 'all');

		foreach (self::getTypes() as $type)
		{
			// Break if not passed and matching method is ALL
			// Or if passed and matching method is ANY
			if (
				( ! $pass && $matching_method == 'all')
				|| ($pass && $matching_method == 'any')
			)
			{
				break;
			}

			if ( ! isset($conditions[$type]))
			{
				continue;
			}

			$pass = self::passByType($conditions[$type], $type, $article, $module);
		}

		return $cache->set($pass);
	}

	private static function passByType($condition, $type, $article = null, $module = null)
	{
		$article_id = $article->id ?? '';
		$module_id  = $module->id ?? '';

		$cache = new Cache([__METHOD__, $type, $article_id, $module_id, $condition]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		self::initParametersByType($condition, $type);

		$cache = new Cache([__METHOD__, $type, $article_id, $module_id, $condition]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$pass = false;

		switch ($condition->include_type)
		{
			case 'all':
				$pass = true;
				break;

			case 'none':
				$pass = false;
				break;

			default:
				if ( ! file_exists(__DIR__ . '/Condition/' . $condition->class_name . '.php'))
				{
					break;
				}

				$className = '\\RegularLabs\\Library\\Condition\\' . $condition->class_name;

				$class = new $className($condition, $article, $module);

				$class->beforePass();

				$pass = $class->pass();

				break;
		}

		return $cache->set($pass);
	}

	private static function initParametersByType(&$params, $type = '')
	{
		$params->class_name = str_replace('.', '', $type);

		$params->include_type = self::getConditionState($params->include_type);
	}

	private static function getConditionState($include_type)
	{
		switch ($include_type . '')
		{
			case 1:
			case 'include':
				return 'include';

			case 2:
			case 'exclude':
				return 'exclude';

			case 3:
			case -1:
			case 'none':
				return 'none';

			default:
				return 'all';
		}
	}
}
