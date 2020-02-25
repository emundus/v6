<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
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

namespace JchOptimize\Interfaces;

/**
 * Interface HttpInterface
 * @package JchOptimize\Interfaces
 */
interface HttpInterface
{
	/**
	 *
	 * @param   string      $sPath
	 * @param   array       $aPost
	 * @param   array|null  $aHeaders
	 * @param   string      $sUserAgent
	 *
	 * @return array
	 */
	public function request($sPath, $aPost = null, $aHeaders = null, $sUserAgent = '');

	/**
	 * Returns an available http transport object
	 *
	 * @return mixed False if no http adapter found, Http object otherwise
	 */
	public function available();
}
