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

defined( '_JEXEC' ) or die;

if ( ! defined( 'FOF40_INCLUDED' ) && ! @include_once( JPATH_LIBRARIES . '/fof40/include.php' ) )
{
	throw new RuntimeException( 'FOF 4.0 is not installed', 500 );
}

FOF40\Container\Container::getInstance( 'com_jchoptimize' )->dispatcher->dispatch();