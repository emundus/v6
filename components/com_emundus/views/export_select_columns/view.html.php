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

	protected $elements;
	protected $form;
	protected $program;

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
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');

		$jinput     = JFactory::getApplication()->input;
		$prg        = $jinput->get('code', null);
		$this->form = $jinput->get('form', null);
		$all        = $jinput->get('all', null);
		$camp       = $jinput->get('camp', null);
		$profile    = $jinput->get('profile', null);

		if (!empty($prg)) {
			$program = $m_program->getProgramme($prg);
			$code    = $prg;
		}
		else {
			$programs       = $m_program->getProgrammes();
			$this->programs = $programs;
			$code           = null;
		}

		$camps = array();

		$camps[] = $camp;

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}

		$m_admission = new EmundusModelAdmission;
		$m_eval      = new EmundusModelEvaluation;

		//TODO fix bug when a different application form is created for the same programme. Need to now the campaign id, then associated profile and menu links...
		// To fix this : Get all campaigns, get profile, get menu, check form IDs, for each unique ID: make an array containing the code below (or some variety of it).
		// When displaying the results: make tabs or panels separating the different forms for the programme.


		if ($this->form == "decision") {
			$this->elements = $m_admission->getAdmissionElementsName(0, 0, $code);
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

		$this->program = $program->label;

		parent::display($tpl);
	}
}