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

      try {
          // Check if exist update else insert
          $query
              ->select('COUNT(*)')
              ->from($db->quoteName('#__falang_content'))
              ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
              ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
              ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
              ->andWhere($db->quoteName('language_id') . ' = 1');
          $db->setQuery($query);
          if ($db->loadResult() == 0) {
              // Insert english text
              $query->clear()
                  ->insert('#__falang_content')
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
          } else {
              $this->updateFalang($textfr, $texten, $reference_id, $reference_table, $reference_field);
          }
          //

          // Check if exist update else insert
          $query->clear()
              ->select('COUNT(*)')
              ->from($db->quoteName('#__falang_content'))
              ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
              ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
              ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
              ->andWhere($db->quoteName('language_id') . ' = 2');
          $db->setQuery($query);
          if ($db->loadResult() == 0) {
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
              return $db->execute();
          } else {
              return $this->updateFalang($textfr, $texten, $reference_id, $reference_table, $reference_field);
          }
      }  catch(Exception $e) {
          JLog::add('component/com_emundus_onboard/models/falang | Error when try to insert the translation ' . $textfr . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

  function deleteFalang($reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      try {
          $query->delete('#__falang_content')
              ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
              ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
              ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field));
          $db->setQuery($query);
          return $db->execute();
      } catch(Exception $e) {
          JLog::add('component/com_emundus_onboard/models/falang | Error when try to delete the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

  function updateFalang($textfr,$texten,$reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      try {
          $query
              ->select('COUNT(*)')
              ->from($db->quoteName('#__falang_content'))
              ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
              ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
              ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
              ->andWhere($db->quoteName('language_id') . ' = 1');
          $db->setQuery($query);
          if ($db->loadResult() != 0) {

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
              return $db->execute();
          } else {

              return $this->insertFalang($textfr, $texten, $reference_id, $reference_table, $reference_field);
          }
      } catch(Exception $e) {
          JLog::add('component/com_emundus_onboard/models/falang | Error when try to update the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

  function getFalang($reference_id,$reference_table,$reference_field){
      $labels = new stdClass();

      $db = $this->getDbo();
      $query = $db->getQuery(true);

      try {
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
      } catch(Exception $e) {
          JLog::add('component/com_emundus_onboard/models/falang | Error at getting the translations ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }
}
