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
use Joomla\CMS\Date\Date;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelitem extends JModelList
{
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    //GET ALL TYPES OF ELEMENT
    public function getAllItems() {
        $db = JFactory::getDbo();
        $query =$db->getQuery(true);

        try {
            //query string
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_workflow_item_type'));

            //execute query string
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get all item types' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //GET ALL ITEMS BY WORKFLOW ID
    public function getAllItemsByWorkflowId($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$id);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get all item by workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //GET INIT BLOC BY WORKFLOW ID
    public function getInitIDByWorkflow($wid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid)
                ->andWhere($db->quoteName('#__emundus_workflow_item.item_id') . '=' . 1);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get init item by workflow' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //GET COUNT HOW MANY ITEM BY WORKFLOW ID
    public function getCountItemByID($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.item_id') . ' = ' . (int)$data['item_id'])
                ->andWhere($db->quoteName('#__emundus_workflow_item.workflow_id') . ' = ' . (int)$data['workflow_id']);

            $db->setQuery($query);
            return $db->loadResult();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot count items' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //CREATE NEW ITEM
    public function createItem($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($data)) {
            $query->clear()
                ->insert($db->quoteName('#__emundus_workflow_item'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                return $db->insertid();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create new item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //DELETE ITEM
    public function deleteItem($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try{
            $query->clear()
                ->delete($db->quoteName("#__emundus_workflow_item"))
                ->where($db->quoteName('#__emundus_workflow_item.id') . ' = ' . (int)$data);

            $db->setQuery($query);

            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot delete item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //GET ITEM BY ID
    public function getItemByID($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*, #__emundus_workflow_item_type.CSS_style')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->leftJoin($db->quoteName('#__emundus_workflow_item_type') .
                    ' ON '
                    . $db->quoteName('#__emundus_workflow_item.item_id') .
                    '='
                    . $db->quoteName('#__emundus_workflow_item_type.id')
                )
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int) $id);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //SAVE ALL ITEMS
    public function saveWorkflow($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $data['last_saved'] = date('Y-m-d H:i:s');

        try {
            //save item
            $query->clear()
                ->update($db->quoteName('#__emundus_workflow_item'))
                ->set($db->quoteName('#__emundus_workflow_item.axisX') . '=' . $data['axisX'] .
                    ',' . $db->quoteName('#__emundus_workflow_item.axisY') . '=' . $data['axisY'] .
                    ',' . $db->quoteName('#__emundus_workflow_item.item_label') . '=' . $db->quote($data['item_label']) .
                    ',' . $db->quoteName('#__emundus_workflow_item.last_saved') . '=' . $db->quote($data['last_saved']) .
                    ',' . $db->quoteName('#__emundus_workflow_item.style') . '=' . $db->quote($data['style']) .
                    ',' . $db->quoteName('#__emundus_workflow_item.saved_by') .   '=' . (int)$data['saved_by']
                )
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['id']);

            $db->setQuery($query);
            $db->execute();

            //get workflow id
            $query->clear()
                ->select('#__emundus_workflow_item.workflow_id')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['id']);

            $db->setQuery($query);
            $_workflow_id = $db->loadObject()->workflow_id;

            //update last saved workflow
            $query->clear()
                ->update($db->quoteName('#__emundus_workflow'))
                ->set($db->quoteName('#__emundus_workflow.updated_at') . '=' . $db->quote($data['last_saved']) .
                    ',' . $db->quoteName('#__emundus_workflow.user_id') . '=' . (int)$data['saved_by']
                )
                ->where($db->quoteName('#__emundus_workflow.id') . '=' . (int)$_workflow_id);

            $db->setQuery($query);
            $db->execute();

            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot save item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //CREATE NEW LINK BETWEEN TWO ITEMS
    public function createLink($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($data)) {
            try {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_workflow_links'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->quote(array_values($data))));
                $db->setQuery($query);
                $db->execute();
                return $db->insertid();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //DELETE LINK BETWEEN TWO ITEMS
    public function deleteLink($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->delete($db->quoteName('#__emundus_workflow_links'))
                ->where($db->quoteName('#__emundus_workflow_links.id') . '=' . (int)$data);

            $db->setQuery($query);
            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot delete link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //get link by item (_from or _to)
    public function getLinkByItem($mode=null,$data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            if($mode == 'from') {
                //get link by from
                $query->clear()
                    ->select('#__emundus_workflow_links.*')
                    ->from($db->quoteName('#__emundus_workflow_links'))
                    ->where($db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_from']);
                $db->setQuery($query);
            }
            else if($mode == 'to') {
                //get link by to
                $query->clear()
                    ->select('#__emundus_workflow_links.*')
                    ->from($db->quoteName('#__emundus_workflow_links'))
                    ->where($db->quoteName('#__emundus_workflow_links.to') . '=' . (int)$data['_to']);
                $db->setQuery($query);
            }
            else {
                //get link by both from and to
                $query->clear()
                    ->select('#__emundus_workflow_links.*')
                    ->from($db->quoteName('#__emundus_workflow_links'))
                    ->where($db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_from'])
                    ->andWhere($db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_to']);
                $db->setQuery($query);
            }
//            var_dump($db->loadObjectList());die;
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get link by item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //GET ALL LINKS FROM WORKFLOW ID
    public function getAllLinksByWorkflowID($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_links.*')
                ->from($db->quoteName('#__emundus_workflow_links'))
                ->where($db->quoteName('#__emundus_workflow_links.workflow_id') . '=' . (int)$data);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get links : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //UPDATE PARAMS --> table [ jos_emundus_workflow_item ] // column [ params ]
    public function updateParamsByItemID($data) {
        $_string = "";

        if(isset($data['inputStatus'])) {
            foreach($data['inputStatus'] as $key=>$value) {
                if($value == "true") {
                    $_string .= (string)$key . ",";
                    $_lastString = substr_replace($_string ,"",-1);
                    $data['inputStatus'] = $_lastString;
                }
                else {}
            }
        }
        else {}

        $id = (int)$data['id'];

        unset($data['id']);
        unset($data['type']);
        unset($data['label']);
        unset($data['x']);
        unset($data['y']);
        unset($data['background']);

        $db = JFactory::getDbo();
        $query= $db->getQuery(true);
        try {
            if(isset($data['color'])) {
                $query->clear()
                    ->update($db->quoteName('#__emundus_workflow_item'))
                    ->set($db->quoteName('#__emundus_workflow_item.params') . '=' . $db->quote(json_encode($data)))
                    ->set($db->quoteName('#__emundus_workflow_item.style') . '=' . $db->quote($data['color']))
                    ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . $id);
            }
            else {
                $query->clear()
                    ->update($db->quoteName('#__emundus_workflow_item'))
                    ->set($db->quoteName('#__emundus_workflow_item.params') . '=' . $db->quote(json_encode($data)))
                    ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . $id);
            }

            $db->setQuery($query);

            return $db->execute();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot update params : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //A1, B1 --> from itself
    public function getStatusByCurrentItem($iid,$mode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$iid);

            $db->setQuery($query);
            $_result = $db->loadObject();

            $_exportStatus = array('in' => [], 'out' => []);     //empty key-value pair array

            //check the item type
            if (($_result->item_id) == 2) {
                $_exportStatus['in'] = json_decode(($_result->params))->inputStatus;
                $_exportStatus['out'] = json_decode(($_result->params))->outputStatus;
            }
            else if (($_result->item_id) == 3) {}

            $query2 = $db->getQuery(true);
            if ($mode == 'in' and isset($_exportStatus['in'])) {
                $query2->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db->quoteName('#__emundus_setup_status'))
                    ->where($db->quoteName('#__emundus_setup_status.step') . 'IN (' . $_exportStatus['in'] . ')');
                $db->setQuery($query2);

                return $db->loadObjectList();
            }

            else if (isset($_exportStatus['out']) and $mode == 'out'){
                $query2->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db->quoteName('#__emundus_setup_status'))
                    ->where($db->quoteName('#__emundus_setup_status.step') . 'IN (' . $_exportStatus['out'] . ')');
                $db->setQuery($query2);

                return $db->loadObjectList();
            }
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get status by this item : ' . preg_replace("/[\r\n]/"," ",$query2->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //A2, B2 --> compare with all remaining items which !== id
    public function getAvailableStatusByItem($data,$mode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.item_id, #__emundus_workflow_item.params')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)(json_decode($data))->wid)
                ->andWhere($db->quoteName('#__emundus_workflow_item.item_id') . '!=' . '1')                     //not initialization
                ->andWhere($db->quoteName('#__emundus_workflow_item.item_id') . '!=' . '4')                     //not message
                ->andWhere($db->quoteName('#__emundus_workflow_item.item_id') . '!=' . '5')                     //not cloture
                ->andWhere($db->quoteName('#__emundus_workflow_item.id') . '!=' . (int)(json_decode($data))->id);

            $db->setQuery($query);
            $_results = $db->loadAssocList();

            $_statusList = array();     //empty array

            foreach($_results as $k=>$v) {
                if($v['item_id'] == 2) { ////2 --> espace candidat

                    if($mode == 'in') {
                        if (json_decode($v['params'])->inputStatus == "") {
                            array_push($_statusList, -1);
                        }

                        else {
                            array_push($_statusList, json_decode($v['params'])->inputStatus);
                        }
                    }

                    if($mode == 'out') {

                        if (json_decode($v['params'])->outputStatus == "") {
                            array_push($_statusList, -1);
                        }

                        else {
                            array_push($_statusList, json_decode($v['params'])->outputStatus);
                        }
                    }
                }

                else if($v['item_id'] == 3) { ////3 --> condition
                    ///
                }
            }

            $_t = array_filter(array_values($_statusList), 'strlen' );      //remove all empty values

            $_lst = "-1,";

            foreach($_t as $key=>$value) {
                $_lst .= $value . ",";
            }

            $_lastString = substr_replace($_lst ,"",-1);

            if($_lastString == "") { array_push($_lastString, -1); }

            $query1 = $db->getQuery(true);

            $query1->clear()
                ->select('#__emundus_setup_status.*')
                ->from($db->quoteName('#__emundus_setup_status'))
                ->where($db->quoteName('#__emundus_setup_status.step') . 'NOT IN (' . $_lastString . ')');

            $db->setQuery($query1);

            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get available status : ' . preg_replace("/[\r\n]/"," ",$query1->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //get all params not being status --> $id
    public function getNonStatusParams($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$id);

            $db->setQuery($query);
            $_data = $db->loadObject();

            $_exportData = array();

            if($_data->item_id == 2) {
                $_profileArray = array("profile" => array(), "notes" => array(), "color" => array());
                $_profileArray['profile'] = json_decode($_data->params)->formNameSelected;
                $_profileArray['notes'] = json_decode($_data->params)->notes;
                $_profileArray['color'] = json_decode($_data->params)->color;
                $_exportData = $_profileArray;
            }

            if($_data->item == 3) {}

            return $_exportData;
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get non-status params : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    // auto-find the item having the same output status --> params --> $data['wid']
    public function matchAllLinksByWorkflow($wid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            ///find all 'params' of workflow --> return an object list
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid);

            $db->setQuery($query);
            $_allParams = $db->loadObjectList();

            $_inputStatusList = array();
            $_outputStatusList = array();

            $_exportData = array();

            foreach($_allParams as $key=>$value) {
                if ($value->item_id == 1 || $value->item_id == 5 || $value->item_name == 'Initialisation' || $value->item_name == 'Cloture' || $value->item_id == 4 || $value->item_name == 'Message') {
                    unset($_allParams[$key]);
                }
                else {
                    $_outputStatusList[$value->id] = json_decode($value->params)->outputStatus;
                    $_inputStatusList[$value->id] = json_decode($value->params)->inputStatus;
                }
            }

            foreach ($_inputStatusList as $key=>$val) {
                $_inArray = explode(',', $val);

                foreach($_outputStatusList as $k=>$v) {
                    if(in_array($v,$_inArray)) {

                        $_lst = $key . '...' . $k;
                        if($key !== $k) {
                            array_push($_exportData, $_lst);
                        }
                        else {}
                    }
                }
            }

            return $_exportData;
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot find suitable link between item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    public function matchLinkByItem($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);           //get current params
        $query1 = $db->getQuery(true);          //get all remaining params

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['id']);
            $db->setQuery($query);

            $_currentParams = $db->loadObject();
            $_currentInputStatus = explode(',', json_decode($_currentParams->params)->inputStatus);     //return an array

            $query1->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$data['wid'])
                ->andWhere($db->quoteName('#__emundus_workflow_item.id') . '!=' . (int)$data['id']);

            $db->setQuery($query1);
            $_allParams = $db->loadObjectList();

            //// match $_currentParams vs $_allParams
            foreach($_allParams as $key => $value) {
                if($value->item_id == 1 || $value->item_id == 4 || $value->item_id == 5 || $value->item_name == 'Initialisation' || $value->item_name == 'Message' || $value->item_name == 'Cloture') {
                    unset($_allParams[$key]);
                }
                else {
                    if(in_array(json_decode($value->params)->outputStatus,$_currentInputStatus)) {
                        return $_allParams[$key]->id;
                    }
                    else {}
                }
            }
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot find suitable link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    // check whether two blocs match or not --> params :: $data['from'] // $data['to']
    public function checkMatchingStatus($data) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query1 = $db->getQuery(true);

        try {
            //get from.params.outputStatus
            $query->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['from']);
            $db->setQuery($query);
            $_fromParams = $db->loadObject();


            //get to.params.inputStatus
            $query1->clear()
                ->select('#__emundus_workflow_item.*')
                ->from($db->quoteName('#__emundus_workflow_item'))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['to']);
            $db->setQuery($query1);
            $_toParams = $db->loadObject();

            // check matching item
            if(is_null($_fromParams->params) || is_null($_toParams) || empty($_fromParams) || empty($_toParams)) {
                return false;
            }

            else {
                $_inArray = explode(',', json_decode($_toParams->params)->inputStatus);

                if(in_array(json_decode($_fromParams->params)->outputStatus, $_inArray)) {
                    return true;
                }
                else {
                    return false;
                }
            }

        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get matching item : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //check whether there are links from an item --> if yes --> remove
    public function checkExistLink($data) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_workflow_links'))
                ->where($db->quoteName('#__emundus_workflow_links.to') . '=' . (int)$data);
            $db->setQuery($query);

            $_count = $db->loadResult();

            if((int)$_count > 0 ) {
                return true;
            }
            else {
                return false;
            }
            die;
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot check the link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }
}
