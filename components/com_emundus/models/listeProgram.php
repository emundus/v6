
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

class EmundusModelListeProgram extends JModelList
{
    public function __construct()
    {
        parent::__construct();
        global $option;
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'logs.php');
        require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');

        $this->_mainframe = JFactory::getApplication();

        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getSession()->get('emundusUser');

        $this->locales = substr(JFactory::getLanguage()->getTag(), 0, 2);
    }
    public function getCampaign($training){
        $query = $this->_db->getQuery(true);


        $query->select($this->_db->quoteName('id'));
        $query->from($this->_db->quoteName('#__emundus_setup_campaigns'));
        $query->where($this->_db->quoteName('training') . ' LIKE ' . $this->_db->quote($training));


        $this->_db->setQuery($query);


        return $this->_db->loadResult();
    }
}