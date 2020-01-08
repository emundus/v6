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
     */
    public function getCampaign($id = 0)
    {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $query->select('pr.*,ca.*');
        $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
        $query->where('ca.training = pr.code AND ca.published=1 AND ca.id='.$id);
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    public function getParams($id = 0)
    {
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
     */
    public function getAssociatedProgrammes($user)
    {
        $query = 'select DISTINCT sc.training
                  from #__emundus_users_assoc as ua
                  LEFT JOIN #__emundus_campaign_candidature as cc ON cc.fnum=ua.fnum
                  left join #__emundus_setup_campaigns as sc on sc.id = cc.campaign_id
                  where ua.user_id='.$user;
        try
        {
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadColumn();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    /**
     * @param $published  int     get published or unpublished programme
     * @param $codeList   array   array of IN and NOT IN programme code to get
     * @return array
     * get list of declared programmes
     */
    public function getProgrammes($published = null, $codeList = array()) {
        $db = $this->getDbo();

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
     * @return array
     * get list of declared programmes
     */
    public function getProgramme($code) {
        
	if (empty($code))
	    return false;
	    
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
     */
    public function addProgrammes($data)
    {
        $db = $this->getDbo();

        if (count($data) > 0) {

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

          try
          {
              $db->setQuery($query);
              return $db->execute();
          }
          catch(Exception $e)
          {
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
     */
    public function editProgrammes($data)
    {
        $db = $this->getDbo();

        if (count($data) > 0) {

          try
          {
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
          }
          catch(Exception $e)
          {
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
     */
    function isFavorite($programme_id, $user_id = null) {

        if (empty($user_id))
            $user_id = JFactory::getUser()->id;

        if (empty($user_id) || empty($programme_id))
        	return false;

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
	 */
	public function favorite($programme_id, $user_id = null) {

		if (empty($user_id))
			$user_id = JFactory::getUser()->id;

		if (empty($user_id) || empty($programme_id))
			return false;

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
	 */
	public function unfavorite($programme_id, $user_id = null) {

		if (empty($user_id))
			$user_id = JFactory::getUser()->id;

		if (empty($user_id) || empty($programme_id))
			return false;

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
	 */
	public function getUpcomingFavorites($user_id = null) {

		if (empty($user_id))
			$user_id = JFactory::getUser()->id;

		if (empty($user_id))
			return false;

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

}
