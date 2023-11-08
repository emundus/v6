<?php
/**
 * @version        $Id: mod_emundusuniv.php 7692 2007-06-08 20:41:29Z brivalland $
 * @package        Joomla
 * @copyright      Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$db =& JFactory::getDBO();

$query = 'SELECT title FROM #__categories WHERE section = "com_contact_details" ORDER BY ordering';
$db->setQuery($query);
$univ = $db->loadResultArray();

require(JModuleHelper::getLayoutPath('mod_emundusuniv'));