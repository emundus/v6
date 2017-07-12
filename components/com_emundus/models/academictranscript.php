<?php
/**
 * Learning Agreement Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     eMundus SAS - Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelAcademicTranscript extends JModelList
{
	var $_db = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		$this->_db = JFactory::getDBO();
	}
	
	function getStudentLearningUnits()
	{
		$current_user = JFactory::getSession()->get('emundusUser');
		$student_id = JRequest::getVar('student_id', null, 'GET', 'none', 0);

		if($current_user->profile == 4 || $current_user->profile == 5)
			$where = ' AND estu.university_id='.$current_user->university_id.' ';
		else
			$where = '';
		$query = 'SELECT ela.teacher_id, ela.teaching_unity_id, ela.published, estu.id, estu.code, estu.label, eat.grade, eat.obtained  
			FROM #__emundus_learning_agreement AS ela
			LEFT JOIN #__emundus_setup_teaching_unity AS estu ON estu.id=ela.teaching_unity_id  
			LEFT JOIN #__emundus_academic_transcript AS eat ON eat.teaching_unit_id=ela.teaching_unity_id AND eat.student_id=ela.user_id  
			WHERE ela.user_id='.$student_id.$where.' 
			ORDER BY estu.university_id, estu.label'; 
		$this->_db->setQuery( $query );
		//echo(str_replace('#_','jos',$query));
		return $this->_db->loadObjectList();
	}
	
	function getTeachingUnity()
	{
		$query = 'SELECT estu.id, estu.code, estu.label, estu.university_id, c.title as university, estu.schoolyear, estu.semester, estu.ects, estu.notes 
			FROM #__emundus_setup_teaching_unity AS estu
			LEFT JOIN #__categories AS c ON c.id=estu.university_id 
			WHERE estu.published=1'; 
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getLearningAgreementSatus()
	{
		$query = 'SELECT id, user_id, teacher_id, status FROM #__emundus_learning_agreement_status';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList('user_id');
	}
}
?>