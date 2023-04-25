<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.emundus
 * @subpackage Components
 */
//error_reporting(E_ALL);
class EmundusControllerInterview extends JControllerLegacy
{
    var $_user = null;
    var $_db = null;

    public function __construct($config = array())
    {
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'files.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'filters.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'list.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'emails.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'export.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'menu.php');


        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false)
    {
        // Set a default view if none exists
        if (!JFactory::getApplication()->input->get('view')) {
            $default = 'files';
            JFactory::getApplication()->input->set('view', $default);
        }
        parent::display();
    }
    function pdf(){
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $student_id = $jinput->getInt('student_id', $jinput->getInt('user', $this->_user->id));

        if (!EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum) )
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

        $m_profile = $this->getModel('profile');
        $m_campaign = $this->getModel('campaign');

        if (!empty($fnum)) {
            $candidature = $m_profile->getFnumDetails($fnum);
            $campaign = $m_campaign->getCampaignByID($candidature['campaign_id']);
            $name = 'interview-'.$fnum.'.pdf';
            $tmpName = JPATH_SITE.DS.'tmp'.DS.$name;
        }

        $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_interview'.$campaign['training'].'.php';

        if (!file_exists($file)) {
            $file = JPATH_LIBRARIES.DS.'emundus'.DS.'pdf_interview.php';
        }

        if (!file_exists(EMUNDUS_PATH_ABS.$student_id)) {
            mkdir(EMUNDUS_PATH_ABS.$student_id);
            chmod(EMUNDUS_PATH_ABS.$student_id, 0755);
        }

        require_once($file);
        pdf_interview($student_id, $fnum, true, $name);

        exit();
    }
}
