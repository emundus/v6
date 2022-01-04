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

namespace JchOptimize\Core\Interfaces;

defined('_JCH_EXEC') or die('Restricted access');

/**
 * Interface HttpInterface
 * @package JchOptimize\Core\Interfaces
 */
interface Http
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
