<?php
/**
 * Profile Model for eMundus Component
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelEmails extends JModelList {
    var $_db = null;
    var $_em_user = null;
    var $_user = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct() {
        parent::__construct();

        $this->_db = JFactory::getDBO();
        $this->_em_user = JFactory::getSession()->get('emundusUser');
        $this->_user = JFactory::getUser();

        JLog::addLogger(['text_file' => 'com_emundus.email.error.php'], JLog::ERROR);
    }

    /**
     * Get email template by code
     * @param    $lbl string the email code
     * @return   object|bool  The email template object
     *
     * @since version v6
     */
    public function getEmail($lbl) {
        $email = null;

        if (!empty($lbl)) {
            $query = $this->_db->getQuery(true);
            $query->select('se.*, et.Template')
                ->from('#__emundus_setup_emails AS se')
                ->leftJoin('#__emundus_email_templates AS et ON et.id = se.email_tmpl')
                ->where('se.lbl like ' . $this->_db->quote($lbl));

            try {
                $this->_db->setQuery($query);
                $email = $this->_db->loadObject();
            } catch(Exception $e) {
                error_log($e->getMessage(), 0);
                JLog::add($query, JLog::ERROR, 'com_emundus.email');
            }
        }

        return $email;
    }

    /**
     * Get email template by ID
     * @param   $id int The email template ID
     * @param   $lbl string the email code
     * @return  object  The email template object
     *
     * @since version v6
     */
    public function getEmailById($id)
    {
        $email = new stdClass();

        if (!empty($id)) {
            $query = $this->_db->getQuery(true);

            $query->select('ese.*, et.Template')
                ->from('#__emundus_setup_emails AS ese')
                ->leftJoin('#__emundus_email_templates AS et ON et.id = ese.email_tmpl')
                ->where('ese.id = ' . $this->_db->quote($id));

            try {
                $this->_db->setQuery($query);
                $email = $this->_db->loadObject();
            } catch (Exception $e) {
                JLog::add('Failed to get email by id ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }
        }

        return $email;
    }

    /**
     * Get email definition to trigger on Status changes
     * @param   $step           INT The status of application
     * @param   $code           array of programme code
     * @param   $to_applicant   int define if trigger concern selected fnum from list or not. Can be 0, 1
     * @return  array           Emails templates and recipient to trigger
     *
     * @since version v6
     */
    public function getEmailTrigger($step, $code, $to_applicant = 0, $to_current_user = null, $student = null) {
        if(!isset($step) || empty($code)){
            return [];
        }

        $query = $this->_db->getQuery(true);
        $query->select('eset.id as trigger_id, eset.step, ese.*, eset.to_current_user, eset.to_applicant, eserp.programme_id, esp.code, esp.label, eser.profile_id, eserg.group_id, eseru.user_id, et.Template, GROUP_CONCAT(ert.tags) as tags, GROUP_CONCAT(erca.candidate_attachment) as attachments, GROUP_CONCAT(err1.receivers) as cc, GROUP_CONCAT(err2.receivers) as bcc')
            ->from($this->_db->quoteName('#__emundus_setup_emails_trigger', 'eset'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails','ese').' ON '.$this->_db->quoteName('ese.id').' = '.$this->_db->quoteName('eset.email_id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id','eserp').' ON '.$this->_db->quoteName('eserp.parent_id').' = '.$this->_db->quoteName('eset.id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_programmes','esp').' ON '.$this->_db->quoteName('esp.id').' = '.$this->_db->quoteName('eserp.programme_id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id','eser').' ON '.$this->_db->quoteName('eser.parent_id').' = '.$this->_db->quoteName('eset.id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_group_id','eserg').' ON '.$this->_db->quoteName('eserg.parent_id').' = '.$this->_db->quoteName('eset.id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','eseru').' ON '.$this->_db->quoteName('eseru.parent_id').' = '.$this->_db->quoteName('eset.id'))
            ->leftJoin($this->_db->quoteName('#__emundus_email_templates','et').' ON '.$this->_db->quoteName('et.id').' = '.$this->_db->quoteName('ese.email_tmpl'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_tags','ert').' ON '.$this->_db->quoteName('ert.parent_id').' = '.$this->_db->quoteName('eset.email_id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment','erca').' ON '.$this->_db->quoteName('erca.parent_id').' = '.$this->_db->quoteName('eset.email_id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment','erla').' ON '.$this->_db->quoteName('erla.parent_id').' = '.$this->_db->quoteName('eset.email_id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers','err1').' ON '.$this->_db->quoteName('err1.parent_id').' = '.$this->_db->quoteName('eset.email_id').' AND '.$this->_db->quoteName('err1.type').' = '.$this->_db->quote('receiver_cc_email'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers','err2').' ON '.$this->_db->quoteName('err2.parent_id').' = '.$this->_db->quoteName('eset.email_id').' AND '.$this->_db->quoteName('err2.type').' = '.$this->_db->quote('receiver_bcc_email'))
            ->where($this->_db->quoteName('eset.step').' = '.$this->_db->quote($step))
            ->andWhere($this->_db->quoteName('eset.to_applicant').' IN ('.$to_applicant .')');
        if(!is_null($to_current_user)) {
            $query->andWhere($this->_db->quoteName('eset.to_current_user') . ' IN (' . $to_current_user . ')');
        }
        $query->andWhere($this->_db->quoteName('esp.code').' IN ('.implode(',', $this->_db->quote($code)) .')')
            ->group('eset.id');
        try {
            $this->_db->setQuery($query);
            $results = $this->_db->loadObjectList();
        }
        catch (Exception $e) {
            JLog::add('Error when get emails triggers with query : ' . $query->__toString(), JLog::ERROR, 'com_emundus');
        }

        $triggers = array_filter($results, function($obj){
            if (empty($obj->trigger_id)) { return false; }
            return true;
        });

        $emails_tmpl = array();

        if (!empty($triggers) && !empty($triggers[0]->id)) {
            foreach ($triggers as $trigger) {
                // email tmpl
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['subject'] = $trigger->subject;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['emailfrom'] = $trigger->emailfrom;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['message'] = $trigger->message;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['name'] = $trigger->name;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['tags'] = $trigger->tags;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['attachments'] = $trigger->attachments;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['letter_attachment'] = $trigger->letter_attachment;

                // This is the email template model, the HTML structure that makes the email look good.
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['template'] = $trigger->Template;

                // default recipients
                if (isset($trigger->profile_id) && !empty($trigger->profile_id)) {
                    $emails_tmpl[$trigger->id][$trigger->code]['to']['profile'][] = $trigger->profile_id;
                }

                if (isset($trigger->group_id) && !empty($trigger->group_id)) {
                    $emails_tmpl[$trigger->id][$trigger->code]['to']['group'][] = $trigger->group_id;
                }

                if (isset($trigger->user_id) && !empty($trigger->user_id)) {
                    $emails_tmpl[$trigger->id][$trigger->code]['to']['user'][] = $trigger->user_id;
                }

                $emails_tmpl[$trigger->id][$trigger->code]['to']['to_applicant'] = $trigger->to_applicant;
                $emails_tmpl[$trigger->id][$trigger->code]['to']['to_current_user'] = $trigger->to_current_user;
                $emails_tmpl[$trigger->id][$trigger->code]['to']['cc'] = $trigger->cc;
                $emails_tmpl[$trigger->id][$trigger->code]['to']['bcc'] = $trigger->bcc;
            }

            // generate list of default recipient email + name
            foreach ($emails_tmpl as $key => $codes) {
                $trigger_id = $key;

                foreach ($codes as $key => $tmpl) {
                    $code = $key;
                    $recipients = array();
                    $as_where = false;
                    $where = '';

                    if (isset($tmpl['to']['profile'])) {
                        if (count($tmpl['to']['profile']) > 0) {
                            $where = ' eu.profile IN ('.implode(',', $tmpl['to']['profile']).')';
                            $as_where = true;
                        }
                    }

                    if (isset($tmpl['to']['group'])) {
                        if (count($tmpl['to']['group']) > 0) {
                            $where .= $as_where?' OR ':'';
                            $where .= ' eg.group_id IN ('.implode(',', $tmpl['to']['group']).')';
                            $as_where = true;
                        }
                    }

                    if (isset($tmpl['to']['user'])) {
                        if (count(@$tmpl['to']['user']) > 0) {
                            $where .= $as_where?' OR ':'';
                            $where .= 'u.block=0 AND u.id IN ('.implode(',', $tmpl['to']['user']).')';
                            $as_where = true;
                        }
                    }

                    if ($as_where) {
                        $query = 'SELECT u.id, u.name, u.email, eu.university_id
                                    FROM #__users as u
                                    LEFT JOIN #__emundus_users as eu on eu.user_id=u.id
                                    LEFT JOIN #__emundus_groups as eg on eg.user_id=u.id
                                    WHERE '.$where.'
                                    GROUP BY u.id';
                        $this->_db->setQuery( $query );
                        $users = $this->_db->loadObjectList();

                        foreach ($users as $user) {
                            $recipients[$user->id] = array('id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'university_id' => $user->university_id);
                        }
                    }

                    if ($tmpl['to']['to_current_user'] == 1) {
                        $current_user = JFactory::getSession()->get('emundusUser');
                        if(empty($current_user) && !empty($student)){
                            $current_user = $student;
                        }
                        $recipients[$current_user->id] = array('id' => $current_user->id, 'name' => $current_user->name, 'email' => $current_user->email, 'university_id' => $current_user->university_id);
                    }

                    $emails_tmpl[$trigger_id][$code]['to']['recipients'] = $recipients;
                }

            }
        }

        return $emails_tmpl;
    }

    /**
     * Send email triggered for Status
     *
     * @param   $step           int The status of application
     * @param   $code           array of programme code
     * @param   $to_applicant   int define if trigger concern selected fnum or not
     * @param   $student        Object Joomla user
     *
     * @return  bool           Emails templates and recipient to trigger
     *
     * @since version v6
     * @throws Exception
     */
    public function sendEmailTrigger($step, $code, $to_applicant = 0, $student = null, $to_current_user = null) {
        $app = JFactory::getApplication();
        $config = JFactory::getConfig();
        $email_from_sys = $config->get('mailfrom');

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.email.php'), JLog::ALL, array('com_emundus'));

        $trigger_emails = $this->getEmailTrigger($step, $code, $to_applicant, $to_current_user, $student);

        if (count($trigger_emails) > 0) {
            // get current applicant course
            include_once(JPATH_SITE.'/components/com_emundus/models/campaign.php');
            $m_campaign = new EmundusModelCampaign;
            $campaign = $m_campaign->getCampaignByID($student->campaign_id);
            $post = array(
                'APPLICANT_ID' => $student->id,
                'DEADLINE' => JHTML::_('date', $campaign['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
                'APPLICANTS_LIST' => '',
                'EVAL_CRITERIAS' => '',
                'EVAL_PERIOD' => '',
                'CAMPAIGN_LABEL' => $campaign['label'],
                'CAMPAIGN_YEAR' => $campaign['year'],
                'CAMPAIGN_START' => JHTML::_('date', $campaign['start_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
                'CAMPAIGN_END' => JHTML::_('date', $campaign['end_date'], JText::_('DATE_FORMAT_OFFSET1'), null),
                'CAMPAIGN_CODE' => $campaign['training'],
                'FNUM' => $student->fnum,
                'COURSE_NAME' => $campaign['label']
            );

            require_once(JPATH_ROOT . '/components/com_emundus/helpers/emails.php');
            $h_emails = new EmundusHelperEmails();

            foreach ($trigger_emails as $trigger_email_id => $trigger_email) {

                foreach ($trigger_email[$student->code]['to']['recipients'] as $recipient) {
                    if (!$h_emails->assertCanSendMailToUser($recipient['id'])) {
                        continue;
                    }

                    $mailer = JFactory::getMailer();

                    $tags = $this->setTags($student->id, $post, $student->fnum, '', $trigger_email[$student->code]['tmpl']['emailfrom'].$trigger_email[$student->code]['tmpl']['name'].$trigger_email[$student->code]['tmpl']['subject'].$trigger_email[$student->code]['tmpl']['message']);

                    $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
                    $from_id = JFactory::getUser()->id;
                    $from_id = empty($from_id) ? 62 : $from_id;
                    $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['name']);
                    $to = $recipient['email'];
                    $to_id = $recipient['id'];
                    $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['subject']);

                    $body = $trigger_email[$student->code]['tmpl']['message'];
                    if ($trigger_email[$student->code]['tmpl']['template']) {
                        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $trigger_email[$student->code]['tmpl']['template']);
                    }
                    $body = preg_replace($tags['patterns'], $tags['replacements'], $body);
                    $body = $this->setTagsFabrik($body, array($student->fnum));


                    $mail_from_address = $email_from_sys;

                    // Set sender
                    $sender = [
                        $mail_from_address,
                        $fromname
                    ];

                    $toAttach= [];
                    if(!empty($trigger_email[$student->code]['tmpl']['letter_attachment'])){
                        include_once(JPATH_SITE . '/components/com_emundus/models/evaluation.php');
                        $m_eval = new EmundusModelEvaluation();
                        $letters = $m_eval->generateLetters($student->fnum, explode(',', $trigger_email[$student->code]['tmpl']['letter_attachment']), 1, 0, 0);

                        foreach($letters->files as $filename){
                            if(!empty($filename['filename'])) {
                                $toAttach[] = EMUNDUS_PATH_ABS . $student->id . '/' . $filename['filename'];
                            }
                        }
                    }
                    if(!empty($trigger_email[$student->code]['tmpl']['attachments'])){
                        require_once (JPATH_SITE . '/components/com_emundus/models/application.php');
                        $m_application = new EmundusModelApplication();
                        $attachments = $m_application->getAttachmentsByFnum($student->fnum,null, explode(',', $trigger_email[$student->code]['tmpl']['attachments']));

                        foreach ($attachments as $attachment) {
                            if(!empty($attachment->filename)) {
                                $toAttach[] = EMUNDUS_PATH_ABS . $student->id . '/' . $attachment->filename;
                            }
                        }
                    }

                    $mailer->setSender($sender);
                    $mailer->addReplyTo($from, $fromname);
                    $mailer->addRecipient($to);
                    $mailer->addAttachment($toAttach);
                    $mailer->setSubject($subject);
                    $mailer->isHTML(true);
                    $mailer->Encoding = 'base64';
                    $mailer->setBody($body);

                    try {
                        $send = $mailer->Send();
                    } catch (Exception $e) {
                        JLog::add('eMundus Triggers - PHP Mailer send failed ' . $e->getMessage(), JLog::ERROR, 'com_emundus.email');
                    }

                    if ($send !== true) {
                        echo 'Error sending email: ' . $send;
                        JLog::add($send, JLog::ERROR, 'com_emundus');
                    } else {
                        $message = array(
                            'user_id_from' => $from_id,
                            'user_id_to' => $to_id,
                            'subject' => $subject,
                            'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body,
                            'email_id' => $trigger_email_id
                        );
                        $this->logEmail($message, $student->fnum);
                    }
                }
            }
        }
        return true;
    }

    /**
     * @description    replace body message tags [constant] by value
     *
     * @param          $user           Object      user object
     * @param          $str            String      string with tags
     * @param          $passwd         String      user password
     *
     * @return string $strval         String      str with tags replace by value
     * @since version v6
     */
    public function setBody($user, $str, $passwd = '') {
        $constants = $this->setConstants($user->id, null, $passwd);
        return html_entity_decode(preg_replace($constants['patterns'], $constants['replacements'], $str), ENT_QUOTES);
    }

    public function replace($replacement, $str) {
        return preg_replace($replacement['patterns'], $replacement['replacements'], $str);
    }

    /**
     *  @description    Replace all accented characters by something else
     *  @param          $str              string
     *  @return         string            String with accents stripped
     * @since version v6
     */
    public function stripAccents($str) {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        return strtr($str, $unwanted_array);
    }

    /**
     *  @description    get tags with Fabrik elementd IDs
     *  @param          $body           string
     *  @return         array           array of application file elements IDs
     *  @since version v6
     */
    public function getFabrikElementIDs($body) {
        preg_match_all('/\{(.*?)\}/', $body, $element_ids);
        return $element_ids;
    }

    /**
     *  @description    replace tags like {fabrik_element_id} by the application form value for current application file
     *  @param          $fnum           string  application file number
     *  @param          $element_ids    array   Fabrik element ID
     *  @return         array           array of application file elements values
     *  @since version v6
     */
    public function getFabrikElementValues($fnum, $element_ids) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        $db = JFactory::getDBO();

        $element_details = @EmundusHelperList::getElementsDetailsByID('"'.implode('","', $element_ids).'"');

        $element_values = array();
        foreach ($element_details as $value) {
            $query = 'SELECT '.$value->element_name.' FROM '.$value->tab_name.' WHERE fnum like '.$db->Quote($fnum);
            $db->setQuery($query);
            $element_values[$value->element_id] = $db->loadResult();
        }

        return $element_values;
    }

    /**
     *  @description    replace tags like {fabrik_element_id} by the applicaiton form value in text
     *  @param          $body               string  source containing tags like {fabrik_element_id}
     *  @param          $element_values     array   Array of values index by Fabrik elements IDs
     *  @return         string              String with values
     * @since version v6
     */
    public function setElementValues($body, $element_values) {
        foreach ($element_values as $key => $value) {
            $body = str_replace('{'.$key.'}', $value, $body);
        }

        return $body;
    }

    /**
     * @param $user_id
     * @param $post
     * @param $passwd
     *
     * @return array
     *
     * @throws Exception
     * @since version v6
     */
    public function setConstants($user_id, $post=null, $passwd='', $fnum=null) {
        $app            = JFactory::getApplication();
        $current_user   = JFactory::getUser();
        if(!empty($current_user)) {
            $user = $current_user->id == $user_id ? $current_user : JFactory::getUser($user_id);
        } else {
            $user = JFactory::getUser($user_id);
        }
        $config         = JFactory::getConfig();

        //get logo
        $template   = $app->getTemplate(true);
        $params     = $template->params;
        $sitename   = $config->get('sitename');

        $base_url = JURI::base();
        if($app->isClient('administrator'))
        {
            $base_url = JURI::root();
        }

        if (!empty($params->get('logo')->custom->image)) {
            $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
            $logo = !empty($logo['path']) ? $base_url.$logo['path'] : "";

        } else {
            $logo_module = JModuleHelper::getModuleById('90');

            if(empty($logo_module->content)) {
                $logo = JURI::root().'images/custom/logo_custom.png';
                if(!file_exists($logo)) {
                    $logo = JURI::root().'images/custom/logo.png';
                }
            } else
            {
                preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
                $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

                if ((bool) preg_match($pattern, $tab[1]))
                {
                    $tab[1] = parse_url($tab[1], PHP_URL_PATH);
                }

                $logo = $base_url . $tab[1];
            }
        }

        $activation = $user->get('activation');

        $patterns = array(
            '/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[SENDER_MAIL\]/', '/\[USERNAME\]/', '/\[USER_ID\]/', '/\[USER_NAME\]/', '/\[USER_EMAIL\]/', '/\n/', '/\[USER_USERNAME\]/', '/\[PASSWORD\]/',
            '/\[ACTIVATION_URL\]/', '/\[ACTIVATION_URL_RELATIVE\]/' ,'/\[SITE_URL\]/' ,'/\[SITE_NAME\]/',
            '/\[APPLICANT_ID\]/', '/\[APPLICANT_NAME\]/', '/\[APPLICANT_EMAIL\]/', '/\[APPLICANT_USERNAME\]/', '/\[CURRENT_DATE\]/', '/\[LOGO\]/'
        );
        $replacements = array(
            $user->id, $user->name, $user->email, $current_user->email, $user->username, $current_user->id, $current_user->name, $current_user->email, ' ', $current_user->username, $passwd,
            $base_url."index.php?option=com_users&task=registration.activate&token=".$activation, "index.php?option=com_users&task=registration.activate&token=".$activation, $base_url, $sitename,
            $user->id, $user->name, $user->email, $user->username, JFactory::getDate('now')->format(JText::_('DATE_FORMAT_LC3')), $logo
        );

        if(!empty($fnum)){
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles();
            $status = $m_files->getStatusByFnums([$fnum]);

            $patterns[] = '/\[APPLICATION_STATUS\]/';
            $replacements[] = $status[$fnum]['value'];

            $tags = $m_files->getTagsByFnum([$fnum]);
            $tags_label = [];
            foreach ($tags as $tag){
                $tags_label[] = $tag['label'];
            }
            $patterns[] = '/\[APPLICATION_TAGS\]/';
            $replacements[] = implode(',', $tags_label);
        }

        if(isset($post)) {
            foreach ($post as $key => $value) {
                $constant_key = array_search('/\['.$key.'\]/', $patterns);
                if ($constant_key !== false) {
                    $replacements[$constant_key] = $value;
                } else {
                    $patterns[] = '/\['.$key.'\]/';
                    $replacements[] = $value;
                }
            }
        }

        return array('patterns' => $patterns , 'replacements' => $replacements);
    }

    /**
     * Define replacement values for tags
     *
     * @param int $user_id
     * @param array $post custom tags define from context
     * @param string $fnum used to get fabrik tags ids from applicant file
     * @param string $passwd used set password if needed
     * @param string $content string containing tags to replace, ATTENTION : if empty all tags are computing
     * @return array[]
     */
    public function setTags($user_id, $post=null, $fnum=null, $passwd='', $content='',$base64 = false) {
        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'helpers'.DS.'tags.php');
        $h_tags = new EmundusHelperTags();

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select('tag, request')
            ->from($db->quoteName('#__emundus_setup_tags', 't'))
            ->where($db->quoteName('t.published') . ' = 1');

        if (!empty($content)) {
            $tags_content = $h_tags->getVariables($content, 'SQUARE');

            if( !empty($tags_content) ) {
                $tags_content = array_unique($tags_content);
                $query->andWhere('t.tag IN ("' . implode('","', $tags_content) .'")');
            }
        }

        try {
            $db->setQuery($query);
            $tags = $db->loadAssocList();
        } catch(Exception $e) {
            JLog::add('Error getting tags model/emails/setTags at query : '.$query->__toString(), JLog::ERROR, 'com_emundus.email');
            return array('patterns' => array() , 'replacements' => array());
        }

        $constants = $this->setConstants($user_id, $post, $passwd, $fnum);

        $patterns = $constants['patterns'];
        $replacements = $constants['replacements'];

        foreach ($tags as $tag) {

            $patterns[] = '/\['.$tag['tag'].'\]/';
            $value = preg_replace($constants['patterns'], $constants['replacements'], $tag['request']);

            if (strpos( $value, 'php|' ) === false) {
                $request = explode('|', $value);

                if (count($request) > 1) {

                    try {

                        $query = 'SELECT '.$request[0].' FROM '.$request[1].' WHERE '.$request[2];
                        $db->setQuery($query);
                        $result = $db->loadResult();

                    } catch (Exception $e) {

                        $error = JUri::getInstance().' :: USER ID : '.$user_id.'\n -> '.$query;
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                        $result = "";

                    }
                    if ($tag['tag'] == 'PHOTO') {
                        if (empty($result)) {
                            $result = 'media/com_emundus/images/icones/personal.png';
                        } else {
                            if(file_exists(EMUNDUS_PATH_REL.$user_id.'/tn_'.$result)) {
                                $result = EMUNDUS_PATH_REL.$user_id.'/tn_'.$result;
                            } else {
                                $result = EMUNDUS_PATH_REL.$user_id.'/'.$result;
                            }

                            if($base64) {
                                $type = pathinfo($result, PATHINFO_EXTENSION);
                                $data = file_get_contents($result);
                                $result = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            }
                        }
                    }
                    $replacements[] = $result;
                } else {
                    $replacements[] = $request[0];
                }
            } elseif (!empty($fnum)) {
                $request = str_replace('php|', '', $value);
                $val = $this->setTagsFabrik($request, array($fnum));

                $result = "";
                try {

                    $result = eval("$val");
                } catch (Exception $e) {
                    JLog::add('Error setTags for tag : ' .  $tag['tag'] . '. Message : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    $result = "";
                }

                if (!empty($result)) {
                    $replacements[] = $result;
                } else {
                    $replacements[] = "";
                }
            } else {
                $request = str_replace('php|', '', $value);

                try {
                    $result = eval("$request");
                } catch (Exception $e) {
                    JLog::add('Error setTags for tag : ' .  $tag['tag'] . '. Message : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    $result = "";
                }

                if (!empty($result)){
                    $replacements[] = $result;
                } else {
                    $replacements[] = "";
                }
            }

        }

        return array('patterns' => $patterns , 'replacements' => $replacements);
    }


    /**
     * @param $user_id
     * @param $post
     * @param $fnum
     * @param $passwd
     *
     * @return array[]
     *
     * @throws Exception
     * @since version v6
     */
    public function setTagsWord($user_id, $post=null, $fnum=null, $passwd='') {
        $db = JFactory::getDBO();

        $query = "SELECT tag, request FROM #__emundus_setup_tags";
        $db->setQuery($query);
        $tags = $db->loadAssocList();

        $constants = $this->setConstants($user_id, $post, $passwd);

        $patterns = array();
        $replacements = array();
        foreach ($tags as $tag) {
            $patterns[] = $tag['tag'];
            $value = preg_replace($constants['patterns'], $constants['replacements'], $tag['request']);
            $value = $this->setTagsFabrik($value, array($fnum));

            if( strpos( $value, 'php|' ) === false ) {
                $request = explode('|', $value);
                if (count($request) > 1) {
                    $query = 'SELECT '.$request[0].' FROM '.$request[1].' WHERE '.$request[2];
                    $db->setQuery($query);
                    $replacements[] = $db->loadResult();

                } else {
                    $replacements[] = $request[0];
                }
            }
            else {
                $request = explode('php|', $value);
                $val = $this->setTagsFabrik($request[1], array($fnum));
                $replacements[] = eval("$val");
            }
        }

        return array('patterns' => $patterns , 'replacements' => $replacements);
    }

    /**
     * @param $str
     * @param $fnums
     *
     * @return array|string|string[]|null
     *
     * @throws Exception
     * @since version v6
     */
    public function setTagsFabrik($str, $fnums = array(), $raw = false) {
        require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
        $m_files = new EmundusModelFiles();

        $jinput = JFactory::getApplication()->input;

        if (count($fnums) == 0) {
            $fnums = $jinput->get('fnums', null, 'RAW');
            $fnumsArray = (array) json_decode(stripslashes($fnums), false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $fnumsArray = $fnums;
        }

        $tags = $m_files->getVariables($str);

        $idFabrik = array();
        if (count($tags) > 0) {

            foreach ($tags as $val) {
                $tag = strip_tags($val);
                if (is_numeric($tag)) {
                    $idFabrik[] = $tag;
                }
            }
        }

        if (!empty($idFabrik)) {
            $fabrikElts = $m_files->getValueFabrikByIds($idFabrik);
            $fabrikValues = array();
            foreach ($fabrikElts as $elt) {

                $params = json_decode($elt['params']);
                $groupParams = json_decode($elt['group_params']);
                $isDate = ($elt['plugin'] == 'date');
                $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');

                if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                    $fabrikValues[$elt['id']] = $m_files->getFabrikValueRepeat($elt, $fnumsArray, $params, @$groupParams->repeat_group_button == 1);


                    if (empty($fabrikValues[$elt['id']])) {
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                    }


                } else {
                    if ($isDate) {
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name'], $params->date_form_format);
                    } else {
                        $fabrikValues[$elt['id']] = $m_files->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                    }
                }

                if ($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown" || $elt['plugin'] == "radiobutton") {
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                        $params = json_decode($elt['params']);
                        $elm = array();
                        if($elt['plugin'] == "checkbox"){
                            $index = array_intersect(json_decode($val["val"]), $params->sub_options->sub_values);
                        } else {
                            $index = array_intersect((array)$val["val"], $params->sub_options->sub_values);
                        }
                        foreach ($index as $value) {
                            $key = array_search($value,$params->sub_options->sub_values);
                            $elm[] = !$raw ? JText::_($params->sub_options->sub_labels[$key]) : $value;
                        }
                        $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $elm);
                    }
                }

                if ($elt['plugin'] == "birthday") {
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                        $val = explode(',', $val['val']);
                        foreach ($val as $k => $v) {
                            $val[$k] = date($params->details_date_format, strtotime($v));
                        }
                        $fabrikValues[$elt['id']][$fnum]['val'] = implode(",", $val);
                    }
                }

                if ($elt['plugin'] == 'cascadingdropdown') {
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                        $fabrikValues[$elt['id']][$fnum]['val'] = $this->getCddLabel($elt, $val['val']);
                    }
                }
                if ($elt['plugin'] == 'textarea') {
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                        $fabrikValues[$elt['id']][$fnum]['val'] = htmlentities($val['val'],ENT_QUOTES);
                    }
                }
                if ($elt['plugin'] == 'emundus_phonenumber'){
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val)
                    {
	                    $fabrikValues[$elt['id']][$fnum]['val'] = substr($val['val'], 2, strlen($val['val']));
                    }
                }
            }
            $preg = array('patterns' => array(), 'replacements' => array());
            foreach ($fnumsArray as $fnum) {
                foreach ($idFabrik as $id) {
                    $preg['patterns'][] = '/\$\{(.*?)'.$id.'(.*?)}/i';
                    if (isset($fabrikValues[$id][$fnum])) {
                        $preg['replacements'][] = JText::_($fabrikValues[$id][$fnum]['val']);
                    } else {
                        $preg['replacements'][] = '';
                    }
                }
            }

            return $this->replace($preg, $str);
        } else {
            return $str;
        }
    }


    /**
     * Gets the label of a CascadingDropdown element based on the value.
     *
     * @param $elt array the cascadingdropdown element.
     * @param $val string the value of the element to be used for retrieving the label.
     *
     * @return mixed|string
     * @since version v6
     */
    private function getCddLabel($elt, $val) {
        $attribs = json_decode($elt['params']);
        $id = $attribs->cascadingdropdown_id;
        $r1 = explode('___', $id);
        $label = $attribs->cascadingdropdown_label;
        $r2 = explode('___', $label);
        $select = !empty($attribs->cascadingdropdown_label_concat)?str_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0 , 2), str_replace('{thistable}',$r2[0],"CONCAT(".$attribs->cascadingdropdown_label_concat.")")):$r2[1];

        $query = $this->_db->getQuery(true);
        $query->select($select)
            ->from($this->_db->quoteName($r2[0]))
            ->where($this->_db->quoteName($r1[1]).' LIKE '.$this->_db->quote($val));
        $this->_db->setQuery($query);

        try {
            $ret = $this->_db->loadResult();
            if (empty($ret)) {
                return $val;
            }
            return $ret;
        } catch (Exception $e) {
            JLog::add('Error getting cascadingdropdown label in model/emails/getCddLabel at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return $val;
        }
    }

    /**
     * Find all variables like ${var} in string.
     *
     * @param string $str
     * @return string[]
     * @since v6
     */
    private function getVariables($str)
    {
        preg_match_all('/\$\{(.*?)}/i', $str, $matches);

        return $matches[1];
    }


    /**
     * @param $type
     * @param $fnum
     *
     * @return array|bool
     *
     * @throws Exception
     * @since version v6
     */
    public function sendMail($type = null, $fnum = null) {

        $jinput = JFactory::getApplication()->input;
        $mail_type = $jinput->get('mail_type', null, 'CMD');

        if ($fnum != null) {
            require_once (JPATH_ROOT . '/components/com_emundus/models/files.php');
            $m_files = new EmundusModelFiles();
            $fnum_infos = $m_files->getFnumInfos($fnum);

            $student_id = $fnum_infos['applicant_id'];
            $campaign_id = $fnum_infos['campaign_id'];
        } else {
            $student_id = $jinput->getInt('student_id', null);
            $campaign_id = $jinput->getInt('campaign_id', null);
        }

        $student = JFactory::getUser($student_id);

        if (!isset($type)) {
            $type = $mail_type;
        }

        if ($type == "evaluation_result") {

            $mode = 1; // HTML

            $mail_cc = null;
            $mail_subject = $jinput->get('mail_subject', null, 'STRING');

            $mail_from_name = $this->_em_user->name;
            $mail_from = $this->_em_user->email;

            $mail_to_id = $jinput->get('mail_to', null, 'STRING');
            $student = JFactory::getUser($mail_to_id);
            $mail_to = $student->email;

            $mail_body = $this->setBody($student, JRequest::getVar('mail_body', null, 'POST', 'VARCHAR', JREQUEST_ALLOWHTML), '');
            $mail_attachments = $jinput->get('mail_attachments', null, 'STRING');

            if (!empty($mail_attachments)) {
                $mail_attachments = explode(',', $mail_attachments);
            }

            $message = [
                'user_id_from'  => $this->_em_user->id,
                'user_id_to'    => $mail_to_id,
                'subject'       => $mail_subject,
                'message'       => $mail_body
            ];
            $this->logEmail($message);

        } elseif ($type == 'expert') {

            require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
            include_once(JPATH_ROOT . '/components/com_emundus/models/application.php');
            $eMConfig   = JComponentHelper::getParams('com_emundus');
            $formid     = json_decode($eMConfig->get('expert_fabrikformid', '{"accepted":169, "refused":328}'));
            $documentid = $eMConfig->get('expert_document_id', '36');

            $mail_cc        = null;
            $mail_subject   = $jinput->get('mail_subject', null, 'STRING');

            $mail_from_name = $jinput->get('mail_from_name', null, 'STRING');
            $mail_from      = $jinput->get('mail_from', null, 'STRING');
			$mail_to		= $jinput->get('mail_to', null, 'STRING');
			$mail_body 		= $jinput->get('mail_body', null, 'RAW');
            $tags = $this->setTags($this->_em_user->id, null, null, '', $mail_from_name.$mail_from.$mail_to);
            $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);
            $mail_from      = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
            $mail_to = explode(',', $mail_to);
            $mail_body = $this->setBody($student, $mail_body);

            //
            // Replacement
            //
            $campaign = @EmundusHelperfilters::getCampaignByID($campaign_id);
            $post = [
                'TRAINING_PROGRAMME'    => $campaign['label'],
                'CAMPAIGN_START'        => $campaign['start_date'],
                'CAMPAIGN_END'          => $campaign['end_date'],
                'EVAL_DEADLINE'         => date("d/M/Y", mktime(0, 0, 0, date("m")+2, date("d"), date("Y")))
            ];
            $tags = $this->setTags($student_id, $post, $fnum, '', $mail_body);
            $mail_body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);

            //tags from Fabrik ID
            $element_ids = $this->getFabrikElementIDs($mail_body);
            if (count(@$element_ids[0]) > 0) {
                $element_values     = $this->getFabrikElementValues($fnum, $element_ids[1]);
            }

            $mail_attachments = $jinput->get('mail_attachments', null, 'STRING');
            $delete_attachment = $jinput->get('delete_attachment', null, 'INT');

            if (!empty($mail_attachments)) {
                $mail_attachments = explode(',', $mail_attachments);
            }

            $sent = array();
            $failed = array();
            $print_message = '';

            $query = $this->_db->getQuery(true);
            foreach ($mail_to as $m_to) {

                $key1 = md5($this->rand_string(20).time());
                $m_to = trim($m_to);

                // 2. MAJ de la table emundus_files_request
                $attachment_id = $documentid; // document avec clause de confidentialité
                $query->clear();
                $query->insert('#__emundus_files_request')
                    ->columns('time_date, student_id, keyid, attachment_id, campaign_id, email, fnum')
                    ->values( $this->_db->quote(gmdate('Y-m-d H:i:s')) . ', '.$student_id.', "'.$key1.'", "'.$attachment_id.'", '.$campaign_id.', '.$this->_db->quote($m_to).', '.$this->_db->quote($fnum));

                try {
                    $this->_db->setQuery($query);
                    $this->_db->query();
                } catch (Exception $e) {
                   JLog::add('Error trying to insert emundus files request (fnum ' . $fnum . ') : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                }

                // 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
                $link_accept = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->accepted.'&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id;
                $link_refuse = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->refused.'&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id.'&usekey=keyid&rowid='.$key1;
                $link_accept_noform = 'index.php?option=com_fabrik&c=form&view=form&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id;
                $link_refuse_noform = 'index.php?option=com_fabrik&c=form&view=form&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id.'&usekey=keyid&rowid='.$key1;

                $post = array(
                    'EXPERT_ACCEPT_LINK'    => JURI::base().$link_accept,
                    'EXPERT_REFUSE_LINK'    => JURI::base().$link_refuse,
                    'EXPERT_ACCEPT_LINK_RELATIVE'    => $link_accept,
                    'EXPERT_REFUSE_LINK_RELATIVE'    => $link_refuse,
                    'EXPERT_ACCEPT_LINK_NOFORM'    => JURI::base().$link_accept_noform,
                    'EXPERT_REFUSE_LINK_NOFORM'    => JURI::base().$link_refuse_noform,
                    'EXPERT_ACCEPT_LINK_RELATIVE_NOFORM'    => $link_accept_noform,
                    'EXPERT_REFUSE_LINK_RELATIVE_NOFORM'    => $link_refuse_noform
                );

                $tags = $this->setTags($student_id, $post, $fnum);

                $body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);
                $body = $this->setTagsFabrik($body, array($fnum));

                // If we have an email sender in our jinput then we use that, else we use the default system sender.
                $app = JFactory::getApplication();
                $email_from_sys = $app->getCfg('mailfrom');

                // If the email sender has the same domain as the system sender address.
                if (!empty($mail_from) && substr(strrchr($mail_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                    $mail_from_address = $mail_from;
                } else {
                    $mail_from_address = $email_from_sys;
                }

                // Set sender
                $sender = [
                    $mail_from_address,
                    $mail_from_name
                ];

                $mailer = JFactory::getMailer();
                $mailer->setSender($sender);
                $mailer->addReplyTo($mail_from, $mail_from_name);
                $mailer->addRecipient($m_to);
                $mailer->setSubject($mail_subject);
                $mailer->isHTML(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);
                if (is_array($mail_attachments) && count($mail_attachments) > 0) {
                    foreach ($mail_attachments as $attachment) {
                        $mailer->addAttachment($attachment);
                    }
                }

                $send = $mailer->Send();

                if ($send !== true) {
                    $failed[] = $m_to;
                    $row = [
                        'applicant_id'  => $student_id,
                        'user_id'       => $this->_em_user->id,
                        'reason'        => JText::_( 'COM_EMUNDUS_EXPERTS_INFORM_EXPERTS' ),
                        'comment_body'  => JText::_('ERROR').' '.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_NOT_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$m_to,
                        'fnum'          => $fnum
                    ];
                    $print_message .= '<hr>Error sending email: ' . $send->__toString();

                } else {
                    $sent[] = $m_to;
                    $row = [
                        'applicant_id'  => $student_id,
                        'user_id'       => $this->_em_user->id,
                        'reason'        => JText::_( 'COM_EMUNDUS_EXPERTS_INFORM_EXPERTS' ),
                        'comment_body'  => JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$m_to,
                        'fnum'          =>  $fnum
                    ];

                    $query = 'SELECT id FROM #__users WHERE email like '.$this->_db->Quote($m_to);
                    $this->_db->setQuery($query);
                    $user_id_to = $this->_db->loadResult();

                    if ($user_id_to > 0) {
                        $message = [
                            'user_id_from'  => $this->_em_user->id,
                            'user_id_to'    => $user_id_to,
                            'subject'       => $mail_subject,
                            'message'       => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$m_to.'</i><br>'.$body
                        ];
                        $this->logEmail($message);
                    }

                    $print_message .= '<hr>'.JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT').' : '.$m_to;
                    $print_message .= '<hr>'.JText::_('COM_EMUNDUS_EMAILS_SUBJECT').' : '.$mail_subject;
                    $print_message .= '<hr>'.$body;
                }

                $m_application = new EmundusModelApplication;
                $m_application->addComment($row);
            }

            // delete attached files
            if (is_array($mail_attachments) && count($mail_attachments) > 0 && $delete_attachment == 1) {
                foreach ($mail_attachments as $attachment) {

                    $filename = explode(DS, $attachment);
                    $query = 'DELETE FROM #__emundus_uploads
                                WHERE user_id='.$student_id.'
                                    AND campaign_id='.$campaign_id. '
                                    AND fnum like '.$this->_db->Quote($fnum).'
                                    AND filename LIKE "'.$filename[count($filename)-1].'"';
                    $this->_db->setQuery($query);
                    $this->_db->query();

                    @unlink(EMUNDUS_PATH_ABS.$student_id.DS.$filename[count($filename)-1]);

                }
            }

            return array('sent' => $sent, 'failed' => $failed, 'message' => $print_message);

        } else {
            return false;
        }

        JFactory::getApplication()->enqueueMessage(JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT'), 'message');
        return true;
    }

    /**
     * Used for sending the expert invitation email with the link to the form.
     * @param $fnums array
     *
     * @return array
     *
     * @throws Exception
     * @since version v6
     */
    public function sendExpertMail(array $fnums) : array {
        $sent = [];
        $failed = [];
        $print_message = '';

        if (!empty($fnums)) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

            $h_filters = new EmundusHelperFilters();
            $m_files = new EmundusModelFiles();

            JLog::addLogger(['text_file' => 'com_emundus.inviteExpert.error.php'], JLog::ALL, 'com_emundus');

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $formid = json_decode($eMConfig->get('expert_fabrikformid', '{"accepted":169, "refused":328}'));
            $documentid = $eMConfig->get('expert_document_id', '36');

            $app = JFactory::getApplication();
            $email_from_sys = $app->getCfg('mailfrom');
            $jinput = $app->input;
            $mail_subject = $jinput->post->getString('mail_subject');
            $mail_from_name = $jinput->post->getString('mail_from_name');
            $mail_from = $jinput->post->getRaw('mail_from');

            // We are using the first fnum for things like setting tags and getting campaign info.
            // ! This means that we should NOT PUT TAGS RELATING TO PERSONAL INFO IN THE EMAIL.
            $example_fnum = $fnums[0];
            $campaign_id = (int)substr($example_fnum, 14, 7);
            $campaign = $h_filters->getCampaignByID($campaign_id);
            $example_user_id = (int)substr($example_fnum, -7);
            $example_user = JFactory::getUser($example_user_id);
            $tags = $this->setTags($this->_em_user->id);

            $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);
            $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);

            $mail_to = $jinput->post->getRaw('mail_to');

            $mail_tmpl = $this->getEmail('confirm_post');

            if (!empty($mail_to)) {
                $mail_body = $this->setBody($example_user, $jinput->post->getRaw('mail_body'));

                // Build an HTML list to stick in the email body.
                $fnums_infos = $m_files->getFnumsInfos($fnums);
                $fnums_html = '<ul>';
                foreach ($fnums_infos as $fnum) {
                    $fnums_html .= '<li>'.$fnum['name'].' ('.$fnum['fnum'].')</li>';
                }
                $fnums_html .= '</ul>';

                // Replacement
                $post = [
                    'CAMPAIGN_LABEL'        => $campaign['label'],
                    'TRAINING_PROGRAMME'    => $campaign['label'],
                    'CAMPAIGN_START'        => $campaign['start_date'],
                    'CAMPAIGN_END'          => $campaign['end_date'],
                    'EVAL_DEADLINE'         => date("d/M/Y", mktime(0, 0, 0, date("m")+2, date("d"), date("Y"))),
                    'FNUMS'                 => $fnums_html
                ];
                $tags = $this->setTags($example_user_id, $post, $example_fnum);
                $mail_body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);

                // Tags from Fabrik ID
                $element_ids = $this->getFabrikElementIDs($mail_body);
                if (count(@$element_ids[0]) > 0) {
                    $element_values = $this->getFabrikElementValues($example_fnum, $element_ids[1]);
                }

                $mail_attachments = $jinput->post->getString('mail_attachments');
                $delete_attachment = $jinput->post->getInt('delete_attachment');

                if (!empty($mail_attachments)) {
                    $mail_attachments = explode(',', $mail_attachments);
                }

                $sent = array();
                $failed = array();
                $print_message = '';

                foreach ($mail_to as $m_to) {
                    $key1 = md5($this->rand_string(20).time());
                    $m_to = trim($m_to);


                    // 2. MAJ de la table emundus_files_request
                    $attachment_id = $documentid; // document avec clause de confidentialité

                    // Build multiline insert, 1 key can accept for multiple files.
                    $query = $this->_db->getQuery(true);
                    $query->insert($this->_db->quoteName('#__emundus_files_request'))
                        ->columns($this->_db->quoteName(['time_date', 'student_id', 'keyid', 'attachment_id', 'campaign_id', 'email', 'fnum']));

                    foreach ($fnums_infos as $fnum_info) {
                        $query->values('NOW(), '.$fnum_info['applicant_id'].', "'.$key1.'", "'.$attachment_id.'", '.$fnum_info['campaign_id'].', '.$this->_db->quote($m_to).', '.$this->_db->quote($fnum_info['fnum']));
                    }

                    $this->_db->setQuery($query);
                    try {
                        $this->_db->execute();
                    } catch (Exception $e) {
                        $failed[] = $m_to;
                        $print_message .= '<hr>Error inviting expert '.$m_to;
                        JLog::add('Error inserting file requests for expert invitations ' . $m_to . ' : '.$e->getMessage() . ' with query : ' . $query->__toString(), JLog::ERROR, 'com_emundus');
                        continue;
                    }

                    $this->_db->setQuery('show tables');
                    $existingTables = $this->_db->loadColumn();
                    if (in_array('jos_emundus_files_request_1614_repeat', $existingTables)) {
                        $parent_id = 0;

                        foreach ($fnums_infos as $fnum) {
                            try {
                                $query->clear()
                                    ->select($this->_db->quoteName(['id', 'fnum', 'student_id']))
                                    ->from($this->_db->quoteName('#__emundus_files_request'))
                                    ->where($this->_db->quoteName('email').' LIKE '.$this->_db->Quote($m_to) . ' AND ' . $this->_db->quoteName('fnum').' LIKE '.$this->_db->Quote($fnum['fnum']));
                                $this->_db->setQuery($query);
                                $files_request = $this->_db->loadObject();

                                if(empty($parent_id)){
                                    $parent_id = $files_request->id;
                                }

                                $query->clear()
                                    ->select($this->_db->quoteName('name'))
                                    ->from($this->_db->quoteName('#__users'))
                                    ->where($this->_db->quoteName('id').' = ' . $files_request->student_id);
                                $this->_db->setQuery($query);
                                $student_name = $this->_db->loadResult();

                                $query->clear()
                                    ->insert($this->_db->quoteName('#__emundus_files_request_1614_repeat'))
                                    ->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($parent_id))
                                    ->set($this->_db->quoteName('nom_candidat_expertise') . ' = ' . $this->_db->quote($student_name))
                                    ->set($this->_db->quoteName('fnum_expertise') .'=' . $this->_db->quote($fnum['fnum']));
                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            } catch (Exception $e) {
                                $failed[] = $m_to . '  ' . $fnum['fnum'];
                                $print_message .= '<hr>Error associating expert '.$m_to . ' to fnum ' . $fnum['fnum'];
                                JLog::add('Error inserting file requests for expert invitations ' . $m_to . ' and fnum ' .  $fnum['fnum'] . ' : '.$e->getMessage() . ' with query : ' . $query->__toString(), JLog::ERROR, 'com_emundus');
                                continue;
                            }
                        }
                    }

                    // 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
                    $link_accept = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->accepted.'&keyid='.$key1.'&cid='.$campaign_id;
                    $link_refuse = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->refused.'&keyid='.$key1.'&cid='.$campaign_id.'&usekey=keyid&rowid='.$key1;
                    $link_accept_noform = 'index.php?option=com_fabrik&c=form&view=form&keyid='.$key1.'&sid='.$fnum_info['applicant_id'].'&email='.$m_to.'&cid='.$campaign_id;
                    $link_refuse_noform = 'index.php?option=com_fabrik&c=form&view=form&keyid='.$key1.'&sid='.$fnum_info['applicant_id'].'&email='.$m_to.'&cid='.$campaign_id.'&usekey=keyid&rowid='.$key1;

                    $post = array(
                        'EXPERT_ACCEPT_LINK'    => JURI::base().$link_accept,
                        'EXPERT_REFUSE_LINK'    => JURI::base().$link_refuse,
                        'EXPERT_ACCEPT_LINK_RELATIVE'    => $link_accept,
                        'EXPERT_REFUSE_LINK_RELATIVE'    => $link_refuse,
                        'EXPERT_ACCEPT_LINK_NOFORM'    => JURI::base().$link_accept_noform,
                        'EXPERT_REFUSE_LINK_NOFORM'    => JURI::base().$link_refuse_noform,
                        'EXPERT_ACCEPT_LINK_RELATIVE_NOFORM'    => $link_accept_noform,
                        'EXPERT_REFUSE_LINK_RELATIVE_NOFORM'    => $link_refuse_noform
                    );

                    $tags = $this->setTags($example_user_id, $post, $example_fnum);

                    $message = $this->setTagsFabrik($mail_body, [$example_fnum]);
                    $subject = $this->setTagsFabrik($mail_subject, [$example_fnum]);

                    // Tags are replaced with their corresponding values using the PHP preg_replace function.
                    $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);
                    $body = $message;
                    if ($mail_tmpl) {
                        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $mail_tmpl->Template);
                    }
                    $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

                    // If the email sender has the same domain as the system sender address.
                    if (!empty($mail_from) && substr(strrchr($mail_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                        $mail_from_address = $mail_from;
                    } else {
                        $mail_from_address = $email_from_sys;
                    }

                    // Set sender
                    $sender = [
                        $mail_from_address,
                        $mail_from_name
                    ];

                    $mailer = JFactory::getMailer();
                    $mailer->setSender($sender);
                    $mailer->addReplyTo($mail_from, $mail_from_name);
                    $mailer->addRecipient($m_to);
                    $mailer->setSubject($mail_subject);
                    $mailer->isHTML(true);
                    $mailer->Encoding = 'base64';
                    $mailer->setBody($body);
                    if (is_array($mail_attachments) && count($mail_attachments) > 0) {
                        foreach ($mail_attachments as $attachment) {
                            $mailer->addAttachment($attachment);
                        }
                    }

                    $send = $mailer->Send();
                    if ($send !== true) {
                        $failed[] = $m_to;
                        $print_message .= '<hr>Error sending email: ' . $send;
                    } else {
                        $sent[] = $m_to;

                        $query = $this->_db->getQuery(true);
                        $query->select($this->_db->quoteName('id'))
                            ->from($this->_db->quoteName('#__users'))
                            ->where($this->_db->quoteName('email').' LIKE '.$this->_db->Quote($m_to));
                        $this->_db->setQuery($query);

                        try {
                            $user_id_to = $this->_db->loadResult();

                            if ($user_id_to > 0) {
                                $message = [
                                    'user_id_from'  => $this->_em_user->id,
                                    'user_id_to'    => $user_id_to,
                                    'subject'       => $mail_subject,
                                    'message'       => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$m_to.'</i><br>'.$body
                                ];
                                $this->logEmail($message);
                            }
                        } catch (Exception $e) {
                            JLog::add('Could not get user by email : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                        }

                        $print_message .= '<hr>'.JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT').' : '.$m_to;
                        $print_message .= '<hr>'.JText::_('COM_EMUNDUS_EMAILS_SUBJECT').' : '.$mail_subject;
                        $print_message .= '<hr>'.$body;
                    }
                }
                unset($key1);

                // delete attached files
                if (is_array($mail_attachments) && count($mail_attachments) > 0 && $delete_attachment == 1) {
                    foreach ($mail_attachments as $attachment) {

                        $filename = explode(DS, $attachment);
                        // TODO: Make documents contain some sort of fnum information because deleting by raw filename seems a bit scary.
                        $query = $this->_db->getQuery(true);
                        $query->delete($this->_db->quoteName('#__emundus_uploads'))
                            ->where($this->_db->quoteName('filename').' LIKE '.$this->_db->quote($filename[count($filename)-1]).' AND '.$this->_db->quoteName('user_id').' = '.$filename[count($filename)-2]);
                        $this->_db->setQuery($query);

                        try {
                            $this->_db->execute();
                        } catch (Exception $e) {
                            JLog::add('Could not delete file : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                            continue;
                        }

                        @unlink(EMUNDUS_PATH_ABS.$filename[count($filename)-2].DS.$filename[count($filename)-1]);

                    }
                } else {
                    JLog::add(JFactory::getUser()->id . ' Function sendExpertMail has been called but mail_to has been found empty. fnums => (' . json_encode($fnums) . ')', JLog::WARNING, 'com_emundus');
                    $print_message = JText::_('NO_MAIL_TO_SEND');
                }
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'message' => $print_message
        ];
    }

    /**
     * @param $row
     *
     *
     * @since version v6
     */
    public function logEmail($row, $fnum = null) {
        $logged = false;

        // log email to admin user if user_id_from is empty
        $row['user_id_from'] = !empty($row['user_id_from']) ? $row['user_id_from'] : 62;

        require_once(JPATH_SITE.'/components/com_emundus/helpers/date.php');
        $h_date = new EmundusHelperDate();
        $now = $h_date->getNow();

        $query = $this->_db->getQuery(true);

        $columns = ['user_id_from', 'user_id_to', 'date_time', 'subject', 'message', 'email_cc'];
        $values = [$row['user_id_from'], $row['user_id_to'], $this->_db->quote($now), $this->_db->quote($row['subject']), $this->_db->quote($row['message']), $this->_db->quote($row['email_cc'])];

        // If we are logging the email type as well, this allows us to put them in separate folders.
        if (isset($row['type']) && !empty($row['type'])) {
            $columns[] = 'folder_id';
            $values[] = $row['type'];
        }

        $query->insert($this->_db->quoteName('#__messages'))
            ->columns($this->_db->quoteName($columns))
            ->values(implode(',',$values));

        try {
            $this->_db->setQuery($query);
            $logged = $this->_db->execute();

            if ($logged && !empty($fnum)) {
                $message_id = $this->_db->insertid();

                // check user_id_to is the applicant user id, before logging in file
                $query->clear()
                    ->select($this->_db->quoteName('applicant_id'))
                    ->from($this->_db->quoteName('#__emundus_campaign_candidature'))
                    ->where($this->_db->quoteName('fnum').' LIKE '.$this->_db->quote($fnum));

                $this->_db->setQuery($query);
                $applicant_id = $this->_db->loadResult();
                if ($applicant_id == $row['user_id_to']) {
                    $email_id = isset($row['email_id']) ? $row['email_id'] : 0;

                    include_once (JPATH_ROOT . '/components/com_emundus/models/logs.php');
                    if (class_exists('EmundusModelLogs')) {
                        $m_logs = new EmundusModelLogs();
                        $m_logs->log($row['user_id_from'], $row['user_id_to'], $fnum, 9, 'c', 'COM_EMUNDUS_LOGS_EMAIL_SENT', json_encode(['email_id' => $email_id, 'message_id' => $message_id, 'created' => [$row['subject']]], JSON_UNESCAPED_UNICODE));
                    }
                }
            }
        } catch (Exception $e) {
            JLog::add('Error logging email in model/emails : '.preg_replace("/[\r\n]/"," ",$query->__toString()) .  ' data : ' . json_encode($row) , JLog::ERROR, 'com_emundus.email.error');
        }

        return $logged;
    }

    //////////////////////////  SET FILES REQUEST  /////////////////////////////
    //
    // Génération de l'id du prochain fichier qui devra être ajouté par le referent

    // 1. Génération aléatoire de l'ID
    public function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
        $string = '';
        for ($i = 0; $i < $len; $i++) {
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars[$pos];
        }
        return $string;
    }

    /**
     * Gets all emails sent to or from the User id.
     * @param Int user ID
     * @return Mixed Array
     * @since v6
     */
    public function get_messages_to_from_user($user_id) {
        $messages = [];

        if (!empty($user_id)) {
            $query = $this->_db->getQuery(true);
            $query->select('*')
                ->from($this->_db->quoteName('#__messages'))
                ->where($this->_db->quoteName('user_id_to').' = '.$user_id.' AND '.$this->_db->quoteName('folder_id').' <> 2')
                ->order($this->_db->quoteName('date_time').' DESC');

            try {
                $this->_db->setquery($query);
                $messages = $this->_db->loadObjectList();
            } catch (Exception $e) {
                JLog::add('Error getting messages sent to or from user: '.$user_id.' at query: '.$query, JLog::ERROR, 'com_emundus.error');
                return false;
            }
        }

        return $messages;
    }

    /**
     * @param int $email
     * @param array $groups
     * @param array $attachments
     * @return bool
     * @since v6
     */
    public function sendEmailToGroup(int $email, array $groups, array $attachments = []) : bool {

        if (empty($email) || empty($groups)) {
            JLog::add('No user or group found in sendEmailToGroup function: ', JLog::ERROR, 'com_emundus');
            return false;
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
        $m_messages = new EmundusModelMessages();
        $template = $m_messages->getEmail($email);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'groups.php');
        $m_groups = new EmundusModelGroups();
        $users = $m_groups->getUsersByGroups($groups);

        foreach ($users as $user) {
            try {
                $this->sendEmailFromPlatform($user["user_id"], $template, $attachments);
            } catch (Exception $e) {
                JLog::add('Error sending an email via the platform: ', JLog::ERROR, 'com_emundus');
                return false;
            }
        }
        JLog::add(sizeof($users) .' emails sent to the following groups: ' . implode(",", $groups), JLog::ERROR, 'com_emundus');
        return true;
    }

    /**
     * @param int $user
     * @param object $template
     * @param array $attachments
     * @return void
     * @since v6
     */
    public function sendEmailFromPlatform(int $user, object $template, array $attachments) : void {
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $current_user = JFactory::getUser();
        $user = JFactory::getUser($user);
        $toAttach = [];

        require_once(JPATH_ROOT . '/components/com_emundus/helpers/emails.php');
        $h_emails = new EmundusHelperEmails();

        if ($h_emails->assertCanSendMailToUser($user->id)) {
            // Tags are replaced with their corresponding values using the PHP preg_replace function.
            $tags = $this->setTags($user->id);

            $subject = preg_replace($tags['patterns'], $tags['replacements'], $template->subject);
            $body =  $template->message;
            if ($template) {
                $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $template->Template);
            }
            $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

            $config = JFactory::getConfig();
            // Get default mail sender info
            $mail_from_sys = $config->get('mailfrom');
            $mail_from_sys_name = $config->get('fromname');
            // Set sender
            $sender = [
                $mail_from_sys,
                $mail_from_sys_name
            ];

            // Configure email sender
            $mailer = JFactory::getMailer();
            $mailer->setSender($sender);
            $mailer->addReplyTo($mail_from_sys, $mail_from_sys_name);
            $mailer->addRecipient($user->email);
            $mailer->setSubject($subject);
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);

            $files = '';
            // Files uploaded from the frontend.
            if (!empty($attachments)) {
                // Here we also build the HTML being logged to show which files were attached to the email.
                $files = '<ul>';
                foreach ($attachments as $upload) {
                    if (file_exists(JPATH_SITE.DS.$upload)) {
                        $toAttach[] = JPATH_SITE.DS.$upload;
                        $files .= '<li>'.basename($upload).'</li>';
                    }
                }
                $files .= '</ul>';
            }

            $mailer->addAttachment($toAttach);

            // Send and log the email.
            $send = $mailer->Send();
            if ($send !== true) {
                $failed[] = $user->email;
                echo 'Error sending email: ' . $send->__toString();
                JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
            } else {
                $sent[] = $user->email;
                $log = [
                    'user_id_from' => $current_user->id,
                    'user_id_to' => $user->id,
                    'subject' => $subject,
                    'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('COM_EMUNDUS_APPLICATION_SENT') . ' ' . JText::_('COM_EMUNDUS_TO') . ' ' . $user->email . '</i><br>' . $body . $files,
                    'type' => !empty($template)?$template->type:''
                ];
                $this->logEmail($log);
                // Log the email in the eMundus logging system.
                $logsParams = array('created' => [$subject]);
                EmundusModelLogs::log($current_user->id, $user->id, '', 9, 'c', 'COM_EMUNDUS_ACCESS_MAIL_APPLICANT_CREATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * @param $lim
     * @param $page
     * @param $filter
     * @param $sort
     * @param $recherche
     *
     * @return array
     *
     * @since version 1.0
     */
    function getAllEmails($lim, $page, $filter, $sort, $recherche) {
        $query = $this->_db->getQuery(true);

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

        if ($filter == 'Unpublish') {
            $filterDate = $this->_db->quoteName('se.published') . ' = 0';
        } else {
            $filterDate = $this->_db->quoteName('se.published') . ' = 1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheSubject = $this->_db->quoteName('se.subject') . ' LIKE ' . $this->_db->quote('%'.$recherche.'%');
            $rechercheMessage = $this->_db->quoteName('se.message') . ' LIKE ' . $this->_db->quote('%'.$recherche.'%');
            $rechercheEmail = $this->_db->quoteName('se.emailfrom') . ' LIKE ' . $this->_db->quote('%'.$recherche.'%');
            $rechercheType = $this->_db->quoteName('se.type') . ' LIKE ' . $this->_db->quote('%'.$recherche.'%');
            $rechercheCategory = $this->_db->quoteName('se.category') . ' LIKE ' . $this->_db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheSubject.' OR '.$rechercheMessage.' OR '.$rechercheEmail.' OR '.$rechercheType .' OR '.$rechercheCategory;
        }

        $query->select('*')
            ->from($this->_db->quoteName('#__emundus_setup_emails', 'se'))
            ->where($filterDate)
            ->andWhere($fullRecherche)

            ->group($sortDb)
            ->order($sortDb.$sort);

        try {
            $this->_db->setQuery($query);
            $count_emails  = sizeof($this->_db->loadObjectList());
            if(empty($lim)) {
                $this->_db->setQuery($query, $offset);
            } else {
                $this->_db->setQuery($query, $offset, $limit);
            }

            $emails = $this->_db->loadObjectList();
            if (!empty($emails)) {
                foreach ($emails as $key => $email) {
                    $email->label = ['fr' => $email->subject, 'en' => $email->subject];

                    if (!empty($email->category)) {
                        $email->additional_columns = [
                            [
                                'key' => JText::_('COM_EMUNDUS_ONBOARD_CATEGORY'),
                                'value' => $email->category,
                                'classes' => 'em-p-5-12 em-font-weight-600 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius',
                                'display' => 'all'
                            ],
                        ];
                    } else {
                        $email->additional_columns = [['key' => JText::_('COM_EMUNDUS_ONBOARD_CATEGORY'), 'value' => '', 'classes' => '', 'display' => 'all']];
                    }

                    $emails[$key] = $email;
                }
            }

            return array('datas' => $emails, 'count' => $count_emails);
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/email | Error when try to get emails : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    /**
     * @param $data
     *
     * @return false
     *
     * @since version 1.0
     */
    public function deleteEmail($ids) {
        $deleted = false;
        $query = $this->_db->getQuery(true);

        if (!empty($ids)) {
            try {
                $query->delete($this->_db->quoteName('#__emundus_setup_emails'));
                if (is_array($ids)) {
                    $query->where($this->_db->quoteName('id') . ' IN (' . implode(', ', $ids) . ')');
                } else {
                    $query->where($this->_db->quoteName('id') . ' = ' . $ids);
                }

                // Do not delete system emails
                $query->andWhere($this->_db->quoteName('type') . ' != 1');

                $this->_db->setQuery($query);
                $this->_db->execute();

                // check if the emails were deleted, cannot just check db->execute() because it returns true even if no rows were deleted (e.g. if the email was a system email)
                $query->clear();
                $query->select($this->_db->quoteName('id'))
                    ->from($this->_db->quoteName('#__emundus_setup_emails'));
                if (is_array($ids)) {
                    $query->where($this->_db->quoteName('id') . ' IN (' . implode(', ', $ids) . ')');
                } else {
                    $query->where($this->_db->quoteName('id') . ' = ' . $ids);
                }

                $this->_db->setQuery($query);

                if (empty($this->_db->loadColumn())) {
                    $deleted = true;
                }
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/email | Cannot delete emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

        return $deleted;
    }

    /**
     * @param $data
     *
     * @return false
     *
     * @since version 1.0
     */
    public function unpublishEmail($data) {
        $query = $this->_db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array(
                    $this->_db->quoteName('published') . ' = 0'
                );
                $se_conditions = array(
                    $this->_db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($this->_db->quoteName('#__emundus_setup_emails'))
                    ->set($fields)
                    ->where($se_conditions);

                $this->_db->setQuery($query);
                return $this->_db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/email | Cannot unpublish emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return false|string
     *
     * @since version 1.0
     */
    public function publishEmail($data) {
        $query = $this->_db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array(
                    $this->_db->quoteName('published') . ' = 1'
                );
                $se_conditions = array(
                    $this->_db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($this->_db->quoteName('#__emundus_setup_emails'))
                    ->set($fields)
                    ->where($se_conditions);

                $this->_db->setQuery($query);
                return $this->_db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/email | Cannot publish emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return false
     *
     * @since version 1.0
     */
    public function duplicateEmail($data) {
        $query = $this->_db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $columns = array_keys($this->_db->getTableColumns('#__emundus_setup_emails'));

                $columns = array_filter($columns, function($k) {
                    return ($k != 'id' && $k != 'date_time');
                });

                foreach ($data as $id){
                    $query->clear()
                        ->select(implode(',', $this->_db->qn($columns)))
                        ->from($this->_db->quoteName('#__emundus_setup_emails'))
                        ->where($this->_db->quoteName('id') . ' = ' . $id);

                    $this->_db->setQuery($query);
                    $values[] = implode(', ',$this->_db->quote($this->_db->loadRow()));
                }


                $query->clear()
                    ->insert($this->_db->quoteName('#__emundus_setup_emails'))
                    ->columns(
                        implode(',', $this->_db->quoteName($columns))
                    )
                    ->values($values);

                $this->_db->setQuery($query);
                return $this->_db->execute();

            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/email | Cannot duplicate emails: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $id
     *
     * @return array|false
     *
     * @since version 1.0
     */
    public function getAdvancedEmailById($id) {
        $query = $this->_db->getQuery(true);

        if (empty($id)) {
            return false;
        }

        $query->select('*')
            ->from ($this->_db->quoteName('#__emundus_setup_emails'))
            ->where($this->_db->quoteName('id') . ' = '.$id);

        $this->_db->setQuery($query);

        try {
            $this->_db->setQuery($query);
            $email_Info = $this->_db->loadObject();           /// get email info

            /// count records of #emundus_setup_emails_repeat_receivers
            $query->clear()->select('COUNT(*)')->from($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' = ' . (int)$id);

            $this->_db->setQuery($query);
            $receiver_count = $this->_db->loadResult();
            $receiver_Info = array();

            if($receiver_count > 0) {
                $query->clear()->select('#__emundus_setup_emails_repeat_receivers.*')->from($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' = ' . (int)$id);

                $this->_db->setQuery($query);
                $receiver_Info = $this->_db->loadObjectList();         /// get receivers info (empty or not)
            }

            /// get associated email template (jos_emundus_email_template)
            $query->clear()
                ->select('#__emundus_email_templates.*')
                ->from($this->_db->quoteName('#__emundus_email_templates'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_emails').' ON '.$this->_db->quoteName('#__emundus_email_templates.id').' = '.$this->_db->quoteName('#__emundus_setup_emails.email_tmpl'))
                ->where($this->_db->quoteName('#__emundus_setup_emails.id') . ' = ' . (int)$id);

            $this->_db->setQuery($query);
            $template_Info = $this->_db->loadObjectList();

            /// get associated letters
            $query->clear()
                ->select('esa.*')
                ->from($this->_db->quoteName('#__emundus_setup_attachments','esa'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_letters','esl') . ' ON ' . $this->_db->quoteName('esl.attachment_id') . ' = ' . $this->_db->quoteName('esa.id'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment','eslr') . ' ON ' . $this->_db->quoteName('esl.attachment_id') . ' = ' . $this->_db->quoteName('eslr.letter_attachment'))
                ->where($this->_db->quoteName('eslr.parent_id') . ' = ' . (int)$id);
            $this->_db->setQuery($query);
            $letter_Info = $this->_db->loadObjectList();         /// get attachment info

            /// get associated candidate attachments
            $query->clear()
                ->select('#__emundus_setup_attachments.*')
                ->from($this->_db->quoteName('#__emundus_setup_attachments'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment') . ' ON ' . $this->_db->quoteName('#__emundus_setup_attachments.id') . ' = ' . $this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.candidate_attachment'))
                ->where($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . ' = ' . (int)$id);

            $this->_db->setQuery($query);
            $attachments_Info = $this->_db->loadObjectList();         /// get attachment info

            /// get associated tags
            $query->clear()
                ->select('#__emundus_setup_action_tag.*')
                ->from($this->_db->quoteName('#__emundus_setup_action_tag'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_repeat_tags') . ' ON ' . $this->_db->quoteName('#__emundus_setup_action_tag.id') . ' = ' . $this->_db->quoteName('#__emundus_setup_emails_repeat_tags.tags'))
                ->where($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . ' = ' . (int)$id);

            $this->_db->setQuery($query);
            $tags_Info = $this->_db->loadObjectList();         /// get attachment info

            return array('email' => $email_Info, 'receivers' => $receiver_Info, 'template' => $template_Info, 'letter_attachment' => $letter_Info, 'candidate_attachment' => $attachments_Info, 'tags' => $tags_Info);
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Cannot get the email by id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $data
     * @param $receiver_cc
     * @param $receiver_bcc
     * @param $letters
     * @param $documents
     * @param $tags
     *
     * @return bool
     *
     * @since version 1.0
     */
    public function createEmail($data, $receiver_cc=null, $receiver_bcc = null, $letters=null, $documents=null, $tags=null) {
        $created = false;
        $query = $this->_db->getQuery(true);

        // set regular expression for fabrik elem
        $fabrik_pattern = '/\${(.+[0-9])\}/';

        if (!empty($data)) {
            $query->insert($this->_db->quoteName('#__emundus_setup_emails'))
                ->columns($this->_db->quoteName(array_keys($data)))
                ->values(implode(',', $this->_db->Quote(array_values($data))));

            try {
                $this->_db->setQuery($query);
                $inserted = $this->_db->execute();

                if ($inserted) {
                    $newemail = $this->_db->insertid();
                    $created = $newemail;

                    $query->clear()
                        ->update($this->_db->quoteName('#__emundus_setup_emails'))
                        ->set($this->_db->quoteName('lbl') . ' = ' . $this->_db->quote('custom_'.date('YmdhHis')))
                        ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($newemail));
                    $this->_db->setQuery($query);
                    $this->_db->execute();

                    // add cc for new email
                    if(!empty($receiver_cc)) {
                        foreach ($receiver_cc as $key => $receiver) {
                            $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                            if($is_fabrik_tag == true) {
                                $query->clear()
                                    ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_cc_fabrik'));

                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false){
                                $query->clear()
                                    ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_cc_email'));

                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            }
                        }
                    }

                    // add bcc for new email
                    if(!empty($receiver_bcc)) {
                        foreach ($receiver_bcc as $key => $receiver) {
                            $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                            if($is_fabrik_tag == true) {
                                $query->clear()
                                    ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_bcc_fabrik'));

                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false) {
                                $query->clear()
                                    ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$newemail)
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                    ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_bcc_email'));

                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            }
                        }
                    }

                    // add letter attachment to table #jos_emundus_setup_emails_repeat_letter_attachment
                    if(!empty($letters)) {
                        foreach ($letters as $key => $letter) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' =  ' . (int)$newemail)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment') . ' = ' . (int)$letter);

                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }

                    // add candidate attachment to table #jos_emundus_setup_emails_repeat_candidate_attachment
                    if(!empty($documents)) {
                        foreach ($documents as $key => $document) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . ' =  ' . (int)$newemail)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.candidate_attachment') . ' = ' . (int)$document);

                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }

                    // add tag to table #jos_emundus_setup_emails_repeat_tags
                    if(!empty($tags)) {
                        foreach ($tags as $key => $tag) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_tags'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . ' =  ' . (int)$newemail)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.tags') . ' = ' . (int)$tag);

                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }
                }
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/email | Cannot create an email: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                $created = false;
            }
        }

        return $created;
    }

    /**
     * @param $id
     * @param $data
     * @param $receiver_cc
     * @param $receiver_bcc
     * @param $letters
     * @param $documents
     * @param $tags
     *
     * @return bool
     *
     * @since version 1.0
     */
    public function updateEmail($id, $data, $receiver_cc=null, $receiver_bcc=null, $letters=null, $documents=null, $tags=null) {

        $query = $this->_db->getQuery(true);

        // set regular expression for fabrik elem
        $fabrik_pattern = '/\${(.+[0-9])\}/';

        if (count($data) > 0) {

            $fields = [];

            foreach ($data as $key => $val) {
                $insert = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($val);
                $fields[] = $insert;
            }

            $query->update($this->_db->quoteName('#__emundus_setup_emails'))->set($fields)->where($this->_db->quoteName('id') . ' = '.$this->_db->quote($id));

            try {
                $this->_db->setQuery($query);
                $this->_db->execute();

                /// remove and update new documents for an email
                if(!empty($letters)) {
                    $query->clear()->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . '=' . (int)$id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();

                    foreach($letters as $key => $letter) {
                        $query->clear()
                            ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))
                            ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . ' =  ' . (int)$id)
                            ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.letter_attachment') . ' = ' . (int)$letter);

                        $this->_db->setQuery($query);
                        $this->_db->execute();
                    }
                } else {
                    /// if empty --> remove all letter attachments
                    $query->clear()->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_letter_attachment.parent_id') . '=' . (int)$id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                if(!empty($receiver_cc)) {
                    /// update receivers cc/bcc --> first :: delete old cc
                    $query->clear()
                        ->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $this->_db->quote('receiver_cc_%'));

                    $this->_db->setQuery($query);
                    $this->_db->execute();

                    foreach ($receiver_cc as $key => $receiver) {
                        /// if fabrik tags --> then, receiver type = 'receiver_cc_fabrik', otherwise, 'receivers_cc_email'
                        $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                        if($is_fabrik_tag == true) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_cc_fabrik'));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false){
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_cc_email'));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }
                } else {
                    /// if empty --> remove all receivers cc
                    $query->clear()
                        ->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $this->_db->quote('receiver_cc_%'));

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                /// update bcc
                if(!empty($receiver_bcc)) {
                    $query->clear()
                        ->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $this->_db->quote('receiver_bcc_%'));

                    $this->_db->setQuery($query);
                    $this->_db->execute();

                    foreach ($receiver_bcc as $key => $receiver) {
                        /// if fabrik tags --> then, receiver type = 'receiver_cc_fabrik', otherwise, 'receivers_cc_email'
                        $is_fabrik_tag = (bool) preg_match_all($fabrik_pattern, $receiver);
                        if($is_fabrik_tag) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_bcc_fabrik'));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        } else if(filter_var($receiver, FILTER_VALIDATE_EMAIL) !== false) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . ' =  ' . (int)$id)
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.receivers') . ' = ' . $this->_db->quote($receiver))
                                ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' = ' . $this->_db->quote('receiver_bcc_email'));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }
                } else {
                    /// if empty --> remove all bcc receivers
                    $query->clear()
                        ->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers'))
                        ->where($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.parent_id') . '=' . (int)$id)
                        ->andWhere($this->_db->quoteName('#__emundus_setup_emails_repeat_receivers.type') . ' LIKE ' . $this->_db->quote('receiver_bcc_%'));

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                // update candidate attachments #jos_emundus_setup_emails_repeat_candidate_attachment
                if(!empty($documents)) {
                    $query->clear()->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . '=' . (int)$id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();

                    foreach($documents as $key => $document) {
                        $query->clear()
                            ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))
                            ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . ' =  ' . (int)$id)
                            ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.candidate_attachment') . ' = ' . (int)$document);

                        $this->_db->setQuery($query);
                        $this->_db->execute();
                    }
                } else {
                    /// if empty --> remove all candidate attachments
                    $query->clear()->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_candidate_attachment.parent_id') . '=' . (int)$id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                // update tags #jos_emundus_setup_emails_repeat_tags
                if(!empty($tags)) {
                    $query->clear()->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_tags'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . '=' . (int)$id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();

                    foreach($tags as $key => $tag) {
                        $query->clear()
                            ->insert($this->_db->quoteName('#__emundus_setup_emails_repeat_tags'))
                            ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . ' =  ' . (int)$id)
                            ->set($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.tags') . ' = ' . (int)$tag);

                        $this->_db->setQuery($query);
                        $this->_db->execute();
                    }
                } else {
                    /// if empty --> remove all tags
                    $query->clear()->delete($this->_db->quoteName('#__emundus_setup_emails_repeat_tags'))->where($this->_db->quoteName('#__emundus_setup_emails_repeat_tags.parent_id') . '=' . (int)$id);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }


                return true;
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/email | Cannot update the email ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     *
     * @return false
     *
     * @since version 1.0
     */
    public function getEmailTypes() {
        $query = $this->_db->getQuery(true);

        $query->select('DISTINCT(type)')
            ->from ($this->_db->quoteName('#__emundus_setup_emails'))
            ->order('id DESC');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadColumn();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Cannot get emails types : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     *
     * @return false
     *
     * @since version 1.0
     */
    public function getEmailCategories() {
        $query = $this->_db->getQuery(true);

        $query->select('DISTINCT(category)')
            ->from ($this->_db->quoteName('#__emundus_setup_emails'))
            ->where($this->_db->quoteName('category') . ' <> ""')
            ->order('id DESC');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadColumn();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Cannot get emails categories : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     *
     * @return false
     *
     * @since version 1.0
     */
    function getStatus() {
        $query = $this->_db->getQuery(true);

        $query->select('*')
            ->from($this->_db->quoteName('#__emundus_setup_status'))
            ->order('step ASC');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Cannot get status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $pid
     *
     * @return false
     *
     * @since version 1.0
     */
    function getTriggersByProgramId($pid) {
        $lang = JFactory::getLanguage();
        $lid = 2;
        if ($lang->getTag() != 'fr-FR'){
            $lid = 1;
        }


        $query = $this->_db->getQuery(true);

        $query
            ->select(['DISTINCT(et.id) AS trigger_id','se.subject AS subject','ss.step AS status','ep.profile_id AS profile','et.to_current_user AS candidate','et.to_applicant AS manual'])
            ->from($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id', 'etrp'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger', 'et')
                . ' ON ' .
                $this->_db->quoteName('etrp.parent_id') . ' = ' . $this->_db->quoteName('et.id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails', 'se')
                . ' ON ' .
                $this->_db->quoteName('et.email_id') . ' = ' . $this->_db->quoteName('se.id'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_status', 'ss')
                . ' ON ' .
                $this->_db->quoteName('et.step') . ' = ' . $this->_db->quoteName('ss.step'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id', 'ep')
                . ' ON ' .
                $this->_db->quoteName('et.id') . ' = ' . $this->_db->quoteName('ep.parent_id'))
            ->where($this->_db->quoteName('etrp.programme_id') . ' = ' . $this->_db->quote($pid));

        try {
            $this->_db->setQuery($query);
            $triggers = $this->_db->loadObjectList();

            foreach ($triggers as $trigger) {
                $query->clear()
                    ->select('value')
                    ->from($this->_db->quoteName('#__falang_content'))
                    ->where($this->_db->quoteName('reference_id') . ' = ' . $this->_db->quote($trigger->status))
                    ->andWhere($this->_db->quoteName('reference_table') . ' = ' . $this->_db->quote('emundus_setup_status'))
                    ->andWhere($this->_db->quoteName('reference_field') . ' = ' . $this->_db->quote('value'))
                    ->andWhere($this->_db->quoteName('language_id') . ' = ' . $this->_db->quote($lid));
                $this->_db->setQuery($query);
                $trigger->status = $this->_db->loadResult();

                $query->clear()
                    ->select(['us.firstname','us.lastname'])
                    ->from($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','tu'))
                    ->leftJoin($this->_db->quoteName('#__emundus_users', 'us')
                        . ' ON ' .
                        $this->_db->quoteName('tu.user_id') . ' = ' . $this->_db->quoteName('us.user_id'))
                    ->where($this->_db->quoteName('tu.parent_id') . ' = ' . $this->_db->quote($trigger->trigger_id));
                $this->_db->setQuery($query);
                $trigger->users = array_values($this->_db->loadObjectList());
            }

            return $triggers;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Error at getting triggers by program id ' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $tid
     *
     * @return false
     *
     * @since version 1.0
     */
    function getTriggerById($tid) {
        $query = $this->_db->getQuery(true);

        $query->select(['DISTINCT(et.id) AS trigger_id','et.step AS status','et.email_id AS model','ep.profile_id AS target','et.to_current_user','et.to_applicant'])
            ->from($this->_db->quoteName('#__emundus_setup_emails_trigger', 'et'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id', 'ep')
                . ' ON ' .
                $this->_db->quoteName('et.id') . ' = ' . $this->_db->quoteName('ep.parent_id'))
            ->where($this->_db->quoteName('et.id') . ' = ' . $this->_db->quote($tid));

        try {
            $this->_db->setQuery($query);
            $trigger = $this->_db->loadObject();

            $query->clear()
                ->select('us.user_id')
                ->from($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','tu'))
                ->leftJoin($this->_db->quoteName('#__emundus_users', 'us')
                    . ' ON ' .
                    $this->_db->quoteName('tu.user_id') . ' = ' . $this->_db->quoteName('us.user_id'))
                ->where($this->_db->quoteName('tu.parent_id') . ' = ' . $this->_db->quote($trigger->trigger_id));
            $this->_db->setQuery($query);
            $trigger->users = array_values($this->_db->loadObjectList());

            return $trigger;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Error at getting trigger ' . $tid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $trigger
     * @param $users
     * @param $user
     *
     * @return false
     *
     * @since version 1.0
     */
    function createTrigger($trigger, $users, $user) {
        $created = false;

        if (!empty($user->id) && !empty($trigger['model']) && isset($trigger['status'])) {
            $email = $this->getEmailById($trigger['model']);

            if (!empty($email) && !empty($email->id)) {
                $query = $this->_db->getQuery(true);

                $to_current_user = 0;
                $to_applicant = 0;

                if ($trigger['action_status'] == 'to_current_user') {
                    $to_current_user = 1;
                } elseif ($trigger['action_status'] == 'to_applicant') {
                    $to_applicant = 1;
                }

                try {
                    $query->insert($this->_db->quoteName('#__emundus_setup_emails_trigger'))
                        ->set($this->_db->quoteName('user') . ' = ' . $this->_db->quote($user->id))
                        ->set($this->_db->quoteName('step') . ' = ' . $this->_db->quote($trigger['status']))
                        ->set($this->_db->quoteName('email_id') . ' = ' . $this->_db->quote($trigger['model']))
                        ->set($this->_db->quoteName('to_current_user') . ' = ' . $this->_db->quote($to_current_user))
                        ->set($this->_db->quoteName('to_applicant') . ' = ' . $this->_db->quote($to_applicant));


                    $this->_db->setQuery($query);
                    $this->_db->execute();
                    $trigger_id = $this->_db->insertid();

                    if (!empty($trigger_id)) {
                        if ($trigger['target'] == 5 || $trigger['target'] == 6) {
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id'))
                                ->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($trigger_id))
                                ->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($trigger['target']));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        } elseif ($trigger['target'] == 0) {
                            foreach (array_keys($users) as $uid) {
                                $query->clear()
                                    ->insert($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                                    ->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($trigger_id))
                                    ->set($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($uid));

                                $this->_db->setQuery($query);
                                $this->_db->execute();
                            }
                        }

                        $query->clear()
                            ->insert($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id'))
                            ->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($trigger_id))
                            ->set($this->_db->quoteName('programme_id') . ' = ' . $this->_db->quote($trigger['program']));

                        $this->_db->setQuery($query);
                        $trigger_assoc_prog = $this->_db->execute();
                        $created = true;
                    }
                } catch(Exception $e) {
                    JLog::add('component/com_emundus/models/email | Cannot create a trigger : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.error');
                }
            }
        }

        return $created;
    }

    /**
     * @param $tid
     * @param $trigger
     * @param $users
     *
     * @return false|void
     *
     * @since version 1.0
     */
    function updateTrigger($tid,$trigger,$users) {

        $query = $this->_db->getQuery(true);

        $to_current_user = 0;
        $to_applicant = 0;

        if ($trigger['action_status'] == 'to_current_user') {
            $to_current_user = 1;
        } elseif ($trigger['action_status'] == 'to_applicant') {
            $to_applicant = 1;
        }

        $query->update($this->_db->quoteName('#__emundus_setup_emails_trigger'))
            ->set($this->_db->quoteName('step') . ' = ' . $this->_db->quote($trigger['status']))
            ->set($this->_db->quoteName('email_id') . ' = ' . $this->_db->quote($trigger['model']))
            ->set($this->_db->quoteName('to_current_user') . ' = ' . $this->_db->quote($to_current_user))
            ->set($this->_db->quoteName('to_applicant') . ' = ' . $this->_db->quote($to_applicant))
            ->where($this->_db->quoteName('id') . ' = ' . $tid);

        try {
            $this->_db->setQuery($query);
            $this->_db->execute();

            if ($trigger['target'] == 5 || $trigger['target'] == 6) {
                $query->clear()
                    ->update($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id'))
                    ->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($trigger['target']))
                    ->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($tid));

                try {
                    $this->_db->setQuery($query);
                    $this->_db->execute();
                } catch(Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                    return false;
                }

            } elseif ($trigger['target'] == 0) {
                foreach (array_keys($users) as $uid) {
                    $query->clear()
                        ->select('COUNT(*)')
                        ->from($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->where($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($uid))
                        ->andWhere($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($tid));
                    $this->_db->setQuery($query);
                    $row = $this->_db->loadResult();

                    if ($row < 1) {
                        $query->clear()
                            ->insert($this->_db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                            ->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($tid))
                            ->set($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($uid));
                        try {
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        } catch(Exception $e) {
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                            return false;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Cannot update the trigger ' . $tid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $tid
     *
     * @return false
     *
     * @since version 1.0
     */
    function removeTrigger($tid) {
        $query = $this->_db->getQuery(true);

        $query->delete($this->_db->quoteName('#__emundus_setup_emails_trigger'))
            ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($tid));

        try {
            $this->_db->setQuery($query);
            return $this->_db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/email | Error at remove the trigger ' . $tid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $ids
     *
     * @return array
     *
     * @throws Exception
     * @since version 1.0
     */
    public function getEmailsFromFabrikIds($ids,$fnum = null) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $m_files = new EmundusModelFiles;

        $output = [];

        $fabrik_results = $m_files->getValueFabrikByIds($ids);

        foreach($fabrik_results as $fabrik) {
            $query = 'SELECT ' . $fabrik['db_table_name'] . '.' . $fabrik['name'] . ' FROM ' . $fabrik['db_table_name'] . ' WHERE ' . $fabrik['db_table_name'] . '.' . $fabrik['name'] . ' IS NOT NULL';
            if(!empty($fnum)){
                $query .= ' AND '.$fabrik['db_table_name'].'.fnum LIKE ' . $fnum;
            }
            $this->_db->setQuery($query);
            $output[] = $this->_db->loadObjectList();
        }

        $array_reduce = (array) array_reduce($output, 'array_merge', array());

        $result = [];
        foreach($array_reduce as $value) { foreach((array)$value as $data) { $result[] = $data; } }

        return array_unique($result);
    }
    
    public function checkUnpublishedTags($content)
    {
        $tags = [];

        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'helpers'.DS.'tags.php');
        $h_tags = new EmundusHelperTags();

        $db = JFactory::getDBO();

        if (!empty($content))
        {
            $query = $db->getQuery(true);
            $query->select('tag')
                ->from($db->quoteName('#__emundus_setup_tags', 't'))
                ->where($db->quoteName('t.published') . ' = 0');

            $tags_content = $h_tags->getVariables($content, 'SQUARE');

            if( !empty($tags_content) )
            {
                $tags_content = array_unique($tags_content);
                $query->andWhere('t.tag IN ("' . implode('","', $tags_content) . '")');

                try
                {
                    $db->setQuery($query);
                    $tags = $db->loadColumn();
                }
                catch (Exception $e)
                {
                    JLog::add('Error checking unpublished tags model/emails/setTags at query : ' . $query->__toString(), JLog::ERROR, 'com_emundus.email');

                    return array('patterns' => array(), 'replacements' => array());
                }
            }
        }

        return $tags;
    }
}
?>
