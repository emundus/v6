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

		parent::__construct($config);
        // Attach logging system.
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.webhook.php'], JLog::ALL, array('com_emundus.webhook'));
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
	 * @return boolean
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
        $file_name = basename($file);

        $ip = $_SERVER['REMOTE_ADDR'];

        if ($token != $secret) {

            JLog::add('Bad token sent.', JLog::ERROR, 'com_emundus.webhook');
            return false;
        }

        if(in_array($ip,$filtre_ip)){

            $mime_type = $this->controller->get_mime_type($file);
            header('Content-type: '.$mime_type);
            header('Content-Disposition: inline; filename='.basename($file));
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: anytextexeptno-cache', true);
            header('Cache-control: private');
            header('Expires: 0');

            ob_clean();
            flush();

            if(file_put_contents($file_name,file_get_contents($file))){
                $date = date('Y-m-d');
                $attachment_id = $eMConfig->get('attachment_id');
                $bytes = random_bytes(32);
                $new_token = bin2hex($bytes);
                $db = JFactory::getDbo();

                $query = $db->getQuery(true);

                $columns = array('fnum','keyid', 'attachment_id', 'filename');

                $values = array($db->quote($fnum),$db->quote($new_token), $attachment_id, $db->quote($filename.$date.'.csv'));

                $query
                    ->insert($db->quoteName('#__emundus_files_request'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                try{
                    $db->execute();
                }
                catch (Exception $e){
                    JLog::add('An error occurring in sql request: '.$e->getMessage(), JLog::ERROR, 'com_emundus.webhook');
                }

                JLog::add('File download with the ip address'.$ip, JLog::NOTICE, 'com_emundus.webhook');
            }
        }
        else {
            JLog::add('Your ip address is blocked', JLog::ERROR, 'com_emundus.webhook');
        }
    }
}