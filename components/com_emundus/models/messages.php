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
                ->where($db->quoteName('type').' IN ('.$db->Quote($type).') AND published=1');

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
                ->where($db->quoteName('type').' IN ('.$db->Quote($type).') AND published=1');

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
     * @param Bool Whether or not to also get the candidate file attachments linked to this template, this is an option use for compatibility because some DBs may not have this table.
     * @param Bool Whether or not to also get the letter attachments linked to this template.
     * @return Object The email we seek, false if none is found.
     */
	function getEmail($id, $candidateAttachments = false, $letterAttachments = false) {

		$db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $select = 'e.*, et.*, GROUP_CONCAT(etr.tags) as tags';

        if ($candidateAttachments) {
	        $select .= ', GROUP_CONCAT(ca.candidate_attachment) AS candidate_attachments';
        }

        if ($letterAttachments) {
	        $select .= ', GROUP_CONCAT(la.letter_attachment) AS letter_attachments';
        }

        $query->select($select)
                ->from($db->quoteName('#__emundus_setup_emails','e'))
                ->leftJoin($db->quoteName('#__emundus_email_templates','et').' ON '.$db->quoteName('e.email_tmpl').' = '.$db->quoteName('et.id'))
                ->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_tags','etr').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('etr.parent_id'));

        if ($candidateAttachments) {
            $query->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment','ca').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('ca.parent_id'));
		}

        if ($letterAttachments) {
            $query->leftJoin($db->quoteName('#__emundus_setup_emails_repeat_letter_attachment','la').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('la.parent_id'));
		}

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
            return false;
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
                ->from($db->quoteName('#__emundus_setup_emails'));

        if ($category != 'all')
            $query->where($db->quoteName('category').' = '.$db->Quote($category));

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
            $preprocess = $phpWord->loadTemplate(JPATH_BASE.$letter->file);
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

            $preprocess = new \PhpOffice\PhpWord\TemplateProcessor(JPATH_BASE.$letter->file);
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

        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'emails.php');

        $_mEval = new EmundusModelEvaluation;
        $_mFile = new EmundusModelFiles;
        $_mEmail = new EmundusModelEmails;

	    if(!empty($fnum)) {
	        try {
                /// first --> get attachment ids from fnums
                $attachment_ids = $_mEval->getLettersByFnums($fnum, $attachments = true);

                $attachment_list = array();
                foreach ($attachment_ids['attachments'] as $key => $value) {
                    $attachment_list[] = $value['id'];
                }

                $attachment_list = array_unique(array_filter($attachment_list));            /// this line ensures that all attachment ids will appear once
                $email_id = array_unique(array_filter($attachment_ids['emails']));

                $email_Template = $_mEmail->getEmailById($email_id[0]);

                /// third, for each $attachment ids --> detect the uploaded letters (if any), otherwise, detect the letter (default)
                $uploads = array();

                foreach($attachment_list as $key => $attach) {

                    $upload_files = $_mEval->getFilesByAttachmentFnums($attach, [$fnum]);

                    /// $uploaded_files is not empty --> get this file
                    if(!empty($upload_files)) {
                        /// check this file exist on disk or not
                        $file_path = EMUNDUS_PATH_ABS . $upload_files[0]->user_id . DS. $upload_files[0]->filename;
                        if(file_exists($file_path)) {
                            $uploads[] = array('is_existed' => true, 'id' => $upload_files[0]->id, 'value' => $upload_files[0]->value, 'label' => $upload_files[0]->lbl, 'dest' => JURI::base() . EMUNDUS_PATH_REL . $upload_files[0]->user_id . DS . $upload_files[0]->filename);
                        } else {
                            /// generate document
                            $letter = $_mEval->generateLetters($fnum, [$attach], 0,0,0);
                            $_generated_letter = $_mEval->getFilesByAttachmentFnums($attach, [$fnum]);
                            $uploads[] = array('is_existed' => true, 'id' => $_generated_letter[0]->id, 'value' => $_generated_letter[0]->value, 'label' => $_generated_letter[0]->lbl, 'dest' => JURI::base().EMUNDUS_PATH_REL . $_generated_letter[0]->user_id . DS. $_generated_letter[0]->filename);
                        }
                    } else {
                        /// if upload file does not exist --> generate this file
                        //$letter = $_mEval->getLetterTemplateForFnum($fnum, [$attach]);

                        $letter = $_mEval->generateLetters($fnum, [$attach], 0,0,0);
                        $_generated_letter = $_mEval->getFilesByAttachmentFnums($attach, [$fnum]);
                        $uploads[] = array('is_existed' => true, 'id' => $_generated_letter[0]->id, 'value' => $_generated_letter[0]->value, 'label' => $_generated_letter[0]->lbl, 'dest' => JURI::base().EMUNDUS_PATH_REL . $_generated_letter[0]->user_id . DS. $_generated_letter[0]->filename);
                    }
                }

                /// get tags by email
                $_tags = $this->getTagsByEmail($email_Template->id);

                return array('message_recap' => $email_Template, 'attached_letter' => $uploads, 'tags' => $_tags);
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
                JLog::add('Error get tags by email : '.$e->getMessage(), JLog::ERROR, 'com_emundus.message');
                return false;
            }
        } else {
	        return false;
        }
    }

    /// add tags by fnum
    public function addTagsByFnums($fnums, $tmpl) {
        $set_tag = [];
        if(!empty($fnums) and !empty($tmpl)) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles();

            $_tags = $this->getTagsByEmail($tmpl);      // return type :: array

            foreach($fnums as $key => $fnum) {
                $fnum_info = $m_files->getFnumInfos($fnum);

                if (!empty($_tags)) {
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);       // make new query

                    $query->clear()
                        ->delete($db->quoteName('#__emundus_tag_assoc'))
                        ->where($db->quoteName('#__emundus_tag_assoc.fnum') . ' = ' . $fnum)
                        ->andWhere($db->quoteName('#__emundus_tag_assoc.tag_assoc_type') . ' = ' . $db->quote('email'));
                    $db->setQuery($query);
                    $db->execute();

                    foreach ($_tags as $key => $tag) {

                        $assoc_tag = $m_files->getTagsByIdFnumUser($tag->id, $fnum_info['fnum'], $fnum_info['applicant_id']);

                        if ($assoc_tag == false) {
                            $fnum_tag = $m_files->tagFile([$fnum_info['fnum']], [$tag->id], 'email');

                            if($fnum_tag == true) {
                                $set_tag[] = true;
                            }
                        } else {
                            /// do nothing
                        }
                    }
                } else {

                }
            }
        } else {
            return false;
        }

        if(!empty($set_tag)) {
            return true;
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

            require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');
            require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
            require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'emails.php');

            $_mEval = new EmundusModelEvaluation;
            $_mFile = new EmundusModelFiles;
            $_mEmail = new EmundusModelEmails;

            try {
                /// first --> get attachment ids from fnums
                $attachment_ids = $_mEval->getLettersByFnums($fnum, $attachments = true);

                $attachment_list = array();
                foreach ($attachment_ids['attachments'] as $key => $value) {
                    $attachment_list[] = $value['id'];
                }

                $attachment_list = array_unique(array_filter($attachment_list));            /// this line ensures that all attachment ids will appear once
                $email_id = array_unique(array_filter($attachment_ids['emails']));

                $email_Template = $_mEmail->getEmailById($email_id[0]);

                if(!empty($email_Template)) {
                    return true;
                }
                else {
                    return false;
                }
            } catch(Exception $e) {
                JLog::add('Cannot get action by fnum : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
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
            return false;
        }
    }

    /// send email to applicant with two choices: 'customized' or 'instant' (with envelop icon)
    public function sendEmailToApplicant($fnum, $mail_from_sys, $mail_from_sys_name, $data) {
        $logs = "";
        $jinput = JFactory::getApplication()->input;

        // If no mail sender info is provided (already in controller), we use the system global config
        $mail_from_name = $jinput->post->getString('mail_from_name', $mail_from_sys_name);
        $mail_from = $jinput->post->getString('mail_from', $mail_from_sys);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'evaluation.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'messages.php');

        $m_messages = new EmundusModelMessages();
        $m_emails = new EmundusModelEmails();
        $m_files = new EmundusModelFiles();
        $m_campaign = new EmundusModelCampaign();
        $_meval = new EmundusModelEvaluation;

        /// from fnum --> detect candidat email
        $fnum_info = $m_files->getFnumInfos($fnum);

        $candidat_email = $fnum_info['email'];

        // get programme info
        $programme = $m_campaign->getProgrammeByTraining($fnum_info['training']);


//        var_dump($data);die;

        $toAttach = [];
        $post = [
            'FNUM' => $fnum_info['fnum'],
            'USER_NAME' => $fnum_info['name'],
            'COURSE_LABEL' => $programme->label,
            'CAMPAIGN_LABEL' => $fnum_info['label'],
            'CAMPAIGN_YEAR' => $fnum_info['year'],
            'CAMPAIGN_START' => $fnum_info['start_date'],
            'CAMPAIGN_END' => $fnum_info['end_date'],
            'SITE_URL' => JURI::base(),
            'USER_EMAIL' => $fnum_info['email'],
        ];

        //// GLOBAL CONFIGS OF SENDER
        $tags = $m_emails->setTags($fnum_info['applicant_id'], $post, $fnum_info['fnum']);

        $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
        $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);

        // If the email sender has the same domain as the system sender address.
        if (substr(strrchr($mail_from, "@"), 1) === substr(strrchr($mail_from_sys, "@"), 1)) {
            $mail_from_address = $mail_from;
        } else {
            $mail_from_address = $mail_from_sys;
        }

        // Set sender
        $sender = [
            $mail_from_address,
            $mail_from_name
        ];

        $mailer = JFactory::getMailer();
        $mailer->setSender($sender);
        $mailer->addReplyTo($mail_from, $mail_from_name);
        $mailer->addRecipient($fnum_info['email']);

        /// END OF GLOBAL CONFIG

        /////// $raw_data --> not ∅ (send customized message)
        if($data['mode'] == 'custom') {
            $template = $m_emails->getEmailById($data['template']);

            if (empty($template) || empty($template->Template)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select($db->quoteName('Template'))
                    ->from($db->quoteName('#__emundus_email_templates'))
                    ->where($db->quoteName('id') . ' = 1');
                $db->setQuery($query);

                $template->Template = $db->loadResult();
            }

            $body = $m_emails->setTagsFabrik($data['message'], [$fnum]);
            $subject = $m_emails->setTagsFabrik($data['mail_subject'], [$fnum]);

            // Tags are replaced with their corresponding values using the PHP preg_replace function.
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);

            $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);

            if (!empty($data['cc'])) {
                $mailer->addCc($data['cc']);
            }

            if (!empty($data['bcc'])) {
                $mailer->addBcc($data['bcc']);
            }

            $toAttach = [];

            // attach uploaded file(s) if they exist
            if (!empty($data['attachments']['upload'])) {
                foreach ($data['attachments']['upload'] as $upload) {
                    if (file_exists(JPATH_BASE.DS.$upload)) {
                        $toAttach[] = JPATH_BASE.DS.$upload;
                    }
                }
            }

            // Files gotten from candidate files, requires attachment read rights.
            if (EmundusHelperAccess::asAccessAction(4, 'r') && !empty($data['attachments']['candidate_file'])) {
                foreach ($data['attachments']['candidate_file'] as $candidate_file) {
                    $filename = $this->get_upload($fnum, $candidate_file);
                    if ($filename != false) {

                        // Build the path to the file we are searching for on the disk.
                        $path = EMUNDUS_PATH_ABS.$fnum_info['applicant_id'].DS.$filename;
                        if (file_exists($path)) {
                            $toAttach[] = $path;
                        }
                    }
                }
            }

            // Files generated using the Letters system. Requires attachment creation and doc generation rights.
            if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c') && !empty($data['attachments']['setup_letters'])) {
                /// get all evaluation info for this fnum
                $query = $this->_db->getQuery(true);

                $query->clear()
                    ->select('#__emundus_setup_profiles.id')
                    ->from($this->_db->quoteName('#__emundus_setup_profiles'))
                    ->leftJoin($this->_db->quoteName('#__emundus_users') . ' ON ' . $this->_db->quoteName('#__emundus_setup_profiles.id') . ' = ' . $this->_db->quoteName('#__emundus_users.profile'))
                    ->leftJoin($this->_db->quoteName('#__emundus_evaluations') . ' ON ' . $this->_db->quoteName('#__emundus_evaluations.user') . ' = ' . $this->_db->quoteName('#__emundus_users.user_id'))
                    ->where($this->_db->quoteName('#__emundus_evaluations.fnum') . ' = ' . $fnum);

                $this->_db->setQuery($query);
                $evals = $this->_db->loadColumn();



                foreach ($data['attachments']['setup_letters'] as $setup_letter) {

                    $letters = $_meval->getLettersByFnumTemplates($fnum, [$setup_letter]);

                    if (!empty($letters)) {
                        foreach ($letters as $key => $email_tmpl) {
                            if (!is_null($email_tmpl->evaluator)) {
                                if(!empty($evals)) {
                                    foreach ($evals as $index => $eval) {
                                        if ($email_tmpl->evaluator == $eval) {
                                            $rand = rand(0, 1000000);       // random number and then
                                            $_filename = explode('/',$email_tmpl->file)[count(explode('/',$email_tmpl->file))-1];
                                            $res = $_meval->generateLetters($fnum, [$email_tmpl->attachment_id], 0, 0, 0, $email_tmpl, date("Y-m-d") . '_' . $email_tmpl->evaluator . '_' . md5(rand()));
                                            $_files = json_decode($res)->files;
                                            foreach($_files as $k => $f) {
                                                $path = EMUNDUS_PATH_ABS . $fnum_info['applicant_id'] . DS . $f->filename;
                                                $toAttach[] = $path;
                                                break;
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                } else {
                                    $res = $_meval->generateLetters($fnum, [$email_tmpl->attachment_id], 0, 0, 0);
                                    $res_status = json_decode($res)->status;
                                    $res_data = reset(json_decode($res)->files);
                                    $path = EMUNDUS_PATH_ABS . $fnum_info['applicant_id'] . DS . $res_data->filename;
                                    $toAttach[] = $path;
                                }
                            }

                            /// if no evaluation available --> classic mode like KIT
                            else {
                                $res = $_meval->generateLetters($fnum, [$setup_letter], 0, 0, 0);
                                $res_status = json_decode($res)->status;
                                $res_data = reset(json_decode($res)->files);
                                $path = EMUNDUS_PATH_ABS . $fnum_info['applicant_id'] . DS . $res_data->filename;
                                $toAttach[] = $path;
                            }
                        }
                    } else {
                        // Envoyer l'email sans documents car aucun document attache avec cette campagne
                        $forceLetters = $_meval->getLettersByFnumTemplates($fnum, [$setup_letter], $forceGet='force');      /// get $forceGet not null

                        require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
                        //$path = generateLetterFromHtml(reset($forceLetters), $fnum, $fnum_info['applicant_id'], $fnum_info['training']);            /// here I should use $_meval->generateLetters :: with optional param = forceLetter
                        $res = $_meval->generateLetters($fnum, [$setup_letter],0, 0, 0, null,null, $forceLetters);            /// here I should use $_meval->generateLetters :: with optional param = forceLetter
                        $res_status = json_decode($res)->status;
                        $res_data = reset(json_decode($res)->files);
                        $path = EMUNDUS_PATH_ABS . $fnum_info['applicant_id'] . DS . $res_data->filename;
                        $toAttach[] = $path;
                    }

                }
            }

            $mailer->addAttachment($toAttach);
            $send = $mailer->Send();
        }

        /////// $TEMPLATE is ∅ --> send instant message (just based on fnum)...Used case: send instant message
        else {
            /// get message recap by fnum --> reuse the function models/messages.php/getMessageRecapByFnum($fnum)
            $message = $m_messages->getMessageRecapByFnum($fnum);
            $email_recap = $message['message_recap'];                   /// length = 1
            $letter_recap = $message['attached_letter'];                /// length >= 1

            if (!empty($email_recap) and !is_null($email_recap)) {

                $body = $m_emails->setTagsFabrik($email_recap->message, [$fnum_info['fnum']]);
                $subject = $m_emails->setTagsFabrik($email_recap->subject, [$fnum_info['fnum']]);

                // Tags are replaced with their corresponding values using the PHP preg_replace function.
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
                $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

                $mailer->setSubject($subject);
                $mailer->isHTML(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);

                $attachments = $_meval->getLettersByFnums($fnum, $attachments = true);

                if (!empty($attachments) and !is_null($attachments)) {

                    foreach ($attachments['attachments'] as $key => $value) {
                        $attachment_ids[] = $value['id'];
                    }

                    $attachment_ids = array_unique(array_filter($attachment_ids));

                    /// get attachment letters by fnum
                    $file_path = [];

                    if(!is_null($data['attachments'])) {
                        if (count($data['attachments']) > 0) {
                            if (count($data['attachments']) == count($letter_recap)) {
                                foreach ($attachment_ids as $key => $value) {
                                    $attached_letters = $_meval->getFilesByAttachmentFnums($value, [$fnum]);
                                    $file_path[] = EMUNDUS_PATH_ABS . $attached_letters[0]->user_id . DS . $attached_letters[0]->filename;
                                }
                            } else {
                                // get exactly the path to selected letter
                                foreach ($data['attachments'] as $key => $value) {
                                    $custom_file = $m_files->getAttachmentsById([$value]);
                                    $file_path[] = EMUNDUS_PATH_ABS . $custom_file[0]['user_id'] . DS . $custom_file[0]['filename'];
                                }
                            }
                            $mailer->addAttachment($file_path);
                        }
                    }
                    else {
                        if(count($attachment_ids) > 0) {
                            foreach ($attachment_ids as $key => $value) {
                                $attached_letters = $_meval->getFilesByAttachmentFnums($value, [$fnum]);
                                $file_path[] = EMUNDUS_PATH_ABS . $attached_letters[0]->user_id . DS . $attached_letters[0]->filename;
                            }
                            $mailer->addAttachment($file_path);
                        }
                    }
                }
                // assoc tag for fnum --> in case of instant message
                $assoc_tag = $this->addTagsByFnums(explode(',', $fnum), $email_recap->id);
            }

            $send = $mailer->Send();
        }

        if ($send !== true) {
            $failed[] = $candidat_email;
            $logs .= '<div class="alert alert-dismissable alert-danger">' . JText::_('EMAIL_NOT_SENT') . ' : ' . $candidat_email . ' ' . $send . '</div>';
            JLog::add($send, JLog::ERROR, 'com_emundus.email');
        } else {
            $success[] = $candidat_email;
            $message = array(
                'applicant_id_to' => $fnum_info['applicant_id'],
                'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $candidat_email,
            );
            $m_emails->logEmail($message);
            $logs .= JText::_('EMAIL_SENT') . ' : ' . $candidat_email . '<br>';
        }

        // Due to mailtrap now limiting emails sent to fast, we add a long sleep.
        $config = JFactory::getConfig();

        return array('status' => $send, 'success' => $success, 'failed' => $failed);   ///send is "true" or "false"
    }
}
