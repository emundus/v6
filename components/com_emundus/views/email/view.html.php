<?php
/**
 * @package    Joomla
 * @subpackage emundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
//error_reporting(E_ALL);
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewEmail extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	protected $email;

	public function __construct($config = array()) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}

    public function display($tpl = null) {

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
		    die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
	    }
/******
 * DEPRECATED
 * You should use raw view
 *
	    $document = JFactory::getDocument();
		$document->addStyleSheet("media/com_emundus/css/emundus.css" );
		$document->addStyleSheet("media/jui/css/chosen.min.css" );
		$document->addScript("media/jui/js/chosen.jquery.min.js" );

		$jinput = JFactory::getApplication()->input;
	    $fnums_post = $jinput->getString('fnums', null);
		$fnums_array = ($fnums_post=='all')?'all':(array) json_decode(stripslashes($fnums_post), false, 512, JSON_BIGINT_AS_STRING);

	    if ($fnums_array == 'all') {
			$m_files = new EmundusModelFiles;
		    $fnums = $m_files->getAllFnums();
		    $fnums_infos = $m_files->getFnumsInfos($fnums, 'object');
		    $fnums = $fnums_infos;
		} else {
            $fnums = array();
            foreach ($fnums_array as $key => $value) {
                $fnums[] = $value->fnum;
            }
        }

	   $dest = $jinput->getInt('desc', 0);
	   $fnum_array = array();

        if ($dest == 0) {
		    require_once(JPATH_BASE . '/components/com_emundus/models/application.php');
		    $m_application = new EmundusModelApplication();

		    $tables = array('jos_users.name', 'jos_users.username', 'jos_users.email', 'jos_users.id');
		    foreach ($fnums as $fnum) {
			    if (EmundusHelperAccess::asAccessAction(9, 'c', $this->_user->id, $fnum->fnum) && !empty($fnum->sid)) {
				    $user = $m_application->getApplicantInfos($fnum->sid, $tables);
				    $user['campaign_id'] = $fnum->cid;
				    $users[] = $user;
			    }
		    }
		    $this->email = @EmundusHelperEmails::createEmailBlock(array('applicants'), $users);

        } elseif ($dest == 1) {

        	require_once(JPATH_BASE.'/components/com_emundus/models/users.php');
		    $m_users = new EmundusModelUsers();

		    foreach ($fnums as $fnum) {
		        if (EmundusHelperAccess::asAccessAction(15, 'c', $this->_user->id, $fnum->fnum)) {
				    $fnum_array[] = $fnum->fnum;
			    }
		    }
		    $evs = $m_users->getEvalutorByFnums($fnum_array);
		    $this->email = @EmundusHelperEmails::createEmailBlock(array('evaluators'), $evs);

        } elseif ($dest == 2) {
        	$this->email = @EmundusHelperEmails::createEmailBlock(array('groups'));
        } elseif ($dest == 3) {

		    require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'emails.php');
		    require_once(JPATH_BASE . '/components/com_emundus/models/application.php');

		    $m_application = new EmundusModelApplication();
		    $m_evaluation = new EmundusModelEvaluation;
		    $h_emails = new EmundusHelperEmails();

		    $eMConfig = JComponentHelper::getParams('com_emundus');
			$reference_table = $eMConfig->get('reference_table', '#__emundus_references');
			$reference_field = $eMConfig->get('reference_field', 'Email_1 as email');
			$default_email_tmpl = $eMConfig->get('default_email_tmpl', 'expert');

		    foreach ($fnums as $fnum) {
			    if (EmundusHelperAccess::asAccessAction(18, 'c', $this->_user->id, $fnum->fnum)) {
			        $fnum_array[] = $fnum->fnum;
				    $app_file = $m_application->getApplication($fnum->fnum);
				    $fnum->status = $app_file->status;
			    }
		    }

		    $this->experts_list = $m_evaluation->getExperts();
		    $this->email = $h_emails->createEmailBlock(['expert'], $this->experts_list);
		    $this->fnums = $fnums;
		    $this->fnum_array = $fnum_array;
			$this->default_email_tmpl = $default_email_tmpl;
	    }
*/
		parent::display($tpl);
    }
}
