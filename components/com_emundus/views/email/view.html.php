<?php
/**
 * @package    Joomla
 * @subpackage emundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
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

	public function __construct($config = array())
	{
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}

    public function display($tpl = null)
    {
    	if( !EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)
		  )
			die( JText::_('RESTRICTED_ACCESS') );

	    $jinput = JFactory::getApplication()->input;
	    $fnums = $jinput->getString('fnums', null);

	    $document = JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
		$document->addStyleSheet( JURI::base()."media/com_emundus/lib/chosen/chosen.min.css" );
		$document->addScript( JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );
		$document->addScript( JURI::base()."media/com_emundus/lib/chosen/chosen.jquery.min.js" );

/*	    if (is_null($fnums))
	    {
		   
		    if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id))
			    die("ACCESS_DENIED");

		    if(EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id))
		    {
			    if($this->_user->profile!=16)
			    {
				    $email_applicant = @EmundusHelperEmails::createEmailBlock(array('this_applicant'));
			    }
		    }
		    else
			    $email_applicant = '';
		    $this->assignRef('email', $email_applicant);

		    //var_dump($logged);
		    parent::display($tpl);
	    }
	    else
	    {*/
		   $fnums = (array) json_decode(stripslashes($fnums));
		   $dest = $jinput->getInt('desc', 0);
		   $fnum_array = array();

		    if($dest == 0)
		    {
			    require_once(JPATH_BASE . '/components/com_emundus/models/application.php');
			    $appModel = new EmundusModelApplication();
			    
			    $tables = array('jos_users.name', 'jos_users.username', 'jos_users.email', 'jos_users.id');
			    foreach ($fnums as $fnum)
			    {
				    if(EmundusHelperAccess::asAccessAction(9, 'c', $this->_user->id, $fnum->fnum) && is_int($fnum->sid))
				    {
					    $user = $appModel->getApplicantInfos($fnum->sid, $tables);
					    $user['campaign_id'] = $fnum->cid;
					    $users[] = $user;
				    }
			    }
			    $this->email = @EmundusHelperEmails::createEmailBlock(array('applicants'), $users);
		    }
		    elseif($dest == 1)
		    {
			    require_once(JPATH_BASE . '/components/com_emundus/models/users.php');
			    $userModel = new EmundusModelUsers();

			    foreach ($fnums as $fnum)
			    {
			    	if(EmundusHelperAccess::asAccessAction(15, 'c', $this->_user->id, $fnum->fnum))
				    {
					    $fnum_array[] = $fnum->fnum;
				    }
			    }
			    $evs = $userModel->getEvalutorByFnums($fnum_array);

			    $this->email = @EmundusHelperEmails::createEmailBlock(array('evaluators'), $evs);
		    }
		    elseif($dest == 2)
		    {
			    $this->email = @EmundusHelperEmails::createEmailBlock(array('groups'));
		    }
		    elseif($dest == 3)
		    {
			   //require_once(JPATH_BASE . '/components/com_emundus/models/users.php');
			   require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
			    //$userModel = new EmundusModelUsers();
			    $evaluations = new EmundusModelEvaluation;
			    $eMConfig = JComponentHelper::getParams('com_emundus');
				$reference_table = $eMConfig->get('reference_table', '#__emundus_references');
				$reference_field = $eMConfig->get('reference_field', 'Email_1 as email');
			    
			    foreach ($fnums as $fnum)
			    {
				    if(EmundusHelperAccess::asAccessAction(18, 'c', $this->_user->id, $fnum->fnum))
				    {
					    $fnum_array[] = $fnum->fnum;
				    }
			    }
			    $experts_list = $evaluations->getExperts(@$fnums[0]->fnum, $reference_field, $reference_table);

			    $email = @EmundusHelperEmails::createEmailBlock(array('expert'), $experts_list);
			    $this->assignRef('fnums', $fnums[0]);
			    $this->assignRef('experts_list', $experts_list);
			    $this->assignRef('email', $email);
		    }

			parent::display($tpl);

	  //  }
	

    }
}
?>