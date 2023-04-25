<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.controller');
JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class ManageController extends JControllerLegacy  {

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
	 */
	function __construct( ){
		parent::__construct();
		$this->registerTask('show',  'display' );
		$this->registerTask('check',  'checkstatus' );
		$this->registerTask('copy',  'copy' );
	}
	/**
	 * Standard display control structure
	 * 
	 */
    function display($cachable = false, $urlparams = array())
	{
		$this->view =  $this->getView("manage");
		parent::display();
	}
	
	/**
	 * 
	 */
	function copy() {
	    $jinput = Factory::getApplication()->getInput();
		$type = $jinput->getString('type', '' );
		$phase = $jinput->getInt('phase', 1 );
		$statecheck_i = $jinput->getInt('statecheck_i', -1);
		$state_catid = $jinput->getString('state_catid', '' );
		$htmlResult = Text::_('MANAGEMENT_INTRO');
		$language_id = $jinput->getInt( 'language_id', null );
		$overwrite = $jinput->getInt( 'overwrite', 0 );
		$link = '';
		
		// get the view
		$this->_view =  $this->getView("manage");
		$this->_model =  $this->getModel('manage');

		switch ($type) {
			case 'original_language':
				$message = '';
				$session = JFactory::getSession();
				$original2languageInfo = $session->get('original2languageInfo',array());
				$original2languageInfo = $this->_model->copyOriginalToLanguage($original2languageInfo, $phase, $state_catid, $language_id, $overwrite, $message);
				$session->set('original2languageInfo', $original2languageInfo );

				if($phase == 1) {
					$langlist = JHTML::_('select.genericlist', $this->_model->getLanguageList(), 'select_language', 'id="select_language" class="inputbox" size="1"' );
					$htmlResult = $this->_view->renderCopyInformation($original2languageInfo, $message, $langlist);
				} elseif( $phase == 2 || $phase == 3 ) {
					$htmlResult = $this->_view->renderCopyProcess($original2languageInfo, $message);
					$link = 'index.php?option=com_falang&task=manage.copy&type=original_language&phase=' .$phase. '&language_id=' .$language_id. '&state_catid=' .$state_catid. '&overwrite=' .$overwrite;
				} else {
					$htmlResult = $this->_view->renderCopyProcess($original2languageInfo, $message);
					$session->set('original2languageInfo', null );
				}
				break;
		}
		$this->_view->setLayout('result');
		$this->_view->htmlResult = $htmlResult;
		$this->_view->reload = $link;
		$this->_view->display();
	}
	
}
