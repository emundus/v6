<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__) . DS . 'helper.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$helper = new modEmundusUpdateHelper;

if (EmundusHelperAccess::asCoordinatorAccessLevel(JFactory::getUser()->id)) {

	$versionPath = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_emundus' . DS . 'version.json';

	$update = json_decode(file_get_contents($versionPath), true);

	// Get the version in their db
	$siteVersion = $helper->checkVersion();

	if ($update['version'] != $siteVersion->version && $update['version'] != $siteVersion->ignore) {
		require(JModuleHelper::getLayoutPath('mod_emundus_update', 'default.php'));
	}


}


