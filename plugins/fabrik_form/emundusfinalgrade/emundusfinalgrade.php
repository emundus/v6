<?php
/**
 * @version 2: emunduscampaign 2019-04-11 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création de dossier de candidature automatique.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.emunduscampaigncheck
 * @since       3.0
 */

class PlgFabrik_FormEmundusFinalGrade extends plgFabrik_Form {

    /**
     * Get an element name
     *
     * @param   string  $pname  Params property name to look up
     * @param   bool    $short  Short (true) or full (false) element name, default false/full
     *
     * @return	string	element full name
     */
    public function getFieldName($pname, $short = false) {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return '';
        }

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   mixed   $default  Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }


	public function onBeforeLoad() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$user =  JFactory::getUser();

		$r = $app->input->get('r', 0);
		$formid = $app->input->get('formid', '256');
		$rowid = $app->input->get('rowid');
		$student_id = $app->input->get('jos_emundus_final_grade___student_id') ?: '';
		$fnum = $app->input->get('jos_emundus_final_grade___fnum') ?:'';
		$view = strpos(JUri::getInstance()->getPath(), '/details/') !== false ? 'details' : 'form';

		if (empty($fnum) || empty($student_id)) {
			if (!empty($rowid)) {
				$query->select('fnum, student_id')
					->from('#__emundus_final_grade')
					->where('id = ' . $rowid);

				try {
					$db->setQuery($query);
					$decision_row = $db->loadAssoc();

					if (!empty($decision_row)) {
						$fnum = $decision_row['fnum'];
						$student_id = $decision_row['student_id'];
					}
				} catch (Exception $e) {
					JLog::add('Failed to find fnum from rowid ' . $rowid . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			} else {
				$fnum = '{jos_emundus_final_grade___fnum}';
				$student_id = '{jos_emundus_final_grade___student_id}';
			}
		}

		require_once(JPATH_SITE.'/components/com_emundus/models/decision.php');
		$m_decision = new EmundusModelDecision();
		$decision = $m_decision->getDecisionUrl($fnum,$formid,$rowid,$student_id,1, $view);

		if(!empty($decision)) {
			$event_datas = [
				'formid' => $formid,
				'rowid' => $rowid,
				'student_id' => $student_id,
				'fnum' => $fnum
			];
			JPluginHelper::importPlugin('emundus', 'custom_event_handler');
			\Joomla\CMS\Factory::getApplication()->triggerEvent('onCallEventHandler', ['onRenderFinalgrade', ['event_datas' => $event_datas]]);
		}

		$app->enqueueMessage($decision['message']);
		if($r != 1) {
			$app->redirect($decision['url']);
		}

		return true;
	}

	public function onBeforeProcess() {
		$formModel = $this->getModel();

		JPluginHelper::importPlugin('emundus','custom_event_handler');
		\Joomla\CMS\Factory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeSubmitFinalgrade', ['formModel' => $formModel]]);
	}

	public function onAfterProcess() {
		$formModel = $this->getModel();

		JPluginHelper::importPlugin('emundus','custom_event_handler');
		\Joomla\CMS\Factory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterSubmitFinalgrade', ['formModel' => $formModel]]);
	}

    public function onBeforeCalculations() {

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.emundus-final-grade.php'), JLog::ALL, array('com_emundus.emundus-final-grade'));

        $db = JFactory::getDBO();
        $formModel = $this->getModel();

        $fnum = $formModel->formData['fnum_raw'];
        $status = is_array($formModel->formData['final_grade_raw']) ? $formModel->formData['final_grade_raw'][0] : $formModel->formData['final_grade_raw'];

        if (!empty($fnum) && isset($status)) {
			require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
			$m_files = new EmundusModelFiles();
			$m_files->updateState([$fnum], $status);
        }
    }
}
