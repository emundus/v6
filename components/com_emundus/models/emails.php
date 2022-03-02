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
    var $_user = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct() {
        parent::__construct();
        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getSession()->get('emundusUser');

        JLog::addLogger(['text_file' => 'com_emundus.email.error.php'], JLog::ERROR);
    }

    /**
     * Get email template by code
     * @param    $lbl string the email code
     * @return   object|bool  The email template object
     */
    public function getEmail($lbl) {
	    $query = 'SELECT se.*, et.Template FROM #__emundus_setup_emails AS se LEFT JOIN #__emundus_email_templates AS et ON et.id = se.email_tmpl WHERE se.lbl like '.$this->_db->Quote($lbl);

	    try {
            $this->_db->setQuery($query);
            return $this->_db->loadObject();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            JLog::add($query, JLog::ERROR, 'com_emundus.email');
            return false;
        }
    }

    /**
     * Get email template by ID
     * @param   $id int The email template ID
     * @return  object  The email template object
     */
    public function getEmailById($id)
    {
        $query = 'SELECT * FROM #__emundus_setup_emails WHERE id='.$this->_db->Quote($id);
        $this->_db->setQuery( $query );
        return $this->_db->loadObject();
    }

    /**
     * Get email definition to trigger on Status changes
     * @param   $step           INT The status of application
     * @param   $code           array of programme code
     * @param   $to_applicant   int define if trigger concern selected fnum from list or not. Can be 0, 1
     * @return  array           Emails templates and recipient to trigger
     */
    public function getEmailTrigger($step, $code, $to_applicant = 0) {

        $query = 'SELECT eset.id as trigger_id, eset.step, ese.*, eset.to_current_user, eset.to_applicant, eserp.programme_id, esp.code, esp.label, eser.profile_id, eserg.group_id, eseru.user_id, et.Template
                  FROM #__emundus_setup_emails_trigger as eset
                  LEFT JOIN #__emundus_setup_emails as ese ON ese.id=eset.email_id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_programme_id as eserp ON eserp.parent_id=eset.id
                  LEFT JOIN #__emundus_setup_programmes as esp ON esp.id=eserp.programme_id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_profile_id as eser ON eser.parent_id=eset.id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_group_id as eserg ON eserg.parent_id=eset.id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_user_id as eseru ON eseru.parent_id=eset.id
                  LEFT JOIN #__emundus_email_templates AS et ON et.id = ese.email_tmpl
                  WHERE eset.step='.$step.' AND eset.to_applicant IN ('.$to_applicant.') AND esp.code IN ("'.implode('","', $code).'")';
        $this->_db->setQuery( $query );
        $triggers = $this->_db->loadObjectList();

        $emails_tmpl = array();
        if (count($triggers) > 0) {
            foreach ($triggers as $key => $trigger) {
                // email tmpl
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['subject'] = $trigger->subject;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['emailfrom'] = $trigger->emailfrom;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['message'] = $trigger->message;
                $emails_tmpl[$trigger->id][$trigger->code]['tmpl']['name'] = $trigger->name;

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
     * @throws Exception
     */
    public function sendEmailTrigger($step, $code, $to_applicant = 0, $student = null) {
        $app = JFactory::getApplication();
        $email_from_sys = $app->getCfg('mailfrom');

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.email.php'), JLog::ALL, array('com_emundus'));

        $trigger_emails = $this->getEmailTrigger($step, $code, $to_applicant);

        if (count($trigger_emails) > 0) {
            // get current applicant course
            include_once(JPATH_SITE.'/components/com_emundus/models/campaign.php');
            $m_campaign = new EmundusModelCampaign;
            $campaign = $m_campaign->getCampaignByID($student->campaign_id);
            $post = array(
                'APPLICANT_ID' => $student->id,
                'DEADLINE' => strftime("%A %d %B %Y %H:%M", strtotime($campaign['end_date'])),
                'APPLICANTS_LIST' => '',
                'EVAL_CRITERIAS' => '',
                'EVAL_PERIOD' => '',
                'CAMPAIGN_LABEL' => $campaign['label'],
                'CAMPAIGN_YEAR' => $campaign['year'],
                'CAMPAIGN_START' => $campaign['start_date'],
                'CAMPAIGN_END' => $campaign['end_date'],
                'CAMPAIGN_CODE' => $campaign['training'],
                'FNUM' => $student->fnum,
	            'COURSE_NAME' => $campaign['label']
            );

            foreach ($trigger_emails as $trigger_email) {

                foreach ($trigger_email[$student->code]['to']['recipients'] as $recipient) {
                    $mailer = JFactory::getMailer();

                    $tags = $this->setTags($student->id, $post, $student->fnum);

                    $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
                    $from_id = 62;
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


	                // If the email sender has the same domain as the system sender address.
                    if (!empty($from) && substr(strrchr($from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                        $mail_from_address = $from;
                    } else {
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
                    $send = $mailer->Send();

                    if ($send !== true) {
                        echo 'Error sending email: ' . $send->__toString();
                        JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
                    } else {
                        $message = array(
                            'user_id_from' => $from_id,
                            'user_id_to' => $to_id,
                            'subject' => $subject,
                            'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$to.'</i><br>'.$body
                        );
                        $this->logEmail($message);
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
     */
    public function getFabrikElementValues($fnum, $element_ids) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        $db = JFactory::getDBO();

        $element_details = @EmundusHelperList::getElementsDetailsByID('"'.implode('","', $element_ids).'"');

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
     */
    public function setElementValues($body, $element_values) {

        foreach ($element_values as $key => $value) {
            $body = str_replace('{'.$key.'}', $value, $body);
        }

        return $body;

    }

    public function setConstants($user_id, $post=null, $passwd='')
    {
        $app            = JFactory::getApplication();
        $current_user   = JFactory::getUser();
        $user           = JFactory::getUser($user_id);
        $config         = JFactory::getConfig();

        //get logo
        $template   = $app->getTemplate(true);
        $params     = $template->params;
        //$logo       = $params->get('logo');
        $sitename   = $config->get('sitename');

        /*if (!empty($logo)) {
            $logo   = json_decode(str_replace("'", "\"", $logo->custom->image), true);
        }

        $logo       = !empty($logo['path']) ? $logo['path'] : "";*/


        if (!empty($params->get('logo')->custom->image)) {
            $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
            $logo = !empty($logo['path']) ? JURI::base().$logo['path'] : "";

        } else {
            $logo_module = JModuleHelper::getModuleById('90');
            preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
            $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

            if ((bool) preg_match($pattern, $tab[1])) {
                $tab[1] = parse_url($tab[1], PHP_URL_PATH);
            }

            $logo = JURI::base().$tab[1];
        }

        $patterns = array(
            '/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[SENDER_MAIL\]/', '/\[USERNAME\]/', '/\[USER_ID\]/', '/\[USER_NAME\]/', '/\[USER_EMAIL\]/', '/\n/', '/\[USER_USERNAME\]/', '/\[PASSWORD\]/',
            '/\[ACTIVATION_URL\]/', '/\[ACTIVATION_URL_RELATIVE\]/' ,'/\[SITE_URL\]/' ,'/\[SITE_NAME\]/',
            '/\[APPLICANT_ID\]/', '/\[APPLICANT_NAME\]/', '/\[APPLICANT_EMAIL\]/', '/\[APPLICANT_USERNAME\]/', '/\[CURRENT_DATE\]/', '/\[LOGO\]/'
        );
        $replacements = array(
            $user->id, $user->name, $user->email, $user->email, $user->username, $current_user->id, $current_user->name, $current_user->email, ' ', $current_user->username, $passwd,
            JURI::base()."index.php?option=com_users&task=registration.activate&token=".$user->get('activation'), "index.php?option=com_users&task=registration.activate&token=".$user->get('activation'), JURI::base(), $sitename,
            $user->id, $user->name, $user->email, $user->username, JFactory::getDate('now')->format(JText::_('DATE_FORMAT_LC3')), $logo
        );

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

        $constants = array('patterns' => $patterns , 'replacements' => $replacements);

        return $constants;
    }

    public function setTags($user_id, $post=null, $fnum=null, $passwd='', $content='')
    {
        $db = JFactory::getDBO();
        //$user = JFactory::getUser($user_id);

        $query = "SELECT tag, request FROM #__emundus_setup_tags";
        $db->setQuery($query);
        $tags = $db->loadAssocList();

        $constants = $this->setConstants($user_id, $post, $passwd);

        $patterns = $constants['patterns'];
        $replacements = $constants['replacements'];
        foreach ($tags as $tag) {
            if (!empty($content)){
                $tag_pattern = '[' . $tag['tag'] . ']';
                if(strpos($content, $tag_pattern) === false){
                    continue;
                }
            }
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
                        if (empty($result))
                            $result = 'media/com_emundus/images/icones/personal.png';
                        else {
                            if(file_exists(EMUNDUS_PATH_REL.$user_id.'/tn_'.$result)) {
                                $result = EMUNDUS_PATH_REL.$user_id.'/tn_'.$result;
                            } else {
                                $result = EMUNDUS_PATH_REL.$user_id.'/'.$result;
                            }
                        }
                    }
                    $replacements[] = $result;

                } else {
                    $replacements[] = $request[0];
                }

            } elseif (!empty($fnum)) {
                $request = explode('|', $value);
                $val = $this->setTagsFabrik($request[1], array($fnum));

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
                $request = explode('|', $value);

                try {
                    $result = eval("$request[1]");
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

        $tags = array('patterns' => $patterns , 'replacements' => $replacements);

        return $tags;
    }


    public function setTagsWord($user_id, $post=null, $fnum=null, $passwd='')
    {
        $db = JFactory::getDBO();
        //$user = JFactory::getUser($user_id);

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

                } else
                    $replacements[] = $request[0];
            }
            else {
                $request = explode('|', $value);
                $val = $this->setTagsFabrik($request[1], array($fnum));
                $replacements[] = eval("$val");
            }
        }

        $tags = array('patterns' => $patterns , 'replacements' => $replacements);

        return $tags;
    }

    public function setTagsFabrik($str, $fnums = array()) {
        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
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
                            $elm[] = JText::_($params->sub_options->sub_labels[$key]);
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
            }
            $preg = array('patterns' => array(), 'replacements' => array());
            foreach ($fnumsArray as $fnum) {
                foreach ($idFabrik as $id) {
                    $preg['patterns'][] = '/\${' . $id . '\}/';
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
     */
    private function getVariables($str)
    {
        preg_match_all('/\$\{(.*?)}/i', $str, $matches);

        return $matches[1];
    }


    public function sendMail($type = null, $fnum = null) {

        $jinput = JFactory::getApplication()->input;
        $mail_type = $jinput->get('mail_type', null, 'CMD');

        if ($fnum != null) {

            $student_id = (int)substr($fnum, -7);
            $campaign_id = (int)substr($fnum, 14, 7);

        } else {

            $student_id = $jinput->get('student_id', null, 'INT');
            $campaign_id = $jinput->get('campaign_id', null, 'INT');

        }

        $student = JFactory::getUser($student_id);

        if (!isset($type)) {
	        $type = $mail_type;
        }

        if ($type == "evaluation_result") {

            $mode = 1; // HTML

            $mail_cc = null;
            $mail_subject = $jinput->get('mail_subject', null, 'STRING');

            $mail_from_name = $this->_user->name;
            $mail_from = $this->_user->email;

            $mail_to_id = $jinput->get('mail_to', null, 'STRING');
            $student = JFactory::getUser($mail_to_id);
            $mail_to = $student->email;

            $mail_body = $this->setBody($student, JRequest::getVar('mail_body', null, 'POST', 'VARCHAR', JREQUEST_ALLOWHTML), '');
            $mail_attachments = $jinput->get('mail_attachments', null, 'STRING');

            if (!empty($mail_attachments))
                $mail_attachments = explode(',', $mail_attachments);

            $sent = JUtility::sendMail($mail_from, $mail_from_name, $mail_to, $mail_subject, $mail_body, $mode, $mail_cc, null, @$mail_attachments);

            $message = [
                'user_id_from'  => $this->_user->id,
                'user_id_to'    => $mail_to_id,
                'subject'       => $mail_subject,
                'message'       => $mail_body
            ];
            $this->logEmail($message);

        } elseif ($type == "expert") {

            require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
            include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

            $eMConfig   = JComponentHelper::getParams('com_emundus');
            $formid     = json_decode($eMConfig->get('expert_fabrikformid', '{"accepted":169, "refused":328}'));
            $documentid = $eMConfig->get('expert_document_id', '36');

            $mail_cc        = null;
            $mail_subject   = $jinput->get('mail_subject', null, 'STRING');

            $mail_from_name = $jinput->get('mail_from_name', null, 'STRING');
            $mail_from      = $jinput->get('mail_from', null, 'STRING');

            $campaign = @EmundusHelperfilters::getCampaignByID($campaign_id);

            $tags = $this->setTags($this->_user->id);

            $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);
            $mail_from      = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);

            $mail_to = explode(',', $jinput->get('mail_to', null, 'STRING'));

            $mail_body = $this->setBody($student, $jinput->get('mail_body', null, 'RAW'));

            //
            // Replacement
            //
            $post = [
                'TRAINING_PROGRAMME'    => $campaign['label'],
                'CAMPAIGN_START'        => $campaign['start_date'],
                'CAMPAIGN_END'          => $campaign['end_date'],
                'EVAL_DEADLINE'         => date("d/M/Y", mktime(0, 0, 0, date("m")+2, date("d"), date("Y")))
            ];
            $tags = $this->setTags($student_id, $post, $fnum);
            $mail_body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);

            //tags from Fabrik ID
            $element_ids = $this->getFabrikElementIDs($mail_body);
            $synthesis = new stdClass();
            if (count(@$element_ids[0]) > 0) {
                $element_values     = $this->getFabrikElementValues($fnum, $element_ids[1]);
                $synthesis->block   = $this->setElementValues($mail_body, $element_values);
            }

            $mail_attachments = $jinput->get('mail_attachments', null, 'STRING');
            $delete_attachment = $jinput->get('delete_attachment', null, 'INT');

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
                $query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id, campaign_id, email, fnum)
                            VALUES (NOW(), '.$student_id.', "'.$key1.'", "'.$attachment_id.'", '.$campaign_id.', '.$this->_db->quote($m_to).', '.$this->_db->quote($fnum).')';
                $this->_db->setQuery($query);
                $this->_db->query();

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
                        'user_id'       => $this->_user->id,
                        'reason'        => JText::_( 'INFORM_EXPERTS' ),
                        'comment_body'  => JText::_('ERROR').' '.JText::_('MESSAGE').' '.JText::_('NOT_SENT').' '.JText::_('TO').' '.$m_to,
                        'fnum'          => $fnum
                    ];
                    $print_message .= '<hr>Error sending email: ' . $send->__toString();

                } else {
                    $sent[] = $m_to;
                    $row = [
                        'applicant_id'  => $student_id,
                        'user_id'       => $this->_user->id,
                        'reason'        => JText::_( 'INFORM_EXPERTS' ),
                        'comment_body'  => JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to,
                        'fnum'          =>  $fnum
                    ];

                    $query = 'SELECT id FROM #__users WHERE email like '.$this->_db->Quote($m_to);
                    $this->_db->setQuery($query);
                    $user_id_to = $this->_db->loadResult();

                    if ($user_id_to > 0) {
                        $message = [
                            'user_id_from'  => $this->_user->id,
                            'user_id_to'    => $user_id_to,
                            'subject'       => $mail_subject,
                            'message'       => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to.'</i><br>'.$body
                        ];
                        $this->logEmail($message);
                    }

                    $print_message .= '<hr>'.JText::_('EMAIL_SENT').' : '.$m_to;
                    $print_message .= '<hr>'.JText::_('SUBJECT').' : '.$mail_subject;
                    $print_message .= '<hr>'.$body;
                }

                $m_application = new EmundusModelApplication;
                $m_application->addComment($row);

                $key1 = "";

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

        JFactory::getApplication()->enqueueMessage(JText::_('EMAIL_SENT'), 'message');
        return true;
    }

    /**
     * Used for sending the expert invitation email with the link to the form.
	 * @param $fnums array
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function sendExpertMail(array $fnums) : array {

		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

		$h_filters = new EmundusHelperFilters();
		$m_files = new EmundusModelFiles();

		JLog::addLogger(['text_file' => 'com_emundus.inviteExpert.error.php'], JLog::ERROR, 'com_emundus');

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
		// This means that we should NOT PUT TAGS RELATING TO PERSONAL INFO IN THE EMAIL.
		$example_fnum = $fnums[0];
		$campaign_id = (int)substr($example_fnum, 14, 7);
		$campaign = $h_filters->getCampaignByID($campaign_id);
		$example_user_id = (int)substr($example_fnum, -7);
		$example_user = JFactory::getUser($example_user_id);

		$tags = $this->setTags($this->_user->id);

		$mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);
		$mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);

		$mail_to = $jinput->post->getRaw('mail_to');

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
		$synthesis = new stdClass();
		if (count(@$element_ids[0]) > 0) {
			$element_values = $this->getFabrikElementValues($example_fnum, $element_ids[1]);
			$synthesis->block = $this->setElementValues($mail_body, $element_values);
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
				JLog::add('Error inserting file requests for expert invitations : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
				continue;
			}

			// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
			$link_accept = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->accepted.'&keyid='.$key1.'&cid='.$campaign_id;
			$link_refuse = 'index.php?option=com_fabrik&c=form&view=form&formid='.$formid->refused.'&keyid='.$key1.'&cid='.$campaign_id.'&usekey=keyid&rowid='.$key1;
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

			$tags = $this->setTags($example_user_id, $post, $example_fnum);

			$body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);
			$body = $this->setTagsFabrik($body, [$example_fnum]);

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
				$print_message .= '<hr>Error sending email: ' . $send->__toString();
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
							'user_id_from'  => $this->_user->id,
							'user_id_to'    => $user_id_to,
							'subject'       => $mail_subject,
							'message'       => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to.'</i><br>'.$body
						];
						$this->logEmail($message);
					}
				} catch (Exception $e) {
					JLog::add('Could not get user by email : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
				}

				$print_message .= '<hr>'.JText::_('EMAIL_SENT').' : '.$m_to;
				$print_message .= '<hr>'.JText::_('SUBJECT').' : '.$mail_subject;
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
		}

		return [
			'sent' => $sent,
			'failed' => $failed,
			'message' => $print_message
		];
	}

    // @description Log email send by the system or via the system
    // @param $row array of data
    public function logEmail($row) {

        $offset = JFactory::getConfig()->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $now = 'NOW()';
        }

        $query = $this->_db->getQuery(true);

        $columns = ['user_id_from', 'user_id_to', 'subject', 'message' , 'date_time'];

        $values = [$row['user_id_from'], $row['user_id_to'], $this->_db->quote($row['subject']), $this->_db->quote($row['message']), $this->_db->quote($now)];

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
            $this->_db->execute();

        } catch (Exception $e) {
            JLog::add('Error logging email in model/emails : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }

    }

    //////////////////////////  SET FILES REQUEST  /////////////////////////////
    //
    // Génération de l'id du prochain fichier qui devra être ajouté par le referent

    // 1. Génération aléatoire de l'ID
    public function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
        $string = '';
        for ($i = 0; $i < $len; $i++) {
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars{$pos};
        }
        return $string;
    }

    /**
     * Gets all emails sent to or from the User id.
     * @param Int user ID
     * @return Mixed Array
     */
    public function get_messages_to_from_user($user_id) {

        $query = 'SELECT * FROM #__messages WHERE (user_id_to ='.$user_id.' OR user_id_from ='.$user_id.') AND folder_id <> 2 ORDER BY date_time desc';

        try {

            $this->_db->setquery($query);
            return $this->_db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting messages sent to or from user: '.$user_id.' at query: '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

    }

    /**
     * @param int $email
     * @param array $groups
     * @param array $attachments
     * @return bool
     */
    public function sendEmailToGroup(int $email, array $groups, array $attachments = []) : bool {

        if (empty($email) || empty($groups)) {
            JLog::add('No user or group found in sendEmailToGroup function: ', JLog::ERROR, 'com_emundus');
            return false;
        }

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'messages.php');
        $m_messages = new EmundusModelMessages();
        $template = $m_messages->getEmail($email);

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'groups.php');
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
        JLog::add(sizeof($users) .' emails sent to the following groups: ' . implode(", ". $groups), JLog::ERROR, 'com_emundus');
        return true;
    }

    /**
     * @param int $user
     * @param object $template
     * @param array $attachments
     * @return void
     */
    public function sendEmailFromPlatform(int $user, object $template, array $attachments) : void {
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $current_user = JFactory::getUser();
        $user = JFactory::getUser($user);
        $toAttach = [];

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
                if (file_exists(JPATH_BASE.DS.$upload)) {
                    $toAttach[] = JPATH_BASE.DS.$upload;
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
                'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' . JText::_('TO') . ' ' . $user->email . '</i><br>' . $body . $files,
                'type' => !empty($template)?$template->type:''
            ];
            $this->logEmail($log);
            // Log the email in the eMundus logging system.
            $logsParams = array('created' => [$subject]);
            EmundusModelLogs::log($current_user->id, $user->id, '', 9, 'c', 'COM_EMUNDUS_ACCESS_MAIL_APPLICANT_CREATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
        }
    }
}
?>
