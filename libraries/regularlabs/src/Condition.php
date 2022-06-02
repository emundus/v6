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

use DateTimeZone;
use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Api\ConditionInterface;
use RegularLabs\Library\CacheNew as Cache;
use RegularLabs\Library\ParametersNew as Parameters;

/**
 * Class Condition
 * @package RegularLabs\Library
 */
abstract class Condition
	implements ConditionInterface
{
	static  $_request     = null;
	public  $article      = null;
	public  $date         = null;
	public  $db           = null;
	public  $include_type = null;
	public  $module       = null;
	public  $params       = null;
	public  $request      = null;
	public  $selection    = null;
	private $dates        = [];
	private $timezone     = null;

	public function __construct($condition = [], $article = null, $module = null)
	{
		$tz         = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
		$this->date = JFactory::getDate()->setTimeZone($tz);

		$this->request = self::getRequest();

		$this->db = JFactory::getDbo();

		$this->selection    = $condition->selection ?? [];
		$this->params       = $condition->params ?? [];
		$this->include_type = $condition->include_type ?? 'none';

		if (is_array($this->selection))
		{
			$this->selection = ArrayHelper::clean($this->selection);
		}

		$this->article = $article;
		$this->module  = $module;
	}

	private function getRequest()
	{
		$return_early = ! is_null(self::$_request);

		$app   = JFactory::getApplication();
		$input = $app->input;

		$id = $input->get(
			'a_id',
			$input->get('id', [0], 'array'),
			'array'
		);

		self::$_request = (object) [
			'idname' => 'id',
			'option' => $input->get('option'),
			'view'   => $input->get('view'),
			'task'   => $input->get('task'),
			'layout' => $input->getString('layout'),
			'Itemid' => $this->getItemId(),
			'id'     => (int) $id[0],
		];

		switch (self::$_request->option)
		{
			case 'com_categories':
				$extension              = $input->getCmd('extension');
				self::$_request->option = $extension ?: 'com_content';
				self::$_request->view   = 'category';
				break;

			case 'com_breezingforms':
				if (self::$_request->view == 'article')
				{
					self::$_request->option = 'com_content';
				}
				break;
		}

		$this->initRequest(self::$_request);

		if ( ! self::$_request->id)
		{
			$cid                = $input->get('cid', [0], 'array');
			self::$_request->id = (int) $cid[0];
		}

		if ($return_early)
		{
			return self::$_request;
		}

		// if no id is found, check if menuitem exists to get view and id
		if (Document::isClient('site')
			&& ( ! self::$_request->option || ! self::$_request->id)
		)
		{
			$menuItem = empty(self::$_request->Itemid)
				? $app->getMenu('site')->getActive()
				: $app->getMenu('site')->getItem(self::$_request->Itemid);

			if ($menuItem)
			{
				if ( ! self::$_request->option)
				{
					self::$_request->option = (empty($menuItem->query['option'])) ? null : $menuItem->query['option'];
				}

				self::$_request->view = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
				self::$_request->task = (empty($menuItem->query['task'])) ? null : $menuItem->query['task'];

				if ( ! self::$_request->id)
				{
					self::$_request->id = (empty($menuItem->query[self::$_request->idname])) ? $menuItem->params->get(self::$_request->idname) : $menuItem->query[self::$_request->idname];
				}
			}

			unset($menuItem);
		}

		return self::$_request;
	}

	private function getItemId()
	{
		$id = JFactory::getApplication()->input->getInt('Itemid', 0);

		if ($id)
		{
			return $id;
		}

		$menu = $this->getActiveMenu();

		return $menu->id ?? 0;
	}

	public function initRequest(&$request)
	{
	}

	private function getActiveMenu()
	{
		$menu = JFactory::getApplication()->getMenu()->getActive();

		if (empty($menu->id))
		{
			return false;
		}

		return $this->getMenuById($menu->id);
	}

	private function getMenuById($id = 0)
	{
		$menu = JFactory::getApplication()->getMenu()->getItem($id);

		if (empty($menu->id))
		{
			return false;
		}

		if ($menu->type == 'alias')
		{
			$params = $menu->getParams();

			return $this->getMenuById($params->get('aliasoptions'));
		}

		return $menu;
	}

	public function beforePass()
	{
	}

	public function getDateString($date = '')
	{
		$date = $this->getDate($date);
		$date = strtotime($date->format('Y-m-d H:i:s', true));

		return $date;
	}

	public function getDate($date = '')
	{
		$date = Date::fix($date);

		$id = 'date_' . $date;

		if (isset($this->dates[$id]))
		{
			return $this->dates[$id];
		}

		$this->dates[$id] = JFactory::getDate($date);

		if (empty($this->params->ignore_time_zone))
		{
			$this->dates[$id]->setTimeZone($this->getTimeZone());
		}

		return $this->dates[$id];
	}

	private function getTimeZone()
	{
		if ( ! is_null($this->timezone))
		{
			return $this->timezone;
		}

		$this->timezone = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

		return $this->timezone;
	}

	public function getMenuItemParams($id = 0)
	{
		$cache = new Cache([__METHOD__, $id]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$query = $this->db->getQuery(true)
			->select('m.params')
			->from('#__menu AS m')
			->where('m.id = ' . (int) $id);
		$this->db->setQuery($query);
		$params = $this->db->loadResult();

		return $cache->set(Parameters::getObjectFromRegistry($params));
	}

	public function getNow()
	{
		return strtotime($this->date->format('Y-m-d H:i:s', true));
	}

	public function getParentIds($id = 0, $table = 'menu', $parent = 'parent_id', $child = 'id')
	{
		if ( ! $id)
		{
			return [];
		}

		$cache = new Cache([__METHOD__, $id, $table, $parent, $child]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$parent_ids = [];

		while ($id)
		{
			$query = $this->db->getQuery(true)
				->select('t.' . $parent)
				->from('#__' . $table . ' as t')
				->where('t.' . $child . ' = ' . (int) $id);
			$this->db->setQuery($query);
			$id = $this->db->loadResult();

			// Break if no parent is found or parent already found before for some reason
			if ( ! $id || in_array($id, $parent_ids))
			{
				break;
			}

			$parent_ids[] = $id;
		}

		return $cache->set($parent_ids);
	}

	public function init()
	{
	}

	public function passByPageType($option, $selection = [], $include_type = 'all', $add_view = false, $get_task = false, $get_layout = true)
	{
		if ($this->request->option != $option)
		{
			return $this->_(false, $include_type);
		}

		if ($get_task && $this->request->task && $this->request->task != $this->request->view && $this->request->task != 'default')
		{
			$pagetype = ($add_view ? $this->request->view . '_' : '') . $this->request->task;

			return $this->passSimple($pagetype, $selection, $include_type);
		}

		if ($get_layout && $this->request->layout && $this->request->layout != $this->request->view && $this->request->layout != 'default')
		{
			$pagetype = ($add_view ? $this->request->view . '_' : '') . $this->request->layout;

			return $this->passSimple($pagetype, $selection, $include_type);
		}

		return $this->passSimple($this->request->view, $selection, $include_type);
	}

	public function _($pass = true, $include_type = null)
	{
		$include_type = $include_type ?: $this->include_type;

		return $pass ? ($include_type == 'include') : ($include_type == 'exclude');
	}

	public function passSimple($values = '', $caseinsensitive = false, $include_type = null, $selection = null)
	{
		$values       = $this->makeArray($values);
		$include_type = $include_type ?: $this->include_type;
		$selection    = $selection ?: $this->selection;

		$pass = false;
		foreach ($values as $value)
		{
			if ($caseinsensitive)
			{
				if (in_array(strtolower($value), array_map('strtolower', $selection)))
				{
					$pass = true;
					break;
				}

				continue;
			}

			if (in_array($value, $selection))
			{
				$pass = true;
				break;
			}
		}

		return $this->_($pass, $include_type);
	}

	public function makeArray($array = '', $delimiter = ',', $trim = false)
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

		$array = $this->mixedDataToArray($array, $delimiter);

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

	private function mixedDataToArray($array = '', $onlycommas = false)
	{
		if ( ! is_array($array))
		{
			$delimiter = ($onlycommas || strpos($array, '|') === false) ? ',' : '|';

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

		if (count($array) === 1 && strpos($array[0], ',') !== false)
		{
			return explode(',', $array[0]);
		}

		return $array;
	}

	public function passInRange($value = '', $include_type = null, $selection = null)
	{
		$include_type = $include_type ?: $this->include_type;

		if (empty($value))
		{
			return $this->_(false, $include_type);
		}

		$selections = $this->makeArray($selection ?: $this->selection);

		$pass = false;
		foreach ($selections as $selection)
		{
			if (empty($selection))
			{
				continue;
			}

			if (strpos($selection, '-') === false)
			{
				if ((int) $value == (int) $selection)
				{
					$pass = true;
					break;
				}

				continue;
			}

			[$min, $max] = explode('-', $selection, 2);

			if ((int) $value >= (int) $min && (int) $value <= (int) $max)
			{
				$pass = true;
				break;
			}
		}

		return $this->_($pass, $include_type);
	}

	public function passItemByType(&$pass, $type = '', $data = null)
	{
		$pass_type = ! empty($data) ? $this->{'pass' . $type}($data) : $this->{'pass' . $type}();

		if ($pass_type === null)
		{
			return true;
		}

		$pass = $pass_type;

		return $pass;
	}
}
