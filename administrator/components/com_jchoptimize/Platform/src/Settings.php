<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

defined('_JEXEC') or die('Restricted access');

use JchOptimize\Core\Interfaces\Settings as SettingsInterface;
use Joomla\Registry\Registry;

class Settings implements SettingsInterface
{
	private $params;

	/**
	 *
	 * @param   Registry  $params
	 *
	 * @return Settings
	 * @deprecated  Just instantiate class directly when using
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
	public function __construct($params)
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
