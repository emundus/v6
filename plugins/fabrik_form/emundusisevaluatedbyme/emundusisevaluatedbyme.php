<?php
/**
 * @version 1.34.0: emundusisevaluatedbyme 2022-12-02 Brice HUBINET
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Locks access to a file if the file is not of a certain status.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */
class PlgFabrik_FormEmundusisevaluatedbyme extends plgFabrik_Form {

    // TODO: Add dispatcher to event handler onRenderEvaluation
	public function onBeforeLoad() {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $user =  JFactory::getUser();

        $offset = $app->get('offset', 'UTC');
        $date_time = new DateTime(gmdate('Y-m-d H:i:s'), new DateTimeZone('UTC'));
        $date_time = $date_time->setTimezone(new DateTimeZone($offset));
        $now = $date_time->format('Y-m-d H:i:s');

        $r = $app->input->get('r', 0);
        $formid = $app->input->get('formid', '256');
        $rowid = $app->input->get('rowid', null);
        $student_id = '{jos_emundus_evaluations___student_id}';
        $fnum = '{jos_emundus_evaluations___fnum}';
        $params = JComponentHelper::getParams('com_emundus');
        $multi_eval = $params->get('multi_eval', 0);

        $query->select('esc.eval_start_date,esc.eval_end_date')
            ->from($db->quoteName('#__emundus_setup_campaigns','esc'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('ecc.campaign_id').' = '.$db->quoteName('esc.id'))
            ->where($db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($fnum));
        $db->setQuery($query);
        $eval_dates = $db->loadObject();

        $passed = false;
        $started = true;
        if(!empty($eval_dates->eval_end_date)) {
            $passed = strtotime($now) > strtotime($eval_dates->eval_end_date);
        }
        if(!empty($eval_dates->eval_start_date)) {
            $started = strtotime($now) > strtotime($eval_dates->eval_start_date);
        }

        // If we try to open an evaluation with rowid in url
        if(!empty($rowid) ) {
            // If we open an evaluation
            $query->clear()
                ->select('id,user')
                ->from($db->quoteName('#__emundus_evaluations'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($rowid));
        }
        // If multi evaluation is allowed we search for our evaluation
        elseif($multi_eval == 1) {
            $query->clear()
                ->select('id,user')
                ->from($db->quoteName('#__emundus_evaluations'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum))
                ->andWhere($db->quoteName('user') . ' = ' . $db->quote($user->id));
        }
        // If multi evaluation is not allowed we search for the evaluation of file
        else {
            $query->clear()
                ->select('id,user')
                ->from($db->quoteName('#__emundus_evaluations'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
        }
        $db->setQuery($query);
        $evaluation = $db->loadObject();


        $form_url = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&jos_emundus_evaluations___student_id='.$student_id.'&jos_emundus_evaluations___fnum='.$fnum.'&tmpl=component&iframe=1&r=1';
        $details_url = 'index.php?option=com_fabrik&c=form&view=details&formid='.$formid.'&jos_emundus_evaluations___student_id='.$student_id.'&jos_emundus_evaluations___fnum='.$fnum.'&tmpl=component&iframe=1&r=1';

        if(!empty($evaluation)) {
            $form_url = 'index.php?option=com_fabrik&c=form&view=form&formid=' . $formid . '&jos_emundus_evaluations___student_id=' . $student_id . '&tmpl=component&iframe=1&rowid=' . $evaluation->id . '&r=1';
            $details_url = 'index.php?option=com_fabrik&c=form&view=details&formid=' . $formid . '&jos_emundus_evaluations___student_id=' . $student_id . '&rowid=' . $evaluation->id . '&tmpl=component&iframe=1&r=1';

            // If evaluation period is passed
            if ($passed) {
                $app->enqueueMessage(JText::_('EVALUATION_PERIOD_PASSED'), 'warning');

                if ($r != 1) {
                    $app->redirect($details_url);
                }
            }
            // If evaluation period started and not passed and we have update rights
            elseif (EmundusHelperAccess::asAccessAction(5, 'u', $user->id)) {
                if ($r != 1) {
                    $app->redirect($form_url);
                }
            }
            // If evaluation period started and not passed and we have read rights
            elseif (EmundusHelperAccess::asAccessAction(5, 'r', $user->id)){
                if ($r != 1) {
                    $app->redirect($details_url);
                }
            }
            // If we do not have any rights on evaluation
            elseif ($r != 1) {
                $app->enqueueMessage(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'), 'error');
                $app->redirect('index.php');
            }
        }
        // If no evaluation found but period is not started or passed
        elseif(($passed || !$started) && EmundusHelperAccess::asAccessAction(5, 'r', $user->id)) {
            if($r != 1) {
                if($passed){
                    $app->enqueueMessage(JText::_('EVALUATION_PERIOD_PASSED'), 'warning');
                } elseif (!$started){
                    $app->enqueueMessage(JText::_('EVALUATION_PERIOD_NOT_STARTED'), 'warning');
                }

                $app->redirect($details_url);
            }
        }
        // If no evaluation and period is started and not passed and I have create rights
        elseif ((!$passed && $started) && EmundusHelperAccess::asAccessAction(5, 'c', $user->id)) {
            if($r != 1) {
                $app->redirect($form_url);
            }
        }
        // I don't have rights to evaluate
        elseif($r != 1) {
            $app->enqueueMessage(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'), 'error');
            $app->redirect('index.php');
        }

        return true;
	}

    // TODO: Add dispatcher to event handler onBeforeSubmitEvaluation
    public function onBeforeProcess() {

    }

    // TODO: Add dispatcher to event handler onAfterSubmitEvaluation
    public function onAfterProcess() {

    }
}
