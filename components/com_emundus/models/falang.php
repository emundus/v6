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

class EmundusModelFalang extends JModelList {

  function insertFalang($values,$reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);
      $languages = JLanguageHelper::getLanguages();

      $values = json_decode(json_encode($values), true);

      $currentDate = date('Y-m-d H:i:s');
      $user = JFactory::getUser()->id;

      $results = array();

      try {
          foreach ($languages as $language) {
              $query->clear()
                  ->select('COUNT(*)')
                  ->from($db->quoteName('#__falang_content'))
                  ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                  ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                  ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                  ->andWhere($db->quoteName('language_id') . ' = ' . $db->quote($language->lang_id));
              $db->setQuery($query);
              if ($db->loadResult() == 0) {
                  $query->clear()
                      ->insert('#__falang_content')
                      ->set($db->quoteName('language_id') . ' = ' . $language->lang_id)
                      ->set($db->quoteName('value') . ' = ' . $db->quote($values[$language->sef]))
                      ->set($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                      ->set($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                      ->set($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                      ->set($db->quoteName('original_text') . ' = ' . $db->quote(''))
                      ->set($db->quoteName('modified') . ' = ' . $db->quote($currentDate))
                      ->set($db->quoteName('modified_by') . ' = ' . $db->quote($user))
                      ->set($db->quoteName('published') . ' = 1');
                  $db->setQuery($query);
                  $results[] = $db->execute();
              } else {
                  $results[] = $this->updateFalangOnce($values,$reference_id,$reference_table,$reference_field, $language->lang_id);
              }
          }

          return $results;
      }  catch(Exception $e) {
          JLog::add('component/com_emundus/models/falang | Error when try to insert the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

  function insertFalangOnce($text,$reference_id,$reference_table,$reference_field, $language){
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      $currentDate = date('Y-m-d H:i:s');
      $user = JFactory::getUser()->id;

      try {
          // Insert text
          $query->insert('#__falang_content')
              ->set($db->quoteName('language_id') . ' = ' . $db->quote($language))
              ->set($db->quoteName('value') . ' = ' . $db->quote($text))
              ->set($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
              ->set($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
              ->set($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
              ->set($db->quoteName('original_text') . ' = ' . $db->quote(''))
              ->set($db->quoteName('modified') . ' = ' . $db->quote($currentDate))
              ->set($db->quoteName('modified_by') . ' = ' . $db->quote($user))
              ->set($db->quoteName('published') . ' = 1');
          $db->setQuery($query);
          return $db->execute();
          //
      }  catch(Exception $e) {
          JLog::add('component/com_emundus/models/falang | Error when try to insert the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

    function updateFalangOnce($values,$reference_id,$reference_table,$reference_field, $language){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update('#__falang_content')
                ->set($db->quoteName('value') . ' = ' . $db->quote($values[$language->sef]))
                ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                ->andWhere($db->quoteName('language_id') . ' = ' . $db->quote($language));
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/falang | Error when try to update the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
          JLog::add('component/com_emundus/models/falang | Error when try to delete the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

  function updateFalang($values,$reference_id,$reference_table,$reference_field){
      $db = $this->getDbo();
      $query = $db->getQuery(true);
      $languages = JLanguageHelper::getLanguages();

      $values = json_decode(json_encode($values), true);

      $results = array();

      try {
          foreach ($languages as $language) {
              $query->clear()
                  ->select('COUNT(*)')
                  ->from($db->quoteName('#__falang_content'))
                  ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                  ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                  ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                  ->andWhere($db->quoteName('language_id') . ' = ' . $db->quote($language->lang_id));
              $db->setQuery($query);
              if ($db->loadResult() != 0) {
                  $query->clear()
                      ->update('#__falang_content')
                      ->set($db->quoteName('value') . ' = ' . $db->quote($values[$language->sef]))
                      ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                      ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                      ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                      ->andWhere($db->quoteName('language_id') . ' = ' . $db->quote($language->lang_id));
                  $db->setQuery($query);
                  $results[] = $db->execute();
              } else {
                  $results[] = $this->insertFalangOnce($values[$language->sef], $reference_id, $reference_table, $reference_field, $language->lang_id);
              }
          }

          return $results;
      } catch(Exception $e) {
          JLog::add('component/com_emundus/models/falang | Error when try to update the translation ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }

  function getFalang($reference_id,$reference_table,$reference_field,$default = ''){
      $labels = new stdClass();

      $db = $this->getDbo();
      $query = $db->getQuery(true);
      $languages = JLanguageHelper::getLanguages();

      try {
          foreach ($languages as $language) {
              $query->clear()
                  ->select('value')
                  ->from($db->quoteName('#__falang_content'))
                  ->where($db->quoteName('reference_id') . ' = ' . $db->quote($reference_id))
                  ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote($reference_table))
                  ->andWhere($db->quoteName('reference_field') . ' = ' . $db->quote($reference_field))
                  ->andWhere($db->quoteName('language_id') . ' = ' . $db->quote($language->lang_id));
              $db->setQuery($query);
              $labels->{$language->sef} = $db->loadResult();
              if(empty($labels->{$language->sef})){
                  $labels->{$language->sef} = $default;
              }
          }

          return $labels;
      } catch(Exception $e) {
          JLog::add('component/com_emundus/models/falang | Error at getting the translations ' . $reference_id . ' references to table ' . $reference_table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
          return false;
      }
  }
}
