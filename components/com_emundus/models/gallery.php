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

	public function __construct($config = array())
    {
		parent::__construct($config);

        require_once JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php';

        $this->_db = JFactory::getDbo();
	}

	public function getGalleries($filter = '', $sort = 'DESC', $recherche = '', $lim = 25, $page = 0)
    {
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

	public function createGallery($data, $user = null)
    {
		$result = array();

		if (empty($user)) {
			$user = JFactory::getUser();
		}

		try {
			$query = $this->_db->getQuery(true);

			$list_id = $this->createFabrikList($data['gallery_name'],$user);

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

	private function createFabrikList($label, $user)
    {
		$list_id = 0;

		try {
            $datas = array(
                'label' => $label
            );
            $form_id = EmundusHelperUpdate::addFabrikForm($datas,[],1,$user)['id'];

            if(!empty($form_id)) {
                $datas = array(
                    'name' => $label
                );
                $group_id = EmundusHelperUpdate::addFabrikGroup($datas,[],1,false,$user)['id'];

                if(!empty($group_id)) {
                    $joined = EmundusHelperUpdate::joinFormGroup($form_id,[$group_id]);

                    if($joined['status']) {
                        $datas = array(
                            'name' => 'id',
                            'group_id' => $group_id,
                            'plugin' => 'internalid'
                        );
                        EmundusHelperUpdate::addFabrikElement($datas,[],$user);

                        $datas = array(
                            'name' => 'fnum',
                            'group_id' => $group_id,
                            'plugin' => 'field'
                        );
                        EmundusHelperUpdate::addFabrikElement($datas,[],$user);

                        $datas = array(
                            'name' => 'status',
                            'group_id' => $group_id,
                            'plugin' => 'databasejoin'
                        );
                        $params = array(
                            'database_join_display_type' => 'dropdown',
                            'join_conn_id' => '1',
                            'join_db_name' => 'jos_emundus_setup_status',
                            'join_key_column' => 'step',
                            'join_val_column' => 'value',
                        );
                        EmundusHelperUpdate::addFabrikElement($datas,$params,$user);

                        $datas = array(
                            'name' => 'published',
                            'group_id' => $group_id,
                            'plugin' => 'field'
                        );
                        EmundusHelperUpdate::addFabrikElement($datas,[],$user);

                        $datas = array(
                            'label' => $label,
                            'form_id' => $form_id,
                            'db_table_name' => 'jos_emundus_gallery'
                        );
                        $params = array(
                            'group_by_access' => 10,
                            'menu_access_only' => 1,
                        );
                        $list_id = EmundusHelperUpdate::addFabrikList($datas,$params,1,$user)['id'];

                        if(!empty($list_id)) {
                            $query = $this->_db->getQuery(true);

                            $query->update($this->_db->quoteName('#__fabrik_lists'))
                                ->set($this->_db->quoteName('db_table_name') . ' = ' . $this->_db->quote('jos_emundus_gallery_' . $list_id))
                                ->set($this->_db->quoteName('db_primary_key') . ' = ' . $this->_db->quote('jos_emundus_gallery_' . $list_id . '.id'))
                                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($list_id));
                            $this->_db->setQuery($query);
                            $this->_db->execute();

                            //TODO: Create public menu linked to this list
                        }
                    }
                }
            }
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $list_id;
	}

	private function createSQLView($list_id, $campaign_id) {
		$result   = false;
		$nameView = "jos_emundus_gallery_" . $list_id;

		try {
			$query = "SET autocommit = 0;";
			$this->_db->setQuery($query);
			$this->_db->execute();

			$this->_db->transactionStart();

			$query = "CREATE VIEW " . $nameView . " AS select cc.id, cc.status, cc.published, cc.fnum
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

    //TODO: Create function to select an element to display (need to manage table join if needed)
}
