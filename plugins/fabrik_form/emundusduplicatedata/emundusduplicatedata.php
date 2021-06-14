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

class PlgFabrik_FormEmundusduplicatedata extends plgFabrik_Form {

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

    public function onLoad() {
        $mainframe = JFactory::getApplication();

        if (!$mainframe->isAdmin()) {

            $formModel = $this->getModel();

            if (empty($formModel->getRowId())) {

                $rowid = $formModel->data["rowid"];
                $form_id = $formModel->getId();
                $listModel =  $formModel->getListModel();
                $table = $listModel->getTable()->db_table_name;
                $table_elements = $formModel->getElementOptions(false, 'name', false, false, array(), '', true);
                $table_elements = json_encode($table_elements);
                $itemid = $mainframe->input->get('Itemid');
                $groups = json_encode($formModel->getFormGroups(true));

                echo "
            <script>
                Swal.fire({
                    type: 'info',
                    title: '" . JText::_('DATA_FOUND') . "',
                    text: '" . JText::_('DO_YOU_WISH_TO_DUPLICATE') . "',
                    showCancelButton: true,
                })
                .then(() => {
                    var xhr = new XMLHttpRequest();
                    var myFormData = new FormData();
                    myFormData.append('fnum', '$rowid');
                    myFormData.append('form_id', '$form_id');
                    myFormData.append('itemId', $itemid);
                    myFormData.append('table', '$table');
                    myFormData.append('groups', JSON.stringify($groups));
                    myFormData.append('table_elements', JSON.stringify($table_elements));
                    
                    xhr.open('POST', 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&g=form&plugin=emundusduplicatedata&method=ajax_duplicate', true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText != '') {
                            document.location.reload();
                        }
                    }
                    xhr.send(myFormData);
                }).catch(() => {
                  Swal.fire({
                    type: 'warning',
                    title: '" . JText::_('ERROR_ON_DUPLCATION') . "'
                    });
                });
            </script>
        
        ";
            }
        }
    }
    
    
    public function onAjax_duplicate() {

        $jinput = $this->app->input;
        $fnum = $jinput->post->get('fnum');
        $table = $jinput->post->get('table');
        $groups = json_decode($jinput->post->getString('groups'));
        $table_elements = json_decode($jinput->post->getString('table_elements'));


        // check if data stored for current user
        try {
            $db = JFactory::getDBO();

            $elements = array();

            foreach ($table_elements as $element) {
                $elements[] = $element->value;
            }



            $aid = intval(substr($fnum, 21, 7));
            
            $query = 'SELECT '.implode(',', $elements).' FROM '.$table.' WHERE user='.$aid . ' ORDER BY id DESC';
            $db->setQuery($query);

            $stored = $db->loadAssoc();
            
            
            if (count($stored) > 0) {
                // update form data
                $parent_id = $stored['id'];
                unset($stored['id']);
                unset($stored['fnum']);

                try {
                    $query = 'INSERT INTO '.$table.' (`fnum`, `'.implode('`,`', array_keys($stored)).'`) VALUES('.$db->Quote($fnum).', '.implode(',', $db->Quote($stored)).')';
                    $db->setQuery($query);
                    $db->execute();
                    $id = $db->insertid();

                } catch (Exception $e) {
                    $error = JUri::getInstance().' :: USER ID : '.$aid.' -> '.$e->getMessage();
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                }

                // get data and update current form
                
                $data = array();
                
                if (count($groups) > 0) {
                    foreach ($groups as $key => $group) {
                        $group_params = json_decode($group->gparams);
                        if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {

                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id = '.$group->group_id.' AND table_key LIKE "id" AND table_join_key LIKE "parent_id"';
                            $db->setQuery($query);
                            try {
                                $repeat_table = $db->loadResult();
                            } catch (Exception $e) {
                                $error = JUri::getInstance().' :: USER ID : '.$aid.' -> '.$e->getMessage();
                                JLog::add($error, JLog::ERROR, 'com_emundus');
                                $repeat_table = $table.'_'.$group->group_id.'_repeat';
                            }

                            $data[$group->group_id]['repeat_group'] = $group_params->repeat_group_button;
                            $data[$group->group_id]['group_id'] = $group->group_id;
                            $data[$group->group_id]['element_name'][] = $group->name;
                            $data[$group->group_id]['table'] = $repeat_table;
                        }
                    }
                    if (count($data) > 0) {
                        foreach ($data as $key => $d) {

                            try {
                                $query = 'SELECT '.implode(',', $d['element_name']).' FROM '.$d['table'].' WHERE parent_id='.$parent_id;
                                $db->setQuery( $query );
                                $stored = $db->loadAssoc();

                                if (count($stored) > 0) {
                                    // update form data
                                    unset($stored['id']);
                                    unset($stored['parent_id']);

                                    try {
                                        $query = 'INSERT INTO '.$d['table'].' (`parent_id`, `'.implode('`,`', array_keys($stored)).'`) VALUES('.$id.', '.implode(',', $db->Quote($stored)).')';
                                        $db->setQuery( $query );
                                        $db->execute();
                                    } catch (Exception $e) {
                                        $error = JUri::getInstance().' :: USER ID : '.$aid.' -> '.$e->getMessage();
                                        JLog::add($error, JLog::ERROR, 'com_emundus');
                                    }
                                }

                            } catch (Exception $e) {
                                $error = JUri::getInstance().' :: USER ID : '.$aid.' -> '.$e->getMessage();
                                JLog::add($error, JLog::ERROR, 'com_emundus');
                            }
                        }
                    }
                }
            }

            $data = ['status' => 200];
            echo json_encode($data);
            return json_encode($data);
        } catch (Exception $e) {
            JLog::add($error, JLog::ERROR, 'com_emundus');
        }
    }

    private function checkData($params) {

    }
}