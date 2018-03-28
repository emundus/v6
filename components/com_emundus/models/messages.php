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

    /**
     * Constructor
     *
     * @since 3.8.6
     */
    public function __construct() {

    }

    /**
     * Gets all published message templates of a certain type.
     *
     * @param Int $type The type of email to get, type 2 is by default (Templates).
     * @return Boolean False if the query fails and nothing can be loaded.
     * @return Array AN array of objects describing the messages. (sender, subject, body, etc..)
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
            JLog::add('Error getting emails in model/messages at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }

	}


    /**
     * Gets all published message categories of a certain type.
     *
     * @param Int $type The type of category to get, type 2 is by default (Templates).
     * @return Boolean False if the query fails and nothing can be loaded.
     * @return Array An array of the categories.
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
            JLog::add('Error getting email categories in model/messages at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /**
     * Gets all published attachements unless a filter is active.
     *
     * @return Boolean False if the query fails and nothing can be loaded.
     * @return Array An array of objects describing attachements.
     */
    function getAttachements() {

        $db = JFactory::getDbo();
        $session = JFactory::getSession();

        $filt_params = $session->get('filt_params');

        $query = $db->getQuery(true);

        // Get all info about the attachements in the table.
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
            JLog::add('Error getting attachements in model/messages at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
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

            $query->leftJoin($db->quoteName('#__emundus_setup_programmes', 'p').' ON '.$db->QuoteName('l.code').' = '.$db->QuoteName('p.code'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->QuoteName('c.profile_id').' = '.$db->QuoteName('p.id'))
                    ->where($db->quoteName('c.id').' LIKE '.$filt_params['campaign'][0]);

        } else if (isset($filt_params['programme'][0]) && $filt_params['programme'][0] != '%') {

            $query->where($db->quoteName('l.code').' LIKE '.$db->Quote($filt_params['programme'][0]));

        }

        try {

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting letters in model/messages at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


    /**
     * Gets a message template.
     *
     * Gets selected template from POST data.
     * Returns a JSON containing the template info.
     */
	function getTemplate() {

		$db = JFactory::getDBO();
        $select = JRequest::getVar('select', null, 'POST', 'none', 0);

        $query = $db->getQuery(true);

        $query->select('*')
                ->from($db->quoteName('#__emundus_setup_emails'))
                ->where($db->quoteName('id').' = '.$select);

        try {

            $db->setQuery($query);
            $email = $db->loadObject();

        } catch (Exception $e) {
            JLog::add('Error getting template in model/messages at query :'.$query->__toString(), JLog::ERROR, 'com_emundus');
            echo json_encode((object)(array('status' => false)));
            die();
        }

        echo json_encode((object)(array('status' => true, 'tmpl' => $email)));
		die();

    }


    /**
     * Gets a message template.
     *
     * Gets selected template from POST data.
     * Returns a JSON containing the template info.
     */
	function getEmail($id) {

		$db = JFactory::getDBO();

        $query = $db->getQuery(true);

        $query->select('*')
                ->from($db->quoteName('#__emundus_setup_emails','e'))
                ->leftJoin($db->quoteName('#__emundus_email_templates','et').' ON '.$db->quoteName('e.email_tmpl').' = '.$db->quoteName('et.id'))
                ->where($db->quoteName('e.id').' = '.$id);

        try {

            $db->setQuery($query);
            return $db->loadObject();

        } catch (Exception $e) {
            JLog::add('Error getting template in model/messages at query :'.$query->__toString(), JLog::ERROR, 'com_emundus');
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
            JLog::add('Error getting emails by category in model/messages at query '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }


    }


    /**
     * Gets the a file from the setup_attachement table linked to an fnum.
     *
     * @since 3.8.6
     * @param String $fnum the fnum used for getting the attachement.
     * @param Int $attachement_id the ID of the attachement used in setup_attachement
     */
    function get_upload($fnum, $attachement_id) {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select($db->quoteName('filename'))
                ->from($db->quoteName('#__emundus_uploads'))
                ->where($db->quoteName('attachement_id').' = '.$attachement_id.' AND '.$db->quoteName('fnum').' = '.$fnum);

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
            JLog::add('Error getting upload filename in model/messages at query '.$query->__toString(), JLog::ERROR, 'com_emudus');
            return false;
        }

    }


    /** Generates a DOC file for setup_letters
     *
     * @param Object $letter The template for the doc to create.
     * @param String $fnum The fnum used to generate the tags.
     * @return String The path to the saved file.
     * @return Boolean False if an error occurs.
     */
    function generateLetterDoc($letter, $fnum) {

        require_once (JPATH_LIBRARIES.DS.'vendor'.DS.'autoload.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'emails.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

        $m_emails   = new EmundusModelEmails;
        $m_files    = new EmundusModelFiles;


        $fnumsInfos = $m_files->getFnumTagsInfos($fnum);
        $attachInfos= $m_files->getAttachmentInfos($letter->attachment_id);

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

                        if ($elt['plugin'] == "checkbox")
                            $val = json_decode($val['val']);
                        else
                            $val = explode(',', $val['val']);

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
                    if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin)
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnum, $params, $groupParams->repeat_group_button == 1);
                    else
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnum, $elt['db_table_name'], $elt['name']);
                }

            }

            $preprocess = new \PhpOffice\PhpWord\TemplateProcessor(JPATH_BASE.$letter->file);
            if (isset($fnumsInfos)) {

                foreach ($setupTags as $tag) {
                    $val = "";
                    $lowerTag = strtolower($tag);

                    if (array_key_exists($lowerTag, $const))
                        $preprocess->setValue($tag, $const[$lowerTag]);
                    elseif (!empty(@$fnumsInfos[$lowerTag]))
                        $preprocess->setValue($tag, @$fnumsInfos[$lowerTag]);
                    else {
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
                if (!file_exists(EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id']))
                    mkdir(EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'], 0775);

                $filename = str_replace(' ', '', $fnumsInfos['applicant_name']).$attachInfos['lbl']."-".md5($rand.time()).".docx";

                $preprocess->saveAs(EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'].DS.$filename);

                $upId = $m_files->addAttachment($fnum, $filename, $fnumsInfos['applicant_id'], $fnumsInfos['campaign_id'], $letter->attachment_id, $attachInfos['description']);

                return EMUNDUS_PATH_ABS.$fnumsInfos['applicant_id'].DS.$filename;

            }
            unset($preprocess);


        } catch (Extension $e) {
            JLog::add('Error generating DOC file in model/messages', JLog::ERROR, 'com_emundus');
            return false;
        }

    }

}

?>