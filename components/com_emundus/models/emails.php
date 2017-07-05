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

class EmundusModelEmails extends JModelList
{
    var $_db = null;
    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct()
    {
        parent::__construct();
        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getUser();
    }

    /**
     * Get email template by code
     * @param    $lbl    the email code
     * @return   object  The email template object
     */
    public function getEmail($lbl)
    {
        $query = 'SELECT * FROM #__emundus_setup_emails WHERE lbl="'.mysql_real_escape_string($lbl).'"';
        $this->_db->setQuery( $query );
        return $this->_db->loadObject();
    }

    /**
     * Get email template by ID
     * @param   $id     The email template ID
     * @return  object  The email template object
     */
    public function getEmailById($id)
    {
        $query = 'SELECT * FROM #__emundus_setup_emails WHERE id='.mysql_real_escape_string($id);
        $this->_db->setQuery( $query );
        return $this->_db->loadObject();
    }

    /**
     * Get email definition to trigger on Status changes
     * @param   $step           INT The status of application
     * @param   $code           ARRAY of programme code
     * @param   $to_applicant   STRING define if trigger concern selected fnum from list or not. Can be 0, 1
     * @return  array           Emails templates and recipient to trigger
     */
    public function getEmailTrigger($step, $code, $to_applicant = 0)
    {
        $query = 'SELECT eset.id as trigger_id, eset.step, ese.*, eset.to_current_user, eset.to_applicant, eserp.programme_id, esp.code, esp.label, eser.profile_id, eserg.group_id, eseru.user_id
                  FROM #__emundus_setup_emails_trigger as eset
                  LEFT JOIN #__emundus_setup_emails as ese ON ese.id=eset.email_id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_programme_id as eserp ON eserp.parent_id=eset.id
                  LEFT JOIN #__emundus_setup_programmes as esp ON esp.id=eserp.programme_id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_profile_id as eser ON eser.parent_id=eset.id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_group_id as eserg ON eserg.parent_id=eset.id
                  LEFT JOIN #__emundus_setup_emails_trigger_repeat_user_id as eseru ON eseru.parent_id=eset.id
                  WHERE eset.step='.mysql_real_escape_string($step).' AND eset.to_applicant IN ('.$to_applicant.') AND esp.code IN ("'.implode('","', $code).'")';
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

                // default recipients
                if (isset($trigger->profile_id) && !empty($trigger->profile_id))
                    $emails_tmpl[$trigger->id][$trigger->code]['to']['profile'][] = $trigger->profile_id;
                if (isset($trigger->group_id) && !empty($trigger->group_id))
                    $emails_tmpl[$trigger->id][$trigger->code]['to']['group'][] = $trigger->group_id;
                if (isset($trigger->user_id) && !empty($trigger->user_id))
                    $emails_tmpl[$trigger->id][$trigger->code]['to']['user'][] = $trigger->user_id;
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
                    if (count(@$tmpl['to']['profile']) > 0) {
                        $where = ' eu.profile IN ('.implode(',', $tmpl['to']['profile']).')';
                        $as_where = true;
                    }

                    if (count(@$tmpl['to']['group']) > 0) {
                        $where = ' eg.group_id IN ('.implode(',', $tmpl['to']['group']).')';
                        $as_where = true;
                    }

                    if (count(@$tmpl['to']['user']) > 0) {
                        $where .= $as_where?' OR ':'';
                        $where .= 'u.block=0 AND u.id IN ('.implode(',', $tmpl['to']['user']).')';
                        $as_where = true;
                    }

                    if($as_where) {
                        $query = 'SELECT u.id, u.name, u.email, eu.university_id
                                    FROM #__users as u 
                                    LEFT JOIN #__emundus_users as eu on eu.user_id=u.id
                                    LEFT JOIN #__emundus_groups as eg on eg.user_id=u.id 
                                    WHERE '.$where.' 
                                    GROUP BY u.id';
                        $this->_db->setQuery( $query );
                        $users = $this->_db->loadObjectList();

                        foreach ($users as $key => $user) {
                            $recipients[$user->id] = array('id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'university_id' => $user->university_id);
                        }
                    }

                    if ($tmpl['to']['to_current_user'] == 1) {
                        $current_user = JFactory::getUser();
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
     * @param   $step           INT The status of application
     * @param   $code           ARRAY of programme code
     * @param   $to_applicant   INT define if trigger concern selected fnum or not
     * @param   $student        Object Joomla user
     * @return  array           Emails templates and recipient to trigger
     */
    public function sendEmailTrigger($step, $code, $to_applicant = 0, $student)
    {
        $app = JFactory::getApplication();
        $email_from_sys = $app->getCfg('mailfrom');


        jimport('joomla.log.log');
        JLog::addLogger(
            array(
                // Sets file name
                'text_file' => 'com_emundus.email.php'
            ),
            // Sets messages of all log levels to be sent to the file
            JLog::ALL,
            array('com_emundus')
        );

        $trigger_emails = $this->getEmailTrigger($step, $code, $to_applicant);

        if (count($trigger_emails) > 0) {
            // get current applicant course
            include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
            $campaigns = new EmundusModelCampaign;
            $campaign = $campaigns->getCampaignByID($student->campaign_id);
            $post = array( 
                'APPLICANT_ID'  => $student->id,
                'DEADLINE' => strftime("%A %d %B %Y %H:%M", strtotime($campaign['end_date'])),
                'APPLICANTS_LIST' => '',
                'EVAL_CRITERIAS' => '',
                'EVAL_PERIOD' => '',
                'CAMPAIGN_LABEL' => $campaign['label'],
                'CAMPAIGN_YEAR' => $campaign['year'],
                'CAMPAIGN_START' => $campaign['start_date'],
                'CAMPAIGN_END' => $campaign['end_date'],
                'CAMPAIGN_CODE' => $campaign['training'],
                'FNUM'          => $student->fnum
            );

            foreach ($trigger_emails as $key => $trigger_email) {

                foreach ($trigger_email[$student->code]['to']['recipients'] as $key => $recipient) {
                    $mailer     = JFactory::getMailer();

                    //$post = array();
                    $tags = $this->setTags($student->id, $post, $student->fnum);

                    $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['emailfrom']);
                    $from_id = 62;
                    $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['name']);
                    $to = $recipient['email'];
                    $to_id = $recipient['id'];
                    $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['subject']);
                    $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$student->code]['tmpl']['message']);
                    $body = $this->setTagsFabrik($body, array($student->fnum));
                    //$attachment[] = $path_file;

                    // setup mail
                    $sender = array(
                        $email_from_sys,
                        $fromname
                    );
        
                    $mailer->setSender($sender);
                    $mailer->addReplyTo($from, $fromname);
                    $mailer->addRecipient($to);
                    $mailer->setSubject($subject);
                    $mailer->isHTML(true);
                    $mailer->Encoding = 'base64';
                    $mailer->setBody($body);
                    $send = $mailer->Send();

                    if ( $send !== true ) {
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
                        //JLog::add($to.' '.$body, JLog::INFO, 'com_emundus');
                    }
                }
            }
        }
        return true;
    }

    /*
     *  @description    replace body message tags [constant] by value
     *  @param          $user           Object      user object
     *  @param          $str            String      string with tags
     *  @param          $fnum           String      application file number
     *  @param          $passwd         String      user password
     *  @return         $strval         String      str with tags replace by value
     */
    public function setBody($user, $str, $fnum=null, $passwd='')
    {
        $constants = $this->setConstants($user->id, null, $passwd);
        $strval = html_entity_decode(preg_replace($constants['patterns'], $constants['replacements'], $str), ENT_QUOTES);

        return $strval;
    }

    public function replace($replacement, $str)
    {
        $strval = preg_replace($replacement['patterns'], $replacement['replacements'], $str);

        return $strval;
    }

    /*
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
        $str = strtr( $str, $unwanted_array );
        return $str;
    }

    /*
     *  @description    get tags with Fabrik elementd IDs
     *  @param          $body           string
     *  @return         array           array of application file elements IDs
     */
    public function getFabrikElementIDs($body){
        preg_match_all('/\{(.*?)\}/', $body, $element_ids);

        return $element_ids;
    }

    /*
     *  @description    replace tags like {fabrik_element_id} by the application form value for current application file
     *  @param          $fum            string  application file number
     *  @param          $element_ids    array   Fabrik element ID
     *  @return         array           array of application file elements values
     */
    public function getFabrikElementValues($fnum, $element_ids){
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        $db = JFactory::getDBO();

        $element_details = @EmundusHelperList::getElementsDetailsByID('"'.implode('","', $element_ids).'"');

        foreach ($element_details as $key => $value) {
            $query = 'SELECT '.$value->element_name.' FROM '.$value->tab_name.' WHERE fnum like '.$db->Quote($fnum);
            $db->setQuery($query);
            $element_values[$value->element_id] = $db->loadResult();
        }

        return $element_values;
    }

    /*
     *  @description    replace tags like {fabrik_element_id} by the applicaiton form value in text
     *  @param          $body               string  source containing tags like {fabrik_element_id}
     *  @param          $element_values     array   Array of values index by Fabrik elements IDs
     *  @return         string              String with values
     */
    public function setElementValues($body, $element_values){

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

        //get logo
        $template   = $app->getTemplate(true);
        $params     = $template->params;

        $logo       = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
        $logo       = !empty($logo['path']) ? $logo['path'] : "";

        $patterns = array(
            '/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[USERNAME\]/', '/\[USER_ID\]/', '/\[USER_NAME\]/', '/\[USER_EMAIL\]/', '/\n/', '/\[USER_USERNAME\]/', '/\[PASSWORD\]/',
            '/\[ACTIVATION_URL\]/', '/\[SITE_URL\]/',
            '/\[APPLICANT_ID\]/', '/\[APPLICANT_NAME\]/', '/\[APPLICANT_EMAIL\]/', '/\[APPLICANT_USERNAME\]/', '/\[CURRENT_DATE\]/', '/\[LOGO\]/'
        );
        $replacements = array(
            $user->id, $user->name, $user->email, $user->username, $current_user->id, $current_user->name, $current_user->email, ' ', $current_user->username, $passwd,
            JURI::base()."index.php?option=com_users&task=registration.activate&token=".$user->get('activation'), JURI::base(),
            $user->id, $user->name, $user->email, $user->username, date("F j, Y"), $logo
        );

        if(count($post) > 0) {
            foreach ($post as $key => $value) {
                $patterns[] = '/\['.$key.'\]/';
                $replacements[] = $value;
            }
        }

        $constants = array('patterns' => $patterns , 'replacements' => $replacements);

        return $constants;
    }

    public function setTags($user_id, $post=null, $fnum=null, $passwd='')
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
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');

                        $result = "";
                    }
                    if ($tag['tag'] == 'PHOTO') {
                        if (empty($result))
                            $result = 'media/com_emundus/images/icones/personal.png';
                        else
                            $result = EMUNDUS_PATH_REL.$user_id.'/tn_'.$result;
                    }
                    $replacements[] = $result;
                } else
                    $replacements[] = $request[0];
            } else {
                $request = explode('|', $value);
                $val = $this->setTagsFabrik($request[1], array($fnum));
                $replacements[] = eval("$val");
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

    public function setTagsFabrik($str, $fnums = array())
    {
        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $file = new EmundusModelFiles();

        $jinput = JFactory::getApplication()->input;

        if (count($fnums) == 0) {
            $fnums = $jinput->get('fnums', null, 'RAW');
            $fnumsArray = (array) json_decode(stripslashes($fnums));
        }
        else {
            $fnumsArray = $fnums;
        }

        $tags = $file->getVariables($str);
        $idFabrik = array();
        $setupTags = array();

        if(count($tags) > 0) {
            foreach($tags as $i => $val)
            {
                $tag = strip_tags($val);
                if(is_numeric($tag))
                {
                    $idFabrik[] = $tag;
                }
                else
                {
                    $setupTags[] = $tag;
                }
            }
        }

        if(count($idFabrik) > 0) {
            $fabrikElts = $file->getValueFabrikByIds($idFabrik);
            $fabrikValues = array();
            foreach ($fabrikElts as $elt) {
                $params = json_decode($elt['params']);
                $groupParams = json_decode($elt['group_params']);
                $isDate = ($elt['plugin'] == 'date');
                $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');
                if (@$groupParams->repeat_group_button == 1 || $isDatabaseJoin) {
                    $fabrikValues[$elt['id']] = $file->getFabrikValueRepeat($elt, $fnumsArray, $params,
                        @$groupParams->repeat_group_button == 1);
                } else {
                    if ($isDate) {
                        $fabrikValues[$elt['id']] =
                            $file->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name'],
                                $params->date_form_format);
                    } else { 
                        $fabrikValues[$elt['id']] =
                            $file->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                    }
                }
                if ($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown" || $elt['plugin'] == "radiobutton") {
                    foreach ($fabrikValues[$elt['id']] as $fnum => $val) {
                        if (($elt['plugin'] == "checkbox") || ($elt['plugin'] == "radiobutton")) {
                            $val = json_decode($val['val']);
                        } else {
                            $val = explode(',', $val['val']);
                        }

                        foreach ($val as $k => $v) {
                            $index = array_search(trim($v), $params->sub_options->sub_values);
                            $val[$k] = $params->sub_options->sub_labels[$index];
                        }
                        $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $val);
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
            }
            $preg = array('patterns' => array(), 'replacements' => array());
            foreach ($fnumsArray as $fnum) {

                foreach ($idFabrik as $id) {
                    $preg['patterns'][] = '/\${' . $id . '\}/';
                    if (isset($fabrikValues[$id][$fnum])) {
                        $preg['replacements'][] = $fabrikValues[$id][$fnum]['val'];
                    } else {
                        $preg['replacements'][] = '';
                    }
                }
            }
            return $this->replace($preg, $str);
        }
        else
            return $str;
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


    public function sendMail($type=null, $fnum=null)
    {
        $jinput = JFactory::getApplication()->input;

        //$mail_type = JRequest::getVar('mail_type', null, 'POST', 'VARCHAR',0);
        $mail_type = $jinput->get('mail_type', null, 'CMD');

        if ($fnum != null) {
            $student_id = (int)substr($fnum, -7);
            $campaign_id = (int)substr($fnum, 14, 7);
        } else {
            $student_id = $jinput->get('student_id', null, 'INT'); //JRequest::getVar('student_id', null, 'POST', 'VARCHAR', 0);
            $campaign_id = $jinput->get('campaign_id', null, 'INT'); //JRequest::getVar('campaign_id', null, 'POST', 'VARCHAR', 0);
        }

        $student = JFactory::getUser($student_id);

        if (!isset($type))
            $type = $mail_type;

        if($type == "evaluation_result") {
            $mode = 1; // HTML
            $mail_cc = null;
            $mail_subject = $jinput->get('mail_subject', null, 'STRING'); //JRequest::getVar('mail_subject', null, 'POST', 'VARCHAR', 0);
            $mail_from_id = $this->_user->id;
            $mail_from_name = $this->_user->name;
            $mail_from = $this->_user->email;
            $mail_to_id = $jinput->get('mail_to', null, 'STRING'); //JRequest::getVar('mail_to', null, 'POST', 'VARCHAR', 0);
            $student = JFactory::getUser($mail_to_id);
            $mail_to_name = $student->name;
            $mail_to = $student->email;
            $mail_body = $this->setBody($student, JRequest::getVar('mail_body', null, 'POST', 'VARCHAR', JREQUEST_ALLOWHTML), $fnum, $passwd='');
            $mail_attachments = $jinput->get('mail_attachments', null, 'STRING'); //JRequest::getVar('mail_attachments', null, 'POST', 'VARCHAR', 0);

            if (!empty($mail_attachments)) $mail_attachments = explode(',', $mail_attachments);

            $sent = JUtility::sendMail($mail_from, $mail_from_name, $mail_to, $mail_subject, $mail_body, $mode, $mail_cc, null, @$mail_attachments);

            $message = array(
                'user_id_from' => $this->_user->id,
                'user_id_to' => $mail_to_id,
                'subject' => $mail_subject,
                'message' => $mail_body
            );
            $this->logEmail($message);

        } elseif($type == "expert") {

            require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
            include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $formid = $eMConfig->get('expert_fabrikformid', '110');
            $documentid = $eMConfig->get('expert_document_id', '36');

            $mode = 1; // HTML
            $mail_cc = null;
            $mail_subject = $jinput->get('mail_subject', null, 'STRING'); //JRequest::getVar('mail_subject', null, 'POST', 'VARCHAR', 0);
            $mail_from_name = $jinput->get('mail_from_name', null, 'STRING'); //JRequest::getVar('mail_from_name', null, 'POST', 'VARCHAR', 0);
            $mail_from = $jinput->get('mail_from', null, 'STRING'); //JRequest::getVar('mail_from', null, 'POST', 'VARCHAR', 0);
            

            $campaign = @EmundusHelperfilters::getCampaignByID($campaign_id);
            $application = new EmundusModelApplication;

            $tags = $this->setTags($this->_user->id);

            $mail_from_name = preg_replace($tags['patterns'], $tags['replacements'], $mail_from_name);
            $mail_from = preg_replace($tags['patterns'], $tags['replacements'], $mail_from);
            $mail_from_id = $this->_user->id;
  
            $mail_to = explode(',', $jinput->get('mail_to', null, 'STRING'));

            $mail_body = $this->setBody($student, $jinput->get('mail_body', null, 'RAW'), $fnum, $passwd='');

            //
            // Replacement
            //
            $post = array(  'TRAINING_PROGRAMME'    => $campaign['label'],
                            'CAMPAIGN_START'        => $campaign['start_date'],
                            'CAMPAIGN_END'          => $campaign['end_date'],
                            'EVAL_DEADLINE'         => date("d/M/Y", mktime(0, 0, 0, date("m")+2, date("d"), date("Y")))
            );
            $tags = $this->setTags($student_id, $post, $fnum);
            $mail_body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);

            //tags from Fabrik ID
            $element_ids = $this->getFabrikElementIDs($mail_body);
            if(count(@$element_ids[0])>0) {
                $element_values = $this->getFabrikElementValues($fnum, $element_ids[1]);
                $synthesis->block = $this->setElementValues($mail_body, $element_values);
            }

            $mail_attachments = $jinput->get('mail_attachments', null, 'STRING'); //JRequest::getVar('mail_attachments', null, 'POST', 'VARCHAR', 0);
            $delete_attachment = $jinput->get('delete_attachment', null, 'INT');

            if (!empty($mail_attachments))
                $mail_attachments = explode(',', $mail_attachments); 

            foreach ($mail_to as $m_to) {
                $key1 = md5($this->rand_string(20).time());
                $m_to = trim($m_to);
                // 2. MAJ de la table emundus_files_request
                $attachment_id = $documentid; // document avec clause de confidentialité
                $query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id, campaign_id, email, fnum)
                            VALUES (NOW(), '.$student_id.', "'.$key1.'", "'.$attachment_id.'", '.$campaign_id.', '.$this->_db->quote($m_to).', '.$this->_db->quote($fnum).')';
                $this->_db->setQuery( $query );
                $this->_db->query();

                // 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
                $link_accept = JURI::base().'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&tableid=71&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id;
                $link_refuse = JURI::base().'index.php?option=com_fabrik&c=form&view=form&formid=168&tableid=71&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id.'&usekey=keyid&rowid='.$key1;
                //$link_refuse = JURI::base().'index.php?option=com_emundus&task=decline&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id;

                $post = array(  'EXPERT_ACCEPT_LINK'    => $link_accept,
                                'EXPERT_REFUSE_LINK'    => $link_refuse
                );
                $tags = $this->setTags($student_id, $post, $fnum);

                $body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);
                $body = $this->setTagsFabrik($body, array($fnum));

                //$sent = JUtility::sendMail($mail_from, $mail_from_name, $m_to, $mail_subject, $body, $mode, $mail_cc, null, @$mail_attachments);
                $app    = JFactory::getApplication();
                $email_from_sys = $app->getCfg('mailfrom');
                $sender = array(
                    $email_from_sys,
                    $mail_from_name
                );
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
                if ( $send !== true ) {
                    $row = array(
                        'applicant_id' => $student_id,
                        'user_id' => $this->_user->id,
                        'reason' => JText::_( 'INFORM_EXPERTS' ),
                        'comment_body' => JText::_('ERROR').' '.JText::_('MESSAGE').' '.JText::_('NOT_SENT').' '.JText::_('TO').' '.$m_to,
                        'fnum'          =>  $fnum
                    );
                    echo 'Error sending email: ' . $send->__toString(); die();
                } else {
                    $row = array(
                        'applicant_id' => $student_id,
                        'user_id' => $this->_user->id,
                        'reason' => JText::_( 'INFORM_EXPERTS' ),
                        'comment_body' => JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to,
                        'fnum'          =>  $fnum
                    );
                    $message = array(
                        'user_id_from' => $this->_user->id,
                        'user_id_to' => '',
                        'subject' => $mail_subject,
                        'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to.'</i><br>'.$body
                    );
                    $this->logEmail($message);

                    echo "<hr>".JText::_('EMAIL_SENT').' : '.$m_to;
                    echo "<hr>".JText::_('SUBJECT').' : '.$mail_subject;
                    echo "<hr>".$body;
                }

                $application->addComment($row);

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

        } else
            return false;

        JFactory::getApplication()->enqueueMessage(JText::_('EMAIL_SENT'), 'message');
        return true;
    }

    // @description Log email send by the system or via the system
    // @param $row array of data
    public function logEmail($row) {
        $query = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
                        VALUES (".$this->_db->quote($row['user_id_from']).", ".$this->_db->quote($row['user_id_to']).", ".$this->_db->quote($row['subject']).", ".$this->_db->quote($row['message']).", NOW())";
        $this->_db->setQuery( $query );
        $this->_db->query();

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

}
?>