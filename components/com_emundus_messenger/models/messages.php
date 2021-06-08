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

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_messenger/models');

class EmundusmessengerModelmessages extends JModelList
{
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    function getCampaignsByUser() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getSession()->get('emundusUser');

        try {
            $query->select('sc.*,cc.fnum')
                ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
                ->where($db->quoteName('cc.applicant_id') .' = ' . $user->id)
                ->group('sc.id');
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get campaigns associated to user : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    function getMessagesByFnum($fnum) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getSession()->get('emundusUser');

        try {
            $query->select('m.*,u.name')
                ->from($db->quoteName('#__messages','m'))
                ->leftJoin($db->quoteName('#__emundus_chatroom','c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('m.page'))
                ->leftJoin($db->quoteName('#__users','u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('m.user_id_from'))
                ->where($db->quoteName('c.fnum') .' LIKE ' . $db->quote($fnum));
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id .'and to campaign : '.$cid . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    function sendMessage($message,$fnum){
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getSession()->get('emundusUser');

        $m_messages = new EmundusModelMessages;

        try {
            $query->select('id')
                ->from($db->quoteName('#__emundus_chatroom'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
            $db->setQuery($query);
            $chatroom = $db->loadResult();

            if(empty($chatroom)){
                $chatroom = $m_messages->createChatroom($fnum);
            }

            $query->insert($db->quoteName('#__messages'))
                ->set($db->quoteName('user_id_from') . ' = ' . $db->quote($user->id))
                ->set($db->quoteName('folder_id') . ' = 2')
                ->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('state') . ' = 1')
                ->set($db->quoteName('message') . ' = ' . $db->quote($message))
                ->set($db->quoteName('page') . ' = ' . $db->quote($chatroom));
            $db->setQuery($query);
            $db->execute();

            $new_message = $db->insertid();
            return $this->getMessageById($new_message);
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id .'and to campaign : '.$cid . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    function getMessageById($id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('m.*,u.name')
                ->from($db->quoteName('#__messages','m'))
                ->leftJoin($db->quoteName('#__users','u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('m.user_id_from'))
                ->where($db->quoteName('m.message_id') . ' = ' . $db->quote($id));
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id .'and to campaign : '.$cid . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }
}
