<?php
defined('_JEXEC') or die();
/**
 * @version 1: final_grade.php 89 2015-06-15 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Validation finale du dossier de candidature
 */

$db = JFactory::getDBO();
$jinput	= JFactory::getApplication()->input->post;

$fnum = $jinput->get('jos_emundus_final_grade___fnum');
$status = $jinput->get('jos_emundus_final_grade___final_grade')[0];
$motif = $jinput->get('jos_emundus_final_grade___motif_refus')[0];

include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
include_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

$email_from_sys = JFactory::getApplication()->getCfg('mailfrom');

if (!empty($status)) {

    if($status == 7){
        switch ($motif) {
            case '1':
                $status = 21;
                break;
            case '2':
                $status = 22;
                break;
            case '3':
                $status = 23;
                break;
            case '4':
                $status = 24;
                break;
            case '5':
                $status = 25;
                break;
            case '6':
                $status = 26;
                break;
            case '7':
                $status = 27;
                break;
            case '8':
                $status = 28;
                break;
            default:
                $status = 7;
        }
    }

    jimport('joomla.log.log');
    JLog::addLogger(['text_file' => 'com_emundus.finalGrade.php'], JLog::ALL, ['com_emundus']);

    $m_files = new EmundusModelFiles();
    $fnumsInfos = $m_files->getFnumInfos($fnum);

    $code = array();
    $code[] = $fnumsInfos['training'];

    $mFile = new EmundusModelFiles();

    $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

    # get old status label
    $fnumOldStatus = $mFile->getStatusByFnums([$fnum])[$fnum]['value'];        // status id //

    # get new status label (write SQL query)
    $fnumNewStatus = $mFile->getStatusByStep($status)[0]['value'];

    # track the logs
    $logsStd = new stdClass();
    $logsStd->old = $fnumOldStatus;
    $logsStd->new = $fnumNewStatus;

    $logsParams = array('updated' => [$logsStd]);

    $query = $db->getQuery(true);
    $query->update($db->quoteName('#__emundus_campaign_candidature'))
        ->set($db->quoteName('status').' = '.$status)
        ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));

    try {

        $db->setQuery($query);
        $db->execute();

        $m_emails = new EmundusModelEmails;
        $to_applicant = '0,1';
        $trigger_emails = $m_emails->getEmailTrigger($status, $code, '0,1');

        $toAttach = [];

        if (count($trigger_emails) > 0) {

            foreach ($trigger_emails as $trigger_email) {

                // Manage with default recipient by programme
                foreach ($trigger_email as $code => $trigger) {
                    if ($trigger['to']['to_applicant'] == 1) {

                        // Manage with selected fnum
                        if ($fnumsInfos['training'] != $code) {
                            continue;
                        }

                        $mailer = JFactory::getMailer();

                        $post = array('FNUM' => $fnumsInfos['fnum'],'CAMPAIGN_LABEL' => $fnumsInfos['label'], 'CAMPAIGN_END' => $fnumsInfos['end_date']);
                        $tags = $m_emails->setTags($fnumsInfos['applicant_id'], $post, $fnumsInfos['fnum'], '', $trigger['tmpl']['emailfrom'].$trigger['tmpl']['name'].$trigger['tmpl']['subject'].$trigger['tmpl']['message']);

                        $from       = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                        $from_id    = 62;
                        $fromname   = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                        $to         = $fnumsInfos['email'];
                        $subject    = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                        $body = $trigger['tmpl']['message'];


                        // Add the email template model.
                        if (!empty($trigger['tmpl']['template']))
                            $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger['tmpl']['template']);

                        $body = preg_replace($tags['patterns'], $tags['replacements'], $body);
                        $body = $m_emails->setTagsFabrik($body, array($fnumsInfos['fnum']));

                        // If the email sender has the same domain as the system sender address.
                        if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1))
                            $mail_from_address = $from;
                        else
                            $mail_from_address = $email_from_sys;

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
                            $msg .= '<div class="alert alert-dismissable alert-danger">'.JText::_('EMAIL_NOT_SENT').' : '.$to.' '.$send->__toString().'</div>';
                            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
                        } else {
                            $message = array(
                                'user_id_from' => $from_id,
                                'user_id_to' => $fnumsInfos['applicant_id'],
                                'subject' => $subject,
                                'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                            );
                            $m_emails->logEmail($message);
                            $msg .= JText::_('EMAIL_SENT').' : '.$to.'<br>';
                            JLog::add($to.' '.$body, JLog::INFO, 'com_emundus.email');
                        }
                    }
                }
            }
        }
        EmundusModelLogs::log(JFactory::getUser()->id, $applicant_id, $fnum, 13, 'u', 'COM_EMUNDUS_ACCESS_STATUS_UPDATE',json_encode($logsParams,JSON_UNESCAPED_UNICODE));

    } catch(Exception $e) {
        JLog::add('Unable to set status in plugin/emundusFinalGrade at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
    }
}
