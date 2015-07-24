<?php
/**
 * @version		1.2.0
 * @package		Joomla
 * @subpackage	Falang
 * @author      Stéphane Bouey
 * @copyright	Copyright (C) 2012-2013 Faboba
 * @license		GNU/GPL, see LICENSE.php
 */


/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

define('FALANG_J16',version_compare(JVERSION,'1.6.0','>=') ? true : false);
define('FALANG_J17',version_compare(JVERSION,'1.7.0','>=') ? true : false);
define('FALANG_J25',version_compare(JVERSION,'2.5.0','>=') ? true : false);
define('FALANG_J30',version_compare(JVERSION,'3.0.0','>=') ? true : false);

if( !defined('FALANG_PATH') ) {
	define( 'FALANG_PATH', JPATH_SITE .DS.'components'.DS.'com_falang' );
	define( 'FALANG_ADMINPATH', JPATH_SITE .DS.'administrator'.DS.'components'.DS.'com_falang' );
	define( 'FALANG_LIBPATH', FALANG_ADMINPATH .DS. 'libraries' );
	define( 'FALANG_LANGPATH', FALANG_PATH .DS. 'language' );
	define( 'FALANG_URL', '/components/com_falang');
}

