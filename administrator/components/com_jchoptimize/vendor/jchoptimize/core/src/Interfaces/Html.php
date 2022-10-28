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

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

interface Html
{
	/**
	 * Returns HTML of the front page
	 *
	 * @return string
	 */
	public function getHomePageHtml();

	public function getMainMenuItemsHtmls();
}
