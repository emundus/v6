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

class EmundusworkflowModelitem extends JModelList
{
    var $db = null;
    var $query = null;
    var $workflow_model = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);

        $this->workflow_model = JModelLegacy::getInstance('workflow', 'EmundusworkflowModel');          /// get workflow_model
    }

    //// get item menu
    public function getItemMenu() {
        try {
            $this->query->clear()
                ->select('*')
                ->from($this->db->quoteName('#__emundus_workflow_item_type'));

            $this->db->setQuery($this->query);
            return $this->db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot get item menu' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    //// get items by step --> params ==> $sid
    public function getAllItemsByStep($sid) {
        if(!empty($sid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.step_id') . '=' . (int)$sid);

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get all items by step' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //get init bloc by workflow --> get init bloc by step
    public function getInitIDByStep($sid) {
        if(!empty($sid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.step_id') . '=' . (int)$sid)
                    ->andWhere($this->db->quoteName('#__emundus_workflow_item.item_id') . '=' . 1);

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get init bloc by step' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get count items by step --> params ==> $data['item_id'], $data['step_id']
    public function getCountItemByID($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->select('count(*)')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.item_id') . ' = ' . (int)$data['item_id'])
                    ->andWhere($this->db->quoteName('#__emundus_workflow_item.step_id') . ' = ' . (int)$data['step_id']);

                $this->db->setQuery($this->query);
                return $this->db->loadResult();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot count items' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// create new item --> params ==> $data
    public function createItem($data) {
        if (!empty($data)) {
            try {
                //// step 1 --> create new item --> add new row in database
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_item'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));

                $this->db->setQuery($this->query);
                $this->db->execute();
                $_itemID = $this->db->insertid();

                //// step 2 --> get the CSS style of this item
                $this->query->clear()
                    ->select('#__emundus_workflow_item_type.CSS_style')
                    ->from($this->db->quoteName('#__emundus_workflow_item_type'))
                    ->where($this->db->quoteName('#__emundus_workflow_item_type.id') . '=' . (int)$data['item_id']);

                $this->db->setQuery($this->query);
                $_CSSStyle = $this->db->loadObject();

                // step 3 --> save this item to database
                $data['style'] = $_CSSStyle->CSS_style;
                $data['id'] = $_itemID;

                $this->saveItemById($data);

                return array('id' => $_itemID, 'style' => $_CSSStyle, 'saving_status' => $this->saveItemById($data));
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create new item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// delete item --> params ==> $id
    public function deleteItem($data) {
        if(!empty($data)) {
            try {
                /// step 1 --> delete item by id
                $this->query->clear()
                    ->delete($this->db->quoteName("#__emundus_workflow_item"))
                    ->where($this->db->quoteName('#__emundus_workflow_item.id') . ' = ' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                /// step 2 --> update workflow logs
                $this->workflow_model->workflowLastActivity($data['workflow_id']);
                $this->workflow_model->workflowLastActivity($data['workflow_id'], 'saved_at');

                return (object)['message'=>true];
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot delete item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get item by id --> use to call modal
    public function getItemByID($id) {
        if(!empty($id)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*, #__emundus_workflow_item_type.CSS_style')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->leftJoin($this->db->quoteName('#__emundus_workflow_item_type') .
                        ' ON '
                        . $this->db->quoteName('#__emundus_workflow_item.item_id') .
                        '='
                        . $this->db->quoteName('#__emundus_workflow_item_type.id')
                    )
                    ->where($this->db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$id);

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get item by id: ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// save item by id
    public function saveItemById($data) {
        if(!empty($data)) {
            try {
                //// step 1 --> save all item attributs
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow_item'))
                    ->set($this->db->quoteName('#__emundus_workflow_item.axisX') . '=' . $data['axisX'] .
                        ',' . $this->db->quoteName('#__emundus_workflow_item.axisY') . '=' . $data['axisY'] .
                        ',' . $this->db->quoteName('#__emundus_workflow_item.item_label') . '=' . $this->db->quote($data['item_label']) .
                        ',' . $this->db->quoteName('#__emundus_workflow_item.style') . '=' . $this->db->quote($data['style']))
                    ->where($this->db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                //// step 2 --> update workflow logs (last_activity, saved_at)
                $this->workflow_model->workflowLastActivity($data['workflow_id']);
                $this->workflow_model->workflowLastActivity($data['workflow_id'], 'saved_at');

                return array('message' => 'true', 'data' => $this->db->execute());
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot save item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// create link between item
    public function createLink($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_links'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));
                $this->db->setQuery($this->query);
                $this->db->execute();
                return $this->db->insertid();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create link : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //DELETE LINK BETWEEN TWO ITEMS
    public function deleteLink($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_workflow_links'))
                    ->where($this->db->quoteName('#__emundus_workflow_links.id') . '=' . (int)$data);

                $this->db->setQuery($this->query);
                return $this->db->execute();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot delete link : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //get link by item (_from or _to)
    public function getLinkByItem($mode=null,$data) {
        if(!empty($data)) {
            try {
                if ($mode == 'from') {
                    //get link by from
                    $this->query->clear()
                        ->select('#__emundus_workflow_links.*')
                        ->from($this->db->quoteName('#__emundus_workflow_links'))
                        ->where($this->db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_from']);
                }

                else if ($mode == 'to') {
                    //get link by to
                    $this->query->clear()
                        ->select('#__emundus_workflow_links.*')
                        ->from($this->db->quoteName('#__emundus_workflow_links'))
                        ->where($this->db->quoteName('#__emundus_workflow_links.to') . '=' . (int)$data['_to']);
                }

                else {
                    //get link by both from and to
                    $this->query->clear()
                        ->select('#__emundus_workflow_links.*')
                        ->from($this->db->quoteName('#__emundus_workflow_links'))
                        ->where($this->db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_from'])
                        ->andWhere($this->db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_to']);
                }

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get link by item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //GET ALL LINKS FROM WORKFLOW ID
    public function getAllLinksByStep($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_links.*')
                    ->from($this->db->quoteName('#__emundus_workflow_links'))
                    ->where($this->db->quoteName('#__emundus_workflow_links.step_id') . '=' . (int)$data);

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get links : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //UPDATE PARAMS --> table [ jos_emundus_workflow_item ] // column [ params ]
    public function updateParamsByItemID($data) {

        if(!empty($data)) {
            $_string = "";

            if (isset($data['params']['inputStatus'])) {
                foreach ($data['params']['inputStatus'] as $key => $value) {
                    if ($value == "true") {
                        $_string .= (string)$key . ",";
                        $_lastString = substr_replace($_string, "", -1);
                        $data['params']['inputStatus'] = $_lastString;
                    } else {
                    }
                }
            } else {
            }

            $id = (int)$data['params']['id'];

            unset($data['params']['id']);
            unset($data['params']['type']);
            unset($data['params']['label']);
            unset($data['params']['x']);
            unset($data['params']['y']);
            unset($data['params']['background']);

            try {
                if (isset($data['params']['color'])) {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow_item'))
                        ->set($this->db->quoteName('#__emundus_workflow_item.params') . '=' . $this->db->quote(json_encode($data['params'])))
                        ->set($this->db->quoteName('#__emundus_workflow_item.style') . '=' . $this->db->quote($data['params']['color']))
                        ->where($this->db->quoteName('#__emundus_workflow_item.id') . '=' . $id);
                } else {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow_item'))
                        ->set($this->db->quoteName('#__emundus_workflow_item.params') . '=' . $this->db->quote(json_encode($data['params'])))
                        ->where($this->db->quoteName('#__emundus_workflow_item.id') . '=' . $id);
                }

                $this->db->setQuery($this->query);
                $this->db->execute();

                $this->workflow_model->workflowLastActivity($data['workflow_id']);
                $this->workflow_model->workflowLastActivity($data['workflow_id'], 'saved_at');

                return (object)['message'=>true];
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot update params : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //A1, B1 --> from itself --> need to refactor
    public function getStatusByCurrentItem($iid,$mode) {
        if(!empty($iid) and !empty($mode)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$iid);

                $this->db->setQuery($this->query);
                $_result = $this->db->loadObject();

                $_exportStatus = array('in' => [], 'out' => []);     //empty key-value pair array

                //check the item type
                if (($_result->item_id) == 2) {
                    $_exportStatus['in'] = json_decode(($_result->params))->inputStatus;
                    $_exportStatus['out'] = json_decode(($_result->params))->outputStatus;
                } else if (($_result->item_id) == 3) {
                }

                $query2 = $this->db->getQuery(true);
                if ($mode == 'in' and isset($_exportStatus['in'])) {
                    $query2->clear()
                        ->select('#__emundus_setup_status.*')
                        ->from($db->quoteName('#__emundus_setup_status'))
                        ->where($db->quoteName('#__emundus_setup_status.step') . 'IN (' . $_exportStatus['in'] . ')');
                    $db->setQuery($query2);

                    return $db->loadObjectList();
                } else if (isset($_exportStatus['out']) and $mode == 'out') {
                    $query2->clear()
                        ->select('#__emundus_setup_status.*')
                        ->from($db->quoteName('#__emundus_setup_status'))
                        ->where($db->quoteName('#__emundus_setup_status.step') . 'IN (' . $_exportStatus['out'] . ')');
                    $db->setQuery($query2);

                    return $db->loadObjectList();
                }
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get status by this item : ' . preg_replace("/[\r\n]/", " ", $query2->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
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
        if(!empty($id)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$id);

                $this->db->setQuery($this->query);
                $_data = $this->db->loadObject();

                $_exportData = array();

                if ($_data->item_id == 2) {
                    $_profileArray = array("profile" => array(), "notes" => array(), "color" => array());
                    $_profileArray['profile'] = json_decode($_data->params)->formNameSelected;
                    $_profileArray['notes'] = json_decode($_data->params)->notes;
                    $_profileArray['color'] = json_decode($_data->params)->color;
                    $_exportData = $_profileArray;
                }

                if ($_data->item == 3) {
                }

                return $_exportData;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get non-status params : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    // auto-find the item having the same output status --> params --> $data['wid']
    public function matchAllLinksByWorkflow($wid) {
        if(!empty($wid)) {
            try {
                ///find all 'params' of workflow --> return an object list
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid);

                $this->db->setQuery($this->query);
                $_allParams = $this->db->loadObjectList();

                $_inputStatusList = array();
                $_outputStatusList = array();

                $_exportData = array();

                foreach ($_allParams as $key => $value) {
                    if ($value->item_id == 1 || $value->item_id == 5 || $value->item_name == 'Initialisation' || $value->item_name == 'Cloture' || $value->item_id == 4 || $value->item_name == 'Message') {
                        unset($_allParams[$key]);
                    } else {
                        $_outputStatusList[$value->id] = json_decode($value->params)->outputStatus;
                        $_inputStatusList[$value->id] = json_decode($value->params)->inputStatus;
                    }
                }

                foreach ($_inputStatusList as $key => $val) {
                    $_inArray = explode(',', $val);

                    foreach ($_outputStatusList as $k => $v) {
                        if (in_array($v, $_inArray)) {

                            $_lst = $key . '...' . $k;
                            if ($key !== $k) {
                                array_push($_exportData, $_lst);
                            } else {
                            }
                        }
                    }
                }

                return $_exportData;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot find suitable link between item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
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
            die; //// fill in up
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/item | Cannot check the link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }
}
