<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelsteps extends JModelList {
    var $db = null;
    var $query = null;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    //// get all workflows --> use alias "ew" === emundus_workflow
    public function getStepsByWorkflow($wid) {
        if ($wid) {
            try {
                $this->query = "SELECT ewsr.*, ess1.step as istatus, ess2.step as ostatus, ess1.value as ilabels, ess2.value as olabels, ess1.class as iclass, ess2.class oclass, ews.*
                                    FROM #__emundus_workflow_step_repeat AS ewsr
                                        LEFT JOIN #__emundus_workflow_step AS ews ON ewsr.parent_id = ews.id
                                            LEFT JOIN #__emundus_campaign_workflow AS ecw ON ews.workflow = ecw.id
                                                LEFT JOIN #__emundus_setup_status AS ess1 ON ewsr.input_status = ess1.step
                                                    LEFT JOIN #__emundus_setup_status AS ess2 ON ewsr.output_status = ess2.step
                                                        WHERE ecw.id = " . $wid;
                
                $this->db->setQuery($this->query);      //set query string
                $raws = $this->db->loadObjectList();     // get steps repeat

                $res = array();

                foreach($raws as $raw) {
                    $res[$raw->parent_id]['label'] = $raw->label;

                    $res[$raw->parent_id]['start_date'] = $raw->start_date;
                    $res[$raw->parent_id]['end_date'] = $raw->end_date;

                    $res[$raw->parent_id]['input'][$raw->input_status]->lbl = $raw->ilabels;
                    $res[$raw->parent_id]['input'][$raw->input_status]->class = $raw->iclass;

                    $res[$raw->parent_id]['output'][$raw->output_status]->lbl = $raw->olabels;
                    $res[$raw->parent_id]['output'][$raw->output_status]->class = $raw->oclass;
                }

                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot get all steps by workflow' . preg_replace("/[\r\n]/", " ", $this->query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        } else {
            return false;
        }

    }
}
