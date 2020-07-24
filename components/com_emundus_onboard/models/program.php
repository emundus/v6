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

class EmundusonboardModelprogram extends JModelList {

    function getAllPrograms($lim, $page, $filter, $sort, $recherche) {
        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->getUserPrograms($user->id);
        //

        if (empty($lim)) {
            $limit = 25;
        } else {
            $limit = $lim;
        }

        if (empty($page)) {
            $offset = 0;
        } else {
            $offset = ($page-1) * $limit;
        }

        if (empty($sort)) {
            $sort = 'DESC';
        }

        $sortDb = 'p.id ';

        $db = $this->getDbo();
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
            ->andWhere($db->quoteName('p.code') . ' IN (' . implode(',',$db->quote($programs)) . ')')
            ->group($sortDb)
            ->order($sortDb.$sort);

        try {
            $db->setQuery($query, $offset, $limit);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return new stdClass();
        }
    }

    function getProgramCount($filter, $recherche) {
        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->getUserPrograms($user->id);
        //

        $db = $this->getDbo();
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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return 0;
        }
    }

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
            ->select('parent_id')
            ->from ($db->quoteName('#__emundus_setup_groups_repeat_course'))
            ->where($db->quoteName('course') . ' = '. $db->quote($programme->code));
        $db->setQuery($query);
        $prog_group = $db->loadResult();

        $programme->group = $prog_group;

        try {
            return $programme;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function addProgram($data) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (array_values($data)[1] == "") {
            $data = null;
        }

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
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_groups'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($programme->label))
                    ->set($db->quoteName('published') . ' = 1')
                    ->set($db->quoteName('class') . ' = ' . $db->quote('label-default'));
                $db->setQuery($query);
                $db->execute();
                $group_id = $db->insertid();
                //

                // Link group with programme
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
                    ->set($db->quoteName('parent_id') . ' = ' . $group_id)
                    ->set($db->quoteName('course') . ' = ' . $db->quote($programme->code));
                $db->setQuery($query);
                $db->execute();
                //

                // Affect coordinator to manager group of the program
                $this->affectuserstomanagergroup($group_id,[95]);
                //

                return $prog_id;
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }

        } else {
            return false;
        }
    }

    /**
     * @param   int $id the program to update
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Update program in DB
     */
    public function updateProgram($id, $data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {

            $fields = [];

            foreach ($data as $key => $val) {
                $insert = $db->quoteName($key) . ' = ' . $db->quote($val);
                $fields[] = $insert;
            }

            $query->update($db->quoteName('#__emundus_setup_programmes'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = '.$db->quote($id));

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }

        } else {
            return false;
        }
    }

    /**
     * @param   array $data the row to delete in table.
     *
     * @return boolean
     * Delete program(s) in DB
     */
    public function deleteProgram($data) {

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_onboard'.DS.'models'.DS.'campaign.php');
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {
            try {
                $query->select($db->qn('sc.id'))
                    ->from($db->qn('#__emundus_setup_campaigns', 'sc'))
                    ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sc.training').' LIKE '.$db->quoteName('sp.code'))
                    ->where($db->quoteName('sp.id') . ' IN (' . implode(", ",array_values($data)) . ')');

                $db->setQuery($query);
                $campaigns = $db->loadColumn();

                $m_campaign = new EmundusonboardModelcampaign();

                $m_campaign->deleteCampaign($campaigns);

                $conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')'
                );

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_programmes'))
                    ->where($conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }

        } else {
            return false;
        }
    }

    /**
     * @param   array $data the row to unpublish in table.
     *
     * @return boolean
     * Unpublish program(s) in DB
     */
    public function unpublishProgram($data) {

        $db = $this->getDbo();
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
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
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
     */
    public function publishProgram($data) {

        $db = $this->getDbo();
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
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
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
     */
    public function getProgramCategories() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(programmes)')
            ->from ($db->quoteName('#__emundus_setup_programmes'))
            ->order('id DESC');

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

	/**
	 * get list of all campaigns associated to the user
	 *
	 * @param $code
	 *
	 * @return Object
	 */
    function getYearsByProgram($code) {

        $db = $this->getDbo();
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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return new stdClass();
        }
    }

    function getuserstoaffect($group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $managers = $this->getManagers($group);
        $evaluators = $this->getEvaluators($group);
        $notinlist = array();
        if (!empty($managers) || !empty($evaluators)) {
            foreach ($managers as $manager) {
                $notinlist[] = $manager->id;
            }
            foreach ($evaluators as $evaluator) {
                $notinlist[] = $evaluator->id;
            }

            $not_conditions = array(
                $db->quoteName('eus.user_id') .
                ' NOT IN (' .
                implode(", ", array_values($notinlist)) .
                ')'
            );

            $query->select(['us.id AS id, us.name AS name, us.email AS email'])
                ->from($db->quoteName('#__emundus_users','eus'))
                ->leftJoin($db->quoteName('#__users', 'us').
                    ' ON '.
                    $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
                ->where($not_conditions)
                ->andWhere($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'));
        } else {
            $query->select(['us.id AS id, us.name AS name, us.email AS email'])
                ->from($db->quoteName('#__emundus_users','eus'))
                ->leftJoin($db->quoteName('#__users', 'us').
                    ' ON '.
                    $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
                ->where($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'));
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    function getuserstoaffectbyterm($group,$term) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $managers = $this->getManagers($group);
        $evaluators = $this->getEvaluators($group);
        $notinlist = array();

        $searchName = $db->quoteName('us.name') . ' LIKE ' . $db->quote('%' . $term . '%');
        $searchEmail = $db->quoteName('us.email') . ' LIKE ' . $db->quote('%' . $term . '%');
        $fullSearch = $searchName . ' OR ' . $searchEmail;

        if (!empty($managers) || !empty($evaluators)) {
            foreach ($managers as $manager) {
                $notinlist[] = $manager->id;
            }
            foreach ($evaluators as $evaluator) {
                $notinlist[] = $evaluator->id;
            }

            $not_conditions = array($db->quoteName('eus.user_id') . ' NOT IN (' . implode(", ", array_values($notinlist)) . ')');

            $query->select(['us.id AS id, us.name AS name, us.email AS email'])
                ->from($db->quoteName('#__emundus_users','eus'))
                ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
                ->where($not_conditions)
                ->andWhere($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                ->andWhere($fullSearch);
        } else {
            $query->select(['us.id AS id, us.name AS name, us.email AS email'])
                ->from($db->quoteName('#__emundus_users','eus'))
                ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
                ->where($db->quoteName('eus.user_id') . ' NOT IN (62,95)')
                ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'))
                ->andWhere($fullSearch);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }
    function getManagers($group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(['us.id as id','us.name as name','us.email as email'])
            ->from($db->quoteName('#__emundus_groups', 'g'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('g.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('g.group_id').' = 5')
            ->orWhere($db->quoteName('g.group_id').' = ' . $db->quote($group))
            ->andWhere($db->quoteName('us.id').' != 95')
            ->group('us.id')
            ->having('count(*) > 1');


        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    function getEvaluators($group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(['us.id as id','us.name as name','us.email as email'])
            ->from($db->quoteName('#__emundus_groups', 'g'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('g.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('g.group_id').' = 4')
            ->orWhere($db->quoteName('g.group_id').' = ' . $db->quote($group))
            ->group('us.id')
            ->having('count(*) > 1');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    function affectusertomanagergroups($group, $email) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('email').' = ' . $db->quote($email));
        $db->setQuery($query);
        $uid = $db->loadResult();

        $query->clear()
            ->insert($db->quoteName('#__emundus_groups'))
            ->set($db->quoteName('user_id') . ' = ' . $uid)
            ->set($db->quoteName('group_id') . ' = ' . $group);
        $db->setQuery($query);
        $db->execute();

        $query->clear()
            ->select('count(*)')
            ->from($db->quoteName('#__emundus_groups'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($uid))
            ->andWhere($db->quoteName('group_id') . ' = 5');
        $db->setQuery($query);
        $already_in_manager = $db->loadResult();

        if($already_in_manager == 0) {
            $query->clear()
                ->insert($db->quoteName('#__emundus_groups'))
                ->set($db->quoteName('user_id') . ' = ' . $uid)
                ->set($db->quoteName('group_id') . ' = 5');
        }

        try {
            $db->setQuery($query);
            $db->execute();
            return $uid;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function affectusertoevaluatorgroups($group, $email) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('email').' = ' . $db->quote($email));
        $db->setQuery($query);
        $uid = $db->loadResult();

        $query->clear()
            ->insert($db->quoteName('#__emundus_groups'))
            ->set($db->quoteName('user_id') . ' = ' . $uid)
            ->set($db->quoteName('group_id') . ' = ' . $group);
        $db->setQuery($query);
        $db->execute();

        $query->clear()
            ->select('count(*)')
            ->from($db->quoteName('#__emundus_groups'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($uid))
            ->andWhere($db->quoteName('group_id') . ' = 4');
        $db->setQuery($query);
        $already_in_evaluator = $db->loadResult();

        if($already_in_evaluator == 0) {
            $query->clear()
                ->insert($db->quoteName('#__emundus_groups'))
                ->set($db->quoteName('user_id') . ' = ' . $uid)
                ->set($db->quoteName('group_id') . ' = 4');
        }

        try {
            $db->setQuery($query);
            $db->execute();
            return $uid;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function affectuserstomanagergroup($group, $users) {
        foreach ($users as $user) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->insert($db->quoteName('#__emundus_groups'))
                ->set($db->quoteName('user_id') . ' = ' . $user)
                ->set($db->quoteName('group_id') . ' = ' . $group);
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_groups'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($user))
                ->andWhere($db->quoteName('group_id') . ' = 5');
            $db->setQuery($query);
            $already_in_manager = $db->loadResult();

            if($already_in_manager == 0) {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_groups'))
                    ->set($db->quoteName('user_id') . ' = ' . $user)
                    ->set($db->quoteName('group_id') . ' = 5');
            }

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
        }
        return true;
    }

    function affectuserstoevaluatorgroup($group, $users) {
        foreach ($users as $user) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->insert($db->quoteName('#__emundus_groups'))
                ->set($db->quoteName('user_id') . ' = ' . $user)
                ->set($db->quoteName('group_id') . ' = ' . $group);
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_groups'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($user))
                ->andWhere($db->quoteName('group_id') . ' = 4');
            $db->setQuery($query);
            $already_in_evaluator = $db->loadResult();

            if($already_in_evaluator == 0) {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_groups'))
                    ->set($db->quoteName('user_id') . ' = ' . $user)
                    ->set($db->quoteName('group_id') . ' = 4');
            }

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
        }
        return true;
    }

    function removefrommanagergroup($userid, $group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_groups'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
            ->andWhere($db->quoteName('group_id') . ' = ' . $db->quote($group));

        try {
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_groups'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
                ->andWhere($db->quoteName('group_id') . ' != 5');
            $db->setQuery($query);
            $in_other_group = $db->loadResult();

            if($in_other_group == 0) {
                $query->clear()
                    ->delete($db->quoteName('#__emundus_groups'))
                    ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
                    ->andWhere($db->quoteName('group_id') . ' = 5');
            }

            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function removefromevaluatorgroup($userid, $group) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_groups'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
            ->andWhere($db->quoteName('group_id') . ' = ' . $db->quote($group));

        try {
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->select('count(*)')
                ->from($db->quoteName('#__emundus_groups'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
                ->andWhere($db->quoteName('group_id') . ' != 4');
            $db->setQuery($query);
            $in_other_group = $db->loadResult();

            if($in_other_group == 0) {
                $query->clear()
                    ->delete($db->quoteName('#__emundus_groups'))
                    ->where($db->quoteName('user_id') . ' = ' . $db->quote($userid))
                    ->andWhere($db->quoteName('group_id') . ' = 4');
            }

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function getusers($filters) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $block_conditions = $db->quoteName('block') . ' = ' . $db->quote(0);
        if($filters['block'] == 'true'){
            $block_conditions = $db->quoteName('block') . ' = ' . $db->quote(0) . ' OR ' . $db->quote(1);
        }

        $user = JFactory::getUser()->id;

        $query->select('id, name, email, registerDate, lastvisitDate, block')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' != ' . $db->quote($user))
            ->andWhere($db->quoteName('id') . ' != 62')
            ->andWhere($db->quoteName('username') . ' != ' . $db->quote('sysemundus'))
            ->andWhere($block_conditions);

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    function getuserswithoutapplicants() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(['us.id AS id, us.name AS name, us.email AS email'])
            ->from($db->quoteName('#__emundus_users','eus'))
            ->leftJoin($db->quoteName('#__users', 'us'). ' ON '. $db->quoteName('eus.user_id').' = '.$db->quoteName('us.id'))
            ->where($db->quoteName('eus.user_id') . ' != 62')
            ->andWhere($db->quoteName('us.username') . ' != ' . $db->quote('sysemundus'));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    function searchuserbytermwithoutapplicants($term) {
        $db = $this->getDbo();
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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    function updateVisibility($cid,$gid,$visibility) {
        $db = $this->getDbo();
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
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                        }

                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                            ->set($db->quoteName('parent_id') . ' = 4')
                            ->set($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($group->group_id));
                        try {
                            $db->setQuery($query);
                            $db->execute();
                        } catch (Exception $e) {
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
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
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                }

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                    ->where($db->quoteName('parent_id') . ' = 4')
                    ->andWhere($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($gid));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
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
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                }

                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_groups_repeat_fabrik_group_link'))
                    ->set($db->quoteName('parent_id') . ' = 4')
                    ->set($db->quoteName('fabrik_group_link') . ' = ' . $db->quote($gid));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                }
            }
        }

        return true;
    }

    function clonegroup($gid) {
        $db = $this->getDbo();
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
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    }
                }

                return $newgroup;
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }
    }

    function getEvaluationGrid($pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('fabrik_group_id')
            ->from($db->quoteName('#__emundus_setup_programmes'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
        $db->setQuery($query);
        $fabrik_groups = explode(',',$db->loadResult());

        $query->clear()
            ->select('form_id')
            ->from($db->quoteName('#__fabrik_formgroup'))
            ->where($db->quoteName('group_id') . ' = ' . $db->quote($fabrik_groups[0]));
        $db->setQuery($query);
        return $db->loadResult();
    }

    function affectGroupToProgram($group, $pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

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
    }

    function deleteGroupFromProgram($group, $pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('fabrik_group_id')
            ->from($db->quoteName('#__emundus_setup_programmes'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
        $db->setQuery($query);
        $program_groups = $db->loadResult();

        $program_groups = str_replace($group,'',$program_groups);
        $program_groups = str_replace(',,',',',$program_groups);

        var_dump(strrpos($program_groups,','));
        var_dump(strlen($program_groups));

        if (strrpos($program_groups,',') == (strlen($program_groups) - 1)) {
            $program_groups = substr($program_groups,0,-1);
        }

        $query->clear()
            ->update($db->quoteName('#__emundus_setup_programmes'))
            ->set($db->quoteName('fabrik_group_id') . ' = ' . $db->quote($program_groups))
            ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
        $db->setQuery($query);
        return $db->execute();
    }

    function createGridFromModel($label, $intro, $model, $pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $new_groups = [];

        // Prepare Fabrik API
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
        $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        $form->setId($model);
        $groups	= $form->getGroups();
        //

        // Prepare languages
        $path_to_file = basename(__FILE__) . '/../language/overrides/';
        $path_to_file_fr = $path_to_file . 'fr-FR.override.ini' ;
        $Content_Folder_FR = file_get_contents($path_to_file_fr);
        $path_to_file_en = $path_to_file . 'en-GB.override.ini' ;
        $Content_Folder_EN = file_get_contents($path_to_file_en);

        $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusonboardModel');
        //

        $query->clear()
            ->select('*')
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

            $formbuilder->addTransationFr('FORM_' . $pid. '_' . $formid . '=' . "\"" . $label['fr'] . "\"");
            $formbuilder->addTransationEn('FORM_' . $pid. '_' . $formid . '=' . "\"" . $label['en'] . "\"");
            $formbuilder->addTransationFr('FORM_' . $pid . '_INTRO_' . $formid . '=' . "\"" . $intro['fr'] . "\"");
            $formbuilder->addTransationEn('FORM_' . $pid . '_INTRO_' . $formid . '=' . "\"" . $intro['en'] . "\"");
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
                ->where($db->quoteName('form_id') . ' = ' . $db->quote(270));
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

                $formbuilder->duplicateTranslation($group_model->label, $Content_Folder_FR, $Content_Folder_EN, $path_to_file_fr, $path_to_file_en, 'GROUP_' . $formid . '_' . $newgroupid);
                //

                $query->set('label = ' . $db->quote('GROUP_' . $formid . '_' . $newgroupid));
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
                        $newelement = $element->copyRow($element->element->id, 'Copy of %s', $newgroupid);
                        $newelementid = $newelement->id;

                        // Update translation files
                        $query->clear();
                        $query->update($db->quoteName('#__fabrik_elements'));
                        $formbuilder->duplicateTranslation($element->element->label, $Content_Folder_FR, $Content_Folder_EN, $path_to_file_fr, $path_to_file_en, 'ELEMENT_' . $newgroupid . '_' . $newelementid);
                        //

                        $query->set('label = ' . $db->quote('ELEMENT_' . $newgroupid . '_' . $newelementid));
                        $query->set('published = 1');
                        $query->where('id =' . $newelementid);
                        $db->setQuery($query);
                        $db->execute();
                    } catch (Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                        return false;
                    }
                }
            }
            //

            // Link groups to programme
            $query->clear()
                ->update($db->quoteName('#__emundus_setup_programmes'))
                ->set($db->quoteName('fabrik_group_id') . ' = ' . $db->quote(implode(',',$new_groups)))
                ->where($db->quoteName('id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            return $db->execute();
            //
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function getGridsModel() {
        $db = $this->getDbo();
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
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function createGrid($label, $intro, $pid, $template) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusonboardModel');

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
        try {
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
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
        }

        $formbuilder->addTransationFr('FORM_' . $pid. '_' . $formid . '=' . "\"" . $label['fr'] . "\"");
        $formbuilder->addTransationFr('FORM_' . $pid. '_INTRO_' . $formid . '=' . "\"" . $intro['fr'] . "\"");
        $formbuilder->addTransationEn('FORM_' . $pid. '_' . $formid . '=' . "\"" . $label['en'] . "\"");
        $formbuilder->addTransationEn('FORM_' . $pid. '_INTRO_' . $formid . '=' . "\"" . $intro['en'] . "\"");
        //

        // INSERT FABRIK LIST
        $query->clear()
            ->select('*')
            ->from('#__fabrik_lists')
            ->where($db->quoteName('id') . ' = 279');
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

        $group = $formbuilder->createGroup($label,$formid);

        // Link groups to programme
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
    }

    function getUserPrograms($user_id){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('sp.code')
            ->from($db->quoteName('#__emundus_groups','g'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg'). ' ON '. $db->quoteName('g.group_id').' = '.$db->quoteName('sg.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'sgr'). ' ON '. $db->quoteName('sg.id').' = '.$db->quoteName('sgr.parent_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp'). ' ON '. $db->quoteName('sgr.course').' = '.$db->quoteName('sp.code'))
            ->where($db->quoteName('g.user_id') . ' = ' . $db->quote($user_id));
        $db->setQuery($query);

        try {
            $programs = $db->loadObjectList();

            $progs = [];
            foreach ($programs as $program){
                if($program->code != null){
                    $progs[] = $program->code;
                }
            }

            return $progs;
        }  catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

}
