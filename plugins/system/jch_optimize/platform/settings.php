<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2014 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

use JchOptimize\Interfaces\SettingsInterface;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class Settings implements SettingsInterface
{
	private $params;

	/**
	 *
	 * @param   Registry  $params
	 *
	 * @return Settings
	 */
	public static function getInstance($params)
	{
		return new Settings($params);
	}

	/**
	 *
	 * @param   mixed  $param
	 * @param   mixed  $default
	 *
	 * @return mixed
	 */
	public function get($param, $default = null)
	{
		return $this->params->get($param, $default);
	}

	/**
	 *
	 * @param   Registry  $params
	 */
	private function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 *
	 * @param   mixed  $param
	 * @param   mixed  $value
	 */
	public function set($param, $value)
	{
		$this->params->set($param, $value);
	}

	/**
	 *
	 */
	public function toArray()
	{
		return $this->params->toArray();
	}


	/**
	 *
	 */
	public function __clone()
	{

		$this->params = unserialize(serialize($this->params));
	}

	/**
	 *
	 */
	public function getOptions()
	{
		return $this->params->toObject();
	}

	/**
	 * Delete a value from the settings object
	 *
	 * @param   mixed  $param  The parameter value to be deleted
	 *
	 * @return   null
	 */
	public function remove($param)
	{
		return $this->params->remove($param);
	}
}
