<?php
/**
 * @version 1.34.0: emundusisevaluatedbyme 2022-12-02 Brice HUBINET
 * @package Fabrik
 * @copyright Copyright (C) 2022 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * @description Check how can the connected user can access to an evaluation
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


	public function onBeforeLoad() {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $user =  JFactory::getUser();

        $r = $app->input->get('r', 0);
        $formid = $app->input->get('formid', '256');
        $rowid = $app->input->get('rowid');
        $student_id = $app->input->get('jos_emundus_evaluations___student_id') ?: '';
		$fnum = $app->input->get('jos_emundus_evaluations___fnum') ?:'';
		$view = strpos(JUri::getInstance()->getPath(), '/details/') !== false ? 'details' : 'form';

		if (empty($fnum) || empty($student_id)) {
			if (!empty($rowid)) {
				$query->select('fnum, student_id')
					->from('#__emundus_evaluations')
					->where('id = ' . $rowid);

				try {
					$db->setQuery($query);
					$evaluation_row = $db->loadAssoc();

					if (!empty($evaluation_row)) {
						$fnum = $evaluation_row['fnum'];
						$student_id = $evaluation_row['student_id'];
					}
				} catch (Exception $e) {
					JLog::add('Failed to find fnum from rowid ' . $rowid . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			} else {
				$fnum = '{jos_emundus_evaluations___fnum}';
				$student_id = '{jos_emundus_evaluations___student_id}';
			}
		}

		require_once(JPATH_SITE.'/components/com_emundus/models/evaluation.php');
        $m_evaluation = new EmundusModelEvaluation();
        $evaluation = $m_evaluation->getEvaluationUrl($fnum,$formid,$rowid,$student_id,1, $view);

        if(!empty($evaluation)) {
            $event_datas = [
                'formid' => $formid,
                'rowid' => $rowid,
                'student_id' => $student_id,
                'fnum' => $fnum
            ];
            JPluginHelper::importPlugin('emundus', 'custom_event_handler');
            \Joomla\CMS\Factory::getApplication()->triggerEvent('onCallEventHandler', ['onRenderEvaluation', ['event_datas' => $event_datas]]);
        }

        $app->enqueueMessage($evaluation['message']);
        if($r != 1) {
            $app->redirect($evaluation['url']);
        }

        return true;
	}

    public function onBeforeProcess() {
        $formModel = $this->getModel();

        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeSubmitEvaluation', ['formModel' => $formModel]]);
    }

    public function onAfterProcess() {
        $formModel = $this->getModel();

        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterSubmitEvaluation', ['formModel' => $formModel]]);
    }
}
