<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core;

defined('_JCH_EXEC') or die('Restricted access');

use JchOptimize\Platform\Http;
use JchOptimize\Platform\Paths;

/**
 *
 *
 */
class FileRetriever
{

	protected static $instances = array();
	protected $oHttpAdapter = null;
	public $response_code = null;
	public $response_error = '';
	public $allow_400 = false;

	/**
	 * @param   array  $aDrivers
	 */
	private function __construct($aDrivers)
	{
		$this->oHttpAdapter = new Http($aDrivers);
	}

	/**
	 *
	 * @param   string  $sPath
	 *
	 * @param   array   $aPost
	 * @param   array   $aHeader
	 * @param   string  $sOrigPath
	 * @param   int     $timeout
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getFileContents($sPath, $aPost = null, $aHeader = array(), $sOrigPath = '', $timeout = 7)
	{
		//We need to use an http adapter if it's a remote or dynamic file
		if (strpos($sPath, 'http') === 0)
		{
			//Initialize response code
			$this->response_code = 0;

			try
			{
				$sUserAgent          = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$aHeader             = array_merge($aHeader, array('Accept-Encoding' => 'identity;q=0'));
				$response            = $this->oHttpAdapter->request($sPath, $aPost, $aHeader, $sUserAgent, $timeout);
				$this->response_code = $response['code'];

				if (!isset($response) || $response === false)
				{
					throw new \RuntimeException(sprintf('Failed getting file contents from %s', $sPath));
				}
			}
			catch (\RuntimeException $ex)
			{
				//Record error message
				$this->response_error = $ex->getMessage();
			}
			catch (Exception $ex)
			{
				throw new Exception($sPath . ': ' . $ex->getMessage());
			}

			if ($this->response_code != 200 && !$this->allow_400)
			{
				//Most likely a RuntimeException has occurred here in that case we want the error message
				if ($this->response_code === 0 && $this->response_error !== '')
				{
					$sContents = '|"COMMENT_START ' . $this->response_error . ' COMMENT_END"|';
				}
				else
				{
					$sPath     = $sOrigPath == '' ? $sPath : $sOrigPath;
					$sContents = $this->notFound($sPath);
				}
			}
			else
			{
				$sContents = $response['body'];
			}
		}
		else
		{
			if (file_exists($sPath))
			{
				$sContents = @file_get_contents($sPath);
			}
			elseif ($this->oHttpAdapter->available() !== false)
			{
				$sUriPath = Paths::path2Url($sPath);

				$sContents = $this->getFileContents($sUriPath, null, array(), $sPath);
			}
			else
			{
				$sContents = $this->notFound($sPath);
			}
		}

		return $sContents;
	}

	/**
	 *
	 * @param   array  $aDrivers
	 *
	 * @return mixed
	 */
	public static function getInstance($aDrivers = array('curl', 'stream', 'socket'))
	{
		$hash = serialize($aDrivers);

		if (empty(static::$instances[$hash]))
		{
			static::$instances[$hash] = new FileRetriever($aDrivers);
		}

		return static::$instances[$hash];
	}

	/**
	 *
	 * @return mixed False if no adapter was found, Http object otherwise
	 */
	public function isHttpAdapterAvailable()
	{
		return $this->oHttpAdapter->available();
	}


	/**
	 *
	 * @param   string  $sPath
	 *
	 * @return string
	 */
	public function notFound($sPath)
	{
		return '|"COMMENT_START File [' . $sPath . '] not found COMMENT_END"|';
	}

}
