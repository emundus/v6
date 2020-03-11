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

defined('_JEXEC') or die('Restricted access');

use JchOptimize\Core\Exception;
use JchOptimize\Core\FileRetriever;
use JchOptimize\Core\Logger;
use JchOptimize\Interfaces\HtmlInterface;

class Html implements HtmlInterface
{
	protected $params;

	/**
	 *
	 * @param   Settings  $params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * Returns HTML of the front page
	 *
	 * @return string
	 */
	public function getOriginalHtml()
	{
		JCH_DEBUG ? Profiler::mark('beforeGetHtml') : null;

		try
		{
			$oFileRetriever = FileRetriever::getInstance();

			$response = $oFileRetriever->getFileContents($this->getSiteUrl());

			if ($oFileRetriever->response_code != 200)
			{
				throw new Exception('Failed fetching front end HTML with response code ' . $oFileRetriever->response_code);
			}

			JCH_DEBUG ? Profiler::mark('afterGetHtml') : null;

			return $response;
		}
		catch (Exception $e)
		{
			Logger::log($this->getSiteUrl() . ': ' . $e->getMessage(), $this->params);

			JCH_DEBUG ? Profiler::mark('afterGetHtml') : null;

			throw new \RuntimeException('Try reloading the front page to populate the Exclude options');
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getSiteUrl()
	{
		return \JUri::root() . '?jchbackend=2';
	}

}
