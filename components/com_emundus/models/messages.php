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

class EmundusModelMessages extends JModelList {
    var $user = null;

	/**
	 * Constructor
	 *
	 * @since 3.8.6
	 *
	 * @param array $config
	 */
    public function __construct($config = array()) {
        $this->user = JFactory::getSession()->get('emundusUser');

        JLog::addLogger(['text_file' => 'com_emundus.chatroom.error.php'], JLog::ERROR, 'com_emundus.chatroom');

		parent::__construct($config);
	}

    /**
     * Gets all published message templates of a certain type.
     *
     * @param Int $type The type of email to get, type 2 is by default (Templates).
     * @return Mixed False if the query fails and nothing can be loaded. An array of objects describing the messages. (sender, subject, body, etc..)
     */
    function getAllMessages($type = 2) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('type').' IN ('.$db->Quote($type).')')
            ->andWhere($db->quoteName('published') . ' = ' . $db->quote(1))
            ->order($db->quoteName('subject'));

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting emails in model/messages at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

	}


    /**
     * Gets all published message categories of a certain type.
     *
     * @param Int $type The type of category to get, type 2 is by default (Templates).
     * @return Mixed False if the query fails and nothing can be loaded. An array of the categories.
     */
	function getAllCategories($type = 2) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select('DISTINCT(category)')
            ->from($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('type').' IN ('.$db->Quote($type).')')
            ->andWhere($db->quoteName('published') . ' = ' . $db->quote(1))
            ->order($db->quoteName('category'));

        try {

            $db->setQuery($query);
            return $db->loadColumn();

        } catch (Exception $e) {
            JLog::add('Error getting email categories in model/messages at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /**
     * Gets all published attachments unless a filter is active.
     *
     * @return Boolean|array False if the query fails and nothing can be loaded. or An array of objects describing attachments.
     */
    function getAttachments() {

        $db = JFactory::getDbo();
        $session = JFactory::getSession();

        $filt_params = $session->get('filt_params');

        $query = $db->getQuery(true);

        // Get all info about the attachments in the table.
        $query->select('a.*')
                ->from($db->quoteName('#__emundus_setup_attachments', 'a'));

        $where = '1 = 1 ';

        // if a filter is added then we need to filter out the attachemnts that dont match.
        if (isset($filt_params['campaign'][0]) && $filt_params['campaign'][0] != '%') {

            // Joins are added in the ifs, even though some are redundant it's better than doing tons of joins when not needed.
            $query->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles', 'ap').' ON '.$db->QuoteName('ap.attachment_id').' = '.$db->QuoteName('a.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'p').' ON '.$db->QuoteName('ap.profile_id').' = '.$db->QuoteName('p.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->QuoteName('c.profile_id').' = '.$db->QuoteName('p.id'));

            $where .= ' AND '.$db->quoteName('c.id').' LIKE '.$filt_params['campaign'][0];

        } else if (isset($filt_params['programme'][0]) && $filt_params['programme'][0] != '%') {

            $query->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles', 'ap').' ON '.$db->QuoteName('ap.attachment_id').' = '.$db->QuoteName('a.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'p').' ON '.$db->QuoteName('ap.profile_id').' = '.$db->QuoteName('p.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->QuoteName('c.profile_id').' = '.$db->QuoteName('p.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'pr').' ON '.$db->QuoteName('c.training').' = '.$db->QuoteName('pr.code'));

            $where .= ' AND '.$db->quoteName('pr.code').' LIKE '.$db->Quote($filt_params['programme'][0]);

        }

        $query->where($where.' AND '.$db->quoteName('a.published').'=1');

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting attachments in model/messages at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /**
     * Gets all published letters unless a filter is active.
     *
     * @return Boolean False if the query fails and nothing can be loaded.
     * @return Array An array of objects describing letters.
     */
    function getLetters() {

        $db = JFactory::getDbo();
        $session = JFactory::getSession();

        $filt_params = $session->get('filt_params');

        $query = $db->getQuery(true);

        // Get all info about the letters in the table.
        $query->select('l.*')
                ->from($db->quoteName('#__emundus_setup_letters', 'l'));

        // if a filter is added then we need to filter out the letters that dont match.
        if (isset($filt_params['campaign'][0]) && $filt_params['campaign'][0] != '%') {

            $query->leftJoin($db->quoteName('#__emundus_setup_letters_repeat_training','lrt').' ON '.$db->quoteName('lrt.parent_id').' = '.$db->quoteName('l.id'))
                    ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'p').' ON '.$db->QuoteName('lrt.training').' = '.$db->QuoteName('p.code'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->QuoteName('c.training').' = '.$db->QuoteName('p.code'))
                    ->where($db->quoteName('c.id').' LIKE '.$filt_params['campaign'][0]);

        } else if (isset($filt_params['programme'][0]) && $filt_params['programme'][0] != '%') {

            $query->leftJoin($db->quoteName('#__emundus_setup_letters_repeat_training','lrt').' ON '.$db->quoteName('lrt.parent_id').' = '.$db->quoteName('l.id'))
                        ->where($db->quoteName('lrt.training').' LIKE '.$db->Quote($filt_params['programme'][0]));

        }

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting letters in model/messages at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /**
     * Gets a message template.
     *
     * @param Mixed The ID or label of the email.
     * @return Object The email we seek, false if none is found.
     */
    function getEmail($id) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('e.*, et.*, GROUP_CONCAT(etr.tags) as tags, GROUP_CONCAT(ca.candidate_attachment) AS candidate_attachments, GROUP_CONCAT(la.letter_attachment) AS letter_attachments')
            ->from($db->quoteName('#__emundus_setup_emails','e'))
            ->leftJoin($db->quoteName('#__emundus_email_templates','et').' ON '.$db->quoteName('e.email_tmpl').' = '.$db->quoteName('et.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_tags','etr').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('etr.parent_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment','ca').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('ca.parent_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment','la').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('la.parent_id'));

        // Allow the function to dynamically decide if it is getting by ID or label depending on the value submitted.
        if (is_numeric($id)) {
            $query->where($db->quoteName('e.id').' = '.$id);
        } else {
            $query->where($db->quoteName('e.lbl').' LIKE '.$db->quote($id));
        }


        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting template in model/messages at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return new stdClass;
        }

    }

    /**
     * Gets the email templates by using the category.
     *
     * @since 3.8.6
     * @param String $category The name of the category.
     * @return Object The list of templates corresponding.
     */
    function getEmailsByCategory($category) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select('id, subject')
            ->from($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('type') . ' = 2')
            ->andWhere($db->quoteName('published') . ' = 1');

        if ($category != 'all')
            $query->andWhere($db->quoteName('category').' = '.$db->quote($category));

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting emails by category in model/messages at query '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }


    }


    /**
     * Gets the a file from the setup_attachment table linked to an fnum.
     *
     * @since 3.8.6
     *
     * @param String $fnum          the fnum used for getting the attachment.
     * @param Int    $attachment_id the ID of the attachment used in setup_attachment
     *
     * @return bool|mixed
     */
    function get_upload($fnum, $attachment_id) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select($db->quoteName('filename'))
            ->from($db->quoteName('#__emundus_uploads'))
            ->where($db->quoteName('attachment_id').' = '.$attachment_id.' AND '.$db->quoteName('fnum').' = '.$db->Quote($fnum));

        try {

            $db->setQuery($query);
            return $db->loadResult();

        } catch (Exception $e) {
            JLog::add('Error getting upload filename in model/messages at query '.$query, JLog::ERROR, 'com_emudus');
            return false;
        }
    }

    /**
     * Gets the a file type label from the setup_attachment table .
     *
     * @since 3.8.13
     *
     * @param Int    $attachment_id the ID of the attachment used in setup_attachment
     *
     * @return bool|mixed
     */
    function get_filename($attachment_id) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('value'))
            ->from($db->quoteName('#__emundus_setup_attachments'))
            ->where($db->quoteName('id').' = '.$attachment_id);

        try {

            $db->setQuery($query);
            return $db->loadResult();

        } catch (Exception $e) {
            JLog::add('Error getting upload filename in model/messages at query '.$query, JLog::ERROR, 'com_emudus');
            return false;
        }
    }

    /**
     * Gets the a file from the setup_letters table linked to an fnum.
     *
     * @since 3.8.6
     * @param Int $letter_id the ID of the letter used in setup_letters
     * @return Object The letter object as found in the DB, also contains the status and training.
     * @return Boolean False if ther query fails.
     */
    function get_letter($letter_id) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select("l.*, GROUP_CONCAT( DISTINCT(lrs.status) SEPARATOR ',' ) as status, CONCAT(GROUP_CONCAT( DISTINCT(lrt.training) SEPARATOR '\",\"' )) as training")
            ->from($db->quoteName('#__emundus_setup_letters', 'l'))
            ->leftJoin($db->quoteName('#__emundus_setup_letters_repeat_status','lrs').' ON '.$db->quoteName('lrs.parent_id').' = '.$db->quoteName('l.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_letters_repeat_training','lrt').' ON '.$db->quoteName('lrt.parent_id').' = '.$db->quoteName('l.id'))
            ->where($db->quoteName('l.id').' = '.$letter_id);

        try {

            $db->setQuery($query);
            return $db->loadObject();

        } catch (Exception $e) {
            JLog::add('Error getting upload filename in model/messages at query '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emudus');
            return false;
        }

    }

    /**
     * Gets the names of candidate files.
     *
     * @since 3.8.6
     * @param String The IDs of the candidate files to get the names of
     * @return Array A list of objects containing the names and ids of the candidate files.
     */
    function getCandidateFileNames($ids) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'value']))
            ->from($db->quoteName('#__emundus_setup_attachments'))
            ->where($db->quoteName('id').' IN ('.$ids.')');

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting candidate file attachment name in model/messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }

    /**
     * Gets the names of candidate files.
     *
     * @since 3.8.6
     * @param String The IDs of the candidate files to get the names of
     * @return Array A list of objects containing the names and ids of the candidate files.
     */
    function getLetterFileNames($ids) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__emundus_setup_letters'))
            ->where($db->quoteName('id').' IN ('.$ids.')');

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting letter attachment name in model/messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /** Generates a DOC file for setup_letters
     *
     * @param Object $letter The template for the doc to create.
     * @param String $fnum   The fnum used to generate the tags.
     *
     * @return String The path to the saved file.
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    function generateLetterDoc($letter, $fnum) {

        //require_once (JPATH_LIBRARIES.DS.'vendor'.DS.'autoload.php');
        require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'vendor'.DS.'autoload.php');
        require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'export.php');

        $m_export = new EmundusModelExport;

        $m_emails   = new EmundusModelEmails;
        $m_files    = new EmundusModelFiles;

        $fnumsInfos = $m_files->getFnumTagsInfos($fnum);
        $attachInfos= $m_files->getAttachmentInfos($letter->attachment_id);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $gotenberg_activation = $eMConfig->get('gotenberg_activation', 0);

        $user = JFactory::getUser();

        $const = [
            'user_id'       => $user->id,
            'user_email'    => $user->email,
            'user_name'     => $user->name,
            'current_date'  => date('d/m/Y', time())
        ];

        try {

            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $preprocess = $phpWord->loadTemplate(JPATH_SITE.$letter->file);
            $tags = $preprocess->getVariables();

            $idFabrik   = array();
            $setupTags  = array();
            foreach ($tags as $i => $val) {
                $tag = strip_tags($val);
                if (is_numeric($tag))
                    $idFabrik[] = $tag;
                else
                    $setupTags[] = $tag;
            }

            // Process the Fabrik tags in the document ${tag}
            $fabrikElts = $m_files->getValueFabrikByIds($idFabrik);
            $fabrikValues = array();
            foreach ($fabrikElts as $elt) {

                $params = json_decode($elt['params']);
                $groupParams = json_decode($elt['group_params']);
                $isDate = ($elt['plugin'] == 'date');
                $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');

                if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                    $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnum, $params, $groupParams->repeat_group_button == 1);
                } else {
                    if ($isDate)
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnum, $elt['db_table_name'], $elt['name'], $params->date_form_format);
                    else
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnum, $elt['db_table_name'], $elt['name']);
                }

                if ($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown") {
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {

                        if ($elt['plugin'] == "checkbox") {
                            $val = json_decode($val['val']);
                        } else {
                            $val = explode(',', $val['val']);
                        }

                        if (count($val) > 0) {
                            foreach ($val as $k => $v) {
                                $index = array_search(trim($v),$params->sub_options->sub_values);
                                $val[$k] = $params->sub_options->sub_labels[$index];
                            }
                            $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $val);
                        } else {
                            $fabrikValues[$elt['id']][$fnum]['val'] = "";
                        }
                    }

                } elseif ($elt['plugin'] == "birthday") {

                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                        $val = explode(',', $val['val']);
                        foreach ($val as $k => $v) {
                            $val[$k] = date($params->details_date_format, strtotime($v));
                        }
                        $fabrikValues[$elt['id']][$fnum]['val'] = implode(",", $val);
                    }

                } else {
                    if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnum, $params, $groupParams->repeat_group_button == 1);
                    } else {
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnum, $elt['db_table_name'], $elt['name']);
                    }
                }

            }

            $preprocess = new \PhpOffice\PhpWord\TemplateProcessor(JPATH_SITE.$letter->file);
            if (isset($fnumsInfos)) {

                foreach ($setupTags as $tag) {
                    $val = "";
                    $lowerTag = strtolower($tag);

                    if (array_key_exists($lowerTag, $const)) {
                        $preprocess->setValue($tag, $const[$lowerTag]);
                    } elseif (!empty(@$fnumsInfos[$lowerTag])) {
                        $preprocess->setValue($tag, @$fnumsInfos[$lowerTag]);
                    } else {
                        $tags = $m_emails->setTagsWord(@$fnumsInfos['applicant_id'], null, $fnum, '');
                        $i = 0;
                        foreach ($tags['patterns'] as $key => $value) {
                            if ($value == $tag) {
                                $val = $tags['replacements'][$i];
                                break;
                            }
                            $i++;
                        }

                        $preprocess->setValue($tag, htmlspecialchars($val));
                    }
                }

                foreach ($idFabrik as $id) {
                    if (isset($fabrikValues[$id][$fnum])) {
                        $value = str_replace('\n', ', ', $fabrikValues[$id][$fnum]['val']);
                        $preprocess->setValue($id, $value);
                    } else {
                        $preprocess->setValue($id, '');
                    }
                }

                $rand = rand(0, 1000000);
                if (!file_exists(EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id']))  {
                    mkdir(EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'], 0775);
                }

                $filename = str_replace(' ', '', $fnumsInfos['applicant_name']).$attachInfos['lbl']."-".md5($rand.time()).".docx";

                $preprocess->saveAs(EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'].DS.$filename);

                if($gotenberg_activation == 1 && $letter->pdf == 1){
                    //convert to PDF
                    $src = EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'].DS.$filename;
                    $dest = str_replace('.docx', '.pdf', $src);
                    $filename = str_replace('.docx', '.pdf', $filename);
                    $res = $m_export->toPdf($src, $dest, $fnum);
                }

                $m_files->addAttachment($fnum, $filename, $fnumsInfos['applicant_id'], $fnumsInfos['campaign_id'], $letter->attachment_id, $attachInfos['description']);

                return EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'].DS.$filename;

            }
            unset($preprocess);

        } catch (Exception $e) {
            JLog::add('Error generating DOC file in model/messages', JLog::ERROR, 'com_emundus');
            return false;
        }

    }



    ///// All functions from here are for the messages view

    /** get all contacts the current user has received or sent a message as well as their latest message.
     *
     * @param null $user
     *
     * @return bool|mixed
     */
    public function getContacts($user = null) {

        if (empty($user)) {
            $user = $this->user->id;
        }

        $db = JFactory::getDbo();
        $query = "SELECT jos_messages.*, sender.name as name_from, sp_sender.label as profile_from, recipient.name as name_to, sp_recipient.label as profile_to, recipientUpload.attachment_id as photo_to, senderUpload.attachment_id as photo_from
                  FROM jos_messages
                  INNER JOIN jos_emundus_users AS sender ON sender.user_id = jos_messages.user_id_from
                  INNER JOIN jos_emundus_users AS recipient ON recipient.user_id = jos_messages.user_id_to
                  LEFT JOIN jos_emundus_setup_profiles sp_recipient ON sp_recipient.id =  recipient.profile
                  LEFT JOIN jos_emundus_setup_profiles sp_sender ON sp_sender.id =  sender.profile
                  LEFT JOIN jos_emundus_uploads recipientUpload ON recipientUpload.user_id = recipient.user_id AND recipientUpload.attachment_id = 10
                  LEFT JOIN jos_emundus_uploads senderUpload ON senderUpload.user_id = sender.user_id AND senderUpload.attachment_id = 10
                  INNER JOIN (
                      SELECT MAX(message_id) AS most_recent_message_id
                      FROM jos_messages
                      WHERE (folder_id = 2 OR (folder_id = 3 AND user_id_to = ".$user."))
                      GROUP BY CASE WHEN user_id_from > user_id_to
                          THEN user_id_to
                          ELSE user_id_from
                      END,
                      CASE WHEN user_id_from < user_id_to
                          THEN user_id_to
                          ELSE user_id_from
                      END) T ON T.most_recent_message_id = jos_messages.message_id
				  WHERE user_id_from = ".$user."
                  OR user_id_to = ".$user."
                  ORDER BY date_time DESC";

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting candidate file attachment name in model/messages at query: '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /** gets all messages received after the message $lastID
     *
     * @param         $lastId
     * @param   null  $user
     * @param   null  $other_user
     *
     * @return bool|mixed
     */
    public function updateMessages($lastId, $user = null, $other_user = null) {

        if (empty($user)) {
            $user = $this->user->id;
        }

        $db = JFactory::getDbo();

        $where = $db->quoteName('message_id').' > '.$lastId.' AND '.$db->quoteName('user_id_to').' = '.$user.' AND ' . $db->quoteName('state') . ' = 1 AND '.$db->quoteName('folder_id').' = 2';
        if (!empty($other_user)) {
            $where .= ' AND '.$db->quoteName('user_id_from').' = '.$other_user;
        }

        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__messages'))
            ->where($where)
            ->order('message_id DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error loading messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /** Get number of unread messages between two users (messages with folder_id 2)
     *
     * @param         $sender
     * @param   null  $receiver
     *
     * @return bool|mixed
     */
    public function getUnread($sender, $receiver = null) {

        if (empty($receiver)) {
            $receiver = $this->user->id;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(state)')
            ->from($db->quoteName('#__messages'))
            ->where($db->quoteName('state').' = 1 AND '.$db->quoteName('folder_id').' = 2 AND '.$db->quoteName('user_id_to').' = '.$receiver.' AND '.$db->quoteName('user_id_from').' = '.$sender);
        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error loading unread messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    /** load messages between two users ( messages with folder_id 2 )
     *
     * @param      $user1
     * @param null $user2
     *
     * @return bool|mixed
     */
    public function loadMessages($user1, $user2 = null) {

        if (empty($user2)) {
            $user2 = $this->user->id;
        }

        $db = JFactory::getDbo();

        // update message state to read
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__messages'))
            ->set([$db->quoteName('state').' = 0'])
            ->where('('.$db->quoteName('user_id_to').' = '.$user2.' AND '.$db->quoteName('user_id_from').' = '.$user1.') OR ('.$db->quoteName('user_id_from').' = '.$user2.' AND '.$db->quoteName('user_id_to').' = '.$user1.')');

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add('Error loading messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__messages'))
            ->where('('.$db->quoteName('user_id_from').' = '.$user2.' AND '.$db->quoteName('user_id_to').' = '.$user1.' AND '.$db->quoteName('folder_id').' = 2) OR ('.$db->quoteName('user_id_from').' = '.$user1.' AND '.$db->quoteName('user_id_to').' = '.$user2.' AND '.$db->quoteName('folder_id').' IN (2,3))')
            ->order($db->quoteName('date_time').' ASC')
            ->setLimit('100');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error loading messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    /** sends message folder_id=2 from user_from to user_to and sets stats to 1
     *
     * @param      $receiver
     * @param      $message
     * @param null $user
     * @param bool $system_message
     *
     * @return bool
     */
    public function sendMessage($receiver, $message, $user = null, $system_message = false) {

        if (empty($user)) {
            $user = $this->user->id;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ($system_message) {
            $folder = 3;
        } else {
            $folder = 2;
        }

        $columns = array('user_id_from', 'user_id_to', 'folder_id', 'date_time', 'state', 'priority', 'message');

        $values = array($user, $receiver, $folder, $db->quote(date("Y-m-d H:i:s")), 1, 0, $db->quote($message));

        $query->insert($db->quoteName('#__messages'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        try {

            $db->setQuery($query);
            $db->execute();
            return true;

        } catch (Exception $e) {
            JLog::add('Error sending message at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    public function deleteSystemMessages($user1, $user2) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);


        $query->delete($db->quoteName('#__messages'))
            ->where('(('.$db->quoteName('user_id_from').' = '.$user1.' AND '.$db->quoteName('user_id_to').' = '.$user2.') OR ('.$db->quoteName('user_id_from').' = '.$user2.' AND '.$db->quoteName('user_id_to').' = '.$user1.')) AND '.$db->quoteName('folder_id').' = 3 ');

        try {

            $db->setQuery($query);
            $db->execute();
            return true;

        } catch (Exception $e) {
            JLog::add('Error deleting messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }


    }



    /*
    Chatroom system

    Messages are put in folder ID 4.
    The PAGE column of the jos_messages table is used to indicate which chatroom the messages are in.
    Chatrooms are joined by adding user in jos_emundus_chatroom_users
    Chatrooms may be linked to an fnum or not, by addding the fnum in jos_emundus_chatroom.
    */


    /**
     * @param   null  $fnum
     * @param   null  $id
     *
     * @return bool|mixed
     *
     * @since version
     */
    public function createChatroom($fnum = null, $id = null) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $columns = [$db->quoteName('fnum')];
        $values = [$db->quote($fnum)];

        if (!empty($id)) {
            $columns[] = $db->quoteName('id');
            $values[] = $id;
        }

        $query->insert($db->quoteName('jos_emundus_chatroom'))
            ->columns($columns)
            ->values($values);
        $db->setQuery($query);

        try {

            $db->execute();
            return $db->insertid();

        } catch (Exception $e) {
            JLog::add('Error creating chatroom : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }

    }


    /**
     * @param   int  $chatroom Chatroom id, if the room doesn't exist, it will be created.
     * @param   mixed  ...$users Function is called as such : joinChatroom(4, $user1, $user2, $user3);
     *
     * @return bool
     *
     * @since version
     */
    public function joinChatroom($chatroom, ...$users) {

        if (!$this->chatRoomExists($chatroom)) {
            $chatroom = $this->createChatroom(null, $chatroom);
        }

        if (!$chatroom) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert($db->quoteName('jos_emundus_chatroom_users'))
            ->columns([$db->quoteName('chatroom_id'), $db->quoteName('user_id')]);
        foreach ($users as $user) {
            $query->values($chatroom.', '.$user);
        }

        $db->setQuery($query);

        try {
            $db->execute();
            return true;
        } catch (Exception $e) {
            JLog::add('Error joining chatroom : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }
    }


    /**
     * @param int $chatroom PAGE column in jos_messages is used to indicate that it's
     * @param String $message
     *
     * @return bool
     *
     * @since version
     */
    public function sendChatroomMessage($chatroom, $message) {

        if (!$this->chatRoomExists($chatroom)) {
            JLog::add('Sending message to non-existant chatroom.', JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert($db->quoteName('#__messages'))
            ->columns($db->quoteName(['user_id_from', 'folder_id', 'date_time', 'state', 'priority', 'message', 'page']))
            ->values($this->user->id.', 4, '.$db->quote(date("Y-m-d H:i:s")).', 1, 0, '.$db->quote($message).', '.$chatroom);

        $db->setQuery($query);

        try {
            $db->execute();
            return true;
        } catch (Exception $e) {
            JLog::add('Error sending chatroom message : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }
    }


    /**
     * @param int $chatroom
     *
     * @return array|bool|mixed
     *
     * @since version
     */
    public function getChatroomMessages($chatroom) {

        if (!$this->chatRoomExists($chatroom)) {
            JLog::add('Getting messages from non-existant chatroom.', JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__messages'))
            ->where($db->quoteName('folder_id').' = 4 AND '.$db->quoteName('page').' = '.$chatroom);
        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting chatroom messages : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }
    }

    /** gets all messages received after the message $lastID
     *
     * @param         $lastId
     * @param   int  $chatroom
     *
     * @return bool|mixed
     */
    public function updateChatroomMessages($lastId, $chatroom) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['m.*', 'u.name as user_from'])
            ->from($db->quoteName('#__messages', 'm'))
            ->leftJoin($db->quoteName('#__users', 'u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('m.user_id_from'))
            ->where($db->quoteName('folder_id').' = 4 AND '.$db->quoteName('page').' = '.$chatroom.' AND '.$db->quoteName('user_id_from').' <> '.JFactory::getUser()->id.' AND '.$db->quoteName('message_id').' > '.$lastId)
            ->order('message_id DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error loading messages at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }

    /**
     * @param $id
     *
     * @return bool|mixed|null
     *
     * @since version
     */
    public function getChatroom($id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('jos_emundus_chatroom'))
            ->where($db->quoteName('id').' = '.$id);
        $db->setQuery($query);

        try {
            return $db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting chatroom : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }
    }

    /**
     * @param int $chatroom_id
     *
     * @return bool|mixed|null
     *
     * @since version
     */
    public function getChatroomUsersId($chatroom_id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('cu.user_id')
            ->from($db->quoteName('jos_emundus_chatroom','c'))
            ->leftJoin($db->quoteName('jos_emundus_chatroom_users','cu').' ON '.$db->quoteName('cu.chatroom_id').' = '.$db->quoteName('c.id'))
            ->where($db->quoteName('c.id').' = '.$chatroom_id);
        $db->setQuery($query);

        try {
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('Error getting chatroom users : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }
    }


    /**
     * @param   mixed  ...$users
     *
     * @return bool|int
     *
     * @since version
     */
    public function getChatroomByUsers(...$users) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get all chatrooms containing at least one of our three users and that contain all memebers.
        // We then will check for a chatroom having ONLY these three users using PHP.
        $query->select('c.id, GROUP_CONCAT(cu.user_id) AS users, count(cu.user_id) as nbusers')
            ->from($db->quoteName('jos_emundus_chatroom', 'c'))
            ->leftJoin($db->quoteName('jos_emundus_chatroom_users', 'cu').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('cu.chatroom_id'))
            ->where($db->quoteName('cu.user_id').' IN ('.implode(',', $users).')')
            ->group($db->quoteName('c.id'))
            ->having($db->quoteName('nbusers').' = '.count($users));
        $db->setQuery($query);

        try {
            $chatrooms = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting chatroom by users : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }

        if (empty($chatrooms)) {
            return false;
        }

        $return = false;
        foreach ($chatrooms as $chatroom) {
            if (!array_diff($users, explode(',', $chatroom->users))) {
                $return = $chatroom->id;
                break;
            }
        }

        return $return;
    }


    private function chatRoomExists($chatroom) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('id'))
            ->from($db->quoteName('jos_emundus_chatroom'))
            ->where($db->quoteName('id').' = '.$chatroom);
        $db->setQuery($query);

        try {

            return !empty($db->loadResult());

        } catch (Exception $e) {
            JLog::add('Error getting chatroom : '.$e->getMessage(), JLog::ERROR, 'com_emundus.chatroom');
            return false;
        }

    }

    /// get message recap by fnum
    public function getMessageRecapByFnum($fnum) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');

        $_mEval = new EmundusModelEvaluation;
        $_mFile = new EmundusModelFiles;

        if(!empty($fnum)) {
            try {
                /// first --> get attachment ids from fnums
                $attachment_ids = $_mEval->getLettersByFnums($fnum, $attachments = true);

                $attachment_list = array();
                foreach ($attachment_ids as $key => $value) {
                    $attachment_list[] = $value['id'];
                }

                $attachment_list = array_unique(array_filter($attachment_list));            /// this line ensures that all attachment ids will appear once

                /// get message template from attachment list
                $query->clear()
//                    ->select('distinct #__emundus_setup_emails.id, #__emundus_setup_emails.lbl, #__emundus_setup_emails.subject, #__emundus_setup_emails.message')
                    ->select('distinct jos_emundus_setup_emails.*, jos_emundus_email_templates.Template')
                    ->from($db->quoteName('#__emundus_setup_emails'))
                    ->leftJoin($db->quoteName('#__emundus_email_templates') . ' ON ' . $db->quoteName('#__emundus_email_templates.id') . ' = ' . $db->quoteName('#__emundus_setup_emails.email_tmpl'))
                    ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment') . ' ON ' . $db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' = ' . $db->quoteName('#__emundus_setup_emails.id'))
                    ->where($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment') . ' IN (' . implode(',', $attachment_list) . ')');

                $db->setQuery($query);
                $_message_Info = $db->loadObjectList();

                /// third, for each $attachment ids --> detect the uploaded letters (if any), otherwise, detect the letter (default)
                $uploads = array();

                foreach($attachment_list as $key => $attach) {
                    /* generate letters each time get instant message */
                    $letter = $_mEval->generateLetters($fnum, [$attach], 0,0,0);

                    $upload_id = current($letter->files)['upload'];
                    $upload_filename = current($letter->files)['url'] . current($letter->files)['filename'];

                    $attachment_raw = $_mFile->getSetupAttachmentsById([$attach]);

                    $attachment_value = current($attachment_raw)['value'];
                    $attachment_label = current($attachment_raw)['lbl'];

                    $uploads[] = array('is_existed' => true, 'id' => $upload_id, 'value' => $attachment_value, 'label' => $attachment_label, 'dest' => $upload_filename);
                }

                /// get tags by email
                $_tags = $this->getTagsByEmail($_message_Info[0]->id);

                return array('message_recap' => $_message_Info, 'attached_letter' => $uploads, 'tags' => $_tags);
            } catch(Exception $e) {
                JLog::add('Error get available message by fnum : '.$e->getMessage(), JLog::ERROR, 'com_emundus.message');
                return false;
            }
        } else {
            return false;
        }
    }

    // get tags by email
    public function getTagsByEmail($eid) {
        if(!empty($eid)) {
            try {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->clear()
                    ->select('#__emundus_setup_action_tag.*')
                    ->from($db->quoteName('#__emundus_setup_action_tag'))
                    ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_tags') . ' ON ' . $db->quoteName('#__emundus_setup_action_tag.id') . ' = ' . $db->quoteName('#__emundus_setup_emails_repeat_tags.tags'))
                    ->leftJoin($db->quoteName('#__emundus_setup_emails') . ' ON ' . $db->quoteName('#__emundus_setup_emails.id') . ' = ' . $db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id'))
                    ->where($db->quoteName('#__emundus_setup_emails.id') . ' = ' . (int) $eid);

                $db->setQuery($query);
                return $db->loadObjectList();

            } catch(Exception $e) {
                JLog::add('Error get tags by fnum : '.$e->getMessage(), JLog::ERROR, 'com_emundus.message');
                return false;
            }
        } else {
            return false;
        }
    }

    /// add tags by fnum
    public function addTagsByFnum($fnum, $tmpl) {
        if(!empty($fnum) and !empty($tmpl)) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles();

            $fnum_info = $m_files->getFnumInfos($fnum);
            $_tags = $this->getTagsByEmail($tmpl);      // return type :: array

            if(!empty($_tags)) {
                foreach ($_tags as $key => $tag) {
                    $assoc_tag = $m_files->getTagsByIdFnumUser($tag->id, $fnum_info['fnum'], $fnum_info['applicant_id']);
                    if(!$assoc_tag) {
                        $m_files->tagFile([$fnum_info['fnum']], [$tag->id]);
                    }
                }
                return true;
            }
        } else {
            return false;
        }
    }

    // lock or unlock action for fnum
    public function getActionByFnum($fnum) {
        if(!empty($fnum)) {
            /// from fnum --> detect the message
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');

            $_mEval = new EmundusModelEvaluation;
            $_mFile = new EmundusModelFiles;

            try {
                $attachment_ids = $_mEval->getLettersByFnums($fnum, $attachments = true);

                if(count($attachment_ids) > 0) {

                    $attachment_list = array();
                    foreach ($attachment_ids as $key => $value) {
                        $attachment_list[] = $value['id'];
                    }

                    $attachment_list = array_unique(array_filter($attachment_list));            /// this line ensures that all attachment ids will appear once

                    /// get message template from attachment list
                    $query->clear()
                        ->select('distinct #__emundus_setup_emails.id, #__emundus_setup_emails.lbl, #__emundus_setup_emails.subject, #__emundus_setup_emails.message')
                        ->from($db->quoteName('#__emundus_setup_emails'))
                        ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment') . ' ON ' . $db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' = ' . $db->quoteName('#__emundus_setup_emails.id'))
                        ->where($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment') . ' IN (' . implode(',', $attachment_list) . ')');

                    $db->setQuery($query);
                    $_message_Info = $db->loadObjectList();
                    if(!empty($_message_Info)) {
                        return true;
                    }
                    else {
                        return false;
                    }
                }
                else {
                    return false;
                }
            } catch(Exception $e) {
                JLog::add('Error get getActionByFnum : '.$e->getMessage(), JLog::ERROR, 'com_emundus.message');
                return false;
            }
        } else {
            return false;
        }
    }

    /// get all documents being letter
    public function getAllDocumentsLetters() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_setup_attachments.*')
                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->where($db->quoteName('#__emundus_setup_attachments.id') . ' IN (SELECT DISTINCT #__emundus_setup_letters.attachment_id FROM #__emundus_setup_letters)');

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('Cannot get all documents being letter : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return [];      /// return empty array
        }
    }

    /// get attachments by profile (jos_emundus_setup_attachment_profiles)
    public function getAttachmentsByProfiles($fnums=array()) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($fnums) and !is_null($fnums)) {
            try {
                require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'profile.php');
                require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');

                $_mProfiles = new EmundusModelProfile;
                $_mFiles = new EmundusModelFiles;

                $_profiles = array();

                foreach($fnums as $fnum) {
                    $fnumInfos = $_mFiles->getFnumInfos($fnum);

                    //$profile = $_mProfiles->getProfileByStatus($fnum)['profile'];
                    $profiles = $_mProfiles->getProfilesIDByCampaign([$fnumInfos['id']]);

                    if(!is_null($profiles)) {
                        foreach ($profiles as $profile) {
                            $_profiles[] = $profile;
                        }
                    }
                    else {
                        /// if profile is null, get default profile of campaign
                        $_fnumInfo = $_mFiles->getFnumInfos($fnum);
                        $_profiles[] = $_fnumInfo['profile_id'];
                    }
                }

                $_profiles = array_unique($_profiles);

                $attachments = new stdClass();

                foreach($_profiles as $_p) {
                    $letters = array();
                    $query->clear()
                        ->select('#__emundus_setup_attachments.*, #__emundus_setup_profiles.id AS pr_id, #__emundus_setup_profiles.label as pr_label')
                        ->from($db->quoteName('#__emundus_setup_attachments'))
                        ->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles') . ' ON ' . $db->quoteName('#__emundus_setup_attachment_profiles.attachment_id') . ' = ' . $db->quoteName('#__emundus_setup_attachments.id'))
                        ->leftJoin($db->quoteName('#__emundus_setup_profiles') . ' ON ' . $db->quoteName('#__emundus_setup_attachment_profiles.profile_id') . ' = ' . $db->quoteName('#__emundus_setup_profiles.id'))
                        ->where($db->quoteName('#__emundus_setup_attachment_profiles.profile_id') . ' = ' . $_p);
                    $db->setQuery($query);

                    $res = $db->loadObjectList();

                    foreach($res as $_r) { $letters[] = array('letter_id' => $_r->id, 'letter_label' => $_r->value); }

                    $attachments->$_p->label = $_mProfiles->getProfileById($_p)['label'];
                    $attachments->$_p->letters = $letters;
                }

                return (array)$attachments;
            } catch(Exception $e) {
                JLog::add('Cannot get attachments by profiles : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                return [];      /// return empty array
            }
        } else { return false; }
    }

    /// get all attachments
    public function getAllAttachments() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_setup_attachments.*')
                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->where($db->quoteName('published') . " = 1");

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('Cannot get all attachments : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return [];      /// return empty array
        }
    }

    /// add tags by fnums
    public function addTagsByFnums($fnums, $tmpl) {
        $set_tag = [];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($fnums) and !empty($tmpl)) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles();

            foreach($fnums as $fnum) {
                $this->addTagsByFnum($fnum, $tmpl);
            }
            return true;
        } else {
            return false;       /// no fnum or no email template, cannot add tag
        }
    }
}
