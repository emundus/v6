<?php

/**
 * @version        $Id: tags.php
 * @package        Joomla
 * @subpackage    Emundus
 * @copyright    Copyright (C) 2019 eMundus. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * Content Component Cache Helper
 *
 * @static
 * @package        Joomla
 * @subpackage    Helper
 * @since 1.5
 */
class EmundusHelperCache
{
	private $cache = null;
	private $group = '';
	private $cache_enabled = false;

	public function __construct($group = 'com_emundus', $handler = '', $lifetime = '', $context = 'component')
	{
		$config = JFactory::getConfig();
		$cache_enabled = $config->get('caching'); // 1 = conservative, 2 = progressive
		$cache_handler = $config->get('cache_handler', 'file');

		if ($cache_enabled > 0 && $cache_handler == 'file') {
			if ($context === 'component' || $cache_enabled === 2) {
				if (empty($lifetime)) {
					$cache_time = $config->get('cachetime', 15);
					$lifetime = $cache_time * 60;
				}

				$this->group = $group;
				$this->cache = JFactory::getCache($group, $handler);
				$this->cache->setLifeTime($lifetime);
				$this->cache->setCaching(true);
				$this->cache_enabled = true;
			}
		}
	}

	public function isEnabled()
	{
		return $this->cache_enabled;
	}

	public function get($id)
	{
		$cache = null;

		if ($this->isEnabled()) {
			$cache = $this->cache->get($id, $this->group);
		}

		return $cache;
	}

	public function set($id, $data)
	{
		$stored = false;

		if ($this->isEnabled()) {
			$stored = $this->cache->store($data, $id, $this->group);
		}

		return $stored;
	}

	public function clean() {
		$cleaned = false;

		if ($this->isEnabled()) {
			$cleaned = $this->cache->__call('clean', array($this->group));
		}

		return $cleaned;
	}
}
