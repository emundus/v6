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

class EmundusModelForm extends JModelList {

    var $model_campaign = null;
    var $model_menus = null;
    public function __construct($config = array()) {
        parent::__construct($config);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        $this->model_campaign = new EmundusModelCampaign;

        // Get MenuItemModel.
        JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models/', 'MenusModel');
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables/');
        $this->model_menus = JModelLegacy::getInstance('Item', 'MenusModel');

	    JLog::addLogger(['text_file' => 'com_emundus.form.php'], JLog::ALL, array('com_emundus.form'));
    }

    /**
     * @param String $filter
     * @param String $sort
     * @param String $recherche
     * @param Int $lim
     * @param Int $page
     * @return array|stdClass
     */
    function getAllForms(String $filter = '', String $sort = '', String $recherche = '', Int $lim = 0, Int $page = 0) : Array {
        $data = ['datas' => [], 'count' => 0];
		require_once (JPATH_ROOT . '/components/com_emundus/models/users.php');

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Build filter / limit / pagination part of the query
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

        if ($filter == 'Unpublish') {
            $filterDate = $db->quoteName('sp.status') . ' = 0';
        } else {
            $filterDate = $db->quoteName('sp.status') . ' = 1';
        }

        $filterId = $db->quoteName('sp.published') . ' = 1';
		$fullRecherche =empty($recherche) ? 1 : $db->quoteName('sp.label').' LIKE '.$db->quote('%' . $recherche . '%');

        $m_user = new EmundusModelUsers();
        $allowed_programs = $m_user->getUserGroupsProgramme(JFactory::getUser()->id);

        // GET ALL PROFILES THAT ARE NOT LINKED TO A CAMPAIGN
        $other_profile_query = $db->getQuery(true);
		$other_profile_full_recherche = empty($recherche) ? 1 : $db->quoteName('esp.label').' LIKE '.$db->quote('%' . $recherche . '%');

        $other_profile_query->select(['esp.*', 'esp.label AS form_label'])
            ->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esc.profile_id').' = '.$db->quoteName('esp.id'))
            ->where($db->quoteName('esc.profile_id') . ' IS NULL')
            ->andWhere($db->quoteName('esp.published') . ' = 1')
            ->andWhere($other_profile_full_recherche)
            ->andWhere($db->quoteName('esp.menutype') . ' IS NOT NULL');

        // Now we need to put the query together and get the profiles
        $query->select(['sp.*', 'sp.label AS form_label'])
            ->from($db->quoteName('#__emundus_setup_profiles', 'sp'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esc.profile_id').' = '.$db->quoteName('sp.id'))
            ->where($filterDate)
            ->andWhere($fullRecherche)
            ->andWhere($filterId)
            ->andWhere($db->quoteName('esc.training') . ' IN (' . implode(',', $db->quote($allowed_programs)). ')')
            ->group($db->quoteName('id'))
            ->order('id ' . $sort)
            ->union($other_profile_query);

        try {
            $db->setQuery($query);
	        $data['count'] = sizeof($db->loadObjectList());
	        $db->setQuery($query, $offset, $limit);
	        $data['datas'] = $db->loadObjectList();

	        if (!empty($data['datas'])) {
				$path_to_file = basename(__FILE__) . '/../language/overrides/';
				$path_to_files = array();
				$Content_Folder = array();
				$languages = JLanguageHelper::getLanguages();
				if (!empty($languages)) {
					foreach ($languages as $language) {
						$path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
						$Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
					}

					require_once (JPATH_ROOT . '/components/com_emundus/models/formbuilder.php');
					$formbuilder = new EmundusModelFormbuilder;
					foreach ($data['datas'] as $key => $form) {
						$label= [];
						foreach ($languages as $language) {
							$label[$language->sef] = $formbuilder->getTranslation($form->label,$language->lang_code) ?: $form->label;
						}
						$data['datas'][$key]->label = $label;
					}
				}
			}
		} catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Cannot getting the list of forms : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }

		return $data;
    }

    /**
     * TODO: Add filters / recherche etc./.. At the moment, it's not working
     * @param $filter
     * @param $sort
     * @param $recherche
     * @param $lim
     * @param $page
     * @return array
     */
    function getAllGrilleEval($filter, $sort, $recherche, $lim, $page) : array{
        $data = ['datas' => [], 'count' => 0];
		$db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            // We need to get the list of fabrik forms that are linked to the jos_emundus_evaluations table
            $query->clear();
            $query
                ->select([$db->quoteName('ff.id'), $db->quoteName('ff.label'), '"grilleEval" AS type'])
                ->from($db->quoteName('#__fabrik_forms', 'ff'))
                ->leftJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('ff.id'))
                ->where($db->quoteName('fl.db_table_name').' = '.$db->quote('jos_emundus_evaluations' ));
            $db->setQuery($query);

            $evaluation_forms = $db->loadObjectList();

	        if (!empty($evaluation_forms)) {
		        require_once (JPATH_ROOT.'/components/com_emundus/models/formbuilder.php');
		        $m_form_builder = new EmundusModelFormbuilder();

		        $path_to_file = basename(__FILE__) . '/../language/overrides/';
		        $path_to_files = array();
		        $Content_Folder = array();
		        $languages = JLanguageHelper::getLanguages();
		        foreach ($languages as $language) {
			        $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
			        $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
		        }

		        foreach ( $evaluation_forms as $evaluation_form ) {
			        $label= [];
			        foreach ($languages as $language) {
				        $label[$language->sef] = $m_form_builder->getTranslation($evaluation_form->label,$language->lang_code) ?: $evaluation_form->label;
			        }
			        $evaluation_form->label=$label;
		        }
	        }

			$data['datas'] = $evaluation_forms;
			$data['count'] = sizeof($evaluation_forms);
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Cannot getting the list of forms : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }

