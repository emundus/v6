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
use Joomla\CMS\Date\Date;

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

class EmundusModelCampaign extends JModelList {
	var $_em_user = null;
	var $_user = null;
	var $_db = null;

	function __construct() {
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();

		$this->_db = JFactory::getDBO();
		$this->_em_user = JFactory::getSession()->get('emundusUser');
		$this->_user = JFactory::getUser();

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

    /**
     * Get active campaign
     *
     * @return mixed
     *
     * @since version v6
     */
	function getActiveCampaign() {
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		return $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
	}

    /**
     * Build query to get campaign
     *
     * @return string
     *
     * @since version v6
     */
	function _buildQuery() {
		$config = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

		return 'SELECT id, label, year, description, start_date, end_date
		FROM #__emundus_setup_campaigns
		WHERE published = 1 AND '.$this->_db->Quote($now).'>=start_date AND '.$this->_db->Quote($now).'<=end_date';
	}

    /**
     * Build Content with order by
     *
     * @return string
     *
     * @since version v6
     */
	function _buildContentOrderBy() {
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

    /**
     * Get allowed campaigns by user and depending of eMundus params
     *
     * @param $uid
     *
     * @return array|void
     *
     * @since version v6
     */
	function getAllowedCampaign($uid = null) {

		if (empty($uid)) {
            $uid = $this->_user->id;
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
	 * Get campaigns by my applicant_id
     *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getMyCampaign() {
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE ecc.applicant_id='.$this->_em_user->id.'
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * @param $campaign_id
	 *
	 * @return mixed
	 *
	 * @since version v6
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
	 * @since version v6
	 */
	function getAllCampaigns($published = true) {
		$query = $this->_db->getQuery(true);

		$query->select(['tu.*'])
			->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'));

		if ($published) {
			$query->where($this->_db->quoteName('c.published').' = 1');
		}

		try {
            $this->_db->setQuery($query);
			return $this->_db->loadObjectList();
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
	 * @since version v6
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
	 * @since version v6
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
	 * @since version v6
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
	 * @since version v6
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
	 * @since version v6
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
	 * @since version v6
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
	 * @since version v6
	 */
	function getMySubmittedCampaign() {
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$this->_em_user->id. 'AND ecc.submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * @param $aid
	 *
	 * @return mixed
	 *
	 * @since version v6
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
	 * @since version v6
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
	 * @param $aid
	 *
	 * @return mixed
	 *
	 * @since version v6
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
	 * @since version v6
	 */
	function setSelectedCampaign($cid, $aid) {

		$query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `campaign_id`, `fnum`)
		VALUES ('.$aid.', '.$cid.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
		$this->_db->setQuery( $query );
		try {
			$this->_db->Query();
		} catch (Exception $e) {
            JLog::add('Error getting selected campaign ' . $cid . ' at model/campaign at query :'.preg_replace("/[\r\n]/"," ",$query), JLog::ERROR, 'com_emundus');
        }
	}

    /**
     * @param $aid
     * @param $campaign_id
     *
     *
     * @since version v6
     */
	function setResultLetterSent($aid, $campaign_id) {
		$query = 'UPDATE #__emundus_final_grade SET result_sent=1, date_result_sent=NOW() WHERE student_id='.$aid.' AND campaign_id='.$campaign_id;
		$this->_db->setQuery( $query );
		try {
			$this->_db->Query();
		} catch (Exception $e) {
			// catch any database errors.
		}
	}

    /**
     * @param $aid
     *
     * @return bool
     *
     * @since version v6
     */
	function isOtherActiveCampaign($aid) {
		$query='SELECT count(id) as cpt
				FROM #__emundus_setup_campaigns
				WHERE id NOT IN (
								select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$aid.'
								)';
		$this->_db->setQuery($query);
		$cpt = $this->_db->loadResult();

		return $cpt > 0;
	}

    /**
     *
     * @return JPagination
     *
     * @since version v6
     */
	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

    /**
     *
     * @return false|int
     *
     * @since version v6
     */
	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

    /**
     *
     * @return array|mixed
     *
     * @since version v6
     */
	function getCampaignsXLS()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT cc.id, cc.applicant_id, sc.start_date, sc.end_date, sc.label, sc.year
		FROM #__emundus_setup_campaigns AS sc
		LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
		WHERE sc.published=1';

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
     * @since version v6
     */
    public function addCampaignsForProgrammes($data, $programmes) {
        $data['date_time'] = date("Y-m-d H:i:s");
		$data['user'] = $this->_user->id;
		$data['label'] = '';
		$data['training'] = '';
		$data['published'] = 1;

		if (!empty($data) && !empty($programmes)) {
			$column = array_keys($data);

			$values = array();
			$values_unity = array();
            $result = '';
			foreach ($programmes as $v) {
				try {
					$query = 'SELECT count(id) FROM `#__emundus_setup_campaigns` WHERE year LIKE '.$this->_db->Quote($data['year']).' AND  training LIKE '.$this->_db->Quote($v['code']);
                    $this->_db->setQuery($query);
					$cpt = $this->_db->loadResult();

					if ($cpt == 0) {
						$values[] = '('.$this->_db->Quote($data['start_date']).', '.$this->_db->Quote($data['end_date']).', '.$data['profile_id'].', '.$this->_db->Quote($data['year']).', '.$this->_db->Quote($data['short_description']).', '.$this->_db->Quote($data['date_time']).', '.$data['user'].', '.$this->_db->Quote($v['label']).', '.$this->_db->Quote($v['code']).', '.$data['published'].')';
						$values_unity[] = '('.$this->_db->Quote($v['code']).', '.$this->_db->Quote($v['label']).', '.$this->_db->Quote($data['year']).', '.$data['profile_id'].', '.$this->_db->Quote($v['programmes']).')';

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
				if (!empty($values)) {
					$query = 'INSERT INTO `#__emundus_setup_campaigns` (`'.implode('`, `', $column).'`) VALUES '.implode(',', $values);
                    $this->_db->setQuery($query);
                    $this->_db->execute();

	                $query = 'INSERT INTO `#__emundus_setup_teaching_unity` (`code`, `label`, `schoolyear`, `profile_id`, `programmes`) VALUES '.implode(',', $values_unity);
                    $this->_db->setQuery($query);
                    $this->_db->execute();
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
     *
     * @since version v6
	 */
	function getLatestCampaign() {
        $query = $this->_db->getQuery(true);

        $query->select($this->_db->quoteName('training'))
                ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
                ->order('id DESC')
                ->setLimit('1');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting latest programme at model/campaign at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return '';
        }

    }


    /**
     * Gets all elements in teaching unity table
     * @return array
     *
     * @since version v6
     */
    function getCCITU() {
        $query = $this->_db->getQuery(true);

        $query->select(['tu.*', 'p.prerequisite', 'p.audience', 'p.objectives', 'p.content', 'p.manager_firstname', 'p.manager_lastname' , 'p.pedagogie', 't.label AS thematique', 'p.id AS row_id'])
			->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'p').' ON '.$this->_db->quoteName('tu.code').' LIKE '.$this->_db->quoteName('p.code'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_thematiques', 't').' ON '.$this->_db->quoteName('t.id').' = '.$this->_db->quoteName('p.programmes'))
            ->where($this->_db->quoteName('tu.published'). ' = 1 AND '.$this->_db->quoteName('p.published') . ' = 1');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
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
	 * @since version v6
	 */
    function getTeachingUnity($id = null) {
	    $query = $this->_db->getQuery(true);

	    $query->select(['tu.*'])
		    ->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'));

	    if (!empty($id) && is_numeric($id)) {
		    $query->where($this->_db->quoteName('tu.id').' = '.$id);
	    }

	    try {
            $this->_db->setQuery($query);
		    return $this->_db->loadObjectList();
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
        $query = $this->_db->getQuery(true);

        $query
            ->select([$this->_db->quoteName('esc.is_limited'), $this->_db->quoteName('esc.limit'), 'GROUP_CONCAT(escrl.limit_status) AS steps'])
            ->from($this->_db->quoteName('#__emundus_setup_campaigns', 'esc'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status','escrl').' ON '.$this->_db->quoteName('escrl.parent_id').' = '.$this->_db->quoteName('esc.id'))
            ->where($this->_db->quoteName('esc.id') . ' = ' . $id);

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObject();
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
            $user = $this->_user;
        }

        if(!EmundusHelperAccess::isApplicant($user->id) || empty($id)) {
            return null;
        }

        $limit = $this->getLimit($id);

        if (!empty($limit->is_limited)) {
            $query = $this->_db->getQuery(true);

            $query
                ->select('COUNT(id)')
                ->from($this->_db->quoteName('#__emundus_campaign_candidature'))
                ->where($this->_db->quoteName('status') . ' IN (' . $limit->steps . ')')
                ->andWhere($this->_db->quoteName('campaign_id') . ' = ' . $id);

            try {
                $this->_db->setQuery($query);
                return ($limit->limit <= $this->_db->loadResult());
            } catch (Exception $exception) {
                JLog::add('Error checking obtained limit at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Get number of campaigns
     *
     * @param $filter
     * @param $recherche
     *
     * @return int
     *
     * @since version 1.0
     */
    function getCampaignCount($filter, $recherche)
    {
        $query = $this->_db->getQuery(true);
        $date = new Date();

        // Get affected programs
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');

        $m_programme = new EmundusModelProgramme;
        $programs = $m_programme->getUserPrograms($this->_user->id);
        //

        if ($filter == 'notTerminated') {
            $filterCount =
                'Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $this->_db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00"';
        } elseif ($filter == 'Terminated') {
            $filterCount =
                'Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' <= ' .
                $this->_db->quote($date) .
                ' AND end_date != "0000-00-00 00:00:00"';
        } elseif ($filter == 'Publish') {
            $filterCount =
                $this->_db->quoteName('sc.published') .
                ' = 1 AND (Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $this->_db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } elseif ($filter == 'Unpublish') {
            $filterCount =
                $this->_db->quoteName('sc.published') .
                ' = 0 AND (Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $this->_db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } else {
            $filterCount = '1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $fullRecherche =
                $this->_db->quoteName('sc.label') .
                ' LIKE ' .
                $this->_db->quote('%' . $recherche . '%');
        }

        $query
            ->select('COUNT(sc.id)')
            ->from($this->_db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->where($filterCount)
            ->andWhere($fullRecherche)
            ->andWhere($this->_db->quoteName('sc.training') . ' IN (' . implode(',',$this->_db->quote($programs)) . ')');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error when try to get number of campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    /**
     * Get associated campaigns
     *
     * @param $filter
     * @param $sort
     * @param $recherche
     * @param $lim
     * @param $page
     * @param $program
     *
     * @return array|mixed|stdClass
     *
     * @since version 1.0
     */
    function getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page,$program) {
        $query = $this->_db->getQuery(true);

        if (empty($lim)) {
            $limit = 25;
        } else {
            $limit = $lim;
        }

        if (empty($page)) {
            $offset = 0;
        } else {
            $offset = ($page - 1) * $limit;
        }

        if (empty($sort)) {
            $sort = 'DESC';
        }
        $sortDb = 'sc.id ';

        $date = new Date();

        // Get affected programs
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');

        $m_programme = new EmundusModelProgramme;
        $programs = $m_programme->getUserPrograms($this->_user->id);

        if($program !="all"){
            $programs=array_filter($programs,function($value) use ($program) {
                return $value == $program;
            });
        }
        //

        if ($filter == 'notTerminated') {
            $filterDate =
                'Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $this->_db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00"';
        } elseif ($filter == 'Terminated') {
            $filterDate =
                'Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' <= ' .
                $this->_db->quote($date) .
                ' AND end_date != "0000-00-00 00:00:00"';
        } elseif ($filter == 'Publish') {
            $filterDate =
                $this->_db->quoteName('sc.published') .
                ' = 1 AND (Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $this->_db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } elseif ($filter == 'Unpublish') {
            $filterDate =
                $this->_db->quoteName('sc.published') .
                ' = 0 AND (Date(' .
                $this->_db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $this->_db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } else {
            $filterDate = '1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $fullRecherche =
                $this->_db->quoteName('sc.label') .
                ' LIKE ' .
                $this->_db->quote('%' . $recherche . '%');
        }

        $query
            ->select([
                'sc.*',
                'COUNT(cc.id) AS nb_files',
                'sp.label AS program_label',
                'sp.id AS program_id',
                'sp.published AS published_prog'
            ])
            ->from($this->_db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->leftJoin(
                $this->_db->quoteName('#__emundus_campaign_candidature', 'cc') .
                ' ON ' .
                $this->_db->quoteName('cc.campaign_id') .
                ' = ' .
                $this->_db->quoteName('sc.id')
            )
            ->leftJoin(
                $this->_db->quoteName('#__emundus_setup_programmes', 'sp') .
                ' ON ' .
                $this->_db->quoteName('sp.code') .
                ' LIKE ' .
                $this->_db->quoteName('sc.training')
            )
            ->where($filterDate)
            ->andWhere($fullRecherche)
            ->andWhere($this->_db->quoteName('sc.training') . ' IN (' . implode(',',$this->_db->quote($programs)) . ')')
            ->group($sortDb)
            ->order($sortDb . $sort);

        try {
            $this->_db->setQuery($query, $offset, $limit);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error when try to get list of campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    /**
     * Get campaigns by program id
     *
     * @param $program
     *
     * @return array|mixed|stdClass
     *
     * @since version 1.0
     */
    function getCampaignsByProgramId($program){
        $query = $this->_db->getQuery(true);

        $date = new Date();

        $query
            ->select('sc.*')
            ->from($this->_db->quoteName('#__emundus_setup_programmes','sp'))
            ->leftJoin(
                $this->_db->quoteName('#__emundus_setup_campaigns', 'sc') .
                ' ON ' .
                $this->_db->quoteName('sp.code') .
                ' LIKE ' .
                $this->_db->quoteName('sc.training')
            )
            ->where($this->_db->quoteName('sp.id') . ' = ' . $this->_db->quote($program))
            ->andWhere($this->_db->quoteName('sc.end_date') . ' >= ' . $this->_db->quote($date));

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/campaign | Error when try to get campaigns associated to programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    /**
     * Delete a campaign
     *
     * @param $data
     *
     * @return false|string
     *
     * @since version 1.0
     */
    public function deleteCampaign($data) {
        $query = $this->_db->getQuery(true);

        // TODO REPLACE BY TRANSLATION MODEL
        $falang = JModelLegacy::getInstance('falang', 'EmundusModel');

        if (count($data) > 0) {
            try {
                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onBeforeCampaignDelete', $data);
                $dispatcher->trigger('callEventHandler', ['onBeforeCampaignDelete', ['campaign' => $data]]);

                foreach (array_values($data) as $id) {
                    $falang->deleteFalang($id,'emundus_setup_campaigns','label');
                }

                $cc_conditions = [
                    $this->_db->quoteName('campaign_id').' IN ('.implode(", ", array_values($data)).')'
                ];

                $query->delete($this->_db->quoteName('#__emundus_campaign_candidature'))
                    ->where($cc_conditions);

                $this->_db->setQuery($query);
                $this->_db->execute();

                $sc_conditions = [
                    $this->_db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'
                ];

                $query->clear()
                    ->delete($this->_db->quoteName('#__emundus_setup_campaigns'))
                    ->where($sc_conditions);

                $this->_db->setQuery($query);
                $res = $this->_db->execute();

                if ($res) {
                    $dispatcher->trigger('onAfterCampaignDelete', $data);
                    $dispatcher->trigger('callEventHandler', ['onAfterCampaignDelete', ['campaign' => $data]]);
                }
                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/campaign | Error when delete campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     *
     * @param $data
     *
     * @return false|string
     *
     * @since version 1.0
     */
    public function unpublishCampaign($data) {
        $query = $this->_db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignUnpublish', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignUnpublish', ['campaign' => $data]]);

            try {
                $fields = [
                    $this->_db->quoteName('published').' = 0'
                ];
                $sc_conditions = [
                    $this->_db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'
                ];

                $query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

                $this->_db->setQuery($query);
                $res = $this->_db->execute();

                if ($res) {
                    $dispatcher->trigger('onAfterCampaignUnpublish', $data);
                    $dispatcher->trigger('callEventHandler', ['onAfterCampaignUnpublish', ['campaign' => $data]]);
                }
                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/campaign | Error when unpublish campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     *
     * @param $data
     *
     * @return false|string
     *
     * @since version 1.0
     */
    public function publishCampaign($data) {
        $query = $this->_db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignPublish', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignPublish', ['campaign' => $data]]);
            try {
                $fields = [$this->_db->quoteName('published') . ' = 1'];
                $sc_conditions = [$this->_db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'];

                $query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

                $this->_db->setQuery($query);
                $res = $this->_db->execute();

                if ($res) {
                    $dispatcher->trigger('onAfterCampaignPublish', $data);
                    $dispatcher->trigger('callEventHandler', ['onAfterCampaignPublish', ['campaign' => $data]]);
                }
                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/campaign | Error when publish campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     * @param $data
     *
     * @return false|mixed|string
     *
     * @since version 1.0
     */
    public function duplicateCampaign($data) {
        $query = $this->_db->getQuery(true);

        if (!empty($data)) {
            try {
                $columns = array_keys(
                    $this->_db->getTableColumns('#__emundus_setup_campaigns')
                );

                $columns = array_filter($columns, function ($k) {
                    return $k != 'id' && $k != 'date_time';
                });

                foreach ($data as $id) {
                    $query->clear()
                        ->select(implode(',', $this->_db->qn($columns)))
                        ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
                        ->where($this->_db->quoteName('id') . ' = ' . $id);

                    $this->_db->setQuery($query);
                    $values[] = implode(', ', $this->_db->quote($this->_db->loadRow()));
                }

                $query->clear()
                    ->insert($this->_db->quoteName('#__emundus_setup_campaigns'))
                    ->columns(implode(',', $this->_db->quoteName($columns)))
                    ->values($values);

                $this->_db->setQuery($query);
                return $this->_db->execute();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/campaign | Error when duplicate campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    //TODO Throw in the years model

    /**
     *
     * @return array|mixed
     *
     * @since version 1.0
     */
    function getYears() {
        $query = $this->_db->getQuery(true);

        $query->select('DISTINCT(tu.schoolyear)')
            ->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
            ->order('tu.id DESC');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add(preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    /**
     * @param $data
     *
     * @return false|mixed|string
     *
     * @since version 1.0
     */
    public function createCampaign($data) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'settings.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

        $m_falang = new EmundusModelFalang;
        $m_settings = new EmundusModelSettings;
        $m_emails = new EmundusModelEmails;

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $i = 0;

        $labels = new stdClass;
        $limit_status = [];

        if (!empty($data)) {
            $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'jos_emundus_setup_campaigns'";
            $this->_db->setQuery($query);
            $campaign_columns = $this->_db->loadColumn($query);

            $data['label'] = json_decode($data['label'],true);

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignCreate', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignCreate', ['campaign' => $data]]);

            $query = $this->_db->getQuery(true);
            foreach ($data as $key => $val) {
                if ($key == 'profileLabel') {
                    array_splice($data, $i, 1);
                }
                if ($key == 'label') {
                    $labels->fr = !empty($data['label']['fr']) ? $data['label']['fr'] : '';
                    $labels->en = !empty($data['label']['en']) ? $data['label']['en'] : '';
                    $data['label'] = $data['label'][$actualLanguage];
                }
                if ($key == 'limit_status') {
                    $limit_status = $data['limit_status'];
                    array_splice($data, $i, 1);
                }
                if ($key == 'profile_id') {
                    $query->select('id')
                        ->from($this->_db->quoteName('#__emundus_setup_profiles'))
                        ->where($this->_db->quoteName('published') . ' = 1')
                        ->andWhere($this->_db->quoteName('status') . ' = 1');
                    $this->_db->setQuery($query);
                    $data['profile_id'] = $this->_db->loadResult();
                    if(empty($data['profile_id'])){
                        unset($data['profile_id']);
                        $data['published'] = 0;
                    }
                }
                if (!in_array($key,$campaign_columns)){
                    unset($data[$key]);
                }
                $i++;
            }

            $query->clear()
                ->insert($this->_db->quoteName('#__emundus_setup_campaigns'))
                ->columns($this->_db->quoteName(array_keys($data)))
                ->values(implode(',', $this->_db->Quote(array_values($data))));

            try {
                $this->_db->setQuery($query);
                $this->_db->execute();
                $campaign_id = $this->_db->insertid();

                $m_falang->insertFalang($labels,$campaign_id,'emundus_setup_campaigns','label');

                if($data['is_limited'] == 1){
                    foreach ($limit_status as $key => $limit_statu) {
                        if($limit_statu == 'true'){
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'));
                            $query->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($campaign_id))
                                ->set($this->_db->quoteName('limit_status') . ' = ' . $this->_db->quote($key));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }
                }

                $user = JFactory::getUser();
                $m_settings->onAfterCreateCampaign($user->id);

                // Create a default trigger
                $query->clear()
                    ->select('id')
                    ->from($this->_db->quoteName('#__emundus_setup_programmes'))
                    ->where($this->_db->quoteName('code') . ' LIKE ' . $this->_db->quote($data['training']));
                $this->_db->setQuery($query);
                $pid = $this->_db->loadResult();

                $emails = $m_emails->getTriggersByProgramId($pid);

                if(empty($emails)) {
                    $trigger = array(
                        'status' => 1,
                        'model' => 1,
                        'action_status' => 'to_current_user',
                        'target' => -1,
                        'program' => $pid,
                    );
                    $m_emails->createTrigger($trigger, array(), $user);
                }
                //

                // Create teaching unity
                $this->createYear($data);
                //

                $dispatcher->trigger('onAfterCampaignCreate', $campaign_id);
                $dispatcher->trigger('callEventHandler', ['onAfterCampaignCreate', ['campaign' => $campaign_id]]);

                return $campaign_id;
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/campaign | Error when create the campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @param $cid
     *
     * @return bool|string
     *
     * @since version 1.0
     */
    public function updateCampaign($data, $cid) {
        $query = $this->_db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $m_falang = new EmundusModelFalang;

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $limit_status = [];

        if (!empty($data)) {
            $fields = [];
            $labels = new stdClass;

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignUpdate', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignUpdate', ['campaign' => $cid]]);

            foreach ($data as $key => $val) {
                if ($key == 'label') {
                    $labels = $data['label'];
                    $data['label'] = $data['label'][$actualLanguage];
                    $fields[] = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($data['label']);
                } else if ($key == 'limit_status') {
                    $limit_status = $data['limit_status'];
                }
                else if ($key !== 'profileLabel' && $key !== 'progid' && $key !== 'status') {
                    $insert = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($val);
                    $fields[] = $insert;
                }
            }

            $m_falang->updateFalang($labels,$cid,'emundus_setup_campaigns','label');

            $query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
                ->set($fields)
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($cid));

            try {
                $this->_db->setQuery($query);
                $this->_db->execute();

                $query->clear()
                    ->delete($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'))
                    ->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($cid));
                $this->_db->setQuery($query);
                $this->_db->execute();

                if ($data['is_limited'] == 1) {
                    foreach ($limit_status as $key => $limit_statu) {
                        if($limit_statu == 'true'){
                            $query->clear()
                                ->insert($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'));
                            $query->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($cid))
                                ->set($this->_db->quoteName('limit_status') . ' = ' . $this->_db->quote($key));
                            $this->_db->setQuery($query);
                            $this->_db->execute();
                        }
                    }
                }


                // Create teaching unity
                $this->createYear($data);
                //

                $dispatcher->trigger('onAfterCampaignUpdate', $data);
                $dispatcher->trigger('callEventHandler', ['onAfterCampaignUpdate', ['campaign' => $cid]]);
                return true;
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/campaign | Error when update the campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @param $profile
     *
     * @return bool|string
     *
     * @since version 1.0
     */
    public function createYear($data,$profile = null) {
        $query = $this->_db->getQuery(true);

        $prid = !empty($profile) ? $profile : $data['profile_id'];

        try {
            // Create teaching unity
            $query->select('count(id)')
                ->from($this->_db->quoteName('#__emundus_setup_teaching_unity'))
                ->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($prid))
                ->andWhere($this->_db->quoteName('schoolyear') . ' = ' . $this->_db->quote($data['year']))
                ->andWhere($this->_db->quoteName('code') . ' = ' . $this->_db->quote($data['training']));
            $this->_db->setQuery($query);
            $teaching_unity_exist = $this->_db->loadResult();

            if ($teaching_unity_exist == 0) {
                $query->clear()
                    ->insert($this->_db->quoteName('#__emundus_setup_teaching_unity'))
                    ->set($this->_db->quoteName('code') . ' = ' . $this->_db->quote($data['training']))
                    ->set($this->_db->quoteName('label') . ' = ' . $this->_db->quote($data['label']))
                    ->set($this->_db->quoteName('schoolyear') . ' = ' . $this->_db->quote($data['year']))
                    ->set($this->_db->quoteName('published') . ' = 1')
                    ->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($prid));
                $this->_db->setQuery($query);
                $this->_db->execute();
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error when create the campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
        //
    }

    /**
     * @param $id
     *
     * @return false|stdClass
     *
     * @since version 1.0
     */
    public function getCampaignDetailsById($id) {
        if (empty($id)) {
            return false;
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $m_falang = new EmundusModelFalang;

        $query = $this->_db->getQuery(true);

        $results = new stdClass();

        try {
            $query->select(['sc.*', 'spr.label AS profileLabel','sp.id as progid'])
                ->from($this->_db->quoteName('#__emundus_setup_campaigns', 'sc'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_profiles', 'spr').' ON '.$this->_db->quoteName('spr.id').' = '.$this->_db->quoteName('sc.profile_id'))
                ->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$this->_db->quoteName('sp.code').' = '.$this->_db->quoteName('sc.training'))
                ->where($this->_db->quoteName('sc.id') . ' = ' . $id);

            $this->_db->setQuery($query);
            $results->campaign = $this->_db->loadObject();
            $results->label = $m_falang->getFalang($id,'emundus_setup_campaigns','label');

            if($results->campaign->is_limited == 1){
                $query->clear()
                    ->select('limit_status')
                    ->from($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'))
                    ->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($results->campaign->id));
                $this->_db->setQuery($query);
                $results->campaign->status = $this->_db->loadObjectList();
            }

            $query->clear()
                ->select('*')
                ->from($this->_db->quoteName('#__emundus_setup_programmes'))
                ->where($this->_db->quoteName('code') . ' LIKE ' . $this->_db->quote($results->campaign->training));
            $this->_db->setQuery($query);
            $results->program = $this->_db->loadObject();
            return $results;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error at getting the campaign by id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     *
     * @return false|mixed|null
     *
     * @since version 1.0
     */
    public function getCreatedCampaign() {
        $query = $this->_db->getQuery(true);

        $currentDate = date('Y-m-d H:i:s');

        $query->select('*')
            ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
            ->where($this->_db->quoteName('date_time') . ' = ' . $this->_db->quote($currentDate));

        $this->_db->setQuery($query);

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObject();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error at getting the campaign created today : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $profile
     * @param $campaign
     *
     * @return bool
     *
     * @since version 1.0
     */
    public function updateProfile($profile, $campaign) {
        $query = $this->_db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'form.php');

        $m_form = new EmundusModelForm;

        $query->select('label,year,training')
            ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
            ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($campaign));

        try {
            $this->_db->setQuery($query);
            $schoolyear = $this->_db->loadAssoc();

            $query->clear()
                ->update($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
                ->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($profile))
                ->where($this->_db->quoteName('campaign_id') . ' = ' . $this->_db->quote($campaign));
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Create checklist menu if documents are asked
            $query->clear()
                ->select('*')
                ->from($this->_db->quoteName('#__menu'))
                ->where($this->_db->quoteName('alias') . ' = ' . $this->_db->quote('checklist-' . $profile));
            $this->_db->setQuery($query);
            $checklist = $this->_db->loadObject();

            if ($checklist == null) {
                $m_form->addChecklistMenu($profile);
            }

            $query = $this->_db->getQuery(true);
            $query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
                ->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($profile))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($campaign));

            $this->_db->setQuery($query);
            $this->_db->execute();

            // Create teaching unity
            $this->createYear($schoolyear,$profile);
            //

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error at updating setup_profile of the campaign: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Get campaigns without applicant files
     *
     * @return array|mixed
     *
     * @since version 1.0
     */
    public function getCampaignsToAffect() {
        // Get campaigns that don't have applicant files
        $query = 'select sc.id,sc.label 
                  from jos_emundus_setup_campaigns as sc
                  where (
                    select count(cc.id)
                    from jos_emundus_campaign_candidature as cc
                    left join jos_emundus_users as u on u.id = cc.applicant_id
                    where cc.campaign_id = sc.id
                    and u.profile NOT IN (2,4,5,6)
                  ) = 0';
        //

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error getting campaigns without setup_profiles associated: ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    /**
     * @param $term
     *
     * @return false
     *
     * @since version 1.0
     */
    public function getCampaignsToAffectByTerm($term){
        $query = $this->_db->getQuery(true);

        $date = new Date();

        // Get affected programs
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');

        $m_programme = new EmundusModelProgramme;
        $programs = $m_programme->getUserPrograms($this->_user->id);
        //

        $searchName = $this->_db->quoteName('label').' LIKE '.$this->_db->quote('%' . $term . '%');

        $query->select('id,label')
            ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
            ->where($this->_db->quoteName('profile_id') . ' IS NULL')
            ->andWhere($this->_db->quoteName('end_date') . ' >= ' . $this->_db->quote($date))
            ->andWhere($searchName)
            ->andWhere($this->_db->quoteName('training') . ' IN (' . implode(',',$this->_db->quote($programs)) . ')');

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Error getting campaigns without setup_profiles associated with search terms : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $document
     * @param $types
     * @param $cid
     * @param $pid
     *
     * @return string
     *
     * @since version 1.0
     */
    public function createDocument($document,$types,$cid,$pid) {
        $query = $this->_db->getQuery(true);

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $m_falang = new EmundusModelFalang;

        $types = implode(";", array_values($types));
        $query
            ->insert($this->_db->quoteName('#__emundus_setup_attachments'));

        $query
            ->set($this->_db->quoteName('lbl') . ' = ' . $this->_db->quote('_em'))
            ->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($document['name'][$actualLanguage]))
            ->set($this->_db->quoteName('description') . ' = ' . $this->_db->quote($document['description'][$actualLanguage]))
            ->set($this->_db->quoteName('allowed_types') . ' = ' . $this->_db->quote($types))
            ->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote(0))
            ->set($this->_db->quoteName('nbmax') . ' = ' . $this->_db->quote($document['nbmax']));

        /// insert image resolution if image is found
        if($document['minResolution'] != null and $document['maxResolution'] != null) {
            if(empty($document['minResolution']['width']) or (int)$document['minResolution']['width'] == 0) {
                $document['minResolution']['width'] = 'null';
            }

            if(empty($document['minResolution']['height']) or (int)$document['minResolution']['height'] == 0) {
                $document['minResolution']['height'] = 'null';
            }

            if(empty($document['maxResolution']['width']) or (int)$document['maxResolution']['width'] == 0) {
                $document['maxResolution']['width'] = 'null';
            }

            if(empty($document['maxResolution']['height']) or (int)$document['maxResolution']['height'] == 0) {
                $document['maxResolution']['height'] = 'null';
            }

            $query
                ->set($this->_db->quoteName('min_width') . ' = ' . $document['minResolution']['width'])
                ->set($this->_db->quoteName('min_height') . ' = ' . $document['minResolution']['height'])
                ->set($this->_db->quoteName('max_width') . ' = ' . $document['maxResolution']['width'])
                ->set($this->_db->quoteName('max_height') . ' = ' . $document['maxResolution']['height']);
        }

        try{

            $this->_db->setQuery($query);
            $this->_db->execute();
            $newdocument = $this->_db->insertid();
            $m_falang->insertFalang($document['name'],$newdocument,'emundus_setup_attachments','value');
            $m_falang->insertFalang($document['description'],$newdocument,'emundus_setup_attachments','description');

            $query
                ->clear()
                ->update($this->_db->quoteName('#__emundus_setup_attachments'))
                ->set($this->_db->quoteName('lbl') . ' = ' . $this->_db->quote('_em' . $newdocument))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($newdocument));
            $this->_db->setQuery($query);
            $this->_db->execute();
            $query->clear()
                ->select('max(ordering)')
                ->from($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid));
            $this->_db->setQuery($query);
            $ordering = $this->_db->loadResult();

            $query->clear()
                ->insert($this->_db->quoteName('#__emundus_setup_attachment_profiles'));
            $query->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
                ->set($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($newdocument))
                ->set($this->_db->quoteName('mandatory') . ' = ' . $this->_db->quote($document['mandatory']))
                ->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote($ordering + 1));
            $this->_db->setQuery($query);
            $this->_db->execute();
            return $newdocument;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot create a document : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
    }

    /**
     * @param $document
     * @param $types
     * @param $did
     * @param $pid
     *
     * @return bool|string
     *
     * @since version 1.0
     */
    public function updateDocument($document,$types,$did,$pid) {
        $query = $this->_db->getQuery(true);

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $m_falang = new EmundusModelFalang;

        $types = implode(";", array_values($types));

        $query
            ->update($this->_db->quoteName('#__emundus_setup_attachments'));
        $query
            ->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($document['name'][$actualLanguage]))
            ->set($this->_db->quoteName('description') . ' = ' . $this->_db->quote($document['description'][$actualLanguage]))
            ->set($this->_db->quoteName('allowed_types') . ' = ' . $this->_db->quote($types))
            ->set($this->_db->quoteName('nbmax') . ' = ' . $this->_db->quote($document['nbmax']));

        /// many cases
        if(isset($document['minResolution'])) {

            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['minResolution']['width'])) {
                $query
                    ->set($this->_db->quoteName('min_width') . ' = ' . $document['minResolution']['width']);
            } else {
                $query
                    ->set($this->_db->quoteName('min_width') . ' = null');
            }

            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['minResolution']['height'])) {
                $query
                    ->set($this->_db->quoteName('min_height') . ' = ' . $document['minResolution']['height']);
            } else {
                $query
                    ->set($this->_db->quoteName('min_height') . ' = null');
            }
        } else {
            $query
                ->set($this->_db->quoteName('min_width') . ' = null')
                ->set($this->_db->quoteName('min_height') . ' = null');
        }

        if(isset($document['maxResolution'])) {
            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['maxResolution']['width'])) {
                $query
                    ->set($this->_db->quoteName('max_width') . ' = ' . $document['maxResolution']['width']);
            } else {
                $query
                    ->set($this->_db->quoteName('max_width') . ' = null');
            }

            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['maxResolution']['height'])) {
                $query
                    ->set($this->_db->quoteName('max_height') . ' = ' . $document['maxResolution']['height']);
            } else {
                $query
                    ->set($this->_db->quoteName('max_height') . ' = null');
            }
        } else {
            $query
                ->set($this->_db->quoteName('max_width') . ' = null')
                ->set($this->_db->quoteName('max_height') . ' = null');
        }

        $query->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($did));

        try{

            $this->_db->setQuery($query);
            $this->_db->execute();
            $query->clear()
                ->update($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
                ->set($this->_db->quoteName('mandatory') . ' = ' . $this->_db->quote($document['mandatory']))
                ->where($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did));
            $this->_db->setQuery($query);
            $this->_db->execute();


            $m_falang->updateFalang($document['name'],$did,'emundus_setup_attachments','value');
            $m_falang->updateFalang($document['description'],$did,'emundus_setup_attachments','description');

            $query->clear()
                ->select('count(id)')
                ->from($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
                ->andWhere($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did));
            $this->_db->setQuery($query);
            $assignations = $this->_db->loadResult();

            if(empty($assignations)) {

                $query->clear()
                    ->select('max(ordering)')
                    ->from($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
                    ->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid));
                $this->_db->setQuery($query);
                $ordering = $this->_db->loadResult();
                if ($did !==20){
                    $query->clear()
                        ->insert($this->_db->quoteName('#__emundus_setup_attachment_profiles'));
                    $query->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
                        ->set($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did))
                        ->set($this->_db->quoteName('mandatory') . ' = ' . $this->_db->quote($document['mandatory']))
                        ->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote($ordering + 1));
                    $this->_db->setQuery($query);
                } else {
                    $query->clear()
                        ->insert($this->_db->quoteName('#__emundus_setup_attachment_profiles'));
                    $query->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
                        ->set($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did))
                        ->set($this->_db->quoteName('mandatory') . ' = ' . $this->_db->quote($document['mandatory']))
                        ->set($this->_db->quoteName('displayed') . ' = '. 0)
                        ->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote($ordering + 1));
                }
                $this->_db->execute();
            }
            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot update a document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
    }

    /**
     * @param $cid
     *
     * @return false
     *
     * @since version 1.0
     */
    function getCampaignCategory($cid){
        $query = $this->_db->getQuery(true);

        try {
            $query->select('id,params')
                ->from($this->_db->quoteName('#__categories'))
                ->where('json_extract(`params`, "$.idCampaign") LIKE ' . $this->_db->quote('"'.$cid.'"'))
                ->andWhere($this->_db->quoteName('extension') . ' = ' . $this->_db->quote('com_dropfiles'));
            $this->_db->setQuery($query);
            $campaign_dropfile_cat = $this->_db->loadResult();

            if(!$campaign_dropfile_cat){
                JPluginHelper::importPlugin('emundus', 'setup_category');
                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onAfterCampaignCreate', $cid);
                $this->getCampaignCategory($cid);
            }
            return $campaign_dropfile_cat;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot get dropfiles category of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $campaign_cat
     *
     * @return false
     *
     * @since version 1.0
     */
    function getCampaignDropfilesDocuments($campaign_cat) {
        $query = $this->_db->getQuery(true);

        try {
            $query->select('*')
                ->from($this->_db->quoteName('#__dropfiles_files'))
                ->where($this->_db->quoteName('catid') . ' = ' . $this->_db->quote($campaign_cat))
                ->group($this->_db->quoteName('ordering'));
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot get dropfiles documents of the category ' . $campaign_cat . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $did
     *
     * @return false
     *
     * @since version 1.0
     */
    function getDropfileDocument($did){
        $query = $this->_db->getQuery(true);

        try {
            $query->select('*')
                ->from($this->_db->quoteName('#__dropfiles_files'))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($did));
            $this->_db->setQuery($query);
            return $this->_db->loadObject();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot get the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $did
     *
     * @return false
     *
     * @since version 1.0
     */
    public function deleteDocumentDropfile($did){
        $query = $this->_db->getQuery(true);

        try{
            $query->select('file,catid')
                ->from($this->_db->quoteName('#__dropfiles_files'))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote(($did)));
            $this->_db->setQuery($query);
            $file = $this->_db->loadObject();
            unlink('media/com_dropfiles/' . $file->catid . '/' . $file->file);

            $query->clear()
                ->delete($this->_db->quoteName('#__dropfiles_files'))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote(($did)));
            $this->_db->setQuery($query);
            return $this->_db->execute();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot delete the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $did
     * @param $name
     *
     * @return false
     *
     * @since version 1.0
     */
    public function editDocumentDropfile($did,$name){
        $query = $this->_db->getQuery(true);

        try{
            $query->update($this->_db->quoteName('#__dropfiles_files'))
                ->set($this->_db->quoteName('title') . ' = ' . $this->_db->quote($name))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote(($did)));
            $this->_db->setQuery($query);
            return $this->_db->execute();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot update the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $documents
     *
     * @return bool
     *
     * @since version 1.0
     */
    public function updateOrderDropfileDocuments($documents){
        $query = $this->_db->getQuery(true);

        try{
            foreach ($documents as $document) {
                $query->clear()
                    ->update($this->_db->quoteName('#__dropfiles_files'))
                    ->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote($document['ordering']))
                    ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote(($document['id'])));
                $this->_db->setQuery($query);
                $this->_db->execute();
            }

            return true;
        }  catch (Exception $e) {
            JLog::add('component/com_emundus/models/campaign | Cannot reorder the dropfile documents : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $pid
     *
     * @return array|false
     *
     * @since version 1.0
     */
    public function getFormDocuments($pid){
        $query = $this->_db->getQuery(true);

        try{
            $query->select('*')
                ->from($this->_db->quoteName('#__modules'))
                ->where('json_extract(`note`, "$.pid") LIKE ' . $this->_db->quote('"'.$pid.'"'));
            $this->_db->setQuery($query);
            $form_module = $this->_db->loadObject();

            $files = array();

            if($form_module != null) {
                // create the DOMDocument object, and load HTML from string
                $dochtml = new DOMDocument();
                $dochtml->loadHTML($form_module->content);

                // gets all DIVs
                $links = $dochtml->getElementsByTagName('a');
                foreach($links as $link) {
                    $file = new stdClass;
                    if($link->hasAttribute('href')) {
                        $file->link = $link->getAttribute('href');
                        $file->name = $link->textContent;
                    }
                    if($link->parentNode->hasAttribute('id')) {
                        $file->id = $link->parentNode->getAttribute('id');
                    }
                    $files[] = $file;
                }
            }

            return $files;
        }  catch (Exception $e) {
            JLog::add('Error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $did
     * @param $name
     * @param $pid
     *
     * @return bool
     *
     * @since version 1.0
     */
    public function editDocumentForm($did,$name,$pid){
        $query = $this->_db->getQuery(true);

        try{
            $query->select('*')
                ->from($this->_db->quoteName('#__modules'))
                ->where('json_extract(`note`, "$.pid") LIKE ' . $this->_db->quote('"'.$pid.'"'));
            $this->_db->setQuery($query);
            $form_module = $this->_db->loadObject();

            if($form_module != null) {
                // create the DOMDocument object, and load HTML from string
                $dochtml = new DOMDocument();
                $dochtml->loadHTML($form_module->content);

                // gets all DIVs
                $link_li = $dochtml->getElementById($did);
                $link = $link_li->firstChild;
                $link->textContent = $name;
                $link->parentNode->replaceChild($link,$link_li->firstChild);

                $newcontent = explode('</body>',explode('<body>',$dochtml->saveHTML())[1])[0];

                $query->clear()
                    ->update('#__modules')
                    ->set($this->_db->quoteName('content') . ' = ' . $this->_db->quote($newcontent))
                    ->where($this->_db->quoteName('id') . '=' .  $this->_db->quote($form_module->id));
                $this->_db->setQuery($query);

                return $this->_db->execute();
            } else {
                return true;
            }
        }  catch (Exception $e) {
            JLog::add('Error updating form document in component/com_emundus/models/campaign: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $did
     * @param $pid
     *
     * @return false
     *
     * @since version 1.0
     */
    public function deleteDocumentForm($did,$pid){
        $query = $this->_db->getQuery(true);

        try{
            $query->select('*')
                ->from($this->_db->quoteName('#__modules'))
                ->where('json_extract(`note`, "$.pid") LIKE ' . $this->_db->quote('"'.$pid.'"'));
            $this->_db->setQuery($query);
            $form_module = $this->_db->loadObject();

            // create the DOMDocument object, and load HTML from string
            $dochtml = new DOMDocument();
            $dochtml->loadHTML($form_module->content);

            // gets all DIVs
            $link = $dochtml->getElementById($did);
            unlink($link->firstChild->getAttribute('href'));
            $link->parentNode->removeChild($link);

            $newcontent = explode('</body>',explode('<body>',$dochtml->saveHTML())[1])[0];

            if(strpos($newcontent,'<li') === false) {
                $query->clear()
                    ->select('m.id')
                    ->from($this->_db->quoteName('#__menu', 'm'))
                    ->leftJoin($this->_db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $this->_db->quoteName('sp.menutype') . ' = ' . $db->quoteName('m.menutype'))
                    ->where($this->_db->quoteName('sp.id') . ' = ' . $this->_db->quote($pid));
                $this->_db->setQuery($query);
                $mids = $this->_db->loadObjectList();

                foreach ($mids as $mid) {
                    $query->clear()
                        ->delete($this->_db->quoteName('#__modules_menu'))
                        ->where($this->_db->quoteName('moduleid') . ' = ' . $this->_db->quote($form_module->id))
                        ->andWhere($this->_db->quoteName('menuid') . ' = ' . $this->_db->quote($mid->id));
                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                $query->clear()
                    ->delete('#__modules')
                    ->where($this->_db->quoteName('id') . '=' .  $this->_db->quote($form_module->id));
                $this->_db->setQuery($query);
                return $this->_db->execute();
            } else {
                $query->clear()
                    ->update('#__modules')
                    ->set($this->_db->quoteName('content') . ' = ' . $this->_db->quote($newcontent))
                    ->where($this->_db->quoteName('id') . '=' .  $this->_db->quote($form_module->id));
                $this->_db->setQuery($query);
                return $this->_db->execute();
            }
        }  catch (Exception $e) {
            JLog::add('Error updating form document in component/com_emundus/models/campaign: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
