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

	/**
	 * @return array
	 * get list of declared programmes
	 */
    function getAllPrograms($user, $lim, $page, $filter, $sort, $recherche) {

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

        $sortDb = 'p.id ';

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        if ($filter == 'Publish') {
            $filterDate = $db->quoteName('p.published') . ' LIKE 1';
        }
        else if ($filter == 'Unpublish') {
            $filterDate = $db->quoteName('p.published') . ' LIKE 0';
        }
        else {
            $filterDate = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        }
        else {
            $rechercheLbl = $db->quoteName('p.label') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheNotes = $db->quoteName('p.notes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheCategory = $db->quoteName('p.programmes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheLbl.' OR '.$rechercheNotes.' OR '.$rechercheCategory;
        }

        $query->select(['p.*', 'COUNT(cc.id) AS nb_files'])
            ->from($db->quoteName('#__emundus_setup_programmes', 'p'))
            ->leftJoin(
                $db->quoteName('#__emundus_setup_campaigns', 'sc') .
                ' ON ' .
                $db->quoteName('sc.training') .
                ' LIKE ' .
                $db->quoteName('p.code')
            )
            ->leftJoin(
                $db->quoteName('#__emundus_campaign_candidature', 'cc') .
                ' ON ' .
                $db->quoteName('cc.campaign_id') .
                ' = ' .
                $db->quoteName('sc.id')
            )
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
     * @return array
     * get list of declared programmes
     */
    function getProgramCount($user, $filter, $recherche) {

        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if ($filter == 'Publish') {
            $filterCount = $db->quoteName('p.published') . ' LIKE 1';
        }
        else if ($filter == 'Unpublish') {
            $filterCount = $db->quoteName('p.published') . ' LIKE 0';
        }
        else {
            $filterCount = ('1');
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        }
        else {
            $rechercheLbl = $db->quoteName('p.label') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheNotes = $db->quoteName('p.notes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $rechercheCategory = $db->quoteName('p.programmes') . ' LIKE ' . $db->quote('%'.$recherche.'%');
            $fullRecherche = $rechercheLbl.' OR '.$rechercheNotes.' OR '.$rechercheCategory;
        }

        $query->select('COUNT(p.id)')
            ->from($db->quoteName('#__emundus_setup_programmes', 'p'))
            ->where($filterCount)
            ->where($fullRecherche);
        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }
    }


	/**
	 * @param $id
	 *
	 * @return array
	 * get list of declared programmes
	 */
    public function getProgramById($id) {

        if (empty($id)) {
	        return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from ($db->quoteName('#__emundus_setup_programmes'))
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
     * Add new program in DB
     */
    public function addProgram($data) {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (array_values($data)[1] == "") {
            $data = null;
        }

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

        	$query
                ->insert($db->quoteName('#__emundus_setup_programmes'))
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
     * @param   String $code the program to update
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
                $insert = $db->quoteName(htmlentities($key)) . ' = ' . $db->quote(htmlentities($val));
                $fields[] = $insert;
            }

            $query
                ->update($db->quoteName('#__emundus_setup_programmes'))
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
                $query
                    ->select($db->qn('sc.id'))
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

                $query
                    ->clear()
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

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 0'
                );
                $conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query
                    ->update($db->quoteName('#__emundus_setup_programmes'))
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

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlentities($data[$key]);
              }

            try {
                $fields = array(
                    $db->quoteName('published') . ' = 1'
                );
                $conditions = array(
                    $db->quoteName('id') . ' IN (' . implode(", ",array_values($data)) . ')',
                );

                $query
                    ->update($db->quoteName('#__emundus_setup_programmes'))
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
     * @param $code
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
     * @param $user int
     * get list of all campaigns associated to the user
     * @param int $offset
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
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return;
        }
    }

}