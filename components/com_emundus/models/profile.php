<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelProfile extends JModelList
{
	var $_db = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		$this->_db = JFactory::getDBO();
	}

	/**
	* Gets the greeting
	* @return string The greeting to be displayed to the user
	*/
	function getProfile($p)
	{
		$query = 'SELECT * FROM #__emundus_setup_profiles WHERE id='.mysql_real_escape_string($p);
		
		try
        {
            $this->_db->setQuery( $query );
			return $this->_db->loadObject();
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	/**
     * @return mixed
     */
    public function getApplicantsProfiles()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * 
        			FROM #__emundus_setup_profiles esp
                 	WHERE esp.published=1  AND status=1 
                  	ORDER BY esp.label';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

	function getProfileByApplicant($aid)
	{
		$query = 'SELECT eu.firstname, eu.lastname, eu.profile, eu.university_id, 
							esp.label AS profile_label, esp.menutype, esp.published
						FROM #__emundus_users AS eu 
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile 
						WHERE eu.user_id = '.$aid;
		
		try
        {
            $this->_db->setQuery( $query );
			return $this->_db->loadAssoc();
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}
	
	function getAttachments($p)
	{
		$query = 'SELECT attachment.*, profile.id AS selected, profile.displayed, profile.mandatory, profile.bank_needed 
					FROM #__emundus_setup_attachments AS attachment
					LEFT JOIN #__emundus_setup_attachment_profiles AS profile ON profile.attachment_id = attachment.id AND profile.profile_id='.mysql_real_escape_string($p).' 
					WHERE attachment.published=1 
					ORDER BY attachment.ordering';
		
		try
        {
            $this->_db->setQuery( $query );
			return $this->_db->loadObjectList();
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}
	
	function getForms($p)
	{
		$query = 'SELECT fbtable.id, fbtable.label, menu.id>0 AS selected, menu.lft AS `order` FROM #__fabrik_lists AS fbtable 
					LEFT JOIN #__menu AS menu ON fbtable.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					AND menu.menutype=(SELECT profile.menutype FROM #__emundus_setup_profiles AS profile WHERE profile.id = '.mysql_real_escape_string($p).')
					WHERE fbtable.created_by_alias = "form" ORDER BY selected DESC, menu.lft ASC, fbtable.label ASC';
		
		try
        {
            $this->_db->setQuery( $query );
			return $this->_db->loadObjectList();
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}
	
	function isProfileUserSet($uid) {
		$query = 'SELECT count(user_id) as cpt, profile FROM #__emundus_users WHERE user_id = '.$uid. ' GROUP BY user_id';
		
		try
        {
            $this->_db->setQuery( $query );
			$res = $this->_db->loadAssocList();

			return $res[0];
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function updateProfile($uid, $campaign) {
		$query = 'UPDATE #__emundus_users SET profile='.$campaign->profile_id.', schoolyear="'.$campaign->year.'" WHERE user_id='.$uid;
		
		try
        {
            $this->_db->setQuery( $query ); 
			return $this->_db->execute();
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function getCurrentCampaignByApplicant($uid) {
		$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id = '.$uid. ' ORDER BY date_time DESC';
		
		try
        {
            $this->_db->setQuery( $query );
			$res = $this->_db->loadResult();

			return $res;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function getCurrentIncompleteCampaignByApplicant($uid) {
		$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE (submitted=0 OR submitted IS NULL) AND applicant_id = '.$uid. ' ORDER BY date_time DESC';

		try
        {
			$this->_db->setQuery( $query );
			$res = $this->_db->loadResult();

			return $res;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function getCurrentCompleteCampaignByApplicant($uid) {
		$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE submitted=1 AND applicant_id = '.$uid. ' ORDER BY date_time DESC';
		
		try
        {
			$this->_db->setQuery( $query );
			$res = $this->_db->loadResult();

			return $res;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function getCurrentCampaignInfoByApplicant($uid) {
		$query = 'SELECT esc.*, ecc.date_time, ecc.submitted, ecc.date_submitted, ecc.fnum, esc.profile_id, esp.label, esp.menutype, ecc.submitted, ecc.status
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON ecc.campaign_id = esc.id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.applicant_id = '.$uid. ' ORDER BY ecc.date_time DESC';
		
		try
        {
			$this->_db->setQuery( $query );
			$res = $this->_db->loadAssoc();

			return $res;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function getCampaignById($id) {
		$query = 'SELECT * FROM  #__emundus_setup_campaigns AS esc WHERE id='.$id;
		
		try
        {
			$this->_db->setQuery( $query );
			$res = $this->_db->loadAssoc();

			return $res;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

	function getProfileByCampaign($id) {
		$query = 'SELECT esp.*, esc.* 
					FROM  #__emundus_setup_profiles AS esp 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.profile_id = esp.id
					WHERE esc.id='.$id;
		
		try
        {
			$this->_db->setQuery( $query );
			$res = $this->_db->loadAssoc();
			return $res;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}

		/**
	* Gets the list of profiles from array of programmes
	* @param 	$code 	array 	list of programmes code
	* @return  	string The greeting to be displayed to the user
	*/
	function getProfileIDByCourse($code = array()) {
		if (count($code)>0) {
			$query = 'SELECT DISTINCT(esc.profile_id)
						FROM  #__emundus_setup_campaigns AS esc 
						WHERE esc.training IN ("'.implode('","', $code).'")';		
			try
	        {
	            $this->_db->setQuery( $query ); 
				$res = $this->_db->loadColumn();
	        }
	        catch(Exception $e)
	        {
	            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            	JError::raiseError(500, $e->getMessage());
	        }
		} 
		else 
			$res = $code;
		
		return $res;
	}

	/**
	* Gets the list of profiles from array of programmes
	* @param 	$code 	array 	list of programmes code
	* @return  	string The greeting to be displayed to the user
	*/
    function getProfileIDByCampaign($campaign_id) {
        if (count($campaign_id)>0) {
            if (in_array('%', $campaign_id))
                $where = '';
            else
                $where = 'WHERE esc.id IN ('.implode(',', $campaign_id).')';

            $query = 'SELECT DISTINCT(esc.profile_id)
						FROM  #__emundus_setup_campaigns AS esc '.$where;
           
            try
	        {
	            $this->_db->setQuery( $query );
            	$res = $this->_db->loadColumn();
	        }
	        catch(Exception $e)
	        {
	            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            	JError::raiseError(500, $e->getMessage());
	        }
        }
        else
            $res = false;
        
        return $res;
    }

	function getFnumDetails($fnum){
		$query = 'SELECT ecc.*, esc.*, ess.*, epd.profile as profile_id_form
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id 
					LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
					LEFT JOIN #__emundus_personal_detail as epd on epd.fnum = ecc.fnum
					WHERE ecc.fnum like '.$this->_db->Quote($fnum); 
		try
        {
            $this->_db->setQuery( $query );
			$res = $this->_db->loadAssoc();
        }
        catch(Exception $e)
        {
            $query = 'SELECT ecc.*, esc.*, ess.*
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id 
					LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
					LEFT JOIN #__emundus_personal_detail as epd on epd.fnum = ecc.fnum
					WHERE ecc.fnum like '.$this->_db->Quote($fnum); 
			try
	        {
	            $this->_db->setQuery( $query );
				$res = $this->_db->loadAssoc();
	        }
	        catch(Exception $e)
	        {
	            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
	            JError::raiseError(500, $e->getMessage());
	        }
        }

        return $res;
	}

	function getCandidatureByFnum($fnum) {
		return $this->getFnumDetails($fnum);
	}

	function isApplicationDeclared($aid) {
		$query = 'SELECT COUNT(*) FROM #__emundus_declaration WHERE user = '.$aid;
		
		try
        {
			$this->_db->setQuery( $query );
			$res = $this->_db->loadResult();
			return $res>0?true:false;
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
	}


    /**
     * Get fnums for applicants
     * @param int $aid               Applicant ID
     * @param int $submitted         Submitted application
     * @param date $start_date       campaigns as started after
     * @param date $end_date         campaigns as ended before
     * @return array
     * @throws Exception
     */
    public function getApplicantFnums($aid, $submitted = null, $start_date = null, $end_date = null){
        $db = JFactory::getDBO();

        $query = 'SELECT ecc.*, esc.label, esc.start_date, esc.end_date, esc.training, esc.year, esc.profile_id
                    FROM #__emundus_campaign_candidature as ecc 
                    LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id 
                    WHERE ecc.published=1 AND ecc.applicant_id='.$aid;
        $query .= (!empty($submitted))?' AND ecc.submitted='.$submitted:'';
        $query .= (!empty($start_date))?' AND esc.start_date<='.$db->Quote($start_date):'';
        $query .= (!empty($end_date))?' AND esc.end_date>='.$db->Quote($end_date):'';

        try
        {
            $db->setQuery($query);
            return $db->loadObjectList('fnum');
        }
        catch(Exception $e)
        {
            JLog::add(JUri::getInstance().' :: fct : getAttachmentsById :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

}
?>