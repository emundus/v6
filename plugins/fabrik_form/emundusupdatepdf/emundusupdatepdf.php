<?php

//First start with information about the Plugin and yourself. For example:
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik_form.emundusupdatepdf
 *
 * @copyright   Copyright
 * @license     License, for example GNU/GPL
 */

//To prevent accessing the document directly, enter this code:
// no direct access
defined('_JEXEC') or die();

class PlgFabrik_FormEmundusupdatepdf extends plgFabrik_Form {

    public function onBeforeStore()
    {
        $formModel = $this->getModel();

        $data = $formModel->formData;
        $sqlData =  $this->selectAdmission($data['jos_emundus_admission___fnum']);
        $admissionData = [];
        foreach ($sqlData as $key => $exceptValue) {
           if($key == 'date_time'){
               continue;
           }
           if($key == 'id'){
               continue;
           }
           if($key == 'user'){
               continue;
           }
           if ($key == 'fnum') {
               continue;
           }
            $admissionData[$key] = $exceptValue;
        }

        $elements=[];
        $arrayfabrikelements = [];
        foreach (array_keys($data) as $fabrikName) {

            $eltName = explode('___', $fabrikName );

            if (is_array($eltName) && !empty($eltName[1])){
                if (strpos($eltName[1], '_raw') !== false)
                $elements[$eltName[1]] = $data[$fabrikName];

            }
        }

        foreach ($elements as $key =>  $fabrikElements){
            if (is_array($fabrikElements)){
                $fabrikElements = $fabrikElements[0];
            }
            if($key == 'date_time_raw'){
                continue;
            }
            if($key == 'id_raw'){
                continue;
            }
            if($key == 'user_raw'){
                continue;
            }
            if($key == 'fnum_raw'){
                continue;
            }
            $arrayfabrikelements[$key] = $fabrikElements;
        }
        $result = array_diff($arrayfabrikelements,$admissionData);

        if(!empty($result)){
            $fnum = $formModel->formData['jos_emundus_admission___fnum'];
            $uid = (int)substr($fnum,-7);
            $i = 0;

            $formid = $formModel->formData['formid'];
            foreach ($result as $key => $value){

                $elt = explode('_raw',$key);
                $eltId[$i] = $this->selectFabrikEltName($elt[0],$formid);
                $i++;
            }

            $elementId = json_encode($eltId);
            $insertUpdatedData = $this->insertUpdatedInfo($fnum,$uid,$elementId);
        }
    }
    public function selectAdmission($fnum){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Conditions for which records should be updated.
        $conditions = $db->quoteName('ea.fnum') . '=' . $db->quote($fnum);

        $query
            ->select('*')
            ->from($db->quoteName('#__emundus_admission', 'ea'))
            ->where($conditions);
        //die($query->__toString());
        $db->setQuery($query);

        return $db->loadAssoc();
    }
    public function insertUpdatedInfo($fnum, $uid, $fabrikElt){
        $db = JFactory::getDbo();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Insert columns.
        $columns = array('fnum', 'user_id', 'date_time', 'fabrik_element_id');
        $date = date('Y-m-d');
        // Insert values.
        $values = array($db->quote($fnum), $uid, $db->quote($date), $db->quote($fabrikElt));

        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__emundus_updated'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        $db->execute();
    }
    public function selectFabrikEltName($eltName,$formid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Conditions for which records should be updated.
        $conditions = $db->quoteName('fe.name') . ' LIKE ' . $db->quote($eltName). ' AND ' . $db->quoteName('ffg.form_id') . '=' . $formid ;

        $query
            ->select($db->quoteName(array('fe.name')))
            ->from($db->quoteName('#__fabrik_elements', 'fe'))
            ->join('LEFT', $db->quoteName('#__fabrik_formgroup', 'ffg'). ' ON ' . $db->quoteName('fe.group_id') . ' = ' . $db->quoteName('ffg.group_id') )
            ->join('LEFT', $db->quoteName('#__fabrik_forms', 'ff'). ' ON ' . $db->quoteName('ff.id') . ' = ' . $db->quoteName('ffg.form_id'))
            ->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }
}