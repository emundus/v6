<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die();

$db = Factory::getDbo();
$query = $db->getQuery(true);

$group_id = $formModel->getElementData('jos_emundus_setup_groups___id');


// If group already is in ACL, then don't ovveride the ACL.
$query->select('DISTINCT 1')
	->from('#__emundus_acl')
	->where('group_id = '.$group_id);

try {
    $db->setQuery($query);
    $result = $db->loadResult();

} catch (Exception $e) {
    Log::add('Error getting group ACL in script/emundus-setup-groups at query :'.$query, Log::ERROR, 'com_emundus');
}

if ($result != 1) {
	require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'actions.php');
	$m_actions = new EmundusModelActions;
	$m_actions->syncAllActions(false, $group_id);
}