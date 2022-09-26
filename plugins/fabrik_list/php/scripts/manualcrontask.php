<?php
$app = JFactory::getApplication();
$ids = $app->input->get('ids', array(), 'array');

foreach($ids as $id) {
    $app->input->set('cid', $id);

    require_once(JPATH_SITE . DS . 'administrator/components/com_fabrik/controllers/crons.php');
    require_once(JPATH_SITE . DS . 'administrator/components/com_fabrik/controllers/fabcontrolleradmin.php');
    require_once(JPATH_SITE . DS . 'administrator/components/com_fabrik/models/list.php');
    require_once(JPATH_SITE . DS . 'components/com_fabrik/models/pluginmanager.php');
    require_once(JPATH_SITE . DS . 'components/com_fabrik/models/list.php');
    $cron = new FabrikAdminControllerCrons;
    $cron->run();
}
?>

