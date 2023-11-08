<?php
/**
 * @package     Joomla
 * @subpackage  mod_emundus_qcm
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_ROOT . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
$document = JFactory::getDocument();
$document->addScript('media/mod_emundus_qcm/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_qcm/app.css');

JText::script('MOD_EM_QCM_STARTING');
JText::script('MOD_EM_QCM_NEXT_QUESTION');
JText::script('MOD_EM_QCM_ANSWER_SENDED');
JText::script('MOD_EM_QCM_RESTART');
JText::script('MOD_EM_QCM_ARE_YOU_READY');
JText::script('MOD_EM_QCM_COMPLETED');
JText::script('MOD_EM_QCM_SUCCESSFULL');
JText::script('MOD_EM_QCM_CONFIRM_ANSWER');

JText::script('MOD_EM_QCM_TEST_QUESTION_PROPOSAL');
JText::script('MOD_EM_QCM_TEST_QUESTION_PROPOSAL_1');
JText::script('MOD_EM_QCM_TEST_QUESTION_PROPOSAL_2');
JText::script('MOD_EM_QCM_TEST_QUESTION_PROPOSAL_3');
JText::script('MOD_EM_QCM_TEST_QUESTION_PROPOSAL_4');
?>

<div id="em-qcm-vue"
     questions="<?= $qcm_applicant->questions ?>"
     formid="<?= $formid ?>"
     step="<?= $qcm_applicant->step ?>"
     pending="<?= $qcm_applicant->pending ?>"
     module="<?= $module->id ?>"
     tierstemps="<?= $qcm_applicant->tiers_temps ?>"
></div>

<script src="media/mod_emundus_qcm/app.js"></script>
