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
    public function getUpload($fnum,$attachment_id){


        $query = $this->_db->getQuery(true);

        $query->select($this->_db->quoteName('filename'));
        $query->from($this->_db->quoteName('#__emundus_uploads'));
        $query->where($this->_db->quoteName('fnum') . ' LIKE ' . $this->_db->quote($fnum). ' AND' . $this->_db->quoteName('attachment_id') . ' = ' . $this->_db->quote($attachment_id));

        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }
    public function getSecondaryUpload($fnum,$attachment_id){


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
   }
   public function updatePlusNbVote($fnum, $user,$thematique){

        $nb_vote = $this->getNbVote($fnum);

       $date = new DateTime();
       $date = $date->format('U = Y-m-d H:i:s');

       $query = $this->_db->getQuery(true);


       $columns = array('time_date', 'fnum', 'user', 'thematique','','','');


       $values = array($date, $fnum, $user,$this->_db->quote('Inserting a record using insert()'), 1);


       $query
           ->insert($this->_db->quoteName('#__emundus_vote'))
           ->columns($this->_db->quoteName($columns))
           ->values(implode(',', $values));


       $this->_db->setQuery($query);
       $this->_db->execute();

   }
    public function updateMinusNbVote($fnum){

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
    }
}