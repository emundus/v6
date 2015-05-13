<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 26/01/15
 * Time: 17:27
 */
defined( '_JEXEC' ) or die();
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();
$fnum = $jinput->get('jos_emundus_uploads___fnum',array(), 'ARRAY');
$fnum = $fnum['value'];
$action_id = $jinput->getInt('action_id', null);

if($action_id !== null)
{
	if(!EmundusHelperAccess::asAccessAction($action_id, 'c', $user->id, $fnum))
	{
		die(JText::_('ACCESS_DENIED'));
	}
}