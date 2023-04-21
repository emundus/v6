<?php
/**
* @version		$Id: toolbar.contact.php 14401 2011-05-26 14:10:00Z Benjamin Rivalland $
* @package		Joomla
* @subpackage	emundus
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

JToolBarHelper::title( JText::_( 'eMundus' ), 'generic.png' );
JToolBarHelper::preferences('com_emundus', '500');

?>