<?php

/**
 * Example script for adding an entry to JomSocial wall.  In this example, it just adds ...
 * '{actor} sent {target} a drink'
 * ... to the wall, getting {actor} from logged in user, and {target} from a field on the form.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');
$myuser =& JFactory::getUser();
$act = new stdClass();
$act->cmd 	= 'wall.write';
$act->actor 	= $myuser->get('id');
$act->target 	= $fabrikFormData['other_user_raw'];
$act->title 	= JText::_('{actor} sent {target} a drink');
$act->content 	= '';
$act->app 	= 'wall';
$act->cid 	= 0;
CFactory::load('libraries', 'activities');
CActivityStream::add($act);
?>