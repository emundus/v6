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

}