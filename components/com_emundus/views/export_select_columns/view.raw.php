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

		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}

    function display($tpl = null) {

        require_once (JPATH_COMPONENT.DS.'models'.DS.'admission.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');

        $jinput = JFactory::getApplication()->input;
        $prg    = $jinput->getVar('code', null);
        $view   = $jinput->getVar('viewcall', null);
        $form   = $jinput->getVar('form', null);
        $year   = $jinput->getVar('year', null);
        $camp   = $jinput->getVar('camp', null);


        $code       = array();
        $years       = array();
        $code[]     = $prg;
        $years[]     = $year;

        $current_user = JFactory::getUser();

        
        if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die(JText::_('ACCESS_DENIED'));

        $m_admission = new EmundusModelAdmission;
        $m_eval = new EmundusModelEvaluation;
        
        //@TODO fix bug when a different application form is created for the same programme. Need to now the campaign id, then associated profile and menu links...
        if ($form == "decision")
            $elements = $m_admission->getAdmissionElementsName(1, 1, $code);
        elseif ($form == "admission")
            $elements = $m_admission->getApplicantAdmissionElementsName(0, 0, $code);
        elseif ($form == "evaluation")
            $elements = $m_eval->getEvaluationElementsName(1, 1, $code);
        else
		    $elements = EmundusHelperFiles::getElements($code, $years, $camp);

        $this->assignRef('elements', $elements);
        $this->assignRef('form', $form);
		parent::display($tpl);
    }
}
?>