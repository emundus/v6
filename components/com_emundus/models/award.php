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
    /*public function getSecondaryUpload($fnum,$attachment_id){


        $query = $this->_db->getQuery(true);

        $query->select($this->_db->quoteName('filename'));
        $query->from($this->_db->quoteName('#__emundus_uploads'));
        $query->where($this->_db->quoteName('fnum') . ' LIKE ' . $this->_db->quote($fnum). ' AND' . $this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($attachment_id));

        $this->_db->setQuery($query);

        return $this->_db->loadColumn();
    }
   public function getNbVote($fnum){


       $query = $this->_db->getQuery(true);

       $query->select($this->_db->quoteName('nb_vote'));
       $query->from($this->_db->quoteName('#__emundus_challenges'));
       $query->where($this->_db->quoteName('fnum') . ' LIKE ' . $this->_db->quote($fnum));


       $this->_db->setQuery($query);

       return $this->_db->loadResult();
   }
   public function getDataChallenge($fnum){
       $query = $this->_db->getQuery(true);

       $query->select($this->_db->quoteName('*'));
       $query->from($this->_db->quoteName('#__emundus_challenges'));
       $query->where($this->_db->quoteName('fnum') . ' LIKE ' . $this->_db->quote($fnum));


       $this->_db->setQuery($query);

       return $this->_db->loadObject();
   }*/
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
    /*public function updateMinusNbVote($fnum){

        $nb_vote = $this->getNbVote($fnum);
        $query = $this->_db->getQuery(true);
        $fields = array(
            $this->_db->quoteName('nb_vote') . ' = ' . $this->_db->quote($nb_vote-1)
        );

        $conditions = array(
            $this->_db->quoteName('fnum') . 'LIKE' . $this->_db->quote($fnum)
        );

        $query->update($this->_db->quoteName('#__emundus_challenges'))->set($fields)->where($conditions);

        $this->_db->setQuery($query);

        return $this->_db->execute();
    }*/
}