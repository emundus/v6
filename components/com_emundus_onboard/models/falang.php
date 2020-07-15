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

class EmundusonboardModelfalang extends JModelList {

  function insertFalang($textfr,$texten,$reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      $currentDate = date('Y-m-d H:i:s');
      $user = JFactory::getUser()->id;

      // Insert english text
      $query->insert('#__falang_content')
          ->set($db->quoteName('language_id') . ' = 1')
          ->set($db->quoteName('value') . ' = ' . $db->quote($texten))
          ->set($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->set($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->set($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
          ->set($db->quoteName('original_text') . ' = ' . $db->quote(''))
          ->set($db->quoteName('modified') . ' = ' . $db->quote($currentDate))
          ->set($db->quoteName('modified_by') . ' = ' . $db->quote($user))
          ->set($db->quoteName('published') . ' = 1');
      $db->setQuery($query);
      $db->execute();

      // Insert french text
      $query->clear()
          ->insert('#__falang_content')
          ->set($db->quoteName('language_id') . ' = 2')
          ->set($db->quoteName('value') . ' = ' . $db->quote($textfr))
          ->set($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->set($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->set($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
          ->set($db->quoteName('original_text') . ' = ' . $db->quote(''))
          ->set($db->quoteName('modified') . ' = ' . $db->quote($currentDate))
          ->set($db->quoteName('modified_by') . ' = ' . $db->quote($user))
          ->set($db->quoteName('published') . ' = 1');
      $db->setQuery($query);
      $db->execute();
  }

  function deleteFalang($reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      $query->delete('#__falang_content')
          ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field));
      $db->setQuery($query);
      $db->execute();
  }

  function updateFalang($textfr,$texten,$reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      $query->update('#__falang_content')
          ->set($db->quoteName('value') . ' = ' . $db->quote($texten))
          ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
          ->andWhere($db->quoteName('language_id') . ' = 1');
      $db->setQuery($query);
      $db->execute();

      $query->clear()
          ->update('#__falang_content')
          ->set($db->quoteName('value') . ' = ' . $db->quote($textfr))
          ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
          ->andWhere($db->quoteName('language_id') . ' = 2');
      $db->setQuery($query);
      $db->execute();
  }

  function getFalang($reference_id,$reference_table,$reference_field){
      $labels = new stdClass();

      $db = $this->getDbo();
      $query = $db->getQuery(true);

      $query->select('value')
          ->from($db->quoteName('#__falang_content'))
          ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
          ->andWhere($db->quoteName('language_id') . ' = 1');
      $db->setQuery($query);
      $labels->en = $db->loadObject();

      $query->clear()
          ->select('value')
          ->from($db->quoteName('#__falang_content'))
          ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
          ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
          ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
          ->andWhere($db->quoteName('language_id') . ' = 2');
      $db->setQuery($query);
      $labels->fr = $db->loadObject();

      return $labels;
  }
}
