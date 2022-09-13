<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerAcademicTranscript extends JControllerLegacy {

	public function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
        $jinput = JFactory::getApplication()->input;
		if (! $jinput->get('view')) {
			$default = 'academicTranscript';
            $jinput->set('view', $default );
		}
		$user = JFactory::getUser();
		$menu = JFactory::getApplication()->getMenu()->getActive();
		$access = !empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			parent::display();
		}
    }

	////// UPDATE LEARNING AGREEMENT ///////////////////
	public function update() {
		//$allowed = array("Super Users", "Administrator", "Publisher", "Editor", "Author");
		$user = JFactory::getUser();
		$menu = JFactory::getApplication()->getMenu()->getActive();
		$access = !empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access))
			die('You are not allowed to access to this page.');
		$db = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;
		$student_id 	= $jinput->getInt('student_id');
		$grades 		= $jinput->get('grade');
		$obtained 		= $jinput->get('obtained');
		//die(print_r($obtained));


        try {
            $db->setQuery('DELETE FROM `#__emundus_academic_transcript` WHERE student_id='.$student_id);
            $db->execute();
        } catch (Exception $e) {
            die($e->getMessage());
        }

		$i=0;
		foreach ($grades as $grade) {
			$tui = explode('___', key($grades));

			if (isset($grade) && $grade != '') {
				$o = $obtained[$tui[0]]=='on'?1:0;
				$query = 'INSERT INTO `#__emundus_academic_transcript` (`code`, `teaching_unit_id`, `student_id`, `user_id`, `grade`, `obtained`)
							VALUES ("'.$tui[1].'", '.$tui[0].', '.$student_id.', '.$user->id.', '.$grade.', '.$o.')';
                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    die($e->getMessage());
                }
			}
			next($grades);
			$i++;
		}
		$this->setRedirect('index.php?option=com_emundus&view=academictranscript&student_id='.$student_id.'&tmpl=component&action=DONE');
	}

} //END CLASS
?>
