<?php
/**
 * Messages model used for the new message dialog.
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
jimport('joomla.database.table');

class EmundusonboardModelform extends JModelList {

    var $model_campaign = null;
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->model_campaign = JModelLegacy::getInstance('campaign', 'EmundusonboardModel');
    }

	function getFormCount($filter, $recherche) {

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		if ($filter == 'Unpublish') {
			$filterCount = $db->quoteName('sp.published') . ' = 0';
		} else {
            $filterCount = $db->quoteName('sp.published') . ' = 1';
		}

		if (empty($recherche)) {
			$fullRecherche = 1;
		} else {
			$rechercheLbl = $db->quoteName('sp.label').' LIKE '.$db->quote('%' . $recherche . '%');
			$rechercheResume = $db->quoteName('sp.description').' LIKE '.$db->quote('%' . $recherche . '%');
			$fullRecherche = $rechercheLbl.' OR '.$rechercheResume;
		}

		$filterId = $db->quoteName('sp.id') . ' > 1000';

		$query->select('COUNT(sp.id)')
			->from($db->quoteName('#__emundus_setup_profiles', 'sp'))
			->where($filterId)
			->andWhere($filterCount)
			->andWhere($fullRecherche);

		try {
			$db->setQuery($query);
			return $db->loadResult();
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return 0;
		}
	}

	function getAllForms($filter, $sort, $recherche, $lim, $page) {

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

		$sortDb = 'sp.id ';

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		if ($filter == 'Unpublish') {
			$filterDate = $db->quoteName('sp.published') . ' = 0';
		} else {
            $filterDate = $db->quoteName('sp.published') . ' = 1';
		}

		$filterId = $db->quoteName('sp.id') . ' > 1000';

		if (empty($recherche)) {
			$fullRecherche = 1;
		} else {
			$rechercheLbl = $db->quoteName('sp.label').' LIKE '.$db->quote('%' . $recherche . '%');
			$rechercheResume = $db->quoteName('sp.description').' LIKE '.$db->quote('%' . $recherche . '%');
			$fullRecherche = $rechercheLbl.' OR '.$rechercheResume;
		}

		$query->select([
			    'sp.*',
                'sp.label AS form_label'
            ])
			->from($db->quoteName('#__emundus_setup_profiles', 'sp'))
			->where($filterDate)
			->andWhere($fullRecherche)
			->andWhere($filterId)
			->group($sortDb)
			->order($sortDb . $sort);

		try {
			$db->setQuery($query, $offset, $limit);
			return $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return new stdClass();
		}
	}

	function getFormsUpdated() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $access_profiles = [];
        $query
            ->select('id')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->where($db->quoteName('id') . ' > 1000');
        $db->setQuery($query);
        foreach ($db->loadRowList() as $profile){
            $access_profiles[] = $profile[0];
        }

        $profiles_campaign_associated = [];

	    $campaigns = $this->model_campaign->getAssociatedCampaigns('','','',100,'');
	    $campaigns_id = [];
        $profiles_campaign_associated = [];
	    foreach ($campaigns as $campaign){
	        if($campaign->profile_id != null){
                $profiles_campaign_associated[] = $campaign->profile_id;
            }
            $campaigns_id[] = $campaign->id;
        }

        $query
            ->clear()
            ->select('*')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('id') . ' NOT IN (' . implode(',',$db->quote($campaigns_id)) . ')');
        $db->setQuery($query);
        $campaigns_not_user = $db->loadObjectList();

        foreach ($campaigns_not_user as $campaign){
            if($campaign->profile_id != null){
                if(!in_array($campaign->profile_id,$profiles_campaign_associated)) {
                    array_splice($access_profiles, array_search($campaign->profile_id, $access_profiles), 1);
                }
            }
        }

        sort($access_profiles);

	    return $access_profiles;
    }

    function getAllFormsPublished() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $filterId = $db->quoteName('sp.id') . ' > 1000';

        $query->select([
                'sp.*',
                'sp.label AS form_label'
            ])
            ->from($db->quoteName('#__emundus_setup_profiles', 'sp'))
            ->where($db->quoteName('sp.published') . ' = 1')
            ->andWhere($filterId);

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return new stdClass();
        }
    }

	/**
	 * @param   array $data the row to delete in table.
	 *
	 * @return boolean
	 * Delete form(s) in DB
	 */
	public function deleteForm($data) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

        $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusonboardModel');
        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $modules = [93,102,103,104,168,170];

        if (count($data) > 0) {
			$sp_conditions = array(
				$db->quoteName('sp.id').' IN ('.implode(", ", array_values($data)).')'
			);

			$query->select([
					'sp.id AS spid',
					'mt.id AS mtid',
					'me.id AS meid'
				])
				->from($db->quoteName('#__emundus_setup_profiles', 'sp'))
				->leftJoin($db->quoteName('#__menu_types', 'mt') . ' ON ' . $db->quoteName('mt.menutype') . ' = ' . $db->quoteName('sp.menutype'))
                ->leftJoin($db->quoteName('#__menu', 'me') . ' ON ' . $db->quoteName('me.menutype') . ' = ' . $db->quoteName('mt.menutype'))
				->where($sp_conditions);

			try {
				$db->setQuery($query);
				$results = $db->loadObjectList();
				$spids_arr = array();
				$mtids_arr = array();
				$meids_arr = array();
				$flids_arr = array();
				foreach (array_values($results) as $result){
				    if (!in_array($result->spid,$spids_arr)){
                        $spids_arr[] = $result->spid;
                    }
                    if (!in_array($result->mtid,$mtids_arr)) {
                        $mtids_arr[] = $result->mtid;
                    }
                    if (!in_array($result->meid,$meids_arr)) {
                        $meids_arr[] = $result->meid;
                    }
				}

				$query->clear()
                    ->select('form_id')
                    ->from($db->quoteName('#__emundus_setup_formlist'))
                    ->where($db->quoteName('profile_id') . ' IN (' . implode(", ", array_values($data)) . ')');
                $db->setQuery($query);
                $forms = $db->loadObjectList();

                foreach (array_values($forms) as $form){
                    if (!in_array($form->form_id,$flids_arr)){
                        $flids_arr[] = $form->form_id;
                    }
                }

                $fl_conditions = array($db->quoteName('fl.id') . ' IN (' . implode(", ", array_values($flids_arr)) . ')');

                $query->clear();
                $query->select([
                        'ff.intro AS ffintro',
                        'ff.id AS ffid',
                        'fl.db_table_name AS dbtable',
                        'ffg.id AS ffgid',
                        'fg.id AS fgid',
                        'fe.id AS feid'
                    ])
                    ->from($db->quoteName('#__fabrik_lists', 'fl'))
                    ->leftJoin($db->quoteName('#__fabrik_forms', 'ff') . ' ON ' . $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('ff.id'))
                    ->leftJoin($db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $db->quoteName('ffg.form_id') . ' = ' . $db->quoteName('ff.id'))
                    ->leftJoin($db->quoteName('#__fabrik_groups', 'fg') . ' ON ' . $db->quoteName('fg.id') . ' = ' . $db->quoteName('ffg.group_id'))
                    ->leftJoin($db->quoteName('#__fabrik_elements', 'fe') . ' ON ' . $db->quoteName('fe.group_id') . ' = ' . $db->quoteName('fg.id'))
                    ->where($fl_conditions);

                $db->setQuery($query);
                $results = $db->loadObjectList();
                $ffids_arr = array();
                $dbtables_arr = array();
                $ffgids_arr = array();
                $fgids_arr = array();
                $feids_arr = array();
                $ffintros_arr = array();
                foreach (array_values($results) as $result) {
                    if (!in_array($result->ffid, $ffids_arr)) {
                        $ffids_arr[] = $result->ffid;
                    }
                    if (!in_array($result->dbtable, $dbtables_arr)) {
                        $dbtables_arr[] = $result->dbtable;
                    }
                    if (!in_array($result->ffgid, $ffgids_arr)) {
                        $ffgids_arr[] = $result->ffgid;
                    }
                    if (!in_array($result->fgid, $fgids_arr)) {
                        $fgids_arr[] = $result->fgid;
                    }
                    if (!in_array($result->feid, $feids_arr) && $result->feid != null) {
                        $feids_arr[] = $result->feid;
                    }
                    if (!in_array($result->ffintro, $ffintros_arr)) {
                        $ffintros_arr[] = $result->ffintro;
                    }
                }

				try {
                    // DISSOCIATE CAMPAIGN WITH THIS PROFILE ID
                    $conditions = array($db->quoteName('profile_id') . ' IN (' . implode(", ", array_values($spids_arr)) . ')');

                    $query->clear()
                        ->update($db->quoteName('#__emundus_setup_campaigns'))
                        ->set($db->quoteName('profile_id') . ' = NULL')
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();
                    //

					// DELETE SETUP PROFILE
					$conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($spids_arr)) . ')');

					$query->clear()
						->delete($db->quoteName('#__emundus_setup_profiles'))
						->where($conditions);

					$db->setQuery($query);
					$db->execute();

					// DELETE MENU TYPE
					$conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($mtids_arr)) . ')');

					$query->clear()
						->delete($db->quoteName('#__menu_types'))
						->where($conditions);

					$db->setQuery($query);
					$db->execute();

                    // DELETE MENUS
                    $conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($meids_arr)) . ')');

                    $query->clear()
                        ->select('*')
                        ->from($db->quoteName('#__menu'))
                        ->where($conditions);
                    $db->setQuery($query);
                    $menus = $db->loadObjectList();

                    foreach ($menus as $menu) {
                        $falang->deleteFalang($menu->id,'menu','title');

                        foreach ($modules as $module){
                            $query
                                ->clear()
                                ->delete($db->quoteName('#__modules_menu'))
                                ->where($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                                ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }

                    $query->clear()
                        ->delete($db->quoteName('#__menu'))
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();

                    // DELETE FABRIK FORMS
                    $conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($ffids_arr)) . ')');

                    $query->clear()
                        ->select(['label AS label','intro AS intro'])
                        ->from($db->quoteName('#__fabrik_forms'))
                        ->where($conditions);
                    $db->setQuery($query);
                    $forms_texts = $db->loadObjectList();

                    foreach ($forms_texts as $form_text){
                        $formbuilder->deleteTranslation($form_text->intro);
                        $formbuilder->deleteTranslation($form_text->label);
                    }

                    $query->clear()
                        ->delete($db->quoteName('#__fabrik_forms'))
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();

                    // DELETE FABRIK LISTS
                    foreach ($dbtables_arr as $dbtablearr) {
                        $query = "DROP TABLE " . $dbtablearr;
                        $db->setQuery($query);
                        $db->execute();
                    }

                    $query = $db->getQuery(true);

                    $conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($flids_arr)) . ')');

                    $query->delete($db->quoteName('#__fabrik_lists'))
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();

                    // DELETE FORMLIST
                    $conditions = array($db->quoteName('profile_id') . ' IN (' . implode(", ", array_values($data)) . ')');

                    $query->clear()
                        ->delete($db->quoteName('#__emundus_setup_formlist'))
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();

                    // DELETE FABRIK FORM GROUP
                    $conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($ffgids_arr)) . ')');

                    $query->clear()
                        ->delete($db->quoteName('#__fabrik_formgroup'))
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();

                    // DELETE FABRIK GROUP
                    $conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($fgids_arr)) . ')');

                    $query->clear()
                        ->select(['label AS label'])
                        ->from($db->quoteName('#__fabrik_groups'))
                        ->where($conditions);
                    $db->setQuery($query);
                    $groups_texts = $db->loadObjectList();

                    foreach ($groups_texts as $group_text) {
                        $formbuilder->deleteTranslation($group_text->label);
                    }

                    $query->clear()
                        ->delete($db->quoteName('#__fabrik_groups'))
                        ->where($conditions);

                    $db->setQuery($query);
                    $db->execute();

                    // DELETE FABRIK ELEMENTS
                    $conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($feids_arr)) . ')');

                    $query->clear()
                        ->select(['label AS label'])
                        ->from($db->quoteName('#__fabrik_elements'))
                        ->where($conditions);
                    $db->setQuery($query);
                    $elts_texts = $db->loadObjectList();

                    foreach ($elts_texts as $elt_text) {
                        $formbuilder->deleteTranslation($elt_text->label);
                    }

                    $query->clear()
                        ->delete($db->quoteName('#__fabrik_elements'))
                        ->where($conditions);

                    $db->setQuery($query);
                    return $db->execute();

				} catch (Exception $e) {
					JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
					return $e->getMessage();
				}
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
				return $e->getMessage();
			}
		} else {
			return false;
		}
	}


	public function unpublishForm($data) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

			try {
				$fields = array($db->quoteName('published') . ' = 0');
				$se_conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

				$query->update($db->quoteName('#__emundus_setup_profiles'))
					->set($fields)
					->where($se_conditions);

				$db->setQuery($query);
				return $db->execute();
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
				return $e->getMessage();
			}
		} else {
			return false;
		}
	}


	public function publishForm($data) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = htmlspecialchars($data[$key]);
			}

			try {
				$fields = array($db->quoteName('published') . ' = 1');
				$se_conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

				$query->update($db->quoteName('#__emundus_setup_profiles'))
					->set($fields)
					->where($se_conditions);

				$db->setQuery($query);
				return $db->execute();
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
				return $e->getMessage();
			}
		} else {
			return false;
		}
	}


	public function duplicateForm($data) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		if (!is_array($data)) {
		    $data = array($data);
        }

        if (count($data) > 0) {
            try {
                foreach ($data as $pid) {
                    // Get profile
                    $query->select('*')
                        ->from($db->quoteName('#__emundus_setup_profiles'))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($pid));

                    $db->setQuery($query);
                    $oldprofile = $db->loadObject();

                    // Create a new profile
                    $query->clear()
                        ->insert('#__emundus_setup_profiles')
                        ->set($db->quoteName('label') . ' = ' . $db->quote($oldprofile->label))
                        ->set($db->quoteName('published') . ' = 1')
                        ->set($db->quoteName('menutype') . ' = ' . $db->quote($oldprofile->menutype))
                        ->set($db->quoteName('acl_aro_groups') . ' = ' . $db->quote($oldprofile->acl_aro_groups))
                        ->set($db->quoteName('status') . ' = ' . $db->quote($oldprofile->status));

                    $db->setQuery($query);
                    $db->execute();
                    $newprofile = $db->insertid();

                    $newmenutype = 'menu-profile' . $newprofile;

                    $this->createMenuType($newmenutype,$oldprofile->label);

                    $query->clear()
                        ->update('#__emundus_setup_profiles')
                        ->set($db->quoteName('menutype') . ' = ' . $db->quote($newmenutype))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($newprofile));
                    $db->setQuery($query);
                    $db->execute();

                    $query->clear()
                        ->select('*')
                        ->from('#__menu')
                        ->where($db->quoteName('alias') . ' = ' . $db->quote('applicationform'));

                    $db->setQuery($query);
                    $menu_model = $db->loadObject();

                    $query->clear();
                    $query->insert($db->quoteName('#__menu'));
                    foreach ($menu_model as $key => $val) {
                        if ($key != 'id' && $key != 'menutype' && $key != 'title' && $key != 'alias' && $key != 'path') {
                            $query->set($key . ' = ' . $db->quote($val));
                        } elseif ($key == 'menutype' || $key == 'path') {
                            $query->set($key . ' = ' . $db->quote($newmenutype));
                        } elseif ($key == 'title') {
                            $query->set($key . ' = ' . $db->quote($oldprofile->label));
                        } elseif ($key == 'alias') {
                            $query->set($key . ' = ' . $db->quote(str_replace(array(' '),'-',strtolower($oldprofile->label . '-duplicate'))));
                        }
                    }
                    $db->setQuery($query);
                    $db->execute();

                    // Get fabrik_lists
                    $query->clear()
                        ->select('form_id')
                        ->from($db->quoteName('#__emundus_setup_formlist'))
                        ->where($db->quoteName('profile_id') . ' = ' . $db->quote($pid));

                    $db->setQuery($query);
                    $lists = $db->loadObjectList();
                    $listsid_arr = array();
                    foreach (array_values($lists) as $list) {
                        if (!in_array($list->form_id, $listsid_arr)) {
                            $listsid_arr[] = $list->form_id;
                        }
                    }

                    // Get forms
                    $query->clear()
                        ->select('form_id')
                        ->from($db->quoteName('#__fabrik_lists'))
                        ->where($db->quoteName('id') . ' IN (' . implode(", ", array_values($listsid_arr)) . ')');

                    $db->setQuery($query);
                    $forms = $db->loadObjectList();
                    $formsid_arr = array();
                    foreach (array_values($forms) as $form) {
                        if (!in_array($form->form_id, $formsid_arr)) {
                            $formsid_arr[] = $form->form_id;
                        }
                    }

                    $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusonboardModel');

                    foreach ($formsid_arr as $formid) {
                        $query->clear()
                            ->select('label', 'intro')
                            ->from($db->quoteName('#__fabrik_forms'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($formid));
                        $db->setQuery($query);
                        $form = $db->loadObject();

                        $label = array(
                            'fr' => $formbuilder->getTranslationFr($form->label),
                            'en' => $formbuilder->getTranslationEn($form->label),
                        );

                        $intro = array(
                            'fr' => $formbuilder->getTranslationFr($form->intro),
                            'en' => $formbuilder->getTranslationEn($form->intro),
                        );

                        $formbuilder->createMenuFromTemplate($label, $intro, $formid, $newprofile);
                    }
                }

                return $newprofile;
            } catch (Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    return $e->getMessage();
                }
        } else {
            return false;
        }
	}


	public function getFormById($id) {

		if (empty($id)) {
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(['sp.*', 'sp.label AS form_label'])
			->from($db->quoteName('#__emundus_setup_profiles','sp'))
			->where($db->quoteName('sp.id') . ' = ' . $id);

		$db->setQuery($query);

		try {
			$db->setQuery($query);
			return $db->loadObject();
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return false;
		}
	}


	public function createProfile($data, $userId, $userName) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

        $settings = JModelLegacy::getInstance('settings', 'EmundusonboardModel');

        $modules = [93,102,168,170];

        $query->select('id')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->order('id DESC');
        $db->setQuery($query);
        $lastprofile = $db->loadObjectList()[0];

		if (!empty($data)) {
            // Insert columns.
            $columns = array(
                'label',
                'description',
                'published',
                'schoolyear',
                'candidature_start',
                'candidature_end',
                'menutype',
                'reference_letter',
                'acl_aro_groups',
                'is_evaluator',
                'evaluation_start',
                'evaluation_end',
                'evaluation',
                'status',
                'class');

            // Insert values.
            $values = array(
                $data['label'],
                $data['description'],
                $data['published'],
                null,
                null,
                null,
                'menu-profile',
                null,
                2,
                0,
                null,
                null,
                null,
                1,
                null
            );

            if ($lastprofile->id == '999' || $lastprofile->id == '1000') {
                array_unshift($columns , 'id');
                array_unshift($values , 1001);
            }
            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_profiles'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $db->Quote($values)));

			try {
				$db->setQuery($query);
				$db->execute();
				$newprofile = $db->insertid();

                $this->createMenuType('menu-profile' . $newprofile,$data['label']);

                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_profiles'))
                    ->set($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile' . $newprofile))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($newprofile));
                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->select('*')
                    ->from('#__menu')
                    ->where($db->quoteName('alias') . ' = ' . $db->quote('applicationform'));

                $db->setQuery($query);
                $menu_model = $db->loadObject();

                $query->clear();
                $query->insert($db->quoteName('#__menu'));
                foreach ($menu_model as $key => $val) {
                    if ($key != 'id' && $key != 'menutype' && $key != 'title' && $key != 'alias' && $key != 'path' && $key != 'rgt' && $key != 'lft') {
                        $query->set($key . ' = ' . $db->quote($val));
                    } elseif ($key == 'menutype' || $key == 'path') {
                        $query->set($key . ' = ' . $db->quote('menu-profile' . $newprofile));
                    } elseif ($key == 'title') {
                        $query->set($key . ' = ' . $db->quote($data['label']));
                    } elseif ($key == 'alias') {
                        $query->set($key . ' = ' . $db->quote(str_replace(array(' '),'-',strtolower($data['label']))));
                    } elseif ($key == 'rgt') {
                        $query->set($key . ' = ' . $db->quote(1));
                    } elseif ($key == 'lft') {
                        $query->set($key . ' = ' . $db->quote(0));
                    }
                }
                $db->setQuery($query);
                $db->execute();

				// Create a first page
                $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusonboardModel');
                $label = array(
                    'fr' => 'Ma première page',
                    'en' => 'My first page'
                );
                $intro = array(
                    'fr' => 'Décrivez votre page de formulaire avec une introduction',
                    'en' => 'Describe your form page with an introduction'
                );
                $formbuilder->createMenu($label, $intro, $newprofile, 'false');

                // Create submittion page
                $query->clear()
                    ->select('*')
                    ->from('#__menu')
                    ->where($db->quoteName('alias') . ' = ' . $db->quote('submitting-application-forms'));

                $db->setQuery($query);
                $submit_model = $db->loadObject();

                $query->clear()
	                ->insert($db->quoteName('#__menu'));
                foreach ($submit_model as $key => $val) {
                    if ($key != 'id' && $key != 'menutype' && $key != 'alias' && $key != 'path') {
                        $query->set($key . ' = ' . $db->quote($val));
                    } elseif ($key == 'menutype') {
                        $query->set($key . ' = ' . $db->quote('menu-profile' . $newprofile));
                    } elseif ($key == 'alias' || $key == 'path') {
                        $query->set($key . ' = ' . $db->quote($val . '-' . $newprofile));
                    }
                }
                $db->setQuery($query);
                $db->execute();
                $submittion_id = $db->insertid();

                // Affect modules to this menu
                foreach ($modules as $module) {
                    $query->clear()
                        ->insert($db->quoteName('#__modules_menu'))
                        ->set($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                        ->set($db->quoteName('menuid') . ' = ' . $db->quote($submittion_id));
                    $db->setQuery($query);
                    $db->execute();
                }

                $user = JFactory::getUser();
                $settings->onAfterCreateForm($user->id);

				return $newprofile;
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
				return $e->getMessage();
			}
		} else {
			return false;
		}
	}


	public function createMenuType($menutype, $title) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Insert columns.
		$columns = array(
			'asset_id',
			'menutype',
			'title',
			'description',
			'client_id'
		);

		// Insert values.
		$values = array(
			251,
			$menutype,
			$title,
			'',
			0
		);

		$query->insert($db->quoteName('#__menu_types'))
			->columns($db->quoteName($columns))
			->values(implode(',', $db->Quote($values)));

		try {
			$db->setQuery($query);
			return $db->execute();
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return $e->getMessage();
		}
	}


	public function createMenu($menu, $menutype) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Insert columns.
		$columns = array(
			'menutype',
			'title',
			'alias',
			'note',
			'path',
			'link',
			'type',
			'published',
			'parent_id',
			'level',
			'component_id',
			'checked_out',
			'checked_out_time',
			'browserNav',
			'access',
			'img',
			'template_style_id',
			'params',
			'lft',
			'rgt',
			'home',
			'language',
			'client_id',
		);

		// Insert values.
		$values = array(
			$menutype,
			$menu['title'],
			$menu['alias'],
			$menu['note'],
			$menu['path'],
			$menu['link'],
			$menu['type'],
			$menu['published'],
			$menu['parent_id'],
			$menu['level'],
			$menu['component_id'],
			$menu['checked_out'],
			$menu['checked_out_time'],
			$menu['browserNav'],
			$menu['access'],
			$menu['img'],
			$menu['template_style_id'],
			$menu['params'],
			$menu['lft'],
			$menu['rgt'],
			$menu['home'],
			$menu['language'],
			$menu['client_id'],
		);

		$query->insert($db->quoteName('#__menu'))
			->columns($db->quoteName($columns))
			->values(implode(',', $db->Quote($values)));

		try {
			$db->setQuery($query);
			return $db->execute();
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return $e->getMessage();
		}
	}

	/**
	 * @param   int $id the form to update
	 * @param   array $data the row to add in table.
	 *
	 * @return boolean
	 * Update form in DB
	 */
	public function updateForm($id, $data) {
		$db = $this->getDbo();
		$query_pid = $db->getQuery(true);

		if (!empty($data)) {
			$fields = [];

			foreach ($data as $key => $val) {
				$insert = $db->quoteName(htmlspecialchars($key)) . ' = ' . $db->quote(htmlspecialchars($val));
				$fields[] = $insert;
			}

			$query_pid->update($db->quoteName('#__emundus_setup_profiles'))
				->set($fields)
				->where($db->quoteName('id') . ' = ' . $db->quote($id));

			try {
				$db->setQuery($query_pid);
				return $db->execute();
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
				return $e->getMessage();
			}
		} else {
			return false;
		}
	}


	public function getAllDocuments($prid, $cid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select([
				'sap.attachment_id AS id',
				'sap.ordering',
				'sap.mandatory AS need',
				'sa.value',
                'sa.allowed_types'
			])
			->from($db->quoteName('#__emundus_setup_attachment_profiles', 'sap'))
			->leftJoin($db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON ' . $db->quoteName('sa.id') . ' = ' . $db->quoteName('sap.attachment_id'))
			->order($db->quoteName('sap.ordering'))
			->where($db->quoteName('sap.published') . ' = 1')
			->where($db->quoteName('sap.profile_id') . ' = ' . $prid)
			->andWhere($db->quoteName('sap.campaign_id') . ' = ' . $cid);

		try {
			$db->setQuery($query);
			return $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return false;
		}
	}


	public function getUnDocuments() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__emundus_setup_attachments'))
			->order($db->quoteName('ordering'));

		$db->setQuery($query);

		try {
			$db->setQuery($query);
			$undocuments = $db->loadObjectList();

			foreach ($undocuments as $undocument){
			    if(strpos($undocument->lbl, '_em') === 0){
                    $undocument->can_be_deleted = true;
                } else {
                    $undocument->can_be_deleted = false;
                }
            }

			return $undocuments;
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return false;
		}
	}

	public function updateDocuments($data, $prid, $cid) {
		$db = $this->getDbo();

		$allDocuments = EmundusonboardModelform::getAllDocuments($prid, $cid);
		$allDocumentsIds = [];

		foreach ($allDocuments as $documents) {
			array_push($allDocumentsIds, $documents->id);
		}

		if (!empty($data)) {
			$values = [];

			foreach ($data as $vals) {
				foreach ($vals as $key => $val) {
					if ($key == 'id') {
						$did = $val;

						if (in_array($val, $allDocumentsIds)) {
							unset($allDocumentsIds[array_search($val, $allDocumentsIds)]);
						}
					} elseif ($key == 'ordering') {
						$ordering = $val;
					} elseif ($key == 'need') {
						$need = $val;
					}
				}

				array_push($values, '(' . $did . ',' . $cid . ',' . $prid . ',1,' . $ordering . ',' . $need . ', 1)');
			}

			$query =
				'INSERT INTO jos_emundus_setup_attachment_profiles 
            (attachment_id, campaign_id, profile_id, displayed, ordering, mandatory, published)
            VALUES 
            ' .
				implode(',', $values) .
				'
            ON DUPLICATE KEY UPDATE 
            campaign_id = VALUES(campaign_id),
            profile_id = VALUES(profile_id),
            displayed = VALUES(displayed),
            ordering = VALUES(ordering),
            mandatory = VALUES(mandatory),
            published = VALUES(published)
            ;';

			try {
				$db->setQuery($query);
				$db->execute();

				// Create checklist menu if documents are asked
                $query = $db->getQuery(true);
                $query->clear()
                    ->select('*')
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('alias') . ' = ' . $db->quote('checklist-' . $prid));
                $db->setQuery($query);
                $checklist = $db->loadObject();

                if ($checklist == null) {
                    $this->addChecklistMenu($prid);
                }
            } catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
				return $e->getMessage();
			}

			$this->deleteRemainingDocuments(
				$prid,
				$allDocumentsIds
			);

			return true;
		} else {
			return false;
		}
	}

    public function deleteRemainingDocuments($prid, $allDocumentsIds) {
        $db = $this->getDbo();

        $values = [];

        foreach ($allDocumentsIds as $document) {
            array_push($values, '(' . $document . ',' . $prid . ',0,0)');
        }

        $query =
            'INSERT INTO jos_emundus_setup_attachment_profiles 
        (attachment_id, profile_id, displayed, published)
        VALUES 
        ' .
            implode(',', $values) .
            '
        ON DUPLICATE KEY UPDATE 
        displayed = VALUES(displayed),
        published = VALUES(published),
        profile_id = VALUES(profile_id)
        ;';

        try {
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }


    public function removeDocument($did,$prid,$cid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_setup_attachment_profiles'))
            ->where($db->quoteName('attachment_id') . ' = ' . $db->quote($did))
            ->andWhere($db->quoteName('campaign_id') . ' = ' . $db->quote($cid))
            ->andWhere($db->quoteName('profile_id') . ' = ' . $db->quote($prid));
        try {
            $db->setQuery($query);
            $db->execute();

            $documents_campaign = EmundusonboardModelform::getAllDocuments($prid, $cid);

            if (empty($documents_campaign)) {
                $this->removeChecklistMenu($prid);
            }

            return true;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }

    public function deleteDocument($did){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        try {
            $falang->deleteFalang($did,'emundus_setup_attachments','value');
            $falang->deleteFalang($did,'emundus_setup_attachments','description');

            $query->clear()
                ->delete($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('attachment_id') . ' = ' . $db->quote($did));

            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->delete($db->quoteName('#__emundus_setup_attachments'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($did));

            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }

    public function addChecklistMenu($prid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $modules = [93,102,103,104,168,170];

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('alias') . ' = ' . $db->quote('checklist'));
        $db->setQuery($query);
        $checklist_model = $db->loadObject();

        $query->clear()
            ->insert($db->quoteName('#__menu'));

        foreach ($checklist_model as $key => $row) {
            if ($key != 'id' && $key != 'alias' && $key != 'path' && $key != 'menutype') {
                $query->set($key . ' = ' . $db->quote($row));
            } elseif ($key == 'alias' || $key == 'path') {
                $query->set($key . ' = ' . $db->quote('checklist-' . $prid));
            } elseif ($key == 'menutype') {
                $query->set($key . ' = ' . $db->quote('menu-profile' . $prid));
            }
        }
        $db->setQuery($query);
        $db->execute();
        $newmenuid = $db->insertid();

        // Affect modules to this menu
        foreach ($modules as $module) {
            $query->clear()
                ->insert($db->quoteName('#__modules_menu'))
                ->set($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                ->set($db->quoteName('menuid') . ' = ' . $db->quote($newmenuid));
            $db->setQuery($query);
            $db->execute();
        }

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile' . $prid));
        $db->setQuery($query);
        $menus = $db->loadObjectList();

        foreach ($menus as $menu){
            $query->clear()
                ->insert($db->quoteName('#__modules_menu'))
                ->set($db->quoteName('moduleid') . ' = 103')
                ->set($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->insert($db->quoteName('#__modules_menu'))
                ->set($db->quoteName('moduleid') . ' = 104')
                ->set($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
            $db->setQuery($query);
            $db->execute();
        }

        return $newmenuid;
    }

    public function removeChecklistMenu($prid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $modules = [93,102,103,104,168,170];

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('alias') . ' = ' . $db->quote('checklist-' . $prid));
        $db->setQuery($query);
        $checklist = $db->loadObject();

        foreach ($modules as $module) {
            $query->clear()
                ->delete($db->quoteName('#__modules_menu'))
                ->where($db->quoteName('moduleid') . ' = ' . $db->quote($module))
                ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($checklist->id));
            $db->setQuery($query);
            $db->execute();
        }

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile' . $prid));
        $db->setQuery($query);
        $menus = $db->loadObjectList();

        foreach ($menus as $menu) {
            $query->clear()
                ->delete($db->quoteName('#__modules_menu'))
                ->where($db->quoteName('moduleid') . ' IN (103,104)')
                ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
            $db->setQuery($query);
            $db->execute();
        }

        $query->clear()
            ->delete($db->quoteName('#__menu'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($checklist->id));
        $db->setQuery($query);
        return $db->execute();
    }


	/**
	 * @param $profile_id
	 *
	 * @return array|boolean
	 * Retrieve menus links by profile_id
	 */
	public function getFormsByProfileId($profile_id) {

		if (empty($profile_id)) {
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select(['menu.link' , 'menu.rgt'])
			->from ($db->quoteName('#__menu', 'menu'))
			->leftJoin($db->quoteName('#__menu_types', 'mt').' ON '.$db->quoteName('mt.menutype').' = '.$db->quoteName('menu.menutype'))
			->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp').' ON '.$db->quoteName('sp.menutype').' = '.$db->quoteName('mt.menutype'))
			->where($db->quoteName('sp.id') . ' = '.$profile_id)
			->where($db->quoteName('menu.parent_id') . ' != 1')
			->group('menu.rgt')
			->order('menu.rgt ASC');


		try {
			$db->setQuery($query);
			return $db->loadObjectList();
		} catch(Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return false;
		}
	}

    /**
     * @param $profile_id
     * @return bool|mixed|null
     * Retrieve Form page name by profile_id
     */
	public function getProfileLabelByProfileId($profile_id) {

		if (empty($profile_id)) {
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('stpr.label')
			->from ($db->quoteName('#__emundus_setup_profiles', 'stpr'))
			->where($db->quoteName('stpr.id') . ' = '.$profile_id) ;
		try {
			$db->setQuery($query);
			return $db->loadObject();
		} catch(Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
			return false;
		}
	}

	public function getFilesByProfileId($profile_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $files = 0;

        $query->select('id')
            ->from ($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' = ' . $profile_id);
        try {
            $db->setQuery($query);
            $campaigns = $db->loadObjectList();

            foreach ($campaigns as $campaign) {
                $query->clear()
                    ->select('COUNT(*)')
                    ->from ($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('campaign_id') . ' = ' . $campaign->id);

                $db->setQuery($query);
                $files += $db->loadResult();
            }

            return $files;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function getAssociatedCampaign($profile_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from ($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' = ' . $db->quote($profile_id));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function affectCampaignsToForm($prid, $campaigns) {
        foreach ($campaigns as $campaign) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('year')
                ->from($db->quoteName('#__emundus_setup_campaigns'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
            $db->setQuery($query);
            $schoolyear = $db->loadResult();

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_campaigns'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($prid))
                ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));

            try {
                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_teaching_unity'))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote($prid))
                    ->where($db->quoteName('schoolyear') . ' = ' . $db->quote($schoolyear));

                $db->setQuery($query);
                $db->execute();

            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
        }

        return true;
    }
}
