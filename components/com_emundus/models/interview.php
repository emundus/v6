<?php

/**
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license        GNU/GPL
 * @author        Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class EmundusModelInterview extends JModelList
{
    public function __construct() {
        parent::__construct();
    }
    /*
        * 	Get evaluations form ID By programme code
        *	@param code 		code of the programme
        * 	@return int 		The fabrik ID for the admission form
        */
    function getInterviewFormByProgramme($code = null)
    {
        $db = JFactory::getDbo();
        if ($code === NULL) {

            $session = JFactory::getSession();
            if ($session->has('filt_params')) {

                $filt_params = $session->get('filt_params');
                if (count(@$filt_params['programme']) > 0)
                    $code = $filt_params['programme'][0];
            }
        }

        try {

            $query = 'SELECT ff.form_id
					FROM #__fabrik_formgroup ff
					WHERE ff.group_id IN (SELECT fabrik_interview_group_id FROM #__emundus_setup_programmes WHERE code like ' .
                $db->Quote($code) . ')';
            $db->setQuery($query);

            return $db->loadResult();

        } catch (Exception $e) {

            echo $e->getMessage();
            JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

        }
    }
    // get string of fabrik group ID use for evaluation form
    public function getGroupsInterviewByProgramme($code) {
        $db = $this->getDbo();
        $query = 'select fabrik_interview_group_id from #__emundus_setup_programmes where code like '.$db->Quote($code);
        
        try {
            if (!empty($code)) {
                $db->setQuery($query);
                return $db->loadResult();
            } else return null;
        } catch(Exception $e) {
            throw $e;
        }
    }
    function getEvaluationsFnumUser($fnum, $user) {

        try {

            $query = 'SELECT *
					FROM #__emundus_evaluations ee
					WHERE ee.fnum like ' . $this->_db->Quote($fnum) . ' AND user = ' . $user;
//die(str_replace('#_', 'jos', $query));
            $this->_db->setQuery($query);

            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }
    function getEvaluationsByFnum($fnum) {
        try {

            $query = 'SELECT * FROM #__emundus_evaluations ee WHERE ee.fnum like ' . $this->_db->Quote($fnum).' ORDER BY ee.id DESC' ;
            $this->_db->setQuery($query);

            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }
    // get ALL elements by groups
    // @params string List of Fabrik groups comma separated
    function getAllElementsByGroups($groups){
        return @EmundusHelperFilters::getAllElementsByGroups($groups);
    }
    /**
     * Get list of ALL evaluation element
     *
     * @param int $show_in_list_summary
     * @param      string code of the programme
     *
     * @return array list of Fabrik element ID used in evaluation form
     * @throws Exception
     */
    public function getAllInterviewElements($show_in_list_summary, $programme_code) {
        $session = JFactory::getSession();

        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('cfnums', null);

        if ($session->has('filt_params')) {
            //var_dump($session->get('filt_params'));
            $elements_id = array();
            $filt_params = $session->get('filt_params');

            if (is_array(@$filt_params['programme']) && $filt_params['programme'][0] != '%') {
                foreach ($filt_params['programme'] as $value) {
                    if ($value == $programme_code) {
                        $groups = $this->getGroupsInterviewByProgramme($value);
                        if (!empty($groups)) {
                            $eval_elt_list = $this->getAllElementsByGroups($groups); // $show_in_list_summary
                            if (count($eval_elt_list)>0) {
                                foreach ($eval_elt_list as $eel) {
                                    $elements_id[] = $eel->element_id;
                                }
                            }
                        }
                    }
                }
            } else {
                $groups = $this->getGroupsInterviewByProgramme($programme_code);
                if (!empty($groups)) {
                    $eval_elt_list = $this->getAllElementsByGroups($groups); // $show_in_list_summary
                    if (count($eval_elt_list)>0) {
                        foreach ($eval_elt_list as $eel) {
                            $elements_id[] = $eel->element_id;
                        }
                    }
                }
            }
        }

        return @$elements_id;
    }

}