<?php
/**
 * Securitycheck Pro Library
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No Permission
defined('_JEXEC') or die('Restricted access');

$library = dirname(__FILE__);

JLoader::register('SecuritycheckproController', $library.'/controller.php');
JLoader::register('SecuritycheckproModel', $library.'/model.php');
JLoader::register('SecuritycheckproView', $library.'/view.php');
