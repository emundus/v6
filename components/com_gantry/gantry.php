<?php
/**
 * @package   gantry
 * @subpackage core
 * @version   4.1.31 April 11, 2016
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
// Include dependancies
jimport('joomla.application.component.controller');
require_once(dirname(__FILE__).'/compatability.php');

$controller	= GantryLegacyJController::getInstance('Gantry');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();