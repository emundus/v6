<?php
/**
 * Application Model for eMundus Component
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class EmundusModelAward extends JModelList
{
    public function __construct() {
        parent::__construct();
        global $option;
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

        $this->_mainframe = JFactory::getApplication();

        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getSession()->get('emundusUser');

        $this->locales = substr(JFactory::getLanguage()->getTag(), 0 , 2);
    }
    public function getUpload($fnum,$cid){


        $query = $this->_db->getQuery(true);

        $query->select($this->_db->quoteName('filename'));
        $query->from($this->_db->quoteName('#__emundus_uploads'));
        $query->where($this->_db->quoteName('fnum') . ' LIKE ' . $this->_db->quote($fnum). ' AND' . $this->_db->quoteName('campaign_id') . ' = ' . $this->_db->quote($cid));
        //var_dump($query->__toString()).die();
        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }
    public function getCampaignId($fnum){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('campaign_id'))
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('fnum') . " LIKE " . $db->quote($fnum));
        $db->setQuery($query);

        return $db->loadResult();
    }

   public function updatePlusNbVote($fnum,$user,$thematique,$engagement,$engagement_financier,$engagement_materiel){

       $db = JFactory::getDbo();
       $date_time = new DateTime('NOW');
       $date = $date_time->format('Y-m-d h:i:s');

       $query = $db->getQuery(true);

       $columns = array('time_date', 'fnum', 'user', 'thematique','engagement','engagement_financier','engagement_materiel');

       $values = array($db->quote($date), $db->quote($fnum), $user, $db->quote($thematique), $db->quote($engagement),$db->quote($engagement_financier),$db->quote($engagement_materiel));

       $query
           ->insert($db->quoteName('#__emundus_vote'))
           ->columns($db->quoteName($columns))
           ->values(implode(',', $values));
       $db->setQuery($query);
       $db->execute();

   }
    public function CountVote($fnum,$user){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__emundus_vote'))
            ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum).' AND '.$db->quoteName('user').' = '. $db->quote($user));

        $db->setQuery($query);

        return $db->loadResult();
    }
    public function CountThematique($user,$thematique){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__emundus_vote'))
            ->where($db->quoteName('thematique').' = '.$db->quote($thematique).' AND '.$db->quoteName('user').' = '. $db->quote($user));

        $db->setQuery($query);

        return $db->loadResult();
    }
    public function getFavoris($fnum,$user){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__emundus_favoris'))
            ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum).' AND '.$db->quoteName('user').' = '. $db->quote($user));

        $db->setQuery($query);

        return $db->loadResult();
    }
    public function addToFavoris($fnum, $user){
        $db = JFactory::getDbo();
        $date_time = new DateTime('NOW');
        $date = $date_time->format('Y-m-d h:i:s');

        $query = $db->getQuery(true);

        $columns = array('date_time', 'fnum', 'user');

        $values = array($db->quote($date), $db->quote($fnum), $user);

        $query
            ->insert($db->quoteName('#__emundus_favoris'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();
    }
    public function deleteToFavoris($fnum,$user){
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('user') . ' = ' . $user,
            $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum)
        );

        $query->delete($db->quoteName('#__emundus_favoris'));
        $query->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }
}