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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewExport_select_columns extends JViewLegacy
{
	private $_user;

	public $elements;
	public $form;
	public $program;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');

		$this->_user = JFactory::getUser();

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$m_program = new EmundusModelProgramme();
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'admission.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'decision.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');


		$jinput     = JFactory::getApplication()->input;
		$prg        = $jinput->getString('code', null);
		$this->form = $jinput->get('form', null);
		$camp       = $jinput->get('camp', null);
		$profile    = $jinput->get('profile', null);
		$all        = $jinput->get('all', null);

		$program = $m_program->getProgramme($prg);
		$code    = array();
		$camps   = array();
		$code[]  = $prg;
		$camps[] = $camp;

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}

		$m_admission = new EmundusModelAdmission;
		$m_decision  = new EmundusModelDecision;
		$m_eval      = new EmundusModelEvaluation;

		if ($this->form == "decision") {
			$this->elements = $m_decision->getDecisionElementsName(0, 0, $code, $all);
		}
		elseif ($this->form == "admission") {
			$this->elements = $m_admission->getApplicantAdmissionElementsName(0, 0, $code, $all);
		}
		elseif ($this->form == "evaluation") {
			$this->elements = $m_eval->getEvaluationElementsName(0, 0, $code, $all);
		}
		else {
			$this->elements = EmundusHelperFiles::getElements($code, $camps, [], $profile);
		}

		$allowed_groups = EmundusHelperAccess::getUserFabrikGroups($this->_user->id);
		if ($allowed_groups !== true) {
			foreach ($this->elements as $key => $elt) {
				if (!in_array($elt->group_id, $allowed_groups)) {
					unset($this->elements[$key]);
				}
			}
		}

		$this->program = $program->label;

		parent::display($tpl);
	}
}