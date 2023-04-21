<?php
/**
 * Messages controller used for the creation and emission of messages from the platform.
 *
 * @package    Joomla
 * @subpackage Emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Hugo Moracchini
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerQcm extends JControllerLegacy {

    var $model = null;

    /**
     * Constructor
     *
     * @since 3.8.6
     */
    function __construct($config = array()) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'qcm.php');
	    parent::__construct($config);
        $this->model = $this->getModel('qcm');
    }

    public function getQuestions() {
	    $results = [];
        $jinput = JFactory::getApplication()->input;
        $questions = $jinput->getString('questions');

		// todo: check user is inside qcm environment ?
		if (!empty($questions)) {
			$m_qcm = $this->model;
			$results = $m_qcm->getQuestions($questions);
		}

        echo json_encode((object)$results);
        exit;
    }

    public function saveanwser() {
        $session        = JFactory::getSession();
        $current_user   = $session->get('emundusUser');

        $m_qcm = $this->model;

        $jinput = JFactory::getApplication()->input;
        $answers = $jinput->getRaw('answer');
        $question = $jinput->getString('question');
        $formid = $jinput->getString('formid');
        $module = $jinput->getInt('module');

        $results = $m_qcm->saveAnswer($question,$answers,$current_user,$formid,$module);

        echo json_encode((object)$results);
        exit;
    }

    public function updatepending() {
        $session        = JFactory::getSession();
        $current_user   = $session->get('emundusUser');

        $m_qcm = $this->model;

        $jinput = JFactory::getApplication()->input;
        $pending = $jinput->getInt('pending');
        $formid = $jinput->getInt('formid');

        $results = $m_qcm->updatePending($pending,$current_user, $formid);

        echo json_encode((object)$results);
        exit;
    }

    public function getintro() {
        $m_qcm = $this->model;

        $jinput = JFactory::getApplication()->input;
        $module = $jinput->getInt('module');

        $results = $m_qcm->getIntro($module);

        echo json_encode((object)$results);
        exit;
    }

}
