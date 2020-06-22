<?php
/**
 * Created by PhpStorm.
 * User: benja
 * Date: 15/05/2019
 * Time: 17:34
 */

public function em_createRequest($data)
{
	$config = JFactory::getConfig();
	$timezone = new DateTimeZone( $config->get('offset') );
    // Creating requests requires the site's email sending be enabled
    if (!config->get('mailonline', 1))
    {
        $this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'));

        return false;
    }

    // Get the form.
    $form = $this->getForm();
    $data['email'] = JStringPunycode::emailToPunycode($data['email']);

    // Check for an error.
    if ($form instanceof Exception)
    {
        return $form;
    }

    // Filter and validate the form data.
    $data = $form->filter($data);
    $return = $form->validate($data);

    // Check for an error.
    if ($return instanceof Exception)
    {
        return $return;
    }

    // Check the validation results.
    if ($return === false)
    {
        // Get the validation messages from the form.
        foreach ($form->getErrors() as $formError)
        {
            $this->setError($formError->getMessage());
        }

        return false;
    }

    // Search for an open information request matching the email and type
    $db = $this->getDbo();
    $query = $db->getQuery(true)
        ->select('COUNT(id)')
        ->from('#__privacy_requests')
        ->where('email = ' . $db->quote($data['email']))
        ->where('request_type = ' . $db->quote($data['request_type']))
        ->where('status IN (0, 1)');

    try
    {
        $result = (int) $db->setQuery($query)->loadResult();
    }
    catch (JDatabaseException $exception)
    {
        // Can't check for existing requests, so don't create a new one
        $this->setError(JText::_('COM_PRIVACY_ERROR_CHECKING_FOR_EXISTING_REQUESTS'));

        return false;
    }

    if ($result > 0)
    {
        $this->setError(JText::_('COM_PRIVACY_ERROR_PENDING_REQUEST_OPEN'));

        return false;
    }

    // Everything is good to go, create the request
    $token       = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
    $hashedToken = JUserHelper::hashPassword($token);

    $data['confirm_token']            = $hashedToken;
    $data['confirm_token_created_at'] = JFactory::getDate()->setTimezone($timezone);

    if (!$this->save($data))
    {
        // The save function will set the error message, so just return here
        return false;
    }

    // Push a notification to the site's super users, deliberately ignoring if this process fails so the below message goes out
    JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/models', 'MessagesModel');
    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/tables');

    /** @var MessagesModelMessage $messageModel */
    $messageModel = JModelLegacy::getInstance('Message', 'MessagesModel');

    $messageModel->notifySuperUsers(
        JText::_('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CREATED_REQUEST_SUBJECT'),
        JText::sprintf('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CREATED_REQUEST_MESSAGE', $data['email'])
    );

    // The mailer can be set to either throw Exceptions or return boolean false, account for both
    try
    {
        $app = JFactory::getApplication();

        $linkMode = $app->get('force_ssl', 0) == 2 ? 1 : -1;

        $substitutions = array(
            '[SITENAME]' => $app->get('sitename'),
            '[URL]'      => JUri::root(),
            '[TOKENURL]' => JRoute::link('site', 'index.php?option=com_privacy&view=confirm&confirm_token=' . $token, false, $linkMode),
            '[FORMURL]'  => JRoute::link('site', 'index.php?option=com_privacy&view=confirm', false, $linkMode),
            '[TOKEN]'    => $token,
            '\\n'        => "\n",
        );

        switch ($data['request_type'])
        {
            case 'export':
                $emailSubject = JText::_('COM_PRIVACY_EMAIL_REQUEST_SUBJECT_EXPORT_REQUEST');
                $emailBody    = JText::_('COM_PRIVACY_EMAIL_REQUEST_BODY_EXPORT_REQUEST');

                break;

            case 'remove':
                $emailSubject = JText::_('COM_PRIVACY_EMAIL_REQUEST_SUBJECT_REMOVE_REQUEST');
                $emailBody    = JText::_('COM_PRIVACY_EMAIL_REQUEST_BODY_REMOVE_REQUEST');

                break;

            default:
                $this->setError(JText::_('COM_PRIVACY_ERROR_UNKNOWN_REQUEST_TYPE'));

                return false;
        }

        foreach ($substitutions as $k => $v)
        {
            $emailSubject = str_replace($k, $v, $emailSubject);
            $emailBody    = str_replace($k, $v, $emailBody);
        }

        $mailer = JFactory::getMailer();
        $mailer->setSubject($emailSubject);
        $mailer->setBody($emailBody);
        $mailer->addRecipient($data['email']);

        $mailResult = $mailer->Send();

        if ($mailResult instanceof JException)
        {
            // JError was already called so we just need to return now
            return false;
        }
        elseif ($mailResult === false)
        {
            $this->setError($mailer->ErrorInfo);

            return false;
        }

        /** @var PrivacyTableRequest $table */
        $table = $this->getTable();

        if (!$table->load($this->getState($this->getName() . '.id')))
        {
            $this->setError($table->getError());

            return false;
        }

        // Log the request's creation
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

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

        // The email sent and the record is saved, everything is good to go from here
        return true;
    }
    catch (phpmailerException $exception)
    {
        $this->setError($exception->getMessage());

        return false;
    }
}