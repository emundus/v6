<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewExport_select_columns extends JViewLegacy
{
	var $_user = null;
	var $_db = null;

	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'programme.php');

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}

    function display($tpl = null) {
        $m_program  = new EmundusModelProgramme();

        require_once (JPATH_COMPONENT.DS.'models'.DS.'admission.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');

        $jinput = JFactory::getApplication()->input;
        $prg = $jinput->getVar('code', null);
        $form = $jinput->getVar('form', null);
        $all = $jinput->get('all', null);
        $camp = $jinput->getVar('camp', null);
        $profile = $jinput->getVar('profile', null);

        if (!empty($prg)) {
            $program = $m_program->getProgramme($prg);
            $code = $prg;
        } else {
            $programs = $m_program->getProgrammes();
            $this->programs = $programs;
            $code = null;
        }

        $camps = array();
        
        $camps[] = $camp;
        $current_user = JFactory::getUser();
        
        if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
	        die(JText::_('ACCESS_DENIED'));
        }

        $m_admission = new EmundusModelAdmission;
        $m_eval = new EmundusModelEvaluation;
        
        //TODO fix bug when a different application form is created for the same programme. Need to now the campaign id, then associated profile and menu links...
	    // To fix this : Get all campaigns, get profile, get menu, check form IDs, for each unique ID: make an array containing the code below (or some variety of it).
	    // When displaying the results: make tabs or panels separating the different forms for the programme.


        if ($form == "decision") {
	        $elements = $m_admission->getAdmissionElementsName(0, 0, $code);
        } elseif ($form == "admission") {
	        $elements = $m_admission->getApplicantAdmissionElementsName(0, 0, $code, $all);
        } elseif ($form == "evaluation") {
	        $elements = $m_eval->getEvaluationElementsName(0, 0, $code, $all);
        } else {
	        $elements = EmundusHelperFiles::getElements($code, $camps, [], $profile);
        }

		$label = $program->label;
        $this->assignRef('elements', $elements);
        $this->assignRef('form', $form);
        $this->assignRef('program', $label);
		parent::display($tpl);
    }
}