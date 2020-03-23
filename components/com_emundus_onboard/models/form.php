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

class EmundusonboardModelform extends JModelList
{
  /**
   * @param $user int
   * gets the amount of camapaigns
   * @param int $offset
   * @return integer
   */
  function getFormCount($user, $filter)
  {
    if (empty($user)) {
      $user = JFactory::getUser()->id;
    }

    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if ($filter == 'Publish') {
      $filterCount = $db->quoteName('ff.published') . ' = 1';
    } elseif ($filter == 'Unpublish') {
      $filterCount = $db->quoteName('ff.published') . ' = 0';
    } else {
      $filterCount = '1';
    }

    $query
      ->select('COUNT(ff.id)')
      ->from($db->quoteName('#__fabrik_forms', 'ff'))
      ->where($filterCount);

    try {
      $db->setQuery($query);
      return $db->loadResult();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return;
    }
  }

  /**
   * @return array
   * get list of declared forms
   */
  function getAllForms($user, $limit = 0, $offset = 0, $filter)
  {
    if (empty($user)) {
      $user = JFactory::getUser()->id;
    }

    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if ($filter == 'Publish') {
      $filterDate = $db->quoteName('ff.published') . ' = 1';
    } elseif ($filter == 'Unpublish') {
      $filterDate = $db->quoteName('ff.published') . ' = 0';
    } else {
      $filterDate = '1';
    }

    $query
      ->select('*')
      ->from($db->quoteName('#__fabrik_forms', 'ff'))
      ->where($filterDate);

    if (!empty($limit) || !empty($offset)) {
      $query->setLimit(5, $offset);
    }

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return;
    }
  }

  /**
   * @param   array $data the row to delete in table.
   *
   * @return boolean
   * Delete form(s) in DB
   */
  public function deleteForm($data)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($data) > 0) {
      try {
        $se_conditions = array(
          $db->quoteName('id') .
          ' IN (' .
          implode(", ", array_values($data)) .
          ')'
        );

        $query
          ->clear()
          ->delete($db->quoteName('#__emundus_setup_emails'))
          ->where($se_conditions);

        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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
   * Unpublish form(s) in DB
   */
  public function unpublishForm($data)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($data) > 0) {
      try {
        $fields = array($db->quoteName('published') . ' = 0');
        $se_conditions = array(
          $db->quoteName('id') .
          ' IN (' .
          implode(", ", array_values($data)) .
          ')'
        );

        $query
          ->update($db->quoteName('#__emundus_setup_emails'))
          ->set($fields)
          ->where($se_conditions);

        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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
   * Publish form(s) in DB
   */
  public function publishForm($data)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($data) > 0) {
      try {
        $fields = array($db->quoteName('published') . ' = 1');
        $se_conditions = array(
          $db->quoteName('id') .
          ' IN (' .
          implode(", ", array_values($data)) .
          ')'
        );

        $query
          ->update($db->quoteName('#__emundus_setup_emails'))
          ->set($fields)
          ->where($se_conditions);

        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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
   * Copy form(s) in DB
   */
  public function duplicateForm($data)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($data) > 0) {
      try {
        $columns = array_keys($db->getTableColumns('#__emundus_setup_emails'));

        $columns = array_filter($columns, function ($k) {
          return $k != 'id' && $k != 'date_time';
        });

        foreach ($data as $id) {
          $query
            ->clear()
            ->select(implode(',', $db->qn($columns)))
            ->from($db->quoteName('#__emundus_setup_emails'))
            ->where($db->quoteName('id') . ' = ' . $id);

          $db->setQuery($query);
          $values[] = implode(', ', $db->quote($db->loadRow()));
        }

        $query
          ->clear()
          ->insert($db->quoteName('#__emundus_setup_emails'))
          ->columns(implode(',', $db->quoteName($columns)))
          ->values($values);

        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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
   * get list of declared forms
   */
  public function getFormById($id)
  {
    if (empty($id)) {
      return false;
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('*')
      ->from($db->quoteName('#__emundus_setup_emails'))
      ->where($db->quoteName('id') . ' = ' . $id);

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObject();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  /**
   * @param   array $data the row to add in table.
   *
   * @return boolean
   * Add new form in DB
   */
  public function createForm($data)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($data) > 0) {
      $query
        ->insert($db->quoteName('#__emundus_setup_emails'))
        ->columns($db->quoteName(array_keys($data)))
        ->values(implode(',', $db->Quote(array_values($data))));

      try {
        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        return $e->getMessage();
      }
    } else {
      return false;
    }
  }

  /**
   * @param   String $code the form to update
   * @param   array $data the row to add in table.
   *
   * @return boolean
   * Update form in DB
   */
  //TODO UPDATE CAMPAIGN AND TU CODE
  public function updateForm($id, $data)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($data) > 0) {
      $fields = [];

      foreach ($data as $key => $val) {
        $insert = $db->quoteName($key) . ' = ' . $db->quote($val);
        $fields[] = $insert;
      }

      $query
        ->update($db->quoteName('#__emundus_setup_emails'))
        ->set($fields)
        ->where($db->quoteName('id') . ' = ' . $db->quote($id));

      try {
        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
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
   * get list of declared forms
   */
  public function getFormTypes()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('DISTINCT(type)')
      ->from($db->quoteName('#__emundus_setup_emails'));

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadColumn();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  /**
   * @param $code
   *
   * @return array
   * get list of declared forms
   */
  public function getFormCategories()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('DISTINCT(category)')
      ->from($db->quoteName('#__emundus_setup_emails'));

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadColumn();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  /**
   * @param $code
   *
   * @return array
   * get list of declared documents
   */
  public function getAllDocuments($prid)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select([
        'sap.attachment_id AS id',
        'sap.ordering',
        'sap.mandatory AS need',
        'sa.value'
      ])
      ->from($db->quoteName('#__emundus_setup_attachment_profiles', 'sap'))
      ->leftJoin(
        $db->quoteName('#__emundus_setup_attachments', 'sa') .
          ' ON ' .
          $db->quoteName('sa.id') .
          ' = ' .
          $db->quoteName('sap.attachment_id')
      )
      ->order($db->quoteName('sap.ordering'))
      ->where($db->quoteName('sap.published') . ' = 1')
      ->where($db->quoteName('sap.profile_id') . ' = ' . $prid);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  /**
   * @param $code
   *
   * @return array
   * get list of declared documents
   */
  public function getUnDocuments()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('*')
      ->from($db->quoteName('#__emundus_setup_attachments'))
      ->order($db->quoteName('ordering'));

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function updateDocuments($data, $prid)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $allDocuments = EmundusonboardModelform::getAllDocuments($prid);

    $allDocumentsIds = [];

    foreach ($allDocuments as $documents) {
      array_push($allDocumentsIds, $documents->id);
    }

    if (count($data) > 0) {
      $values = [];

      foreach ($data as $keys => $vals) {
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

        array_push(
          $values,
          '(' . $did . ',' . $prid . ',1,' . $ordering . ',' . $need . ', 1)'
        );
      }

      $query =
        'INSERT INTO jos_emundus_setup_attachment_profiles 
            (attachment_id, profile_id, displayed, ordering, mandatory, published)
            VALUES 
            ' .
        implode(',', $values) .
        '
            ON DUPLICATE KEY UPDATE 
            profile_id = VALUES(profile_id),
            displayed = VALUES(displayed),
            ordering = VALUES(ordering),
            mandatory = VALUES(mandatory),
            published = VALUES(published)
            ;';

      try {
        $db->setQuery($query);
        $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        return $e->getMessage();
      }

      EmundusonboardModelform::deleteRemainingDocuments(
        $prid,
        $allDocumentsIds
      );

      return true;
    } else {
      return false;
    }
  }

  public function deleteRemainingDocuments($prid, $allDocumentsIds)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

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
      $db->execute();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
      return $e->getMessage();
    }
  }

  public function reorderMenuItems($menuId, $allIds)
  {
    $table = JTable::getInstance('Menu', 'JTable', array());
    $menuTree = $table->getTree($menuId);
    $ids = [];
    foreach ($allIds as $id) {
      array_push($ids, $id);
    }
    $lfts = [];
    foreach ($menuTree as $root) {
      array_push($lfts, $root->lft);
    }
    $reorderMenu = $table->saveorder($ids, $lfts);
  }

  public function modifyMenuItem($itemId, $itemToChange)
  {
    $menuitem = new stdClass();
    $menuitem->id = $itemId;
    foreach ($itemToChange as $key => $value) {
      $menuitem->$key = $value;
    }
    $result = JFactory::getDbo()->updateObject('#__menu', $menuitem, 'id');
  }

  public function getMenu($prid)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $menu = 'menu-profile' . $prid;

    $query
      ->select('*')
      ->from($db->quoteName('#__menu_types'))
      ->where($db->quoteName('menutype') . ' = ' . $db->quote($menu));

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObject();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function getMenuItems($menutype)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select([
        'id AS itemid',
        'title',
        'alias',
        'type',
        'link',
        'published',
        'parent_id',
        'component_id',
        'published'
      ])
      ->from($db->quoteName('#__menu'))
      ->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype))
      ->where($db->quoteName('published') . '!= -2');

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function getAliases()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('alias')->from($db->quoteName('#__menu'));

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function getGroupRights($groupId)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('*')
      ->from($db->quoteName('#__emundus_acl'))
      ->where($db->quoteName('group_id') . ' = ' . $db->quote($groupId));

    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function getActionsLabels($actionIds)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $bigWhere = 'id = -1';

    foreach ($actionIds as $actionId) {
      $bigWhere .= ' OR id = ' . $actionId;
    }

    $query
      ->select('label')
      ->from($db->quoteName('#__emundus_setup_actions'))
      ->where($bigWhere);

    $db->setQuery($query);

    $labels = $db->loadObjectList();

    $newLabels = [];
    $newLabel = "";

    foreach ($labels as $label) {
      $label = get_object_vars($label);
      $label = $label['label'];

      JText::script($label);
      $newLabel = JText::_($label);

      array_push($newLabels, $newLabel);
    }

    try {
      $db->setQuery($query);
      return $newLabels;
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function updateGroupRights($datas, $group_id)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    if (count($datas) > 0) {
      $values = [];

      foreach ($datas as $data) {
        foreach ($data as $key => $value) {
          $id = $data['id'];
          $action_id = $data['action_id'];
          $c = $data['c'];
          $r = $data['r'];
          $u = $data['u'];
          $d = $data['d'];
          $time_date = $data['time_date'];
        }
        array_push(
          $values,
          '(' .
            $id .
            ', ' .
            $group_id .
            ', ' .
            $action_id .
            ', ' .
            $c .
            ', ' .
            $r .
            ', ' .
            $u .
            ', ' .
            $d .
            ', ' .
            $db->quote($time_date) .
            ')'
        );
      }

      $query =
        'INSERT INTO jos_emundus_acl 
            (id, group_id, action_id, c, r, u, d, time_date)
            VALUES 
            ' .
        implode(',', $values) .
        '
            ON DUPLICATE KEY UPDATE 
            group_id = VALUES(group_id),
            action_id = VALUES(action_id),
            c = VALUES(c),
            r = VALUES(r),
            u = VALUES(u),
            d = VALUES(d),
            time_date = VALUES(time_date)
            ;';

      try {
        $db->setQuery($query);
        return $db->execute();
      } catch (Exception $e) {
        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
        return $e->getMessage();
      }
    } else {
      return false;
    }
  }

  public function getGroupsIds()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('DISTINCT(group_id)')
      ->from($db->quoteName('#__emundus_acl'));
    $db->setQuery($query);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function deleteGroup($group_id)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->clear()
      ->delete($db->quoteName('#__emundus_setup_groups_repeat_campaign'))
      ->where($db->quoteName('parent_id') . ' = ' . $group_id);

    try {
      $db->setQuery($query);
      return $db->execute();
    } catch (Exception $e) {
      error_log($e->getMessage(), 0);
      return false;
    }
  }

  public function addGroup($group_id, $campaign_id)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $data = array(
      "parent_id" => $group_id,
      "campaign_id" => $campaign_id,
      "params" => ""
    );

    $query
      ->insert($db->quoteName('#__emundus_setup_groups_repeat_campaign'))
      ->columns($db->quoteName(array_keys($data)))
      ->values(implode(',', $db->Quote(array_values($data))));

    try {
      $db->setQuery($query);
      return $db->execute();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
      return $e->getMessage();
    }
  }

  public function maxGroup()
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('MAX(group_id) AS maxParent')
      ->from($db->quoteName('#__emundus_acl'));

    try {
      $db->setQuery($query);
      return $db->loadObject();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
      return $e->getMessage();
    }
  }

  public function getGroupsCampaign($campaign_id)
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('parent_id')
      ->from($db->quoteName('#__emundus_setup_groups_repeat_campaign'))
      ->where($db->quoteName('campaign_id') . ' = ' . $campaign_id);

    try {
      $db->setQuery($query);
      return $db->loadObjectList();
    } catch (Exception $e) {
      JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
      return $e->getMessage();
    }
  }
  
      /**
	 * @param $id
	 *
	 * @return array
	 * get list of declared forms
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
            error_log($e->getMessage(), 0);
            return false;
        }
    }
      /**
	 * @param $id
	 *
	 * @return array
	 * get list of declared forms
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
            error_log($e->getMessage(), 0);
            return false;
        }
    }
}
