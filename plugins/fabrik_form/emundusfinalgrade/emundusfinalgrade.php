<?php
/**
 * @version 2: emunduscampaign 2019-04-11 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création de dossier de candidature automatique.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.emunduscampaigncheck
 * @since       3.0
 */

class PlgFabrik_FormEmundusFinalGrade extends plgFabrik_Form {

    /**
     * Get an element name
     *
     * @param   string  $pname  Params property name to look up
     * @param   bool    $short  Short (true) or full (false) element name, default false/full
     *
     * @return	string	element full name
     */
    public function getFieldName($pname, $short = false) {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return '';
        }

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   mixed   $default  Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return Bool
     * @throws Exception
     */
    public function onBeforeCalculations() {

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.emundus-final-grade.php'), JLog::ALL, array('com_emundus.emundus-final-grade'));

        $db = JFactory::getDBO();
        $formModel = $this->getModel();

        $fnum = $formModel->formData['fnum_raw'];
        $status = is_array($formModel->formData['final_grade_raw']) ? $formModel->formData['final_grade_raw'][0] : $formModel->formData['final_grade_raw'];

        if (isset($status)) {


            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__emundus_campaign_candidature'))
                ->set($db->quoteName('status').' = '.$status)
                ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));

            try {

                $db->setQuery($query);
                $db->execute();

                if (!empty($this->getParam('email-trigger', '0'))) {

                    include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
                    include_once(JPATH_BASE.'/components/com_emundus/models/files.php');
                    $m_email = new EmundusModelEmails;
                    $m_files = new EmundusModelFiles;

                    $email_from_sys = JFactory::getApplication()->get('mailfrom');

                    $fnumsInfos = $m_files->getFnumsInfos([$fnum]);
                    $code = $fnumsInfos[$fnum]['training'];

                    $trigger_emails = $m_email->getEmailTrigger($status, [$code], 1);
                    $toAttach = [];


                    if (count($trigger_emails) > 0) {

                        foreach ($trigger_emails as $trigger_email) {

                            // Manage with default recipient by programme
                            foreach ($trigger_email as $code => $trigger) {
                                if ($trigger['to']['to_applicant'] == 1) {

                                    // Manage with selected fnum
                                    foreach ($fnumsInfos as $file) {
                                        if ($file['training'] != $code) {
                                            continue;
                                        }

                                        $mailer = JFactory::getMailer();

                                        $post = array('FNUM' => $file['fnum'],'CAMPAIGN_LABEL' => $file['label'], 'CAMPAIGN_END' => JHTML::_('date', $file['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null));
                                        $tags = $m_email->setTags($file['applicant_id'], $post, $file['fnum'], '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

                                        $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                                        $from_id    = 62;
                                        $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                                        $to         = $file['email'];
                                        $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                                        $body = $trigger['tmpl']['message'];


                                        // Add the email template model.
                                        if (!empty($trigger['tmpl']['template'])) {
                                            $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger['tmpl']['template']);
                                        }

                                        $body = preg_replace($tags['patterns'], $tags['replacements'], $body);
                                        $body = $m_email->setTagsFabrik($body, array($file['fnum']));

                                        // If the email sender has the same domain as the system sender address.
                                        if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                                            $mail_from_address = $from;
                                        }
                                        else {
                                            $mail_from_address = $email_from_sys;
                                        }

                                        // Set sender
                                        $sender = [
                                            $mail_from_address,
                                            $fromname
                                        ];

                                        $mailer->setSender($sender);
                                        $mailer->addReplyTo($from, $fromname);
                                        $mailer->addRecipient($to);
                                        $mailer->setSubject($subject);
                                        $mailer->isHTML(true);
                                        $mailer->Encoding = 'base64';
                                        $mailer->setBody($body);
                                        $mailer->addAttachment($toAttach);

                                        $send = $mailer->Send();
                                        if ($send !== true) {
                                            JLog::add($send->__toString(), JLog::ERROR, 'emundus-final-grade');
                                        } else {
                                            $message = array(
                                                'user_id_from' => $from_id,
                                                'user_id_to' => $file['applicant_id'],
                                                'subject' => $subject,
                                                'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                                            );
                                            $m_email->logEmail($message);
                                            JLog::add($to.' '.$body, JLog::INFO, 'emundus-final-grade');
                                        }
                                    }
                                }

                                foreach ($trigger['to']['recipients'] as $key => $recipient) {
                                    $mailer = JFactory::getMailer();

                                    $post = array();
                                    $tags = $m_email->setTags($recipient['id'], $post, $fnum, '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

                                    $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                                    $from_id    = 62;
                                    $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                                    $to         = $recipient['email'];
                                    $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                                    $body       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
                                    $body       = $m_email->setTagsFabrik($body, [$fnum]);

                                    // If the email sender has the same domain as the system sender address.
                                    if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                                        $mail_from_address = $from;
                                    }
                                    else {
                                        $mail_from_address = $email_from_sys;
                                    }

                                    // Set sender
                                    $sender = [
                                        $mail_from_address,
                                        $fromname
                                    ];

                                    $mailer->setSender($sender);
                                    $mailer->addReplyTo($from, $fromname);
                                    $mailer->addRecipient($to);
                                    $mailer->setSubject($subject);
                                    $mailer->isHTML(true);
                                    $mailer->Encoding = 'base64';
                                    $mailer->setBody($body);
                                    $mailer->addAttachment($toAttach);

                                    $send = $mailer->Send();
                                    if ($send !== true) {
                                        JLog::add($send->__toString(), JLog::ERROR, 'emundus-final-grade');
                                    } else {
                                        $message = array(
                                            'user_id_from' => $from_id,
                                            'user_id_to' => $recipient['id'],
                                            'subject' => $subject,
                                            'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                                        );
                                        $m_email->logEmail($message);
                                        JLog::add($to.' '.$body, JLog::INFO, 'emundus-final-grade');
                                    }
                                }
                            }
                        }
                    }
                }
                return true;

            } catch(Exception $e) {
                JLog::add('Unable to set status in plugin/emundusFinalGrade at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.emundus-final-grade');
                return false;
            }
        }
    }
}
