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
use Joomla\CMS\Date\Date;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');

class EmundusonboardModelcampaign extends JModelList
{
    /**
     * @param $user int
     * gets the amount of camapaigns
     * @param int $offset
     * @return integer
     */
    function getCampaignCount($filter, $recherche)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $date = new Date();

        if ($filter == 'notTerminated') {
            $filterCount =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00"';
        } elseif ($filter == 'Terminated') {
            $filterCount =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' <= ' .
                $db->quote($date) .
                ' AND end_date != "0000-00-00 00:00:00"';
        } elseif ($filter == 'Publish') {
            $filterCount =
                $db->quoteName('sc.published') .
                ' = 1 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } elseif ($filter == 'Unpublish') {
            $filterCount =
                $db->quoteName('sc.published') .
                ' = 0 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } else {
            $filterCount = '1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheLbl =
                $db->quoteName('sc.label') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheResume =
                $db->quoteName('sc.short_description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheDescription =
                $db->quoteName('sc.description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $fullRecherche =
                $rechercheLbl .
                ' OR ' .
                $rechercheResume .
                ' OR ' .
                $rechercheDescription;
        }

        $query
            ->select('COUNT(sc.id)')
            ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->where($filterCount)
            ->andWhere($fullRecherche);

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return 0;
        }
    }

