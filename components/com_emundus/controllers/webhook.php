<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @copyright  eMundus
 * @author     Benjamin Rivalland
 * @since      3.9.16
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

use GuzzleHttp\Client as GuzzleClient;

/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage Components
 */

class EmundusControllerWebhook extends JControllerLegacy {

	private $m_files;
	private $c_emundus;

	public function __construct(array $config = array()) {
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'controller.php');

		$this->m_files = new EmundusModelFiles;
		$this->controller = new EmundusController;

		// Attach logging system.
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.webhook.php'], JLog::ALL, array('com_emundus.webhook'));

		parent::__construct($config);
        // Attach logging system.
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.webhook.php'], JLog::ALL, array('com_emundus.webhook'));
	}

	public function callback(){
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$ips_allowed = $eMConfig->get('callback_whitelist_ip');
		$ips_allowed = !empty($ips_allowed) ? explode(',', $eMConfig->get('callback_whitelist_ip')) : null;

		$allowed = true;
		if(!empty($ips_allowed)){
			$allowed = false;

			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			if(!empty($ip)) {
				$allowed = in_array($ip, $ips_allowed);
			}
		}

		if($allowed) {
			$jinput = JFactory::getApplication()->input;
			$type   = $jinput->getString('type');

			$payload       = !empty($_POST["payload"]) ? $_POST["payload"] : file_get_contents("php://input");
			$webhook_datas = json_decode($payload, true);

			JPluginHelper::importPlugin('emundus', 'custom_event_handler');
			$return = \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onWebhookCallbackProcess', ['webhook_datas' => $webhook_datas, 'type' => $type]]);

			$result = $return[0]['onWebhookCallbackProcess'];
		} else {
			$result = ['status' => false,'message' => 'You are not allowed to access to this route'];
		}

		header('Content-type: application/json');
		echo json_encode((object)$result);
		exit;
	}


	/**
	 * Downloads the file associated to the YouSign procedure that was pushed.
	 */
	public function yousign() {
        $body = (string)file_get_contents('php://input');
        $body = json_decode($body);

        if ($body->event_name == 'signature_request.done' && !empty($body->data)) {
            JLog::add('Reveived WebHook : '. json_encode($body), JLog::INFO, 'com_emundus.webhook');
            $signatureRequest = $body->data->signature_request;

            if (!empty($signatureRequest->id)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $eMConfig = JComponentHelper::getParams('com_emundus');

                $baseUrl = $eMConfig->get('yousign_prod', 'https://staging-api.yousign.com');
                $baseUrl = "https://api-sandbox.yousign.app/v3";
                $api_key = $eMConfig->get('yousign_api_key', 'https://staging-api.yousign.com');
                $api_key = "6zzPEoij6Gwr5jsq14UF8whW0nHl62Ru";
                $client = new GuzzleClient();

                foreach($signatureRequest->documents as $document) {
                    if ($document->nature == 'signable_document') {
                        // TODO: retrieve upload id from document->id


                        // TODO: replace emundus document content by yousign document content
                        try {
                            $response = $client->request('GET', $baseUrl . '/signature_requests/' . $signatureRequest->id . '/documents/download', [
                                'headers' => [
                                    'Authorization' => 'Bearer '. $api_key,
                                    'accept' => 'application/zip, application/pdf',
                                ],
                                'sink' => fopen()
                            ]);

                            if ($response->getStatusCode() == 200) {

                            }
                        } catch (Exception $e) {
                            echo json_encode(array('status' => false, 'msg' => $e->getMessage()));
                            exit;
                        }
                    }
                }
            }
        } else {
            JLog::add('YouSign bad JSON : '.file_get_contents('php://input'), JLog::ERROR, 'com_emundus.webhook');
            echo json_encode(array('status' => false, 'msg' => "Nothing to do here..."));
            exit;
        }
	}



	/**
	 * Gets video info from addpipe webhook
	 *
	 * @return bool|string
	 * @throws Exception
	 */
	public function addpipe() {

		$db 		= JFactory::getDBO();
		$eMConfig 	= JComponentHelper::getParams('com_emundus');

		$ftp_path 	= $eMConfig->get('addpipe_path_ftp', null);
		$secret 	= JFactory::getConfig()->get('secret');
		$token 		= JFactory::getApplication()->input->get('token', '', 'ALNUM');

		if ($token != $secret) {
			JLog::add('Bad token sent.', JLog::ERROR, 'com_emundus.webhook');
			return false;
		}

		if (is_null($ftp_path)) {
			JLog::add('FTP path is null.', JLog::ERROR, 'com_emundus.webhook');
			return false;
		}

		try {
			$payload = !empty($_POST["payload"]) ? $_POST["payload"] : file_get_contents("php://input");

			//the data is JSON encoded, so we must decode it in an associative array
			$webhookData = json_decode($payload, true);
			$webhookDataApplication = json_decode($webhookData["data"]["payload"], true);

			$vidName = $webhookData["data"]["videoName"].'.'.$webhookData["data"]["type"];

			//you can get the webhook type by accessing the event element in the array
			//$type = $webhookData["event"];

			if (empty($webhookDataApplication["userId"])) {
				$error = JUri::getInstance().' APPLICANT_ID is NULL';
				JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

				return false;
			}

			//move video from ftp to applicant documents
			if (!file_exists(EMUNDUS_PATH_ABS.$webhookDataApplication["userId"])) {
				// An error would occur when the index.html file was missing, the 'Unable to create user file' error appeared yet the folder was created.
				if (!file_exists(EMUNDUS_PATH_ABS.'index.html')) {
					touch(EMUNDUS_PATH_ABS.'index.html');
				}

				if (!mkdir(EMUNDUS_PATH_ABS.$webhookDataApplication["userId"]) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$webhookDataApplication["userId"].DS.'index.html')){
					$error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> Unable to create user file';
					JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

					return false;
				}
			}
			chmod(EMUNDUS_PATH_ABS.$webhookDataApplication["userId"], 0755);

			if (!file_exists($ftp_path.DS.$vidName)) {
				$error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> File not found: '.$ftp_path.DS.$vidName;
				JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

				return false;
			}

			if (!copy($ftp_path.DS.$vidName, EMUNDUS_PATH_ABS.$webhookDataApplication["userId"].DS.$vidName)) {

                $error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> Cannot move file: '.$ftp_path.DS.$vidName.' to '.EMUNDUS_PATH_ABS.$webhookDataApplication["userId"].DS.$vidName;
                JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

                return false;
            }

			//add document to emundus_attachments table
			$fnumInfos = $this->m_files->getFnumInfos($webhookDataApplication["fnum"]);

            $addPipeUrl = '<a target="_blank" href="'.$eMConfig->get('addpipe_video_link', null). $eMConfig->get('addpipe_account_hash', null) . '/' . $vidName .'">' . JText::_('LINK_TO_ADDPIPE_VIDEO') . '</a>';
            $description = $this->FileSizeConvert(filesize($ftp_path.DS.$vidName)) . ' ' .$addPipeUrl;

            $query = 'INSERT INTO jos_emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum) 
						VALUES ('.$webhookDataApplication["userId"].', '.$webhookDataApplication["aid"].', '.$db->Quote($vidName).', '.$db->Quote($description).', 1, 1, '.$fnumInfos['id'].', '.$db->Quote($webhookDataApplication["fnum"]).')';

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.$webhookDataApplication["userId"].' -> '.$e->getMessage().' :: '.$query;
                JLog::add('Unable to insert uploaded document: '.$error, JLog::ERROR, 'com_emundus.webhook');
            }

		} catch (Exception $e) {
			JLog::add('Unable to handle addpipe webhook: '.$payload, JLog::ERROR, 'com_emundus.webhook');
			return false;
		}

		//log webhook
		JLog::add('Webhook START: '.$webhookDataApplication["aid"].' :: '.$webhookDataApplication["userId"].' :: '.$webhookDataApplication["fnum"].' :: '.$webhookDataApplication["jobId"].' :: '.$vidName.' :: '.$payload, JLog::WARNING, 'com_emundus.webhook');
		return true;
	}


	/**
	* Converts bytes into human readable file size.
	*
	* @param string $bytes
	* @return string human readable file size (2,87 Мб)
	* @author Mogilev Arseny
	*/
	function FileSizeConvert($bytes) {
	    $bytes = floatval($bytes);
	        $arBytes = array(
	            0 => array(
	                "UNIT" => "TB",
	                "VALUE" => pow(1024, 4)
	            ),
	            1 => array(
	                "UNIT" => "GB",
	                "VALUE" => pow(1024, 3)
	            ),
	            2 => array(
	                "UNIT" => "MB",
	                "VALUE" => pow(1024, 2)
	            ),
	            3 => array(
	                "UNIT" => "KB",
	                "VALUE" => 1024
	            ),
	            4 => array(
	                "UNIT" => "B",
	                "VALUE" => 1
	            ),
	        );

	    foreach ($arBytes as $arItem) {
	        if ($bytes >= $arItem["VALUE"]) {
	            $result = $bytes / $arItem["VALUE"];
	            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
	            break;
	        }
	    }
	    return $result;
	}

	/**
	 * Check if video upladed by addpipe has been moved to applicant files.
	 * @return void
	 * @throws Exception
	 */
	public function is_file_uploaded() {

	   	$db 		= JFactory::getDBO();
	   	$user 		= JFactory::getSession()->get('emundusUser');

		//$secret 	= JFactory::getConfig()->get('secret');
		//$token 		= JFactory::getApplication()->input->get('token', '', 'ALNUM');
		//$fnum 		= JFactory::getApplication()->input->get('fnum', '', 'STRING');
		$aid = JFactory::getApplication()->input->get('aid', '', 'ALNUM');
		$applicant_id = JFactory::getApplication()->input->get('applicant_id', '', 'ALNUM');

		if ($user->id != $applicant_id) {
			JLog::add('Curent user and fnum does not match.', JLog::ERROR, 'com_emundus.webhook');
			echo json_encode((object)(array('status' => false)));
			exit();
		}

		//@TODO manage a specific filename. Will issue if we have more than one video by filetype
		$query = 'SELECT count(id) 
					FROM #__emundus_uploads 
					WHERE attachment_id='.$aid.' AND user_id='.$user->id.' AND fnum LIKE '.$db->Quote($user->fnum);
		try {
            $db->setQuery($query);
            $result = ($db->loadResult() > 0);

            echo json_encode((object)(array('status' => $result)));
            exit();
        } catch (Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage().' :: '.$query;
            JLog::add($error, JLog::ERROR, 'com_emundus.webhook');

		    echo json_encode((object)(array('status' => false)));
			exit();
		}
	}


	/**
	 * @param string $user_email
	 * @param        $param
	 * @param string $value
	 *
	 * @return bool
	 * @since version
	 */
	private function setUserParam(string $user_email, $param, string $value) : bool {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('jos_users'))
			->where($db->quoteName('email').' LIKE '.$db->quote($user_email));
		$db->setQuery($query);

		try {
			$user_id = $db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting user by email when saving param : '.$e->getMessage(), JLog::ERROR, 'com_emundus.yousign');
			return false;
		}

		if (empty($user_id)) {
			JLog::add('User not found', JLog::ERROR, 'com_emundus.yousign');
			return false;
		}

		$user = JFactory::getUser($user_id);

		$table = JTable::getInstance('user', 'JTable');
		$table->load($user->id);

		// Store token in User's Parameters
		$user->setParam($param, $value);

		// Get the raw User Parameters
		$params = $user->getParameters();

		// Set the user table instance to include the new token.
		$table->params = $params->toString();

		// Save user data
		if (!$table->store()) {
			JLog::add('Error saving params : '.$table->getError(), JLog::ERROR, 'com_emundus.yousign');
			return false;
		}
		return true;
	}
    public function export_siscole(){

        $eMConfig 	= JComponentHelper::getParams('com_emundus');
        $filtre_ip  = $eMConfig->get('filtre_ip');
        $filtre_ip  = explode(',',$filtre_ip);
        $secret 	= JFactory::getConfig()->get('secret');
        $token 		= JFactory::getApplication()->input->get('token');
        $fnum 		= JFactory::getApplication()->input->get('rowid');
        $filename   = $eMConfig->get('filename');
        $url        = 'images'.DS.'emundus'.DS.'files'.DS.'archives';
        $file       = JPATH_SITE.DS.$url.DS.$filename.'.csv';
        $date = date('Y-m-d');
        $time_date = date('Y-m-d H:i:s');

        $db = JFactory::getDbo();

        $file_name = basename($file);
        if(isset($_SERVER['HTTP_X_REAL_IP'])){
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if ($token != $secret) {
            JLog::add('Bad token sent.', JLog::ERROR, 'com_emundus.webhook');
            return false;
        }

        $query = $db->getQuery(true);

        $query->select('COUNT(*) as nb_requete, is_downloaded')
            ->from($db->quoteName('#__emundus_files_request'))
            ->where($db->quoteName('attachment_id').' = 77 AND '. $db->quoteName('ip_address'). ' LIKE ' . $db->quote($ip).' AND '. $db->quoteName('time_date'). ' LIKE ' . $db->quote($date.'%'));

        $db->setQuery($query);

        $ip_addess_request = $db->loadAssoc();



        if((in_array($ip,$filtre_ip) && $ip_addess_request['nb_requete'] == 0 && ($ip_addess_request['is_downloaded'] == 1 || $ip_addess_request['is_downloaded'] == null)) || ((in_array($ip,$filtre_ip) && $ip_addess_request['nb_requete'] >= 1 && ($ip_addess_request['is_downloaded'] == 1 || $ip_addess_request['is_downloaded'] == null)))){

            //$mime_type = $this->controller->get_mime_type($file);
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename='.$file_name);
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: anytextexeptno-cache', true);
            header('Cache-control: private');
            header('Expires: 0');

            ob_clean();
            flush();

            //if(file_put_contents($file_name, file_get_contents(JURI::base().$url.DS.$file_name))){
            if(readfile($file)){


                $attachment_id = $eMConfig->get('attachment_id');
                $bytes = random_bytes(32);
                $new_token = bin2hex($bytes);

                JLog::add('File download with the ip address'.$ip, JLog::NOTICE, 'com_emundus.webhook');



                $query = $db->getQuery(true);

                if($ip_addess_request['nb_requete'] == 0){
                    $columns = array('time_date','fnum','keyid', 'attachment_id', 'filename','ip_address','is_downloaded');

                    $values = array($db->quote($time_date),$db->quote($fnum),$db->quote($new_token), 77, $db->quote($filename.$date.'.csv'),$db->quote($ip),0);

                    $query
                        ->insert($db->quoteName('#__emundus_files_request'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));

                    $db->setQuery($query);
                }
                else{
                    $fields = array(
                        $db->quoteName('time_date') . ' = ' . $db->quote($time_date),
                        $db->quoteName('is_downloaded') . ' = 0',
                    );

                    // Conditions for which records should be updated.
                    $conditions = array(
                        $db->quoteName('ip_address') . ' LIKE ' . $db->quote($ip),
                        $db->quoteName('time_date'). ' LIKE ' . $db->quote($date.'%')
                    );
                    $query->update($db->quoteName('#__emundus_files_request'))->set($fields)->where($conditions);
                    $db->setQuery($query);
                }
                try{
                    $db->execute();
                }
                catch (Exception $e){
                    JLog::add('An error occurring in sql request: '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
                }
                exit;
            }
        }
        else {
            JLog::add('Your ip address is blocked', JLog::ERROR, 'com_emundus.webhook');
        }
    }

    public function export_banner(){
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
        $mFile = new EmundusModelFiles;

        $banner_limit = JFactory::getApplication()->input->get('limit', 100);

        $db = JFactory::getDbo();
        $res = new stdClass();

        /* "program - label - semester" mapping --> stocked in "prog" field */
        // standard format: prog=univ,stp,202020|winter-school,wstp,202010

        header('Content-type: application/json');
        try {
            // controle des remontees --> Si is_up_banner = 0 or null, do not call api
            $query = "SELECT e_360_7749 as Nom, e_360_7747 as Prenom, e_360_7746 as Civilite, e_360_7751 as DateNaissance,e_360_7755 as VilleNaissance, dc.code as PaysNaissance, 
                                dn.code as Nationalite, code_prg_banner as Programme, semester as Semestre, ju.email as Email,trim(e_362_7764) as Telephone, e_362_7757 as AdrPersoL1,e_362_7758 as AdrPersoL2,e_362_7760 as AdrPersoCodePost,
                                e_362_7761 as AdrPersoVille, dc2.code as AdrPersoCodePays,                 
                                case
                                    when e.e_394_8112 = 'JYES' then 'Oui'
                                    when e.e_394_8112 = 'JNO' then 'Non'
                                    when e.e_394_8112 is null then 'Non'
                        end as 'UsagePhoto', jecc.fnum as NoClientemundus, 'summer.school@sciencespo.fr' as EmailAssistante, jeu.filename as Photo
                    from #__emundus_1001_00
                    left join #__emundus_campaign_candidature jecc on #__emundus_1001_00.fnum = jecc.fnum
                    left join #__emundus_1001_01 j on #__emundus_1001_00.fnum = j.fnum
                    left join #__emundus_1025_00 e on #__emundus_1001_00.fnum = e.fnum
                    left join data_country dc on #__emundus_1001_00.e_360_7754 = dc.id
                    left join data_country dc2 on j.e_362_7763 = dc2.id
                    left join data_nationality dn on #__emundus_1001_00.e_360_7752 = dn.id
                    left join #__users ju on ju.id = jecc.applicant_id
                    left join #__emundus_uploads jeu on #__emundus_1001_00.fnum = jeu.fnum
                    left join #__emundus_setup_campaigns jesc on jecc.campaign_id = jesc.id
                    where jecc.status = 4 
                      and jesc.is_up_banner = 1
                      and jeu.attachment_id = 10 
                      and (jecc.id_banner is null or jecc.id_banner = '')
            ";

            $db->setQuery($query,0,$banner_limit);

            $raw = $db->loadObjectList();

            $res->Status = 'OK';
            $res->Count = sizeof($raw);
            $res->Message = '';

            /* encode 64 bit images + mapping prog..lbl, prog..session*/
            foreach($raw as $data) {
                // get user_id from $data->noClientemundus
                $fnum_Info = $mFile->getFnumsInfos([$data->NoClientemundus])[$data->NoClientemundus];
                $user_id = $fnum_Info['applicant_id'];

                // get url to image
                $img_url = "images/emundus/files" . DS . $user_id . DS . $data->Photo;

                $handle = fopen($img_url, "r");
                $contents = fread($handle, filesize($img_url));
                fclose($handle);

                $data->Photo = base64_encode($contents);

                if($data->Civilite === 'Femme') {
                    $data->Civilite = 0;
                } else {
                    $data->Civilite = 1;
                }
            }

            $res->Results = $raw;
            echo json_encode((array)$res);
            exit;
        } catch(Exception $e) {
            $res->Status = 'NOK';
            $res->Message = $e->getMessage();
            JLog::add('Cannot get files', JLog::ERROR, 'com_emundus.webhook');
        }
    }

    /**
     * @throws Exception
     */
    public function process_banner() {
//        $request = file_get_contents('php://input');        /// POST method

        $cand_num 		= JFactory::getApplication()->input->get('noClientemundus');
        $cand_idBanner  = JFactory::getApplication()->input->get('IDBanner');

        header('Content-type: application/json');

        if (empty($cand_num) || !isset($cand_idBanner) || (isset($cand_idBanner) and ($cand_idBanner == '')) || (isset($cand_num) and ($cand_num == ''))) {
            JLog::add('BAD_REQUEST_OR_MISSING_PARAMS', JLog::ERROR, 'com_emundus.webhook');
            echo json_encode(array('Status' => 'NOK', 'message' => JText::_('BAD_REQUEST_OR_MISSING_PARAMS')));
        } else {
            $this->update_banner($cand_idBanner, $cand_num);
        }
        exit;
    }

    /* using GET methos */
    public function update_banner($id, $fnum) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            /* check if fnum exists, if yes, update, if no, return error code */
            $query->clear()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $is_exist = $db->loadResult();

            if($is_exist == 1) {
                $query->clear()
                    ->update($db->quoteName('#__emundus_campaign_candidature'))
                    ->set($db->quoteName('#__emundus_campaign_candidature.id_banner') . ' = ' . $db->quote($id))
                    ->where($db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quote($fnum));

                $db->setQuery($query);

                $db->execute();
                echo json_encode(array('Status' => 'OK', 'message' => JText::_('RECORD_UPDATED_SUCCESSFULLY')));
            } else {
                echo json_encode(array('Status' => 'NOK', 'message' => JText::_('NO_CLIENT_NOT_EXIST')));
            }
            exit;
        } catch(Exception $e) {
            JLog::add('Cannot update id banner', JLog::ERROR, 'com_emundus.webhook');
            echo json_encode(array('Status' => 'NOK', 'message' => JText::_('RECORD_UPDATED_FAILED')));
            exit;
        }
    }

    /* get zoom session by id */
    public function getzoomsession() {
        $tab = array('status' => false, 'msg' => JText::_('ZOOM_SESION_RETRIEVED_FAILED'), 'data' => null);
        $jinput = JFactory::getApplication()->input;
        $zid = $jinput->getString('zid', null);

        if (!empty($zid)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*')
                ->from($db->quoteName('#__emundus_jury', 'jej'))
                ->where('jej.id = ' . $db->quote($zid));
            $db->setQuery($query);

            $raw = $db->loadObject();

            if (!empty($raw)) {
                $tab = array('status' => true, 'msg' => JText::_('ZOOM_SESION_RETRIEVED_SUCCESSFULLY'), 'data' => $raw);
            }
        }

        echo json_encode($tab);
        exit;
    }

    /**
     * POST method
     * Waiting for :
     *  - callback_id
     *  - amount
     *  - at
     *  - status
     * @return string json_encoded
     */
    public function updateFlywirePaymentInfos()
    {
        $status = false;
        $msg = JText::_('FLYWIRE_PAYMENT_INFOS_UPDATED_FAILED');

        $post_data = file_get_contents('php://input');
        $data = json_decode($post_data, true);

        if (!empty($data['callback_id']) && !empty($data['status']) && !empty($data['amount'])) {
            require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'payment.php');
            $m_payment = new EmundusModelPayment;
            $status = $m_payment->updateFlywirePaymentInfos($data['callback_id'], $data);

            if ($status) {
                $msg = JText::_('FLYWIRE_PAYMENT_INFOS_UPDATED_SUCCESSFULLY');
            }
        } else {
            JLog::addLogger(['text_file' => 'com_emundus.payment.php'], JLog::ALL, array('com_emundus.payment'));
            JLog::add('[updateFlywirePaymentInfos] BAD_REQUEST_OR_MISSING_PARAMS - Malicious attempt ? Sender : ' . $_SERVER['REMOTE_ADDR'] .  ' data : ' . json_encode($data), JLog::ERROR, 'com_emundus.payment');
            header('HTTP/1.1 400 Bad Request');
            echo 'Error 400 - Bad Request';
            die();
        }

        header('Content-type: application/json');
        echo json_encode(array('status' => $status, 'msg' => $msg));
        exit;
    }

    public function updateAxeptaPaymentInfos(){
        JLog::addLogger(['text_file' => 'com_emundus.payment.php'], JLog::ALL, array('com_emundus.payment'));

        try {
            JLog::add('[updateAxeptaPaymentInfos] Start to get payment notification from axepta', JLog::INFO, 'com_emundus.payment');

            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'payment'.DS.'Axepta.php');
            $axepta = new Axepta();

            $status = false;
            $msg = JText::_('AXEPTA_PAYMENT_INFOS_UPDATED_FAILED');

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $blowfish_key = $eMConfig->get('axepta_blowfish_key','Tc5*2D_xs7B[6E?w');

            $data = $_POST["Data"];
            $len = $_POST["Len"];
            $plaintext = $axepta->ctDecrypt($data, $len, $blowfish_key);

            $a = "";
            $a = explode('&', $plaintext);
            $info = $axepta->ctSplit($a, '=');
            $TransID = $axepta->ctSplit($a, '=', 'TransID');
            $Status = $axepta->ctSplit($a, '=', 'Status');
            $PayID = $axepta->ctSplit($a, '=', 'PayID');
            $Type = $axepta->ctSplit($a, '=', 'Type');
            $UserData = $axepta->ctSplit($a, '=', 'UserData');

            // check transmitted decrypted status
            $realstatus = $axepta->ctRealstatus($Status);

            JLog::add('[updateAxeptaPaymentInfos] Get payment from Axepta with status : ' . $Status, JLog::INFO, 'com_emundus.payment');

            if (!empty($TransID) && !empty($Status) && !empty($PayID)) {
                require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'payment.php');
                $m_payment = new EmundusModelPayment();
                $status = $m_payment->updateAxeptaPaymentInfos($TransID,$Status,$PayID);

                if ($status) {
                    $msg = JText::_('AXEPTA_PAYMENT_INFOS_UPDATED_SUCCESSFULLY');
                }
            } else {
                JLog::add('[updateAxeptaPaymentInfos] BAD_REQUEST_OR_MISSING_PARAMS - Malicious attempt ? Sender : ' . $_SERVER['REMOTE_ADDR'] .  ' data : ' . json_encode($data), JLog::ERROR, 'com_emundus.payment');
                header('HTTP/1.1 400 Bad Request');
                echo 'Error 400 - Bad Request';
                die();
            }
        } catch (Exception $e) {
            JLog::add('[updateAxeptaPaymentInfos] BAD_REQUEST_OR_MISSING_PARAMS - Malicious attempt ? Sender : ' . $_SERVER['REMOTE_ADDR'] .  ' with error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
        }


        header('Content-type: application/json');
        echo json_encode(array('status' => $realstatus, 'msg' => $msg));
        exit;
    }
}
