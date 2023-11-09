<?php
/**
 * Users Model for eMundus Component
 *
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

class EmundusModelProgramme extends JModelList
{
	private $app;
	private $_em_user;
	private $_user;
	protected $_db;
	private $config;

	function __construct()
	{
		parent::__construct();

		$this->app = Factory::getApplication();

		if (version_compare(JVERSION, '4.0', '>')) {
			$this->_db      = Factory::getContainer()->get('DatabaseDriver');
			$this->_em_user = $this->app->getSession()->get('emundusUser');
			$this->_user    = $this->app->getIdentity();
			$this->config   = $this->app->getConfig();
		}
		else {
			$this->_db      = Factory::getDBO();
			$this->_em_user = Factory::getSession()->get('emundusUser');
			$this->_user    = Factory::getUser();
			$this->config   = Factory::getConfig();
		}
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 * @since version v6
	 */
	public function getCampaign($id = 0)
	{
		
		$query = $this->_db->getQuery(true);
		$query->select('pr.*,ca.*');
		$query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
		$query->where('ca.training = pr.code AND ca.published=1 AND ca.id=' . $id);
		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	public function getParams($id = 0)
	{
		
		$query = $this->_db->getQuery(true);
		$query->select('params');
		$query->from('#__menu');
		$query->where('id=' . $id);
		$this->_db->setQuery($query);

		return json_decode($this->_db->loadResult(), true);
	}

	/**
	 * @param $user
	 *
	 * @return array
	 * get list of programmes for associated files
	 * @since version v6
	 */
	public function getAssociatedProgrammes($user)
	{
		$associated_programs = [];

		if (!empty($user)) {
			
			$query = $this->_db->getQuery(true);
			$query->select('DISTINCT sc.training')
				->from($this->_db->quoteName('#__emundus_users_assoc', 'ua'))
				->leftJoin($this->_db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $this->_db->quoteName('cc.fnum') . '=' . $this->_db->quoteName('ua.fnum'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.id') . '=' . $this->_db->quoteName('cc.campaign_id'))
				->where($this->_db->quoteName('ua.user_id') . '=' . $user);

			try {
				$this->_db->setQuery($query);
				$associated_programs = $this->_db->loadColumn();
			}
			catch (Exception $e) {
				JLog::add('Error getting associated programmes in model/programme at query : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $associated_programs;
	}

	/**
	 * @param $published  int     get published or unpublished programme
	 * @param $codeList   array   array of IN and NOT IN programme code to get
	 *
	 * @return array
	 * @since version v6
	 * get list of declared programmes
	 */
	public function getProgrammes($published = null, $codeList = array())
	{
		$programmes = [];

		
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->from($this->_db->quoteName('#__emundus_setup_programmes'))
			->where('1 = 1');

		if (isset($published)) {
			$query->andWhere('published = ' . $published);
		}

		if (!empty($codeList)) {
			if (!empty($codeList['IN'])) {
				$query->andWhere('code IN (' . implode(',', $this->_db->quote($codeList['IN'])) . ')');
			}
			if (!empty($codeList['NOT_IN'])) {
				$query->andWhere('code NOT IN (' . implode(',', $this->_db->quote($codeList['NOT_IN'])) . ')');
			}
		}

		try {
			$this->_db->setQuery($query);
			$programmes = $this->_db->loadAssocList('code');
		}
		catch (Exception $e) {
			error_log($e->getMessage(), 0);
		}

		return $programmes;
	}

	/**
	 * @param $published  int     get published or unpublished programme
	 * @param $codeList   array   array of IN and NOT IN programme code to get
	 *
	 * @return mixed
	 * get list of declared programmes
	 * @since version v6
	 */
	public function getProgramme($code)
	{

		if (empty($code)) {
			return false;
		}

		

		$query = $this->_db->getQuery(true);

		$query
			->select('*')
			->from($this->_db->quoteName('#__emundus_setup_programmes'))
			->where($this->_db->quoteName('code') . ' LIKE ' . $this->_db->quote($code));

		$this->_db->setQuery($query);

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObject();
		}
		catch (Exception $e) {
			error_log($e->getMessage(), 0);

			return false;
		}
	}

	/**
	 * @param   array  $data  the row to add in table.
	 *
	 * @return boolean
	 * Add new programme in DB
	 * @since version v6
	 */
	public function addProgrammes($data)
	{
		

		if (!empty($data)) {
			unset($data[0]['organisation']);
			unset($data[0]['organisation_code']);
			$column = array_keys($data[0]);

			$values = array();
			foreach ($data as $key => $v) {
				unset($v['organisation']);
				unset($v['organisation_code']);
				$values[] = '(' . implode(',', $this->_db->Quote($v)) . ')';
			}

			$query = 'INSERT INTO `#__emundus_setup_programmes` (`' . implode('`, `', $column) . '`) VALUES ' . implode(',', $values);

			try {
				$this->_db->setQuery($query);

				return $this->_db->execute();
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');

				return $e->getMessage();
			}
		}
		else {
			return false;
		}
	}

	/**
	 * @param   array  $data  the row to add in table.
	 *
	 * @return boolean
	 * Edit programme in DB
	 * @since version v6
	 */
	public function editProgrammes($data)
	{
		

		if (count($data) > 0) {
			try {
				foreach ($data as $key => $v) {
					$query = 'UPDATE `#__emundus_setup_programmes` SET label=' . $this->_db->Quote($v['label']) . ' WHERE code like ' . $this->_db->Quote($v['code']);
					$this->_db->setQuery($query);
					$this->_db->execute();

					$query = 'UPDATE `#__emundus_setup_teaching_unity` SET label=' . $this->_db->Quote($v['label']) . ' WHERE code like ' . $this->_db->Quote($v['code']);
					$this->_db->setQuery($query);
					$this->_db->execute();

					$query = 'UPDATE `#__emundus_setup_campaigns` SET label=' . $this->_db->Quote($v['label']) . ' WHERE training like ' . $this->_db->Quote($v['code']);
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');

				return $e->getMessage();
			}
		}
		else {
			return false;
		}

		return true;
	}


	/**
	 * Gets the most recent programme code.
	 * @return string The most recently added programme in the DB.
	 * @since version v6
	 */
	function getLatestProgramme()
	{


		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName('code'))
			->from($this->_db->quoteName('#__emundus_setup_programmes'))
			->order('id DESC')
			->setLimit('1');

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error getting latest programme at model/programme at query :' . $query, JLog::ERROR, 'com_emundus');

			return '';
		}
	}


	/**
	 * Checks if the user has this programme in his favorites.
	 *
	 * @param         $programme_id Int The ID of the programme to be favorited.
	 * @param   null  $user_id      Int The user ID, if null: the current user ID.
	 *
	 * @return bool True if favorited.
	 * @since version v6
	 */
	function isFavorite($programme_id, $user_id = null)
	{

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		if (empty($user_id) || empty($programme_id)) {
			return false;
		}


		$query = $this->_db->getQuery(true);
		$query->select('1')
			->from($this->_db->quoteName('#__emundus_favorite_programmes'))
			->where($this->_db->quoteName('user_id') . ' = ' . $user_id . ' AND ' . $this->_db->quoteName('programme_id') . ' = ' . $programme_id);
		$this->_db->setQuery($query);

		try {
			return $this->_db->loadResult() == 1;
		}
		catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Adds a programme to the user's list of favorites.
	 *
	 * @param         $programme_id Int The ID of the programme to be favorited.
	 * @param   null  $user_id      Int The user ID, if null: the current user ID.
	 *
	 * @return bool
	 * @since version v6
	 */
	public function favorite($programme_id, $user_id = null)
	{

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		if (empty($user_id) || empty($programme_id)) {
			return false;
		}


		$query = $this->_db->getQuery(true);
		$query->insert($this->_db->quoteName('#__emundus_favorite_programmes'))
			->columns($this->_db->quoteName(['user_id', 'programme_id']))
			->values($user_id . ',' . $programme_id);
		$this->_db->setQuery($query);

		try {
			$this->_db->execute();

			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Removes a programme from the user's list of favorites.
	 *
	 * @param         $programme_id Int The ID of the programme to be unfavorited.
	 * @param   null  $user_id      Int The user ID, if null: the current user ID.
	 *
	 * @return bool
	 * @since version v6
	 */
	public function unfavorite($programme_id, $user_id = null)
	{

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		if (empty($user_id) || empty($programme_id)) {
			return false;
		}


		$query = $this->_db->getQuery(true);
		$query->delete($this->_db->quoteName('#__emundus_favorite_programmes'))
			->where($this->_db->quoteName('user_id') . ' = ' . $user_id . ' AND ' . $this->_db->quoteName('programme_id') . ' = ' . $programme_id);
		$this->_db->setQuery($query);

		try {
			$this->_db->execute();

			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Get's the upcoming sessions of the user's favorite programs.
	 *
	 * @param   null  $user_id
	 *
	 * @return mixed
	 * @since version v6
	 */
	public function getUpcomingFavorites($user_id = null)
	{

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		if (empty($user_id)) {
			return false;
		}


		$query = $this->_db->getQuery(true);
		$query->select(['t.*', $this->_db->quoteName('c.id', 'cid'), $this->_db->quoteName('p.id', 'pid'), $this->_db->quoteName('p.url')])
			->from($this->_db->quoteName('#__emundus_favorite_programmes', 'f'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'p') . ' ON ' . $this->_db->quoteName('p.id') . ' = ' . $this->_db->quoteName('f.programme_id'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_teaching_unity', 't') . ' ON ' . $this->_db->quoteName('t.code') . ' LIKE ' . $this->_db->quoteName('p.code'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'c') . ' ON ' . $this->_db->quoteName('c.session_code') . ' LIKE ' . $this->_db->quoteName('t.session_code'))
			->where($this->_db->quoteName('f.user_id') . ' = ' . $user_id . ' AND ' . $this->_db->quoteName('t.published') . '= 1 AND ' . $this->_db->quoteName('t.date_start') . ' >= NOW()')
			->order($this->_db->quoteName('t.date_start') . ' ASC');
		$this->_db->setQuery($query);

		try {
			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Get's the user's favorite programs.
	 *
	 * @param   null  $user_id
	 *
	 * @return mixed
	 * @since version v6
	 */
	public function getFavorites($user_id = null)
	{

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		if (empty($user_id)) {
			return false;
		}


		$query = $this->_db->getQuery(true);
		$query->select(['p.*', $this->_db->quoteName('t.label', 'title')])
			->from($this->_db->quoteName('#__emundus_favorite_programmes', 'f'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'p') . ' ON ' . $this->_db->quoteName('p.id') . ' = ' . $this->_db->quoteName('f.programme_id'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_thematiques', 'th') . ' ON ' . $this->_db->quoteName('th.id') . ' = ' . $this->_db->quoteName('p.programmes'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_teaching_unity', 't') . ' ON ' . $this->_db->quoteName('t.code') . ' LIKE ' . $this->_db->quoteName('p.code'))
			->where($this->_db->quoteName('f.user_id') . ' = ' . $user_id . ' AND ' . $this->_db->quoteName('p.id') . ' NOT IN (SELECT p.id FROM `jos_emundus_setup_programmes` AS `p` LEFT JOIN `jos_emundus_setup_teaching_unity` AS `t` ON `t`.`code` LIKE `p`.`code` LEFT JOIN `jos_emundus_setup_campaigns` AS `c` ON `c`.`session_code` LIKE `t`.`session_code` LEFT JOIN `jos_emundus_campaign_candidature` AS `cc` ON `cc`.`campaign_id` LIKE `t`.`id` WHERE `cc`.`user_id` = ' . $user_id . ' AND `cc`.`published`= 1) AND ' . $this->_db->quoteName('p.published') . '= 1 AND ' . $this->_db->quoteName('t.date_start') . ' > NOW() AND ' . $this->_db->quoteName('t.published') . '= 1 AND ' . $this->_db->quoteName('th.published') . '= 1')
			->group($this->_db->quoteName('p.id'));
		$this->_db->setQuery($query);

		try {
			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @param $lim
	 * @param $page
	 * @param $filter
	 * @param $sort
	 * @param $recherche
	 *
	 * @return stdClass
	 *
	 * @since version 1.0
	 */
	function getAllPrograms($lim, $page, $filter, $sort, $recherche)
	{
		$all_programs = [];

		// Get affected programs
		$user     = JFactory::getUser();
		$programs = $this->getUserPrograms($user->id);
		//

		if (!empty($programs)) {
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

			$sortDb = 'p.id ';


			$query = $this->_db->getQuery(true);

			if ($filter == 'Publish') {
				$filterDate = $this->_db->quoteName('p.published') . ' LIKE 1';
			}
			else if ($filter == 'Unpublish') {
				$filterDate = $this->_db->quoteName('p.published') . ' LIKE 0';
			}
			else {
				$filterDate = ('1');
			}

			if (empty($recherche)) {
				$fullRecherche = 1;
			}
			else {
				$rechercheLbl      = $this->_db->quoteName('p.label') . ' LIKE ' . $this->_db->quote('%' . $recherche . '%');
				$rechercheNotes    = $this->_db->quoteName('p.notes') . ' LIKE ' . $this->_db->quote('%' . $recherche . '%');
				$rechercheCategory = $this->_db->quoteName('p.programmes') . ' LIKE ' . $this->_db->quote('%' . $recherche . '%');
				$fullRecherche     = $rechercheLbl . ' OR ' . $rechercheNotes . ' OR ' . $rechercheCategory;
			}

			$query->select(['p.*', 'COUNT(sc.id) AS nb_campaigns'])
				->from($this->_db->quoteName('#__emundus_setup_programmes', 'p'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quoteName('p.code'))
				->where($filterDate)
				->where($fullRecherche)
				->andWhere($this->_db->quoteName('p.code') . ' IN (' . implode(',', $this->_db->quote($programs)) . ')')
				->group($sortDb)
				->order($sortDb . $sort);

			try {
				$this->_db->setQuery($query);
				$all_programs['count'] = count($this->_db->loadObjectList());

				if (empty($lim)) {
					$this->_db->setQuery($query, $offset);
				}
				else {
					$this->_db->setQuery($query, $offset, $limit);
				}

				$programs = $this->_db->loadObjectList();

				$all_programs['datas'] = $programs;
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/program | Error at getting list of programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $all_programs;
	}

	/**
	 * @param $filter
	 * @param $recherche
	 *
	 * @return int|mixed|null
	 *
	 * @since version 1.0
	 */
	function getProgramCount($filter, $recherche)
	{
		// Get affected programs
		$user     = JFactory::getUser();
		$programs = $this->getUserPrograms($user->id);
		//


		$query = $this->_db->getQuery(true);

		if ($filter == 'Publish') {
			$filterCount = $this->_db->quoteName('p.published') . ' LIKE 1';
		}
		else if ($filter == 'Unpublish') {
			$filterCount = $this->_db->quoteName('p.published') . ' LIKE 0';
		}
		else {
			$filterCount = ('1');
		}

		if (empty($recherche)) {
			$fullRecherche = 1;
		}
		else {
			$rechercheLbl      = $this->_db->quoteName('p.label') . ' LIKE ' . $this->_db->quote('%' . $recherche . '%');
			$rechercheNotes    = $this->_db->quoteName('p.notes') . ' LIKE ' . $this->_db->quote('%' . $recherche . '%');
			$rechercheCategory = $this->_db->quoteName('p.programmes') . ' LIKE ' . $this->_db->quote('%' . $recherche . '%');
			$fullRecherche     = $rechercheLbl . ' OR ' . $rechercheNotes . ' OR ' . $rechercheCategory;
		}

		$query->select('COUNT(p.id)')
			->from($this->_db->quoteName('#__emundus_setup_programmes', 'p'))
			->where($filterCount)
			->where($fullRecherche)
			->andWhere($this->_db->quoteName('p.code') . ' IN (' . implode(',', $this->_db->quote($programs)) . ')');
		try {
			$this->_db->setQuery($query);

			return $this->_db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting number of programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return 0;
		}
	}

	/**
	 * @param $id
	 *
	 * @return false|mixed|null
	 *
	 * @since version 1.0
	 */
	public function getProgramById($id)
	{
		if (empty($id)) {
			return false;
		}


		$query = $this->_db->getQuery(true);

		$query->clear()
			->select('*')
			->from($this->_db->quoteName('#__emundus_setup_programmes'))
			->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($id));

		$this->_db->setQuery($query);
		$programme = $this->_db->loadObject();

		$query->clear()
			->select('sg.id')
			->from($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $this->_db->quoteName('sgr.parent_id') . ' = ' . $this->_db->quoteName('sg.id'))
			->where($this->_db->quoteName('sgr.course') . ' = ' . $this->_db->quote($programme->code))
			->andWhere($this->_db->quoteName('sg.parent_id') . ' IS NULL');
		$this->_db->setQuery($query);
		$prog_group = $this->_db->loadResult();

		$programme->group           = $prog_group;
		$programme->evaluator_group = $this->getGroupByParent($programme->code, 2);
		$programme->manager_group   = $this->getGroupByParent($programme->code, 3);

		try {
			return $programme;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting program by id ' . $id . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

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
	public function addProgram($data, $user = null)
	{
		$response = false;

		if (empty($user)) {
			$user = $this->_user;
		}
		$user_id = !empty($user->id) ? $user->id : 62;

		$query = $this->_db->getQuery(true);

		if (!empty($data) && !empty($data['label'])) {
			$data['code'] = preg_replace('/[^A-Za-z0-9]/', '', $data['label']);
			$data['code'] = str_replace(' ', '_', $data['code']);
			$data['code'] = substr($data['code'], 0, 10);
			$data['code'] = strtolower($data['code']);
			$data['code'] = uniqid($data['code'] . '-');

			PluginHelper::importPlugin('emundus');
			$this->app->triggerEvent('onCallEventHandler', ['onBeforeProgramCreate', ['data' => $data]]);

			if (count($data) > 0) {
				$query->insert($this->_db->quoteName('#__emundus_setup_programmes'))
					->columns($this->_db->quoteName(array_keys($data)))
					->values(implode(',', $this->_db->Quote(array_values($data))));

				try {
					$this->_db->setQuery($query);
					$this->_db->execute();
					$prog_id = $this->_db->insertid();

					$query->clear()
						->select('*')
						->from($this->_db->quoteName('#__emundus_setup_programmes'))
						->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($prog_id));
					$this->_db->setQuery($query);
					$programme = $this->_db->loadObject();

					// Create user group
					$columns = array('label', 'published', 'class');
					$values  = array($this->_db->quote($programme->label), $this->_db->quote(1), $this->_db->quote('label-default'));

					$query->clear()
						->insert($this->_db->quoteName('#__emundus_setup_groups'))
						->columns($this->_db->quoteName($columns))
						->values(implode(',', $values));
					$this->_db->setQuery($query);
					$this->_db->execute();
					$group_id = $this->_db->insertid();
					//

					// Link group with programme
					$columns = array('parent_id', 'course');
					$values  = array($this->_db->quote($group_id), $this->_db->quote($programme->code));

					$query->clear()
						->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_course'))
						->columns($this->_db->quoteName($columns))
						->values(implode(',', $values));
					$this->_db->setQuery($query);
					$this->_db->execute();
					//

					// Affect coordinator to the group of the program
					$columns = array('user_id', 'group_id');
					$values  = array($this->_db->quote($user_id), $group_id);

					$query->clear()
						->insert($this->_db->quoteName('#__emundus_groups'))
						->columns($this->_db->quoteName($columns))
						->values(implode(',', $values));
					$this->_db->setQuery($query);
					$this->_db->execute();
					//

					// Link All rights group with programme
					$eMConfig            = ComponentHelper::getParams('com_emundus');
					$all_rights_group_id = $eMConfig->get('all_rights_group', 1);

					$columns = array('parent_id', 'course');
					$values  = array($this->_db->quote($all_rights_group_id), $this->_db->quote($programme->code));

					$query->clear()
						->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_course'))
						->columns($this->_db->quoteName($columns))
						->values(implode(',', $values));
					$this->_db->setQuery($query);
					$this->_db->execute();
					//

					// Create evaluator and manager group
					$this->addGroupToProgram($programme->label, $programme->code, 2);
					$this->addGroupToProgram($programme->label, $programme->code, 3);
					//

					// Call plugin triggers
					$this->app->triggerEvent('onCallEventHandler', ['onAfterProgramCreate', ['programme' => $programme]]);

					$response = array(
						'programme_id'   => $prog_id,
						'programme_code' => $programme->code
					);
				}
				catch (Exception $e) {
					JLog::add('component/com_emundus/models/program | Error when creating a program : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
					$response = $e->getMessage();
				}

			}
		}

		return $response;
	}

	/**
	 * @param   int    $id    the program to update
	 * @param   array  $data  the row to add in table.
	 *
	 * @return boolean
	 * Update program in DB
	 * @since version 1.0
	 */
	public function updateProgram($id, $data)
	{
		$updated = false;

		if (!empty($id) && !empty($data)) {


			JPluginHelper::importPlugin('emundus');

			JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeProgramUpdate', ['id' => $id, 'data' => $data]]);

			if (!empty($data)) {
				$query = 'SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ' . $this->_db->quote('jos_emundus_setup_programmes');
				$this->_db->setQuery($query);
				$table_columns = $this->_db->loadColumn();

				$fields = [];
				foreach ($data as $key => $val) {
					if (in_array($key, $table_columns) && $key != 'id' && $key != 'code') {
						$fields[] = $this->_db->quoteName($key) . ' = ' . $this->_db->quote($val);
					}
				}

				if (!empty($fields)) {
					$query = $this->_db->getQuery(true);
					$query->update($this->_db->quoteName('#__emundus_setup_programmes'))
						->set($fields)
						->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($id));

					try {
						$this->_db->setQuery($query);
						$updated = $this->_db->execute();

						if ($updated) {

							JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterProgramUpdate', ['id' => $id, 'data' => $data]]);
						}
					}
					catch (Exception $e) {
						JLog::add('component/com_emundus/models/program | Error when updating the program ' . $id . ': ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
					}
				}
			}
		}

		return $updated;
	}

	/**
	 * @param   array  $data  the row to delete in table.
	 *
	 * @return boolean
	 * Delete program(s) in DB
	 * @since version 1.0
	 */
	public function deleteProgram($data)
	{
		$deleted = false;

		if (!empty($data)) {
			if (!is_array($data)) {
				$data = [$data];
			}

			// Call plugin event before we delete the programme
			JPluginHelper::importPlugin('emundus');

			JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeProgramDelete', ['data' => $data]]);


			$query = $this->_db->getQuery(true);

			try {
				$query->select($this->_db->qn('sc.id'))
					->from($this->_db->qn('#__emundus_setup_campaigns', 'sc'))
					->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'sp') . ' ON ' . $this->_db->quoteName('sc.training') . ' LIKE ' . $this->_db->quoteName('sp.code'))
					->where($this->_db->quoteName('sp.id') . ' IN (' . implode(", ", array_values($data)) . ')');

				$this->_db->setQuery($query);
				$campaigns = $this->_db->loadColumn();

				if (!empty($campaigns)) {
					require_once(JPATH_SITE . '/components/com_emundus/models/campaign.php');
					$m_campaign       = new EmundusModelCampaign;
					$campaign_deleted = $m_campaign->deleteCampaign($campaigns);

					if (!$campaign_deleted) {
						JLog::add('Campaign has not been deleted', JLog::ERROR, 'com_emundus');
					}
				}

				$query->clear()
					->delete($this->_db->quoteName('#__emundus_setup_programmes'))
					->where(array($this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')'));

				$this->_db->setQuery($query);
				$deleted = $this->_db->execute();

				if ($deleted) {

					JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterProgramDelete', ['id' => JFactory::getUser()->id, 'data' => $data]]);
				}
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/program | Error wen delete programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $deleted;
	}

	/**
	 * @param   array  $data  the row to unpublish in table.
	 *
	 * @return boolean
	 * Unpublish program(s) in DB
	 * @since version 1.0
	 */
	public function unpublishProgram($data)
	{


		$query = $this->_db->getQuery(true);

		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($data[$key]);
			}

			try {
				$fields     = array(
					$this->_db->quoteName('published') . ' = 0'
				);
				$conditions = array(
					$this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')',
				);

				$query->update($this->_db->quoteName('#__emundus_setup_programmes'))
					->set($fields)
					->where($conditions);

				$this->_db->setQuery($query);

				return $this->_db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/program | Error when unpublish programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return $e->getMessage();
			}

		}
		else {
			return false;
		}
	}

	/**
	 * @param   array  $data  the row to publish in table.
	 *
	 * @return boolean
	 * Publish program(s) in DB
	 * @since version 1.0
	 */
	public function publishProgram($data)
	{


		$query = $this->_db->getQuery(true);

		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($data[$key]);
			}

			try {
				$fields     = array(
					$this->_db->quoteName('published') . ' = 1'
				);
				$conditions = array(
					$this->_db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')',
				);

				$query->update($this->_db->quoteName('#__emundus_setup_programmes'))
					->set($fields)
					->where($conditions);

				$this->_db->setQuery($query);

				return $this->_db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/program | Error when publish programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return $e->getMessage();
			}

		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @return array
	 * get list of declared programmes
	 * @since version 1.0
	 */
	public function getProgramCategories()
	{
		$categories = [];


		$query = $this->_db->getQuery(true);

		$query->select('DISTINCT(programmes)')
			->from($this->_db->quoteName('#__emundus_setup_programmes'))
			->order('id DESC');

		try {
			$this->_db->setQuery($query);
			$categories = $this->_db->loadColumn();


			$tmp = [];
			foreach ($categories as $category) {
				if (!empty($category)) {
					$tmp[] = ['value' => $category, 'label' => $category];
				}
			}
			$categories = $tmp;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting program categories : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
		}

		return $categories;
	}

	/**
	 * get list of all campaigns associated to the user
	 *
	 * @param $code
	 *
	 * @return Object
	 * @since version 1.0
	 */
	function getYearsByProgram($code)
	{


		$query = $this->_db->getQuery(true);

		$query->select($this->_db->quoteName('tu.schoolyear'))
			->from($this->_db->quoteName('#__emundus_setup_programmes', 'p'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $this->_db->quoteName('tu.code') . ' LIKE ' . $this->_db->quoteName('p.code'))
			->where($this->_db->quoteName('p.code') . ' = ' . $code)
			->orders('tu.id DESC');

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting teaching unities of the program ' . $code . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return new stdClass();
		}
	}

	/**
	 * @param $group
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function getuserstoaffect($group)
	{

		$query = $this->_db->getQuery(true);

		$notinlist = array();

		try {
			$query->select('us.id')
				->from($this->_db->quoteName('#__emundus_groups', 'g'))
				->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('g.user_id') . ' = ' . $this->_db->quoteName('us.id'))
				->where($this->_db->quoteName('g.group_id') . ' = ' . $this->_db->quote($group))
				->andWhere($this->_db->quoteName('us.id') . ' != 95')
				->group('us.id');
			$this->_db->setQuery($query);
			$usersinprogram = $this->_db->loadObjectList();

			if (!empty($usersinprogram)) {
				foreach ($usersinprogram as $user) {
					$notinlist[] = $user->id;
				}

				$not_conditions = array(
					$this->_db->quoteName('eus.user_id') .
					' NOT IN (' .
					implode(", ", array_values($notinlist)) .
					')'
				);

				$query->clear()
					->select(['us.id AS id, us.name AS name, us.email AS email'])
					->from($this->_db->quoteName('#__emundus_users', 'eus'))
					->leftJoin($this->_db->quoteName('#__users', 'us') .
						' ON ' .
						$this->_db->quoteName('eus.user_id') . ' = ' . $this->_db->quoteName('us.id'))
					->where($not_conditions)
					->andWhere($this->_db->quoteName('eus.user_id') . ' NOT IN (62,95)')
					->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
					->andWhere($this->_db->quoteName('eus.profile') . ' IN (5,6)');
				$this->_db->setQuery($query);
				$users = $this->_db->loadObjectList();
			}
			else {
				$users = $this->getuserswithoutapplicants();
			}

			return $users;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting users that can be affected to the group ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 * @param $group
	 * @param $term
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function getuserstoaffectbyterm($group, $term)
	{

		$query = $this->_db->getQuery(true);

		$notinlist = array();

		try {
			$query->select('us.id')
				->from($this->_db->quoteName('#__emundus_groups', 'g'))
				->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('g.user_id') . ' = ' . $this->_db->quoteName('us.id'))
				->where($this->_db->quoteName('g.group_id') . ' = ' . $this->_db->quote($group))
				->andWhere($this->_db->quoteName('us.id') . ' != 95')
				->group('us.id');
			$this->_db->setQuery($query);
			$usersinprogram = $this->_db->loadObjectList();

			$searchName  = $this->_db->quoteName('us.name') . ' LIKE ' . $this->_db->quote('%' . $term . '%');
			$searchEmail = $this->_db->quoteName('us.email') . ' LIKE ' . $this->_db->quote('%' . $term . '%');
			$fullSearch  = $searchName . ' OR ' . $searchEmail;

			if (!empty($usersinprogram)) {
				foreach ($usersinprogram as $user) {
					$notinlist[] = $user->id;
				}

				$not_conditions = array(
					$this->_db->quoteName('eus.user_id') .
					' NOT IN (' .
					implode(", ", array_values($notinlist)) .
					')'
				);

				$query->clear()
					->select(['us.id AS id, us.name AS name, us.email AS email'])
					->from($this->_db->quoteName('#__emundus_users', 'eus'))
					->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('eus.user_id') . ' = ' . $this->_db->quoteName('us.id'))
					->where($not_conditions)
					->andWhere($this->_db->quoteName('eus.user_id') . ' NOT IN (62,95)')
					->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
					->andWhere($this->_db->quoteName('eus.profile') . ' IN (5,6)')
					->andWhere($fullSearch);
			}
			else {
				$query->select(['us.id AS id, us.name AS name, us.email AS email'])
					->from($this->_db->quoteName('#__emundus_users', 'eus'))
					->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('eus.user_id') . ' = ' . $this->_db->quoteName('us.id'))
					->where($this->_db->quoteName('eus.user_id') . ' NOT IN (62,95)')
					->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
					->andWhere($this->_db->quoteName('eus.profile') . ' IN (5,6)')
					->andWhere($fullSearch);
			}

			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting users that can be affected to the group with a search term ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 * @param $group
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function getManagers($group)
	{

		$query = $this->_db->getQuery(true);

		$query->select(['us.id as id', 'us.name as name', 'us.email as email'])
			->from($this->_db->quoteName('#__emundus_groups', 'g'))
			->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('g.user_id') . ' = ' . $this->_db->quoteName('us.id'))
			->where($this->_db->quoteName('g.group_id') . ' = ' . $this->_db->quote($group))
			->andWhere($this->_db->quoteName('us.id') . ' != 95')
			->group('us.id');

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting administrators of the group ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 * @param $group
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function getEvaluators($group)
	{

		$query = $this->_db->getQuery(true);

		$query->select(['us.id as id', 'us.name as name', 'us.email as email'])
			->from($this->_db->quoteName('#__emundus_groups', 'g'))
			->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('g.user_id') . ' = ' . $this->_db->quoteName('us.id'))
			->where($this->_db->quoteName('g.group_id') . ' = ' . $this->_db->quote($group))
			->group('us.id');

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting evaluators of the group ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 * @param $group
	 * @param $email
	 * @param $prog_group
	 *
	 * @return false|mixed|null
	 *
	 * @since version 1.0
	 */
	function affectusertogroups($group, $email, $prog_group)
	{

		$query = $this->_db->getQuery(true);

		try {
			$query->select('id')
				->from($this->_db->quoteName('#__users'))
				->where($this->_db->quoteName('email') . ' = ' . $this->_db->quote($email));
			$this->_db->setQuery($query);
			$uid = $this->_db->loadResult();

			$query->clear()
				->insert($this->_db->quoteName('#__emundus_groups'))
				->set($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($uid))
				->set($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($group));
			$this->_db->setQuery($query);
			$this->_db->execute();

			$query->clear()
				->insert($this->_db->quoteName('#__emundus_groups'))
				->set($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($uid))
				->set($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($prog_group));
			$this->_db->setQuery($query);
			$this->_db->execute();

			return $uid;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot affect the user ' . $email . ' to the group ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $group
	 * @param $users
	 * @param $prog_group
	 *
	 * @return bool
	 *
	 * @since version 1.0
	 */
	function affectuserstogroup($group, $users, $prog_group)
	{
		foreach ($users as $user) {

			$query = $this->_db->getQuery(true);

			try {
				$query->clear()
					->insert($this->_db->quoteName('#__emundus_groups'))
					->set($this->_db->quoteName('user_id') . ' = ' . $user)
					->set($this->_db->quoteName('group_id') . ' = ' . $group);
				$this->_db->setQuery($query);
				$this->_db->execute();

				$query->clear()
					->insert($this->_db->quoteName('#__emundus_groups'))
					->set($this->_db->quoteName('user_id') . ' = ' . $user)
					->set($this->_db->quoteName('group_id') . ' = ' . $prog_group);
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/program | Cannot affect users to the group ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

				return false;
			}
		}

		return true;
	}

	/**
	 * @param $userid
	 * @param $group
	 * @param $prog_group
	 *
	 * @return false|mixed
	 *
	 * @since version 1.0
	 */
	function removefromgroup($userid, $group, $prog_group)
	{

		$query = $this->_db->getQuery(true);
		try {
			$query->delete($this->_db->quoteName('#__emundus_groups'))
				->where($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($userid))
				->andWhere($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($group));
			$this->_db->setQuery($query);
			$this->_db->execute();

			$query->clear()
				->delete($this->_db->quoteName('#__emundus_groups'))
				->where($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($userid))
				->andWhere($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($prog_group));
			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot remove user ' . $userid . ' from the group ' . $group . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $filters
	 * @param $page
	 *
	 * @return array
	 *
	 * @since version 1.0
	 */
	function getusers($filters, $page = null)
	{

		$query = $this->_db->getQuery(true);

		$limit = 10;

		if ($page == null) {
			$offset = 0;
		}
		else {
			$offset = (intval($page) - 1) * $limit;
		}

		$user = JFactory::getUser()->id;

		$block_conditions = $this->_db->quoteName('block') . ' = ' . $this->_db->quote(0);
		if ($filters['block'] == 'true') {
			$block_conditions = $this->_db->quoteName('block') . ' = ' . $this->_db->quote(0) . ' OR ' . $this->_db->quote(1);
		}

		if ($filters['searchProgram'] != -1) {
			$query->select('sgr.parent_id AS parent_id')
				->from($this->_db->quoteName('#__emundus_setup_programmes', 'sp'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr') . ' ON ' . $this->_db->quoteName('sp.code') . ' LIKE ' . $this->_db->quoteName('sgr.course'))
				->where($this->_db->quoteName('sp.id') . ' = ' . $filters['searchProgram']);
			$this->_db->setQuery($query);
			$group = $this->_db->loadObject()->parent_id;

			$query->clear()
				->select('us.id as id, us.name as name, us.email as email, us.registerDate as registerDate, us.lastvisitDate as lastvisitDate, us.block as block, eus.profile as profile')
				->from($this->_db->quoteName('#__users', 'us'))
				->leftJoin($this->_db->quoteName('#__emundus_groups', 'g') . ' ON ' . $this->_db->quoteName('us.id') . ' = ' . $this->_db->quoteName('g.user_id'))
				->leftJoin($this->_db->quoteName('#__emundus_users', 'eus') . ' ON ' . $this->_db->quoteName('us.id') . ' = ' . $this->_db->quoteName('eus.user_id'));

			if ($filters['searchRole'] != -1) {
				$query->where($this->_db->quoteName('eus.profile') . ' = ' . $this->_db->quote($filters['searchRole']));
			}
			$query->where($this->_db->quoteName('g.group_id') . ' = ' . $this->_db->quote($group))
				->andWhere($this->_db->quoteName('us.id') . ' != ' . $this->_db->quote($user))
				->andWhere($this->_db->quoteName('us.id') . ' != 62')
				->andWhere($this->_db->quoteName('eus.profile') . ' IN (5,6)')
				->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
				->andWhere($block_conditions);
		}
		else {
			$query->select('us.id as id, us.name as name, us.email as email, us.registerDate as registerDate, us.lastvisitDate as lastvisitDate, us.block as block, eus.profile as profile')
				->from($this->_db->quoteName('#__users', 'us'))
				->leftJoin($this->_db->quoteName('#__emundus_users', 'eus') . ' ON ' . $this->_db->quoteName('us.id') . ' = ' . $this->_db->quoteName('eus.user_id'));

			if ($filters['searchRole'] != -1) {
				$query->where($this->_db->quoteName('eus.profile') . ' = ' . $this->_db->quote($filters['searchRole']));
			}
			$query->where($this->_db->quoteName('us.id') . ' != ' . $this->_db->quote($user))
				->andWhere($this->_db->quoteName('us.id') . ' != 62')
				->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
				->andWhere($this->_db->quoteName('eus.profile') . ' IN (5,6)')
				->andWhere($block_conditions);
		}

		try {
			$this->_db->setQuery($query);
			$users_count = count($this->_db->loadObjectList());
			$this->_db->setQuery($query, $offset, $limit);
			$users = $this->_db->loadObjectList();

			return array(
				'users'       => $users,
				'users_count' => $users_count,
			);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting users : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function getuserswithoutapplicants()
	{

		$query = $this->_db->getQuery(true);

		$query->select(['us.id AS id, us.name AS name, us.email AS email'])
			->from($this->_db->quoteName('#__emundus_users', 'eus'))
			->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('eus.user_id') . ' = ' . $this->_db->quoteName('us.id'))
			->where($this->_db->quoteName('eus.user_id') . ' != 62')
			->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
			->andWhere($this->_db->quoteName('eus.profile') . ' IN (5,6)');

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting users without applicants : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 * @param $term
	 *
	 * @return array|mixed
	 *
	 * @since version 1.0
	 */
	function searchuserbytermwithoutapplicants($term)
	{

		$query = $this->_db->getQuery(true);

		$searchName  = $this->_db->quoteName('us.name') . ' LIKE ' . $this->_db->quote('%' . $term . '%');
		$searchEmail = $this->_db->quoteName('us.email') . ' LIKE ' . $this->_db->quote('%' . $term . '%');
		$fullSearch  = $searchName . ' OR ' . $searchEmail;

		$query->select(['us.id AS id, us.name AS name, us.email AS email'])
			->from($this->_db->quoteName('#__emundus_users', 'eus'))
			->leftJoin($this->_db->quoteName('#__users', 'us') . ' ON ' . $this->_db->quoteName('eus.user_id') . ' = ' . $this->_db->quoteName('us.id'))
			->where($this->_db->quoteName('eus.user_id') . ' != 62')
			->andWhere($this->_db->quoteName('us.username') . ' != ' . $this->_db->quote('sysemundus'))
			->andWhere($fullSearch);

		try {
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting users by term without applicants : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

	/**
	 * @param $cid
	 * @param $gid
	 * @param $visibility
	 *
	 * @return bool
	 *
	 * @since version 1.0
	 */
	function updateVisibility($cid, $gid, $visibility)
	{

		$query = $this->_db->getQuery(true);

		$query->select('sg.id AS id')
			->from($this->_db->quoteName('#__emundus_setup_campaigns', 'c'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'gc') . ' ON ' . $this->_db->quoteName('c.training') . ' LIKE ' . $this->_db->quoteName('gc.course'))
			->leftJoin($this->_db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $this->_db->quoteName('gc.parent_id') . ' = ' . $this->_db->quoteName('sg.id'))
			->where($this->_db->quoteName('c.id') . ' = ' . $this->_db->quote($cid))
			->andWhere($this->_db->quoteName('sg.description') . ' LIKE ' . $this->_db->quote('constraint_group'));
		$this->_db->setQuery($query);
		$group_prog_id = $this->_db->loadObject();

		if ($group_prog_id == null) {
			$query->clear()
				->select('gc.parent_id AS id')
				->from($this->_db->quoteName('#__emundus_setup_campaigns', 'c'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'gc') . ' ON ' . $this->_db->quoteName('c.training') . ' LIKE ' . $this->_db->quoteName('gc.course'))
				->where($this->_db->quoteName('c.id') . ' = ' . $this->_db->quote($cid));
			$this->_db->setQuery($query);
			$old_group = $this->_db->loadObject();

			$constraintgroupid = $this->clonegroup($old_group->id);
		}
		else {
			$constraintgroupid = $group_prog_id->id;
		}

		$query->clear()
			->select('count(*)')
			->from($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
			->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($constraintgroupid));
		$this->_db->setQuery($query);
		$groups_constraints = $this->_db->loadResult();

		if ($groups_constraints == 0) {
			$query->clear()
				->select('sf.profile_id')
				->from($this->_db->quoteName('#__fabrik_formgroup', 'ffg'))
				->leftJoin($this->_db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $this->_db->quoteName('fl.form_id') . ' = ' . $this->_db->quoteName('ffg.form_id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_formlist', 'sf') . ' ON ' . $this->_db->quoteName('sf.form_id') . ' = ' . $this->_db->quoteName('fl.id'))
				->where($this->_db->quoteName('ffg.group_id') . ' = ' . $this->_db->quote($gid));
			$this->_db->setQuery($query);
			$profile_id = $this->_db->loadResult();

			$query->clear()
				->select('fl.form_id')
				->from($this->_db->quoteName('#__emundus_setup_formlist', 'sf'))
				->leftJoin($this->_db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $this->_db->quoteName('fl.id') . ' = ' . $this->_db->quoteName('sf.form_id'))
				->where($this->_db->quoteName('sf.profile_id') . ' = ' . $this->_db->quote($profile_id));
			$this->_db->setQuery($query);
			$forms = $this->_db->loadObjectList();

			foreach ($forms as $form) {
				$query->clear()
					->select('group_id')
					->from($this->_db->quoteName('#__fabrik_formgroup'))
					->where($this->_db->quoteName('form_id') . ' = ' . $this->_db->quote($form->form_id));
				$this->_db->setQuery($query);
				$groups = $this->_db->loadObjectList();

				foreach ($groups as $group) {
					if ($gid != $group->group_id) {
						$query->clear()
							->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
							->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($constraintgroupid))
							->set($this->_db->quoteName('fabrik_group_link') . ' = ' . $this->_db->quote($group->group_id));
						try {
							$this->_db->setQuery($query);
							$this->_db->execute();
						}
						catch (Exception $e) {
							JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
						}

						$query->clear()
							->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
							->set($this->_db->quoteName('parent_id') . ' = 2')
							->set($this->_db->quoteName('fabrik_group_link') . ' = ' . $this->_db->quote($group->group_id));
						try {
							$this->_db->setQuery($query);
							$this->_db->execute();
						}
						catch (Exception $e) {
							JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
						}
					}
				}
			}
		}
		else {
			if ($visibility == 'false') {
				$query->clear()
					->delete($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
					->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($constraintgroupid))
					->andWhere($this->_db->quoteName('fabrik_group_link') . ' = ' . $this->_db->quote($gid));
				try {
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
				catch (Exception $e) {
					JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
				}

				$query->clear()
					->delete($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
					->where($this->_db->quoteName('parent_id') . ' = 2')
					->andWhere($this->_db->quoteName('fabrik_group_link') . ' = ' . $this->_db->quote($gid));
				try {
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
				catch (Exception $e) {
					JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
				}
			}
			else {
				$query->clear()
					->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
					->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($constraintgroupid))
					->set($this->_db->quoteName('fabrik_group_link') . ' = ' . $this->_db->quote($gid));
				try {
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
				catch (Exception $e) {
					JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
				}

				$query->clear()
					->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
					->set($this->_db->quoteName('parent_id') . ' = 2')
					->set($this->_db->quoteName('fabrik_group_link') . ' = ' . $this->_db->quote($gid));
				try {
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
				catch (Exception $e) {
					JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
				}
			}
		}

		return true;
	}

	/**
	 * @param $gid
	 *
	 * @return mixed|void
	 *
	 * @since version 1.0
	 */
	function clonegroup($gid)
	{

		$query = $this->_db->getQuery(true);

		// Get programme code and group to clone
		$query->select('*')
			->from($this->_db->quoteName('#__emundus_setup_groups'))
			->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($gid));
		$this->_db->setQuery($query);
		$grouptoclone = $this->_db->loadObject();

		$query->clear()
			->insert($this->_db->quoteName('#__emundus_setup_groups'))
			->set($this->_db->quoteName('label') . ' = ' . $this->_db->quote($grouptoclone->label))
			->set($this->_db->quoteName('description') . ' = ' . $this->_db->quote('constraint_group'))
			->set($this->_db->quoteName('published') . ' = 1')
			->set($this->_db->quoteName('class') . ' = ' . $this->_db->quote('label-default'));

		try {
			$this->_db->setQuery($query);
			$this->_db->execute();
			$newgroup = $this->_db->insertid();

			$query->select('*')
				->from($this->_db->quoteName('#__emundus_setup_groups_repeat_course'))
				->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($gid));
			$this->_db->setQuery($query);
			$groupcoursetoclone = $this->_db->loadObject();

			$query->clear();
			$query->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_course'));
			foreach ($groupcoursetoclone as $key => $val) {
				if ($key != 'id' && $key != 'parent_id') {
					$query->set($key . ' = ' . $this->_db->quote($val));
				}
				elseif ($key == 'parent_id') {
					$query->set($key . ' = ' . $this->_db->quote($newgroup));
				}
			}
			try {
				$this->_db->setQuery($query);
				$this->_db->execute();

				$evalutorstomove = $this->getEvaluators($gid);

				foreach ($evalutorstomove as $evalutortomove) {
					$query->clear()
						->update($this->_db->quoteName('#__emundus_groups'))
						->set($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($newgroup))
						->where($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($gid))
						->andWhere($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($evalutortomove->id));
					try {
						$this->_db->setQuery($query);
						$this->_db->execute();
					}
					catch (Exception $e) {
						JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
					}
				}

				return $newgroup;
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}

	/**
	 * @param $pid
	 *
	 * @return false|mixed|null
	 *
	 * @since version 1.0
	 */
	function getEvaluationGrid($pid)
	{

		$query = $this->_db->getQuery(true);

		try {
			$query->select('fabrik_group_id')
				->from($this->_db->quoteName('#__emundus_setup_programmes'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($pid));
			$this->_db->setQuery($query);
			$fabrik_groups = explode(',', $this->_db->loadResult());

			$query->clear()
				->select('form_id')
				->from($this->_db->quoteName('#__fabrik_formgroup'))
				->where($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($fabrik_groups[0]));
			$this->_db->setQuery($query);

			return $this->_db->loadResult();

		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting evaluation grid of the program ' . $pid . ': ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $group
	 * @param $pid
	 *
	 * @return false|mixed
	 *
	 * @since version 1.0
	 */
	function affectGroupToProgram($group, $pid)
	{

		$query = $this->_db->getQuery(true);

		try {
			$query
				->select('fabrik_group_id')
				->from($this->_db->quoteName('#__emundus_setup_programmes'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($pid));
			$this->_db->setQuery($query);
			$program_groups = $this->_db->loadResult();

			if ($program_groups == '') {
				$program_groups = $group;
			}
			else {
				$program_groups = $program_groups . ',' . $group;
			}

			$query->clear()
				->update($this->_db->quoteName('#__emundus_setup_programmes'))
				->set($this->_db->quoteName('fabrik_group_id') . ' = ' . $this->_db->quote($program_groups))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($pid));
			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot affect fabrik_group ' . $group . ' to program ' . $pid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $group
	 * @param $pid
	 *
	 * @return false|mixed
	 *
	 * @since version 1.0
	 */
	function deleteGroupFromProgram($group, $pid)
	{

		$query = $this->_db->getQuery(true);

		try {
			$query->select('fabrik_group_id')
				->from($this->_db->quoteName('#__emundus_setup_programmes'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($pid));
			$this->_db->setQuery($query);
			$program_groups = $this->_db->loadResult();

			$program_groups = str_replace($group, '', $program_groups);
			$program_groups = str_replace(',,', ',', $program_groups);

			var_dump(strrpos($program_groups, ','));
			var_dump(strlen($program_groups));

			if (strrpos($program_groups, ',') == (strlen($program_groups) - 1)) {
				$program_groups = substr($program_groups, 0, -1);
			}

			$query->clear()
				->update($this->_db->quoteName('#__emundus_setup_programmes'))
				->set($this->_db->quoteName('fabrik_group_id') . ' = ' . $this->_db->quote($program_groups))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($pid));
			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot remove fabrik_group ' . $group . ' from the program ' . $pid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $label
	 * @param $intro
	 * @param $model
	 * @param $pid
	 *
	 * @return false|mixed
	 *
	 * @since version 1.0
	 */
	function createGridFromModel($label, $intro, $model, $pid)
	{
		// Prepare Fabrik API
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
		$form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		$form->setId(intval($model));
		$groups = $form->getGroups();

		// Prepare languages
		$path_to_file   = basename(__FILE__) . '/../language/overrides/';
		$path_to_files  = array();
		$Content_Folder = array();
		$languages      = JLanguageHelper::getLanguages();
		foreach ($languages as $language) {
			$path_to_files[$language->sef]  = $path_to_file . $language->lang_code . '.override.ini';
			$Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
		}

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');

		$formbuilder = new EmundusModelFormbuilder;

		$new_groups = [];


		$query = $this->_db->getQuery(true);

		$query->select('*')
			->from('#__fabrik_forms')
			->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($model));
		$this->_db->setQuery($query);
		$form_model = $this->_db->loadObject();

		$query->clear();
		$query->insert($this->_db->quoteName('#__fabrik_forms'));
		foreach ($form_model as $key => $val) {
			if ($key != 'id') {
				$query->set($key . ' = ' . $this->_db->quote($val));
			}
		}
		try {
			$this->_db->setQuery($query);
			$this->_db->execute();
			$formid = $this->_db->insertid();

			// Update translation files
			$query->clear();
			$query->update($this->_db->quoteName('#__fabrik_forms'));

			$formbuilder->translate('FORM_' . $pid . '_' . $formid, $label, 'fabrik_forms', $formid, 'label');
			$formbuilder->translate('FORM_' . $pid . '_INTRO_' . $formid, $intro, 'fabrik_forms', $formid, 'intro');
			//

			$query->set('label = ' . $this->_db->quote('FORM_' . $pid . '_' . $formid));
			$query->set('intro = ' . $this->_db->quote('<p>' . 'FORM_' . $pid . '_INTRO_' . $formid . '</p>'));
			$query->where('id =' . $formid);
			$this->_db->setQuery($query);
			$this->_db->execute();
			//

			$query->clear()
				->select('*')
				->from('#__fabrik_lists')
				->where($this->_db->quoteName('form_id') . ' = ' . $this->_db->quote($model));
			$this->_db->setQuery($query);
			$list_model = $this->_db->loadObject();

			$query->clear();
			$query->insert($this->_db->quoteName('#__fabrik_lists'));
			foreach ($list_model as $key => $val) {
				if ($key != 'id' && $key != 'form_id') {
					$query->set($key . ' = ' . $this->_db->quote($val));
				}
				elseif ($key == 'form_id') {
					$query->set($key . ' = ' . $this->_db->quote($formid));
				}
			}
			$this->_db->setQuery($query);
			$this->_db->execute();
			$newlistid = $this->_db->insertid();

			$query->clear();
			$query->update($this->_db->quoteName('#__fabrik_lists'));
			$query->set('label = ' . $this->_db->quote('FORM_' . $pid . '_' . $formid));
			$query->set('introduction = ' . $this->_db->quote('<p>' . 'FORM_' . $pid . '_INTRO_' . $formid . '</p>'));
			$query->where('id =' . $this->_db->quote($newlistid));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Duplicate group
			$ordering = 0;
			foreach ($groups as $group) {
				$ordering++;
				$properties = $group->getGroupProperties($group->getFormModel());
				$elements   = $group->getMyElements();

				$query->clear()
					->select('*')
					->from('#__fabrik_groups')
					->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($properties->id));
				$this->_db->setQuery($query);
				$group_model = $this->_db->loadObject();

				$query->clear();
				$query->insert($this->_db->quoteName('#__fabrik_groups'));
				foreach ($group_model as $key => $val) {
					if ($key != 'id') {
						$query->set($key . ' = ' . $this->_db->quote($val));
					}
				}
				$this->_db->setQuery($query);
				$this->_db->execute();
				$newgroupid   = $this->_db->insertid();
				$new_groups[] = $newgroupid;

				// Update translation files
				$query->clear();
				$query->update($this->_db->quoteName('#__fabrik_groups'));

				$labels_to_duplicate = array(
					'fr' => $formbuilder->getTranslation($group_model->label, 'fr-FR'),
					'en' => $formbuilder->getTranslation($group_model->label, 'en-GB')
				);
				if ($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
					$labels_to_duplicate = array(
						'fr' => $group_model->label,
						'en' => $group_model->label
					);
				}
				$formbuilder->translate('GROUP_' . $formid . '_' . $newgroupid, $labels_to_duplicate, 'fabrik_groups', $newgroupid, 'label');

				$query->set('label = ' . $this->_db->quote('GROUP_' . $formid . '_' . $newgroupid));
				$query->set('name = ' . $this->_db->quote('GROUP_' . $formid . '_' . $newgroupid));
				$query->where('id =' . $newgroupid);
				$this->_db->setQuery($query);
				$this->_db->execute();

				$query->clear()
					->insert($this->_db->quoteName('#__fabrik_formgroup'))
					->set('form_id = ' . $this->_db->quote($formid))
					->set('group_id = ' . $this->_db->quote($newgroupid))
					->set('ordering = ' . $this->_db->quote($ordering));
				$this->_db->setQuery($query);
				$this->_db->execute();

				foreach ($elements as $element) {
					try {
						// Default parameters
						$dbtype = 'VARCHAR(255)';
						$dbnull = 'NULL';
						//

						$newelement = $element->copyRow($element->element->id, '%s', $newgroupid);
						//add to array
						$newElementArray[] = $newelement->id;

						$skipped_elms = [
							'id',
							'time_date',
							'fnum',
							'student_id',
							'user'
						];

						if (in_array($element->element->name, $skipped_elms)) {
							continue;
						}

						$newelementid = $newelement->id;

						$el_params = json_decode($element->element->params);

						// Update translation files
						if (($element->element->plugin === 'checkbox' || $element->element->plugin === 'radiobutton' || $element->element->plugin === 'dropdown') && $el_params->sub_options) {
							$sub_labels = [];
							foreach ($el_params->sub_options->sub_labels as $index => $sub_label) {
								$labels_to_duplicate = array(
									'fr' => $formbuilder->getTranslation($sub_label, 'fr-FR'),
									'en' => $formbuilder->getTranslation($sub_label, 'en-GB')
								);
								if ($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
									$labels_to_duplicate = array(
										'fr' => $sub_label,
										'en' => $sub_label
									);
								}
								$formbuilder->translate('SUBLABEL_' . $newgroupid . '_' . $newelementid . '_' . $index, $labels_to_duplicate, 'fabrik_elements', $newelementid, 'sub_labels');
								$sub_labels[] = 'SUBLABEL_' . $newgroupid . '_' . $newelementid . '_' . $index;
							}
							$el_params->sub_options->sub_labels = $sub_labels;
						}
						$query->clear();
						$query->update($this->_db->quoteName('#__fabrik_elements'));

						$labels_to_duplicate = array(
							'fr' => $formbuilder->getTranslation($element->element->label, 'fr-FR'),
							'en' => $formbuilder->getTranslation($element->element->label, 'en-GB')
						);
						if ($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
							$labels_to_duplicate = array(
								'fr' => $element->element->label,
								'en' => $element->element->label
							);
						}
						$formbuilder->translate('ELEMENT_' . $newgroupid . '_' . $newelementid, $labels_to_duplicate, 'fabrik_elements', $newelementid, 'label');
						//

						$query->set('label = ' . $this->_db->quote('ELEMENT_' . $newgroupid . '_' . $newelementid));
						$query->set('name = ' . $this->_db->quote('criteria_' . $formid . '_' . $newelementid));
						$query->set('params = ' . $this->_db->quote(json_encode($el_params)));
						$query->where('id =' . $newelementid);
						$this->_db->setQuery($query);
						$this->_db->execute();

						if ($element->element->plugin === 'birthday') {
							$dbtype = 'DATE';
						}
						elseif ($element->element->plugin === 'textarea') {
							$dbtype = 'TEXT';
						}

						$query = "ALTER TABLE jos_emundus_evaluations" . " ADD criteria_" . $formid . "_" . $newelementid . " " . $dbtype . " " . $dbnull;
						$this->_db->setQuery($query);
						$this->_db->execute();
						$query = $this->_db->getQuery(true);
					}
					catch (Exception $e) {
						JLog::add('component/com_emundus/models/program | Cannot create a grid from the model ' . $model . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

						return false;
					}
				}

				// publish new elements. It is outside the foreach so we can publish the skipped elements
				$query
					->clear()
					->update($this->_db->quoteName('#__fabrik_elements'))
					->set('published =  1')
					->where('id IN (' . implode(',', $newElementArray) . ')');
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
			//

			// Link groups to programme
			$query->clear()
				->update($this->_db->quoteName('#__emundus_setup_programmes'))
				->set($this->_db->quoteName('fabrik_group_id') . ' = ' . $this->_db->quote(implode(',', $new_groups)))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($pid));
			$this->_db->setQuery($query);

			return $this->_db->execute();

		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot create a grid from the model ' . $model . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 *
	 * @return array|false|mixed
	 *
	 * @since version 1.0
	 */
	function getGridsModel()
	{
		
		$query = $this->_db->getQuery(true);

		$query->select('*')
			->from($this->_db->quoteName('#__emundus_template_evaluation'))
			->order('form_id');

		try {
			$this->_db->setQuery($query);
			$models = $this->_db->loadObjectList();

			foreach ($models as $model) {
				$model->label = JText::_($model->label);
				$model->intro = JText::_(strip_tags($model->intro));
			}

			return $models;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting evaluation models : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $label
	 * @param $intro
	 * @param $pid
	 * @param $template
	 *
	 * @return bool
	 *
	 * @since version 1.0
	 */
	function createGrid($label, $intro, $pid, $template)
	{
		
		$query = $this->_db->getQuery(true);

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formbuilder.php');

		$formbuilder = new EmundusModelFormbuilder;

		try {
			// INSERT FABRIK_FORMS
			$query->clear()
				->select('*')
				->from('#__fabrik_forms')
				->where($this->_db->quoteName('id') . ' = 270');
			$this->_db->setQuery($query);
			$form_model = $this->_db->loadObject();

			$query->clear();
			$query->insert($this->_db->quoteName('#__fabrik_forms'));
			foreach ($form_model as $key => $val) {
				if ($key != 'id') {
					$query->set($key . ' = ' . $this->_db->quote($val));
				}
			}
			$this->_db->setQuery($query);
			$this->_db->execute();
			$formid = $this->_db->insertid();

			$query->clear()
				->update($this->_db->quoteName('#__fabrik_forms'))
				->set($this->_db->quoteName('label') . ' = ' . $this->_db->quote('FORM_' . $pid . '_' . $formid))
				->set($this->_db->quoteName('intro') . ' = ' . $this->_db->quote('<p>' . 'FORM_' . $pid . '_INTRO_' . $formid . '</p>'))
				->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($formid));

			$this->_db->setQuery($query);
			$this->_db->execute();

			$formbuilder->translate('FORM_' . $pid . '_' . $formid, $label, 'fabrik_forms', $formid, 'label');
			$formbuilder->translate('FORM_' . $pid . '_INTRO_' . $formid, $intro, 'fabrik_forms', $formid, 'intro');
			//

			// INSERT FABRIK LIST
			$query->clear()
				->select('*')
				->from('#__fabrik_lists')
				->where($this->_db->quoteName('form_id') . ' = 270');
			$this->_db->setQuery($query);
			$list_model = $this->_db->loadObject();

			$query->clear();
			$query->insert($this->_db->quoteName('#__fabrik_lists'));
			foreach ($list_model as $key => $val) {
				if ($key != 'id' && $key != 'form_id') {
					$query->set($key . ' = ' . $this->_db->quote($val));
				}
				elseif ($key == 'form_id') {
					$query->set($key . ' = ' . $this->_db->quote($formid));
				}
			}
			$this->_db->setQuery($query);
			$this->_db->execute();
			$listid = $this->_db->insertid();

			$query->clear();
			$query->update($this->_db->quoteName('#__fabrik_lists'));

			$query->set('label = ' . $this->_db->quote('FORM_' . $pid . '_' . $formid));
			$query->set('access = ' . $this->_db->quote($pid));
			$query->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($listid));
			$this->_db->setQuery($query);
			$this->_db->execute();
			//

			//$formbuilder->createHiddenGroup($formid,1);
			$group = $formbuilder->createGroup($label, $formid);

			// Link groups to program
			$this->affectGroupToProgram($group['group_id'], $pid);
			//

			// Save as template
			if ($template == 'true') {
				$query->clear()
					->insert($this->_db->quoteName('#__emundus_template_evaluation'))
					->set($this->_db->quoteName('form_id') . ' = ' . $this->_db->quote($formid))
					->set($this->_db->quoteName('label') . ' = ' . $this->_db->quote('FORM_' . $pid . '_' . $formid))
					->set($this->_db->quoteName('created') . ' = ' . $this->_db->quote(date('Y-m-d H:i:s')));
				$this->_db->setQuery($query);
				$this->_db->execute();
			}

			//

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot create a grid in the program' . $pid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $grid
	 * @param $pid
	 *
	 * @return false|mixed
	 *
	 * @since version 1.0
	 */
	function deleteGrid($grid, $pid)
	{
		
		$query = $this->_db->getQuery(true);

		$query->update($this->_db->quoteName('#__emundus_setup_programmes'))
			->set($this->_db->quoteName('fabrik_group_id') . ' = NULL')
			->where($this->_db->quoteName('id') . ' = ' . $pid);

		try {
			$this->_db->setQuery($query);
			$this->_db->execute();

			$query->clear()
				->update($this->_db->quoteName('#__fabrik_forms'))
				->set($this->_db->quoteName('published') . ' = 0')
				->where($this->_db->quoteName('id') . ' = ' . $grid);

			$this->_db->setQuery($query);

			return $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at delete the grid ' . $grid . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $user_id
	 *
	 * @return array|false
	 *
	 * @since version 1.0
	 */
	function getUserPrograms($user_id)
	{
		$user_programs = [];

		if (!empty($user_id)) {
			
			$query = $this->_db->getQuery(true);

			$query->select('distinct sp.code')
				->from($this->_db->quoteName('#__emundus_groups', 'g'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $this->_db->quoteName('g.group_id') . ' = ' . $this->_db->quoteName('sg.id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr') . ' ON ' . $this->_db->quoteName('sg.id') . ' = ' . $this->_db->quoteName('sgr.parent_id'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'sp') . ' ON ' . $this->_db->quoteName('sgr.course') . ' = ' . $this->_db->quoteName('sp.code'))
				->where($this->_db->quoteName('g.user_id') . ' = ' . $this->_db->quote($user_id));
			$this->_db->setQuery($query);

			try {
				$programs = $this->_db->loadObjectList();

				$progs = [];
				foreach ($programs as $program) {
					if ($program->code != null) {
						$progs[] = $program->code;
					}
				}

				$user_programs = $progs;
			}
			catch (Exception $e) {
				JLog::add('component/com_emundus/models/program | Error at getting programs of the user ' . $user_id . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $user_programs;
	}

	/**
	 * @param $programs
	 *
	 * @return array|false
	 *
	 * @since version 1.0
	 */
	function getGroupsByPrograms($programs)
	{
		
		$query = $this->_db->getQuery(true);

		$groups = array();

		try {
			foreach ($programs as $id => $program) {
				if ($program == 'true') {
					$query->clear()
						->select('code')
						->from($this->_db->quoteName('#__emundus_setup_programmes'))
						->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($id));
					$this->_db->setQuery($query);
					$code                   = $this->_db->loadResult();
					$groups[$id]            = new stdClass();
					$groups[$id]->manager   = $this->getGroupByParent($code, 3);
					$groups[$id]->evaluator = $this->getGroupByParent($code, 2);

					$query->clear()
						->select('sg.id')
						->from($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr'))
						->leftJoin($this->_db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $this->_db->quoteName('sgr.parent_id') . ' = ' . $this->_db->quoteName('sg.id'))
						->where($this->_db->quoteName('sgr.course') . ' = ' . $this->_db->quote($code))
						->andWhere($this->_db->quoteName('sg.parent_id') . ' IS NULL');
					$this->_db->setQuery($query);
					$groups[$id]->prog = $this->_db->loadResult();
				}
			}

			return $groups;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting groups of programs : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $label
	 * @param $code
	 * @param $parent
	 *
	 * @return bool
	 *
	 * @since version 1.0
	 */
	function addGroupToProgram($label, $code, $parent)
	{
		
		$query = $this->_db->getQuery(true);

		$date   = date('Y-m-d H:i:s');
		$glabel = 'Evaluateurs_' . $label;
		$class  = 'label-default';
		if ($parent == 3) {
			$glabel = 'Gestionnaire de programme_' . $label;
			$class  = 'label-lightgreen';
		}

		try {
			// Create user group
			$query->insert($this->_db->quoteName('#__emundus_setup_groups'))
				->set($this->_db->quoteName('label') . ' = ' . $this->_db->quote($glabel))
				->set($this->_db->quoteName('published') . ' = 1')
				->set($this->_db->quoteName('class') . ' = ' . $this->_db->quote($class))
				->set($this->_db->quoteName('anonymize') . ' = ' . $this->_db->quote(0))
				->set($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($parent));
			$this->_db->setQuery($query);
			$this->_db->execute();
			$group_id = $this->_db->insertid();
			//

			// Link group with programme
			$query->clear()
				->insert($this->_db->quoteName('#__emundus_setup_groups_repeat_course'))
				->set($this->_db->quoteName('parent_id') . ' = ' . $group_id)
				->set($this->_db->quoteName('course') . ' = ' . $this->_db->quote($code));
			$this->_db->setQuery($query);
			$this->_db->execute();
			//

			// Duplicate group_rights
			$query->clear()
				->select('*')
				->from('#__emundus_acl')
				->where($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($parent));
			$this->_db->setQuery($query);
			$acl_models = $this->_db->loadObjectList();

			foreach ($acl_models as $acl_model) {
				$query->clear();
				$query->insert($this->_db->quoteName('#__emundus_acl'));
				foreach ($acl_model as $key => $val) {
					if ($key != 'id' && $key != 'group_id' && $key != 'time_date') {
						$query->set($key . ' = ' . $this->_db->quote($val));
					}
					elseif ($key == 'group_id') {
						$query->set($key . ' = ' . $this->_db->quote($group_id));
					}
					elseif ($key == 'time_date') {
						$query->set($key . ' = ' . $this->_db->quote($date));
					}
				}
				$this->_db->setQuery($query);
				$this->_db->execute();
			}

			//

			return true;
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Cannot add the group ' . $parent . ' to the program ' . $code . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $code
	 * @param $parent
	 *
	 * @return false|mixed|null
	 *
	 * @since version 1.0
	 */
	function getGroupByParent($code, $parent)
	{
		
		$query = $this->_db->getQuery(true);

		try {
			$query->select('sg.id')
				->from($this->_db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $this->_db->quoteName('sgr.parent_id') . ' = ' . $this->_db->quoteName('sg.id'))
				->where($this->_db->quoteName('sgr.course') . ' = ' . $this->_db->quote($code))
				->andWhere($this->_db->quoteName('sg.parent_id') . ' = ' . $this->_db->quote($parent));
			$this->_db->setQuery($query);

			return $this->_db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting groups by parent ' . $parent . ' of the program ' . $code . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	/**
	 * @param $program
	 *
	 * @return array|false|mixed
	 *
	 * @since version 1.0
	 */
	function getCampaignsByProgram($program)
	{
		
		$query = $this->_db->getQuery(true);

		try {
			$query->select('c.*')
				->from($this->_db->quoteName('#__emundus_setup_campaigns', 'c'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_programmes', 'sg') . ' ON ' . $this->_db->quoteName('sg.code') . ' = ' . $this->_db->quoteName('c.training'))
				->where($this->_db->quoteName('sg.id') . ' = ' . $this->_db->quote($program));
			$this->_db->setQuery($query);

			return $this->_db->loadObjectList();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting campaigns by program ' . $program . ' : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return false;
		}
	}

	function getAllSessions()
	{
		
		$query = $this->_db->getQuery(true);

		try {
			$query->select('distinct year')
				->from($this->_db->quoteName('#__emundus_setup_campaigns'));
			$this->_db->setQuery($query);

			return $this->_db->loadColumn();
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/program | Error at getting sessions : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus');

			return [];
		}
	}

}
