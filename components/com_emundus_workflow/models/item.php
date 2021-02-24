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

    //get all items from database
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
            JLog::add('component/com_emundus_workflow/models/workflow | Cannot get all item types' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }

//        var_dump($query->__toString()); die;
        //var_dump($db->loadObjectList()); die;
    }

    //create new item --> params = type, name
    public function createItem($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        //falang
        //$falang = JModelLegacy::getInstance('falang', 'EmundusworkflowModel');

        if(!empty($data)) {


            $query->clear()
                ->insert($db->quoteName('#__emundus_workflow_item'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                var_dump($data);
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
}