		return $data;
    }

    function getAllFormsPublished() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $filterId = $db->quoteName('sp.published') . ' = 1';

        $query->select([
            'sp.*',
            'sp.label AS form_label'
        ])
            ->from($db->quoteName('#__emundus_setup_profiles', 'sp'))
            ->where($db->quoteName('sp.status') . ' = 1')
            ->andWhere($filterId);

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Cannot getting the published forms : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function deleteForm($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $formbuilder = new EmundusModelFormbuilder;
        $falang = new EmundusModelFalang;

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $modules = $eMConfig->get('form_builder_page_creation_modules', [93,102,103,104,168,170]);

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
                    JLog::add('component/com_emundus/models/form | Error when try to delete forms : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                    return false;
                }
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/form | Error when try to delete forms : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
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
                $fields = array($db->quoteName('status') . ' = 0');
                $se_conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

                $query->update($db->quoteName('#__emundus_setup_profiles'))
                    ->set($fields)
                    ->where($se_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/form | Error when unpublish forms : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
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
                $fields = array($db->quoteName('status') . ' = 1');
                $se_conditions = array($db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')');

                $query->update($db->quoteName('#__emundus_setup_profiles'))
                    ->set($fields)
                    ->where($se_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/form | Error when publish forms : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }


    public function duplicateForm($data) {
		$duplicated = false;
        if (!is_array($data)) {
            $data = array($data);
        }

        if (!empty($data)) {
	        $db = $this->getDbo();
	        $query = $db->getQuery(true);

	        // Prepare languages
	        $path_to_file = basename(__FILE__) . '/../language/overrides/';
	        $path_to_files = array();
	        $Content_Folder = array();

	        $languages = JLanguageHelper::getLanguages();
	        foreach ($languages as $language) {
		        $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';

                if (file_exists($path_to_files[$language->sef])) {
                    $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
                } else {
                    $Content_Folder[$language->sef] = '';
                }
	        }

	        require_once (JPATH_SITE. '/components/com_emundus/models/formbuilder.php');
	        $formbuilder = new EmundusModelFormbuilder();

	        try {
                foreach ($data as $pid) {
                    // Get profile
                    $query->clear()
	                    ->select('*')
                        ->from($db->quoteName('#__emundus_setup_profiles'))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
                    $db->setQuery($query);
                    $oldprofile = $db->loadObject();

					if (!empty($oldprofile)) {
						// Create a new profile
						$query->clear()
							->insert('#__emundus_setup_profiles')
							->set($db->quoteName('label') . ' = ' . $db->quote($oldprofile->label. ' - Copy'))
							->set($db->quoteName('published') . ' = 1')
							->set($db->quoteName('menutype') . ' = ' . $db->quote($oldprofile->menutype))
							->set($db->quoteName('acl_aro_groups') . ' = ' . $db->quote($oldprofile->acl_aro_groups))
							->set($db->quoteName('status') . ' = ' . $db->quote($oldprofile->status));
						$db->setQuery($query);
						$db->execute();
						$newprofile = $db->insertid();

						if (!empty($newprofile)) {
							$newmenutype = 'menu-profile' . $newprofile;
							$newmenutype = $this->createMenuType($newmenutype,$oldprofile->label . ' - Copy');
							if (empty($newmenutype)) {
								JLog::add('Failed to create new menu from profile ' . $newprofile, JLog::WARNING, 'com_emundus.error');
								return false;
							}

							$query->clear()
								->update('#__emundus_setup_profiles')
								->set($db->quoteName('menutype') . ' = ' . $db->quote($newmenutype))
								->where($db->quoteName('id') . ' = ' . $db->quote($newprofile));
							$db->setQuery($query);
							$db->execute();
							//

							// Duplicate heading menu
							$query->clear()
								->select('*')
								->from('#__menu')
								->where($db->quoteName('menutype') . ' = ' . $db->quote($oldprofile->menutype))
								->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'))
								->andWhere('published = 1');

							$db->setQuery($query);
							$heading_to_duplicate = $db->loadObject();

							if (empty($heading_to_duplicate) || empty($heading_to_duplicate->id)) {
								JLog::add('Could not find heading menu when copying profile ' . $pid, JLog::INFO, 'com_emundus.form');

								$default_heading_menu = new stdClass();
								$default_heading_menu->id = 1;
								$default_heading_menu->menutype = '';
								$default_heading_menu->title = "PROFILE $pid - Copy";
								$default_heading_menu->alias = '';
								$default_heading_menu->note = '';
								$default_heading_menu->path = '';
								$default_heading_menu->link = '';
								$default_heading_menu->type = 'heading';
								$default_heading_menu->published = 1;
								$default_heading_menu->parent_id = 1;
								$default_heading_menu->level = 1;
								$default_heading_menu->component_id = 0;
								$default_heading_menu->checked_out = 0;
								$default_heading_menu->params = '{"menu-anchor_title":"","menu-anchor_css":"","menu-anchor_rel":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1}';
								$default_heading_menu->home = 0;
								$default_heading_menu->language = '*';
								$default_heading_menu->client_id = 0;
								$default_heading_menu->template_style_id = 22;
								$default_heading_menu->access = 1;
								$default_heading_menu->browserNav = 0;
								$heading_to_duplicate = $default_heading_menu;
							}

							if (!empty($heading_to_duplicate->id)) {
								$query->clear();
								$query->insert($db->quoteName('#__menu'));
								foreach ($heading_to_duplicate as $key => $val) {
									if ($key != 'id' && $key != 'menutype' && $key != 'alias' && $key != 'path') {
										$query->set($key . ' = ' . $db->quote($val));
									} elseif ($key == 'menutype') {
										$query->set($key . ' = ' . $db->quote($newmenutype));
									} elseif ($key == 'path') {
										$query->set($key . ' = ' . $db->quote($newmenutype));
									} elseif ($key == 'alias') {
										$query->set($key . ' = ' . $db->quote(str_replace($formbuilder->getSpecialCharacters(), '-', strtolower($oldprofile->label . '-Copy')) . '-' . $newprofile));
									}
								}
								$db->setQuery($query);

								$inserted_heading = $db->execute();

								if ($inserted_heading) {
									// Get fabrik_lists
									$query->clear()
										->select('link')
										->from('#__menu')
										->where($db->quoteName('menutype') . ' = ' . $db->quote($oldprofile->menutype))
										->andWhere($db->quoteName('type') . ' = ' . $db->quote('component'))
										->andWhere('published = 1');
									$db->setQuery($query);
									$links = $db->loadObjectList();

									foreach ($links as $link) {
										if(strpos($link->link,'formid') !== false){
											$formsid_arr[] = explode('=', $link->link)[3];
										}
									}

									foreach ($formsid_arr as $formid) {
										$query->clear()
											->select('label, intro')
											->from($db->quoteName('#__fabrik_forms'))
											->where($db->quoteName('id') . ' = ' . $db->quote($formid));
										$db->setQuery($query);
										$form = $db->loadObject();

										$label = array();
										$intro = array();

										foreach ($languages as $language) {
											# Fabrik has a functionnality that adds <p> tags around the intro text, we need to remove them
											$stripped_intro = strip_tags($form->intro);
											if ($form->intro == '<p>' . $stripped_intro . '</p>') {
												$form->intro = $stripped_intro;
											}

											$label[$language->sef] = $formbuilder->getTranslation($form->label, $language->lang_code);
											$intro[$language->sef] = $formbuilder->getTranslation($form->intro, $language->lang_code);

											if ($label[$language->sef] == ''){
												$label[$language->sef] = $form->label;
											}
											if ($intro[$language->sef] == ''){
												$intro[$language->sef] = $form->intro;
											}
										}

										$formbuilder->createMenuFromTemplate($label, $intro, $formid, $newprofile, true);
									}

									// Copy attachments
									$copied = $this->copyAttachmentsToNewProfile($pid, $newprofile);

									// Create checklist menu
									$this->addChecklistMenu($newprofile);

									$duplicated = $newprofile;
								} else {
									JLog::add('Failed to duplicate form, heading has not been created properly', JLog::WARNING, 'com_emundus.error');
								}
							} else {
								JLog::add('Failed to duplicate form, no heading menu found', JLog::WARNING, 'com_emundus.error');
							}
							//
						} else {
							JLog::add('Failed to duplicate form, empty new profile ', JLog::WARNING, 'com_emundus.error');
						}
					}
                }
			} catch (Exception $e) {
                JLog::add('component/com_emundus/models/form | Error when duplicate forms : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

		return $duplicated;
    }

	public function copyAttachmentsToNewProfile($oldprofile, $newprofile) {
		$copied = false;

		if (!empty($oldprofile) && !empty($newprofile)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$new_profile_exists = false;
			$query->select('id')
				->from($db->quoteName('#__emundus_setup_profiles'))
				->where($db->quoteName('id') . ' = ' . $newprofile);

			try {
				$db->setQuery($query);
				$new_profile_exists = $db->loadResult();
			} catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when get profile : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
			}

			if (!empty($new_profile_exists)) {
				$query->clear();
				$query->select('*')
					->from($db->quoteName('#__emundus_setup_attachment_profiles'))
					->where($db->quoteName('profile_id') . ' = ' . $oldprofile);

				try {
					$db->setQuery($query);
					$attachments = $db->loadAssocList();
				} catch (Exception $e) {
					JLog::add('component/com_emundus/models/form | Error when get attachments to copy : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
					return false;
				}

				if (!empty($attachments)) {
					$columns = array_keys($attachments[0]);
					$id_key = array_search('id', $columns);
					unset($columns[$id_key]);

					$values = array();
					foreach ($attachments as $attachment) {
						$attachment['profile_id'] = $newprofile;
						unset($attachment['id']);

						foreach ($attachment as $key => $value) {
							if (empty($value) && $value != 0) {
								$attachment[$key] = null;
							}
						}

						// do not use db->quote() every time, only if the value is not an integer and not null
						$values[] = implode(',', array_map(function($value) use ($db) {
							return is_null($value) ? 'NULL' : $db->quote($value);
						}, $attachment));
					}

					$query->clear()
						->insert($db->quoteName('#__emundus_setup_attachment_profiles'))
						->columns($db->quoteName($columns))
						->values($values);

					try {
						$db->setQuery($query);
						$copied = $db->execute();
					} catch (Exception $e) {
						JLog::add('component/com_emundus/models/form | Error when copy attachments to new profile : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
					}
				} else {
					$copied = true;
				}
			}
		}

		return $copied;
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
            JLog::add('component/com_emundus/models/form | Error when get form by id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

	public function getFormByFabrikId($id) {
		$form = [];

		if (!empty($id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, label')
				->from($db->quoteName('#__fabrik_forms'))
				->where($db->quoteName('id') . ' = ' . $id);


			try {
				$db->setQuery($query);
				$form = $db->loadObject();

				if (!empty($form->label)) {
					$form->label = JText::_($form->label);
				}
			} catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Error when get form by fabrik id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $form;
	}

    public function createApplicantProfile($first_page = true) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'settings.php');
        require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'update.php');

        $formbuilder = new EmundusModelFormbuilder();
        $settings = new EmundusModelSettings();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Create profile
        $query->clear()
            ->select('id')
            ->from($db->quoteName('#__emundus_setup_profiles'))
            ->order('id DESC');
        $db->setQuery($query);
        $lastprofile = $db->loadObjectList()[0];

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

        $values = array(
            'Nouveau formulaire',
            '',
            1,
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
            if (empty($newprofile)){
                return false;
            }

            // Create menutype
            $menutype = $this->createMenuType('menu-profile' . $newprofile,'Nouveau formulaire');
            if (empty($menutype)) {
                return false;
            }

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_profiles'))
                ->set($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
                ->where($db->quoteName('id') . ' = ' . $db->quote($newprofile));
            $db->setQuery($query);
            $db->execute();


            // Create heading menu
            $datas = [
                'menutype' => 'menu-profile' . $newprofile,
                'title' => 'Nouveau formulaire',
                'link' => '#',
                'type' => 'heading',
                'component_id' => 0,
                'params' => []
            ];
            $heading_menu = EmundusHelperUpdate::addJoomlaMenu($datas);
            if ($heading_menu['status'] !== true){
                return false;
            }
	        $header_menu_id = $heading_menu['id'];

	        $alias = 'menu-profile'.$newprofile.'-heading-'.$header_menu_id;
	        $query->clear()
		        ->update($db->quoteName('#__menu'))
		        ->set($db->quoteName('alias') . ' = ' . $db->quote($alias))
		        ->set($db->quoteName('path') . ' = ' . $db->quote($alias))
		        ->where($db->quoteName('id') . ' = ' . $db->quote($header_menu_id));
	        $db->setQuery($query);
	        $db->execute();

            // Create first page
            if ($first_page) {
                $label = [
                    'fr' => 'Ma première page',
                    'en' => 'My first page'
                ];
                $intro = [
                    'fr' => 'Décrivez votre page de formulaire avec une introduction',
                    'en' => 'Describe your form page with an introduction'
                ];
                $formbuilder->createApplicantMenu($label, $intro, $newprofile, 'false');
            }

            // Create submittion page
            $label = [
                'fr' => "Confirmation d'envoi de dossier",
                'en' => 'Data & disclaimer confirmation'
            ];
            $intro = [
                'fr' => '',
                'en' => ''
            ];
            $submittion_page_res = $formbuilder->createSubmittionPage($label,$intro,$newprofile);
            if($submittion_page_res['status'] !== true){
                return false;
            }

            // Create checklist menu
            $this->addChecklistMenu($newprofile);
            //

            $user = JFactory::getUser();
            $settings->onAfterCreateForm($user->id);

            return $newprofile;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error when create a setup_profile : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

	public function createFormEval()
	{
		require_once (JPATH_ROOT . '/components/com_emundus/models/formbuilder.php');
		$m_formbuilder = new EmundusModelFormbuilder();
		$form_id = $m_formbuilder->createFabrikForm('EVALUATION', ['fr' => 'Nouvelle Évaluation', 'en' => 'New Evaluation'], ['fr' => 'Introduction de l\'évaluation', 'en' => 'Evaluation introduction'], 'eval');

		if (!empty($form_id)) {
			$group = $m_formbuilder->createGroup(array('fr' => 'Hidden group', 'en' => 'Hidden group'), $form_id, -1);
			if (!empty($group)) {
				// Create hidden group
				$m_formbuilder->createElement('id', $group['group_id'],'internalid','id','',1,0,0);
				$m_formbuilder->createElement('time_date',$group['group_id'],'date','time date','',1, 0);
				$m_formbuilder->createElement('fnum',$group['group_id'],'field','fnum','{jos_emundus_evaluations___fnum}',1,0,0,1,0,44);
				$m_formbuilder->createElement('user',$group['group_id'],'databasejoin','user','{$my->id}',1,0,1);
				$m_formbuilder->createElement('student_id',$group['group_id'],'field','student_id','{jos_emundus_evaluations___student_id}',1,0,0);
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('*')
				->from($db->quoteName('#__fabrik_lists'))
				->where($db->quoteName('db_table_name') . ' LIKE ' . $db->quote('jos_emundus_evaluations'));

			$db->setQuery($query);
			$list = $db->loadAssoc();

			if (!empty($list)) {
				$list_id = $m_formbuilder->copyList($list, $form_id);

				if (empty($list_id)) {
					JLog::add('component/com_emundus/models/form | Error when create a list for evaluation form, could not copy list based on jos_emundus_evaluations', JLog::WARNING, 'com_emundus.error');
				}
			} else {
				JLog::add('component/com_emundus/models/form | Error when create a list for evaluation form, could not find list with jos_emundus_evaluations', JLog::WARNING, 'com_emundus.error');
			}
		} else {
			JLog::add('component/com_emundus/models/form | Error when create a form for evaluation form', JLog::WARNING, 'com_emundus.error');
		}

		return $form_id;
	}

    public function createMenuType($menutype, $title) {
        $menutype_table = JTableNested::getInstance('MenuType');

        try {
            JFactory::$database = null;

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);


            $query->clear()
                ->select('menutype')
                ->from($db->quoteName('#__menu_types'))
                ->where($db->quoteName('menutype') . ' LIKE ' . $db->quote($menutype));
            $db->setQuery($query);
            $is_existing = $db->loadResult();

            if(empty($is_existing)) {
                $data = array(
                    'menutype' => $menutype,
                    'title' => $title,
                    'description' => '',
                    'client_id' => 0,
                );

                if (!$menutype_table->save($data)) {
                    return '';
                }
                return $menutype;
            } else {
                return $is_existing;
            }
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Cannot create the menutype ' . $menutype . ' : -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return '';
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
            JLog::add('component/com_emundus/models/form | Cannot create the menu : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


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
                JLog::add('component/com_emundus/models/form | Cannot update the form ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query_pid.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateFormLabel($prid, $label){
	    $results = [];

		if (!empty($prid)) {
			require_once (JPATH_SITE.'/components/com_emundus/models/formbuilder.php');
			$formbuilder = new EmundusModelFormbuilder;

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__menu_types'))
				->set($db->quoteName('title') . ' = ' . $db->quote($label))
				->where($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile'.$prid));

			try {
				$db->setQuery($query);
				$results[] = $db->execute();

				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile'.$prid))
					->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'));
				$db->setQuery($query);
				$heading_id = $db->loadResult();

				$alias = 'menu-profile'.$prid . '-heading-'.$heading_id;
				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('title') . ' = ' . $db->quote($label))
					->set($db->quoteName('alias') . ' = ' . $db->quote($alias))
					->set($db->quoteName('path') . ' = ' . $db->quote($alias))
					->where($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile'.$prid))
					->andWhere($db->quoteName('type') . ' = ' . $db->quote('heading'));
				$db->setQuery($query);
				$results[] = $db->execute();

				$query->clear()
					->update($db->quoteName('#__emundus_setup_profiles'))
					->set($db->quoteName('label') . ' = ' . $db->quote($label))
					->where($db->quoteName('id') . ' = ' . $db->quote($prid));
				$db->setQuery($query);
				$results[] = $db->execute();
			} catch (Exception $e) {
				JLog::add('component/com_emundus/models/form | Cannot update the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

		return $results;
    }


    public function getAllDocuments($prid, $cid)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $falang = new EmundusModelFalang;

        try {
            $query->select('*')
                ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('profile_id') . ' = ' . $db->quote($prid))
                ->andWhere($db->quoteName('campaign_id') . ' IS NULL ');
            $db->setQuery($query);
            $old_docs = $db->loadObjectList();

            if(!empty($old_docs)){
                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__emundus_setup_campaigns'))
                    ->where($db->quoteName('profile_id') . ' = ' . $db->quote($prid));
                $db->setQuery($query);
                $campaignstoaffect = $db->loadObjectList();

                foreach ($campaignstoaffect as $campaign) {
                    foreach ($old_docs as $old_doc){
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_attachment_profiles'));
                        foreach ($old_doc as $key => $value) {
                            if ($key != 'id' && $key != 'campaign_id') {
                                $query->set($key . ' = ' . $db->quote($value));
                            } elseif ($key == 'campaign_id') {
                                $query->set($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign->id));
                            }
                        }
                        $db->setQuery($query);
                        $db->execute();
                    }
                }

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_attachment_profiles'))
                    ->where($db->quoteName('profile_id') . ' = ' . $db->quote($prid))
                    ->andWhere($db->quoteName('campaign_id') . ' IS NULL');
                $db->setQuery($query);
                $db->execute();
            }

            $query->clear()
                ->select([
                    'sap.attachment_id AS id',
                    'sap.ordering',
                    'sap.mandatory AS need',
                    'sa.value',
                    'sa.description',
                    'sa.allowed_types',
                    'sa.nbmax',
                    'sa.lbl'
                ])
                ->from($db->quoteName('#__emundus_setup_attachment_profiles', 'sap'))
                ->leftJoin($db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON ' . $db->quoteName('sa.id') . ' = ' . $db->quoteName('sap.attachment_id'))
                ->order($db->quoteName('sap.ordering'))
                ->where($db->quoteName('sap.published') . ' = 1')
                ->andWhere($db->quoteName('sap.campaign_id') . ' = ' . $cid);

            $db->setQuery($query);
            $documents = $db->loadObjectList();

            foreach ($documents as $document) {
                if(strpos($document->lbl, '_em') === 0){
                    $document->can_be_deleted = true;
                } else {
                    $document->can_be_deleted = false;
                }

                $f_values = $falang->getFalang($document->id,'emundus_setup_attachments','value');
                $document->value_en = $f_values->en->value;
                $document->value_fr = $f_values->fr->value;

                $f_descriptions = $falang->getFalang($document->id,'emundus_setup_attachments','description');
                $document->description_en = $f_descriptions->en->value;
                $document->description_fr = $f_descriptions->fr->value;
            }

            return $documents;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting documents of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    public function getUnDocuments() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $languages = JLanguageHelper::getLanguages();

        require_once (JPATH_SITE.'/components/com_emundus/models/falang.php');

        $falang = new EmundusModelFalang;

        $query->select(array(' DISTINCT a.*', 'b.mandatory'))
            ->from($db->quoteName('#__emundus_setup_attachments','a'))
            ->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles', 'b') . ' ON ' . $db->quoteName('b.attachment_id') . ' = ' . $db->quoteName('a.id'))
            ->where($db->quoteName('a.published') . ' = ' . $db->quote(1))
            ->order($db->quoteName('a.value'));

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

                $f_values = $falang->getFalang($undocument->id,'emundus_setup_attachments','value',$undocument->value);
                $f_descriptions = $falang->getFalang($undocument->id,'emundus_setup_attachments','description',$undocument->description);
                $undocument->name = $f_values;
                $undocument->description = $f_descriptions;
            }

            return $undocuments;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error getting documents not associated : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

	public function getAttachments() {
		$attachments = [];
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__emundus_setup_attachments'))
			->where($db->quoteName('published') . ' = 1')
			->order('value');

		try {
			$db->setQuery($query);
			$attachments = $db->loadObjectList();

			if (!empty($attachments)) {
				require_once (JPATH_SITE . '/components/com_emundus/models/falang.php');
				$falang = new EmundusModelFalang;

				foreach ($attachments as $attachment) {
					$attachment->can_be_deleted = strpos($attachment->lbl, '_em') === 0;
					$attachment->name = $falang->getFalang($attachment->id,'emundus_setup_attachments','value', $attachment->value);
					$attachment->description = $falang->getFalang($attachment->id,'emundus_setup_attachments','description', $attachment->description);
				}
			}
		} catch (Exception $e) {
			JLog::add('Failed to get attachments ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $attachments;
	}

    /**
     * @param $documentIds
     * @return array
     */
    public function getDocumentsUsage($documentIds): array
    {
        $forms = [];

        if (!empty($documentIds)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('jesap.attachment_id, jesap.profile_id, jesp.label')
                ->from('jos_emundus_setup_attachment_profiles AS jesap')
                ->leftJoin('jos_emundus_setup_profiles AS jesp ON jesap.profile_id = jesp.id')
                ->where('jesap.attachment_id  IN (' . implode(',', $documentIds) . ')');

            $db->setQuery($query);

            try {
                $profile_infos = $db->loadObjectList();
            } catch (Exception $e) {
                $msg = 'Error trying to get profile info from attachment_id ' . $e->getMessage();
                JLog::add($msg, JLog::ERROR, 'com_emundus');
            }

            if (!empty($profile_infos)) {
                foreach($profile_infos as $profile_info) {
                    if (!isset($forms[$profile_info->attachment_id])) {
                        $forms[$profile_info->attachment_id] = [
                            'profiles' => [],
                            'usage' => 0
                        ];
                    }

                    $forms[$profile_info->attachment_id]['profiles'][] = [
                        'id' => $profile_info->profile_id,
                        'label' => $profile_info->label
                    ];
                    $forms[$profile_info->attachment_id]['usage']++;
                }
            }
        }

        return $forms;
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
            JLog::add('component/com_emundus/models/form | Error deleting documents : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
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

            $documents_campaign = EmundusModelform::getAllDocuments($prid, $cid);

            if (empty($documents_campaign)) {
                $this->removeChecklistMenu($prid);
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error remove document ' . $did . ' associated to the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function updateMandatory($did,$prid,$cid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id,mandatory')
                ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('attachment_id') . ' = ' . $db->quote($did))
                ->andWhere($db->quoteName('profile_id') . ' = ' . $db->quote($prid))
                ->andWhere($db->quoteName('campaign_id') . ' = ' . $db->quote($cid));
            $db->setQuery($query);
            $attachment = $db->loadObject();
            $mandatory = intval($attachment->mandatory);

            if($mandatory == 0){
                $mandatory = 1;
            } else {
                $mandatory = 0;
            }

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->set($db->quoteName('mandatory') . ' = ' . $db->quote($mandatory))
                ->where($db->quoteName('id') . ' = ' . $db->quote($attachment->id));

            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error remove document ' . $did . ' associated to the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function addDocument($did,$profile,$campaign){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            // Create checklist menu if documents are asked
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('alias') . ' = ' . $db->quote('checklist-' . $profile));
            $db->setQuery($query);
            $checklist = $db->loadObject();

            if ($checklist == null) {
                $this->addChecklistMenu($profile);
            }
            //

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
                ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign))
                ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($did))
                ->set($db->quoteName('displayed') . ' = ' . $db->quote(1))
                ->set($db->quoteName('mandatory') . ' = ' . $db->quote(0))
                ->set($db->quoteName('ordering') . ' = ' . $db->quote(0));
            $db->setQuery($query);
            $db->execute();

            $documents_campaign = EmundusModelform::getAllDocuments($profile, $campaign);

            if (empty($documents_campaign)) {
                $this->removeChecklistMenu($profile);
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error remove document ' . $did . ' associated to the campaign ' . $campaign . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function deleteDocument($did){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');

        $falang = new EmundusModelFalang;

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
            JLog::add('component/com_emundus/models/form | Error when delete the document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function addChecklistMenu($prid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $modules = $eMConfig->get('form_builder_page_creation_modules', [93,102,103,104,168,170]);

        try {
            // Create the menu
            $submittion_page = $this->getSubmittionPage($prid);

            $params = array(
                'custom_title' => "",
                'show_info_panel' => "0",
                'show_info_legend' => "1",
                'show_browse_button' => "0",
                'show_shortdesc_input' => "0",
                'required_desc' => "0",
                'show_nb_column' => "1",
                'is_admission' => "0",
                'notify_complete_file' => 0,
                'menu-anchor_title' => "Documents",
                'menu-anchor_css' => "huge circular inverted blue upload outline icon",
                'menu_image' => "0",
                'menu_image_css' => "0",
                'menu_text' => 1,
                'menu_show' => 1,
                'page_title' => "Documents",
                'show_page_heading' => "",
                'page_heading' => "",
                'pageclass_sfx' => "applicant-form",
                'meta_description' => "",
                'meta_keywords' => "",
                'robots' => "",
                'secure' => 0,
            );

            $datas = [
                'menutype' => 'menu-profile' . $prid,
                'title' => 'Documents',
                'alias' => 'checklist-' . $prid,
                'path' => 'checklist-' . $prid,
                'link' => 'index.php?option=com_emundus&view=checklist',
                'type' => 'component',
                'component_id' => 11369,
                'params' => $params
            ];
            $checklist_menu = EmundusHelperUpdate::addJoomlaMenu($datas,$submittion_page->id,1,'before',$modules);
            if($checklist_menu['status'] !== true){
                return false;
            }

            $newmenuid = $checklist_menu['id'];

            // Affect documents module to each menus of profile
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = ' . $db->quote('menu-profile' . $prid));
            $db->setQuery($query);
            $menus = $db->loadObjectList();

            foreach ($menus as $menu) {
                $query->clear()
                    ->select('moduleid')
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('moduleid') . ' = 103')
                    ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
                $db->setQuery($query);
                $is_existing = $db->loadResult();

                if(!$is_existing) {
                    $query->clear()
                        ->insert($db->quoteName('#__modules_menu'))
                        ->set($db->quoteName('moduleid') . ' = 103')
                        ->set($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
                    $db->setQuery($query);
                    $db->execute();
                }

                $query->clear()
                    ->select('moduleid')
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('moduleid') . ' = 104')
                    ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
                $db->setQuery($query);
                $is_existing = $db->loadResult();

                if(!$is_existing) {
                    $query->clear()
                        ->insert($db->quoteName('#__modules_menu'))
                        ->set($db->quoteName('moduleid') . ' = 104')
                        ->set($db->quoteName('menuid') . ' = ' . $db->quote($menu->id));
                    $db->setQuery($query);
                    $db->execute();
                }
            }
            //

            return $newmenuid;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error to add the checklist module to form (' . $prid . ') menus : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function removeChecklistMenu($prid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $modules = $eMConfig->get('form_builder_page_creation_modules', [93,102,103,104,168,170]);

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('alias') . ' = ' . $db->quote('checklist-' . $prid));
        try {
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
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error to remove the checklist module to form (' . $prid . ') menus : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    public function getFormsByProfileId($profile_id) {
        if (empty($profile_id)) {
            return false;
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');

        $formbuilder = new EmundusModelFormbuilder;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['menu.link' , 'menu.rgt'])
            ->from ($db->quoteName('#__menu', 'menu'))
            ->leftJoin($db->quoteName('#__menu_types', 'mt').' ON '.$db->quoteName('mt.menutype').' = '.$db->quoteName('menu.menutype'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp').' ON '.$db->quoteName('sp.menutype').' = '.$db->quoteName('mt.menutype'))
            ->where($db->quoteName('sp.id') . ' = '.$profile_id)
            ->where($db->quoteName('menu.parent_id') . ' != 1')
            ->where($db->quoteName('menu.published') . ' = 1')
	        ->where($db->quoteName('menu.link') . ' LIKE ' . $db->quote('%option=com_fabrik%'))
            ->group('menu.rgt')
            ->order('menu.rgt ASC');


        try {
            $db->setQuery($query);
            $forms = $db->loadObjectList();

            foreach ($forms as $form) {
                $link = explode('=', $form->link);
                $form->id = $link[sizeof($link) - 1];

                $query->clear()
                    ->select('label')
                    ->from($db->quoteName('#__fabrik_forms'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($form->id));
                $db->setQuery($query);
                $form->label = $formbuilder->getJTEXT($db->loadResult());
                print_r($forms->label);
            }

            return $forms;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting form pages by profile_id ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getGroupsByForm($form_id){
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');

        $formbuilder = new EmundusModelFormbuilder;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['g.id' , 'g.label', 'g.params', 'g.published'])
            ->from ($db->quoteName('#__fabrik_formgroup', 'fg'))
            ->leftJoin($db->quoteName('#__fabrik_groups', 'g').' ON '.$db->quoteName('g.id').' = '.$db->quoteName('fg.group_id'))
            ->where($db->quoteName('fg.form_id') . ' = '.$form_id)
            ->order('fg.ordering ASC');


        try {
            $db->setQuery($query);
            $groups = $db->loadObjectList();

            foreach ($groups as $key => $group){
                $params = json_decode($group->params, true);
                if ($params['repeat_group_show_first'] == -1) {
                    array_splice($groups, $key, 1);
                }
                $group->label = $formbuilder->getJTEXT($group->label);
            }

            return $groups;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting groups by form_id ' . $form_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getSubmittionPage($prid){
        if (empty($prid)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['menu.link','menu.rgt','menu.id'])
            ->from ($db->quoteName('#__menu', 'menu'))
            ->leftJoin($db->quoteName('#__menu_types', 'mt').' ON '.$db->quoteName('mt.menutype').' = '.$db->quoteName('menu.menutype'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp').' ON '.$db->quoteName('sp.menutype').' = '.$db->quoteName('mt.menutype'))
            ->where($db->quoteName('sp.id') . ' = '.$prid)
            ->where($db->quoteName('menu.parent_id') . ' = 1')
            ->where($db->quoteName('menu.type') . ' = ' . $db->quote('component'));

        try {
            $db->setQuery($query);
            $menus = $db->loadObjectList();
            $sub_page = new stdClass();

			foreach($menus as $menu){
                $formid = explode('=',$menu->link)[3];
                if($formid != null){
                    $query->clear()
                        ->select('count(id)')
                        ->from($db->quoteName('#__fabrik_lists'))
                        ->where($db->quoteName('db_table_name') . ' LIKE ' . $db->quote('jos_emundus_declaration'))
                        ->andWhere($db->quoteName('form_id') . ' = ' . $db->quote($formid));
                    $db->setQuery($query);
                    $submittion = $db->loadResult();
                    if($submittion > 0){
                        $sub_page->link = $menu->link;
                        $sub_page->rgt = $menu->rgt;
                        $sub_page->id = $menu->id;

						break;
                    }
                }
            }

            return $sub_page;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting the submittion page of the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }


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
            JLog::add('component/com_emundus/models/form | Error at getting name of the form ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getFilesByProfileId($profile_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getUser();

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
                    ->where($db->quoteName('campaign_id') . ' = ' . $campaign->id)
                    ->andWhere($db->quoteName('published') . ' != -1')
                    ->andWhere($db->quoteName('user_id') . ' != ' . $db->quote($user->id));

                $db->setQuery($query);
                $files += $db->loadResult();
            }

            return $files;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting files by form ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getAssociatedCampaign($profile_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['id as id','label as label'])
            ->from ($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' = ' . $db->quote($profile_id));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting campaigns link to the form ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getAssociatedProgram($form_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['group_id as id'])
            ->from ($db->quoteName('#__fabrik_formgroup'))
            ->where($db->quoteName('form_id') . ' = ' . $db->quote($form_id));

        try {
            $db->setQuery($query);
            $group_id=$db->loadRow();
            //var_dump($group_id);


            $query->clear()
                ->select('*')
                ->from ($db->quoteName('#__emundus_setup_programmes'))
                ->where($db->quoteName('fabrik_group_id') . ' = '.$db->quote($group_id[0]));

            $db->setQuery($query);
            $programme = $db->loadObject();
            //var_dump($programme);
            return $programme;

        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getting eval form program link to the form ' . $form_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
                JLog::add('component/com_emundus/models/form | Error when affect campaigns to the form ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        }

        return true;
    }

    function getDocumentsByProfile($prid){
		$attachments_by_profile = [];

		if (!empty($prid)) {
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			$query->select('sa.id as docid,sa.value as label,sap.*,sa.allowed_types')
				->from($db->quoteName('#__emundus_setup_attachment_profiles','sap'))
				->leftJoin($db->quoteName('#__emundus_setup_attachments','sa').' ON '.$db->quoteName('sa.id').' = '.$db->quoteName('sap.attachment_id'))
				->where($db->quoteName('sap.profile_id') . ' = ' . $db->quote($prid))
				->order('sap.mandatory DESC, sap.ordering, sa.value ASC');

			try {
				$db->setQuery($query);
				$attachments_by_profile = $db->loadObjectList();
			} catch (Exception $e){
				JLog::add('component/com_emundus/models/form | Error cannot get documents by profile_id : ' . $prid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
			}
		}

	    return $attachments_by_profile;
    }

    function reorderDocuments($documents){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $results = array();

        try {
            foreach ($documents as $document) {

                $query->update($db->quoteName('#__emundus_setup_attachment_profiles'))
                    ->set($db->quoteName('ordering') . ' = ' . (int)$document['ordering'])
                    ->where($db->quoteName('id') . ' = ' . (int)$document['id']);
                $db->setQuery($query);

                $results[] = $db->execute();
                $query->clear();
            }

            return $results;

        } catch (Exception $e){
            JLog::add('component/com_emundus/models/form | Error cannot reorder documents : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function removeDocumentFromProfile($did){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->delete($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('id') . ' = ' . (int)$did);
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/form | Error cannot remove document : ' . $did . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function deleteModelDocument($did){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('count(id)')
                ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('attachment_id') . ' = ' . $db->quote($did));
            $db->setQuery($query);
            $attachment_used = $db->loadResult();

            if($attachment_used == 0) {
                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_attachments'))
                    ->where($db->quoteName('id') . ' = ' . (int)$did);
                $db->setQuery($query);
                return $db->execute();
            } else {
                return false;
            }
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/form | Error cannot delete document template : ' . $did . ' with query : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getDatabaseJoinOptions($table, $column, $value,$concat_value = null, $where = null){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $current_shortlang = explode('-',JFactory::getLanguage()->getTag())[0];

        try {
            $value_select = $value . ' as value';
            if(!empty($concat_value)){
                $concat_value = str_replace('{thistable}', $table, $concat_value);
                $concat_value = str_replace('{shortlang}', $current_shortlang, $concat_value);

                $value_select = 'CONCAT('.$concat_value.') as value';
            }
            $query->select(array($db->quoteName($column,'primary_key'),$value_select))
                ->from($db->quoteName($table));
            if(!empty($where)){
                $query->where(str_replace('WHERE','',$where));
            }
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/form | Error at getDatabaseJoinOptions : ' . preg_replace("/[\r\n]/"," ",$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function checkIfDocCanBeRemovedFromCampaign($document_id, $profile_id): array
    {
        $data = [
            'can_be_deleted' => false,
            'reason' => 'No response from sql'
        ];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(jeu.id)')
            ->from('#__emundus_uploads AS jeu')
            ->leftJoin('#__emundus_setup_campaigns AS jesc ON jesc.id = jeu.campaign_id')
            ->where('jesc.profile_id = ' . $profile_id)
            ->andWhere('jeu.attachment_id = ' . $document_id);

        $db->setQuery($query);

        try {
            $nb_uploads = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error trying to know if i can remove document from profile ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        if ($nb_uploads < 1) {
            $data['can_be_deleted'] = true;
            $data['reason'] = 'No document found for this attachment_id and campaign_id';
        } else {
            $data['reason'] = $nb_uploads;
            $data['sql'] = $query->__toString();
        }

        return $data;
    }
}
