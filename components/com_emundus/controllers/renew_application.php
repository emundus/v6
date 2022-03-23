<?php
/**
 * @version		$Id: application_form.php 750 2012-01-23 22:29:38Z brivalland $
 * @package		Joomla
 * @copyright	(C) 2005 - 2008 JXtended LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

/**
 * Custom report controller
 * @package		Emundus
 */
class EmundusControllerRenew_application extends JControllerLegacy
{
	//var $_model = null;
	//$this->_model = $this->getModel( 'renew_application' );

	function display($cachable = false, $urlparams = false) {
		$user = JFactory::getUser();
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'renew_application';
			JRequest::setVar('view', $default );
		}
		if ($user != JRequest::getVar('uid', null, 'GET', 'none',0)) die(JText::_("ACCES_DENIED"));
		parent::display();
	}

	/**
	 * export ZIP
	 */

	function export_zip(){
		$user = JRequest::getVar('uid', null, 'GET', 'none',0);
		$current_user = JFactory::getUser();
		if ($user == $current_user->id) {
			require_once('libraries/emundus/zip.php');
			zip_file(array($user));
			unlink(EMUNDUS_PATH_ABS.$user.DS.'application.pdf');
		} else {
			$this->setRedirect('index.php', JText::_('ACCES_DENIED'), 'message');
			exit();
		}
	}

	/**
	 * Cancel renew. Come back to previous application
	 */
	function cancel_renew(){
		$session = JFactory::getSession();
		$current_user = $session->get('emundusUser');
		$user = JFactory::getUser();
		$profile = $this->getModel('profile');
		$campaign = $this->getModel('campaign');

		$previous_profiles = $campaign->getCampaignByApplicant($user->id);

		if (count($previous_profiles) > 0) {
			$profile->updateProfile($current_user->id, $previous_profiles[0]);
			//$current_user->firstname 			= @$res->firstname;
			//$current_user->lastname	 			= @$res->lastname;
			$current_user->profile	 			= $previous_profiles[0]->profile_id;
			$current_user->profile_label 		= $previous_profiles[0]->profile_label;
			$current_user->menutype	 			= $previous_profiles[0]->menutype;
			//$current_user->university_id		= "";
			$current_user->applicant			= 1;
			$current_user->candidature_start	= $previous_profiles[0]->start_date;
			$current_user->candidature_end		= $previous_profiles[0]->end_date;
			$current_user->candidature_posted 	= 1;
			$current_user->schoolyear			= $previous_profiles[0]->year;
			$current_user->campaign_id			= $previous_profiles[0]->id;
			$current_user->fnum					= $previous_profiles[0]->fnum;
		}

		//$session->restart();
		$session->set('emundusUser',$current_user);
		$this->setRedirect('index.php', JText::_('COM_EMUNDUS_APPLICATION_RENEW_CANCEL'), 'message');

	}

	/**
	 * File new application. Define what to do
	 */
	function new_application() {
		$current_user 	= JFactory::getSession()->get('emundusUser');
		$model 			= $this->getModel('renew_application');
		$application 	= $this->getModel('application');
		$user 			= JRequest::getVar('uid', null, 'GET', 'none',0);
		$profile 		= JRequest::getVar('up', null, 'GET', 'none',0);

		if (empty($user) || !isset($user))
			$user = JFactory::getUser()->id;
		if (empty($up) || !isset($up))
			$profile = 0;

		//1.update the applicant's schoolyear to empty and profile to 0
		$model->updateUser($user, $profile);

		//2.add a comment
		$row = array(	'applicant_id'	=>	$current_user->id,
						'user_id'		=>	62,
						'reason'		=>	JText::_('COM_EMUNDUS_APPLICATION_FILE_NEW_APPLICATION'),
						'comment_body'	=>	$current_user->schoolyear.' : '.$current_user->campaign_name.' ('.$current_user->campaign_id.')',
						'fnum'			=> 	$current_user->fnum
					);
		$application->addComment($row);

		$current_user->profile	 			= 0;
		$current_user->profile_label 		= "";
		$current_user->candidature_posted 	= 0;
		$current_user->schoolyear			= "";
		$current_user->campaign_id			= 0;
		$current_user->menutype				= "";
		$current_user->fnum					= null;

		$this->setRedirect('index.php', JText::_('COM_EMUNDUS_APPLICATION_FILE_NEW_APPLICATION_OK'), 'message');

	}

	/**
	 * Renew application. Define what to do/delete
	 */
	function edit_user(){
		$session 		= JFactory::getSession();
		$current_user 	= $session->get('emundusUser');
		$model 			= $this->getModel('renew_application');
		$application 	= $this->getModel('application');
		$user 			= JRequest::getVar('uid', null, 'GET', 'none',0);
		$profile 		= JRequest::getVar('up', null, 'GET', 'none',0);

		//1.generated zip file & application pdf file
		$this->export_zip();

		//2.delete application forms
		$this->deleteApplication();

		//3.delete all about references
		$this->deleteReferents();

		//4.delete others informations about the applicant
		if ($model->isCompleteApplication($user))
			$this->deleteInformations();

		//5.update the applicant's schoolyear
		$model->updateUser($user, $profile);

		//6.make attachments editable
		$model->updateAttachments($user);

		//7.add a comment
		$row = array(	'applicant_id'	=>	$current_user->id,
						'user_id'		=>	62,
						'reason'		=>	JText::_('COM_EMUNDUS_APPLICATION_RENEW_APPLICATION'),
						'comment_body'	=>	$current_user->schoolyear.' : '.$current_user->campaign_name.' ('.$current_user->campaign_id.')'
					);
		$application->addComment($row);


		//
		//$current_user->firstname 			= @$res->firstname;
		//$current_user->lastname	 			= @$res->lastname;
		$current_user->profile	 			= 0;
		$current_user->profile_label 		= "";
		//$current_user->menutype	 			= "";
		//$current_user->university_id		= "";
		//$current_user->applicant			= 1;
		//$current_user->candidature_start	= "";
		//$current_user->candidature_end		= "";
		$current_user->candidature_posted 	= 0;
		$current_user->schoolyear			= "";
		$current_user->campaign_id			= 0;

		$session->set('emundusUser',$current_user);
		$this->setRedirect('index.php', sprintf(JText::_('COM_EMUNDUS_APPLICATION_RENEW_OK'), $model->getSchoolyear($profile)), 'message');

	}

	//Supprimer ce qui correspond aux r�f�rents (+learning agreement) ==> OKOKOKOKOKOK
	function deleteReferents(){
		$user = JRequest::getVar('uid', null, 'GET', 'none',0);
		$model = $this->getModel('renew_application');
		$files_name = '';

		//first reference letter
		$file = $model->getLinkAttachments(4, $user);
		if (!empty($file))
			$files_name = implode(",",$file);

		//Second reference letter
		$file = $model->getLinkAttachments(6, $user);
		if (!empty($file))
			$files_name .= ','.implode(',',$file);

		//optionnal reference letter
		$file = $model->getLinkAttachments(21, $user);
		if (!empty($file))
			$files_name .= ','.implode(',',$file);

		//Learning agreement
		$file = $model->getLinkAttachments(22, $user);
		if(!empty($file))
			$files_name .= ','.implode(',',$file);

		//list of files to delete
		$array_files_name = explode(',',$files_name);

		if (!empty($files_name)){
			foreach ($array_files_name as $filename){
				//delete in database
				$model->deleteAttachment($filename);
				//delete file if exist
				if (file_exists(EMUNDUS_PATH_ABS.$user.DS.$filename)){
					unlink(EMUNDUS_PATH_ABS.$user.DS.$filename);
					//echo EMUNDUS_PATH_ABS.$user.DS.$filename.' OK OK';
				}
				//echo '<br /><br />';
			}
		}

		//delete file request & references form
		$model->deleteFileRequest($user);
		$model->deleteReferences($user);
	}

	//supprimer ce qui correspond aux applications forms ==> OKOKOKOKOKOKOKOKOKOK
	function deleteApplication(){
		$user = JRequest::getVar('uid', null, 'GET', 'none',0);
		$model = $this->getModel('renew_application');
		$files_name = $model->getLinkAttachments(26, $user);

		foreach($files_name as $filename){
			//delete in database
			$model->deleteAttachment($filename);
			//delete file if exist
			if(file_exists(EMUNDUS_PATH_ABS.$user.DS.$filename)) unlink(EMUNDUS_PATH_ABS.$user.DS.$filename);
		}
		//delete application.pdf (pdf generated by applicant)
		if(file_exists(EMUNDUS_PATH_ABS.$user.DS.'application.pdf')) unlink(EMUNDUS_PATH_ABS.$user.DS.'application.pdf');
	}

	function deleteInformations(){
		$user = JRequest::getVar('uid', null, 'GET', 'none',0);
		$model = $this->getModel('renew_application');
		//$model->deleteEvaluations($user);
		//$model->deleteFinal_grade($user);
		$model->deleteDeclaration($user);
		$model->deleteGroups_eval($user);
		$model->deleteTraining($user);
	}

}
