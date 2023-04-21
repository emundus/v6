<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'qcm.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_qcm/src/assets/mod_emundus_qcm.css" );
$document->addScript('media/mod_emundus_qcm/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_qcm/app.css');

$intro = $params->get('mod_em_qcm_intro');

$model = new EmundusModelQcm;

$jinput = JFactory::getApplication()->input;
$formid   = $jinput->get('formid');
$fnum   = $jinput->get('rowid');

$qcm = $model->getQcm($formid);
$qcm_applicant = $model->getQcmApplicant($fnum,$qcm->id);
if(empty($qcm_applicant)) {
    $qcm_applicant_id = $model->initQcmApplicant($fnum,$qcm->id);
    $qcm_applicant = $model->getQcmApplicant($fnum,$qcm->id);
}

require(JModuleHelper::getLayoutPath('mod_emundus_qcm', $params->get('mod_em_qcm_layout')));
?>
