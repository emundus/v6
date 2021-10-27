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

defined( '_JEXEC' ) or die( 'Restricted access' );

$DIR = dirname( __FILE__, 4 );

if ( file_exists( $DIR . '/defines.php' ) )
{
	include_once $DIR . '/defines.php';
}

if ( ! defined( '_JDEFINES' ) )
{
	define( 'JPATH_BASE', $DIR );
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

if ( version_compare( JVERSION, '3.999.999', '>' ) )
{
	// Boot the DI container
	$container = \Joomla\CMS\Factory::getContainer();

	/*
	 * Alias the session service keys to the web session service as that is the primary session backend for this application
	 *
	 * In addition to aliasing "common" service keys, we also create aliases for the PHP classes to ensure autowiring objects
	 * is supported.  This includes aliases for aliased class names, and the keys for aliased class names should be considered
	 * deprecated to be removed when the class name alias is removed as well.
	 */
	$container->alias( 'session.web', 'session.web.site' )
		->alias( 'session', 'session.web.site' )
		->alias( 'JSession', 'session.web.site' )
		->alias( \Joomla\CMS\Session\Session::class, 'session.web.site' )
		->alias( \Joomla\Session\Session::class, 'session.web.site' )
		->alias( \Joomla\Session\SessionInterface::class, 'session.web.site' );

// Instantiate the application.
	$app = $container->get( \Joomla\CMS\Application\SiteApplication::class );

// Set the application as global app
	\Joomla\CMS\Factory::$application = $app;

}

require_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';
require_once JPATH_LIBRARIES . '/fof40/include.php';
