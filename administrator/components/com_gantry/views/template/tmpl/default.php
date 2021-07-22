<?php
/**
 * @version	$Id: default.php 2625 2012-08-22 16:55:27Z btowles $ 
 * @package Gantry
 * @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author RocketTheme, LLC
 */
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
$app = JFactory::getApplication();
$app->redirect(JRoute::_('index.php?option=com_templates&view=styles', false));
?>

You shouldn't be here. @TODO