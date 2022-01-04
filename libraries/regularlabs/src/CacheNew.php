<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Cache\CacheController as JCacheController;
use Joomla\CMS\Factory as JFactory;

/**
 * Class CacheNew
 * @package RegularLabs\Library
 */
class CacheNew
{
	/**
	 * @var array
	 */
	static $cache = [];
	/**
	 * @var [JCacheController]
	 */
	private $file_cache_controllers = [];
	/**
	 * @var bool
	 */
	private $force_caching = true;
	/**
	 * @var string
	 */
	private $group;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var int
	 */
	private $time_to_life_in_seconds = 0;
	/**
	 * @var bool
	 */
	private $use_files = false;

	/**
	 * @param $id
	 */
	public function __construct($id, $group = 'regularlabs')
	{
		if ( ! is_string($id))
		{
			$id = json_encode($id);
		}

		$this->id    = md5($id);
		$this->group = $group;
	}

	/**
	 * @return bool
	 */
	public function exists()
	{
		if ( ! $this->use_files)
		{
			return $this->existsMemory();
		}

		return $this->existsMemory() || $this->existsFile();
	}

	/**
	 * @return null|mixed
	 */
	public function get()
	{
		return $this->use_files
			? $this->getFile()
			: $this->getMemory();
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	public function set($data)
	{
		return $this->use_files
			? $this->setFile($data)
			: $this->setMemory($data);
	}

	/**
	 * @param int  $time_to_life_in_minutes
	 * @param bool $force_caching
	 *
	 * @return $this
	 */
	public function useFiles($time_to_life_in_minutes = 0, $force_caching = true)
	{
		$this->use_files = true;

		// convert ttl to minutes
		$this->time_to_life_in_seconds = $time_to_life_in_minutes * 60;

		$this->force_caching = $force_caching;

		return $this;
	}

	/**
	 * @return bool
	 */
	private function existsFile()
	{
		if (JFactory::getConfig()->get('debug') || JFactory::getApplication()->input->get('debug'))
		{
			return false;
		}

		return $this->getFileCache()->contains($this->id);
	}

	/**
	 * @return bool
	 */
	private function existsMemory()
	{
		return isset(static::$cache[$this->id]);
	}

	/**
	 * @return false|mixed
	 * @throws Exception
	 */
	private function getFile()
	{
		if ($this->existsMemory())
		{
			return $this->getMemory();
		}

		$data = $this->getFileCache()->get($this->id);

		$this->setMemory($data);

		return $data;
	}

	// Get the cached object from the Joomla cache

	/**
	 * @return JCacheController
	 */
	private function getFileCache()
	{
		$id = json_encode([$this->group, $this->time_to_life_in_seconds, $this->force_caching]);

		if (isset($this->file_cache_controllers[$id]))
		{
			return $this->file_cache_controllers[$id];
		}

		$cache = JFactory::getCache($this->group, 'output');

		if ($this->time_to_life_in_seconds)
		{
			// convert ttl to minutes
			$cache->setLifeTime($this->time_to_life_in_seconds * 60);
		}

		if ($this->force_caching)
		{
			$cache->setCaching(true);
		}

		$this->file_cache_controllers[$id] = $cache;

		return $this->file_cache_controllers[$id];
	}

	/**
	 * @return null|mixed
	 */
	private function getMemory()
	{
		if ( ! $this->existsMemory())
		{
			return null;
		}

		$data = static::$cache[$this->id];

		return is_object($data) ? clone $data : $data;
	}

	/**
	 * @param mixed $data
	 *
	 * @return mixed
	 * @throws Exception
	 */
	private function setFile($data)
	{
		$this->setMemory($data);

		if (JFactory::getConfig()->get('debug') || JFactory::getApplication()->input->get('debug'))
		{
			return $data;
		}

		$this->getFileCache()->store($data, $this->id);

		return $data;
	}

	/**
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	private function setMemory($data)
	{
		static::$cache[$this->id] = $data;

		return $data;
	}
}
