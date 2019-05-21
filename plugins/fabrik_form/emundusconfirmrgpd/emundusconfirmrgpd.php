<?php
/**
 * Created by PhpStorm.
 * User: benja
 * Date: 16/05/2019
 * Time: 15:37
 */

//First start with information about the Plugin and yourself. For example:
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik_form.emundusconfirmrgpd
 *
 * @copyright   Copyright
 * @license     License, for example GNU/GPL
 */

//To prevent accessing the document directly, enter this code:
// no direct access
defined('_JEXEC') or die();
include_once(JPATH_BASE.'/components/com_emundus/controllers/messages.php');

class PlgFabrik_FormEmundusconfirmrgpd extends plgFabrik_Form
{

    public function onAfterProcess()
    {

        $formModel = $this->getModel();
        $current_user = JFactory::getUser();


        $status = $this->selectRequest($formModel->formData['jos_privacy_requests___email'],$formModel->formData['jos_privacy_requests___confirm_token']);

        //var_dump($status->status).die();
        // A request can only be confirmed if it is in a pending status and has a confirmation token
        if ($status->status != '0' || !$status->confirm_token) {
            $this->setError(JText::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

            return false;
        }

        // A request can only be confirmed if the token is less than 24 hours old
        $confirmTokenCreatedAt = new JDate($formModel->formData['jos_privacy_requests___confirm_token_created_at']);
        $confirmTokenCreatedAt->add(new DateInterval('P1D'));

        $now = new JDate('now');

        if ($now > $confirmTokenCreatedAt) {
            // Invalidate the request

            $this->updateRequest($formModel->formData['jos_privacy_requests___email'],-1,$formModel->formData['jos_privacy_requests___request_type'][0]);

            $this->setError(JText::_('COM_PRIVACY_ERROR_CONFIRM_TOKEN_EXPIRED'));

            return false;
        }

        // Everything is good to go, transition the request to confirmed
        if($status->request_type == 'export'){

            $this->updateConsent($current_user->id,1);
            $this->updateRequest($formModel->formData['jos_privacy_requests___email'], 1,$formModel->formData['jos_privacy_requests___request_type'][0]);

        }
        if($status->request_type == 'remove'){

            $this->updateConsent($current_user->id,2);
            $this->updateRequest($formModel->formData['jos_privacy_requests___email'], 1,$status->request_type);

        }


        // Push a notification to the site's super users, deliberately ignoring if this process fails so the below message goes out
        //JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/models', 'MessagesModel');
        //JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/tables');

        /** @var MessagesModelMessage $messageModel */
        //$messageModel = JModelLegacy::getInstance('Message', 'MessagesModel');
        $app = JFactory::getApplication();

        $linkMode = $app->get('force_ssl', 0) == 2 ? 1 : -1;
        $lbl =  'confirm_rgpd';

        $post = [
            'EMAIL' => $formModel->formData['jos_privacy_requests___email'],
            'URL'   => JRoute::link('site', $this->params->get('emunduslist_url'), false, $linkMode),
        ];

        $coordinatorMail = $this->params->get('emundusdpo_email');
        $c_messages = new EmundusControllerMessages();

        $c_messagesUser = $c_messages->sendEmailNoFnum($coordinatorMail, $lbl, $post);

        /*$messageModel->notifySuperUsers(
            JText::_('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CONFIRMED_REQUEST_SUBJECT'),
            JText::sprintf('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CONFIRMED_REQUEST_MESSAGE', $table->email)
        );*/

        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

       $table = $this->getTable();

        $message = array(
            'action'       => 'request-created',
            'requesttype'  => $table->request_type,
            'subjectemail' => $table->email,
            'id'           => $table->id,
            'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
        );
        /** @var ActionlogsModelActionlog $model */
        $model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
        $model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_CREATED_REQUEST', 'com_privacy.request');

        if ($c_messagesUser instanceof JException)
        {
            // JError was already called so we just need to return now
            return false;
        }
        elseif ($c_messagesUser === false)
        {
            $message = $this->setError($c_messagesUser->ErrorInfo);
            $app->redirect(JRoute::_('index.php?option=com_privacy&view=request', false), $message, 'error');
            return false;
        }
        else{
            $app->redirect(JRoute::_(JUri::root()), JText::_('COM_PRIVACY_CONFIRM_REQUEST_SUCCEEDED'), 'info');
            return true;
        }

    }

    public function updateRequest($email,$state,$request_type){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('status') . ' = ' . $state ,
            $db->quoteName('confirm_token') . ' = ' . $db->quote('') ,
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('email') . ' LIKE ' . $db->quote($email),
            $db->quoteName('request_type') . ' LIKE ' . $db->quote($request_type)
        );

        $query->update($db->quoteName('#__privacy_requests'))->set($fields)->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }
    public function updateConsent($id,$state){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('state') . ' = ' . $state ,
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('user_id') . ' = ' . $id
        );

        $query->update($db->quoteName('#__privacy_consents'))->set($fields)->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }
    protected function selectRequest($email, $confirm_token){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Conditions for which records should be updated.
        $conditions = $db->quoteName('email') . 'LIKE' . $db->quote($email). ' AND ' .$db->quoteName('confirm_token'). ' LIKE ' .$db->quote($confirm_token) ;

        $query
            ->select($db->quoteName(array('pr.*')))
            ->from($db->quoteName('#__privacy_requests', 'pr'))
            ->where($conditions);
        //die($query->__toString());
        $db->setQuery($query);

        return $db->loadObject();
    }
}