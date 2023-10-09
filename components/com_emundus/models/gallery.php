<?php
/**
 * Dashboard model used for the new dashboard in homepage.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

use Joomla\CMS\Factory;

class EmundusModelGallery extends JModelList
{
	protected $_db;

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->_db = JFactory::getDbo();
	}

	function getGalleries($filter = '', $sort = 'DESC', $recherche = '', $lim = 25, $page = 0) {
		$all_galleries = [];

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
		$sortDb = 'id ';

		$filterDate = null;

		$fullRecherche = null;
		if (!empty($recherche)) {
			$fullRecherche = '(' .
				$this->_db->quoteName('title') .
				' LIKE ' .
				$this->_db->quote('%' . $recherche . '%') . ')';
		}

		$query->select('*')->from($this->_db->quoteName('#__emundus_setup_gallery'));

		if (!empty($filterDate)) {
			$query->where($filterDate);
		}
		if (!empty($fullRecherche)) {
			$query->where($fullRecherche);
		}
		$query->group($sortDb)
			->order($sortDb . $sort);

		try {
			$this->_db->setQuery($query);
			$galleries_count = sizeof($this->_db->loadObjectList());

			$this->_db->setQuery($query, $offset, $limit);
			$galleries = $this->_db->loadObjectList();

			if (empty($galleries) && $offset != 0) {
				return $this->getGalleries($filter, $sort, $recherche, $lim, 0);
			}
			$all_galleries = array('datas' => $galleries, 'count' => $galleries_count);
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/campaign | Error when try to get list of campaigns : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
		}

		return $all_galleries;
	}

	public function createGallery($data, $user = null) {
		$result = array();

		if (empty($user)) {
			$user = JFactory::getUser();
		}

		try {
			$query = $this->_db->getQuery(true);

			$list_id = 384;

			//$this->createFabrikList($data['gallery_name'],$user);

			if (!empty($list_id)) {
				// Create SQL view
				$sql_view_created = $this->createSQLView($list_id, $data['campaign_id']);

				// Create gallery configuration
				if ($sql_view_created) {
					$columns = array(
						$this->_db->quoteName('created_at'),
						$this->_db->quoteName('created_by'),
						$this->_db->quoteName('list_id'),
						$this->_db->quoteName('title'),
					);

					$values = array(
						$this->_db->quote(date('Y-m-d H:i:s')),
						$this->_db->quote($user->id),
						$this->_db->quote($list_id),
						$this->_db->quote($data['gallery_name']),
					);

					$query->clear()
						->insert($this->_db->quoteName('#__emundus_setup_gallery'))
						->columns($columns)
						->values(implode(',', $values));
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}

	private function createFabrikList($label, $user) {
		$list_id = 0;
		require_once JPATH_SITE . '/components/com_emundus/helpers/fabrik.php';

		$query = $this->_db->getQuery(true);

		try {
			$form_id = $this->createFabrikForm($label, $user);
			$params = EmundusHelperFabrik::prepareListParams();

			$columns = array(
				$this->_db->quoteName('label'),
				$this->_db->quoteName('introduction'),
				$this->_db->quoteName('form_id'),
				$this->_db->quoteName('db_table_name'),
				$this->_db->quoteName('db_primary_key'),
				$this->_db->quoteName('auto_inc'),
				$this->_db->quoteName('connection_id'),
				$this->_db->quoteName('created'),
				$this->_db->quoteName('created_by'),
				$this->_db->quoteName('created_by_alias'),
				$this->_db->quoteName('modified_by'),
				$this->_db->quoteName('access'),
				$this->_db->quoteName('hits'),
				$this->_db->quoteName('rows_per_page'),
				$this->_db->quoteName('template'),
				$this->_db->quoteName('order_by'),
				$this->_db->quoteName('filter_action'),
				$this->_db->quoteName('group_by'),
				$this->_db->quoteName('params'),
			);

			$values = array(
				$this->_db->quote($label),
				$this->_db->quote(''),
				$this->_db->quote($form_id),
				$this->_db->quote('jos_emundus_campaign_candidature'),
				$this->_db->quote('id'),
				$this->_db->quote('1'),
				$this->_db->quote('1'),
				$this->_db->quote(date('Y-m-d H:i:s')),
				$this->_db->quote($user->id),
				$this->_db->quote($user->username),
				$this->_db->quote('0'),
				$this->_db->quote('1'),
				$this->_db->quote('0'),
				$this->_db->quote('10'),
				$this->_db->quote('default'),
				$this->_db->quote('[]'),
				$this->_db->quote('onchange'),
				$this->_db->quote(''),
				$this->_db->quote(json_encode($params)),
			);

			$query->clear()
				->insert($this->_db->quoteName('#__fabrik_lists'))
				->columns($columns)
				->values(implode(',', $values));
			$this->_db->setQuery($query);
			$this->_db->execute();
			$list_id = $this->_db->insertid();
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $list_id;
	}

	private function createFabrikForm($label,$user) {
		$form_id = 0;

		try {
			//TODO: Create group and elements
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $form_id;
	}

	private function createSQLView($list_id, $campaign_id) {
		$result   = false;
		$nameView = "jos_emundus_gallery_" . $list_id;

		try {
			$query = "SET autocommit = 0;";
			$this->_db->setQuery($query);
			$this->_db->execute();

			$this->_db->transactionStart();

			$query = "CREATE VIEW " . $nameView . " AS select cc.id, cc.status, cc.published
					from `jos_emundus_campaign_candidature` `cc`
					where cc.campaign_id = " . $campaign_id . ";";
			$this->_db->setQuery($query);
			$this->_db->execute();

			$this->_db->transactionCommit();

			$query = "SET autocommit = 1;";
			$this->_db->setQuery($query);
			$result = $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}
}
