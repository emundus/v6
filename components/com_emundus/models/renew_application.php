<?php
/**
 * Profile Model for eMundus Component
 *
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class EmundusModelRenew_application extends JModelList
{
	var $_db = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct($model = 'renew_application')
	{
		parent::__construct();
		$this->_db   = JFactory::getDBO();
		$this->_user = JFactory::getUser();
	}

	function getSchoolyear($profile)
	{
		$query = 'SELECT year as schoolyear 
				FROM #__emundus_setup_campaigns
				WHERE id = ' . $profile;
		$this->_db->setQuery($query);

		return $this->_db->loadResult();
	}

	/*
		public function getStatut(){
			$query = 'SELECT *
					FROM #__emundus_setup_profiles esp
					LEFT JOIN #__emundus_users eu ON eu.profile = esp.id
					WHERE eu.user_id = '.$this->_user->id.'
					AND esp.published = 1';
			$this->_db->setQuery( $query );
			$this->_db->query();
			if($this->_db->getNumRows() == 1) return true;
			else return false;
		}
	*/
	function isCompleteApplication($user)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__emundus_declaration WHERE user=' . $user);
		$db->query();
		if ($db->getNumRows() == 1) return true;
		else return false;
	}

	function getLinkAttachments($attachment_id, $user)
	{
		$query = 'SELECT filename FROM #__emundus_uploads WHERE attachment_id = ' . $attachment_id . ' AND user_id =' . $user;
		$this->_db->setQuery($query);

		return $this->_db->loadResultArray();
	}

	function deleteAttachment($filename)
	{
		//delete upload
		$query = 'DELETE FROM #__emundus_uploads WHERE filename="' . $filename . '"';
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteFileRequest($user)
	{
		//delete file requests
		$query = 'DELETE FROM #__emundus_files_request WHERE student_id=' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteReferences($user)
	{
		//delete file requests
		$query = 'DELETE FROM #__emundus_references WHERE user=' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteEvaluations($user)
	{
		//delete evaluations 
		$query = 'DELETE FROM #__emundus_evaluations WHERE student_id=' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteFinal_grade($user)
	{
		//delete final grade 
		$query = 'DELETE FROM #__emundus_final_grade WHERE student_id=' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteDeclaration($user)
	{
		//delete declaration 
		//$query = 'DELETE FROM #__emundus_declaration WHERE user='.$user;
		$query = 'UPDATE #__emundus_declaration SET time_date = "0000-00-00 00:00:00" WHERE user = ' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteGroups_eval($user)
	{
		//delete groups or single evaluator(s)
		$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id=' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function deleteTraining($user)
	{
		//delete groups or single evaluator(s)
		$query = 'DELETE FROM #__emundus_training WHERE user=' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function updateUser($user, $profile)
	{
		$schoolyear = $this->getSchoolyear($profile);
		$query      = 'UPDATE #__emundus_users SET schoolyear = "", profile="0" WHERE user_id = ' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	function updateAttachments($user)
	{
		$query = 'UPDATE #__emundus_uploads SET can_be_deleted = 1 WHERE user_id = ' . $user;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}
}