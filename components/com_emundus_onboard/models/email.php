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

class EmundusonboardModelemail extends JModelList {

     /**
     * @param $user int
     * gets the amount of camapaigns
     * @param int $offset
     * @return integer
     */
     function getEmailCount($filter, $recherche) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if ($filter == 'Publish') {
            $filterCount = $db->quoteName('se.published') . ' = 1';
        } else if ($filter == 'Unpublish') {
            $filterCount = $db->quoteName('se.published') . ' = 0';
        } else {
            $filterCount = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheSubject = $db->quoteName('se.subject') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheMessage = $db->quoteName('se.message') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheEmail = $db->quoteName('se.emailfrom') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheType = $db->quoteName('se.type') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheSubject.' OR '.$rechercheMessage.' OR '.$rechercheEmail.' OR '.$rechercheType;
        }


        $query->select('COUNT(se.id)')
            ->from($db->quoteName('#__emundus_setup_emails', 'se'))
            ->where($filterCount)
            ->where($fullRecherche);

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return 0;
        }

    }

    /**
	 * @return array
	 * get list of declared emails
	 */
     function getAllEmails($lim, $page, $filter, $sort, $recherche) {

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

        $sortDb = 'se.id ';

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if ($filter == 'Publish') {
            $filterDate = $db->quoteName('se.published') . ' = 1';
        } else if ($filter == 'Unpublish') {
            $filterDate = $db->quoteName('se.published') . ' = 0';
        } else {
            $filterDate = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheSubject = $db->quoteName('se.subject') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheMessage = $db->quoteName('se.message') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheEmail = $db->quoteName('se.emailfrom') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheType = $db->quoteName('se.type') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheSubject.' OR '.$rechercheMessage.' OR '.$rechercheEmail.' OR '.$rechercheType;
        }

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_emails', 'se'))
            ->where($filterDate)
            ->where($fullRecherche)
            ->group($sortDb)
            ->order($sortDb.$sort);

        try {
            $db->setQuery($query, $offset, $limit);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }


    /**
     * @param   array $data the row to delete in table.
     *
     * @return boolean
     * Delete email(s) in DB
     */
     public function deleteEmail($data) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            try {
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_emails'))
                    ->where($se_conditions);

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
     * Unpublish email(s) in DB
     */
    public function unpublishEmail($data) {

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
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($db->quoteName('#__emundus_setup_emails'))
                    ->set($fields)
                    ->where($se_conditions);

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
     * Publish email(s) in DB
     */
    public function publishEmail($data) {

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
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query->update($db->quoteName('#__emundus_setup_emails'))
                    ->set($fields)
                    ->where($se_conditions);

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
     * @param array $data the row to copy in table.
     *
     * @return boolean
     * Copy email(s) in DB
     */
    public function duplicateEmail($data) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $columns = array_keys($db->getTableColumns('#__emundus_setup_emails'));

                $columns = array_filter($columns, function($k) {
                    return ($k != 'id' && $k != 'date_time');
                });

                foreach ($data as $id){
                    $query->clear()
                        ->select(implode(',', $db->qn($columns)))
                        ->from($db->quoteName('#__emundus_setup_emails'))
                        ->where($db->quoteName('id') . ' = ' . $id);

                    $db->setQuery($query);
                    $values[] = implode(', ',$db->quote($db->loadRow()));
                }


                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_emails'))
                    ->columns(
                        implode(',', $db->quoteName($columns))
                    )
                    ->values($values);

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
	 * @param $id
	 *
	 * @return array|boolean
	 * get list of declared emails
	 */
     public function getEmailById($id) {

        if (empty($id)) {
	        return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from ($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('id') . ' = '.$id);

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    /**
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Add new email in DB
     */
     public function createEmail($data) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
        	$query->insert($db->quoteName('#__emundus_setup_emails'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

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
     * @param   int $id the email to update
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Update email in DB
     */
    //TODO UPDATE CAMPAIGN AND TU CODE
    public function updateEmail($id, $data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {

            $fields = [];

            foreach ($data as $key => $val) {
                $insert = $db->quoteName($key) . ' = ' . $db->quote($val);
                $fields[] = $insert;
            }

            $query->update($db->quoteName('#__emundus_setup_emails'))
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
     *
     * @return array|boolean
     * get list of declared emails
     */
     public function getEmailTypes() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(type)')
            ->from ($db->quoteName('#__emundus_setup_emails'))
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

    /***
     * @return array|boolean
     * get list of declared emails
     */
     public function getEmailCategories() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(category)')
            ->from ($db->quoteName('#__emundus_setup_emails'))
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

    function getStatus() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_status'))
            ->order('step ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function getTriggersByProgramId($pid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(['DISTINCT(et.id) AS trigger_id','se.subject AS subject','ss.value AS status','ep.profile_id AS profile','et.to_current_user AS candidate','et.to_applicant AS manual'])
            ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id', 'etrp'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_trigger', 'et')
                . ' ON ' .
                $db->quoteName('etrp.parent_id') . ' = ' . $db->quoteName('et.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails', 'se')
                . ' ON ' .
                $db->quoteName('et.email_id') . ' = ' . $db->quoteName('se.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_status', 'ss')
                . ' ON ' .
                $db->quoteName('et.step') . ' = ' . $db->quoteName('ss.step'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id', 'ep')
                . ' ON ' .
                $db->quoteName('et.id') . ' = ' . $db->quoteName('ep.parent_id'))
            ->where($db->quoteName('etrp.programme_id') . ' = ' . $db->quote($pid));

        try {
            $db->setQuery($query);
            $triggers = $db->loadObjectList();

            foreach ($triggers as $trigger) {
                $query->clear()
                    ->select(['us.firstname','us.lastname'])
                    ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','tu'))
                    ->leftJoin($db->quoteName('#__emundus_users', 'us')
                        . ' ON ' .
                        $db->quoteName('tu.user_id') . ' = ' . $db->quoteName('us.user_id'))
                    ->where($db->quoteName('tu.parent_id') . ' = ' . $db->quote($trigger->trigger_id));
                $db->setQuery($query);
                $trigger->users = array_values($db->loadObjectList());
            }

            return $triggers;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }

    function getTriggerById($tid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(['DISTINCT(et.id) AS trigger_id','et.step AS status','et.email_id AS model','ep.profile_id AS target'])
            ->from($db->quoteName('#__emundus_setup_emails_trigger', 'et'))
            ->leftJoin($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id', 'ep')
                . ' ON ' .
                $db->quoteName('et.id') . ' = ' . $db->quoteName('ep.parent_id'))
            ->where($db->quoteName('et.id') . ' = ' . $db->quote($tid));

        try {
            $db->setQuery($query);
            $trigger = $db->loadObject();

            $query->clear()
                ->select('us.user_id')
                ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id','tu'))
                ->leftJoin($db->quoteName('#__emundus_users', 'us')
                    . ' ON ' .
                    $db->quoteName('tu.user_id') . ' = ' . $db->quoteName('us.user_id'))
                ->where($db->quoteName('tu.parent_id') . ' = ' . $db->quote($trigger->trigger_id));
            $db->setQuery($query);
            $trigger->users = array_values($db->loadObjectList());

            return $trigger;
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }

    function createTrigger($trigger, $users, $user) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $to_current_user = 0;
        $to_applicant = 0;

        if ($trigger['action_status'] == 'to_current_user') {
            $to_current_user = 1;
        } elseif ($trigger['action_status'] == 'to_applicant') {
            $to_applicant = 1;
        }

        $query->insert($db->quoteName('#__emundus_setup_emails_trigger'))
            ->set($db->quoteName('user') . ' = ' . $db->quote($user->id))
            ->set($db->quoteName('step') . ' = ' . $db->quote($trigger['status']))
            ->set($db->quoteName('email_id') . ' = ' . $db->quote($trigger['model']))
            ->set($db->quoteName('to_current_user') . ' = ' . $db->quote($to_current_user))
            ->set($db->quoteName('to_applicant') . ' = ' . $db->quote($to_applicant));

        try {
            $db->setQuery($query);
            $db->execute();

            $trigger_id = $db->insertid();

            if ($trigger['target'] == 5 || $trigger['target'] == 6) {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id'))
                    ->set($db->quoteName('parent_id') . ' = ' . $db->quote($trigger_id))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote($trigger['target']));
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch(Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    return false;
                }
            } elseif ($trigger['target'] == 0) {
                foreach (array_keys($users) as $uid) {
                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->set($db->quoteName('parent_id') . ' = ' . $db->quote($trigger_id))
                        ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid));
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    } catch(Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                        return false;
                    }
                }
            }

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_programme_id'))
                ->set($db->quoteName('parent_id') . ' = ' . $db->quote($trigger_id))
                ->set($db->quoteName('programme_id') . ' = ' . $db->quote($trigger['program']));

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

    function updateTrigger($tid,$trigger,$users) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $to_current_user = 0;
        $to_applicant = 0;

        if ($trigger['action_status'] == 'to_current_user') {
            $to_current_user = 1;
        } elseif ($trigger['action_status'] == 'to_applicant') {
            $to_applicant = 1;
        }

        $query->update($db->quoteName('#__emundus_setup_emails_trigger'))
            ->set($db->quoteName('step') . ' = ' . $db->quote($trigger['status']))
            ->set($db->quoteName('email_id') . ' = ' . $db->quote($trigger['model']))
            ->set($db->quoteName('to_current_user') . ' = ' . $db->quote($to_current_user))
            ->set($db->quoteName('to_applicant') . ' = ' . $db->quote($to_applicant))
            ->where($db->quoteName('id') . ' = ' . $tid);

        try {
            $db->setQuery($query);
            $db->execute();

            if ($trigger['target'] == 5 || $trigger['target'] == 6) {
                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_emails_trigger_repeat_profile_id'))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote($trigger['target']))
                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($tid));

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch(Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    return false;
                }

            } elseif ($trigger['target'] == 0) {
                foreach (array_keys($users) as $uid) {
                    $query->clear()
                        ->select('COUNT(*)')
                        ->from($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                        ->where($db->quoteName('user_id') . ' = ' . $db->quote($uid))
                        ->andWhere($db->quoteName('parent_id') . ' = ' . $db->quote($tid));
                    $db->setQuery($query);
                    $row = $db->loadResult();

                    if ($row < 1) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_setup_emails_trigger_repeat_user_id'))
                            ->set($db->quoteName('parent_id') . ' = ' . $db->quote($tid))
                            ->set($db->quoteName('user_id') . ' = ' . $db->quote($uid));
                        try {
                            $db->setQuery($query);
                            $db->execute();
                        } catch(Exception $e) {
                            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                            return false;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    function removeTrigger($tid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_setup_emails_trigger'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($tid));

        try {
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }

    }
}
