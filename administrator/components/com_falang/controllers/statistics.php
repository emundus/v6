<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class StatisticsController extends JControllerLegacy  {

	/**
	 * @var object reference to the currecnt view
	 * @access private
	 */
	var $_view = null;
	
	/**
	 * @var object reference to the current model
	 * @access private
	 */
	var $_model = null;
	
	/**
	 * PHP 4 constructor for the tasker
	 *
	 * @return joomfishTasker
	 */
	function __construct( ){
		parent::__construct();
		$this->registerTask('show',  'display' );
		$this->registerTask('check',  'checkstatus' );
	}
	/**
	 * Standard display control structure
	 * 
	 */
    function display($cachable = false, $urlparams = array())
	{
		$this->view =  $this->getView("statistics");
		parent::display();
	}

	/**
	 * 
	 */
	function checkstatus() {
	    $jinput = Factory::getApplication()->getInput();
		$type = $jinput->getString('type', '' );
		$phase = $jinput->getInt('phase', 1 );
		$statecheck_i = $jinput->getInt('statecheck_i', -1);
		$htmlResult = Text::_('MANAGEMENT_INTRO');
		$link = '';
		// get the view
		$this->_view =  $this->getView("statistics");
		$this->_model =  $this->getModel('statistics');

		switch ($type) {
			case 'translation_status':
				$message = '';
				$session = JFactory::getSession();
				$translationStatus = $session->get('translationState',array());
				$translationStatus = $this->_model->testTranslationStatus($translationStatus, $phase, $statecheck_i, $message);
				$session->set('translationState', $translationStatus );

				$htmlResult = $this->_view->renderTranslationStatusTable($translationStatus, $message);
				if( $phase<=3 ) {
					$link = 'index.php?option=com_falang&task=statistics.check&type=translation_status&phase=' .$phase;

					if( $statecheck_i > -1) {
						$link .= '&statecheck_i='.$statecheck_i;
					}
				} else {
					$session->set('translationState', null );
				}
				break;

			case 'original_status':
				$message = '';
				$session = JFactory::getSession();
				$originalStatus = $session->get('originalStatus', array());
				$langCodes = array();
				$jfManager = FalangManager::getInstance();
				$languages = $jfManager->getLanguages(false);
				foreach ($languages as $lang) {
					$langCodes[] = $lang->getLanguageCode();
				}

				$originalStatus = $this->_model->testOriginalStatus($originalStatus, $phase, $statecheck_i, $message, $languages);
				$session->set('originalStatus', $originalStatus );
				$htmlResult = $this->_view->renderOriginalStatusTable($originalStatus, $message, $langCodes);

				if( $phase<=2 ) {
					$link = 'index.php?option=com_falang&task=statistics.check&type=original_status&phase=' .$phase;

					if( $statecheck_i > -1) {
						$link .= '&statecheck_i='.$statecheck_i;
					}
				} else {
					$session->set('originalStatus', null );
				}
				break;
		}
		// Set the layout
		$this->_view->setLayout('result');
		$this->_view->htmlResult = $htmlResult;
		$this->_view->reload = $link;
		$this->_view->display();
	}	
}
