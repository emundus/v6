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
     function getEmailCount($user, $filter, $recherche) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if ($filter == 'Publish') {
            $filterCount = $db->quoteName('se.published') . ' = 1';
        }
        else if ($filter == 'Unpublish') {
            $filterCount = $db->quoteName('se.published') . ' = 0';
        }
        else {
            $filterCount = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        }
        else {
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
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }

    }

    /**
	 * @return array
	 * get list of declared emails
	 */
     function getAllEmails($user, $lim, $page, $filter, $sort, $recherche) {

        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        if (empty($lim)) {
            $limit = 25;
        }
        else {
            $limit = $lim;
        }
        
        if (empty($page)) {
            $offset = 0;
        } 
        else {
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
        }
        else if ($filter == 'Unpublish') {
            $filterDate = $db->quoteName('se.published') . ' = 0';
        }
        else {
            $filterDate = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        }
        else {
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
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
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

        if (count($data) > 0) {
            try {
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query
                    ->clear()
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

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 0'
                );
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query
                    ->update($db->quoteName('#__emundus_setup_emails'))
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

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 1'
                );
                $se_conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query
                    ->update($db->quoteName('#__emundus_setup_emails'))
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
     * @param   array $data the row to copy in table.
     *
     * @return boolean
     * Copy email(s) in DB
     */
    public function duplicateEmail($data) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

            try {
                $columns = array_keys($db->getTableColumns('#__emundus_setup_emails'));

                $columns = array_filter($columns, function($k) {
                    return ($k != 'id' && $k != 'date_time');
                });

                foreach ($data as $id){
                    $query
                        ->clear()
                        ->select(implode(',', $db->qn($columns)))
                        ->from($db->quoteName('#__emundus_setup_emails'))
                        ->where($db->quoteName('id') . ' = ' . $id);

                    $db->setQuery($query);
                    $values[] = implode(', ',$db->quote($db->loadRow()));
                }


                $query
                    ->clear()
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
	 * @return array
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

        if (count($data) > 0) {

            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

        	$query
                ->insert($db->quoteName('#__emundus_setup_emails'))
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
     * @param   String $code the email to update
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
                $insert = $db->quoteName(htmlentities($key)) . ' = ' . $db->quote(htmlentities($val));
                $fields[] = $insert;
            }

            $query
                ->update($db->quoteName('#__emundus_setup_emails'))
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
     * @param $code
     *
     * @return array
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

    /**
     * @param $code
     *
     * @return array
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
}