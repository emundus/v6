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
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controller.php');

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


	/**
	 * Downloads the file associated to the YouSign procedure that was pushed.
	 */
	public function yousign() {

		// For some absolutely MAGICAL reason, webhook data does not appear in $_POST or $jinput->post
		// It does appear however with file_get_contents('php://input'), so we're using that.
		$post = json_decode(file_get_contents('php://input'));
		if ($post === null) {
			JLog::add('YouSign bad JSON : '.file_get_contents('php://input'), JLog::ERROR, 'com_emundus.webhook');
			return;
		}

		JLog::add('Reveived WebHook : '.print_r(file_get_contents('php://input'), true), JLog::INFO, 'com_emundus.webhook');

		// 'procedure.finished' runs when all signatures are done and blissful harmony is restored to the universe.
		if ($post->eventName === 'procedure.finished') {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$eMConfig = JComponentHelper::getParams('com_emundus');

			$procedure = $post->procedure;

			JLog::add('YouSign procedure : '.print_r($procedure, true), JLog::INFO, 'com_emundus.webhook');

			// Now that the procedure is signed, we can remove the member ID used for loading the iFrame.
			foreach ($procedure->members as $member) {
				$this->setUserParam($member->email, 'yousignMemberId', false);
			}

			$files = [];
			foreach ($procedure->files as $file) {
				$files[] = $file->id;
				JLog::add('YouSign procedure file : '.$file->id, JLog::INFO, 'com_emundus.webhook');
			}

			$query->clear()
				->select([$db->quoteName('fr.fnum'), $db->quoteName('a.lbl'), $db->quoteName('fr.attachment_id')])
				->from($db->quoteName('jos_emundus_files_request', 'fr'))
				->leftJoin($db->quoteName('jos_emundus_setup_attachments', 'a').' ON '.$db->quoteName('fr.attachment_id').' = '.$db->quoteName('a.id'))
				->where($db->quoteName('filename').' IN ("'.implode('","', $files).'")');
			$db->setQuery($query);
			try {
				$attachments = $db->loadObjectList();
			} catch (Exception $e) {
				JLog::add('Could not load files_requests : '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
				return;
			}

			$http = new JHttp();

			$host = $eMConfig->get('yousign_prod', 'https://staging-api.yousign.com');
			$api_key = $eMConfig->get('yousign_api_key', 'https://staging-api.yousign.com');
			if (empty($host) || empty($api_key)) {
				JLog::add('Missing YouSign info.', JLog::ERROR, 'com_emundus.webhook');
				return;
			}

			$frQuery = $db->getQuery(true);
			foreach ($files as $file) {

				// Time to download the files from the WebService.
				$response = $http->get($host.$file.'/download?alt=media', [
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$api_key
				]);

				// File exists, time to write it to the right place.
				if ($response->code === 200) {

					// Prepare the query to save the files into the upload table.
					$query->clear()
						->insert($db->quoteName('jos_emundus_uploads'))
						->columns($db->quoteName(['user_id', 'fnum', 'campaign_id', 'attachment_id', 'filename', 'description', 'can_be_deleted', 'can_be_viewed']));

					$success = [];
					foreach ($attachments as $attachment) {

						// Save the signed file into the users folder.
						$fileName = $attachment->lbl.'_signed.pdf';
						$uid = (int)substr($attachment->fnum, -7);
						if (file_put_contents(EMUNDUS_PATH_ABS.$uid.DS.$fileName, $response->body) !== false) {

							// Set the filerequest as uploaded.
							$frQuery->clear()
								->update($db->quoteName('jos_emundus_files_request'))
								->set([
									$db->quoteName('uploaded').' = 1',
									$db->quoteName('signed_file').' = '.$db->quote($fileName)
								])
								->where($db->quoteName('filename').' LIKE '.$db->quote($file).'');
							$db->setQuery($frQuery);
							try {
								$db->execute();
							} catch (Exception $e) {
								JLog::add('Could not update files_requests : '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
								return;
							}

							$success[] = $attachment->fnum;
							$query->values(implode(',', [$uid, $db->quote($attachment->fnum), (int)substr($attachment->fnum, 14, 7), $attachment->attachment_id, $db->quote($fileName), $db->quote('YouSign signed document'), '0', '0']));

						} else {
							JLog::add('Error downloading file from YouSign -> RESPONSE ('.$response->code.') '.$response->body, JLog::ERROR, 'com_emundus.webhook');
						}
					}

					// Link the files to the users' files.
					$db->setQuery($query);
					try {
						$db->execute();
						JLog::add('Saved YouSigned saved file "'.$file.'" to fnums : '.implode(', ', $success), JLog::INFO, 'com_emundus.webhook');
					} catch (Exception $e) {
						JLog::add('Error adding attachemnts to files: '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
					}
				}
			}
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
			$payload = $_POST["payload"];

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
        $file       = JPATH_BASE.DS.$url.DS.$filename.'.csv';
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
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
        $mFile = new EmundusModelFiles;

        $eMConfig 	= JComponentHelper::getParams('com_emundus');
        $banner_limit = JFactory::getApplication()->input->get('limit', 100);

        $db = JFactory::getDbo();
        $res = new stdClass();

        /* "program - label - semester" mapping --> stocked in "prog" field */
        // standard format: prog=univ,stp,202020|winter-school,wstp,202010

        header('Content-type: application/json');
        try {
            // controle des remontees --> Si is_up_banner = 0 or null, do not call api
            $query = "SELECT    e_360_7747 as nom, e_360_7749 as prenom, e_360_7746 as civilite, e_360_7751 as dateNaissance,e_360_7755 as villeNaissance, label_fr as paysNaissance, 
                                e_360_7752 as nationalite, ju.email as email,trim(e_362_7764) as telephone, e_362_7757 as adrPersoL1,e_362_7758 as adrPersoL2,e_362_7760 as adrPersoCodePost,
                                e_362_7761 as adrPersoVille, e_362_7763 as adrPersoCodePays, jecc.fnum as noClientemundus, 'summer.school@sciencepo.fr' as emailAssistante, filename as photo, code_prg_banner as programme, semester as semestre,
                        case
                            when e.e_394_8112 = 'JYES' then 'Oui'
                            when e.e_394_8112 = 'JNO' then 'Non'
                            when e.e_394_8112 is null then 'Non'
                        end as 'usagePhoto'
                               
                    from #__emundus_1001_00
                    left join #__emundus_campaign_candidature jecc on #__emundus_1001_00.fnum = jecc.fnum
                    left join data_country dc on #__emundus_1001_00.e_360_7754 = dc.id
                    left join #__users ju on ju.id = jecc.applicant_id
                    left join #__emundus_1001_01 j on #__emundus_1001_00.fnum = j.fnum
                    left join #__emundus_1025_00 e on #__emundus_1001_00.fnum = e.fnum
                    left join #__emundus_uploads jeu on #__emundus_1001_00.fnum = jeu.fnum
                    left join #__emundus_setup_campaigns jesc on jecc.campaign_id = jesc.id
                    where jecc.status = 4 
                      and jesc.is_up_banner = 1
                      and jeu.attachment_id = 10 
                      and (jecc.id_banner is null or jecc.id_banner = '')
            ";

            $db->setQuery($query,0,$banner_limit);

            $raw = $db->loadObjectList();

            $res->status = 'OK';
            $res->count = sizeof($raw);
            $res->message = '';

            /* encode 64 bit images + mapping prog..lbl, prog..session*/
            foreach($raw as $data) {
                // get user_id from $data->noClientemundus
                $fnum_Info = $mFile->getFnumsInfos([$data->noClientemundus])[$data->noClientemundus];
                $user_id = $fnum_Info['applicant_id'];

                // get url to image
                $img_url = "images/emundus/files" . DS . $user_id . DS . $data->photo;

                $handle = fopen($img_url, "r");
                $contents = fread($handle, filesize($img_url));
                fclose($handle);

                $data->photo = base64_encode($contents);
            }

            $res->results = $raw;
            echo json_encode((array)$res);
            exit;
        } catch(Exception $e) {
            $res->status = 'NOK';
            $res->message = $e->getMessage();
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

        if (empty($cand_num) || empty($cand_idBanner)) {
            JLog::add('BAD_REQUEST_OR_MISSING_PARAMS', JLog::ERROR, 'com_emundus.webhook');
            echo json_encode(array('status' => 400, 'message' => JText::_('BAD_REQUEST_OR_MISSING_PARAMS')));
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
            $query->update($db->quoteName('#__emundus_campaign_candidature'))
                ->set($db->quoteName('#__emundus_campaign_candidature.id_banner') . ' = ' . $db->quote($id))
                ->where($db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quote($fnum));

            $db->setQuery($query);
            $db->execute();
            echo json_encode(array('status' => 200, 'message' => JText::_('RECORD_UPDATED_SUCCESSFULLY')));
            exit;
        } catch(Exception $e) {
            JLog::add('Cannot update id banner', JLog::ERROR, 'com_emundus.webhook');
        }
    }
}
