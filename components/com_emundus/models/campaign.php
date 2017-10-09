<?php
/**
 * eMundus Campaign model
 * 
 * @package    	Joomla
 * @subpackage 	eMundus
 * @link       	http://www.emundus.fr
 * @copyright	Copyright (C) 2016 eMundus. All rights reserved.
 * @license    	GNU/GPL
 * @author     	Benjamin Rivalland
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

class EmundusModelCampaign extends JModelList
{
	var $_user = null;
	var $_db = null;

	function __construct()
	{
		parent::__construct();
		global $option;
		
		$mainframe = JFactory::getApplication();
		
		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getSession()->get('emundusUser');
		
		// Get pagination request variables
		$filter_order			= $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'label', 'cmd' );
        $filter_order_Dir		= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
        $limit 					= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart 			= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $limitstart 			= ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
 		$this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}

	function getActiveCampaign()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		//echo str_replace('#_', 'jos',$query).'<br /><br />';
		return $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
	} 
	
	function _buildQuery() {
		$config = JFactory::getConfig();
		
		$jdate = JFactory::getDate();
        $timezone = new DateTimeZone( $config->get('offset') );
    	$jdate->setTimezone($timezone);
		$now = $jdate->toSql();

		$query = 'SELECT id, label, year, description, start_date, end_date 
		FROM #__emundus_setup_campaigns 
		WHERE published = 1 AND '.$now.'>=start_date AND '.$now.'<=end_date';
		return $query;
	}
	
	function _buildContentOrderBy()
	{ 
        global $option;

		$mainframe = JFactory::getApplication();
 
        $orderby = '';
		$filter_order     = $this->getState('filter_order');
       	$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array ('id', 'label', 'year', 'start_date', 'end_date');
        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
        	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}

        return $orderby;
	}

	function getMyCampaign()
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$this->_user->id.' 
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	function getCampaignByID($campaign_id)
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_setup_campaigns AS esc 
					WHERE esc.id='.$campaign_id.' ORDER BY esc.end_date DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	function getProgrammeByCampaignID($campaign_id)
	{
		$campaign = $this->getCampaignByID($campaign_id);

		$query = 'SELECT esp.* 
					FROM #__emundus_setup_programmes AS esp 
					WHERE esp.code like "'.$campaign['training'].'"';
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	function getCampaignsByCourse($course)
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_setup_campaigns AS esc 
					WHERE esc.training like '.$this->_db->Quote($course).' ORDER BY esc.end_date DESC'; 
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	static function getLastCampaignByCourse($course)
	{
		$db = JFactory::getDBO();
		
		$query = 'SELECT esc.* 
					FROM #__emundus_setup_campaigns AS esc 
					WHERE published=1 AND esc.training like '.$db->Quote($course).' ORDER BY esc.end_date DESC'; 
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function getMySubmittedCampaign()
	{
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$this->_user->id. 'AND ecc.submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getCampaignByApplicant($aid)
	{
		$query = 'SELECT esc.*,ecc.fnum, esp.menutype, esp.label as profile_label
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.applicant_id='.$aid.' 
					ORDER BY ecc.date_time DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getCampaignSubmittedByApplicant($aid)
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$aid. 'AND submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	function setSelectedCampaign($cid, $aid) {

		$query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `campaign_id`, `fnum`) 
		VALUES ('.$aid.', '.$cid.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
		$this->_db->setQuery( $query );
		try {
			$this->_db->Query();
		} catch (Exception $e) {
			// catch any database errors.
		}
	}

	function setResultLetterSent($aid, $campaign_id) {		
		$query = 'UPDATE #__emundus_final_grade SET result_sent=1, date_result_sent=NOW() WHERE student_id='.$aid.' AND campaign_id='.$campaign_id;
		$this->_db->setQuery( $query );
		try {
			$this->_db->Query();
		} catch (Exception $e) {
			// catch any database errors.
		}
	}

	function isOtherActiveCampaign($aid) {
		$query='SELECT count(id) as cpt 
				FROM #__emundus_setup_campaigns 
				WHERE id NOT IN (
								select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$aid.'
								)';
		$this->_db->setQuery($query); 
		$cpt = $this->_db->loadResult();
		return $cpt>0?true:false;
	}
	
	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);    
		}
		return $this->_total;
	}
	
	function getCampaignsXLS()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT cc.id, cc.applicant_id, sc.start_date, sc.end_date, sc.label, sc.year
		FROM #__emundus_setup_campaigns AS sc 
		LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
		WHERE sc.published=1';
		//echo str_replace('#_','jos',$query);
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	/**
     * Method to create a new compaign for all active programmes.
     *
     * @param   array $data The data to use as campaign definition.
     * @param   array $programmes The list of programmes who need a new campaign.
     *
     * @return  json  Does it work.
     */
    public function addCampaignsForProgrammes($data, $programmes)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();

        $data['date_time'] = date("Y-m-d H:i:s");
		$data['user'] = $user->id;
		$data['label'] = '';
		$data['training'] = '';
		$data['published'] = 1;

		if (count($data) > 0 && count($programmes) > 0) {
			$column = array_keys($data);
			
			$values = array();
			$values_unity = array();
			foreach ($programmes as $key => $v) {
				try{
					$query = 'SELECT count(id) FROM `#__emundus_setup_campaigns` WHERE year LIKE '.$db->Quote($data['year']).' AND  training LIKE '.$db->Quote($v['code']);
					$db->setQuery($query);
					$cpt = $db->loadResult();

					if($cpt == 0) {
						$values[] = '('.$db->Quote($data['start_date']).', '.$db->Quote($data['end_date']).', '.$data['profile_id'].', '.$db->Quote($data['year']).', '.$db->Quote($data['short_description']).', '.$db->Quote($data['date_time']).', '.$data['user'].', '.$db->Quote($v['label']).', '.$db->Quote($v['code']).', '.$data['published'].')';
						$values_unity[] = '('.$db->Quote($v['code']).', '.$db->Quote($v['label']).', '.$db->Quote($data['year']).', '.$data['profile_id'].', '.$db->Quote($v['programmes']).')';

						$result .= '<i class="green check circle outline icon"></i> '.$v['label'].' ['.$data['year'].'] ['.$v['code'].'] '. JText::_('CREATED').'<br>';
					} else{
						$result .= '<i class="orange remove circle outline icon"></i> '.$v['label'].' ['.$data['year'].'] ['.$v['code'].'] '. JText::_('ALREADY_EXIST').'<br>';
					}
				}
				catch(Exception $e)
	            {
	                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
	                return $e->getMessage();
	            }
			}

			try
			{    
				if (count($values) > 0) {
					$query = 'INSERT INTO `#__emundus_setup_campaigns` (`'.implode('`, `', $column).'`) VALUES '.implode(',', $values);
					$db->setQuery($query);
					$db->execute();

	                $query = 'INSERT INTO `#__emundus_setup_teaching_unity` (`code`, `label`, `schoolyear`, `profile_id`, `programmes`) VALUES '.implode(',', $values_unity);
	                $db->setQuery($query);
	                $db->execute();
            	}
			}
			catch(Exception $e)
			{
	        	JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
				return $e->getMessage();
			}
		} else {
			return false;
		}

		return $result;
    }

}
?>