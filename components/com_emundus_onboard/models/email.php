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

class EmundusonboardModelemail extends JModelList {

     function getEmailCount($filter, $recherche) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if ($filter == 'Publish') {
            $filterCount = $db->quoteName('se.published') . ' = 1';
        } else if ($filter == 'Unpublish') {
            $filterCount = $db->quoteName('se.published') . ' = 0';
        } else {
            $filterCount = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheSubject = $db->quoteName('se.subject') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheMessage = $db->quoteName('se.message') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheEmail = $db->quoteName('se.emailfrom') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheType = $db->quoteName('se.type') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheSubject.' OR '.$rechercheMessage.' OR '.$rechercheEmail.' OR '.$rechercheType;
        }

        $query->select('COUNT(se.id)')
            ->from($db->quoteName('#__emundus_setup_emails', 'se'))
            ->where($filterCount)
            ->where($fullRecherche);

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Error when try to get number of emails : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

     function getAllEmails($lim, $page, $filter, $sort, $recherche) {
         $db = $this->getDbo();
         $query = $db->getQuery(true);

        if (empty($lim)) {
            $limit = 25;
        } else {
            $limit = $lim;
        }

        if (empty($page)) {
            $offset = 0;
        } else {
            $offset = ($page-1) * $limit;
        }

        if (empty($sort)) {
            $sort = 'DESC';
        }
        $sortDb = 'se.id ';

        if ($filter == 'Publish') {
            $filterDate = $db->quoteName('se.published') . ' = 1';
        } else if ($filter == 'Unpublish') {
            $filterDate = $db->quoteName('se.published') . ' = 0';
        } else {
            $filterDate = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheSubject = $db->quoteName('se.subject') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheMessage = $db->quoteName('se.message') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheEmail = $db->quoteName('se.emailfrom') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheType = $db->quoteName('se.type') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheSubject.' OR '.$rechercheMessage.' OR '.$rechercheEmail.' OR '.$rechercheType;
        }

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_emails', 'se'))
            ->where($filterDate)
            ->where($fullRecherche)

            ->group($sortDb)
            ->order($sortDb.$sort);

        try {
            $db->setQuery($query, $offset, $limit);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Error when try to get emails : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

     public function deleteEmail($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            try {
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->delete($db->quoteName('#__emundus_setup_emails'))
                    ->where($se_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/email | Cannot delete emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    public function unpublishEmail($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 0'
                );
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($db->quoteName('#__emundus_setup_emails'))
                    ->set($fields)
                    ->where($se_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/email | Cannot unpublish emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    public function publishEmail($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
              }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 1'
                );
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($db->quoteName('#__emundus_setup_emails'))
                    ->set($fields)
                    ->where($se_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/email | Cannot publish emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function duplicateEmail($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $columns = array_keys($db->getTableColumns('#__emundus_setup_emails'));

                $columns = array_filter($columns, function($k) {
                    return ($k != 'id' && $k != 'date_time');
                });

                foreach ($data as $id){
                    $query->clear()
                        ->select(implode(',', $db->qn($columns)))
                        ->from($db->quoteName('#__emundus_setup_emails'))
                        ->where($db->quoteName('id') . ' = ' . $id);

                    $db->setQuery($query);
                    $values[] = implode(', ',$db->quote($db->loadRow()));
                }


                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_emails'))
                    ->columns(
                        implode(',', $db->quoteName($columns))
                    )
                    ->values($values);

                $db->setQuery($query);
                return $db->execute();

            } catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/email | Cannot duplicate emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

     public function getEmailById($id) {
         $db = JFactory::getDbo();
         $query = $db->getQuery(true);

        if (empty($id)) {
	        return false;
        }

        $query->select('*')
            ->from ($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('id') . ' = '.$id);

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            $email_Info = $db->loadObject();           /// get email info

            /// count records of #emundus_setup_emails_repeat_receivers
            $query->clear()->select('COUNT(*)')->from($db->quoteName('#__emundus_setup_emails_repeat_receivers'))->where($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' = ' . (int)$id);

            $db->setQuery($query);
            $receiver_count = $db->loadResult();
            $receiver_Info = array();

            if($receiver_count > 0) {
                $query->clear()->select('#__emundus_setup_emails_repeat_receivers.*')->from($db->quoteName('#__emundus_setup_emails_repeat_receivers'))->where($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' = ' . (int)$id);

                $db->setQuery($query);
                $receiver_Info = $db->loadObjectList();         /// get receivers info (empty or not)
            }

            /// get associated email template (jos_emundus_email_template)
            $query->clear()
                ->select('#__emundus_email_templates.*')
                ->from($db->quoteName('#__emundus_email_templates'))
                ->leftJoin($db->quoteName('#__emundus_setup_emails').' ON '.$db->quoteName('#__emundus_email_templates.id').' = '.$db->quoteName('#__emundus_setup_emails.email_tmpl'))
                ->where($db->quoteName('#__emundus_setup_emails.id') . ' = ' . (int)$id);

            $db->setQuery($query);
            $template_Info = $db->loadObjectList();

            /// get associated letters
            $query->clear()
                ->select('#__emundus_setup_attachments.*')
                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->leftJoin($db->quoteName('#__emundus_setup_letters') . ' ON ' . $db->quoteName('#__emundus_setup_letters.attachment_id') . ' = ' . $db->quoteName('#__emundus_setup_attachments.id'))
                ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment') . ' ON ' . $db->quoteName('#__emundus_setup_letters.id') . ' = ' . $db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment'))
                ->where($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' = ' . (int)$id);

            $db->setQuery($query);
            $letter_Info = $db->loadObjectList();         /// get attachment info

            /// get associated candidate attachments
            $query->clear()
                ->select('#__emundus_setup_attachments.*')
                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment') . ' ON ' . $db->quoteName('#__emundus_setup_attachments.id') . ' = ' . $db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.candidate_attachment'))
                ->where($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . ' = ' . (int)$id);

            $db->setQuery($query);
            $attachments_Info = $db->loadObjectList();         /// get attachment info

            /// get associated tags
            $query->clear()
                ->select('#__emundus_setup_action_tag.*')
                ->from($db->quoteName('#__emundus_setup_action_tag'))
                ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_tags') . ' ON ' . $db->quoteName('#__emundus_setup_action_tag.id') . ' = ' . $db->quoteName('#__emundus_setup_emails_repeat_tags.tags'))
                ->where($db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . ' = ' . (int)$id);

            $db->setQuery($query);
            $tags_Info = $db->loadObjectList();         /// get attachment info

            return array('email' => $email_Info, 'receivers' => $receiver_Info, 'template' => $template_Info, 'letter_attachment' => $letter_Info, 'candidate_attachment' => $attachments_Info, 'tags' => $tags_Info);
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Cannot get the email by id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /// create email params :: email info, cc (email / fabrik elem), bcc (email / fabrik elem), letters
    public function createEmail($data, $receiver_cc=null, $receiver_bcc = null, $letters=null, $documents=null, $tags=null) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

//        $data['category'] = null;

        // set regular expression for fabrik elem
        $fabrik_pattern = '/\${(.+[0-9])\}/';

        if (!empty($data)) {
        	$query->insert($db->quoteName('#__emundus_setup_emails'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                $newemail = $db->insertid();
                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_emails'))
                    ->set($db->quoteName('lbl') . ' = ' . $db->quote('custom_'.date('YmdhHis')))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($newemail));
                $db->setQuery($query);
                $db->execute();

                // add cc for new email
                if(!empty($receiver_cc)) {
                    foreach ($receiver_cc as $key => $receiver) {
                        $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                        if($is_fabrik_tag == true) {
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_cc_fabrik'));

                            $db->setQuery($query);
                            $db->execute();
                        } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false){
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_cc_email'));

                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }

                // add bcc for new email
                if(!empty($receiver_bcc)) {
                    foreach ($receiver_bcc as $key => $receiver) {
                        $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                        if($is_fabrik_tag == true) {
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_bcc_fabrik'));

                            $db->setQuery($query);
                            $db->execute();
                        } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false) {
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_bcc_email'));

                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }

                // add letter attachment to table #jos_emundus_setup_emails_repeat_letter_attachment
                if(!empty($letters)) {
                    foreach ($letters as $key => $letter) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' =  ' . (int)$newemail)
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment') . ' = ' . (int)$letter);

                        $db->setQuery($query);
                        $db->execute();
                    }
                }

                // add candidate attachment to table #jos_emundus_setup_emails_repeat_candidate_attachment
                if(!empty($documents)) {
                    foreach ($documents as $key => $document) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . ' =  ' . (int)$newemail)
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.candidate_attachment') . ' = ' . (int)$document);

                        $db->setQuery($query);
                        $db->execute();
                    }
                }

                // add tag to table #jos_emundus_setup_emails_repeat_tags
                if(!empty($tags)) {
                    foreach ($tags as $key => $tag) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_repeat_tags'))
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . ' =  ' . (int)$newemail)
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_tags.tags') . ' = ' . (int)$tag);

                        $db->setQuery($query);
                        $db->execute();
                    }
                }

                return true;

            } catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/email | Cannot create an email: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    /// update email params :: email info, cc (email / fabrik elem), bcc (email / fabrik elem), letters
    public function updateEmail($id, $data, $receiver_cc=null, $receiver_bcc=null, $letters=null, $documents=null, $tags=null) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // set regular expression for fabrik elem
        $fabrik_pattern = '/\${(.+[0-9])\}/';

        if (count($data) > 0) {

            $fields = [];

            foreach ($data as $key => $val) {
                $insert = $db->quoteName($key) . ' = ' . $db->quote($val);
                $fields[] = $insert;
            }

            $query->update($db->quoteName('#__emundus_setup_emails'))->set($fields)->where($db->quoteName('id') . ' = '.$db->quote($id));

            try {
                $db->setQuery($query);
                $db->execute();

                require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
                $m_eval = new EmundusModelEvaluation;

                /// remove and update new documents for an email
                if(!empty($letters)) {
                    $query->clear()->delete($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))->where($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . '=' . (int)$id);

                    $db->setQuery($query);
                    $db->execute();

                    foreach($letters as $key => $letter) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' =  ' . (int)$id)
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment') . ' = ' . (int)$letter);

                        $db->setQuery($query);
                        $db->execute();
                    }
                } else {
                    /// if empty --> remove all letter attachments
                    $query->clear()->delete($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))->where($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . '=' . (int)$id);

                    $db->setQuery($query);
                    $db->execute();
                }

                if(!empty($receiver_cc)) {
                    /// update receivers cc/bcc --> first :: delete old cc
                    $query->clear()
                        ->delete($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $db->quote('receiver_cc_%'));

                    $db->setQuery($query);
                    $db->execute();

                    foreach ($receiver_cc as $key => $receiver) {
                        /// if fabrik tags --> then, receiver type = 'receiver_cc_fabrik', otherwise, 'receivers_cc_email'
                        $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                        if($is_fabrik_tag == true) {
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_cc_fabrik'));
                            $db->setQuery($query);
                            $db->execute();
                        } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false){
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_cc_email'));
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                } else {
                    /// if empty --> remove all receivers cc
                    $query->clear()
                        ->delete($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $db->quote('receiver_cc_%'));

                    $db->setQuery($query);
                    $db->execute();
                }

                /// update bcc
                if(!empty($receiver_bcc)) {
                    $query->clear()
                        ->delete($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $db->quote('receiver_bcc_%'));

                    $db->setQuery($query);
                    $db->execute();

                    foreach ($receiver_bcc as $key => $receiver) {
                        /// if fabrik tags --> then, receiver type = 'receiver_cc_fabrik', otherwise, 'receivers_cc_email'
                        $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                        if($is_fabrik_tag) {
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_bcc_fabrik'));
                            $db->setQuery($query);
                            $db->execute();
                        } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false) {
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $db->quote($receiver))
                                ->set($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $db->quote('receiver_bcc_email'));
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                } else {
                    /// if empty --> remove all bcc receivers
                    $query->clear()
                        ->delete($db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $db->quote('receiver_bcc_%'));

                    $db->setQuery($query);
                    $db->execute();
                }

                // update candidate attachments #jos_emundus_setup_emails_repeat_candidate_attachment
                if(!empty($documents)) {
                    $query->clear()->delete($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))->where($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . '=' . (int)$id);

                    $db->setQuery($query);
                    $db->execute();

                    foreach($documents as $key => $document) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . ' =  ' . (int)$id)
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.candidate_attachment') . ' = ' . (int)$document);

                        $db->setQuery($query);
                        $db->execute();
                    }
                } else {
                    /// if empty --> remove all candidate attachments
                    $query->clear()->delete($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))->where($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . '=' . (int)$id);

