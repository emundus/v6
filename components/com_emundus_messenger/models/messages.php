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

    function getFilesByUser() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getSession()->get('emundusUser');

        try {
            $query->select('sc.*,cc.fnum,cc.published as file_publish')
                ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
                ->where($db->quoteName('cc.applicant_id') .' = ' . $user->id)
                ->group('sc.id');
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get files associated to user : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    function getMessagesByFnum($fnum,$offset = 0) {
        $db = JFactory::getDbo();

        $user = JFactory::getSession()->get('emundusUser');

        try {
            $query = "SELECT distinct(CAST(m.date_time AS DATE)) as dates,group_concat(m.message_id) as messages
                FROM `jos_messages` AS `m`
                LEFT JOIN `jos_emundus_chatroom` AS `c` ON `c`.`id` = `m`.`page`
                LEFT JOIN `jos_users` AS `u` ON `u`.`id` = `m`.`user_id_from`
                WHERE `c`.`fnum` LIKE ".$db->quote($fnum).
                " group by CAST(m.date_time AS DATE)
                ORDER BY m.date_time";
            $db->setQuery($query);
            $dates = $db->loadObjectList();

            foreach ($dates as $key => $date){
                $dates[$key]->messages = explode(',',$date->messages);
            }

            $query = $db->getQuery(true);

            $query->select('m.*,u.name')
                ->from($db->quoteName('#__messages','m'))
                ->leftJoin($db->quoteName('#__emundus_chatroom','c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('m.page'))
                ->leftJoin($db->quoteName('#__users','u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('m.user_id_from'))
                ->where($db->quoteName('c.fnum') .' LIKE ' . $db->quote($fnum))
                ->order('m.date_time DESC');
                //->setLimit(10);
            $db->setQuery($query,$offset);

            $datas = new stdClass;
            $datas->dates = $dates;
            $datas->messages = array_reverse($db->loadObjectList());

            return $datas;
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
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
                ->set($db->quoteName('state') . ' = 0')
                ->set($db->quoteName('message') . ' = ' . $db->quote($message))
                ->set($db->quoteName('page') . ' = ' . $db->quote($chatroom));
            $db->setQuery($query);
            $db->execute();

            $new_message = $db->insertid();
            return $this->getMessageById($new_message);
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages with ID '. $id . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    function getNotifications($user){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('count(m.message_id) as notifications,ec.fnum')
                ->from($db->quoteName('#__messages','m'))
                ->leftJoin($db->quoteName('#__emundus_chatroom','ec').' ON '.$db->quoteName('ec.id').' = '.$db->quoteName('m.page'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature','cc').' ON '.$db->quoteName('cc.fnum').' = '.$db->quoteName('ec.fnum'))
                ->where($db->quoteName('cc.applicant_id') . ' = ' . $db->quote($user))
                ->andWhere($db->quoteName('m.state') . ' = 0')
                ->andWhere($db->quoteName('m.user_id_from') . ' <> ' . $user)
                ->group('ec.fnum');
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    function getNotificationsByFnum($fnum){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getUser();

        try {
            $query->select('count(m.message_id)')
                ->from($db->quoteName('#__messages','m'))
                ->leftJoin($db->quoteName('#__emundus_chatroom','ec').' ON '.$db->quoteName('ec.id').' = '.$db->quoteName('m.page'))
                ->where($db->quoteName('ec.fnum') . ' = ' . $db->quote($fnum))
                ->andWhere($db->quoteName('m.state') . ' = 0')
                ->andWhere($db->quoteName('m.user_id_from') . ' <> ' . $user->id);
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get messages associated to user : '. $user->id . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    function markAsRead($fnum){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getUser();

        try {
            $query->select('m.message_id')
                ->from($db->quoteName('#__messages','m'))
                ->leftJoin($db->quoteName('#__emundus_chatroom','ec').' ON '.$db->quoteName('ec.id').' = '.$db->quoteName('m.page'))
                ->where($db->quoteName('ec.fnum') . ' LIKE ' . $db->quote($fnum))
                ->andWhere($db->quoteName('m.state') . ' = 0')
                ->andWhere($db->quoteName('m.user_id_from') . ' <> ' . $user->id);
            $db->setQuery($query);
            $messages_readed = $db->loadColumn();

            if(!empty($messages_readed)) {
                $query->clear()
                    ->update($db->quoteName('#__messages'))
                    ->set($db->quoteName('state') . ' = 1')
                    ->where($db->quoteName('message_id') . ' IN (' . implode(',', $messages_readed) . ')');
                $db->setQuery($query);
                $db->execute();
            }

            return sizeof($messages_readed);
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to mark messages as read with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getDocumentsByCampaign($fnum,$applicant){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            if ($applicant == 'true'){
                $query->select('attachments')
                    ->from($db->quoteName('#__emundus_chatroom'))
                    ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
                $db->setQuery($query);
                $attachment_allowed = $db->loadResult();

                $query->clear()
                    ->select('id,value')
                    ->from($db->quoteName('#__emundus_setup_attachments'))
                    ->where($db->quoteName('id') . ' IN (' . $attachment_allowed . ')');
                $db->setQuery($query);
            } else {
                $query->select('sc.profile_id')
                    ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
                    ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $db->quoteName('cc.campaign_id') . ' = ' . $db->quoteName('sc.id'))
                    ->where($db->quoteName('cc.fnum') . ' LIKE ' . $db->quote($fnum));
                $db->setQuery($query);
                $profile_id = $db->loadResult();

                $query->clear()
                    ->select('sa.id,sa.value')
                    ->from($db->quoteName('#__emundus_setup_attachments', 'sa'))
                    ->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles', 'sap') . ' ON ' . $db->quoteName('sap.attachment_id') . ' = ' . $db->quoteName('sa.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('sap.profile_id'))
                    ->where($db->quoteName('sp.id') . ' = ' . $db->quote($profile_id));
                $db->setQuery($query);
            }
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to get documents with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function askAttachment($fnum, $attachment, $message){
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $m_messages = new EmundusModelMessages;

        try{
            $new_message = $this->sendMessage($message,$fnum);

            $query->select('id')
                ->from($db->quoteName('#__emundus_chatroom'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
            $db->setQuery($query);
            $chatroom = $db->loadResult();

            if(empty($chatroom)){
                $chatroom = $m_messages->createChatroom($fnum);
            }

            $query->clear()
                ->select('attachments')
                ->from($db->quoteName('#__emundus_chatroom'))
                ->where($db->quoteName('id') . ' LIKE ' . $db->quote($chatroom));
            $db->setQuery($query);
            $attachment_exist = $db->loadResult();

            if(!empty($attachment_exist)){
                $attachment .= ',' . $attachment_exist;
            }

            $query->clear()
                ->update($db->quoteName('#__emundus_chatroom'))
                ->set($db->quoteName('attachments') . ' = ' . $db->quote($attachment))
                ->where($db->quoteName('id') . ' = ' . $db->quote($chatroom));
            $db->setQuery($query);
            $db->execute();
            return $new_message;
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to ask attachment with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function moveToUploadedFile($fnumInfos,$attachment,$filesrc,$target_file){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getUser();

        try{
            $query->insert($db->quoteName('#__emundus_uploads'))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
                ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnumInfos['fnum']))
                ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($fnumInfos['id']))
                ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($attachment))
                ->set($db->quoteName('filename') . ' = ' . $db->quote($filesrc));
            $db->setQuery($query);
            $result = $db->execute();

            $query->clear()
                ->select('attachments')
                ->from($db->quoteName('#__emundus_chatroom'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
            $db->setQuery($query);
            $attachment_exist = $db->loadResult();

            $attachments_ask = explode(',',$attachment_exist);
            foreach ($attachments_ask as $key => $attach){
                if($attach == $attachment){
                    unset($attachments_ask[$key]);
                }
            }

            if(!empty($attachments_ask)){
                $attachs = implode(',',$attachments_ask);
            } else {
                $attachs = $db->quote(null);
            }
            $query->clear()
                ->update($db->quoteName('#__emundus_chatroom'))
                ->set($db->quoteName('attachments') . ' = ' . $attachs)
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnumInfos['fnum']));
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus_messages/models/messages | Error when try to move file to emundus upload with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
