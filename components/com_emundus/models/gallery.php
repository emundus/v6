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

		$query->select('esg.*,fl.label')
			->from($this->_db->quoteName('#__emundus_setup_gallery','esg'))
			->leftJoin($this->_db->quoteName('#__fabrik_lists','fl').' ON '.$this->_db->quoteName('fl.id').' = '.$this->_db->quoteName('esg.list_id'));

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

	public function getGalleryByList($listid)
	{
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'cache.php');

		$cache = new EmundusHelperCache('com_emundus');
		$cacheId = 'gallery_' . $listid;

		$gallery = $cache->get($cacheId);

		try {
			$query = $this->_db->getQuery(true);

			if(empty($gallery)) {
				$query->select('*')
					->from($this->_db->quoteName('#__emundus_setup_gallery'))
					->where($this->_db->quoteName('list_id') . ' = ' . $this->_db->quote($listid));
				$this->_db->setQuery($query);
				$gallery = $this->_db->loadObject();

				if(!empty($gallery)) {
					$query->clear()
						->select('title,fields')
						->from($this->_db->quoteName('#__emundus_setup_gallery_detail_tabs'))
						->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($gallery->id));
					$this->_db->setQuery($query);
					$gallery->tabs = $this->_db->loadObjectList();
				}

				$cache->set($cacheId, $gallery);
			}
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/gallery | Error when try to get gallery by list : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
		}

		return $gallery;
	}

	public function getGalleryById($id)
	{
		try {
			$query = $this->_db->getQuery(true);

			if(empty($gallery)) {
				$query->select('sg.*,fl.label,fl.introduction,fl.params,fl.db_table_name')
					->from($this->_db->quoteName('#__emundus_setup_gallery','sg'))
					->leftJoin($this->_db->quoteName('#__fabrik_lists','fl').' ON '.$this->_db->quoteName('fl.id').' = '.$this->_db->quoteName('sg.list_id'))
					->where($this->_db->quoteName('sg.id') . ' = ' . $this->_db->quote($id));
				$this->_db->setQuery($query);
				$gallery = $this->_db->loadObject();
				
				if(!empty($gallery)) {
					$gallery->status = 1;

					$list_params = json_decode($gallery->params,true);
					if(!empty($list_params['filter-fields'])) {
						$status_filter = array_search($gallery->db_table_name.'.status_raw',$list_params['filter-fields']);

						if($status_filter !== false) {
							$gallery->status = $list_params['filter-value'][$status_filter];
						}
					}

					
					$query->clear()
						->select('title,fields')
						->from($this->_db->quoteName('#__emundus_setup_gallery_detail_tabs'))
						->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($gallery->id));
					$this->_db->setQuery($query);
					$gallery->tabs = $this->_db->loadObjectList();
				}
			}
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/gallery | Error when try to get gallery by list : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
		}

		return $gallery;
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
						$this->_db->quoteName('campaign_id'),
						$this->_db->quoteName('image'),
						$this->_db->quoteName('max'),
					);

					$values = array(
						$this->_db->quote(date('Y-m-d H:i:s')),
						$this->_db->quote($user->id),
						$this->_db->quote($list_id),
						$this->_db->quote($data['campaign_id']),
						$this->_db->quote(0),
						$this->_db->quote(1),
					);

					$query->clear()
						->insert($this->_db->quoteName('#__emundus_setup_gallery'))
						->columns($columns)
						->values(implode(',', $values));
					$this->_db->setQuery($query);
					if($this->_db->execute()) {
						$gallery_id = $this->_db->insertid();

						$columns = array(
							$this->_db->quoteName('parent_id'),
							$this->_db->quoteName('title'),
						);

						$values = array(
							$this->_db->quote($gallery_id),
							$this->_db->quote('Détails du projet'),
						);

						$query->clear()
							->insert($this->_db->quoteName('#__emundus_setup_gallery_detail_tabs'))
							->columns($columns)
							->values(implode(',', $values));
						$this->_db->setQuery($query);
						$this->_db->execute();
					}
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
                'label' => $label,
	            'view_only_template' => 'emundus_vote'
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
                            'db_table_name' => 'jos_emundus_gallery',
	                        'order_dir' => '["ASC"]',
	                        'template' => 'emundus_vote',
	                        'rows_per_page' => 100
                        );
                        $params = array(
                            'group_by_access' => 10,
                            'menu_access_only' => 1,
	                        'show-table-add' => 0,
	                        'show-table-nav' => 0,
	                        'show_displaynum' => 0,
	                        'csv_import_frontend' => 10,
	                        'csv_export_frontend' => 10,
	                        'allow_edit_details' => 10,
	                        'allow_add' => 10,
	                        'allow_delete' => 10,
	                        'allow_drop' => 10,
	                        'isview' => 1
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

	                        $params = [
		                        'menutype' => 'topmenu',
		                        'title' => $label,
		                        'link' => 'index.php?option=com_fabrik&view=list&listid=' . $list_id,
		                        'component_id' => JComponentHelper::getComponent('com_fabrik')->id,
		                        'params' => []
	                        ];
	                        EmundusHelperUpdate::addJoomlaMenu($params);
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

	public function getElements($cid,$lid)
	{
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'cache.php');

		$cache = new EmundusHelperCache('com_emundus');
		$cacheId = 'gallery_' . $cid . '_elements';

		$elements = $cache->get($cacheId);

		if(empty($elements)) {
			$elements = [];
			$query = $this->_db->getQuery(true);

			try {
				require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
				require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'form.php');
				$m_profile = new EmundusModelProfile();
				$m_form    = new EmundusModelForm();

				$pids = $m_profile->getProfilesIDByCampaign([$cid]);

				$query->select('db_table_name')
					->from($this->_db->quoteName('#__fabrik_lists'))
					->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($lid));
				$this->_db->setQuery($query);
				$db_table_name = $this->_db->loadResult();

				foreach ($pids as $pid) {
					$forms = $m_form->getFormsByProfileId($pid);

					foreach ($forms as $form) {
						$elts_by_form = [
							'label' => $form->label,
						];

						$query->clear()
							->select('fe.id,concat(fl.db_table_name,"___",fe.name) as fullname,fe.label,fe.plugin')
							->from($this->_db->quoteName('#__fabrik_elements', 'fe'))
							->leftJoin($this->_db->quoteName('#__fabrik_groups', 'fg') . ' ON ' . $this->_db->quoteName('fg.id') . ' = ' . $this->_db->quoteName('fe.group_id'))
							->leftJoin($this->_db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $this->_db->quoteName('ffg.group_id') . ' = ' . $this->_db->quoteName('fg.id'))
							->leftJoin($this->_db->quoteName('#__fabrik_lists','fl').' ON '.$this->_db->quoteName('fl.form_id').' = '.$this->_db->quoteName('ffg.form_id'))
							->where($this->_db->quoteName('ffg.form_id') . ' = ' . $this->_db->quote($form->id))
							->where($this->_db->quoteName('fe.published') . ' = ' . $this->_db->quote(1))
							->where($this->_db->quoteName('fe.hidden') . ' = ' . $this->_db->quote(0))
							->where('JSON_VALID(`fg`.`params`)')
							->where('JSON_EXTRACT(`fg`.`params`, "$.repeat_group_show_first") <> "-1"');
						$this->_db->setQuery($query);
						$elts_by_form['elements'] = $this->_db->loadObjectList();

						array_map(function ($elt) use ($db_table_name) {
							$elt->label    = JText::_($elt->label);
						}, $elts_by_form['elements']);

						$elements[] = $elts_by_form;
					}
				}

				$cache->set($cacheId, $elements);
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $elements;
	}

	public function getAttachments($cid)
	{
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'cache.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

		$cache = new EmundusHelperCache('com_emundus');
		$cacheId = 'gallery_' . $cid . '_attachments';

		$attachments = $cache->get($cacheId);

		if(empty($attachments)) {
			$query = $this->_db->getQuery(true);

			try {
				$m_profile = new EmundusModelProfile();
				$pids = $m_profile->getProfilesIDByCampaign((array) $cid);

				$query->select('esa.id,esa.value,esa.allowed_types')
					->from($this->_db->quoteName('#__emundus_setup_attachments','esa'))
					->leftJoin($this->_db->quoteName('#__emundus_setup_attachment_profiles','esap').' ON '.$this->_db->quoteName('esap.attachment_id').' = '.$this->_db->quoteName('esa.id'))
					->where($this->_db->quoteName('esap.profile_id').' IN ('.implode(',',$pids).')');
				$this->_db->setQuery($query);
				$attachments = $this->_db->loadObjectList();

				$attachments = array_filter($attachments, function ($attachment) {
					$image_types = ['jpg','jpeg','png','svg','gif'];
					$allowed_types = explode(';',$attachment->allowed_types);
					return !empty(array_intersect($image_types,$allowed_types));
				});
			}
			catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $attachments;
	}

	public function updateAttribute($gid,$attribute,$value)
	{
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'fabrik.php');

		$added = false;

		$query = $this->_db->getQuery(true);
		$gallery = $this->getGalleryById($gid);

		$fabrik_attributes = ['title','subtitle','tags','resume'];

		try {
			$query->update($this->_db->quoteName('#__emundus_setup_gallery'))
				->set($this->_db->quoteName($attribute) . ' = ' . $this->_db->quote($value))
				->where($this->_db->quoteName('id') .' = ' . $gid);
			$this->_db->setQuery($query);

			if(!in_array($attribute,$fabrik_attributes)) {
				$added = $this->_db->execute();
			}
			else {

				if ($this->_db->execute()) {
					$table_to_join = explode('___', $value)[0];
					$elt_name      = explode('___', $value)[1];
					$group_id      = 0;

					$joins         = EmundusHelperFabrik::getFabrikJoins($gallery->list_id);
					$tables_joined = array_keys($joins);

					if (!in_array($table_to_join, $tables_joined)) {
						$query->clear()
							->select('db_table_name')
							->from($this->_db->quoteName('#__fabrik_lists'))
							->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($gallery->list_id));
						$this->_db->setQuery($query);
						$db_table_name = $this->_db->loadResult();

						$joined = EmundusHelperFabrik::createFabrikJoin($db_table_name, $table_to_join, $gallery->list_id);

						if ($joined['status']) {
							$group_id = $joined['group_id'];
						}
					}
					else {
						$group_id = $joins[$table_to_join]->group_id;
					}

					if (!empty($group_id)) {
						$query->clear()
							->select('fe.id')
							->from($this->_db->quoteName('#__fabrik_elements', 'fe'))
							->where($this->_db->quoteName('fe.group_id') . ' = ' . $this->_db->quote($group_id))
							->where($this->_db->quoteName('fe.name') . ' = ' . $this->_db->quote($elt_name));
						$this->_db->setQuery($query);
						$element_id = $this->_db->loadResult();

						if (empty($element_id)) {
							$query->clear()
								->select('fe.*')
								->from($this->_db->quoteName('#__fabrik_elements', 'fe'))
								->leftJoin($this->_db->quoteName('#__fabrik_formgroup', 'ff') . ' ON ' . $this->_db->quoteName('ff.group_id') . ' = ' . $this->_db->quoteName('fe.group_id'))
								->leftJoin($this->_db->quoteName('#__fabrik_lists', 'fl') . ' ON ' . $this->_db->quoteName('fl.form_id') . ' = ' . $this->_db->quoteName('ff.form_id'))
								->where($this->_db->quoteName('fl.db_table_name') . ' = ' . $this->_db->quote($table_to_join))
								->where($this->_db->quoteName('fe.name') . ' = ' . $this->_db->quote($elt_name));
							$this->_db->setQuery($query);
							$element = $this->_db->loadAssoc();

							unset($element['id']);

							$columns = array_map(function ($column) {
								return $this->_db->quoteName($column);
							}, array_keys($element));

							$element['group_id']             = $group_id;
							$element['show_in_list_summary'] = $group_id;
							$values                          = array_map(function ($value) {
								return $this->_db->quote($value);
							}, array_values($element));

							$query->clear()
								->insert($this->_db->quoteName('#__fabrik_elements'))
								->columns($columns)
								->values(implode(',', $values));
							$this->_db->setQuery($query);
							$added = $this->_db->execute();
						}
						else {
							$added = true;
						}
					}
				}
			}
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $added;
	}

	public function updateList($lid,$attribute,$value)
	{
		$updated = false;

		$query = $this->_db->getQuery(true);

		try {
			$query->update($this->_db->quoteName('#__fabrik_lists'))
				->set($this->_db->quoteName($attribute) . ' = ' . $this->_db->quote($value))
				->where($this->_db->quoteName('id') .' = ' . $lid);
			$this->_db->setQuery($query);

			$updated = $this->_db->execute();
		}
		catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}
	}

	//TODO: Create function to add prefilter to a fabrik list on status
	public function editPrefilter($lid,$value)
	{
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'fabrik.php');

		$updated = false;

		try {
			$query = $this->_db->getQuery(true);

			if(empty($gallery)) {
				$query->select('fl.params,fl.db_table_name')
					->from($this->_db->quoteName('#__fabrik_lists','fl'))
					->where($this->_db->quoteName('fl.id') . ' = ' . $this->_db->quote($lid));
				$this->_db->setQuery($query);
				$list = $this->_db->loadObject();

				if(!empty($list)) {
					$params = json_decode($list->params,true);

					if(!empty($params['filter-fields'])) {
						$status_filter = array_search($list->db_table_name.'.status_raw',$params['filter-fields']);

						if($status_filter !== false) {
							$params['filter-value'][$status_filter] = $value;
						}

						$query->clear()
							->update($this->_db->quoteName('#__fabrik_lists'))
							->set($this->_db->quoteName('params') . ' = ' . $this->_db->quote(json_encode($params)))
							->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($lid));
						$this->_db->setQuery($query);
						$updated = $this->_db->execute();
					} else {
						$updated = EmundusHelperFabrik::createPrefilterList($lid,'status_raw',$value);
					}
				}
			}
		}
		catch (Exception $e) {
			JLog::add('component/com_emundus/models/gallery | Error when try to get gallery by list : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.error');
		}

		return $updated;
	}
}
