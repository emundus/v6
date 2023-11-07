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

use Joomla\CMS\Factory;

/**
 * eMundus Component Controller
 *
 * @package    Joomla.emundus
 * @subpackage Components
 */
//error_reporting(E_ALL);
class EmundusControllerInterview extends JControllerLegacy
{
	protected $app;

    private $_user;
	private $_db;

    public function __construct($config = array())
    {
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'files.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'filters.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'list.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'access.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'emails.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'export.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus' . DS . 'helpers' . DS . 'menu.php');


		$this->app = Factory::getApplication();
        $this->_user = $this->app->getSession()->get('emundusUser');
        $this->_db = Factory::getDBO();

        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false)
    {
        // Set a default view if none exists
        if (!$this->input->get('view')) {
            $default = 'files';
            $this->input->set('view', $default);
        }
        parent::display();
    }
    function pdf(){
        
        $fnum = $this->input->getString('fnum', null);
        $student_id = $this->input->getInt('student_id', $this->input->getInt('user', $this->_user->id));

        if (!EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $fnum) )
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

        $m_profile = $this->getModel('Profile');
        $m_campaign = $this->getModel('Campaign');

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
