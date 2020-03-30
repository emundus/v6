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

class EmundusonboardModelcampaign extends JModelList
{
  /**
   * @param $user int
   * gets the amount of camapaigns
   * @param int $offset
   * @return integer
   */
  function getCampaignCount($user, $filter, $recherche)
  {
    if (empty($user)) {
      $user = JFactory::getUser()->id;
    }

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
      //->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'sgrc').' ON '.$db->quoteName('sgrc.course').' = '.$db->quoteName('sc.training'))
      //->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg').' ON '.$db->quoteName('sgrc.parent_id').' = '.$db->quoteName('sg.id'))
      //->leftJoin($db->quoteName('#__emundus_groups', 'eg').' ON '.$db->quoteName('sg.id').' = '.$db->quoteName('eg.group_id'))
      //->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$db->quoteName('cc.campaign_id').' = '.$db->quoteName('sc.id'))
      //->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sp.code').' LIKE '.$db->quoteName('sc.training'))
      //->where($db->quoteName('eg.user_id').' = '.$user)
      ->where($filterCount)
      ->where($fullRecherche);

    try {
      $db->setQuery($query);
      return $db->loadResult();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return;
    }
  }

  /**
   * @param $user int
   * get list of all campaigns associated to the user
   * @param int $offset
   * @return object
   */
  function getAssociatedCampaigns(
    $user,
    $filter,
    $sort,
    $recherche,
    $lim,
    $page
  ) {
    if (empty($user)) {
      $user = JFactory::getUser()->id;
    }

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
        'sp.published AS published_prog'
      ])
      ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
      // ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'sgrc').' ON '.$db->quoteName('sgrc.course').' = '.$db->quoteName('sc.training'))
      // ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg').' ON '.$db->quoteName('sgrc.parent_id').' = '.$db->quoteName('sg.id'))
      // ->leftJoin($db->quoteName('#__emundus_groups', 'eg').' ON '.$db->quoteName('sg.id').' = '.$db->quoteName('eg.group_id'))
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
      //->where($db->quoteName('eg.user_id').' = '.$user)
      //titre + title des input
      ->where($filterDate)
      ->where($fullRecherche)
      ->group($sortDb)
      ->order($sortDb . $sort);

    try {
      $db->setQuery($query, $offset, $limit);
      return $db->loadObjectList();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return;
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

    if (count($data) > 0) {
      try {
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
        $data[$key] = htmlentities($data[$key]);
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
        $data[$key] = htmlentities($data[$key]);
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
      // ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'sgrc').' ON '.$db->quoteName('sgrc.course').' = '.$db->quoteName('tu.code'))
      // ->leftJoin($db->quoteName('#__emundus_setup_groups', 'sg').' ON '.$db->quoteName('sgrc.parent_id').' = '.$db->quoteName('sg.id'))
      // ->leftJoin($db->quoteName('#__emundus_groups', 'eg').' ON '.$db->quoteName('sg.id').' = '.$db->quoteName('eg.group_id'))
      // ->where($db->quoteName('eg.user_id').' = '.$user);
      ->order('tu.id DESC');

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return;
    }
  }

  /**
   * @param $user int
   * get list of all Profiles
   * @return Object
   */
  //TODO PUT IN THE PROFILE MODEL
  function getApplicantsProfiles()
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('*')
      ->from($db->quoteName('#__emundus_setup_profiles', 'esp'))
      ->where(
        $db->quoteName('esp.published') .
          ' = 1 AND ' .
          $db->quoteName('esp.status') .
          ' = 1'
      )
      ->order($db->quoteName('esp.id') . ' ASC ');

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return;
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

    $i = 0;
    
    if (count($data) > 0) {
      foreach ($data as $key => $val) {
        $data[$key] = htmlentities($data[$key]);

        if ($key == 'profileLabel') {
          array_splice($data, $i, 1);
        }
        $i++;
      }

      $query
        ->insert($db->quoteName('#__emundus_setup_campaigns'))
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

    if (count($data) > 0) {
      $fields = [];
      
      foreach ($data as $key => $val) {
        if ($key !== 'profileLabel') {
          $insert = $db->quoteName(htmlentities($key)) . ' = ' . $db->quote(htmlentities($val));
          $fields[] = $insert;
        }
      }
      
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
        $data[$key] = htmlentities($data[$key]);
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

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

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

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObject();
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

  /**
   * @param   array $data the row to add in table.
   *
   * @return boolean
   * Add new profile in DB
   */
  public function createProfile($profile)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $lastProfileId = EmundusonboardModelcampaign::getLastProfileId();

    $columns = [
      'id',
      'label',
      'description',
      'published',
      'menutype',
      'acl_aro_groups',
      'is_evaluator'
    ];
    $id = htmlentities($lastProfileId) + 1;
    $label = htmlentities($profile);
    $description =
      "Potential student who has started to fill in the online application form.";
    $published = 1;
    $menutype = 'menu_profile' . $id;
    $acl_aro_groups = 2;
    $is_evaluator = 0;

    $values =
      $id .
      ',' .
      $db->Quote($label) .
      ',' .
      $db->Quote($description) .
      ',' .
      $published .
      ',' .
      $db->Quote($menutype) .
      ',' .
      $acl_aro_groups .
      ',' .
      $is_evaluator;

    $query
      ->insert($db->quoteName('#__emundus_setup_profiles'))
      ->columns($db->quoteName($columns))
      ->values($values);

    try {
      $db->setQuery($query);
      $db->execute();
      return $id;
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return $e->getMessage();
    }
  }

  public function getAllProfiles()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select(['id', 'label'])
      ->from($db->quoteName('#__emundus_setup_profiles'))
      ->where($db->quoteName('id') . ' != 999')
      ->order($db->quoteName('id') . ' ASC ');

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return false;
    }
  }

  public function getLastProfileId()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('MAX(id)')
      ->from($db->quoteName('#__emundus_setup_profiles'))
      ->where($db->quoteName('id') . ' != 999');

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadResult();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
      return false;
    }
  }
}
