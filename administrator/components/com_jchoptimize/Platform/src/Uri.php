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

use JchOptimize\Core\Helper;
use JchOptimize\Core\Interfaces\Uri as UriInterface;

class Uri implements UriInterface
{
	private $oUri;

	/**
	 *
	 * @param   string  $path
	 */
	public function setPath($path)
	{
		$this->oUri->setPath($path);
	}

	/**
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->oUri->getPath();
	}

	/**
	 *
	 * @param   array  $parts
	 *
	 * @return string
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		return $this->oUri->toString($parts);
	}

	/**
	 * $pathonly == TRUE => /folder or ''
	 * $pathonly == FALSE => http://localhost/folder/ or http://localhost/
	 *
	 * @param   bool  $pathonly
	 *
	 * @return string|string[]
	 */
	public static function base($pathonly = false)
	{
		if ($pathonly)
		{
			return str_replace('/administrator', '', \JUri::base(true));
		}

		return str_replace('/administrator/', '', \JUri::base());
	}

	/**
	 *
	 * @param   string  $uri
	 *
	 * @return Uri
	 */
	public static function getInstance($uri = 'SERVER')
	{
		static $instances = array();

		if (!isset($instances[$uri]))
		{
			$instances[$uri] = new Uri($uri);
		}

		return $instances[$uri];
	}

	/**
	 *
	 * @param   string  $uri
	 */
	private function __construct($uri)
	{
		$this->oUri = clone \JUri::getInstance($uri);

		if ($uri != 'SERVER')
		{
			$uri   = str_replace('\\/', '/', $uri);
			$parts = Helper::parseUrl($uri);

			$this->oUri->setScheme(!empty($parts['scheme']) ? $parts['scheme'] : null);
			$this->oUri->setUser(!empty($parts['user']) ? $parts['user'] : null);
			$this->oUri->setPass(!empty($parts['pass']) ? $parts['pass'] : null);
			$this->oUri->setHost(!empty($parts['host']) ? $parts['host'] : null);
			$this->oUri->setPort(!empty($parts['port']) ? $parts['port'] : null);
			$this->oUri->setPath(!empty($parts['path']) ? $parts['path'] : null);
			$this->oUri->setQuery(!empty($parts['query']) ? $parts['query'] : null);
			$this->oUri->setFragment(!empty($parts['fragment']) ? $parts['fragment'] : null);
		}

		return $this->oUri;
	}

	/**
	 *
	 */
	public function __clone()
	{
		$this->oUri = clone $this->oUri;
	}

	/**
	 *
	 * @param   mixed  $query
	 */
	public function setQuery($query)
	{
		$this->oUri->setQuery($query);
	}

	/**
	 *
	 * @return string
	 */
	public static function currentUrl()
	{
		return \JUri::current();
	}

	/**
	 *
	 * @param   string  $host
	 */
	public function setHost($host)
	{
		$this->oUri->setHost($host);
	}

	/**
	 *
	 */
	public function getHost()
	{
		return $this->oUri->getHost();
	}

	/**
	 *
	 * @param   bool  $toArray
	 *
	 * @return array|string
	 */
	public function getQuery($toArray=false)
	{
		return $this->oUri->getQuery($toArray);
	}

	/**
	 *
	 * @return string
	 */
	public function getScheme()
	{
		return $this->oUri->getScheme();
	}

	/**
	 *
	 * @param   string  $scheme
	 */
	public function setScheme($scheme)
	{
		$this->oUri->setScheme($scheme);
	}
}
