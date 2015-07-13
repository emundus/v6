<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';
require_once(JPATH_COMPONENT.DS.'models'.DS.'email.php');

/**
 * Email controller class.
 */
class JcrmControllerEmail extends JcrmController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @since	1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_jcrm.edit.email.id');
        $editId = JFactory::getApplication()->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_jcrm.edit.email.id', $editId);

        // Get the model.
        $model = $this->getModel('Email', 'JcrmModel');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId && $previousId !== $editId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_jcrm&view=emailform&layout=edit', false));
    }

    /**
     * Method to save a user's profile data.
     *
     * @return	void
     * @since	1.6
     */
    public function publish() {
        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getUser();
        if ($user->authorise('core.edit', 'com_jcrm') || $user->authorise('core.edit.state', 'com_jcrm')) {
            $model = $this->getModel('Email', 'JcrmModel');

            // Get the user data.
            $id = $app->input->getInt('id');
            $state = $app->input->getInt('state');

            // Attempt to save the data.
            $return = $model->publish($id, $state);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
            }

            // Clear the profile id from the session.
            $app->setUserState('com_jcrm.edit.email.id', null);

            // Flush the data from the session.
            $app->setUserState('com_jcrm.edit.email.data', null);

            // Redirect to the list screen.
            $this->setMessage(JText::_('COM_JCRM_ITEM_SAVED_SUCCESSFULLY'));
            $menu = & JSite::getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else {
            throw new Exception(500);
        }
    }

	/**
     * @throws Exception
     */
    public function remove() {

        // Initialise variables.
        $app = JFactory::getApplication();

        //Checking if the user can remove object
        $user = JFactory::getUser();
        if ($user->authorise($user->authorise('core.delete', 'com_jcrm'))) {
            $model = $this->getModel('Email', 'JcrmModel');

            // Get the user data.
            $id = $app->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $model->delete($id);


            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            } else {
                // Check in the profile.
                if ($return) {
                    $model->checkin($return);
                }

                // Clear the profile id from the session.
                $app->setUserState('com_jcrm.edit.email.id', null);

                // Flush the data from the session.
                $app->setUserState('com_jcrm.edit.email.data', null);

                $this->setMessage(JText::_('COM_JCRM_ITEM_DELETED_SUCCESSFULLY'));
            }

            // Redirect to the list screen.
            $menu = & JSite::getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else {
            throw new Exception(500);
        }
    }

	/**
     *
     */
    public function getmailcontact()
    {
        $jinput = JFactory::getApplication()->input;
        $contact = $jinput->getString('contact', "");
        $model = new JcrmModelEmail();
        $contactList = $model->getMailContact($contact);
        if(!is_string($contactList))
        {
            echo json_encode($contactList);
        }
        else
        {
            echo json_encode(array('error' => JText::_('ERROR'), 'msg' => $contactList));
        }
        exit();
    }


	/**
     * @throws Exception
     * @throws JException
     */
    public function sendmail()
    {
        $request_body = (object) json_decode(file_get_contents('php://input'));
        $model = new JcrmModelEmail();
        $groups = $request_body->contacts->groups;
        $bodyId = intval($request_body->id);
        $contacts = $request_body->contacts->contacts;
        $subject = $request_body->subject;
        $body = $request_body->body;
        if(!empty($groups))
            $groupList = $model->getContacts($groups);
        else
            $groupList = array();
        $contacts = array_unique(array_merge($contacts, $groupList));
        $mailAdress = $model->getEmailAdr($contacts);
        if($bodyId != 1 && $bodyId != -1)
        {
            $userFrom = $model->getEmailFrom($bodyId);
        }
        else
        {
            $userFrom = new stdClass();
            $userFrom->emailfrom = JFactory::getUser()->email;
            $userFrom->name = JFactory::getUser()->name;
        }

        $cpt = 1;
        $fail = 0;
        $tags = array('[FULL_NAME]','[LAST_NAME]', '[FIRST_NAME]', '[EMAIL]', '[PHONE]', '[ORGANISATION]');
        foreach ($mailAdress as $mail)
        {
            if($cpt % 10 == 0)
            {
                sleep(1);
            }

            $body = str_replace($tags, array($mail['full_name'], $mail['last_name'], $mail['first_name'], $mail['email'], $mail['phone'], $mail['organisation']), $body);

            $sender = array(
                $userFrom->emailfrom,
                $userFrom->name
            );
            $mailer     = JFactory::getMailer();
            $mailer->setSender($sender);
            $mailer->addRecipient($mail['email']);
            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);
//die(var_dump($mailer));
            $send = $mailer->Send();
            if ( $send !== true ) {
                $fail++;
            } else {
                $model->addMessage($mail['email'], $subject, $body);
            }
            $cpt++;
/*
            if (JUtility::sendMail($userFrom->emailfrom, $userFrom->name, $mail['email'], $subject, $body, true))
            {
                $model->addMessage($mail['email'], $subject, $body);
            }
            else
            {
                $fail++;
            }
*/        
        }

        $status = true;
        if($fail == ($cpt - 1))
        {
            $status = false;
            $msg = JText::_('CONTACT_FAIL_SENDING_MAILS');
        }
        else
        {
            $cpt--;
            $sent = $cpt - $fail;
            $msg = $sent . ' ' . JText::_('CONTACT_SENT_WITH_SUCCESS');
            if($fail > 0)
            {
                $msg .= ' ' . $fail . ' ' . JText::_('CONTACT_FAILED');
            }
        }

        echo json_encode((object)array('status' => $status, 'msg' => $msg));
        exit();
    }


	/**
     *
     */
    public function getmailbody()
    {
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->getInt('bid', null);
        $model = new JcrmModelEmail();
        if(!is_null($id))
            $body = $model->getMailBody($id);
        if(!is_string($body))
        {
            echo json_encode($body);
        }
        else
        {
            echo json_encode(array('error' => JText::_('ERROR'), 'msg' => $body));
        }
        exit();
    }
}
