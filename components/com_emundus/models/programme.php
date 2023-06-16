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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelProgramme extends JModelList {

    /**
     * Method to get article data.
     *
     * @param   integer $pk The id of the article.
     *
     * @return  mixed  Menu item data object on success, false on failure.
     * @since version v6
     */
    public function getCampaign($id = 0) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $query->select('pr.*,ca.*');
        $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
        $query->where('ca.training = pr.code AND ca.published=1 AND ca.id='.$id);
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    public function getParams($id = 0) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $query->select('params');
        $query->from('#__menu');
        $query->where('id='.$id);
        $db->setQuery($query);
        return json_decode($db->loadResult(), true);
    }

    /**
     * @param $user
     * @return array
     * get list of programmes for associated files
     * @since version v6
     */
    public function getAssociatedProgrammes($user) {
		$associated_programs = [];

		if (!empty($user)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('DISTINCT sc.training')
				->from($db->quoteName('#__emundus_users_assoc', 'ua'))
				->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$db->quoteName('cc.fnum').'='.$db->quoteName('ua.fnum'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc').' ON '.$db->quoteName('sc.id').'='.$db->quoteName('cc.campaign_id'))
				->where($db->quoteName('ua.user_id').'='.$user);

			try {
				$db->setQuery($query);
				$associated_programs = $db->loadColumn();
			} catch(Exception $e) {
				JLog::add('Error getting associated programmes in model/programme at query : '.$e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $associated_programs;
    }

    /**
     * @param $published  int     get published or unpublished programme
     * @param $codeList   array   array of IN and NOT IN programme code to get
     * @return array
     * @since version v6
     * get list of declared programmes
     */
    public function getProgrammes($published = null, $codeList = array()) {
        $db = JFactory::getDbo();

        $query = 'select *
                  from #__emundus_setup_programmes
                  WHERE 1 = 1 ';
        if (isset($published) && !empty($published)) {
            $query .= ' AND published = '.$published;
        }

        if (!empty($codeList)) {
            if (count($codeList['IN']) > 0) {
                $query .= ' AND code IN ('.implode(',', $db->Quote($codeList['IN'])).')';
            }
            if (count($codeList['NOT_IN']) > 0) {
                $query .= ' AND code NOT IN ('.implode(',', $db->Quote($codeList['NOT_IN'])).')';
            }
        }

        try {
            $db->setQuery($query);
            return $db->loadAssocList('code');
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return array();
        }
    }

    /**
     * @param $published  int     get published or unpublished programme
     * @param $codeList   array   array of IN and NOT IN programme code to get
     * @return mixed
     * get list of declared programmes
     * @since version v6
     */
    public function getProgramme($code) {

        if (empty($code)) {
            return false;
        }

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query
            ->select('*')
            ->from ($db->quoteName('#__emundus_setup_programmes'))
            ->where($db->quoteName('code') . ' LIKE '.$db->quote($code));

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    /**
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Add new programme in DB
     * @since version v6
     */
    public function addProgrammes($data) {
        $db = JFactory::getDbo();

        if (!empty($data)) {
            unset($data[0]['organisation']);
            unset($data[0]['organisation_code']);
            $column = array_keys($data[0]);

            $values = array();
            foreach ($data as $key => $v) {
                unset($v['organisation']);
                unset($v['organisation_code']);
                $values[] = '('.implode(',', $db->Quote($v)).')';
            }

            $query = 'INSERT INTO `#__emundus_setup_programmes` (`'.implode('`, `', $column).'`) VALUES '.implode(',', $values);

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Edit programme in DB
     * @since version v6
     */
    public function editProgrammes($data) {
        $db = JFactory::getDbo();

        if (count($data) > 0) {
            try {
                foreach ($data as $key => $v) {
                    $query = 'UPDATE `#__emundus_setup_programmes` SET label='.$db->Quote($v['label']).' WHERE code like '.$db->Quote($v['code']);
                    $db->setQuery($query);
                    $db->execute();

                    $query = 'UPDATE `#__emundus_setup_teaching_unity` SET label='.$db->Quote($v['label']).' WHERE code like '.$db->Quote($v['code']);
                    $db->setQuery($query);
                    $db->execute();

                    $query = 'UPDATE `#__emundus_setup_campaigns` SET label='.$db->Quote($v['label']).' WHERE training like '.$db->Quote($v['code']);
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
        return true;
    }


    /**
     * Gets the most recent programme code.
     * @return string The most recently added programme in the DB.
     * @since version v6
     */
    function getLatestProgramme() {

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('code'))
            ->from($db->quoteName('#__emundus_setup_programmes'))
            ->order('id DESC')
            ->setLimit('1');

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting latest programme at model/programme at query :'.$query, JLog::ERROR, 'com_emundus');
            return '';
        }
    }


    /**
     * Checks if the user has this programme in his favorites.
     *
     * @param      $programme_id Int The ID of the programme to be favorited.
     * @param null $user_id      Int The user ID, if null: the current user ID.
     *
     * @return bool True if favorited.
     * @since version v6
     */
    function isFavorite($programme_id, $user_id = null) {

        if (empty($user_id)) {
            $user_id = JFactory::getUser()->id;
        }

        if (empty($user_id) || empty($programme_id)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('1')
            ->from($db->quoteName('#__emundus_favorite_programmes'))
            ->where($db->quoteName('user_id').' = '.$user_id.' AND '.$db->quoteName('programme_id').' = '.$programme_id);
        $db->setQuery($query);

        try {
            return $db->loadResult() == 1;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Adds a programme to the user's list of favorites.
     *
     * @param      $programme_id Int The ID of the programme to be favorited.
     * @param null $user_id      Int The user ID, if null: the current user ID.
     *
     * @return bool
     * @since version v6
     */
    public function favorite($programme_id, $user_id = null) {

        if (empty($user_id)) {
            $user_id = JFactory::getUser()->id;
        }

        if (empty($user_id) || empty($programme_id)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__emundus_favorite_programmes'))
            ->columns($db->quoteName(['user_id', 'programme_id']))
            ->values($user_id.','.$programme_id);
        $db->setQuery($query);

        try {
            $db->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Removes a programme from the user's list of favorites.
     *
     * @param      $programme_id Int The ID of the programme to be unfavorited.
     * @param null $user_id      Int The user ID, if null: the current user ID.
     *
     * @return bool
     * @since version v6
     */
    public function unfavorite($programme_id, $user_id = null) {

        if (empty($user_id)) {
            $user_id = JFactory::getUser()->id;
        }

        if (empty($user_id) || empty($programme_id)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__emundus_favorite_programmes'))
            ->where($db->quoteName('user_id').' = '.$user_id.' AND '.$db->quoteName('programme_id').' = '.$programme_id);
        $db->setQuery($query);

        try {
            $db->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Get's the upcoming sessions of the user's favorite programs.
     *
     * @param null $user_id
     *
     * @return mixed
     * @since version v6
     */
    public function getUpcomingFavorites($user_id = null) {

        if (empty($user_id)) {
            $user_id = JFactory::getUser()->id;
        }

        if (empty($user_id)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select(['t.*', $db->quoteName('c.id','cid'), $db->quoteName('p.id', 'pid'), $db->quoteName('p.url')])
            ->from($db->quoteName('#__emundus_favorite_programmes','f'))
            ->leftJoin($db->quoteName('#__emundus_setup_programmes','p').' ON '.$db->quoteName('p.id').' = '.$db->quoteName('f.programme_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity','t').' ON '.$db->quoteName('t.code').' LIKE '.$db->quoteName('p.code'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$db->quoteName('c.session_code').' LIKE '.$db->quoteName('t.session_code'))
            ->where($db->quoteName('f.user_id').' = '.$user_id.' AND '.$db->quoteName('t.published').'= 1 AND '.$db->quoteName('t.date_start').' >= NOW()')
            ->order($db->quoteName('t.date_start').' ASC');
        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Get's the user's favorite programs.
     *
     * @param null $user_id
     *
     * @return mixed
     * @since version v6
     */
    public function getFavorites($user_id = null) {

        if (empty($user_id)) {
            $user_id = JFactory::getUser()->id;
        }

        if (empty($user_id)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select(['p.*', $db->quoteName('t.label', 'title')])
            ->from($db->quoteName('#__emundus_favorite_programmes','f'))
            ->leftJoin($db->quoteName('#__emundus_setup_programmes','p').' ON '.$db->quoteName('p.id').' = '.$db->quoteName('f.programme_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_thematiques', 'th').' ON '.$db->quoteName('th.id').' = '.$db->quoteName('p.programmes'))
            ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 't').' ON '.$db->quoteName('t.code').' LIKE '.$db->quoteName('p.code'))
            ->where($db->quoteName('f.user_id').' = '.$user_id.' AND '.$db->quoteName('p.id').' NOT IN (SELECT p.id FROM `jos_emundus_setup_programmes` AS `p` LEFT JOIN `jos_emundus_setup_teaching_unity` AS `t` ON `t`.`code` LIKE `p`.`code` LEFT JOIN `jos_emundus_setup_campaigns` AS `c` ON `c`.`session_code` LIKE `t`.`session_code` LEFT JOIN `jos_emundus_campaign_candidature` AS `cc` ON `cc`.`campaign_id` LIKE `t`.`id` WHERE `cc`.`user_id` = '.$user_id.' AND `cc`.`published`= 1) AND '.$db->quoteName('p.published').'= 1 AND '.$db->quoteName('t.date_start').' > NOW() AND '.$db->quoteName('t.published').'= 1 AND '.$db->quoteName('th.published').'= 1')
            ->group($db->quoteName('p.id'));
        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
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
    function getAllPrograms($lim, $page, $filter, $sort, $recherche) {
        $all_programs = [];

        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->getUserPrograms($user->id);
        //

        if (!empty($programs)) {
            $limit = empty($lim) ? 25 : $lim;

            if (empty($page)) {
                $offset = 0;
            } else {
                $offset = ($page-1) * $limit;
            }

            if (empty($sort)) {
                $sort = 'DESC';
            }

            $sortDb = 'p.id ';

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if ($filter == 'Publish') {
                $filterDate = $db->quoteName('p.published') . ' LIKE 1';
            } else if ($filter == 'Unpublish') {
                $filterDate = $db->quoteName('p.published') . ' LIKE 0';
            } else {
                $filterDate = ('1');
            }

            if (empty($recherche)) {
                $fullRecherche = 1;
            } else {
                $rechercheLbl = $db->quoteName('p.label') . ' LIKE ' . $db->quote('%'.$recherche.'%');
                $rechercheNotes = $db->quoteName('p.notes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
                $rechercheCategory = $db->quoteName('p.programmes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
                $fullRecherche = $rechercheLbl.' OR '.$rechercheNotes.' OR '.$rechercheCategory;
            }

            $query->select(['p.*', 'COUNT(sc.id) AS nb_campaigns'])
                ->from($db->quoteName('#__emundus_setup_programmes', 'p'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc') . ' ON ' . $db->quoteName('sc.training') . ' LIKE ' . $db->quoteName('p.code'))
                ->where($filterDate)
                ->where($fullRecherche)
                ->andWhere($db->quoteName('p.code') . ' IN (' . implode(',', $db->quote($programs)) . ')')
                ->group($sortDb)
                ->order($sortDb.$sort);

            try {
	            $db->setQuery($query);
				$all_programs['count'] = count($db->loadObjectList());

                if(empty($lim)) {
                    $db->setQuery($query, $offset);
                } else {
                    $db->setQuery($query, $offset, $limit);
                }

                $programs = $db->loadObjectList();
				
				$all_programs['datas'] = $programs;
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/program | Error at getting list of programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getProgramCount($filter, $recherche) {
        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->getUserPrograms($user->id);
        //

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ($filter == 'Publish') {
            $filterCount = $db->quoteName('p.published') . ' LIKE 1';
        } else if ($filter == 'Unpublish') {
            $filterCount = $db->quoteName('p.published') . ' LIKE 0';
        } else {
            $filterCount = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheLbl = $db->quoteName('p.label') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheNotes = $db->quoteName('p.notes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheCategory = $db->quoteName('p.programmes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheLbl.' OR '.$rechercheNotes.' OR '.$rechercheCategory;
        }

        $query->select('COUNT(p.id)')
            ->from($db->quoteName('#__emundus_setup_programmes', 'p'))
            ->where($filterCount)
            ->where($fullRecherche)
            ->andWhere($db->quoteName('p.code') . ' IN (' . implode(',',$db->quote($programs)) . ')');
        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting number of programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    public function getProgramById($id) {
        if (empty($id)) {
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->select('*')
            ->from ($db->quoteName('#__emundus_setup_programmes'))
            ->where($db->quoteName('id') . ' = '.$db->quote($id));

        $db->setQuery($query);
        $programme = $db->loadObject();

        $query->clear()
            ->select('sg.id')
            ->from ($db->quoteName('#__emundus_setup_groups_repeat_course','sgr'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups','sg').' ON '.$db->quoteName('sgr.parent_id').' = '.$db->quoteName('sg.id'))
            ->where($db->quoteName('sgr.course') . ' = '. $db->quote($programme->code))
            ->andWhere($db->quoteName('sg.parent_id') . ' IS NULL');
        $db->setQuery($query);
        $prog_group = $db->loadResult();

        $programme->group = $prog_group;
        $programme->evaluator_group = $this->getGroupByParent($programme->code,2);
        $programme->manager_group = $this->getGroupByParent($programme->code,3);

        try {
            return $programme;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting program by id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    public function addProgram($data) {
        $response = false;
        $user = JFactory::getUser();
        $user_id = !empty($user->id) ? $user->id : 62;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!empty($data) && !empty($data['label'])) {
            $data['code'] = preg_replace('/[^A-Za-z0-9]/', '', $data['label']);
            $data['code'] = str_replace(' ', '_', $data['code']);
            $data['code'] = substr($data['code'], 0, 10);
            $data['code'] = strtolower($data['code']);
            $data['code'] = uniqid($data['code']. '-');

            JPluginHelper::importPlugin('emundus');
            JFactory::getApplication()->triggerEvent('callEventHandler', ['onBeforeProgramCreate', ['data' => $data]]);

            if (count($data) > 0) {
                $query->insert($db->quoteName('#__emundus_setup_programmes'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->Quote(array_values($data))));

                try {
                    $db->setQuery($query);
                    $db->execute();
                    $prog_id = $db->insertid();

                    $query->clear()
                        ->select('*')
                        ->from($db->quoteName('#__emundus_setup_programmes'))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($prog_id));
                    $db->setQuery($query);
                    $programme = $db->loadObject();

                    // Create user group
                    $columns = array('label', 'published', 'class');
                    $values = array($db->quote($programme->label), $db->quote(1), $db->quote('label-default'));

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_groups'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',',$values));
                    $db->setQuery($query);
                    $db->execute();
                    $group_id = $db->insertid();
                    //

                    // Link group with programme
                    $columns = array('parent_id', 'course');
                    $values = array($group_id, $db->quote($programme->code));

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',',$values));
                    $db->setQuery($query);
                    $db->execute();
                    //

                    // Affect coordinator to the group of the program
                    $columns = array('user_id', 'group_id');
                    $values = array($db->quote($user_id), $group_id);

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_groups'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',',$values));
                    $db->setQuery($query);
                    $db->execute();
                    //

                    // Link All rights group with programme
                    $eMConfig = JComponentHelper::getParams('com_emundus');
                    $all_rights_group_id = $eMConfig->get('all_rights_group', 1);

                    $columns = array('parent_id', 'course');
                    $values = array($db->quote($all_rights_group_id), $db->quote($programme->code));

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',',$values));
                    $db->setQuery($query);
                    $db->execute();
                    //

                    // Create evaluator and manager group
                    $this->addGroupToProgram($programme->label,$programme->code,2);
                    $this->addGroupToProgram($programme->label,$programme->code,3);
                    //

                    // Call plugin triggers
                    JFactory::getApplication()->triggerEvent('callEventHandler', ['onAfterProgramCreate', ['programme' => $programme]]);

                    $response = array(
                        'programme_id' => $prog_id,
                        'programme_code' => $programme->code
                    );
                } catch(Exception $e) {
                    JLog::add('component/com_emundus/models/program | Error when creating a program : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                    $response = $e->getMessage();
                }

            }
        }

        return $response;
    }

    /**
     * @param   int $id the program to update
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Update program in DB
     * @since version 1.0
     */
    public function updateProgram($id, $data) {
        $updated = false;

        if (!empty($id) && !empty($data)) {
            $db = JFactory::getDbo();

            JPluginHelper::importPlugin('emundus');
            
            JFactory::getApplication()->triggerEvent('callEventHandler', ['onBeforeProgramUpdate', ['id' => $id, 'data' => $data]]);

            if (!empty($data)) {
                $query = 'SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ' . $db->quote('jos_emundus_setup_programmes');
                $db->setQuery($query);
                $table_columns = $db->loadColumn();

                $fields = [];
                foreach ($data as $key => $val) {
                    if (in_array($key, $table_columns) && $key != 'id' && $key != 'code') {
                        $fields[] = $db->quoteName($key) . ' = ' . $db->quote($val);
                    }
                }

                if (!empty($fields)) {
                    $query = $db->getQuery(true);
                    $query->update($db->quoteName('#__emundus_setup_programmes'))
                        ->set($fields)
                        ->where($db->quoteName('id') . ' = '.$db->quote($id));

                    try {
                        $db->setQuery($query);
                        $updated = $db->execute();

                        if ($updated) {
                            
                            JFactory::getApplication()->triggerEvent('callEventHandler', ['onAfterProgramUpdate', ['id' => $id, 'data' => $data]]);
                        }
                    } catch(Exception $e) {
                        JLog::add('component/com_emundus/models/program | Error when updating the program ' . $id . ': ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.error');
                    }
                }
            }
        }

        return $updated;
    }

    /**
     * @param   array $data the row to delete in table.
     *
     * @return boolean
     * Delete program(s) in DB
     * @since version 1.0
     */
    public function deleteProgram($data) {
		$deleted = false;

        if (!empty($data)) {
			if (!is_array($data)) {
				$data = [$data];
			}

            // Call plugin event before we delete the programme
            JPluginHelper::importPlugin('emundus');
            
            JFactory::getApplication()->triggerEvent('callEventHandler', ['onBeforeProgramDelete', ['data' => $data]]);


	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);

            try {
	            $query->select($db->qn('sc.id'))
		            ->from($db->qn('#__emundus_setup_campaigns', 'sc'))
		            ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sc.training').' LIKE '.$db->quoteName('sp.code'))
		            ->where($db->quoteName('sp.id') . ' IN (' . implode(", ", array_values($data)) . ')');

	            $db->setQuery($query);
	            $campaigns = $db->loadColumn();

	            if (!empty($campaigns)) {
		            require_once (JPATH_SITE. '/components/com_emundus/models/campaign.php');
		            $m_campaign = new EmundusModelCampaign;
		            $campaign_deleted = $m_campaign->deleteCampaign($campaigns);

		            if (!$campaign_deleted) {
			            JLog::add('Campaign has not been deleted', JLog::ERROR, 'com_emundus');
		            }
	            }

	            $query->clear()
		            ->delete($db->quoteName('#__emundus_setup_programmes'))
		            ->where(array($db->quoteName('id') . ' IN (' . implode(", ", array_values($data)) . ')'));

	            $db->setQuery($query);
	            $deleted = $db->execute();

	            if ($deleted) {
		            
		            JFactory::getApplication()->triggerEvent('callEventHandler', ['onAfterProgramDelete', ['id' => JFactory::getUser()->id, 'data' => $data]]);
	            }
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/program | Error wen delete programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus.error');
            }
        }

		return $deleted;
    }

    /**
     * @param   array $data the row to unpublish in table.
     *
     * @return boolean
     * Unpublish program(s) in DB
     * @since version 1.0
     */
    public function unpublishProgram($data) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 0'
                );
                $conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($db->quoteName('#__emundus_setup_programmes'))
                    ->set($fields)
                    ->where($conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/program | Error when unpublish programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }

        } else {
            return false;
        }
    }

    /**
     * @param   array $data the row to publish in table.
     *
     * @return boolean
     * Publish program(s) in DB
     * @since version 1.0
     */
    public function publishProgram($data) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 1'
                );
                $conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($db->quoteName('#__emundus_setup_programmes'))
                    ->set($fields)
                    ->where($conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/program | Error when publish programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }

        } else {
            return false;
        }
    }

    /**
     *
     * @return array
     * get list of declared programmes
     * @since version 1.0
     */
    public function getProgramCategories() {
		$categories = [];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(programmes)')
            ->from ($db->quoteName('#__emundus_setup_programmes'))
            ->order('id DESC');

        try {
            $db->setQuery($query);
	        $categories = $db->loadColumn();


	        $tmp = [];
			foreach ($categories as $category) {
				if (!empty($category)) {
					$tmp[] = ['value' => $category, 'label' => $category];
				}
	        }
			$categories = $tmp;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting program categories : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getYearsByProgram($code) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('tu.schoolyear'))
            ->from($db->quoteName('#__emundus_setup_programmes', 'p'))
            ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 'tu').' ON '.$db->quoteName('tu.code').' LIKE '.$db->quoteName('p.code'))
            ->where($db->quoteName('p.code').' = '.$code)
            ->orders('tu.id DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting teaching unities of the program ' . $code . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getuserstoaffect($group) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $notinlist = array();

        try {
            $query->select('us.id')
                ->from($db->quoteName('#__emundus_groups', 'g'))
                ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('g.user_id').' = '.$db->quoteName('us.id'))
                ->where($db->quoteName('g.group_id').' = ' . $db->quote($group))
                ->andWhere($db->quoteName('us.id').' != 95')
                ->group('us.id');
            $db->setQuery($query);
            $usersinprogram = $db->loadObjectList();

            if (!empty($usersinprogram)) {
                foreach ($usersinprogram as $user) {
                    $notinlist[] = $user->id;
                }

                $not_conditions = array(
                    $db->quoteName('eus.user_id') .
                    ' NOT IN (' .
                    implode(", ", array_values($notinlist)) .
                    ')'
                );

                $query->clear()
                    ->select(['us.id AS id, us.name AS name, us.email AS email'])
                    ->from($db->quoteName('#__emundus_users', 'eus'))
                    ->leftJoin($db->quoteName('#__users', 'us') .
                        ' ON ' .
                        $db->quoteName('eus.user_id') . ' = ' . $db->quoteName('us.id'))
                    ->where($not_conditions)
                    ->andWhere($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                    ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                    ->andWhere($db->quoteName('eus.profile') . ' IN (5,6)');
                $db->setQuery($query);
                $users = $db->loadObjectList();
            } else {
                $users = $this->getuserswithoutapplicants();
            }

            return $users;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting users that can be affected to the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getuserstoaffectbyterm($group,$term) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $notinlist = array();

        try {
            $query->select('us.id')
                ->from($db->quoteName('#__emundus_groups', 'g'))
                ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('g.user_id').' = '.$db->quoteName('us.id'))
                ->where($db->quoteName('g.group_id').' = ' . $db->quote($group))
                ->andWhere($db->quoteName('us.id').' != 95')
                ->group('us.id');
            $db->setQuery($query);
            $usersinprogram = $db->loadObjectList();

            $searchName = $db->quoteName('us.name') . ' LIKE ' . $db->quote('%' . $term . '%');
            $searchEmail = $db->quoteName('us.email') . ' LIKE ' . $db->quote('%' . $term . '%');
            $fullSearch = $searchName . ' OR ' . $searchEmail;

            if (!empty($usersinprogram)) {
                foreach ($usersinprogram as $user) {
                    $notinlist[] = $user->id;
                }

                $not_conditions = array(
                    $db->quoteName('eus.user_id') .
                    ' NOT IN (' .
                    implode(", ", array_values($notinlist)) .
                    ')'
                );

                $query->clear()
                    ->select(['us.id AS id, us.name AS name, us.email AS email'])
                    ->from($db->quoteName('#__emundus_users','eus'))
                    ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
                    ->where($not_conditions)
                    ->andWhere($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                    ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                    ->andWhere($db->quoteName('eus.profile') . ' IN (5,6)')
                    ->andWhere($fullSearch);
            } else {
                $query->select(['us.id AS id, us.name AS name, us.email AS email'])
                    ->from($db->quoteName('#__emundus_users','eus'))
                    ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
                    ->where($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                    ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                    ->andWhere($db->quoteName('eus.profile') . ' IN (5,6)')
                    ->andWhere($fullSearch);
            }

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting users that can be affected to the group with a search term ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getManagers($group) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['us.id as id','us.name as name','us.email as email'])
            ->from($db->quoteName('#__emundus_groups', 'g'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('g.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('g.group_id').' = ' . $db->quote($group))
            ->andWhere($db->quoteName('us.id').' != 95')
            ->group('us.id');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting administrators of the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getEvaluators($group) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['us.id as id','us.name as name','us.email as email'])
            ->from($db->quoteName('#__emundus_groups', 'g'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('g.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('g.group_id').' = ' . $db->quote($group))
            ->group('us.id');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting evaluators of the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function affectusertogroups($group, $email, $prog_group) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id')
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('email').' = ' . $db->quote($email));
            $db->setQuery($query);
            $uid = $db->loadResult();

            $query->clear()
                ->insert($db->quoteName('#__emundus_groups'))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                ->set($db->quoteName('group_id') . ' = ' . $db->quote($group));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->insert($db->quoteName('#__emundus_groups'))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                ->set($db->quoteName('group_id') . ' = ' . $db->quote($prog_group));
            $db->setQuery($query);
            $db->execute();

            return $uid;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Cannot affect the user ' . $email . ' to the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function affectuserstogroup($group, $users, $prog_group) {
        foreach ($users as $user) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            try {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_groups'))
                    ->set($db->quoteName('user_id') . ' = ' . $user)
                    ->set($db->quoteName('group_id') . ' = ' . $group);
                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->insert($db->quoteName('#__emundus_groups'))
                    ->set($db->quoteName('user_id') . ' = ' . $user)
                    ->set($db->quoteName('group_id') . ' = ' . $prog_group);
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/program | Cannot affect users to the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function removefromgroup($userid, $group, $prog_group) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        try {
            $query->delete($db->quoteName('#__emundus_groups'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
                ->andWhere($db->quoteName('group_id') . ' = ' . $db->quote($group));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->delete($db->quoteName('#__emundus_groups'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
                ->andWhere($db->quoteName('group_id') . ' = ' . $db->quote($prog_group));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Cannot remove user ' . $userid . ' from the group ' . $group . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getusers($filters, $page = null) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $limit = 10;

        if ($page == null) {
            $offset = 0;
        } else {
            $offset = (intval($page) - 1) * $limit;
        }

        $user = JFactory::getUser()->id;

        $block_conditions = $db->quoteName('block') . ' = ' . $db->quote(0);
        if($filters['block'] == 'true'){
            $block_conditions = $db->quoteName('block') . ' = ' . $db->quote(0) . ' OR ' . $db->quote(1);
        }

        if($filters['searchProgram'] != -1){
            $query->select('sgr.parent_id AS parent_id')
                ->from($db->quoteName('#__emundus_setup_programmes','sp'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course','sgr').' ON '.$db->quoteName('sp.code').' LIKE '.$db->quoteName('sgr.course'))
                ->where($db->quoteName('sp.id') . ' = ' . $filters['searchProgram']);
            $db->setQuery($query);
            $group = $db->loadObject()->parent_id;

            $query->clear()
                ->select('us.id as id, us.name as name, us.email as email, us.registerDate as registerDate, us.lastvisitDate as lastvisitDate, us.block as block, eus.profile as profile')
                ->from($db->quoteName('#__users','us'))
                ->leftJoin($db->quoteName('#__emundus_groups','g').' ON '.$db->quoteName('us.id').' = '.$db->quoteName('g.user_id'))
                ->leftJoin($db->quoteName('#__emundus_users','eus').' ON '.$db->quoteName('us.id').' = '.$db->quoteName('eus.user_id'));

            if($filters['searchRole'] != -1){
                $query->where($db->quoteName('eus.profile') . ' = ' . $db->quote($filters['searchRole']));
            }
            $query->where($db->quoteName('g.group_id') . ' = ' . $db->quote($group))
                ->andWhere($db->quoteName('us.id') . ' != ' . $db->quote($user))
                ->andWhere($db->quoteName('us.id') . ' != 62')
                ->andWhere($db->quoteName('eus.profile') . ' IN (5,6)')
                ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                ->andWhere($block_conditions);
        } else {
            $query->select('us.id as id, us.name as name, us.email as email, us.registerDate as registerDate, us.lastvisitDate as lastvisitDate, us.block as block, eus.profile as profile')
                ->from($db->quoteName('#__users','us'))
                ->leftJoin($db->quoteName('#__emundus_users','eus').' ON '.$db->quoteName('us.id').' = '.$db->quoteName('eus.user_id'));

            if($filters['searchRole'] != -1){
                $query->where($db->quoteName('eus.profile') . ' = ' . $db->quote($filters['searchRole']));
            }
            $query->where($db->quoteName('us.id') . ' != ' . $db->quote($user))
                ->andWhere($db->quoteName('us.id') . ' != 62')
                ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                ->andWhere($db->quoteName('eus.profile') . ' IN (5,6)')
                ->andWhere($block_conditions);
        }

        try {
            $db->setQuery($query);
            $users_count = count($db->loadObjectList());
            $db->setQuery($query, $offset, $limit);
            $users = $db->loadObjectList();
            return array(
                'users' => $users,
                'users_count' => $users_count,
            );
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting users : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

    /**
     *
     * @return array|mixed
     *
     * @since version 1.0
     */
    function getuserswithoutapplicants() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['us.id AS id, us.name AS name, us.email AS email'])
            ->from($db->quoteName('#__emundus_users','eus'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('eus.user_id') . ' != 62')
            ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
            ->andWhere($db->quoteName('eus.profile') . ' IN (5,6)');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting users without applicants : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function searchuserbytermwithoutapplicants($term) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $searchName = $db->quoteName('us.name') . ' LIKE ' . $db->quote('%' . $term . '%');
        $searchEmail = $db->quoteName('us.email') . ' LIKE ' . $db->quote('%' . $term . '%');
        $fullSearch = $searchName . ' OR ' . $searchEmail;

        $query->select(['us.id AS id, us.name AS name, us.email AS email'])
            ->from($db->quoteName('#__emundus_users','eus'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('eus.user_id') . ' != 62')
            ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
            ->andWhere($fullSearch);

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting users by term without applicants : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function updateVisibility($cid,$gid,$visibility) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('sg.id AS id')
            ->from($db->quoteName('#__emundus_setup_campaigns','c'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'gc'). ' ON '. $db->quoteName('c.training') . ' LIKE ' . $db->quoteName('gc.course'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg'). ' ON '. $db->quoteName('gc.parent_id') . ' = ' . $db->quoteName('sg.id'))
            ->where($db->quoteName('c.id') . ' = ' . $db->quote($cid))
            ->andWhere($db->quoteName('sg.description') . ' LIKE ' . $db->quote('constraint_group'));
        $db->setQuery($query);
        $group_prog_id = $db->loadObject();

        if ($group_prog_id == null) {
            $query->clear()
                ->select('gc.parent_id AS id')
                ->from($db->quoteName('#__emundus_setup_campaigns','c'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'gc'). ' ON '. $db->quoteName('c.training') . ' LIKE ' . $db->quoteName('gc.course'))
                ->where($db->quoteName('c.id') . ' = ' . $db->quote($cid));
            $db->setQuery($query);
            $old_group = $db->loadObject();

            $constraintgroupid = $this->clonegroup($old_group->id);
        } else {
            $constraintgroupid = $group_prog_id->id;
        }

        $query->clear()
            ->select('count(*)')
            ->from($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($constraintgroupid));
        $db->setQuery($query);
        $groups_constraints = $db->loadResult();

        if ($groups_constraints == 0) {
            $query->clear()
                ->select('sf.profile_id')
                ->from($db->quoteName('#__fabrik_formgroup','ffg'))
                ->leftJoin($db->quoteName('#__fabrik_lists', 'fl'). ' ON '. $db->quoteName('fl.form_id') . ' = ' . $db->quoteName('ffg.form_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_formlist', 'sf'). ' ON '. $db->quoteName('sf.form_id') . ' = ' . $db->quoteName('fl.id'))
                ->where($db->quoteName('ffg.group_id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $profile_id = $db->loadResult();

            $query->clear()
                ->select('fl.form_id')
                ->from($db->quoteName('#__emundus_setup_formlist','sf'))
                ->leftJoin($db->quoteName('#__fabrik_lists', 'fl'). ' ON '. $db->quoteName('fl.id') . ' = ' . $db->quoteName('sf.form_id'))
                ->where($db->quoteName('sf.profile_id') . ' = ' . $db->quote($profile_id));
            $db->setQuery($query);
            $forms = $db->loadObjectList();

            foreach ($forms as $form) {
                $query->clear()
                    ->select('group_id')
                    ->from($db->quoteName('#__fabrik_formgroup'))
                    ->where($db->quoteName('form_id') . ' = ' . $db->quote($form->form_id));
                $db->setQuery($query);
                $groups = $db->loadObjectList();

                foreach ($groups as $group) {
                    if ($gid != $group->group_id) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                            ->set($db->quoteName('parent_id') . ' = ' . $db->quote($constraintgroupid))
                            ->set($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($group->group_id));
                        try {
                            $db->setQuery($query);
                            $db->execute();
                        } catch (Exception $e) {
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                        }

                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                            ->set($db->quoteName('parent_id') . ' = 2')
                            ->set($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($group->group_id));
                        try {
                            $db->setQuery($query);
                            $db->execute();
                        } catch (Exception $e) {
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                        }
                    }
                }
            }
        } else {
            if ($visibility == 'false') {
                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($constraintgroupid))
                    ->andWhere($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($gid));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                }

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                    ->where($db->quoteName('parent_id') . ' = 2')
                    ->andWhere($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($gid));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                }
            } else {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                    ->set($db->quoteName('parent_id') . ' = ' . $db->quote($constraintgroupid))
                    ->set($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($gid));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                }

                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                    ->set($db->quoteName('parent_id') . ' = 2')
                    ->set($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($gid));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
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
    function clonegroup($gid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get programme code and group to clone
        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_groups'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($gid));
        $db->setQuery($query);
        $grouptoclone = $db->loadObject();

        $query->clear()
            ->insert($db->quoteName('#__emundus_setup_groups'))
            ->set($db->quoteName('label') . ' = ' . $db->quote($grouptoclone->label))
            ->set($db->quoteName('description') . ' = ' . $db->quote('constraint_group'))
            ->set($db->quoteName('published') . ' = 1')
            ->set($db->quoteName('class') . ' = ' . $db->quote('label-default'));

        try {
            $db->setQuery($query);
            $db->execute();
            $newgroup = $db->insertid();

            $query->select('*')
                ->from($db->quoteName('#__emundus_setup_groups_repeat_course'))
                ->where($db->quoteName('parent_id') . ' = ' . $db->quote($gid));
            $db->setQuery($query);
            $groupcoursetoclone = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__emundus_setup_groups_repeat_course'));
            foreach ($groupcoursetoclone as $key => $val) {
                if ($key != 'id' && $key != 'parent_id') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'parent_id') {
                    $query->set($key . ' = ' . $db->quote($newgroup));
                }
            }
            try {
                $db->setQuery($query);
                $db->execute();

                $evalutorstomove = $this->getEvaluators($gid);

                foreach ($evalutorstomove as $evalutortomove) {
                    $query->clear()
                        ->update($db->quoteName('#__emundus_groups'))
                        ->set($db->quoteName('group_id') . ' = ' . $db->quote($newgroup))
                        ->where($db->quoteName('group_id') . ' = ' . $db->quote($gid))
                        ->andWhere($db->quoteName('user_id') . ' = ' . $db->quote($evalutortomove->id));
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
                    }
                }

                return $newgroup;
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        } catch (Exception $e) {
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
    function getEvaluationGrid($pid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('fabrik_group_id')
                ->from($db->quoteName('#__emundus_setup_programmes'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            $fabrik_groups = explode(',', $db->loadResult());

            $query->clear()
                ->select('form_id')
                ->from($db->quoteName('#__fabrik_formgroup'))
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($fabrik_groups[0]));
            $db->setQuery($query);

            return  $db->loadResult();

        } catch (Exception $e){
            JLog::add('component/com_emundus/models/program | Error at getting evaluation grid of the program ' . $pid . ': ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function affectGroupToProgram($group, $pid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query
                ->select('fabrik_group_id')
                ->from($db->quoteName('#__emundus_setup_programmes'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            $program_groups = $db->loadResult();

            if ($program_groups == '') {
                $program_groups = $group;
            } else {
                $program_groups = $program_groups . ',' . $group;
            }

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_programmes'))
                ->set($db->quoteName('fabrik_group_id') . ' = ' . $db->quote($program_groups))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/program | Cannot affect fabrik_group ' . $group . ' to program ' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function deleteGroupFromProgram($group, $pid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('fabrik_group_id')
                ->from($db->quoteName('#__emundus_setup_programmes'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            $program_groups = $db->loadResult();

            $program_groups = str_replace($group, '', $program_groups);
            $program_groups = str_replace(',,', ',', $program_groups);

            var_dump(strrpos($program_groups, ','));
            var_dump(strlen($program_groups));

            if (strrpos($program_groups, ',') == (strlen($program_groups) - 1)) {
                $program_groups = substr($program_groups, 0, -1);
            }

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_programmes'))
                ->set($db->quoteName('fabrik_group_id') . ' = ' . $db->quote($program_groups))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/program | Cannot remove fabrik_group ' . $group . ' from the program ' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $label
     * @param $intro
     * @param $model
     * @param $pid
     * @return false|mixed
     *
     * @since version 1.0
     */
    function createGridFromModel($label, $intro, $model, $pid) {
        // Prepare Fabrik API
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        $form->setId(intval($model));
        $groups	= $form->getGroups();

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_files = array();
        $Content_Folder = array();
        $languages = JLanguageHelper::getLanguages();
        foreach ($languages as $language) {
            $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
            $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');

        $formbuilder = new EmundusModelFormbuilder;

        $new_groups = [];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__fabrik_forms')
            ->where($db->quoteName('id') . ' = ' . $db->quote($model));
        $db->setQuery($query);
        $form_model = $db->loadObject();

        $query->clear();
        $query->insert($db->quoteName('#__fabrik_forms'));
        foreach ($form_model as $key => $val) {
            if ($key != 'id') {
                $query->set($key . ' = ' . $db->quote($val));
            }
        }
        try {
            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            // Update translation files
            $query->clear();
            $query->update($db->quoteName('#__fabrik_forms'));

            $formbuilder->translate('FORM_' . $pid. '_' . $formid,$label,'fabrik_forms',$formid,'label');
            $formbuilder->translate('FORM_' . $pid . '_INTRO_' . $formid,$intro,'fabrik_forms',$formid,'intro');
            //

            $query->set('label = ' . $db->quote('FORM_' . $pid . '_' . $formid));
            $query->set('intro = ' . $db->quote('<p>' . 'FORM_' . $pid . '_INTRO_' . $formid . '</p>'));
            $query->where('id =' . $formid);
            $db->setQuery($query);
            $db->execute();
            //

            $query->clear()
                ->select('*')
                ->from('#__fabrik_lists')
                ->where($db->quoteName('form_id') . ' = ' . $db->quote($model));
            $db->setQuery($query);
            $list_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_lists'));
            foreach ($list_model as $key => $val) {
                if ($key != 'id' && $key != 'form_id') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'form_id') {
                    $query->set($key . ' = ' . $db->quote($formid));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $newlistid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_lists'));
            $query->set('label = ' . $db->quote('FORM_' . $pid . '_' . $formid));
            $query->set('introduction = ' . $db->quote('<p>' . 'FORM_' . $pid . '_INTRO_' . $formid . '</p>'));
            $query->where('id =' . $db->quote($newlistid));
            $db->setQuery($query);
            $db->execute();

            // Duplicate group
            $ordering = 0;
            foreach ($groups as $group) {
                $ordering++;
                $properties = $group->getGroupProperties($group->getFormModel());
                $elements = $group->getMyElements();

                $query->clear()
                    ->select('*')
                    ->from('#__fabrik_groups')
                    ->where($db->quoteName('id') . ' = ' . $db->quote($properties->id));
                $db->setQuery($query);
                $group_model = $db->loadObject();

                $query->clear();
                $query->insert($db->quoteName('#__fabrik_groups'));
                foreach ($group_model as $key => $val) {
                    if ($key != 'id') {
                        $query->set($key . ' = ' . $db->quote($val));
                    }
                }
                $db->setQuery($query);
                $db->execute();
                $newgroupid = $db->insertid();
                $new_groups[] = $newgroupid;

                // Update translation files
                $query->clear();
                $query->update($db->quoteName('#__fabrik_groups'));

                $labels_to_duplicate = array(
                    'fr' => $formbuilder->getTranslation($group_model->label, 'fr-FR'),
                    'en' => $formbuilder->getTranslation($group_model->label, 'en-GB')
                );
                if($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
                    $labels_to_duplicate = array(
                        'fr' => $group_model->label,
                        'en' => $group_model->label
                    );
                }
                $formbuilder->translate('GROUP_' . $formid . '_' . $newgroupid,$labels_to_duplicate,'fabrik_groups',$newgroupid,'label');

                $query->set('label = ' . $db->quote('GROUP_' . $formid . '_' . $newgroupid));
                $query->set('name = ' . $db->quote('GROUP_' . $formid . '_' . $newgroupid));
                $query->where('id =' . $newgroupid);
                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->insert($db->quoteName('#__fabrik_formgroup'))
                    ->set('form_id = ' . $db->quote($formid))
                    ->set('group_id = ' . $db->quote($newgroupid))
                    ->set('ordering = ' . $db->quote($ordering));
                $db->setQuery($query);
                $db->execute();

                foreach ($elements as $element) {
                    try {
                        // Default parameters
                        $dbtype = 'VARCHAR(255)';
                        $dbnull = 'NULL';
                        //

                        $newelement = $element->copyRow($element->element->id, '%s', $newgroupid);
                        //add to array
                        $newElementArray[] =$newelement->id;

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
                        if(($element->element->plugin === 'checkbox' || $element->element->plugin === 'radiobutton' || $element->element->plugin === 'dropdown') && $el_params->sub_options){
                            $sub_labels = [];
                            foreach ($el_params->sub_options->sub_labels as $index => $sub_label) {
                                $labels_to_duplicate = array(
                                    'fr' => $formbuilder->getTranslation($sub_label, 'fr-FR'),
                                    'en' => $formbuilder->getTranslation($sub_label, 'en-GB')
                                );
                                if($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
                                    $labels_to_duplicate = array(
                                        'fr' => $sub_label,
                                        'en' => $sub_label
                                    );
                                }
                                $formbuilder->translate('SUBLABEL_' . $newgroupid. '_' . $newelementid . '_' . $index,$labels_to_duplicate,'fabrik_elements',$newelementid,'sub_labels');
                                $sub_labels[] = 'SUBLABEL_' . $newgroupid . '_' . $newelementid . '_' . $index;
                            }
                            $el_params->sub_options->sub_labels = $sub_labels;
                        }
                        $query->clear();
                        $query->update($db->quoteName('#__fabrik_elements'));

                        $labels_to_duplicate = array(
                            'fr' => $formbuilder->getTranslation($element->element->label, 'fr-FR'),
                            'en' => $formbuilder->getTranslation($element->element->label, 'en-GB')
                        );
                        if($labels_to_duplicate['fr'] == false && $labels_to_duplicate['en'] == false) {
                            $labels_to_duplicate = array(
                                'fr' => $element->element->label,
                                'en' => $element->element->label
                            );
                        }
                        $formbuilder->translate('ELEMENT_' . $newgroupid . '_' . $newelementid,$labels_to_duplicate,'fabrik_elements',$newelementid,'label');
                        //

                        $query->set('label = ' . $db->quote('ELEMENT_' . $newgroupid . '_' . $newelementid));
                        $query->set('name = ' . $db->quote('criteria_' . $formid . '_' . $newelementid));
                        $query->set('params = ' . $db->quote(json_encode($el_params)));
                        $query->where('id =' . $newelementid);
                        $db->setQuery($query);
                        $db->execute();

                        if ($element->element->plugin === 'birthday') {
                            $dbtype = 'DATE';
                        } elseif ($element->element->plugin === 'textarea') {
                            $dbtype = 'TEXT';
                        }

                        $query = "ALTER TABLE jos_emundus_evaluations" . " ADD criteria_" . $formid . "_" . $newelementid . " " . $dbtype . " " . $dbnull;
                        $db->setQuery($query);
                        $db->execute();
                        $query = $db->getQuery(true);
                    } catch (Exception $e) {
                        JLog::add('component/com_emundus/models/program | Cannot create a grid from the model ' . $model . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                        return false;
                    }
                }

                // publish new elements. It is outside the foreach so we can publish the skipped elements
                $query
                    ->clear()
                    ->update($db->quoteName('#__fabrik_elements'))
                    ->set('published =  1')
                    ->where('id IN (' . implode(',', $newElementArray). ')');
                $db->setQuery($query);
                $db->execute();
            }
            //

            // Link groups to programme
            $query->clear()
                ->update($db->quoteName('#__emundus_setup_programmes'))
                ->set($db->quoteName('fabrik_group_id') . ' = ' . $db->quote(implode(',',$new_groups)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            return $db->execute();

        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/program | Cannot create a grid from the model ' . $model . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     *
     * @return array|false|mixed
     *
     * @since version 1.0
     */
    function getGridsModel() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_template_evaluation'))
            ->order('form_id');

        try {
            $db->setQuery($query);
            $models = $db->loadObjectList();

            foreach ($models as $model) {
                $model->label = JText::_($model->label);
                $model->intro = JText::_(strip_tags($model->intro));
            }

            return $models;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting evaluation models : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function createGrid($label, $intro, $pid, $template) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formbuilder.php');

        $formbuilder = new EmundusModelFormbuilder;

        try {
            // INSERT FABRIK_FORMS
            $query->clear()
                ->select('*')
                ->from('#__fabrik_forms')
                ->where($db->quoteName('id') . ' = 270');
            $db->setQuery($query);
            $form_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_forms'));
            foreach ($form_model as $key => $val) {
                if ($key != 'id') {
                    $query->set($key . ' = ' . $db->quote($val));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $formid = $db->insertid();

            $query->clear()
                ->update($db->quoteName('#__fabrik_forms'))
                ->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $pid . '_' . $formid))
                ->set($db->quoteName('intro') . ' = ' . $db->quote('<p>' . 'FORM_' . $pid . '_INTRO_' . $formid . '</p>'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($formid));

            $db->setQuery($query);
            $db->execute();

            $formbuilder->translate('FORM_' . $pid. '_' . $formid,$label,'fabrik_forms',$formid,'label');
            $formbuilder->translate('FORM_' . $pid. '_INTRO_' . $formid,$intro,'fabrik_forms',$formid,'intro');
            //

            // INSERT FABRIK LIST
            $query->clear()
                ->select('*')
                ->from('#__fabrik_lists')
                ->where($db->quoteName('form_id') . ' = 270');
            $db->setQuery($query);
            $list_model = $db->loadObject();

            $query->clear();
            $query->insert($db->quoteName('#__fabrik_lists'));
            foreach ($list_model as $key => $val) {
                if ($key != 'id' && $key != 'form_id') {
                    $query->set($key . ' = ' . $db->quote($val));
                } elseif ($key == 'form_id') {
                    $query->set($key . ' = ' . $db->quote($formid));
                }
            }
            $db->setQuery($query);
            $db->execute();
            $listid = $db->insertid();

            $query->clear();
            $query->update($db->quoteName('#__fabrik_lists'));

            $query->set('label = ' . $db->quote('FORM_' . $pid . '_' . $formid));
            $query->set('access = ' . $db->quote($pid));
            $query->where($db->quoteName('id') . ' = ' . $db->quote($listid));
            $db->setQuery($query);
            $db->execute();
            //

            //$formbuilder->createHiddenGroup($formid,1);
            $group = $formbuilder->createGroup($label,$formid);

            // Link groups to program
            $this->affectGroupToProgram($group['group_id'],$pid);
            //

            // Save as template
            if ($template == 'true') {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_template_evaluation'))
                    ->set($db->quoteName('form_id') . ' = ' . $db->quote($formid))
                    ->set($db->quoteName('label') . ' = ' . $db->quote('FORM_' . $pid. '_' . $formid))
                    ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')));
                $db->setQuery($query);
                $db->execute();
            }
            //

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/program | Cannot create a grid in the program' . $pid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function deleteGrid($grid,$pid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__emundus_setup_programmes'))
            ->set($db->quoteName('fabrik_group_id') . ' = NULL')
            ->where($db->quoteName('id') . ' = ' . $pid);

        try {
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->update($db->quoteName('#__fabrik_forms'))
                ->set($db->quoteName('published') . ' = 0')
                ->where($db->quoteName('id') . ' = ' . $grid);

            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/program | Error at delete the grid ' . $grid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getUserPrograms($user_id) {
        $user_programs = [];

        if (!empty($user_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('distinct sp.code')
                ->from($db->quoteName('#__emundus_groups','g'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg'). ' ON '. $db->quoteName('g.group_id').' = '.$db->quoteName('sg.id'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr'). ' ON '. $db->quoteName('sg.id').' = '.$db->quoteName('sgr.parent_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp'). ' ON '. $db->quoteName('sgr.course').' = '.$db->quoteName('sp.code'))
                ->where($db->quoteName('g.user_id') . ' = ' . $db->quote($user_id));
            $db->setQuery($query);

            try {
                $programs = $db->loadObjectList();

                $progs = [];
                foreach ($programs as $program) {
                    if ($program->code != null) {
                        $progs[] = $program->code;
                    }
                }

                $user_programs = $progs;
            }  catch (Exception $e) {
                JLog::add('component/com_emundus/models/program | Error at getting programs of the user ' . $user_id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getGroupsByPrograms($programs) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $groups = array();

        try {
            foreach ($programs as $id => $program) {
                if ($program == 'true') {
                    $query->clear()
                        ->select('code')
                        ->from($db->quoteName('#__emundus_setup_programmes'))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($id));
                    $db->setQuery($query);
                    $code = $db->loadResult();
                    $groups[$id] = new stdClass();
                    $groups[$id]->manager = $this->getGroupByParent($code, 3);
                    $groups[$id]->evaluator = $this->getGroupByParent($code, 2);

                    $query->clear()
                        ->select('sg.id')
                        ->from($db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr'))
                        ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg') . ' ON ' . $db->quoteName('sgr.parent_id') . ' = ' . $db->quoteName('sg.id'))
                        ->where($db->quoteName('sgr.course') . ' = ' . $db->quote($code))
                        ->andWhere($db->quoteName('sg.parent_id') . ' IS NULL');
                    $db->setQuery($query);
                    $groups[$id]->prog = $db->loadResult();
                }
            }

            return $groups;
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/program | Error at getting groups of programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function addGroupToProgram($label,$code,$parent) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $date = date('Y-m-d H:i:s');
        $glabel = 'Evaluateurs_' . $label;
        $class = 'label-default';
        if($parent == 3){
            $glabel = 'Gestionnaire de programme_' . $label;
            $class = 'label-lightgreen';
        }

        try {
            // Create user group
            $query->insert($db->quoteName('#__emundus_setup_groups'))
                ->set($db->quoteName('label') . ' = ' . $db->quote($glabel))
                ->set($db->quoteName('published') . ' = 1')
                ->set($db->quoteName('class') . ' = ' . $db->quote($class))
                ->set($db->quoteName('anonymize') . ' = ' . $db->quote(0))
                ->set($db->quoteName('parent_id') . ' = ' . $db->quote($parent));
            $db->setQuery($query);
            $db->execute();
            $group_id = $db->insertid();
            //

            // Link group with programme
            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
                ->set($db->quoteName('parent_id') . ' = ' . $group_id)
                ->set($db->quoteName('course') . ' = ' . $db->quote($code));
            $db->setQuery($query);
            $db->execute();
            //

            // Duplicate group_rights
            $query->clear()
                ->select('*')
                ->from('#__emundus_acl')
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($parent));
            $db->setQuery($query);
            $acl_models = $db->loadObjectList();

            foreach ($acl_models as $acl_model) {
                $query->clear();
                $query->insert($db->quoteName('#__emundus_acl'));
                foreach ($acl_model as $key => $val) {
                    if ($key != 'id' && $key != 'group_id' && $key != 'time_date') {
                        $query->set($key . ' = ' . $db->quote($val));
                    } elseif ($key == 'group_id') {
                        $query->set($key . ' = ' . $db->quote($group_id));
                    } elseif ($key == 'time_date') {
                        $query->set($key . ' = ' . $db->quote($date));
                    }
                }
                $db->setQuery($query);
                $db->execute();
            }
            //

            return true;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Cannot add the group ' . $parent . ' to the program ' . $code . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getGroupByParent($code,$parent){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('sg.id')
                ->from ($db->quoteName('#__emundus_setup_groups_repeat_course','sgr'))
                ->leftJoin($db->quoteName('#__emundus_setup_groups','sg').' ON '.$db->quoteName('sgr.parent_id').' = '.$db->quoteName('sg.id'))
                ->where($db->quoteName('sgr.course') . ' = '. $db->quote($code))
                ->andWhere($db->quoteName('sg.parent_id') . ' = ' . $db->quote($parent));
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting groups by parent ' . $parent . ' of the program ' . $code . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
    function getCampaignsByProgram($program){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('c.*')
                ->from($db->quoteName('#__emundus_setup_campaigns','c'))
                ->leftJoin($db->quoteName('#__emundus_setup_programmes','sg').' ON '.$db->quoteName('sg.code').' = '.$db->quoteName('c.training'))
                ->where($db->quoteName('sg.id') . ' = '. $db->quote($program));
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting campaigns by program ' . $program . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getAllSessions(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('distinct year')
                ->from($db->quoteName('#__emundus_setup_campaigns'));
            $db->setQuery($query);
            return $db->loadColumn();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/program | Error at getting sessions : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return [];
        }
    }

}
