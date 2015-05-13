<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');

$current_user = JFactory::getUser();

//if($this->statut){
	JError::raiseNotice('YEAR', JText::_( 'FILE_NEW_APPLICATION' ));
	echo '<p><center><h2>'.JText::_( 'START_FILE_NEW_APPLICATION' ).' <a href="index.php?option=com_emundus&controller=renew_application&task=new_application&view=renew_application&uid='.$current_user->id.'&up='.$current_user->profile.'" class="button">'.JText::_( 'JYES' ).'</a></h2></center></p>';
	/*echo '<center><h2>'.JText::_( 'START_FILE_NEW_APPLICATION' ).'</h2>';
	echo '<a href="index.php?option=com_emundus&controller=renew_application&task=edit_user&view=renew_application&uid='.$current_user->id.'&up='.$current_user->profile.'">
			<img src="media/com_emundus/images/icones/renew.png"/>
		</a></center>';*/
//}
?>