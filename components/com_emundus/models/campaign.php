<?php
/**
 * eMundus Campaign model
 *
 * @package    	Joomla
 * @subpackage 	eMundus
 * @link       	http://www.emundus.fr
 * @copyright	Copyright (C) 2018 eMundus. All rights reserved.
 * @license    	GNU/GPL
 * @author     	Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

class EmundusModelCampaign extends JModelList {
	var $_user = null;
	var $_db = null;

	function __construct() {
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();

		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getSession()->get('emundusUser');

		// Get pagination request variables
		$filter_order = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'label', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

 		$this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        JLog::addLogger(['text_file' => 'com_emundus.error.php'], JLog::ERROR, array('com_emundus'));
    }

	function getActiveCampaign() {
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		return $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
	}

	function _buildQuery() {
		$config = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

		$query = 'SELECT id, label, year, description, start_date, end_date
		FROM #__emundus_setup_campaigns
		WHERE published = 1 AND '.$this->_db->Quote($now).'>=start_date AND '.$this->_db->Quote($now).'<=end_date';
		return $query;
	}

	function _buildContentOrderBy() {
        global $option;

        $orderby = '';
		$filter_order     = $this->getState('filter_order');
       	$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array ('id', 'label', 'year', 'start_date', 'end_date');
        /* Error handling is never a bad thing*/
        if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)) {
        	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}

        return $orderby;
	}

	function getAllowedCampaign($uid = null) {

		if (empty($uid)) {
            $uid = JFactory::getUser()->id;
        }


		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
		$m_profile = new EmundusModelProfile();
		$userProfiles = $m_profile->getUserProfiles($uid);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
		$id_profiles = $eMConfig->get('id_profiles', '0');
		$id_profiles = explode(',', $id_profiles);

		foreach ($userProfiles as $profile) {
			if (in_array($profile->id, $id_profiles)) {
				$applicant_can_renew = 1;
				break;
			}
		}

		$query = $this->_buildQuery();
		switch ($applicant_can_renew) {

			// Applicant can only have one file per campaign.
			case 2:
				$query .= ' AND id NOT IN (
								select campaign_id
								from #__emundus_campaign_candidature
								where applicant_id='. $uid .'
							)';
				break;

			// Applicant can only have one file per year.
			case 3:
				$query .= ' AND year NOT IN (
								select sc.year
								from #__emundus_campaign_candidature as cc
								LEFT JOIN #__emundus_setup_campaigns as sc ON sc.id = cc.campaign_id
								where applicant_id='. $uid .'
							)';
				break;

		}

		try {

			$this->_db->setQuery($query);
			return array_column($this->_db->loadAssocList(), 'id');

		} catch (Exception $e) {
			JLog::add('Error at model/campaign -> query: '.$query, JLog::ERROR, 'com_emundus');
		}
	}

	/**
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getMyCampaign() {
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE ecc.applicant_id='.$this->_user->id.'
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * @param $campaign_id
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignByID($campaign_id) {
		$query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					WHERE esc.id='.$campaign_id.' ORDER BY esc.end_date DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	/**
	 * @param   bool  $published
	 *
	 * @return array|mixed
	 *
	 * @since version
	 */
	function getAllCampaigns($published = true) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(['tu.*'])
			->from($db->quoteName('#__emundus_setup_teaching_unity', 'tu'));

		if ($published) {
			$query->where($db->quoteName('c.published').' = 1');
		}

		try {
			$db->setQuery($query);
			return $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting campaigns at model/campaign at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return [];
		}

	}

	/**
	 * @param $campaign_id
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getProgrammeByCampaignID($campaign_id) {
		$campaign = $this->getCampaignByID($campaign_id);

		$query = 'SELECT esp.*
					FROM #__emundus_setup_programmes AS esp
					WHERE esp.code like "'.$campaign['training'].'"';
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	/**
	 * @param $training
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getProgrammeByTraining($training) {
		$query = 'SELECT esp.*
					FROM #__emundus_setup_programmes AS esp
					WHERE esp.code like "'.$training.'"';
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}

	/**
	 * @param $course
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignsByCourse($course) {
		$query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					WHERE esc.training like '.$this->_db->Quote($course).' ORDER BY esc.end_date DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 *
	 * @since version
	 */
    function getCampaignsByProgram($code) {
        $query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					LEFT JOIN #__emundus_setup_programmes AS esp on esp.code = esc.training
					WHERE esp.code like '.$this->_db->Quote($code);
        $this->_db->setQuery( $query );
        return $this->_db->loadObjectList();
    }

	/**
	 * @param $course
	 * @param $camp
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignsByCourseCampaign($course, $camp) {

		$query = 'SELECT esc.*
				FROM #__emundus_setup_campaigns AS esc
				WHERE esc.training like '.$this->_db->Quote($course).' AND esc.id like '.$this->_db->Quote($camp);


		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}

	/**
	 * @param $course
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	static function getLastCampaignByCourse($course) {
		$db = JFactory::getDBO();

		$query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					WHERE published=1 AND esc.training like '.$db->Quote($course).' ORDER BY esc.end_date DESC';
		$db->setQuery( $query );
		return $db->loadObject();
	}

	/**
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getMySubmittedCampaign() {
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$this->_user->id. 'AND ecc.submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * @param $aid
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignByApplicant($aid) {
		$query = 'SELECT esc.*,ecc.fnum, esp.menutype, esp.label as profile_label
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.applicant_id='.$aid.'
					ORDER BY ecc.date_time DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * @param $fnum
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignByFnum($fnum) {
		$query = 'SELECT esc.*,ecc.fnum, esp.menutype, esp.label as profile_label
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.fnum like '.$this->_db->Quote($fnum).'
					ORDER BY ecc.date_time DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	/**
	 * @param $fnums
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignsByFnums($fnums) {
		foreach ($fnums as $fnum) {
			$query = 'SELECT esc.*,ecc.fnum, esp.menutype, esp.label as profile_label
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.fnum like '.$this->_db->Quote($fnum).'
					ORDER BY ecc.date_time DESC';
			$this->_db->setQuery( $query );
			$res = $this->_db->loadObject();

			$campaigns[] = $res->id;
		}
		
		return $campaigns;
	}

	/**
	 * @param $aid
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	function getCampaignSubmittedByApplicant($aid) {
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$aid. 'AND submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * @param $cid
	 * @param $aid
	 *
	 *
	 * @since version
	 */
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
     * @return  String  Does it work.
     */
    public function addCampaignsForProgrammes($data, $programmes) {
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
				try {
					$query = 'SELECT count(id) FROM `#__emundus_setup_campaigns` WHERE year LIKE '.$db->Quote($data['year']).' AND  training LIKE '.$db->Quote($v['code']);
					$db->setQuery($query);
					$cpt = $db->loadResult();

					if ($cpt == 0) {
						$values[] = '('.$db->Quote($data['start_date']).', '.$db->Quote($data['end_date']).', '.$data['profile_id'].', '.$db->Quote($data['year']).', '.$db->Quote($data['short_description']).', '.$db->Quote($data['date_time']).', '.$data['user'].', '.$db->Quote($v['label']).', '.$db->Quote($v['code']).', '.$data['published'].')';
						$values_unity[] = '('.$db->Quote($v['code']).', '.$db->Quote($v['label']).', '.$db->Quote($data['year']).', '.$data['profile_id'].', '.$db->Quote($v['programmes']).')';

						$result .= '<i class="green check circle outline icon"></i> '.$v['label'].' ['.$data['year'].'] ['.$v['code'].'] '. JText::_('CREATED').'<br>';
					} else{
						$result .= '<i class="orange remove circle outline icon"></i> '.$v['label'].' ['.$data['year'].'] ['.$v['code'].'] '. JText::_('ALREADY_EXIST').'<br>';
					}
				} catch(Exception $e) {
	                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
	                return $e->getMessage();
	            }
			}

			try {
				if (count($values) > 0) {
					$query = 'INSERT INTO `#__emundus_setup_campaigns` (`'.implode('`, `', $column).'`) VALUES '.implode(',', $values);
					$db->setQuery($query);
					$db->execute();

	                $query = 'INSERT INTO `#__emundus_setup_teaching_unity` (`code`, `label`, `schoolyear`, `profile_id`, `programmes`) VALUES '.implode(',', $values_unity);
	                $db->setQuery($query);
	                $db->execute();
            	}
			} catch(Exception $e) {
	        	JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
				return $e->getMessage();
			}
		} else {
			return false;
		}

		return $result;
	}

	/**
	 * Gets the most recent campaign programme code.
	 * @return string The most recent campaign programme in the DB.
	 */
	function getLatestCampaign() {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select($db->quoteName('training'))
                ->from($db->quoteName('#__emundus_setup_campaigns'))
                ->order('id DESC')
                ->setLimit('1');

        try {

            $db->setQuery($query);
            return $db->loadResult();

        } catch (Exception $e) {
            JLog::add('Error getting latest programme at model/campaign at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return '';
        }

    }


    /**
     * Gets all elements in teaching unity table
     * @return array
     */
    function getCCITU() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['tu.*', 'p.prerequisite', 'p.audience', 'p.objectives', 'p.content', 'p.manager_firstname', 'p.manager_lastname' , 'p.pedagogie', 't.label AS thematique', 'p.id AS row_id'])
			->from($db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
			->leftJoin($db->quoteName('#__emundus_setup_programmes', 'p').' ON '.$db->quoteName('tu.code').' LIKE '.$db->quoteName('p.code'))
			->leftJoin($db->quoteName('#__emundus_setup_thematiques', 't').' ON '.$db->quoteName('t.id').' = '.$db->quoteName('p.programmes'))
      ->where($db->quoteName('tu.published'). ' = 1 AND '.$db->quoteName('p.published') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting latest programme at model/campaign at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
	        return [];
        }
    }

	/**
	 * @param   null  $id
	 *
	 * @return array|mixed
	 *
	 * @since version
	 */
    function getTeachingUnity($id = null) {

	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

	    $query->select(['tu.*'])
		    ->from($db->quoteName('#__emundus_setup_teaching_unity', 'tu'));

	    if (!empty($id) && is_numeric($id)) {
		    $query->where($db->quoteName('tu.id').' = '.$id);
	    }

	    try {
		    $db->setQuery($query);
		    return $db->loadObjectList();
	    } catch (Exception $e) {
		    JLog::add('Error getting latest programme at model/campaign at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		    return [];
	    }
    }

    /**
     * Get campaign limit params
     * @param $id
     *
     * @return Object|mixed
     *
     * @since 1.2.0
     *
     */
    public function getLimit($id) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select([$db->quoteName('esc.is_limited'), $db->quoteName('esc.limit'), 'GROUP_CONCAT(escrl.limit_status) AS steps'])
            ->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns_repeat_limit_status','escrl').' ON '.$db->quoteName('escrl.parent_id').' = '.$db->quoteName('esc.id'))
            ->where($db->quoteName('esc.id') . ' = ' . $id);

        try {
            $db->setQuery($query);
            return $db->loadObject();

        } catch (Exception $exception) {
            JLog::add('Error getting campaign limit at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return null;
        }

    }

    /**
     * Check if campaign's limit is obtained
     * @param $id
     *
     * @return Object|mixed
     *
     * @since 1.2.0
     *
     */
    public function isLimitObtained($id) {

        $user = JFactory::getSession()->get('emundusUser');
        if (empty($user)){
            $user = JFactory::getUser();
        }

        if(!EmundusHelperAccess::isApplicant($user->id) || empty($id)) {
            return null;
        }

        $limit = $this->getLimit($id);

        if (!empty($limit->is_limited)) {

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('COUNT(id)')
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('status') . ' IN (' . $limit->steps . ')')
                ->andWhere($db->quoteName('campaign_id') . ' = ' . $id);

            try {

                $db->setQuery($query);
                return ($limit->limit <= $db->loadResult());

            } catch (Exception $exception) {

                JLog::add('Error checking obtained limit at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return null;

            }
        }

    }
}
