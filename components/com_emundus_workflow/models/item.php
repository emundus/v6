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

//    //GET STYLE FROM ITEM ID
//    public function getStyleFromItemID($data) {
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//
//        try {
//            //left join
//            $query->clear()
//                ->select('#__emundus_workflow_item_type.style')
//                ->from($db->quoteName('#__emundus_workflow_item_type'))
//                ->leftJoin($db->quoteName('#__emundus_workflow_item') .
//                    ' ON '
//                    . $db->quoteName('#__emundus_workflow_item.item_id') .
//                    '='
//                    . $db->quoteName('#__emundus_workflow_item_type.id')
//                )
//                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data);
//
//            $db->setQuery($query);
//
//            return $db->loadObjectList();
//        }
//        catch(Exception $e) {
//            return $e->getMessage();
//        }
//    }

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
                $_string .= (string) $key . ",";
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

//            var_dump($query->__toString());
            return $db->execute();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //GET ALL AVAILABLE STATUS FOR NON-MESSAGE BLOC
    public function getIn($wid) {
        $db1 = JFactory::getDbo();
        $db2 = JFactory::getDbo();

        $query = $db1->getQuery(true);
        $query1 = $db2->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.item_id, #__emundus_workflow_item.params')
                ->from($db1->quoteName('#__emundus_workflow_item'))
                ->where($db1->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid)
                ->andWhere($db1->quoteName('#__emundus_workflow_item.item_id') . '!=' . '1')
                ->andWhere($db1->quoteName('#__emundus_workflow_item.item_id') . '!=' . '4')
                ->andWhere($db1->quoteName('#__emundus_workflow_item.item_id') . '!=' . '5');

            $db1->setQuery($query);
            $_results = $db1->loadAssocList();


            $_statusList = array();     //empty array

            foreach($_results as $k=>$v) {
                if($v['item_id'] == 2) { ////2 --> espace candidat
                    ///
                    if(json_decode($v['params'])->editedStatusSelected == "") {
                        $_statusList[0] = "-1000";
                    }
                    else {
                        array_push($_statusList, json_decode($v['params'])->editedStatusSelected);
                    }
                }

                if($v['item_id'] == 3) { ////3 --> condition
                    /// pass
                }
            }


            /// check if 'params' is empty // editedStatus --> " "

//            var_dump($_statusList);die;
            $_t = array_filter(array_values($_statusList), 'strlen' );      //remove all empty values

            $_test = explode(',', $_t[0]);

            $_lst = "";

            foreach($_test as $k=>$v) {
                $_lst .= $v . ",";
            }

            $_lastString = substr_replace($_lst ,"",-1);

            $query1 = $db2->getQuery(true);

            if(in_array(0,$_t)) {
                $query1->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db2->quoteName('#__emundus_setup_status'))
                    ->where($db2->quoteName('#__emundus_setup_status.step') . 'NOT IN (' . $_lastString . ')');

                $db2->setQuery($query1);
                $array1 = $db2->loadObjectList();

                return $array1;

            }
            else {
                $query1->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db2->quoteName('#__emundus_setup_status'))
                    ->where($db2->quoteName('#__emundus_setup_status.step') . 'NOT IN (' . $_lastString . ')');
                $db2->setQuery($query1);

                $array1 = $db2->loadObjectList();
                return $array1;

            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getOut($wid) {
        $db1 = JFactory::getDbo();
        $db2 = JFactory::getDbo();

        $query = $db1->getQuery(true);
        $query1 = $db2->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_workflow_item.item_id, #__emundus_workflow_item.params')
                ->from($db1->quoteName('#__emundus_workflow_item'))
                ->where($db1->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid)
                ->andWhere($db1->quoteName('#__emundus_workflow_item.item_id') . '!=' . '1')
                ->andWhere($db1->quoteName('#__emundus_workflow_item.item_id') . '!=' . '4')
                ->andWhere($db1->quoteName('#__emundus_workflow_item.item_id') . '!=' . '5');

            $db1->setQuery($query);
            $_results = $db1->loadAssocList();


            $_statusList = array();     //empty array

            foreach($_results as $k=>$v) {
                if($v['item_id'] == 2) { ////2 --> espace candidat
                    array_push($_statusList,json_decode($v['params'])->outputStatusSelected);
                }

                if($v['item_id'] == 3) { ////3 --> condition
                    /// pass
                }
            }

            $query1 = $db2->getQuery(true);

            if(in_array('0',$_statusList)) {
                $query1->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db2->quoteName('#__emundus_setup_status'))
                    ->where($db2->quoteName('#__emundus_setup_status.step') . 'NOT IN (' . implode(",", $db2->quote($_statusList)) . ')');

                $db2->setQuery($query1);
                $array1 = $db2->loadObjectList();
                return $array1;
            }

            else {
                $query1->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db2->quoteName('#__emundus_setup_status'))
                    ->where($db2->quoteName('#__emundus_setup_status.step') . 'NOT IN (' . implode(",", $db2->quote($_statusList)) . ')');
                $db2->setQuery($query1);

                $array1 = $db2->loadObjectList();

                $query3 = $db2->getQuery(true);
                $query3->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($db2->quoteName('#__emundus_setup_status'))
                    ->where($db2->quoteName('#__emundus_setup_status.id') . '=' . 1);

                $db2->setQuery($query3);
                $array3 = $db2->loadObjectList();

                return array_merge($array3,$array1);

            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


}
