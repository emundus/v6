<?php
/**
 * @version 2: emundusisapplicationsent 2018-12-04 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
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
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusisqcmcomplete extends plgFabrik_Form {


    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

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

        if ($params->get($pname) == '')
            return '';

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   array   $data     Posted form data
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

    /**
     * Main script.
     *
     * @return  bool
     */
    public function onBeforeLoad() {

        $mainframe = JFactory::getApplication();

        if (!$mainframe->isAdmin()) {
            require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
            require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');


            jimport('joomla.log.log');
            JLog::addLogger(['text_file' => 'com_emundus.isApplicationSent.php'], JLog::ALL, ['com_emundus']);

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $mainframe = JFactory::getApplication();

            $jinput = $mainframe->input;

            $fnum = $jinput->getString('rowid');

            $table = explode(',',$this->getParam('qcmcomplete_parenttable', 'jos_emundus_qcm'));
            $repeat_table = explode(',',$this->getParam('qcmcomplete_repeattable', 'jos_emundus_qcm_880_repeat'));
            $formid = explode(',',$this->getParam('qcmcomplete_formid', '287'));
            $itemid = explode(',',$this->getParam('qcmcomplete_itemid', '3185'));

            $query->select('distinct sq.id')
                ->from($db->quoteName('#__emundus_setup_qcm','sq'))
                ->leftJoin($db->quoteName('#__emundus_qcm_applicants','qc').' ON '.$db->quoteName('qc.qcmid').' = '.$db->quoteName('sq.id'))
                ->where($db->quoteName('qc.fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $qcms = $db->loadColumn();

            if(sizeof($qcms) == sizeof($formid)) {
                foreach ($qcms as $key => $qcm) {
                    $query->clear()
                        ->select('questions')
                        ->from($db->quoteName('#__emundus_qcm_applicants'))
                        ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                        ->andWhere($db->quoteName('qcmid') . ' = ' . $db->quote($qcm));
                    $db->setQuery($query);
                    $q_numbers = sizeof(explode(',',$db->loadResult()));

                    $query->clear()
                        ->select('count(rt.id) as answers')
                        ->from($db->quoteName($repeat_table[$key], 'rt'))
                        ->leftJoin($db->quoteName($table[$key], 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('rt.parent_id'))
                        ->where($db->quoteName('t.fnum') . ' = ' . $db->quote($fnum));
                    $db->setQuery($query);
                    $answers_given = $db->loadResult();

                    if ((int)$answers_given != $q_numbers) {
                        $mainframe->enqueueMessage(JText::sprintf('PLEASE_COMPLETE_QCM_BEFORE_SEND'));
                        $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=" . $formid[$key] . "&Itemid=" . $itemid[$key] . "&usekey=fnum&rowid=" . $fnum . "&r=1");
                    }
                }
            } else {
                $mainframe->enqueueMessage(JText::sprintf('PLEASE_COMPLETE_QCM_BEFORE_SEND'));
                $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=" . $formid[0] . "&Itemid=" . $itemid[0] . "&usekey=fnum&rowid=" . $fnum . "&r=1");
            }
        }
        return true;
    }
}
