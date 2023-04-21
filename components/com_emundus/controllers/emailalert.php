<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
jimport( 'joomla.application.component.view');

class EmundusControllerEmailalert extends JControllerLegacy {

	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'emailalert';
			JRequest::setVar('view', $default );
		}
		parent::display();
    }
	
	function generate(){		
		$model = $this->getModel('emailalert');
		$key = $model->getKey();
		if($key){
			$model->getInsert();
		}
		else echo JText::_('NOT_ALLOWED'); 
	}
	
	function send(){
		$app = JFactory::getApplication();
		$db	= JFactory::getDBO();

		$model = $this->getModel('emailalert');
		$key = $model->getKey();

		if($key){
			$emailfrom = $app->getCfg('mailfrom');
			$fromname = $app->getCfg('fromname');
			$message = $model->getSend();
			foreach($message as $m){
				if(JUtility::sendMail( $emailfrom, $fromname, $m->email, $m->subject, $m->message, true )){
					usleep(100);
					$query = 'UPDATE #__messages SET state = 0 WHERE user_id_to ='.$m->user_id_to;
					$db->setQuery($query);
					$db->Query();
				}
			}
		} else{
			echo JText::_('NOT_ALLOWED');
		}
	}
}
?>