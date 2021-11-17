<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @copyright Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');

if ($this->applicant_can_renew) {
	//JError::raiseNotice('YEAR', JText::_( 'FILE_NEW_APPLICATION' ));
	//$url = 'index.php?option=com_emundus&controller=renew_application&task=new_application&view=renew_application&uid='.$this->current_user->id.'&up='.$this->current_user->profile;
	$url = 'index.php?option=com_fabrik&view=form&formid=102';
	echo '<p><center><h2>'.JText::_( 'COM_EMUNDUS_APPLICATION_START_FILE_NEW_APPLICATION' ).' <a href="'.$url.'" class="button">'.JText::_( 'JYES' ).'</a></h2></center></p>';

} else {
	JError::raiseNotice('YEAR', JText::_( 'COM_EMUNDUS_APPLICATION_FILE_NEW_APPLICATION_IS_NOT_POSSIBLE' ));
}
?>