    /**
     * @param $user int
     * Get list of all campaigns
     * @param int $offset
     * @return object
     */
    function getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page) {
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

        $sortDb = 'sc.id ';

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $date = new Date();

        if ($filter == 'notTerminated') {
            $filterDate =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00"';
        } elseif ($filter == 'Terminated') {
            $filterDate =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' <= ' .
                $db->quote($date) .
                ' AND end_date != "0000-00-00 00:00:00"';
        } elseif ($filter == 'Publish') {
            $filterDate =
                $db->quoteName('sc.published') .
                ' = 1 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } elseif ($filter == 'Unpublish') {
            $filterDate =
                $db->quoteName('sc.published') .
                ' = 0 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } else {
            $filterDate = '1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheLbl =
                $db->quoteName('sc.label') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheResume =
                $db->quoteName('sc.short_description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheDescription =
                $db->quoteName('sc.description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $fullRecherche =
                $rechercheLbl .
                ' OR ' .
                $rechercheResume .
                ' OR ' .
                $rechercheDescription;
        }

        $query
            ->select([
                'sc.*',
                'COUNT(cc.id) AS nb_files',
                'sp.label AS program_label',
                'sp.id AS program_id',
                'sp.published AS published_prog'
            ])
            ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->leftJoin(
                $db->quoteName('#__emundus_campaign_candidature', 'cc') .
                ' ON ' .
                $db->quoteName('cc.campaign_id') .
                ' = ' .
                $db->quoteName('sc.id')
            )
            ->leftJoin(
                $db->quoteName('#__emundus_setup_programmes', 'sp') .
                ' ON ' .
                $db->quoteName('sp.code') .
                ' LIKE ' .
                $db->quoteName('sc.training')
            )
            ->where($filterDate)
            ->andWhere($fullRecherche)
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

    function getCampaignsByProgram($program){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();

        $query
            ->select('sc.*')
            ->from($db->quoteName('#__emundus_setup_programmes','sp'))
            ->leftJoin(
                $db->quoteName('#__emundus_setup_campaigns', 'sc') .
                ' ON ' .
                $db->quoteName('sp.code') .
                ' LIKE ' .
                $db->quoteName('sc.training')
            )
            ->where($db->quoteName('sp.id') . ' = ' . $db->quote($program))
            ->andWhere($db->quoteName('sc.end_date') . ' >= ' . $db->quote($date));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return new stdClass();
        }
    }

    /**
     * @param   array $data the row to delete in table.
     *
     * @return boolean
     * Delete campaign(s) in DB
     */
    public function deleteCampaign($data)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        if (count($data) > 0) {
            try {
                foreach (array_values($data) as $id){
                    $falang->deleteFalang($id,'emundus_setup_campaigns','label');
                }

                $cc_conditions = array(
                    $db->quoteName('campaign_id') .
                    ' IN (' .
                    implode(", ", array_values($data)) .
                    ')'
                );

                $query
                    ->delete($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($cc_conditions);

                $db->setQuery($query);
                $db->execute();

                $sc_conditions = array(
                    $db->quoteName('id') .
                    ' IN (' .
                    implode(", ", array_values($data)) .
                    ')'
                );

                $query
                    ->clear()
                    ->delete($db->quoteName('#__emundus_setup_campaigns'))
                    ->where($sc_conditions);

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

    /**
     * @param   array $data the row to unpublish in table.
     *
     * @return boolean
     * Unpublish campaign(s) in DB
     */
    public function unpublishCampaign($data)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {

            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array($db->quoteName('published') . ' = 0');
                $sc_conditions = array(
                    $db->quoteName('id') .
                    ' IN (' .
                    implode(", ", array_values($data)) .
                    ')'
                );

                $query
                    ->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

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

    /**
     * @param   array $data the row to publish in table.
     *
     * @return boolean
     * Publish campaign(s) in DB
     */
    public function publishCampaign($data)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = array($db->quoteName('published') . ' = 1');
                $sc_conditions = array(
                    $db->quoteName('id') .
                    ' IN (' .
                    implode(", ", array_values($data)) .
                    ')'
                );

                $query
                    ->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

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

    /**
     * @param   array $data the row to copy in table.
     *
     * @return boolean
     * Copy campaign(s) in DB
     */
    public function duplicateCampaign($data)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {
            try {
                $columns = array_keys(
                    $db->getTableColumns('#__emundus_setup_campaigns')
                );

                $columns = array_filter($columns, function ($k) {
                    return $k != 'id' && $k != 'date_time';
                });

                foreach ($data as $id) {
                    $query
                        ->clear()
                        ->select(implode(',', $db->qn($columns)))
                        ->from($db->quoteName('#__emundus_setup_campaigns'))
                        ->where($db->quoteName('id') . ' = ' . $id);

                    $db->setQuery($query);
                    $values[] = implode(', ', $db->quote($db->loadRow()));
                }

                $query
                    ->clear()
                    ->insert($db->quoteName('#__emundus_setup_campaigns'))
                    ->columns(implode(',', $db->quoteName($columns)))
                    ->values($values);

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

    /**
     * @param $user int
     * get list of all campaigns associated to the user
     * @param int $offset
     * @return Array
     */
    //TODO Throw in the years model
    function getYears($user)
    {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('DISTINCT(tu.schoolyear)')
            ->from($db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
            ->order('tu.id DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    /**
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Add new campaign in DB
     */
    public function createCampaign($data)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $settings = JModelLegacy::getInstance('settings', 'EmundusonboardModel');

        $i = 0;

        $label_fr = '';
        $label_en = '';

        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                if ($key == 'profileLabel') {
                    array_splice($data, $i, 1);
                }
                if ($key == 'label') {
                    $label_fr = $data['label']['fr'];
                    $label_en = $data['label']['en'];
                    $data['label'] = $data['label']['fr'];
                }
                $i++;
            }

            $query
                ->insert($db->quoteName('#__emundus_setup_campaigns'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                $campaign_id = $db->insertid();

                $falang->insertFalang($label_fr,$label_en,$campaign_id,'emundus_setup_campaigns','label');

                $user = JFactory::getUser();
                $settings->onAfterCreateCampaign($user->id);

                return $campaign_id;
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /**
     * @param   String $code the campaign to update
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Update campaign in DB
     */
    public function updateCampaign($data, $cid)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $label_fr = '';
        $label_en = '';

        if (count($data) > 0) {
            $fields = [];

            foreach ($data as $key => $val) {
                if ($key == 'label') {
                    $label_fr = $data['label']['fr'];
                    $label_en = $data['label']['en'];
                    $data['label'] = $data['label']['fr'];
                }
                if ($key !== 'profileLabel') {
                    $insert = $db->quoteName(htmlspecialchars($key)) . ' = ' . $db->quote(htmlspecialchars($val));
                    $fields[] = $insert;
                }
            }

            $falang->updateFalang($label_fr,$label_en,$cid,'emundus_setup_campaigns','label');

            $query
                ->update($db->quoteName('#__emundus_setup_campaigns'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $db->quote($cid));

            try {
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

    /**
     * @param   array $data the row to add in table.
     *
     * @return boolean
     * Add new Year in DB
     */
    public function createYear($data)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (count($data) > 0) {

            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            $query
                ->insert($db->quoteName('#__emundus_setup_teaching_unity'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

            try {
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

    /**
     * @param $id
     *
     * @return array
     * get list of declared campaigns
     */
    public function getCampaignById($id)
    {
        if (empty($id)) {
            return false;
        }

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $results = new stdClass();

        $query
            ->select(['sc.*', 'spr.label AS profileLabel'])
            ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->leftJoin(
                $db->quoteName('#__emundus_setup_profiles', 'spr') .
                ' ON ' .
                $db->quoteName('spr.id') .
                ' = ' .
                $db->quoteName('sc.profile_id')
            )
            ->where($db->quoteName('sc.id') . ' = ' . $id);

        try {
            $db->setQuery($query);
            $results->campaign = $db->loadObject();
            try {
                $results->label = $falang->getFalang($id,'emundus_setup_campaigns','label');
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
            return $results;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    /**
     * @param $id
     *
     * @return array
     * get list of declared campaigns
     */
    public function getCreatedCampaign()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $currentDate = date('Y-m-d H:i:s');

        $query
            ->select('*')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('date_time') . ' = ' . $db->quote($currentDate));

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function updateProfile($profile, $campaign){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('year')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
        $db->setQuery($query);
        $schoolyear = $db->loadResult();

        $query
            ->clear()
            ->update($db->quoteName('#__emundus_setup_campaigns'))
            ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
            ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));

        try {
            $db->setQuery($query);
            $db->execute();

            $query
                ->clear()
                ->update($db->quoteName('#__emundus_setup_teaching_unity'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
                ->where($db->quoteName('schoolyear') . ' = ' . $db->quote($schoolyear));

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function getCampaignsToAffect(){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();

        $query
            ->select('id,label')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' IS NULL')
            ->andWhere($db->quoteName('end_date') . ' >= ' . $db->quote($date));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function getCampaignsToAffectByTerm($term){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();

        $searchName =
            $db->quoteName('label') .
            ' LIKE ' .
            $db->quote('%' . $term . '%');

        $query
            ->select('id,label')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' IS NULL')
            ->andWhere($db->quoteName('end_date') . ' >= ' . $db->quote($date))
            ->andWhere($searchName);

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }
}