                    $db->setQuery($query);
                    $db->execute();
                }

                // update tags #jos_emundus_setup_emails_repeat_tags
                if(!empty($tags)) {
                    $query->clear()->delete($db->quoteName('#__emundus_setup_emails_repeat_tags'))->where($db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . '=' . (int)$id);

                    $db->setQuery($query);
                    $db->execute();

                    foreach($tags as $key => $tag) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_repeat_tags'))
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . ' =  ' . (int)$id)
                            ->set($db->quoteName('#__emundus_setup_emails_repeat_tags.tags') . ' = ' . (int)$tag);

                        $db->setQuery($query);
                        $db->execute();
                    }
                } else {
                    /// if empty --> remove all tags
                    $query->clear()->delete($db->quoteName('#__emundus_setup_emails_repeat_tags'))->where($db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . '=' . (int)$id);

                    $db->setQuery($query);
                    $db->execute();
                }


                return true;
            } catch(Exception $e) {
                JLog::add('component/com_emundus_onboard/models/email | Cannot update the email ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }

        } else {
            return false;
        }
    }

     public function getEmailTypes() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(type)')
            ->from ($db->quoteName('#__emundus_setup_emails'))
            ->order('id DESC');

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Cannot get emails types : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

     public function getEmailCategories() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(category)')
            ->from ($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('category') . ' <> ""')
            ->order('id DESC');

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Cannot get emails categories : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getStatus() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_status'))
            ->order('step ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Cannot get status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getTriggersByProgramId($pid) {
        $lang = JFactory::getLanguage();
        $lid = 2;
        if ($lang->getTag() != 'fr-FR'){
            $lid = 1;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(et.id) AS trigger_id','se.subject AS subject','ss.step AS status','ep.profile_id AS profile','et.to_current_user AS candidate','et.to_applicant AS manual'])
            ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id', 'etrp'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_trigger', 'et')
                . ' ON ' .
                $db->quoteName('etrp.parent_id') . ' = ' . $db->quoteName('et.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails', 'se')
                . ' ON ' .
                $db->quoteName('et.email_id') . ' = ' . $db->quoteName('se.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_status', 'ss')
                . ' ON ' .
                $db->quoteName('et.step') . ' = ' . $db->quoteName('ss.step'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id', 'ep')
                . ' ON ' .
                $db->quoteName('et.id') . ' = ' . $db->quoteName('ep.parent_id'))
            ->where($db->quoteName('etrp.programme_id') . ' = ' . $db->quote($pid));

        try {
            $db->setQuery($query);
            $triggers = $db->loadObjectList();

            foreach ($triggers as $trigger) {
                $query->clear()
                    ->select('value')
                    ->from($db->quoteName('#__falang_content'))
                    ->where($db->quoteName('reference_id') . ' = ' . $db->quote($trigger->status))
                    ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote('emundus_setup_status'))
                    ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote('value'))
                    ->andWhere($db->quoteName('language_id') . ' = ' . $db->quote($lid));
                $db->setQuery($query);
                $trigger->status = $db->loadResult();

                $query->clear()
                    ->select(['us.firstname','us.lastname'])
                    ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','tu'))
                    ->leftJoin($db->quoteName('#__emundus_users', 'us')
                        . ' ON ' .
                        $db->quoteName('tu.user_id') . ' = ' . $db->quoteName('us.user_id'))
                    ->where($db->quoteName('tu.parent_id') . ' = ' . $db->quote($trigger->trigger_id));
                $db->setQuery($query);
                $trigger->users = array_values($db->loadObjectList());
            }

            return $triggers;
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Error at getting triggers by program id ' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getTriggerById($tid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['DISTINCT(et.id) AS trigger_id','et.step AS status','et.email_id AS model','ep.profile_id AS target'])
            ->from($db->quoteName('#__emundus_setup_emails_trigger', 'et'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id', 'ep')
                . ' ON ' .
                $db->quoteName('et.id') . ' = ' . $db->quoteName('ep.parent_id'))
            ->where($db->quoteName('et.id') . ' = ' . $db->quote($tid));

        try {
            $db->setQuery($query);
            $trigger = $db->loadObject();

            $query->clear()
                ->select('us.user_id')
                ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','tu'))
                ->leftJoin($db->quoteName('#__emundus_users', 'us')
                    . ' ON ' .
                    $db->quoteName('tu.user_id') . ' = ' . $db->quoteName('us.user_id'))
                ->where($db->quoteName('tu.parent_id') . ' = ' . $db->quote($trigger->trigger_id));
            $db->setQuery($query);
            $trigger->users = array_values($db->loadObjectList());

            return $trigger;
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Error at getting trigger ' . $tid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function createTrigger($trigger, $users, $user) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $to_current_user = 0;
        $to_applicant = 0;

        if ($trigger['action_status'] == 'to_current_user') {
            $to_current_user = 1;
        } elseif ($trigger['action_status'] == 'to_applicant') {
            $to_applicant = 1;
        }

        try {
            $query->insert($db->quoteName('#__emundus_setup_emails_trigger'))
                ->set($db->quoteName('user') . ' = ' . $db->quote($user->id))
                ->set($db->quoteName('step') . ' = ' . $db->quote($trigger['status']))
                ->set($db->quoteName('email_id') . ' = ' . $db->quote($trigger['model']))
                ->set($db->quoteName('to_current_user') . ' = ' . $db->quote($to_current_user))
                ->set($db->quoteName('to_applicant') . ' = ' . $db->quote($to_applicant));

            $db->setQuery($query);
            $db->execute();

            $trigger_id = $db->insertid();

            if ($trigger['target'] == 5 || $trigger['target'] == 6) {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id'))
                    ->set($db->quoteName('parent_id') . ' = ' . $db->quote($trigger_id))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote($trigger['target']));
                $db->setQuery($query);
                $db->execute();
            } elseif ($trigger['target'] == 0) {
                foreach (array_keys($users) as $uid) {
                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->set($db->quoteName('parent_id') . ' = ' . $db->quote($trigger_id))
                        ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid));

                    $db->setQuery($query);
                    $db->execute();
                }
            }

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id'))
                ->set($db->quoteName('parent_id') . ' = ' . $db->quote($trigger_id))
                ->set($db->quoteName('programme_id') . ' = ' . $db->quote($trigger['program']));

            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Cannot create a trigger : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateTrigger($tid,$trigger,$users) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $to_current_user = 0;
        $to_applicant = 0;

        if ($trigger['action_status'] == 'to_current_user') {
            $to_current_user = 1;
        } elseif ($trigger['action_status'] == 'to_applicant') {
            $to_applicant = 1;
        }

        $query->update($db->quoteName('#__emundus_setup_emails_trigger'))
            ->set($db->quoteName('step') . ' = ' . $db->quote($trigger['status']))
            ->set($db->quoteName('email_id') . ' = ' . $db->quote($trigger['model']))
            ->set($db->quoteName('to_current_user') . ' = ' . $db->quote($to_current_user))
            ->set($db->quoteName('to_applicant') . ' = ' . $db->quote($to_applicant))
            ->where($db->quoteName('id') . ' = ' . $tid);

        try {
            $db->setQuery($query);
            $db->execute();

            if ($trigger['target'] == 5 || $trigger['target'] == 6) {
                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id'))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote($trigger['target']))
                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($tid));

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch(Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    return false;
                }

            } elseif ($trigger['target'] == 0) {
                foreach (array_keys($users) as $uid) {
                    $query->clear()
                        ->select('COUNT(*)')
                        ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->where($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                        ->andWhere($db->quoteName('parent_id') . ' = ' . $db->quote($tid));
                    $db->setQuery($query);
                    $row = $db->loadResult();

                    if ($row < 1) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                            ->set($db->quoteName('parent_id') . ' = ' . $db->quote($tid))
                            ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid));
                        try {
                            $db->setQuery($query);
                            $db->execute();
                        } catch(Exception $e) {
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                            return false;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Cannot update the trigger ' . $tid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function removeTrigger($tid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_setup_emails_trigger'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($tid));

        try {
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus_onboard/models/email | Error at remove the trigger ' . $tid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    // get receivers from fabrik tags
    public function getEmailsFromFabrikIds($ids) {
        $db = JFactory::getDbo();

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_onboard'.DS.'models'.DS.'files.php');

        $m_files_onboard = new EmundusModelFiles;

        $output = [];

        $fabrik_results = $m_files_onboard->getValueFabrikByIds($ids);

        foreach($fabrik_results as $key => $fabrik) {
            $query = 'SELECT ' . $fabrik['db_table_name'] . '.' . $fabrik['name'] . ' FROM ' . $fabrik['db_table_name'] . ' WHERE ' . $fabrik['db_table_name'] . '.' . $fabrik['name'] . ' IS NOT NULL';
            $db->setQuery($query);
            $output[] = $db->loadObjectList();
        }

        $array_reduce = (array) array_reduce($output, 'array_merge', array());

        $result = [];
        foreach($array_reduce as $key => $value) { foreach((array)$value as $index => $data) { $result[] = $data; } }

        return array_unique($result);       // return array unique
    }
}
