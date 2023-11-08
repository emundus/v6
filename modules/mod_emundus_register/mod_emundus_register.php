<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__) . DS . 'helper.php');
$urlresult = $params->get('mod_em_register_url');

$id     = JRequest::getVar('id', null, 'GET', 'INT', 0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'INT', 0);
$course = JRequest::getVar('course', null, 'GET', 'CMD', 0);

if (empty($course)) {
	$result = EmundusRegister::getCode($id);
}
else {
	$result['code'] = $course;
}

require(JModuleHelper::getLayoutPath('mod_emundus_register'));
?>