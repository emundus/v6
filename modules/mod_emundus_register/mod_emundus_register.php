<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
$urlresult=$params->get('mod_em_register_url');

$id= JFactory::getApplication()->input->get('id', null, 'GET', 'INT',0);
$itemid= JFactory::getApplication()->input->get('Itemid', null, 'GET', 'INT',0);
$course= JFactory::getApplication()->input->get('course', null, 'GET', 'CMD',0);

if(empty($course)){
	$result=EmundusRegister::getCode($id);
}
else
{
	$result['code']= $course;
}

require(JModuleHelper::getLayoutPath('mod_emundus_register'));
?>