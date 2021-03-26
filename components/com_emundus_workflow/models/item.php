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
    public function saveItems($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $data['last_saved'] = date('Y-m-d H:i:s');

        try {
            $query
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

        if(isset($data['editedStatusSelected'])) {
            foreach($data['editedStatusSelected'] as $key=>$value) {
                if($value == "true") {
                    $_string .= (string)$key . ",";
                }
                else {}
            }
        }
        else {}

        $_lastString = substr_replace($_string ,"",-1);
        $data['editedStatusSelected'] = $_lastString;

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
            $query->clear()
                ->update($db->quoteName('#__emundus_workflow_item'))
                ->set($db->quoteName('#__emundus_workflow_item.params') . '=' . $db->quote(json_encode($data)))
                ->set($db->quoteName('#__emundus_workflow_item.style') . '=' . $db->quote($data['color']))
                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . $id);
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
                $_exportStatus['in'] = json_decode(($_result->params))->editedStatusSelected;
                $_exportStatus['out'] = json_decode(($_result->params))->outputStatusSelected;
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
                        if (json_decode($v['params'])->editedStatusSelected == "") {
                            array_push($_statusList, -1);
                        }

                        else {
                            array_push($_statusList, json_decode($v['params'])->editedStatusSelected);
                        }
                    }

                    if($mode == 'out') {

                        if (json_decode($v['params'])->outputStatusSelected == "") {
                            array_push($_statusList, -1);
                        }

                        else {
                            array_push($_statusList, json_decode($v['params'])->outputStatusSelected);
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
            JLog::add('component/com_emundus_workflow/models/item | Cannot get available status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }
}
