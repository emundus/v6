<?php
/**
 * eMundus Campaign model
 *
 * @package        Joomla
 * @subpackage     eMundus
 * @link           http://www.emundus.fr
 * @copyright      Copyright (C) 2018 eMundus. All rights reserved.
 * @license        GNU/GPL
 * @author         Benjamin Rivalland
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');

class EmundusModelCampaign extends JModelList
{
	private $app;
	private $_em_user;
	private $_user;
	protected $_db;
	private $config;

	function __construct()
	{
		parent::__construct();
		global $option;

		JLog::addLogger([
			'text_file'         => 'com_emundus.campaign.error.php',
			'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE}'
		],
			JLog::ERROR,
			array('com_emundus')
		);

		$this->app = Factory::getApplication();

		$this->_db      = Factory::getDbo();
		$this->_em_user = $this->app->getSession()->get('emundusUser');
		$this->_user    = $this->app->getIdentity();
		$this->config   = Factory::getConfig();

		// Get pagination request variables
		$filter_order     = $this->app->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'label', 'cmd');
		$filter_order_Dir = $this->app->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$limit            = $this->app->getUserStateFromRequest('global.list.limit', 'limit', $this->app->get('list_limit'), 'int');
		$limitstart       = $this->app->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
		$limitstart       = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Get active campaign
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getActiveCampaign()
	{
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();

		return $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}

	/**
	 * Build query to get campaign
	 *
	 * @return string
	 *
	 * @since version v6
	 */
	function _buildQuery()
	{
		$timezone = new DateTimeZone($this->config->get('offset'));
		$now      = Factory::getDate()->setTimezone($timezone);

		return 'SELECT id, label, year, description, start_date, end_date
		FROM #__emundus_setup_campaigns
		WHERE published = 1 AND ' . $this->_db->Quote($now) . '>=start_date AND ' . $this->_db->Quote($now) . '<=end_date';
	}

	/**
	 * Build Content with order by
	 *
	 * @return string
	 *
	 * @since version v6
	 */
	function _buildContentOrderBy()
	{
		$orderby          = '';
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array('id', 'label', 'year', 'start_date', 'end_date');
		if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)) {
			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;
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
	function getAllowedCampaign($uid = null)
	{

		if (empty($uid)) {
			$uid = $this->_user->id;
		}

		$query = $this->_buildQuery();

		if (!empty($uid)) {
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
			$m_profile           = new EmundusModelProfile();
			$userProfiles        = $m_profile->getUserProfiles($uid);
			$userEmundusProfiles = $m_profile->getProfileByApplicant($uid);

			$newObjectProfiles = (object) array(
				'id'        => $userEmundusProfiles['profile'],
				'label'     => $userEmundusProfiles['profile_label'],
				'published' => $userEmundusProfiles['published'],
				'status'    => $userEmundusProfiles['status'],
			);

			$userProfiles[] = $newObjectProfiles;

			$eMConfig            = JComponentHelper::getParams('com_emundus');
			$applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
			$id_profiles         = $eMConfig->get('id_profiles', '0');
			$id_profiles         = explode(',', $id_profiles);

			foreach ($userProfiles as $profile) {
				if (in_array($profile->id, $id_profiles)) {
					$applicant_can_renew = 1;
					break;
				}
			}


			switch ($applicant_can_renew) {
				// Applicant can only have one file per campaign.
				case 2:
					$query .= ' AND id NOT IN (
								select campaign_id
								from #__emundus_campaign_candidature
								where applicant_id=' . $uid . '
							)';
					break;
				// Applicant can only have one file per year.
				case 3:
					$query .= ' AND year NOT IN (
								select sc.year
								from #__emundus_campaign_candidature as cc
								LEFT JOIN #__emundus_setup_campaigns as sc ON sc.id = cc.campaign_id
								where applicant_id=' . $uid . '
							)';
					break;
			}
		}

		try {
			$this->_db->setQuery($query);

			return array_column($this->_db->loadAssocList(), 'id');
		}
		catch (Exception $e) {
			JLog::add('Error at model/campaign -> query: ' . $query, JLog::ERROR, 'com_emundus.error');
		}
	}

	/**
	 * Get campaigns by my applicant_id
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getMyCampaign()
	{
		$query = $this->_db->getQuery(true);

		$query->select('esc.*')
			->from($this->_db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			->join('LEFT', $this->_db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->_db->quoteName('esc.id') . ' = ' . $this->_db->quoteName('ecc.campaign_id'))
			->where($this->_db->quoteName('ecc.applicant_id') . ' = ' . $this->_db->quote($this->_em_user->id))
			->order('ecc.date_submitted DESC');
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * @param $campaign_id
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getCampaignByID($campaign_id)
	{
		$campaign = [];

		if (!empty($campaign_id)) {
			$query = $this->_db->getQuery(true);
			$query->select('*')
				->from('#__emundus_setup_campaigns AS esc')
				->where('esc.id = ' . $campaign_id)
				->order('esc.end_date DESC');

			$this->_db->setQuery($query);

			try {
				$campaign = $this->_db->loadAssoc();
			}
			catch (Exception $e) {
				JLog::add('Failed to retrieve campaign from id ' . $campaign_id . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $campaign;
	}

	/**
	 * @param   bool  $published
	 *
	 * @return array|mixed
	 *
	 * @since version v6
	 */
	function getAllCampaigns($published = true)
	{
		$all_campaigns = [];

		$query = $this->_db->getQuery(true);
		$query->select(['tu.*'])
			->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'));

		if ($published) {
			$query->where($this->_db->quoteName('tu.published') . ' = 1');
		}

		try {
			$this->_db->setQuery($query);
			$all_campaigns = $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('Error getting campaigns at model/campaign at query :' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.error');
		}

		return $all_campaigns;
	}

	/**
	 * @param $campaign_id
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getProgrammeByCampaignID($campaign_id)
	{
		$program = [];

		if (!empty($campaign_id)) {
			$campaign = $this->getCampaignByID($campaign_id);

			if (!empty($campaign)) {
				$query = 'SELECT esp.*
					FROM #__emundus_setup_programmes AS esp
					WHERE esp.code like "' . $campaign['training'] . '"';
				$this->_db->setQuery($query);
				$program = $this->_db->loadAssoc();
			}
		}

		return $program;
	}

	/**
	 * @param $training
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getProgrammeByTraining($training)
	{
		$program = null;

		if (!empty($training)) {
			$query = 'SELECT esp.*
					FROM #__emundus_setup_programmes AS esp
					WHERE esp.code like "' . $training . '"';
			$this->_db->setQuery($query);

			$program = $this->_db->loadObject();
		}

		return $program;
	}

	/**
	 * @param $course
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getCampaignsByCourse($course)
	{
		$query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					WHERE esc.training like ' . $this->_db->Quote($course) . ' ORDER BY esc.end_date DESC';
		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getCampaignsByProgram($code)
	{
		$query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					LEFT JOIN #__emundus_setup_programmes AS esp on esp.code = esc.training
					WHERE esp.code like ' . $this->_db->Quote($code);
		$this->_db->setQuery($query);

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
	function getCampaignsByCourseCampaign($course, $camp)
	{
		$query = 'SELECT esc.*
				FROM #__emundus_setup_campaigns AS esc
				WHERE esc.training like ' . $this->_db->Quote($course) . ' AND esc.id like ' . $this->_db->Quote($camp);

		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	/**
	 * @param $course
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	static function getLastCampaignByCourse($course)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT esc.*
					FROM #__emundus_setup_campaigns AS esc
					WHERE published=1 AND esc.training like ' . $db->Quote($course) . ' ORDER BY esc.end_date DESC';
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getMySubmittedCampaign()
	{
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id=' . $this->_em_user->id . 'AND ecc.submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * @param $aid
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getCampaignByApplicant($aid)
	{
		$query = 'SELECT esc.*,ecc.fnum, esp.menutype, esp.label as profile_label
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.applicant_id=' . $aid . '
					ORDER BY ecc.date_time DESC';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * @param $fnum
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getCampaignByFnum($fnum)
	{
		$query = $this->_db->getQuery(true);

		$query->clear()
			->select('esc.*,ecc.fnum, esp.menutype, esp.label as profile_label')
			->from($this->_db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			->join('LEFT', $this->_db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->_db->quoteName('esc.id') . ' = ' . $this->_db->quoteName('ecc.campaign_id'))
			->join('LEFT', $this->_db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $this->_db->quoteName('esp.id') . ' = ' . $this->_db->quoteName('esc.profile_id'))
			->where($this->_db->quoteName('ecc.fnum') . ' = ' . $this->_db->quote($fnum))
			->order('ecc.date_time DESC');
		$this->_db->setQuery($query);

		return $this->_db->loadObject();
	}

	/**
	 * @param $aid
	 *
	 * @return mixed
	 *
	 * @since version v6
	 */
	function getCampaignSubmittedByApplicant($aid)
	{
		$query = 'SELECT esc.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id=' . $aid . 'AND submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * @param $cid
	 * @param $aid
	 *
	 *
	 * @since version v6
	 */
	function setSelectedCampaign($cid, $aid)
	{

		$query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `campaign_id`, `fnum`)
		VALUES (' . $aid . ', ' . $cid . ', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
		$this->_db->setQuery($query);
		try {
			$this->_db->Query();
		}
		catch (Exception $e) {
			JLog::add('Error getting selected campaign ' . $cid . ' at model/campaign at query :' . preg_replace("/[\r\n]/", " ", $query), JLog::ERROR, 'com_emundus.error');
		}
	}

	/**
	 * @param $aid
	 * @param $campaign_id
	 *
	 *
	 * @since version v6
	 */
	function setResultLetterSent($aid, $campaign_id)
	{
		$query = 'UPDATE #__emundus_final_grade SET result_sent=1, date_result_sent=NOW() WHERE student_id=' . $aid . ' AND campaign_id=' . $campaign_id;
		$this->_db->setQuery($query);
		try {
			$this->_db->Query();
		}
		catch (Exception $e) {
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
	function isOtherActiveCampaign($aid)
	{
		$query = 'SELECT count(id) as cpt
				FROM #__emundus_setup_campaigns
				WHERE id NOT IN (
								select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id=' . $aid . '
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
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
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
			$query        = $this->_buildQuery();
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
		$db    = JFactory::getDBO();
		$query = 'SELECT cc.id, cc.applicant_id, sc.start_date, sc.end_date, sc.label, sc.year
		FROM #__emundus_setup_campaigns AS sc
		LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
		WHERE sc.published=1';

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to create a new compaign for all active programmes.
	 *
	 * @param   array  $data        The data to use as campaign definition.
	 * @param   array  $programmes  The list of programmes who need a new campaign.
	 *
	 * @return  String  Does it work.
	 * @since version v6
	 */
	public function addCampaignsForProgrammes($data, $programmes)
	{
		$data['date_time'] = date("Y-m-d H:i:s");
		$data['user']      = $this->_user->id;
		$data['label']     = '';
		$data['training']  = '';
		$data['published'] = 1;

		if (!empty($data) && !empty($programmes)) {
			$column = array_keys($data);

			$values       = array();
			$values_unity = array();
			$result       = '';
			foreach ($programmes as $v) {
				try {
					$query = 'SELECT count(id) FROM `#__emundus_setup_campaigns` WHERE year LIKE ' . $this->_db->Quote($data['year']) . ' AND  training LIKE ' . $this->_db->Quote($v['code']);
					$this->_db->setQuery($query);
					$cpt = $this->_db->loadResult();

					if ($cpt == 0) {
						$values[]       = '(' . $this->_db->Quote($data['start_date']) . ', ' . $this->_db->Quote($data['end_date']) . ', ' . $data['profile_id'] . ', ' . $this->_db->Quote($data['year']) . ', ' . $this->_db->Quote($data['short_description']) . ', ' . $this->_db->Quote($data['date_time']) . ', ' . $data['user'] . ', ' . $this->_db->Quote($v['label']) . ', ' . $this->_db->Quote($v['code']) . ', ' . $data['published'] . ')';
						$values_unity[] = '(' . $this->_db->Quote($v['code']) . ', ' . $this->_db->Quote($v['label']) . ', ' . $this->_db->Quote($data['year']) . ', ' . $data['profile_id'] . ', ' . $this->_db->Quote($v['programmes']) . ')';

						$result .= '<i class="green check circle outline icon"></i> ' . $v['label'] . ' [' . $data['year'] . '] [' . $v['code'] . '] ' . JText::_('CREATED') . '<br>';
					}
					else {
						$result .= '<i class="orange remove circle outline icon"></i> ' . $v['label'] . ' [' . $data['year'] . '] [' . $v['code'] . '] ' . JText::_('ALREADY_EXIST') . '<br>';
					}
				}
				catch (Exception $e) {
					JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus.error');

					return $e->getMessage();
				}
			}

			try {
				if (!empty($values)) {
					$query = 'INSERT INTO `#__emundus_setup_campaigns` (`' . implode('`, `', $column) . '`) VALUES ' . implode(',', $values);
					$this->_db->setQuery($query);
					$this->_db->execute();

					$query = 'INSERT INTO `#__emundus_setup_teaching_unity` (`code`, `label`, `schoolyear`, `profile_id`, `programmes`) VALUES ' . implode(',', $values_unity);
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus.error');

				return $e->getMessage();
			}
		}
		else {
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
	function getLatestCampaign()
	{
		$latestCampaign = '';

		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName('training'))
			->from($this->_db->quoteName('#__emundus_setup_campaigns'))
			->order('id DESC')
			->setLimit('1');

		try {
			$this->_db->setQuery($query);
			$latestCampaign = $this->_db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting latest programme at model/campaign at query :' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.error');
		}

		return $latestCampaign;
	}


	/**
	 * Gets all elements in teaching unity table
	 * @return array
	 *
	 * @since version v6
	 */
	function getCCITU()
	{
		$query = $this->_db->getQuery(true);

		$query->select(['tu.*', 'p.prerequisite', 'p.audience', 'p.objectives', 'p.content', 'p.manager_firstname', 'p.manager_lastname', 'p.pedagogie', 't.label AS thematique', 'p.id AS row_id'])
			->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'p') . ' ON ' . $this->_db->quoteName('tu.code') . ' LIKE ' . $this->_db->quoteName('p.code'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_thematiques', 't') . ' ON ' . $this->_db->quoteName('t.id') . ' = ' . $this->_db->quoteName('p.programmes'))
			->where($this->_db->quoteName('tu.published') . ' = 1 AND ' . $this->_db->quoteName('p.published') . ' = 1');

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('Error getting latest programme at model/campaign at query :' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.error');

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
	function getTeachingUnity($id = null)
	{
		$response = [];

		$query = $this->_db->getQuery(true);
		$query->select(['tu.*'])
			->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'));

		if (!empty($id) && is_numeric($id)) {
			$query->where($this->_db->quoteName('tu.id') . ' = ' . $id);
		}

		try {
			$this->_db->setQuery($query);
			$response = $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('Error getting latest programme at model/campaign at query :' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.error');
		}

		return $response;
	}

	/**
	 * Get campaign limit params
	 *
	 * @param $id
	 *
	 * @return Object|mixed
	 *
	 * @since 1.2.0
	 *
	 */
	public function getLimit($id)
	{
		$query = $this->_db->getQuery(true);

		$query
			->select([$this->_db->quoteName('esc.is_limited'), $this->_db->quoteName('esc.limit'), 'GROUP_CONCAT(escrl.limit_status) AS steps'])
			->from($this->_db->quoteName('#__emundus_setup_campaigns', 'esc'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status', 'escrl') . ' ON ' . $this->_db->quoteName('escrl.parent_id') . ' = ' . $this->_db->quoteName('esc.id'))
			->where($this->_db->quoteName('esc.id') . ' = ' . $id);

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObject();
		}
		catch (Exception $exception) {
			JLog::add('Error getting campaign limit at query :' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.error');

			return null;
		}

	}

	/**
	 * Check if campaign's limit is obtained
	 *
	 * @param $id
	 *
	 * @return Object|mixed
	 *
	 * @since 1.2.0
	 *
	 */
	public function isLimitObtained($id)
	{
		$is_limit_obtained = null;

		if (EmundusHelperAccess::isApplicant($this->_user->id) && !empty($id)) {
			$limit = $this->getLimit($id);

			if (!empty($limit->is_limited)) {
				$query = $this->_db->getQuery(true);

				$query->select('COUNT(id)')
					->from($this->_db->quoteName('#__emundus_campaign_candidature'))
					->where($this->_db->quoteName('status') . ' IN (' . $limit->steps . ')')
					->andWhere($this->_db->quoteName('campaign_id') . ' = ' . $id);

				try {
					$this->_db->setQuery($query);
					$is_limit_obtained = ($limit->limit <= $this->_db->loadResult());
				}
				catch (Exception $exception) {
					JLog::add('Error checking obtained limit at query :' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.error');
				}
			}
		}

		return $is_limit_obtained;
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
	function getAssociatedCampaigns($filter = '', $sort = 'DESC', $recherche = '', $lim = 25, $page = 0, $program = 'all', $session = 'all')
	{
		$associated_campaigns = [];

		$query = $this->_db->getQuery(true);

		$limit = empty($lim) ? 25 : $lim;

		if (empty($page)) {
			$offset = 0;
		}
		else {
			$offset = ($page - 1) * $limit;
		}

		if (empty($sort)) {
			$sort = 'DESC';
		}
		$sortDb = 'sc.id ';

		$date = new Date();

		// Get affected programs
		require_once(JPATH_SITE . '/components/com_emundus/models/programme.php');
		$m_programme = new EmundusModelProgramme;
		$programs    = $m_programme->getUserPrograms($this->_user->id);

		if (!empty($programs)) {
			if ($program != "all") {
				$programs = array_filter($programs, function ($value) use ($program) {
					return $value == $program;
				});
			}
			//

			$filterDate = null;
			if ($filter == 'yettocome') {
				$filterDate = 'Date(' . $this->_db->quoteName('sc.start_date') . ') > ' . $this->_db->quote($date);
			}
			elseif ($filter == 'ongoing') {
				$filterDate =
					'(Date(' .
					$this->_db->quoteName('sc.end_date') .
					')' .
					' >= ' .
					$this->_db->quote($date) .
					' OR end_date = "0000-00-00 00:00:00") AND ' .
					$this->_db->quoteName('sc.start_date') .
					' <= ' .
					$this->_db->quote($date);
			}
			elseif ($filter == 'Terminated') {
				$filterDate =
					'Date(' .
					$this->_db->quoteName('sc.end_date') .
					')' .
					' <= ' .
					$this->_db->quote($date) .
					' AND end_date != "0000-00-00 00:00:00"';
			}
			elseif ($filter == 'Publish') {
				$filterDate = $this->_db->quoteName('sc.published') . ' = 1';
			}
			elseif ($filter == 'Unpublish') {
				$filterDate = $this->_db->quoteName('sc.published') . ' = 0';
			}

			$fullRecherche = null;
			if (!empty($recherche)) {
				$fullRecherche = '(' .
					$this->_db->quoteName('sc.label') .
					' LIKE ' .
					$this->_db->quote('%' . $recherche . '%') . ')';
			}

			$query->select([
				'sc.*',
				'COUNT(CASE cc.published WHEN 1 THEN 1 ELSE NULL END) as nb_files',
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
				);

			$query->where($this->_db->quoteName('sc.training') . ' IN (' . implode(',', $this->_db->quote($programs)) . ')');

			if (!empty($filterDate)) {
				$query->andWhere($filterDate);
			}
			if (!empty($fullRecherche)) {
				$query->andWhere($fullRecherche);
			}
			if ($session !== 'all') {
				$query->andWhere($this->_db->quoteName('year') . ' = ' . $this->_db->quote($session));
			}
			$query->group($sortDb)
				->order($sortDb . $sort);

			try {
				$this->_db->setQuery($query);
				$campaigns_count = sizeof($this->_db->loadObjectList());

				$this->_db->setQuery($query, $offset, $limit);
				$campaigns = $this->_db->loadObjectList();

				if (empty($campaigns) && $offset != 0) {
					return $this->getAssociatedCampaigns($filter, $sort, $recherche, $lim, 0, $program, $session);
				}
				$associated_campaigns = array('datas' => $campaigns, 'count' => $campaigns_count);
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when try to get list of campaigns : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $associated_campaigns;
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
	function getCampaignsByProgramId($program)
	{
		$campaigns = [];

		if (!empty($program)) {
			$query = $this->_db->getQuery(true);
			$date  = new Date();

			$query->select('sc.*')
				->from($this->_db->quoteName('#__emundus_setup_programmes', 'sp'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sp.code') . ' LIKE ' . $this->_db->quoteName('sc.training'))
				->where($this->_db->quoteName('sp.id') . ' = ' . $this->_db->quote($program))
				->andWhere($this->_db->quoteName('sc.end_date') . ' >= ' . $this->_db->quote($date));

			try {
				$this->_db->setQuery($query);
				$campaigns = $this->_db->loadObjectList();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when try to get campaigns associated to programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $campaigns;
	}

	/**
	 * Delete a campaign
	 *
	 * @param         $data
	 * @param   bool  $force_delete  - if true, delete campaign even if it has files, and delete files too
	 *                               Force delete is only available for super admin users because it can be dangerous
	 *
	 * @return bool
	 *
	 * @since version 1.0
	 */
	public function deleteCampaign($data, $force_delete = false)
	{
		$deleted = false;

		if (!empty($data)) {
			$data = !is_array($data) ? [$data] : $data;

			require_once(JPATH_ROOT . '/components/com_emundus/models/falang.php');
			$falang = new EmundusModelFalang();

			JFactory::getApplication()->triggerEvent('onBeforeCampaignDelete', $data);
			JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeCampaignDelete', ['campaign' => $data]]);

			$query = $this->_db->getQuery(true);

			try {

				foreach (array_values($data) as $id) {
					$falang->deleteFalang($id, 'emundus_setup_campaigns', 'label');
				}

				if ($force_delete === true) {
					$query->delete($this->_db->quoteName('#__emundus_campaign_candidature'))
						->where($this->_db->quoteName('campaign_id') . ' IN (' . implode(", ", array_values($data)) . ')');

					$this->_db->setQuery($query);
					$this->_db->execute();

					$query->clear()
						->delete($this->_db->quoteName('#__emundus_setup_campaigns'))
						->where($this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

					$this->_db->setQuery($query);
					$deleted = $this->_db->execute();
					JLog::add('User ' . JFactory::getUser()->id . ' deleted campaign(s) ' . implode(", ", array_values($data)) . ' ' . date('d/m/Y H:i:s'), JLog::INFO, 'com_emundus');
				}
				else {
					// delete only if there are no files attached to the campaign
					$query->clear()
						->select('count(*)')
						->from($this->_db->quoteName('#__emundus_campaign_candidature'))
						->where($this->_db->quoteName('campaign_id') . ' IN (' . implode(", ", array_values($data)) . ')');

					$this->_db->setQuery($query);
					$nb_files = $this->_db->loadResult();

					if ($nb_files < 1) {
						$query->clear()
							->update($this->_db->quoteName('#__emundus_setup_campaigns'))
							->set($this->_db->quoteName('published') . ' = 0')
							->where($this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

						$this->_db->setQuery($query);
						$deleted = $this->_db->execute();
					}
				}

				if ($deleted) {
					JFactory::getApplication()->triggerEvent('onAfterCampaignDelete', $data);
					JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterCampaignDelete', ['campaign' => $data]]);
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when delete campaigns : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $deleted;
	}

	/**
	 *
	 * @param $data
	 *
	 * @return false
	 *
	 * @since version 1.0
	 */
	public function unpublishCampaign($data)
	{
		$unpublished = false;

		if (!empty($data)) {
			if (!is_array($data)) {
				$data = [$data];
			}

			$query = $this->_db->getQuery(true);
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($val);
			}


			JFactory::getApplication()->triggerEvent('onBeforeCampaignUnpublish', $data);
			JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeCampaignUnpublish', ['campaign' => $data]]);

			try {
				$fields        = [
					$this->_db->quoteName('published') . ' = 0'
				];
				$sc_conditions = [
					$this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')'
				];

				$query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
					->set($fields)
					->where($sc_conditions);

				$this->_db->setQuery($query);
				$unpublished = $this->_db->execute();

				if ($unpublished) {
					JFactory::getApplication()->triggerEvent('onAfterCampaignUnpublish', $data);
					JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterCampaignUnpublish', ['campaign' => $data]]);
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when unpublish campaigns : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $unpublished;
	}

	/**
	 *
	 * @param $data
	 *
	 * @return false
	 *
	 * @since version 1.0
	 */
	public function publishCampaign($data)
	{
		$published = false;

		if (!empty($data)) {
			if (!is_array($data)) {
				$data = [$data];
			}

			$query = $this->_db->getQuery(true);
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($val);
			}


			JFactory::getApplication()->triggerEvent('onBeforeCampaignPublish', $data);
			JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeCampaignPublish', ['campaign' => $data]]);
			try {
				$fields        = [$this->_db->quoteName('published') . ' = 1'];
				$sc_conditions = [$this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')'];

				$query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
					->set($fields)
					->where($sc_conditions);

				$this->_db->setQuery($query);
				$published = $this->_db->execute();

				if ($published) {
					JFactory::getApplication()->triggerEvent('onAfterCampaignPublish', $data);
					JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterCampaignPublish', ['campaign' => $data]]);
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when publish campaigns : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $published;
	}

	/**
	 * @param $data
	 *
	 * @return false|mixed|string
	 *
	 * @since version 1.0
	 */
	public function duplicateCampaign($id)
	{
		$duplicated = false;

		if (!empty($id)) {
			$query = $this->_db->getQuery(true);

			try {
				$columns = array_keys(
					$this->_db->getTableColumns('#__emundus_setup_campaigns')
				);

				$columns = array_filter($columns, function ($k) {
					return $k != 'id' && $k != 'date_time' && $k != 'pinned';
				});

				$query->clear()
					->select(implode(',', $this->_db->qn($columns)))
					->from($this->_db->quoteName('#__emundus_setup_campaigns'))
					->where($this->_db->quoteName('id') . ' = ' . $id);

				$this->_db->setQuery($query);
				$values[] = implode(', ', $this->_db->quote($this->_db->loadRow()));

				$query->clear()
					->insert($this->_db->quoteName('#__emundus_setup_campaigns'))
					->columns(implode(',', $this->_db->quoteName($columns)))
					->values($values);

				$this->_db->setQuery($query);
				$duplicated = $this->_db->execute();

				if ($duplicated) {
					$new_campaign_id = $this->_db->insertid();

					if (!empty($new_campaign_id)) {
						$new_category_id = $this->getCampaignCategory($new_campaign_id);

						if (!empty($new_category_id)) {
							$old_category_id        = $this->getCampaignCategory($id);
							$old_campaign_documents = $this->getCampaignDropfilesDocuments($old_category_id);

							if (!empty($old_campaign_documents)) {
								foreach ($old_campaign_documents as $document) {
									$document->catid  = $new_category_id;
									$document->author = $this->_user->id;

									$columns = array_keys($this->_db->getTableColumns('#__dropfiles_files'));
									$columns = array_filter($columns, function ($k) {
										return $k != 'id';
									});

									$values = '';
									foreach ($columns as $column) {
										$values .= $this->_db->quote($document->$column) . ', ';
									}
									$values = rtrim($values, ', ');

									$query->clear()
										->insert($this->_db->quoteName('#__dropfiles_files'))
										->columns(implode(',', $this->_db->quoteName($columns)))
										->values($values);

									$this->_db->setQuery($query);
									$this->_db->execute();
								}
							}
						}
					}
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when duplicate campaigns : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $duplicated;
	}

	//TODO Throw in the years model

	/**
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function getYears()
	{
		$years = [];

		$query = $this->_db->getQuery(true);
		$query->select('DISTINCT(tu.schoolyear)')
			->from($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
			->order('tu.id DESC');

		try {
			$this->_db->setQuery($query);
			$years = $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add(preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
		}

		return $years;
	}

	/**
	 * @param $data
	 *
	 * @return int campaign_id, 0 if failed
	 *
	 * @since version 1.0
	 */
	public function createCampaign($data)
	{
		$campaign_id = 0;

		if (!empty($data) && !empty($data['label'])) {
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'settings.php');
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
			$m_settings = new EmundusModelSettings;
			$m_emails   = new EmundusModelEmails;

			if (version_compare(JVERSION, '4.0', '>')) {
				$lang = $this->app->getLanguage();
			}
			else {
				$lang = Factory::getLanguage();
			}

			$actualLanguage = !empty($lang->getTag()) ? substr($lang->getTag(), 0, 2) : 'fr';

			$i            = 0;
			$labels       = new stdClass;
			$limit_status = [];

			$query = "SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'jos_emundus_setup_campaigns'";
			$this->_db->setQuery($query);
			$campaign_columns = $this->_db->loadColumn();

			$data['label'] = json_decode($data['label'], true);


			$this->app->triggerEvent('onBeforeCampaignCreate', $data);
			$this->app->triggerEvent('onCallEventHandler', ['onBeforeCampaignCreate', ['campaign' => $data]]);

			$query = $this->_db->getQuery(true);

			foreach ($data as $key => $val) {
				if (!in_array($key, $campaign_columns)) {
					unset($data[$key]);
				}
				else {
					if ($key == 'profileLabel') {
						unset($data['profileLabel']);
					}
					if ($key == 'label') {
						$labels->fr    = !empty($data['label']['fr']) ? $data['label']['fr'] : '';
						$labels->en    = !empty($data['label']['en']) ? $data['label']['en'] : '';
						$data['label'] = $data['label'][$actualLanguage];
					}
					if ($key == 'description' && $data['description'] == 'null') {
						$data['description'] = '';
					}
					if ($key == 'limit_status') {
						$limit_status = $data['limit_status'];
						unset($data['limit_status']);
					}
					if ($key == 'profile_id') {
						$query->select('id')
							->from($this->_db->quoteName('#__emundus_setup_profiles'))
							->where($this->_db->quoteName('published') . ' = 1')
							->andWhere($this->_db->quoteName('status') . ' = 1');
						$this->_db->setQuery($query);
						$data['profile_id'] = $this->_db->loadResult();

						if (empty($data['profile_id'])) {
							unset($data['profile_id']);
							$data['published'] = 0;
						}
					}
				}
			}

			if (!empty($data['label'])) {
				$query->clear()
					->insert($this->_db->quoteName('#__emundus_setup_campaigns'))
					->columns($this->_db->quoteName(array_keys($data)))
					->values(implode(',', $this->_db->Quote(array_values($data))));

				try {
					$this->_db->setQuery($query);
					$this->_db->execute();
					$campaign_id = $this->_db->insertid();

					if (!empty($campaign_id)) {
						if ($data['is_limited'] == 1) {
							foreach ($limit_status as $key => $limit_statu) {
								if ($limit_statu == 'true') {
									$query->clear()
										->insert($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'));
									$query->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($campaign_id))
										->set($this->_db->quoteName('limit_status') . ' = ' . $this->_db->quote($key));
									$this->_db->setQuery($query);
									$this->_db->execute();
								}
							}
						}

						$m_settings->onAfterCreateCampaign($this->_user->id);

						// Create a default trigger
						if (!empty($data['training'])) {
							$query->clear()
								->select('id')
								->from($this->_db->quoteName('#__emundus_setup_programmes'))
								->where($this->_db->quoteName('code') . ' LIKE ' . $this->_db->quote($data['training']));
							$this->_db->setQuery($query);
							$pid = $this->_db->loadResult();

							if (!empty($pid)) {
								$emails = $m_emails->getTriggersByProgramId($pid);

								if (empty($emails)) {
									$trigger = array(
										'status'        => 1,
										'model'         => 1,
										'action_status' => 'to_current_user',
										'target'        => -1,
										'program'       => $pid,
									);
									$m_emails->createTrigger($trigger, array(), $this->_user);
								}
							}
						}

						// Create teaching unity
						$this->createYear($data);

						JFactory::getApplication()->triggerEvent('onAfterCampaignCreate', ['campaign_id' => $campaign_id]);
						JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterCampaignCreate', ['campaign' => $campaign_id]]);
					}
				}
				catch (Exception $e) {
					JLog::add('component/com_emundus/models/campaign | Error when create the campaign : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

					return $e->getMessage();
				}
			}
		}

		return $campaign_id;
	}

	/**
	 * @param $data
	 * @param $cid
	 *
	 * @return bool|string
	 *
	 * @since version 1.0
	 */
	public function updateCampaign($data, $cid)
	{
		$updated = false;

		if (!empty($data) && !empty($cid)) {
			if (isset($data['start_date']) && empty($data['start_date'])) {
				return $updated;
			}
			if (isset($data['end_date']) && empty($data['end_date'])) {
				return $updated;
			}

			$query = $this->_db->getQuery(true);

			require_once(JPATH_ROOT . '/components/com_emundus/models/falang.php');
			require_once(JPATH_SITE . '/components/com_emundus/helpers/date.php');

			$m_falang       = new EmundusModelFalang;
			$lang           = JFactory::getLanguage();
			$actualLanguage = substr($lang->getTag(), 0, 2);

			$limit_status = [];
			$fields       = [];
			$labels       = new stdClass;


			JFactory::getApplication()->triggerEvent('onBeforeCampaignUpdate', $data);
			JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeCampaignUpdate', ['campaign' => $cid]]);

			foreach ($data as $key => $val) {
				switch ($key) {
					case 'label':
						$labels        = $data['label'];
						$data['label'] = $data['label'][$actualLanguage];
						$fields[]      = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($data['label']);
						break;
					case 'limit_status':
						$limit_status = $data['limit_status'];
						break;
					case 'end_date':
					case 'start_date':
						$display_date = EmundusHelperDate::displayDate($val, 'Y-m-d H:i:s', 1);
						if (!empty($display_date)) {
							$fields[] = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($display_date);
						}
						else {
							JLog::add('Attempt to update campaign ' . $key . ' with value ' . $val . ' failed.', JLog::WARNING, 'com_emundus.error');
						}
						break;
					case 'profileLabel':
					case 'progid':
					case 'status':
						// do nothing
						break;
					default:
						$insert   = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($val);
						$fields[] = $insert;
						break;
				}
			}

			$m_falang->updateFalang($labels, $cid, 'emundus_setup_campaigns', 'label');

			$query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
				->set($fields)
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($cid));

			try {
				$this->_db->setQuery($query);
				$updated = $this->_db->execute();

				if ($updated) {
					$query->clear()
						->delete($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'))
						->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($cid));
					$this->_db->setQuery($query);
					$this->_db->execute();

					if ($data['is_limited'] == 1) {
						foreach ($limit_status as $key => $limit_statu) {
							if ($limit_statu == 'true') {
								$query->clear()
									->insert($this->_db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'))
									->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($cid))
									->set($this->_db->quoteName('limit_status') . ' = ' . $this->_db->quote($key));

								$this->_db->setQuery($query);
								$this->_db->execute();
							}
						}
					}

					$this->createYear($data);

					JFactory::getApplication()->triggerEvent('onAfterCampaignUpdate', $data);
					JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterCampaignUpdate', ['campaign' => $cid]]);
				}
				else {
					JLog::add('Attempt to update $campaign ' . $cid . ' with data ' . json_encode($data) . ' failed.', JLog::WARNING, 'com_emundus.error');
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error when update the campaign : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $updated;
	}

	/**
	 * @param $data
	 * @param $profile
	 *
	 * @return bool|string
	 *
	 * @since version 1.0
	 */
	public function createYear($data, $profile = null)
	{
		$created = false;

		$prid = !empty($profile) ? $profile : $data['profile_id'];

		if (!empty($prid)) {
			$query = $this->_db->getQuery(true);

			try {
				// Check if teaching unity does not already exists
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
						->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($prid))
						->set($this->_db->quoteName('date_start') . ' = ' . $this->_db->quote($data['start_date']))
						->set($this->_db->quoteName('date_end') . ' = ' . $this->_db->quote($data['end_date']));
					$this->_db->setQuery($query);
					$created = $this->_db->execute();
				}
				else {
					$created = true;
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error at year creation : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $created;
	}

	/**
	 * @param $id
	 *
	 * @return false|stdClass
	 *
	 * @since version 1.0
	 */
	public function getCampaignDetailsById($id)
	{
		if (empty($id)) {
			return false;
		}

		require_once(JPATH_ROOT . '/components/com_emundus/models/falang.php');
		$m_falang = new EmundusModelFalang;

		$query = $this->_db->getQuery(true);

		$results = new stdClass();

		try {
			$query->select(['sc.*', 'spr.label AS profileLabel', 'sp.id as progid'])
				->from($this->_db->quoteName('#__emundus_setup_campaigns', 'sc'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_profiles', 'spr') . ' ON ' . $this->_db->quoteName('spr.id') . ' = ' . $this->_db->quoteName('sc.profile_id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'sp') . ' ON ' . $this->_db->quoteName('sp.code') . ' = ' . $this->_db->quoteName('sc.training'))
				->where($this->_db->quoteName('sc.id') . ' = ' . $id);

			$this->_db->setQuery($query);
			$results->campaign = $this->_db->loadObject();
			$results->label    = $m_falang->getFalang($id, 'emundus_setup_campaigns', 'label');

			if ($results->campaign->is_limited == 1) {
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
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Error at getting the campaign by id ' . $id . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

			return false;
		}
	}

	/**
	 *
	 * @return false|mixed|null
	 *
	 * @since version 1.0
	 */
	public function getCreatedCampaign()
	{
		$query = $this->_db->getQuery(true);

		$currentDate = date('Y-m-d H:i:s');

		$query->select('*')
			->from($this->_db->quoteName('#__emundus_setup_campaigns'))
			->where($this->_db->quoteName('date_time') . ' = ' . $this->_db->quote($currentDate));

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObject();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Error at getting the campaign created today : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

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
	public function updateProfile($profile, $campaign)
	{
		$updated = false;

		if (!empty($profile) && !empty($campaign)) {
			$query = $this->_db->getQuery(true);
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
					require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'form.php');
					$m_form = new EmundusModelForm;
					$m_form->addChecklistMenu($profile);
				}

				$query = $this->_db->getQuery(true);
				$query->update($this->_db->quoteName('#__emundus_setup_campaigns'))
					->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($profile))
					->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($campaign));

				$this->_db->setQuery($query);
				$this->_db->execute();

				// Create teaching unity
				$this->createYear($schoolyear, $profile);
				//

				$updated = true;
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error at updating setup_profile of the campaign: ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $updated;
	}

	/**
	 * Get campaigns without applicant files
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	public function getCampaignsToAffect()
	{
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
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Error getting campaigns without setup_profiles associated: ' . preg_replace("/[\r\n]/", " ", $query . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

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
	public function getCampaignsToAffectByTerm($term)
	{
		$campaigns_to_affect = [];

		$query = $this->_db->getQuery(true);
		$date  = new Date();

		// Get affected programs
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');

		$m_programme = new EmundusModelProgramme;
		$programs    = $m_programme->getUserPrograms($this->_user->id);

		if (!empty($programs)) {
			$searchName = $this->_db->quoteName('label') . ' LIKE ' . $this->_db->quote('%' . $term . '%');

			$query->select('id,label')
				->from($this->_db->quoteName('#__emundus_setup_campaigns'))
				->where($this->_db->quoteName('profile_id') . ' IS NULL')
				->andWhere($this->_db->quoteName('end_date') . ' >= ' . $this->_db->quote($date))
				->andWhere($searchName)
				->andWhere($this->_db->quoteName('training') . ' IN (' . implode(',', $this->_db->quote($programs)) . ')');

			try {
				$this->_db->setQuery($query);
				$campaigns_to_affect = $this->_db->loadObjectList();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Error getting campaigns without setup_profiles associated with search terms : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $campaigns_to_affect;
	}

	/**
	 * @param $document
	 * @param $types
	 * @param $cid
	 * @param $pid
	 *
	 * @return array
	 *
	 * @since version 1.0
	 */
	public function createDocument($document, $types, $cid, $pid)
	{
		$created = [
			'status' => false,
			'msg'    => JText::_('ERROR_CANNOT_ADD_DOCUMENT')
		];

		if (empty($pid)) {
			$created['msg'] = 'Missing profile id';
		}
		else {
			$query          = $this->_db->getQuery(true);
			$lang           = JFactory::getLanguage();
			$actualLanguage = substr($lang->getTag(), 0, 2);
			$types          = implode(";", array_values($types));

			if (empty($document['name'][$actualLanguage]) || empty($types)) {
				$created['msg'] = 'Missing name or types';
			}
			else {
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
				if ($document['minResolution'] != null and $document['maxResolution'] != null) {
					if (empty($document['minResolution']['width']) or (int) $document['minResolution']['width'] == 0) {
						$document['minResolution']['width'] = 'null';
					}

					if (empty($document['minResolution']['height']) or (int) $document['minResolution']['height'] == 0) {
						$document['minResolution']['height'] = 'null';
					}

					if (empty($document['maxResolution']['width']) or (int) $document['maxResolution']['width'] == 0) {
						$document['maxResolution']['width'] = 'null';
					}

					if (empty($document['maxResolution']['height']) or (int) $document['maxResolution']['height'] == 0) {
						$document['maxResolution']['height'] = 'null';
					}

					$query
						->set($this->_db->quoteName('min_width') . ' = ' . $document['minResolution']['width'])
						->set($this->_db->quoteName('min_height') . ' = ' . $document['minResolution']['height'])
						->set($this->_db->quoteName('max_width') . ' = ' . $document['maxResolution']['width'])
						->set($this->_db->quoteName('max_height') . ' = ' . $document['maxResolution']['height']);
				}

				try {
					require_once(JPATH_ROOT . '/components/com_emundus/models/falang.php');
					$m_falang = new EmundusModelFalang;
					$this->_db->setQuery($query);
					$this->_db->execute();
					$newdocument = $this->_db->insertid();
					$m_falang->insertFalang($document['name'], $newdocument, 'emundus_setup_attachments', 'value');
					$m_falang->insertFalang($document['description'], $newdocument, 'emundus_setup_attachments', 'description');

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
					$created['status'] = $newdocument;
				}
				catch (Exception $e) {
					JLog::add('component/com_emundus/models/campaign | Cannot create a document : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
					$created['msg'] = $e->getMessage();
				}
			}
		}

		return $created;
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
	public function updateDocument($document, $types, $did, $pid, $params = [])
	{
		$query = $this->_db->getQuery(true);

		$lang           = JFactory::getLanguage();
		$actualLanguage = substr($lang->getTag(), 0, 2);

		require_once(JPATH_ROOT . '/components/com_emundus/models/falang.php');
		$m_falang = new EmundusModelFalang;

		$types = implode(";", array_values($types));

		$query->update($this->_db->quoteName('#__emundus_setup_attachments'));
		$query->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($document['name'][$actualLanguage]))
			->set($this->_db->quoteName('description') . ' = ' . $this->_db->quote($document['description'][$actualLanguage]))
			->set($this->_db->quoteName('allowed_types') . ' = ' . $this->_db->quote($types))
			->set($this->_db->quoteName('nbmax') . ' = ' . $this->_db->quote($document['nbmax']));

		/// many cases
		if (isset($document['minResolution'])) {

			/// isset + !empty - !is_null === !empty (just it)
			if (!empty($document['minResolution']['width'])) {
				$query->set($this->_db->quoteName('min_width') . ' = ' . $document['minResolution']['width']);
			}
			else {
				$query->set($this->_db->quoteName('min_width') . ' = null');
			}

			/// isset + !empty - !is_null === !empty (just it)
			if (!empty($document['minResolution']['height'])) {
				$query->set($this->_db->quoteName('min_height') . ' = ' . $document['minResolution']['height']);
			}
			else {
				$query->set($this->_db->quoteName('min_height') . ' = null');
			}
		}
		else {
			$query->set($this->_db->quoteName('min_width') . ' = null')
				->set($this->_db->quoteName('min_height') . ' = null');
		}

		if (isset($document['maxResolution'])) {
			/// isset + !empty - !is_null === !empty (just it)
			if (!empty($document['maxResolution']['width'])) {
				$query->set($this->_db->quoteName('max_width') . ' = ' . $document['maxResolution']['width']);
			}
			else {
				$query->set($this->_db->quoteName('max_width') . ' = null');
			}

			/// isset + !empty - !is_null === !empty (just it)
			if (!empty($document['maxResolution']['height'])) {
				$query->set($this->_db->quoteName('max_height') . ' = ' . $document['maxResolution']['height']);
			}
			else {
				$query->set($this->_db->quoteName('max_height') . ' = null');
			}
		}
		else {
			$query->set($this->_db->quoteName('max_width') . ' = null')
				->set($this->_db->quoteName('max_height') . ' = null');
		}

		$query->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($did));

		try {
			$this->_db->setQuery($query);
			$this->_db->execute();
			$query->clear()
				->update($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
				->set($this->_db->quoteName('mandatory') . ' = ' . $this->_db->quote($document['mandatory']))
				->where($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did))
				->andWhere($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid));

			$this->_db->setQuery($query);
			$this->_db->execute();

			$m_falang->updateFalang($document['name'], $did, 'emundus_setup_attachments', 'value');
			$m_falang->updateFalang($document['description'], $did, 'emundus_setup_attachments', 'description');

			$query->clear()
				->select('count(id)')
				->from($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
				->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
				->andWhere($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did));
			$this->_db->setQuery($query);
			$assignations = $this->_db->loadResult();

			if (empty($assignations)) {
				$query->clear()
					->select('max(ordering)')
					->from($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
					->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid));
				$this->_db->setQuery($query);
				$ordering = $this->_db->loadResult();

				$query->clear()
					->insert($this->_db->quoteName('#__emundus_setup_attachment_profiles'));
				$query->set($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
					->set($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did))
					->set($this->_db->quoteName('mandatory') . ' = ' . $this->_db->quote($document['mandatory']))
					->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote($ordering + 1))
					->set($this->_db->quoteName('has_sample') . ' = ' . $params['has_sample']);

				if ($did === 20) {
					$query->set($this->_db->quoteName('displayed') . ' = ' . 0);
				}

				$this->_db->setQuery($query);
				$this->_db->execute();
			}

			if (!empty($params['file']) && $params['has_sample']) {
				$allowed_ext = array('jpg', 'jpeg', 'png', 'doc', 'docx', 'pdf', 'xls', 'xlsx');
				$ext         = strtolower(pathinfo($params['file']['name'], PATHINFO_EXTENSION));
				if (in_array($ext, $allowed_ext)) {
					$filename  = $params['file']['name'];
					$directory = "/images/custom/attachments/$did/$pid/";

					if (!file_exists(JPATH_ROOT . '/images/custom/attachments')) {
						$created = mkdir(JPATH_ROOT . '/images/custom/attachments', 0775);
					}
					if (!file_exists(JPATH_ROOT . '/images/custom/attachments/' . $did)) {
						$created = mkdir(JPATH_ROOT . '/images/custom/attachments/' . $did, 0775);
					}
					if (!file_exists(JPATH_ROOT . '/images/custom/attachments/' . $did . '/' . $pid)) {
						$created = mkdir(JPATH_ROOT . '/images/custom/attachments/' . $did . '/' . $pid, 0775);
					}

					$filepath    = $directory . "$filename";
					$destination = JPATH_ROOT . $filepath;
					if (move_uploaded_file($params['file']['tmp_name'], $destination)) {
						$query->clear()
							->update($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
							->set($this->_db->quoteName('sample_filepath') . ' = ' . $this->_db->quote($filepath))
							->set('has_sample = 1')
							->where($this->_db->quoteName('profile_id') . ' = ' . $this->_db->quote($pid))
							->andWhere($this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($did));

						$this->_db->setQuery($query);
						$this->_db->execute();
					}
					else {
						JLog::add('component/com_emundus/models/campaign | Cannot upload a document model for ' . $did . ' and profile ' . $pid, JLog::ERROR, 'com_emundus.error');

					}
				}
				else {
					JLog::add(JFactory::getUser()->id . ' Cannot upload a document model for ' . $did . ' and profile ' . $pid, JLog::INFO, 'com_emundus');
				}
			}

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Cannot update a document ' . $did . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

			return $e->getMessage();
		}
	}

	public function updatedDocumentMandatory($did, $pid, $mandatory = 1)
	{
		$query = $this->_db->getQuery(true);

		try {
			$query->update('#__emundus_setup_attachment_profiles')
				->set('mandatory = ' . $mandatory)
				->where('profile_id = ' . $pid)
				->andWhere('attachment_id = ' . $did);

			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @param $cid
	 *
	 * @return false
	 *
	 * @since version 1.0
	 */
	function getCampaignCategory($cid)
	{
		$campaign_dropfile_cat = false;

		if (!empty($cid)) {
			$query = $this->_db->getQuery(true);

			try {
				$query->select('id')
					->from($this->_db->quoteName('#__categories'))
					->where('json_extract(`params`, "$.idCampaign") LIKE ' . $this->_db->quote('"' . $cid . '"'))
					->andWhere($this->_db->quoteName('extension') . ' = ' . $this->_db->quote('com_dropfiles'));
				$this->_db->setQuery($query);
				$campaign_dropfile_cat = $this->_db->loadResult();

				if (!$campaign_dropfile_cat) {
					JPluginHelper::importPlugin('emundus', 'setup_category');
					$result = $this->app->triggerEvent('onAfterCampaignCreate', [$cid]);
					if ($result) {
						$campaign_dropfile_cat = $this->getCampaignCategory($cid);
					}
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Cannot get dropfiles category of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $campaign_dropfile_cat;
	}

	/**
	 * @param $campaign_cat
	 *
	 * @return false
	 *
	 * @since version 1.0
	 */
	function getCampaignDropfilesDocuments($campaign_cat)
	{
		$query = $this->_db->getQuery(true);

		try {
			$query->select('*')
				->from($this->_db->quoteName('#__dropfiles_files'))
				->where($this->_db->quoteName('catid') . ' = ' . $this->_db->quote($campaign_cat))
				->group($this->_db->quoteName('ordering'));
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Cannot get dropfiles documents of the category ' . $campaign_cat . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

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
	function getDropfileDocument($did)
	{
		$query = $this->_db->getQuery(true);

		try {
			$query->select('*')
				->from($this->_db->quoteName('#__dropfiles_files'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($did));
			$this->_db->setQuery($query);

			return $this->_db->loadObject();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Cannot get the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

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
	public function deleteDocumentDropfile($did)
	{
		$query = $this->_db->getQuery(true);

		try {
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
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Cannot delete the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

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
	public function editDocumentDropfile($did, $name)
	{
		$updated = false;

		if (!empty($did) && !empty($name)) {
			if (strlen($name) > 200) {
				$name = substr($name, 0, 200);
			}

			$query = $this->_db->getQuery(true);

			try {
				$query->update($this->_db->quoteName('#__dropfiles_files'))
					->set($this->_db->quoteName('title') . ' = ' . $this->_db->quote($name))
					->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote(($did)));
				$this->_db->setQuery($query);
				$updated = $this->_db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/campaign | Cannot update the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $updated;
	}

	/**
	 * @param $documents
	 *
	 * @return bool
	 *
	 * @since version 1.0
	 */
	public function updateOrderDropfileDocuments($documents)
	{
		$query = $this->_db->getQuery(true);

		try {
			foreach ($documents as $document) {
				$query->clear()
					->update($this->_db->quoteName('#__dropfiles_files'))
					->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quote($document['ordering']))
					->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote(($document['id'])));
				$this->_db->setQuery($query);
				$this->_db->execute();
			}

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Cannot reorder the dropfile documents : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');

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
	public function getFormDocuments($pid)
	{
		$query = $this->_db->getQuery(true);

		try {
			$query->select('*')
				->from($this->_db->quoteName('#__modules'))
				->where('json_extract(`note`, "$.pid") LIKE ' . $this->_db->quote('"' . $pid . '"'));
			$this->_db->setQuery($query);
			$form_module = $this->_db->loadObject();

			$files = array();

			if ($form_module != null) {
				// create the DOMDocument object, and load HTML from string
				$dochtml = new DOMDocument();
				$dochtml->loadHTML($form_module->content);

				// gets all DIVs
				$links = $dochtml->getElementsByTagName('a');
				foreach ($links as $link) {
					$file = new stdClass;
					if ($link->hasAttribute('href')) {
						$file->link = $link->getAttribute('href');
						$file->name = $link->textContent;
					}
					if ($link->parentNode->hasAttribute('id')) {
						$file->id = $link->parentNode->getAttribute('id');
					}
					$files[] = $file;
				}
			}

			return $files;
		}
		catch (Exception $e) {
			JLog::add('Error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');

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
	public function editDocumentForm($did, $name, $pid)
	{
		$query = $this->_db->getQuery(true);

		try {
			$query->select('*')
				->from($this->_db->quoteName('#__modules'))
				->where('json_extract(`note`, "$.pid") LIKE ' . $this->_db->quote('"' . $pid . '"'));
			$this->_db->setQuery($query);
			$form_module = $this->_db->loadObject();

			if ($form_module != null) {
				// create the DOMDocument object, and load HTML from string
				$dochtml = new DOMDocument();
				$dochtml->loadHTML($form_module->content);

				// gets all DIVs
				$link_li           = $dochtml->getElementById($did);
				$link              = $link_li->firstChild;
				$link->textContent = $name;
				$link->parentNode->replaceChild($link, $link_li->firstChild);

				$newcontent = explode('</body>', explode('<body>', $dochtml->saveHTML())[1])[0];

				$query->clear()
					->update('#__modules')
					->set($this->_db->quoteName('content') . ' = ' . $this->_db->quote($newcontent))
					->where($this->_db->quoteName('id') . '=' . $this->_db->quote($form_module->id));
				$this->_db->setQuery($query);

				return $this->_db->execute();
			}
			else {
				return true;
			}
		}
		catch (Exception $e) {
			JLog::add('Error updating form document in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');

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
	public function deleteDocumentForm($did, $pid)
	{
		$query = $this->_db->getQuery(true);

		try {
			$query->select('*')
				->from($this->_db->quoteName('#__modules'))
				->where('json_extract(`note`, "$.pid") LIKE ' . $this->_db->quote('"' . $pid . '"'));
			$this->_db->setQuery($query);
			$form_module = $this->_db->loadObject();

			// create the DOMDocument object, and load HTML from string
			$dochtml = new DOMDocument();
			$dochtml->loadHTML($form_module->content);

			// gets all DIVs
			$link = $dochtml->getElementById($did);
			unlink($link->firstChild->getAttribute('href'));
			$link->parentNode->removeChild($link);

			$newcontent = explode('</body>', explode('<body>', $dochtml->saveHTML())[1])[0];

			if (strpos($newcontent, '<li') === false) {
				$query->clear()
					->select('m.id')
					->from($this->_db->quoteName('#__menu', 'm'))
					->leftJoin($this->_db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $this->_db->quoteName('sp.menutype') . ' = ' . $this->_db->quoteName('m.menutype'))
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
					->where($this->_db->quoteName('id') . '=' . $this->_db->quote($form_module->id));
				$this->_db->setQuery($query);

				return $this->_db->execute();
			}
			else {
				$query->clear()
					->update('#__modules')
					->set($this->_db->quoteName('content') . ' = ' . $this->_db->quote($newcontent))
					->where($this->_db->quoteName('id') . '=' . $this->_db->quote($form_module->id));
				$this->_db->setQuery($query);

				return $this->_db->execute();
			}
		}
		catch (Exception $e) {
			JLog::add('Error updating form document in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');

			return false;
		}
	}

	/**
	 * @param $emundusUser
	 *
	 * @return false|object False if error, object containing emundus_campaign_workflow id, start date and end_date if success
	 *
	 * @since version 1.30.0
	 */
	public function getCurrentCampaignWorkflow($fnum)
	{
		$current_phase = null;

		if (!empty($fnum)) {
			require_once(JPATH_SITE . '/components/com_emundus/models/files.php');
			$m_files   = new EmundusModelFiles();
			$fnumInfos = $m_files->getFnumInfos($fnum);

			$fields = array($this->_db->quoteName('ecw.id'), $this->_db->quoteName('ecw.start_date'), $this->_db->quoteName('ecw.end_date'), $this->_db->quoteName('ecw.profile'), $this->_db->quoteName('ecw.output_status'), $this->_db->quoteName('ecw.display_preliminary_documents'), $this->_db->quoteName('ecw.specific_documents'), 'GROUP_CONCAT(ecw_status.entry_status separator ",") as entry_status');
			$query  = $this->_db->getQuery(true);
			$query->select('DISTINCT ' . implode(',', $fields))
				->from('#__emundus_campaign_workflow as ecw')
				->leftJoin('#__emundus_campaign_workflow_repeat_campaign AS ecw_camp ON ecw_camp.parent_id = ecw.id')
				->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
				->where('ecw_camp.campaign = ' . $this->_db->quote($fnumInfos['campaign_id']))
				->andWhere('ecw_status.entry_status = ' . $this->_db->quote($fnumInfos['status']))
				->group($this->_db->quoteName('ecw.id'));

			$this->_db->setQuery($query);

			try {
				$current_phase = $this->_db->loadObject();
			}
			catch (Exception $e) {
				JLog::add('[getCurrentCampaignWorkflow] Error getting current campaign workflow in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}

			if (empty($current_phase->id)) {
				// if not found from campaigns, check programs

				$query->clear()
					->select('DISTINCT ' . implode(',', $fields))
					->from('#__emundus_campaign_workflow as ecw')
					->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
					->leftJoin('#__emundus_setup_campaigns AS esc ON esc.id = ' . $this->_db->quote($fnumInfos['campaign_id']))
					->leftJoin('#__emundus_campaign_workflow_repeat_programs AS ecwrp ON ecwrp.parent_id = ecw.id')
					->where('ecw_status.entry_status = ' . $this->_db->quote($fnumInfos['status']))
					->andWhere('ecwrp.programs = esc.training')
					->group($this->_db->quoteName('ecw.id'));

				try {
					$current_phase = $this->_db->loadObject();
				}
				catch (Exception $e) {
					JLog::add('[getCurrentCampaignWorkflow] Error getting current campaign workflow from program: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}

				if (empty($current_phase->id)) {
					// If not found from programs nor campaigns, check workflow that are applied only from entry status (0 campaign, 0 program)

					$query->clear()
						->select('DISTINCT ' . implode(',', $fields))
						->from('#__emundus_campaign_workflow as ecw')
						->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
						->where('ecw_status.entry_status = ' . $this->_db->quote($fnumInfos['status']))
						->andWhere('ecw.id NOT IN (SELECT parent_id
                            FROM jos_emundus_campaign_workflow_repeat_programs
                            UNION
                            SELECT parent_id
                            FROM jos_emundus_campaign_workflow_repeat_campaign)')
						->group($this->_db->quoteName('ecw.id'));
					$this->_db->setQuery($query);

					try {
						$current_phase = $this->_db->loadObject();
					}
					catch (Exception $e) {
						JLog::add('[getCurrentCampaignWorkflow] Error getting current campaign workflow from program: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
					}
				}
			}


			if (!empty($current_phase->id)) {
				$current_phase->entry_status = !empty($current_phase->entry_status) ? explode(',', $current_phase->entry_status) : [];

				$query->clear()
					->select($this->_db->quoteName('ecw_documents.href') . ', ' . $this->_db->quoteName('ecw_documents.title'))
					->from($this->_db->quoteName('#__emundus_campaign_workflow_repeat_documents', 'ecw_documents'))
					->where($this->_db->quoteName('ecw_documents.parent_id') . ' = ' . $this->_db->quote($current_phase->id));

				$this->_db->setQuery($query);
				$current_phase->documents = $this->_db->loadObjectList();

				if (empty($current_phase->start_date) || $current_phase->start_date === '0000-00-00 00:00:00') {
					$campaign = $this->getCampaignByID($fnumInfos['campaign_id']);

					if (!empty($campaign)) {
						$current_phase->start_date = $campaign['start_date'];
					}
				}
				if (empty($current_phase->end_date) || $current_phase->end_date === '0000-00-00 00:00:00') {
					$campaign = $this->getCampaignByID($fnumInfos['campaign_id']);

					if (!empty($campaign)) {
						$current_phase->end_date = $campaign['end_date'];
					}
				}
			}
			else {
				$current_phase = null;
			}
		}

		return $current_phase;
	}

	/**
	 * @param $campaign_id int
	 *
	 * @return array
	 */
	public function getAllCampaignWorkflows($campaign_id)
	{
		$workflows = [];

		if (!empty($campaign_id)) {
			$excluded_entry_statuses        = [];
			$campaign_workflows_by_campaign = [];
			$query                          = $this->_db->getQuery(true);
			$query->select('DISTINCT ecw.*, GROUP_CONCAT(ecw_status.entry_status separator ",") as entry_status')
				->from('#__emundus_campaign_workflow as ecw')
				->leftJoin('#__emundus_campaign_workflow_repeat_campaign AS ecw_camp ON ecw_camp.parent_id = ecw.id')
				->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
				->where('ecw_camp.campaign = ' . $this->_db->quote($campaign_id))
				->group('ecw.profile');
			$this->_db->setQuery($query);

			try {
				$campaign_workflows_by_campaign = $this->_db->loadObjectList();
				foreach ($campaign_workflows_by_campaign as $key => $wf) {
					if (empty($wf->id)) {
						unset($campaign_workflows_by_campaign[$key]);
					}
					else {
						$excluded_entry_statuses = array_merge(explode(',', $wf->entry_status), $excluded_entry_statuses);
					}
				}
			}
			catch (Exception $e) {
				JLog::add('[getCurrentCampaignWorkflow] Error getting current campaign workflow in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}

			$campaign_workflows_by_campaign_program = [];
			$query->clear()
				->select('DISTINCT ecw.*, GROUP_CONCAT(ecw_status.entry_status separator ",") as entry_status')
				->from($this->_db->quoteName('#__emundus_campaign_workflow', 'ecw'))
				->leftJoin($this->_db->quoteName('#__emundus_campaign_workflow_repeat_entry_status', 'ecw_status') . ' ON ' . $this->_db->quoteName('ecw_status.parent_id') . ' = ' . $this->_db->quoteName('ecw.id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->_db->quoteName('esc.id') . ' = ' . $this->_db->quote($campaign_id))
				->leftJoin($this->_db->quoteName('#__emundus_campaign_workflow_repeat_programs', 'ecwrp') . ' ON ' . $this->_db->quoteName('ecwrp.parent_id') . ' = ' . $this->_db->quoteName('ecw.id'))
				->where($this->_db->quoteName('ecwrp.programs') . ' = ' . $this->_db->quoteName('esc.training'));

			if (!empty($excluded_entry_statuses)) {
				$query->andWhere('ecw_status.entry_status NOT IN (' . implode(',', $excluded_entry_statuses) . ')');
			}

			$query->group(['ecwrp.programs', 'ecw.profile']);
			$this->_db->setQuery($query);

			try {
				$campaign_workflows_by_campaign_program = $this->_db->loadObjectList();
				foreach ($campaign_workflows_by_campaign_program as $key => $wf) {
					if (empty($wf->id)) {
						unset($campaign_workflows_by_campaign_program[$key]);
					}
					else {
						$excluded_entry_statuses = array_merge(explode(',', $wf->entry_status), $excluded_entry_statuses);
					}
				}
			}
			catch (Exception $e) {
				JLog::add('[getCurrentCampaignWorkflow] Error getting current campaign workflow in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}

			$default_campaign_workflows = [];
			$query->clear()
				->select('DISTINCT ecw.*, GROUP_CONCAT(ecw_status.entry_status separator ",") as entry_status')
				->from('#__emundus_campaign_workflow as ecw')
				->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
				->where('ecw.id NOT IN (SELECT parent_id
                            FROM jos_emundus_campaign_workflow_repeat_programs
                            UNION
                            SELECT parent_id
                            FROM jos_emundus_campaign_workflow_repeat_campaign)');

			if (!empty($excluded_entry_statuses)) {
				$query->andWhere('ecw_status.entry_status NOT IN (' . implode(',', $excluded_entry_statuses) . ')');
			}

			$query->group('ecw.profile');
			$this->_db->setQuery($query);

			try {
				$default_campaign_workflows = $this->_db->loadObjectList();
				foreach ($default_campaign_workflows as $key => $wf) {
					if (empty($wf->id)) {
						unset($default_campaign_workflows[$key]);
					}
				}
			}
			catch (Exception $e) {
				JLog::add('[getCurrentCampaignWorkflow] Error getting current campaign workflow in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}

			$workflows = array_merge($campaign_workflows_by_campaign, $campaign_workflows_by_campaign_program, $default_campaign_workflows);
		}

		return $workflows;
	}

	public function pinCampaign($cid): bool
	{
		$pinned = false;

		if (!empty($cid)) {
			// check if campaign exists
			$campaign = $this->getCampaignByID($cid);

			if (!empty($campaign)) {
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				try {
					$query->clear()
						->select('id')
						->from($db->quoteName('#__emundus_setup_campaigns'))
						->where($db->quoteName('pinned') . ' = 1');
					$db->setQuery($query);
					$campaigns_already_pinned = $db->loadColumn();

					if (!empty($campaigns_already_pinned)) {
						$this->unpinCampaign($campaigns_already_pinned);
					}

					$query->clear()
						->update($db->quoteName('#__emundus_setup_campaigns'))
						->set($db->quoteName('pinned') . ' = 1')
						->where($db->quoteName('id') . ' = ' . $db->quote($cid));
					$db->setQuery($query);

					$pinned = $db->execute();
				}
				catch (Exception $e) {
					JLog::add('Error updating form document in component/com_emundus/models/campaign: ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			}
		}

		return $pinned;
	}

	/**
	 * @param $campaign_id
	 *
	 * @return bool
	 */
	public function unpinCampaign($campaign_id): bool
	{
		$unpinned = false;

		$campaign_id = is_array($campaign_id) ? $campaign_id : array($campaign_id);
		$campaign_id = array_filter($campaign_id, 'is_numeric');
		$campaign_id = array_filter($campaign_id);

		if (!empty($campaign_id)) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__emundus_setup_campaigns'))
				->set($db->quoteName('pinned') . ' = 0')
				->where($db->quoteName('id') . ' IN (' . implode(',', $campaign_id) . ')');

			try {
				$db->setQuery($query);
				$unpinned = $db->execute();
			}
			catch (Exception $e) {
				JLog::add('Error setting pinned = 0 for $cid ' . $campaign_id . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $unpinned;
	}

	/**
	 * Create a workflow
	 *
	 * @param $profile       int
	 * @param $entry_status  array
	 * @param $output_status int
	 * @param $start_date    date
	 * @param $params        array of optional parameters (campaigns, programs, end_date)
	 *
	 * @return $new_workflow_id int
	 */
	public function createWorkflow($profile, $entry_status, $output_status, $start_date = null, $params = [])
	{
		$new_workflow_id = 0;

		if (!empty($profile) && !empty($entry_status)) {
			$canCreate = $this->canCreateWorkflow($profile, $entry_status, $params);

			if ($canCreate) {
				$start_date = empty($start_date) ? date('Y-m-d H:i:s') : $start_date;

				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$columns = ['profile', 'output_status', 'start_date'];
				$values  = $profile . ',' . $output_status . ', ' . $db->quote($start_date);

				if (isset($params['end_date'])) {
					$columns[] = 'end_date';
					$values    .= ', ' . $db->quote($params['end_date']);
				}

				$query->insert('#__emundus_campaign_workflow')
					->columns($columns)
					->values($values);

				$created = false;
				try {
					$db->setQuery($query);
					$created         = $db->execute();
					$new_workflow_id = $db->insertid();
				}
				catch (Exception $e) {
					JLog::add('Failed to create campaign workflow', JLog::ERROR, 'com_emundus.error');
				}

				if ($created) {
					foreach ($entry_status as $status) {
						$query->clear()
							->insert('#__emundus_campaign_workflow_repeat_entry_status')
							->columns(['parent_id', 'entry_status'])
							->values($new_workflow_id . ',' . $db->quote($status));

						$db->setQuery($query);
						$db->execute();
					}

					if (!empty($params['campaigns'])) {
						foreach ($params['campaigns'] as $cid) {
							$query->clear()
								->insert('#__emundus_campaign_workflow_repeat_campaign')
								->columns(['parent_id', 'campaign'])
								->values($new_workflow_id . ',' . $db->quote($cid));

							$db->setQuery($query);
							$db->execute();
						}
					}

					if (!empty($params['programs'])) {
						foreach ($params['programs'] as $code) {
							$query->clear()
								->insert('#__emundus_campaign_workflow_repeat_programs')
								->columns(['parent_id', 'programs'])
								->values($new_workflow_id . ',' . $db->quote($code));

							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}

		return $new_workflow_id;
	}

	/**
	 *
	 * @param $profile
	 * @param $entry_status
	 * @param $params
	 *
	 * @return bool
	 */
	public function canCreateWorkflow($profile, $entry_status, $params): bool
	{
		$canCreate = true;

		if (!empty($profile) && !empty($entry_status)) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			if (!empty($params)) {
				if (!empty($params['programs'])) {
					$query->clear()
						->select('COUNT(DISTINCT ecw.id)')
						->from('#__emundus_campaign_workflow as ecw')
						->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
						->leftJoin('#__emundus_campaign_workflow_repeat_programs AS ecw_programs ON ecw_programs.parent_id = ecw.id AND ecw_programs.parent_id = ecw_status.parent_id')
						->where('ecw_status.entry_status IN (' . implode(',', $entry_status) . ')')
						->andWhere('ecw_programs.programs IN (' . implode(',', $db->quote($params['programs'])) . ')');
					$db->setQuery($query);

					try {
						$nbWorkflows = $db->loadResult();

						if ($nbWorkflows > 0) {
							$canCreate = false;
						}
					}
					catch (Exception $e) {
						JLog::add('Failed to check if can create workflow ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
					}
				}

				if ($canCreate && !empty($params['campaigns'])) {
					$query->clear()
						->select('COUNT(DISTINCT ecw.id)')
						->from('#__emundus_campaign_workflow as ecw')
						->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
						->leftJoin('#__emundus_campaign_workflow_repeat_campaign AS ecw_campaign ON ecw_campaign.parent_id = ecw.id AND ecw_campaign.parent_id = ecw_status.parent_id')
						->where('ecw_status.entry_status IN (' . implode(',', $entry_status) . ')')
						->andWhere('ecw_campaign.campaign IN (' . implode(',', $params['campaigns']) . ')');
					$db->setQuery($query);
					try {
						$nbWorkflows = $db->loadResult();

						if ($nbWorkflows > 0) {
							$canCreate = false;
						}
					}
					catch (Exception $e) {
						JLog::add('Failed to check if can create workflow ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
					}
				}

			}
			else {
				// check there is no workflow with same entry status that has no program nor campaign
				$query->clear()
					->select('COUNT(DISTINCT ecw.id)')
					->from('#__emundus_campaign_workflow as ecw')
					->leftJoin('#__emundus_campaign_workflow_repeat_entry_status AS ecw_status ON ecw_status.parent_id = ecw.id')
					->where('ecw_status.entry_status IN (' . implode(',', $entry_status) . ')')
					->andWhere('ecw.id NOT IN (SELECT parent_id
                            FROM jos_emundus_campaign_workflow_repeat_programs
                            UNION
                            SELECT parent_id
                            FROM jos_emundus_campaign_workflow_repeat_campaign)');
				$db->setQuery($query);

				try {
					$nbWorkflows = $db->loadResult();

					if ($nbWorkflows > 0) {
						$canCreate = false;
					}
				}
				catch (Exception $e) {
					JLog::add('Failed to check if can create workflow ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			}
		}
		else {
			$canCreate = false;
		}

		return $canCreate;
	}

	public function deleteWorkflows($ids = null)
	{
		$deleted = false;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete('#__emundus_campaign_workflow');

		if (!empty($ids)) {
			$query->where('id IN (' . implode(', ' . $ids) . ')');
		}

		try {
			$db->setQuery($query);
			$deleted = $db->execute();

			if ($deleted) {
				$repeat_tables = [
					'#__emundus_campaign_workflow_repeat_campaign',
					'#__emundus_campaign_workflow_repeat_entry_status',
					'#__emundus_campaign_workflow_repeat_programs'
				];

				foreach ($repeat_tables as $table) {
					$query->clear()
						->delete($db->quoteName($table));

					if (!empty($ids)) {
						$query->where('parent_id IN (' . implode(', ' . $ids) . ')');
					}

					$db->setQuery($query);
					$deleted = $db->execute();
				}
			}
		}
		catch (Exception $e) {
			JLog::add('Failed to delete workflow(s) ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $deleted;
	}

	public function getWorkflows($ids = null)
	{
		$workflows = [];

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__emundus_campaign_workflow'));

		if (!empty($ids)) {
			$query->where('id IN (' . implode(',', $ids) . ')');
		}

		try {
			$db->setQuery($query);
			$workflows = $db->loadObjectList();

			if (!empty($workflows)) {
				foreach ($workflows as $key => $workflow) {
					$query->clear()
						->select('entry_status')
						->from('#__emundus_campaign_workflow_repeat_entry_status')
						->where('parent_id = ' . $workflow->id);

					$db->setQuery($query);
					$workflows[$key]->entry_status = $db->loadColumn();

					$query->clear()
						->select('campaign')
						->from('#__emundus_campaign_workflow_repeat_campaign')
						->where('parent_id = ' . $workflow->id);

					$db->setQuery($query);
					$workflows[$key]->campaigns = $db->loadColumn();

					$query->clear()
						->select('programs')
						->from('#__emundus_campaign_workflow_repeat_programs')
						->where('parent_id = ' . $workflow->id);

					$db->setQuery($query);
					$workflows[$key]->programs = $db->loadColumn();
				}
			}
		}
		catch (Exception $e) {
			$workflows = [];
			JLog::add('Failed to getAllWorkflows ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $workflows;
	}

	/**
	 * @return array
	 */
	public function findWorkflowIncoherences()
	{
		$incoherences = [];
		$workflows    = $this->getWorkflows();

		if (!empty($workflows)) {
			$similar_pairs = [];

			foreach ($workflows as $key => $workflow) {
				$other_workflows = $workflows;
				unset($other_workflows[$key]);

				foreach ($other_workflows as $other_workflow) {
					if (!in_array($other_workflow->id . '-' . $workflow->id, $similar_pairs)) {
						// must contain same entry status to produce incoherence
						$similar_statuses = array_intersect($other_workflow->entry_status, $workflow->entry_status);
						if (!empty($similar_statuses)) {
							// are they on every program and every campaign ?
							if (empty($workflow->campaigns) && empty($workflow->programs) && empty($other_workflow->campaigns) && empty($other_workflow->programs)) {
								$incoherences[] = [
									'workflow'         => $workflow->id,
									'similar_workflow' => $other_workflow->id,
									'similarities'     => [
										'status' => $similar_statuses
									]
								];

								$similar_pairs[] = $workflow->id . '-' . $other_workflow->id;
							}
							else {
								// is it on similar campaign
								$similar_campaigns = array_intersect($other_workflow->campaigns, $workflow->campaigns);

								if (!empty($similar_campaigns)) {
									$incoherences[]  = [
										'workflow'         => $workflow->id,
										'similar_workflow' => $other_workflow->id,
										'similarities'     => [
											'status'    => $similar_statuses,
											'campaigns' => $similar_campaigns
										]
									];
									$similar_pairs[] = $workflow->id . '-' . $other_workflow->id;
								}
								else {
									// is it on similar program
									$similar_programs = array_intersect($other_workflow->programs, $workflow->programs);

									if (!empty($similar_programs)) {
										$incoherences[]  = [
											'workflow'         => $workflow->id,
											'similar_workflow' => $other_workflow->id,
											'similarities'     => [
												'status'   => $similar_statuses,
												'programs' => $similar_programs
											]
										];
										$similar_pairs[] = $workflow->id . '-' . $other_workflow->id;
									}
								}
							}
						}
					}
				}
			}
		}

		return $incoherences;
	}
}
